<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Nova comanda</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $conn = connect();
    $uf = $_SESSION['username'];
    $descrip = getdescrip($conn,$uf);

    $fecha = getnext($conn);
    $_SESSION["fecha"] = $fecha;
    $open = isopen($conn);

    // get UC comanda
    $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $com = $stmt->get_result();

    $uctotal = gettotal($conn,$uf,$fecha);
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container pt-3 my-3 border">
        <h1>Comanda <?php echo $fecha; ?></h1>
        <h2>UC: <?php echo $descrip; ?></h2>
        <h3>Total: <?php echo $uctotal; ?></h3>
        <p>Aquest total no inclou alguns productes de preu variable</p>
        <?php if (!$open) {
            echo "<h2 class='text-warning'>Comanda tancada</h2>";
        } else {
            echo "<a class='btn btn-link' href='new_producte.php'>Afegir producte</a>";
        }?>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<?php
    include 'comanda_tbl.php';

    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
