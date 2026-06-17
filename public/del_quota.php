<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    if (isset($_GET['uf']) && isset($_GET['year'])) {
        $uf = clear_input($_GET["uf"]);
        $year = clear_input($_GET["year"]);
        // esborrar la quota
        $conn = connect();
        $stmt = $conn -> prepare("DELETE FROM quotes WHERE uf=? AND year=?");
        $stmt->bind_param('ii',$uf,$year);
        $stmt->execute();
        echo '<script>window.location.href = "quotes.php?year='.$year.'";</script>';
    }
}
exit();
?>
