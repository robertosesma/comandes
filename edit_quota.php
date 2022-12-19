<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Quota</title>
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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $add = clear_input($_POST["add"]);
        $uf = clear_input($_POST["uf"]);
        $year = clear_input($_POST["year"]);
        $quota = clear_input($_POST["quota"]);
        $pendent = clear_input($_POST["filtre"]);
        if ($add==1) {
            // pagar nova quota
            $stmt = $conn -> prepare("INSERT INTO quotes (uf,year,quota) VALUES (?,?,?)");
            $stmt->bind_param('iid', $uf, $year, $quota);
        } else {
            // editar quota existent
            $stmt = $conn -> prepare("UPDATE quotes SET quota=? WHERE uf=? AND year=?");
            $stmt->bind_param('dii', $quota, $uf, $year);
        }
        $stmt->execute();
        header("Location: quotes.php?year=".$year.($pendent==0 ? "" : "&pendent=1"));
    } else {
        $uf = clear_input($_GET["uf"]);
        $year = clear_input($_GET["y"]);
        $pendent = clear_input($_GET["p"]);
        $descrip = getdescrip($conn,$uf);
        // obtenir les quotes de la UF per l'any en qüestió
        $stmt = $conn -> prepare("SELECT * FROM quotes WHERE uf=? AND year=?");
        $stmt->bind_param('ii', $uf, $year);
        $stmt->execute();
        $res = $stmt->get_result();
        $nrows = $res->num_rows;
        if ($nrows>0) {
            // editar quota existent
            $add = 0;
            $q = $res->fetch_assoc();
            $quota = $q["quota"];
        } else {
            $add = 1;
            // marca quota com pagada
            $stmt = $conn -> prepare("SELECT quota FROM admin");
            $stmt->execute();
            $dades = $stmt->get_result();
            $r = mysqli_fetch_array($dades);
            $quota = $r["quota"];
            $dades->free();
        }
        $res->free();
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h2><?php echo ($add==1 ? "Pagar" : "Editar"); ?> quota <?php echo ($add==1 ? "" : "pagada"); ?></h2>
        <h3>UC: <?php echo $descrip; ?></h3>
        <h3>Any: <?php echo $year; ?></h3>
        <?php
        if ($pendent==0) {
            $del = "del_quota.php?uf=".$uf."&year=".$year;
            echo "<a class='btn btn-link' onClick=\"javascript: return confirm('Si us plau, confirma que vols esborrar la quota');\" href='".$del."'>Esborrar</a>";
            echo "<a class='btn btn-link' href='quotes.php?year=".$year."'>Tornar</a>";
        } else {
            echo "<a class='btn btn-link' href='quotes.php?year=".$year."&pendent=1'>Tornar</a>";
        } ?>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="preu">Quota pagada (€):</label>
            <input type="number" class="form-control" min=0 step=".01" name="quota" value="<?php echo $quota; ?>">
        </div>

        <input type="text" class="form-control" hidden="true" name="uf" value=" <?php echo $uf; ?> ">
        <input type="text" class="form-control" hidden="true" name="year" value=" <?php echo $year; ?> ">
        <input type="text" class="form-control" hidden="true" name="add" value=" <?php echo $add; ?> ">
        <input type="text" class="form-control" hidden="true" name="filtre" value=" <?php echo $pendent; ?> ">
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
