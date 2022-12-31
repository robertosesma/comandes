<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Resum anual</title>
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

    // obtenim els anys disponibles
    $stmt = $conn -> prepare("SELECT DISTINCT YEAR(fecha) as year FROM comandes");
    $stmt->execute();
    $years = $stmt->get_result();
    $nyears = $years->num_rows;

    if ($nyears > 0) {
        if (isset($_GET['y'])) {
            $year = clear_input($_GET["y"]);

            // obtenir total comandes i UC
            $stmt = $conn -> prepare("SELECT COUNT(DISTINCT fecha) as ncomandes, COUNT(DISTINCT uf) as nuf
                FROM comandes WHERE YEAR(fecha) = ?");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $totals = $stmt->get_result();
            $t = $totals->fetch_assoc();

            // obtenir dades comandes per UC
            $stmt = $conn -> prepare("SELECT u.uf, u.descrip, COUNT(DISTINCT i.fecha) AS n,
                SUM(i.total) AS total, MAX(i.fecha) AS last, MAX(u.act) AS act
                FROM uf u
                LEFT JOIN (
                    SELECT it.uf, it.fecha, (it.n * d.precio) AS total
                    FROM items it
                	LEFT JOIN dtipo d ON it.tipo = d.tipo
                    WHERE year(fecha) = ?) AS i ON u.uf = i.uf
                WHERE u.uf < 10000
                GROUP BY u.uf
                ORDER BY n DESC, act DESC");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $ucs = $stmt->get_result();

            // obtenir total anual per productora
            $stmt = $conn -> prepare("SELECT t.grupo, t.descrip, SUM(i.n * t.precio) AS total
                FROM items i
                LEFT JOIN (
                    SELECT d.grupo, d.tipo, g.descrip, d.precio
                    FROM dtipo d
                    LEFT JOIN dgrupo g ON d.grupo = g.cod) AS t ON i.tipo = t.tipo
                WHERE year(i.fecha) = ?
                GROUP BY t.grupo
                ORDER BY total DESC");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $prod = $stmt->get_result();

            // obtenir productores per any
            $stmt = $conn -> prepare("SELECT grupo, descrip FROM totales WHERE year = ? GROUP BY grupo ORDER BY grupo");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $res = $stmt->get_result();
            $ps = array();
            $ds = array();
            while ($r = $res->fetch_assoc()) {
                $ps[] = $r["grupo"];
                $ds[] = $r["descrip"];
            }

            // obtenir total consum anual
            $stmt = $conn -> prepare("SELECT SUM(i.n * t.precio) AS total
                FROM items i
                LEFT JOIN (
                    SELECT d.grupo, d.tipo, d.precio
                    FROM dtipo d
                    LEFT JOIN dgrupo g ON d.grupo = g.cod) AS t ON i.tipo = t.tipo
                WHERE year(i.fecha) = ?");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $data = $stmt->get_result();
            $productores = $data->fetch_assoc();
            $total = $productores["total"];

            // obtenir nuc i total per comanda
            $stmt = $conn -> prepare("SELECT i.fecha, COUNT(DISTINCT i.uf) as nuf, SUM(i.n * d.precio) as total
                FROM items i LEFT JOIN dtipo d ON d.tipo = i.tipo
                WHERE year(i.fecha) = ?
                GROUP BY i.fecha ORDER BY i.fecha");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $comandes = $stmt->get_result();

            // obtenir mitjanes
            $stmt = $conn -> prepare("SELECT AVG(t.nuf) as nuf, AVG(t.total) as total
                FROM (
                	SELECT i.fecha, COUNT(DISTINCT i.uf) as nuf, SUM(i.n * d.precio) as total
                	FROM items i
                	LEFT JOIN dtipo d ON d.tipo = i.tipo
                	WHERE year(i.fecha) = ?
                	GROUP BY i.fecha
                	ORDER BY i.fecha ) AS t
                GROUP BY year(t.fecha)");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $res = $stmt->get_result();
            $r = $res->fetch_assoc();
            $avguc = $r["nuf"];
            $avgtot = $r["total"];
        } else {
            $year = 0;
        }
    } else {
        $ok = false;
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border" id="inicio">
        <h1><?php echo ($year>0 ? "Resum any: ".$year : "Resum anual"); ?></h1>
        <p><?php while($r = $years->fetch_assoc()) {
            echo '<a class="btn btn-link" href="resum_any.php?y='.$r["year"].'">'.$r["year"].'</a>';
        } ?></p>
        <p><a class="btn btn-link" href="history.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a></p>
    </div>
</div>

<?php if ($year>0) { ?>
<div class="container">
    <ul>
        <li><a href="#uc" class="btn">Nº de comandes per UC</a></li>
        <li><a href="#prod" class="btn">Totals per mesos i productora</a></li>
        <li><a href="#comandes" class="btn">Nº UC i consum per comanda</a></li>
        <?php $count = 0;
        foreach ($ds as $d) {
            echo '<li><a href="#prod'.$ps[$count].'" class="btn">Productes '.$d.'</a></li>';
            $count = $count + 1;
        } ?>
    </ul>
    <p>Totals aproximats (hi ha productes amb preu variable), calculats amb preus actuals.<br>
    Les files en gris es corresponen a UC o productes desactivats.</p>

    <div class="container" id="uc">
        <h3>Nº de comandes per UC <small>(<?php echo $year; ?>)</small></h3>
        <h4><?php echo $t["ncomandes"]." comandes, han participat ".$t["nuf"]." UC"; ?></h4>

        <table cellpadding="0" cellspacing="0" border="0" class="table table-sm">
            <caption><a href="#inicio" class="btn">Inici</a></caption>
            <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col"><div class='text-center'>UC</th>
                    <th scope="col"><div class='text-center'>Comandes</div></th>
                    <th scope="col"><div class='text-center'>Total (€)</div></th>
                    <th scope="col"><div class='text-center'>Última</div></th>
                </tr>
            </thead>
            <tbody>
            <?php $count = 1;
            while ($r = mysqli_fetch_array($ucs)) {
                echo ($r["act"]==1 ? "<tr>" : "<tr class=table-secondary>");
                echo "<td><strong>".$count."</strong></td>";
                echo "<td><div class='text-center'>".$r["descrip"].($r["act"]==0 ? "<small> (desactivada)</small>" : "")."</td>";
                echo "<td><div class='text-center'>".$r["n"]."</td>";
                echo "<td><div class='text-center'>".($r["n"]>0 ? getascurr($r["total"],"") : "")."</td>";
                echo "<td><div class='text-center'>".$r["last"]."</td>";
                echo "</tr>";
                $count = $count + 1;
            } ?>
            </tbody>
        </table>
    </div>

    <div class="container" id="prod">
        <h3>Totals per mesos i productora <small>(<?php echo $year; ?>)</small></h3>

        <table cellpadding="0" cellspacing="0" border="0" class="table table-sm">
            <caption><a href="#inicio" class="btn">Inici</a></caption>
            <thead>
                <tr>
                    <th scope="col"><div class='text-left'>Mes</th>
                    <?php $sel = "";
                    foreach ($ps as $p) {
                        $sel = $sel.', MAX(CASE WHEN grupo = '.$p.' THEN descrip END) "prod'.$p.'"';
                    }
                    $stmt = $conn -> prepare("SELECT mes".$sel." FROM totales WHERE year = ? GROUP BY year");
                    $stmt->bind_param('i', $year);
                    $stmt->execute();
                    $productores = $stmt->get_result();
                    $r = mysqli_fetch_array($productores);
                    foreach ($ps as $p) {
                        $c = $r["prod".$p];
                        if (strpos($c,"(")>0) $c = substr($c,0,strpos($c,"("));
                        echo "<th scope='col'><div class='text-center'>".$c."</th>";
                    } ?>
                    <th scope="col"><div class='text-center'>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php $sel = "";
            foreach ($ps as $p) {
                $sel = $sel.', MAX(CASE WHEN grupo = '.$p.' THEN total END) "total'.$p.'"';
            }
            $stmt = $conn -> prepare("SELECT mes".$sel." FROM totales WHERE year = ? GROUP BY mes ORDER BY mes");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = mysqli_fetch_array($res)) {
                echo "<tr>";
                echo "<td><div class='text-left'>".getmes($r["mes"])."</td>";
                // calcular total mes
                $tot = 0;
                foreach ($ps as $p) {
                    $tot = $tot + $r["total".$p];
                }
                foreach ($ps as $p) {
                    $pc = number_format(100 * $r["total".$p]/$tot, 1, ",", ".");
                    $v = getascurr($r["total".$p],"€");
                    echo "<td><div class='text-center'>".(strlen($v)>0 ? $v." (".$pc."%)" : "--")."</td>";
                }
                echo "<td><div class='text-center'>".getascurr($tot,"€")."</td>";
                echo "</tr>";
            }
            echo "<tr>";
            echo "<td><div class='text-left'><strong>TOTAL</strong></td>";
            while ($r = mysqli_fetch_array($prod)) {
                $pc = number_format(100 * $r["total"]/$total, 1, ",", ".");
                echo "<td><div class='text-center'><strong>".getascurr($r["total"],"€")." (".$pc."%)"."</strong></td>";
            }
            echo "<td><div class='text-center'><strong>".getascurr($total,"€")."</strong></td>";
            echo "</tr>";
            ?>

            </tbody>
        </table>
    </div>

    <div class="container" id="comandes">
        <h3>Nº UC i consum per comanda <small>(<?php echo $year; ?>)</small></h3>
        <h4>Mitjana per comanda: <?php echo number_format($avguc,1,",","."); ?> UC - <?php echo getascurr($avgtot,"€"); ?></h4>

        <table cellpadding="0" cellspacing="0" border="0" class="table table-sm">
            <caption><a href="#inicio" class="btn">Inici</a></caption>
            <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col"><div class='text-left'>Data</th>
                    <th scope="col"><div class='text-center'>Nº UC</th>
                    <th scope="col"><div class='text-center'>Total (€)</div></th>
                </tr>
            </thead>
            <tbody>
            <?php $count = 1;
            while ($r = mysqli_fetch_array($comandes)) {
                echo "<td><strong>".$count."</strong></td>";
                echo "<td>".$r["fecha"]."</td>";
                echo "<td><div class='text-center'>".$r["nuf"]."</td>";
                echo "<td><div class='text-center'>".getascurr($r["total"],"")."</td>";
                echo "</tr>";
                $count = $count+1;
            } ?>
            </tbody>
        </table>
    </div>

    <?php $count = 0;
    foreach ($ds as $d) {
        $p = $ps[$count];
        $stmt = $conn -> prepare("SELECT d.tipo, d.descrip, d.grupo, SUM(i.n) as n, d.desactivado
            FROM dtipo d
            LEFT JOIN (
            	SELECT tipo, n
            	FROM items
            	WHERE year(fecha)=?) AS i ON d.tipo = i.tipo
            WHERE d.grupo = ?
            GROUP BY d.tipo
            ORDER BY n DESC, d.desactivado");
        $stmt->bind_param('ii', $year, $p);
        $stmt->execute();
        $res = $stmt->get_result();
        echo '<div class="container" id="prod'.$p.'">';
        $count = $count+1;
    ?>
        <h3>Productes <?php echo $d; ?> <small><?php echo $year; ?></small></h3>

        <table cellpadding="0" cellspacing="0" border="0" class="table table-sm">
            <caption><a href="#inicio" class="btn">Inici</a></caption>
            <thead>
                <tr>
                    <th scope="col">Producte</th>
                    <th scope="col">Vegades demanat</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($r = mysqli_fetch_array($res)) {
                echo ($r["desactivado"]==0 ? "<tr>" : "<tr class=table-secondary>");
                echo "<td>".$r["descrip"]."</td>";
                echo "<td>".($r["n"]==NULL ? 0 : $r["n"])."</td>";
                echo "</tr>";
            } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>
<?php } ?>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
