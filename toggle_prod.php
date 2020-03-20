<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true &&
    isset($_SESSION['username']) && isset($_GET['grupo']) && isset($_GET['act'])) {
    $grupo = clear_input($_GET["grupo"]);
    $activado = clear_input($_GET["act"]);
    $conn = connect();

    $stmt = $conn -> prepare("UPDATE dgrupo SET activado = ? WHERE cod = ?");
    $stmt->bind_param('ii', $activado, $grupo);
    $stmt->execute();
    echo '<script>window.location.href = "edit_items.php?grupo='.$grupo.'";</script>';
}
exit();
?>
