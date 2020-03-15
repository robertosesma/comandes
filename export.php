<?php
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=ejemplo.csv");
header("Pragma: no-cache");
header("Expires: 0");

echo "V1;V2\n";

print("1;2\n");

?>
