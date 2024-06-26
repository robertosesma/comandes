<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Històric</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $uf = $_SESSION['username'];
    $conn = connect();
    $descrip = getdescrip($conn,$uf);
    $horari_act = ishorari_act($conn);

    // obtenir llistat de comandes
    $stmt = $conn -> prepare("SELECT fecha FROM comandes GROUP BY fecha ORDER BY fecha DESC");
    $stmt->execute();

    $dades = $stmt->get_result();
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="jumbotron">
        <h1>Històric de Comandes</h1>
        <h2>UC: <?php echo $descrip; ?></h2>
        <p><a class="btn btn-link" href="descarregar_excel.php">Descarregar Excel</a>
        <a class="btn btn-link" href="resum_any.php">Resum anual</a></p>
        <p><a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a></p>
    </div>

    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
        <tbody>
        <?php while ($r = mysqli_fetch_array($dades)) {
            $fecha = $r["fecha"];
            $com = 'comanda.php?&fecha='.$fecha;
            $resum = 'resum.php?&fecha='.$fecha;
            $horari = 'horari.php?&fecha='.$fecha;
            $llistat = 'llistat.php?&fecha='.$fecha;
            $pa = 'pa.php?&fecha='.$fecha;
            // la comanda actual té data superior a la data actual, i només s'ha de veure si la comanda actual està tancada
            $fecha_dt = new DateTime($fecha);
            $today_dt = new DateTime(date("Y-m-d"));
            if ($fecha_dt <= $today_dt || !isopen($conn)) { ?>
                <tr>
                    <td><?php echo "<a href='".$com."'>".$fecha."</a>"; ?></td>
                    <td><?php echo "<a href='".$resum."'>Resum</a>"; ?></td>
                    <?php if ($horari_act) { echo "<td><a href='".$horari."'>Horari</a></td>"; } ?>
                    <td><?php echo "<a href='".$llistat."'>Llistat</a>"; ?></td>
                    <td><?php echo "<a href='".$pa."'>Llistat PA</a>"; ?></td>
                </tr>
        <?php   }
            } ?>
        </tbody>
    </table>
</div>

<?php $conn->close();

} else {
    header("Location: logout.php");
}?>

</body>
</html>
