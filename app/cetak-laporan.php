<?php
include "config.php";

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$filter = $_GET['filter'] ?? '';

$where_date = "";
$periode_text = "Semua Waktu";
if ($filter === 'hari_ini') {
    $where_date = "DATE(t.tanggal) = CURDATE()";
    $periode_text = "Hari Ini (" . date('d M Y') . ")";
} elseif ($filter === 'minggu_ini') {
    $where_date = "YEARWEEK(t.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
    $periode_text = "Minggu Ini";
} elseif ($filter === 'bulan_ini') {
    $where_date = "MONTH(t.tanggal) = MONTH(CURDATE()) AND YEAR(t.tanggal) = YEAR(CURDATE())";
    $periode_text = "Bulan Ini (" . date('F Y') . ")";
}

$where_clause = $where_date ? "WHERE $where_date" : "";

// Ambil Rekapitulasi
$q_rekap = mysqli_query($conn, "
    SELECT 
        COUNT(t.id_transaksi) as total_transaksi,
        SUM(t.total) as total_penjualan,
        COALESCE(SUM((SELECT SUM(dt.jumlah) FROM detail_transaksi dt WHERE dt.id_transaksi = t.id_transaksi)), 0) as produk_terjual
    FROM transaksi t $where_clause
");
$rekap = mysqli_fetch_assoc($q_rekap);

// Ambil Data Transaksi
$query_transaksi = "
    SELECT t.*, u.nama as nama_pelanggan
    FROM transaksi t
    JOIN users u ON t.id_user = u.id_user
    $where_clause
    ORDER BY t.tanggal DESC
";
$result_transaksi = mysqli_query($conn, $query_transaksi);

// Buat PDF dengan FPDF
class PDF extends \Fpdf\Fpdf {
    // Page header
    function Header() {
        // Logo atau Nama Toko
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 8, 'ATK RARA', 0, 1, 'C');
        
        // Alamat Toko
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(108, 117, 125);
        $this->Cell(0, 5, 'Jl. Emmy Saelan No.31, Malango\', Kec. Rantepao', 0, 1, 'C');
        $this->Cell(0, 5, 'Kabupaten Toraja Utara, Sulawesi Selatan 91833', 0, 1, 'C');
        
        // Garis Pembatas Kop Surat
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, 32, 200, 32);
        $this->Ln(8);
    }

    // Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        // Page number
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'C');
    }
}

// Inisialisasi FPDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Judul Laporan & Periode
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 8, 'LAPORAN PENJUALAN', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 6, 'Periode: ' . $periode_text, 0, 1, 'C');
$pdf->Ln(5);

// Ringkasan Laporan
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(0, 7, ' RINGKASAN LAPORAN', 0, 1, 'L', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 7, 'Total Omset Penjualan', 1, 0, 'L');
$pdf->Cell(130, 7, 'Rp ' . number_format($rekap['total_penjualan'] ?? 0, 0, ',', '.'), 1, 1, 'L');
$pdf->Cell(60, 7, 'Total Transaksi', 1, 0, 'L');
$pdf->Cell(130, 7, number_format($rekap['total_transaksi'] ?? 0), 1, 1, 'L');
$pdf->Cell(60, 7, 'Total Produk Terjual', 1, 0, 'L');
$pdf->Cell(130, 7, number_format($rekap['produk_terjual'] ?? 0) . ' pcs', 1, 1, 'L');
$pdf->Ln(8);

// Tabel Daftar Transaksi
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(40, 116, 240); // Primary Blue
$pdf->SetTextColor(255, 255, 255);

// Headers: No(10), ID(25), Tanggal(35), Pelanggan(35), Metode(20), Item(40), Total(25)
$pdf->Cell(8, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'ID Transaksi', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Tanggal & Waktu', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Nama Pelanggan', 1, 0, 'C', true);
$pdf->Cell(18, 8, 'Metode', 1, 0, 'C', true);
$pdf->Cell(49, 8, 'Item Pembelian', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Total', 1, 1, 'C', true);

// Body
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(33, 37, 41);

$no = 1;
while ($row = mysqli_fetch_assoc($result_transaksi)) {
    $id_trx = $row['id_transaksi'];
    $trx_code = 'TRX-' . str_pad($id_trx, 5, '0', STR_PAD_LEFT);
    $tanggal = date('d M Y, H:i', strtotime($row['tanggal']));
    $pelanggan = $row['nama_pelanggan'];
    $metode = strtoupper($row['metode_pembayaran']);
    $total = 'Rp ' . number_format($row['total'], 0, ',', '.');
    
    // Dapatkan detail item pembelian
    $items = [];
    $q_detail = mysqli_query($conn, "SELECT p.nama_produk, dt.jumlah FROM detail_transaksi dt JOIN produk p ON dt.id_produk = p.id_produk WHERE dt.id_transaksi = $id_trx");
    while($dt = mysqli_fetch_assoc($q_detail)) {
        $items[] = $dt['nama_produk'] . " (" . $dt['jumlah'] . "x)";
    }
    $items_text = implode(', ', $items);
    
    // Tentukan koordinat awal sebelum cetak baris untuk auto-wrap atau penanganan string panjang
    $start_x = $pdf->GetX();
    $start_y = $pdf->GetY();
    
    // Karena FPDF Cell tidak otomatis wrap, kita batasi string item dengan Substr/MultiCell jika terlalu panjang atau gunakan MultiCell
    // Agar baris tabel tetap sejajar, kita bisa gunakan Cell biasa tetapi potong teks jika melebihi lebar, atau gunakan MultiCell.
    // Di sini kita potong agar rapi di satu baris atau gunakan MultiCell khusus.
    // Untuk kesederhanaan dan kerapian tabel satu baris, kita batasi panjang string item pembelian:
    if (strlen($items_text) > 28) {
        $items_text = substr($items_text, 0, 25) . '...';
    }
    
    $pdf->Cell(8, 7, $no++, 1, 0, 'C');
    $pdf->Cell(25, 7, $trx_code, 1, 0, 'C');
    $pdf->Cell(30, 7, $tanggal, 1, 0, 'C');
    // Potong nama pelanggan jika terlalu panjang
    $pdf->Cell(35, 7, strlen($pelanggan) > 18 ? substr($pelanggan, 0, 16) . '..' : $pelanggan, 1, 0, 'L');
    $pdf->Cell(18, 7, $metode, 1, 0, 'C');
    $pdf->Cell(49, 7, $items_text, 1, 0, 'L');
    $pdf->Cell(25, 7, $total, 1, 1, 'R');
}

if (mysqli_num_rows($result_transaksi) == 0) {
    $pdf->Cell(0, 10, 'Tidak ada transaksi terdaftar untuk periode ini.', 1, 1, 'C');
}

// Tanda Tangan CEO
$pdf->Ln(15);
$pdf->Cell(120); // Spacer ke kanan
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 5, 'Rantepao, ' . date('d M Y'), 0, 1, 'C');

$pdf->Cell(120);
$pdf->Cell(70, 5, 'CEO ATK Rara', 0, 1, 'C');

$pdf->Ln(20); // Space untuk TTD

$pdf->Cell(120);
$pdf->SetFont('Arial', 'BU', 10);
$pdf->Cell(70, 5, 'Andri Rara Matandung S.Kom', 0, 1, 'C');

// Output PDF ke browser
$pdf->Output('I', 'Laporan_Penjualan_' . str_replace(' ', '_', $periode_text) . '.pdf');
?>
