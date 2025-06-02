<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Obertura</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
$mobErr = "";
$err = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && 
    isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
    $conn = connect();

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $fecha = clear_input($_GET["fecha"]);

        // obtenir les dades d'obertura
        $stmt = $conn -> prepare("SELECT * FROM calendari WHERE fecha = ?");
        $stmt->bind_param('s',$fecha);
        $stmt->execute();
        $res = $stmt->get_result();
        $d = $res->fetch_assoc();
        echo $d["uc1"]."  ".$d["uc2"];

        // obtenir les dades de les UF
        $stmt = $conn -> prepare('SELECT * FROM uf WHERE act=1 AND uf < 1000 ORDER BY descrip');
        $stmt->execute();
        $duf1 = $stmt->get_result();

        $stmt = $conn -> prepare('SELECT * FROM uf WHERE act=1 AND uf < 1000 ORDER BY descrip');
        $stmt->execute();
        $duf2 = $stmt->get_result();
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Obertura <?php echo $fecha?></h2>
        <a class="btn btn-link" href="calendari.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group" style="margin-top:10px">
            <label for="uf1">Unitat de convivència 1:</label>
            <select name="uf1" class="custom-select">
                <?php while ($r1 = mysqli_fetch_array($duf1)) {
                    echo "<option ".($r1["uf"]==$d["uc1"] ? "selected" : "")." value=".$r1["uf"].">".$r1["descrip"]."</option>";
                } ?>
            </select>
        </div>

        <div class="form-group" style="margin-top:10px">
            <label for="uf2">Unitat de convivència 2:</label>
            <select name="uf2" class="custom-select">
                <?php while ($r2 = mysqli_fetch_array($duf2)) {
                    echo "<option ".($r2["uf"]==$d["uc2"] ? "selected" : "")." value=".$r2["uf"].">".$r2["descrip"]."</option>";
                } ?>
            </select>
        </div>
    </form>
</div>

<?php
    $res->free();
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
