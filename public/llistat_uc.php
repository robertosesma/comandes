<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Llistat UC</title>
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

    // obtenir # UC amb membres
    $stmt = $conn -> prepare("SELECT u.uf, u.descrip, u.tresorer, u.calendari, u.obertura
        FROM uf u
        LEFT JOIN membres m ON u.uf = m.uf
        WHERE u.uf < 10000 AND u.act = 1
        GROUP BY u.uf
        HAVING COUNT(m.uf)>0
        ORDER BY u.descrip;");
    $stmt->execute();
    $d = $stmt->get_result();
    $ndades = $d->num_rows;

    // obtenir # UC sense membres
    $stmt = $conn -> prepare("SELECT u.uf, u.descrip, u.tresorer, u.calendari, u.obertura
        FROM uf u
        LEFT JOIN membres m ON u.uf = m.uf
        WHERE u.uf < 10000 AND u.act = 1
        GROUP BY u.uf
        HAVING COUNT(m.uf)=0
        ORDER BY u.descrip;");
    $stmt->execute();
    $d = $stmt->get_result();
    $nsense = $d->num_rows;

    $nuf = $ndades + $nsense;
    if ($nuf==0) {
        $ok = false;
    } else {
        // obtenir # UC fan obertura
        $stmt = $conn -> prepare("SELECT COUNT(*) AS n FROM uf WHERE uf < 10000 AND act = 1 AND obertura = 1;");
        $stmt->execute();
        $d = $stmt->get_result();
        $r = mysqli_fetch_array($d);
        $obren = $r['n'];

        // obtenir # UC fan obertura
        $stmt = $conn -> prepare("SELECT COUNT(*) AS n FROM uf WHERE uf < 10000 AND act = 1 AND obertura = 0;");
        $stmt->execute();
        $d = $stmt->get_result();
        $r = mysqli_fetch_array($d);
        $no_obren = $r['n'];
        $d->free();

        // obtenir dades UC actives
        $stmt = $conn -> prepare("SELECT u.uf, u.descrip, u.tresorer, u.calendari, u.obertura
            FROM uf u
            WHERE u.uf < 10000 AND u.act = 1
            ORDER BY u.descrip;");
        $stmt->execute();
        $dades = $stmt->get_result();
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Llistat Unitats de Convivència</h1>
        <h2>Total: <?php echo $nuf; ?> actives</h2>
        <h3><?php echo $obren; ?> fan obertures, <?php echo $no_obren; ?> no en fan</h3>
        <h4><?php echo $ndades; ?> amb membres, <?php echo $nsense; ?> sense membres definits</h4>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <div class="container">
    <ol>
    <?php while ($r = mysqli_fetch_array($dades)) {
        $uf = $r["uf"];

        $c = "";
        // és contacte de productora?
        $stmt = $conn -> prepare('SELECT * FROM dgrupo WHERE uf = ?');
        $stmt->bind_param('i', $uf);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows>0) {
            $p = $res->fetch_assoc();
            $c = " - Contacte ".$p["descrip"];
        }
        $res->free();
        // tresorera?
        if ($r["tresorer"]) $c = " - Tresorera";
        // calendari?
        if ($r["calendari"]) $c = " - Calendari";
        // no fa obertures?
        if (!$r["obertura"]) $c = " - NO fa obertures";

        echo '<div class="row mb-4">';
        echo "<li><h3>".$r["descrip"]." <small>".$c."</small></h3>";

        // llistat membres
        // obtenir els membres de la UC
        $stmt = $conn -> prepare("SELECT * FROM membres WHERE uf=? ORDER BY ape, nom");
        $stmt->bind_param('i', $uf);
        $stmt->execute();
        $res = $stmt->get_result(); 
        if ($res->num_rows > 0) { ?>
            <table cellpadding="0" cellspacing="0" border="0" class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Mòvil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($m = mysqli_fetch_array($res)) {
                        $n = $m["nom"]." ".$m["ape"];
                        $tel = $m["tel"];
                        $tel = substr($tel,0,3)."****".substr($tel,-2); ?>
                        <tr>
                            <td><?php echo $n; ?></td>
                            <td><?php echo $tel; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else {
            echo "<h4>SENSE membres definits<h3>";
        }
        $res->free();
        echo "</div>";
    } ?>
    </ol>
    </div>
</div>

<div class="container">
<h3>Contactes de productora / Tresoreria / Calendari</h3>
<ol>
<?php         
    // obtenir les UC contactes de productora o tresoreres
    $stmt = $conn -> prepare("SELECT u.uf, u.descrip AS duc, u.tresorer, u.calendari, d.descrip as prod FROM uf u
                        LEFT JOIN dgrupo d ON u.uf = d.uf
                        WHERE u.uf < 10000 AND (d.uf IS NOT NULL OR u.tresorer = 1 OR u.calendari = 1)");
    $stmt->execute();
    $res = $stmt->get_result(); 
    while ($r = mysqli_fetch_array($res)) {
        $c = ($r["tresorer"] ? "Tresorera" : ($r["calendari"] ? "Calendari" : "Contacte de ".$r["prod"]));
        echo "<li><h4>".$r["duc"].": <small>".$c."</small></h4>";
    }
?>
</ol>
</div>

<div class="container mt-5">
<h3>NO fan obertures</h3>
<ol>
<?php         
    // obtenir les UC que no fan obertures
    $stmt = $conn -> prepare("SELECT uf, descrip FROM uf WHERE uf < 10000 AND act = 1 AND obertura = 0");
    $stmt->execute();
    $res = $stmt->get_result(); 
    while ($r = mysqli_fetch_array($res)) {
        echo "<li><h4>".$r["descrip"]."</h4>";
    }
?>
</ol>
</div>

<div class="container mt-5">
<h3>SENSE membres definits</h3>
<ol>
<?php         
    // obtenir les UC que no tenen membres definits
    $stmt = $conn -> prepare("SELECT u.uf, u.descrip
        FROM uf u
        LEFT JOIN membres m ON u.uf = m.uf
        WHERE u.uf < 10000 AND u.act = 1
        GROUP BY u.uf
        HAVING COUNT(m.uf)=0");
    $stmt->execute();
    $res = $stmt->get_result(); 
    while ($r = mysqli_fetch_array($res)) {
        echo "<li><h4>".$r["descrip"]."</h4>";
    }
?>
</ol>
</div>

<?php
    $dades->free();
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
