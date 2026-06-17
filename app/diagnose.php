<?php
include "config.php";
$result = mysqli_query($conn, "SHOW COLUMNS FROM transaksi");
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}
echo "<hr>";
$result = mysqli_query($conn, "SELECT DATABASE()");
$row = mysqli_fetch_row($result);
echo "Current DB: " . $row[0];
?>
