<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true &&
    isset($_SESSION['username']) && isset($_GET['prod']) && isset($_GET['act'])) {
    $conn = connect();

    $prod = clear_input($_GET["prod"]);
    $activado = clear_input($_GET["act"]);

    $stmt = $conn -> prepare("UPDATE dgrupo SET activado = ? WHERE cod = ?");
    $stmt->bind_param('ii', $activado, $prod);
    $stmt->execute();
    echo '<script>window.location.href = "llista_items.php?prod='.$prod.'";</script>';
}
exit();
?>
