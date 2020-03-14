<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Comandes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_GET['uf'])) {
    $uf = $_GET["uf"];
    $conn = connect();
    $descrip = getdescrip($conn,$uf);

    // get UC comandes
    $stmt = $conn -> prepare("SELECT * FROM comandes WHERE uf = ? ORDER BY fecha DESC");
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $com = $stmt->get_result();
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div class="container">
    <?php $url = 'init.php?uf='.$uf; ?>
    <div class="jumbotron">
        <h1>Històric de Comandes</h1>
        <p>Cooperativa de Consum i Resistència Terrassa</p>
        <h2>Unitat de Convivència: <?php echo $descrip; ?></h2>
        <a class="btn btn-link" href=<?php echo $url; ?>>Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered" id="comandes">
        <tbody>
        <?php while ($row = mysqli_fetch_array($com)) {
            $fecha = $row["fecha"];
            $url = 'comanda.php?uf='.$uf.'&fecha='.$fecha; ?>
            <tr>
                <td><?php echo "<a href='".$url."'>".$fecha."</a>"; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); ?>
<?php } else {
    header("Location: index.php");
}?>

</body>
</html>
