<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Resum</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $fecha = $_GET["fecha"];
    $conn = connect();

    // get productors
    $stmt = $conn -> prepare("SELECT cod, descrip FROM dgrupo");
    $stmt->execute();
    $prods = $stmt->get_result();
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Comanda <?php echo $fecha; ?></h1>
        <p>Els totals no inclouen alguns productes de preu variable</p>
        <a class="btn btn-link" href="resumlist.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<div class="container">
    <?php while ($r = mysqli_fetch_array($prods)) {
        $stmt = $conn -> prepare("SELECT * FROM resumen WHERE fecha = ? AND cgrupo = ?");
        $stmt->bind_param('si', $fecha, $r["cod"]);
        $stmt->execute();
        $data = $stmt->get_result();

        $stmt = $conn -> prepare("SELECT fecha, cgrupo, SUM(t) AS total FROM resumen
        WHERE fecha = ? AND cgrupo = ?
        GROUP BY fecha, cgrupo");
        $stmt->bind_param('si', $fecha, $r["cod"]);
        $stmt->execute();
        $total = $stmt->get_result();
        $nrows = $total->num_rows;
        $show = false;
        if ($nrows > 0) {
            $show = true;
            while($t = $total->fetch_assoc()) {
                $subtotal = ($t["total"]==NULL ? '' : number_format($t["total"], 2, ",", ".")."€");
            }
            ?>

            <h3> <?php echo $r["descrip"].": ".$subtotal; ?> </h3>
            <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Producte</th>
                        <th><div class='text-center'>Quantitat</div></th>
                        <th><div class='text-right'>Preu</div></th>
                        <th><div class='text-right'>Total</div></th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($d = mysqli_fetch_array($data)) { ?>
                    <?php
                    $preu = ($d["precio"]==NULL ? '' : number_format($d["precio"], 2, ",", ".")."€");
                    $tot = ($d["t"]==NULL ? '' : number_format($d["t"], 2, ",", ".")."€");
                    ?>
                    <tr>
                        <td><?php echo $d["item"]; ?></td>
                        <td><div class='text-center'><?php echo $d["n"]; ?></div></td>
                        <td><div class='text-right'><?php echo $preu; ?></div></td>
                        <td><div class='text-right'><?php echo $tot; ?></div></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
    <?php    }
    } ?>
</div>

<?php
    $conn->close();
} else {
    header("Location: index.php");
}?>

</body>
</html>
