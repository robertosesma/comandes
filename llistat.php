<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Llistat</title>
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

    // get UCs
    $stmt = $conn -> prepare("SELECT uf, descrip FROM comanda WHERE fecha =? GROUP BY uf");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $ucs = $stmt->get_result();
    $nuc = $ucs->num_rows;

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
        <h3>Llistat per Unitat de Convivència</h3>
        <h4><?php echo $nuc; ?> participants</h4>
        <p>Els totals no inclouen alguns productes de preu variable</p>
        <a class="btn btn-link" href="resumlist.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<div class="container">
    <?php
    $count = 1;
    while ($r = mysqli_fetch_array($ucs)) {
        $uf = $r["uf"];
        $uctotal = gettotal($conn,$uf,$fecha);
        echo "<h4>".$count.". ".$r["descrip"].": ".$uctotal."</h4>";

        $stmt = $conn -> prepare("SELECT * FROM comanda WHERE fecha =? AND uf=?");
        $stmt->bind_param('si', $fecha, $uf);
        $stmt->execute();
        $items = $stmt->get_result();
        $nitems = $items->num_rows;
        $j = 1; ?>
        <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Productor</th>
                    <th>Producte</th>
                    <th><div class='text-center'>Quantitat</div></th>
                    <th><div class='text-right'>Preu</div></th>
                    <th><div class='text-right'>Total</div></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $g0 = 0;
            while ($i = mysqli_fetch_array($items)) {
                $g = $i["cgrupo"];
                if (($g <> $g0 && $g0>0)) {
                    $subtotal = getsubtotal($conn,$uf,$fecha,$g0);?>
                    <tr>
                        <td> </td>
                        <td> </td>
                        <td> </div></td>
                        <td><div class='text-right'>Subtotal</div></td>
                        <td><div class='text-right font-weight-bold'><?php echo $subtotal; ?></div></td>
                    </tr>
                <?php }
                $g0 = $g;

                $preu = ($i["precio"]==NULL ? '' : number_format($i["precio"], 2, ",", ".")."€");
                $tot = ($i["total"]==NULL ? '' : number_format($i["total"], 2, ",", ".")."€"); ?>
                <tr>
                    <td><?php echo $i["dgrupo"]; ?></td>
                    <td><?php echo $i["item"]; ?></td>
                    <td><div class='text-center'><?php echo $i["n"]; ?></div></td>
                    <td><div class='text-right'><?php echo $preu; ?></div></td>
                    <td><div class='text-right'><?php echo $tot; ?></div></td>
                </tr>
            <?php
            if ($j == $nitems) {
                $subtotal = getsubtotal($conn,$uf,$fecha,$g); ?>
                <tr>
                    <td> </td>
                    <td> </td>
                    <td> </div></td>
                    <td><div class='text-right'>Subtotal</div></td>
                    <td><div class='text-right font-weight-bold'><?php echo $subtotal; ?></div></td>
                </tr>
            <?php }
                $j = $j + 1;
            } ?>
            </tbody>
        </table>

        <?php
        $count = $count + 1;
    } ?>
</div>


<?php
    $conn->close();
} else {
    header("Location: index.php");
}?>

</body>
</html>
