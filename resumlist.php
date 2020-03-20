<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Resum i Llistats</title>
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

    // get UC comandes
    $stmt = $conn -> prepare("SELECT * FROM comandes GROUP BY fecha ORDER BY fecha DESC");
    $stmt->execute();
    $com = $stmt->get_result();
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="jumbotron">
        <h1>Resum i Llistats</h1>
        <p>Cooperativa de Consum i Resistència Terrassa</p>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered" id="comandes">
        <tbody>
        <?php while ($row = mysqli_fetch_array($com)) {
            // data actual
            $today_dt = new DateTime(date("Y-m-d"));
            // data de la comanda
            $fecha = $row["fecha"];
            $fecha_dt = new DateTime($fecha);
            // dissabte previ a la comanda
            $sat_dt = new DateTime($fecha);
            $sat_dt->sub(new DateInterval('P3D'));
            // la comanda surt al llistat si és anterior a la data actual o
            // si la data actual es posterior o igual al dissabte previ a la comanda
            if (($fecha_dt < $today_dt) || ($today_dt < $fecha_dt && $today_dt >= $sat_dt)) {
                $resum = 'resum.php?&fecha='.$fecha;
                $detall = 'llistat.php?&fecha='.$fecha;
                $pa = 'pa.php?&fecha='.$fecha; ?>
                <tr>
                    <td><?php echo $fecha; ?></td>
                    <td><?php echo "<a href='".$resum."'>Resum</a>"; ?></td>
                    <td><?php echo "<a href='".$detall."'>Llistat</a>"; ?></td>
                    <td><?php echo "<a href='".$pa."'>Llistat PA</a>"; ?></td>
                </tr>
        <?php }
        } ?>
        </tbody>
    </tbody>
    </table>
</div>
<?php $conn->close(); ?>
<?php } else {
    header("Location: index.php");
}?>

</body>
</html>
