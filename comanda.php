<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Detall comanda</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $uf = $_SESSION['username'];
    $fecha = $_GET["fecha"];

    $conn = connect();
    $descrip = getdescrip($conn,$uf);

    // get UC comandes
    $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $com = $stmt->get_result();
    $nrows = $com->num_rows;
    if ($nrows > 0) {
        $uctotal = gettotal($conn,$uf,$fecha);
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Comanda <?php echo $fecha; ?></h1>
        <h2>UC: <?php echo $descrip; ?></h2>
        <h3>Total: <?php echo $uctotal; ?></h3>
        <p>Aquest total no inclou alguns productes de preu variable</p>
        <a class="btn btn-link" href="history.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<?php
    if ($nrows > 0) {
        $open = false;
        include 'comanda_tbl.php';
    } else { ?>
        <div class="container">
            <h1 class="text-warning">No hi ha productes</h1>
        </div>
<?php }
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
