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

    // obtenir llista d'UC amb membres
    $stmt = $conn -> prepare("SELECT u.uf, u.descrip, u.tresorer
        FROM uf u
        LEFT JOIN membres m ON u.uf = m.uf
        WHERE u.uf < 10000 AND u.act = 1
        GROUP BY u.uf
        HAVING COUNT(m.uf)>0
        ORDER BY u.descrip;");
    $stmt->execute();
    $dades = $stmt->get_result();
    $ndades = $dades->num_rows;

    // obtenir llista d'UC sense membres
    $stmt = $conn -> prepare("SELECT u.uf, u.descrip, u.tresorer
        FROM uf u
        LEFT JOIN membres m ON u.uf = m.uf
        WHERE u.uf < 10000 AND u.act = 1
        GROUP BY u.uf
        HAVING COUNT(m.uf)=0
        ORDER BY u.descrip;");
    $stmt->execute();
    $sense = $stmt->get_result();
    $nsense = $sense->num_rows;

    $nuf = $ndades + $nsense;
    if ($nuf==0) {
        $ok = false;
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Llistat Unitats de Convivència</h1>
        <h2>Total: <?php echo $nuf; ?></h2>
        <h3>Sense membres: <?php echo $nsense; ?></h3>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <div class="container">
    <ol>
    <?php while ($r = mysqli_fetch_array($dades)) {
        $uf = $r["uf"];

        // és contacte de productora?
        $stmt = $conn -> prepare('SELECT * FROM dgrupo WHERE uf = ?');
        $stmt->bind_param('i', $uf);
        $stmt->execute();
        $res = $stmt->get_result();
        $prod = "";
        if ($res->num_rows>0) {
            $p = $res->fetch_assoc();
            $prod = " - Contacte ".$p["descrip"];
        }
        $res->free();
        echo '<div class="row mb-4">';
        echo "<li><h3>".$r["descrip"]." <small>".$prod."</small></h3>";
        if ($r["tresorer"]) { echo "<h5>Tresorera</h5>"; }

        // llistat membres
        // obtenir els membres de la UC
        $stmt = $conn -> prepare("SELECT * FROM membres WHERE uf=? ORDER BY ape, nom");
        $stmt->bind_param('i', $uf);
        $stmt->execute();
        $res = $stmt->get_result(); ?>
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
        <?php $res->free();
        echo "</div>";
    } ?>
    </ol>
    </div>

    <div class="container">
    <h2>UC sense membres:</h2>
    <ul>
    <?php while ($r = mysqli_fetch_array($sense)) {
        echo "<li><h4>".$r["descrip"]."</h3>";
    } ?>
    </ul>
    </div>
</div>

<?php
    $dades->free();
    $sense->free();
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
