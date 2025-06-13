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
$err = false;
$fechaErr = "";
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && 
    ((isset($_SESSION['admin']) && $_SESSION['admin'] == true) || 
     (isset($_SESSION['calendari']) && $_SESSION['calendari'] == true))) {
    $conn = connect();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fecha = clear_input($_POST["fecha"]);
        $uc1 = clear_input($_POST["uf1"]);
        $uc2 = clear_input($_POST["uf2"]);
        $com = clear_input($_POST["coment"]);
        $tancat = 0;
        if (isset($_POST["tancat"])) $tancat = (clear_input($_POST["tancat"])=="activado");
        $ass = 0;
        if (isset($_POST["ass"])) $ass = (clear_input($_POST["ass"])=="activado");

        // dades de l'obertura original
        $fecha0 = clear_input($_POST["fecha0"]);
        $stmt = $conn -> prepare("SELECT cerrado FROM calendari WHERE fecha = ?");
        $stmt->bind_param('s',$fecha0);
        $stmt->execute();
        $res = $stmt->get_result();
        $d = $res->fetch_assoc(); 

        // si hi ha canvi de data, comprovar que està a la mateixa setmana
        $fechaErr = "";
        $err = false;
        if ($fecha!=$fecha0) {
            $date0 = new DateTime($fecha0);
            $date1 = new DateTime($fecha);
            if ($date0->format("Y")!=$date1->format("Y") ||
                $date0->format("W")!=$date1->format("W")) {
                $err = true;
                $fechaErr = "La data introduïda era incorrecta";
                $fecha = $fecha0;
            }
        }

        if (!$err) {
            if ($tancat != $d["cerrado"]) {
                // TANCAR l'obertura
            } else {
                // GRAVAR els canvis
                $stmt = $conn -> prepare("UPDATE calendari SET fecha=?, uc1=?, uc2 =?, cerrado=?, asamblea=?, coment=? 
                    WHERE fecha=?");
                $stmt->bind_param('siiiiss', $fecha, $uc1, $uc2, $tancat, $ass, $com, $fecha0);
                $stmt->execute();
            }
        }
        header("Location: calendari.php");
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $fecha = clear_input($_GET["fecha"]);

        // obtenir les dades d'obertura
        $stmt = $conn -> prepare("SELECT * FROM calendari WHERE fecha = ?");
        $stmt->bind_param('s',$fecha);
        $stmt->execute();
        $res = $stmt->get_result();
        $d = $res->fetch_assoc();
        $uc1 = $d["uc1"];
        $uc2 = $d["uc2"];
        $com = $d["coment"];
        $tancat = $d["cerrado"];
        $ass = $d["asamblea"];
    }

    // obtenir les dades de les UF pels combos
    $stmt = $conn -> prepare('SELECT * FROM uf WHERE act=1 AND uf < 1000 ORDER BY descrip');
    $stmt->execute();
    $duf1 = $stmt->get_result();

    $stmt = $conn -> prepare('SELECT * FROM uf WHERE act=1 AND uf < 1000 ORDER BY descrip');
    $stmt->execute();
    $duf2 = $stmt->get_result();
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Obertura <?php echo date_format(date_create($fecha),"d/m/Y")?></h2>
        <a class="btn btn-link" href="calendari.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group" style="margin-top:10px">
            <label for="fecha">Data:</label>
            <input type="date" name="fecha" value="<?php echo $fecha; ?>">
            <span class="error text-danger"><?php echo $fechaErr;?></span>
        </div>
        
        <div class="form-group" style="margin-top:10px">
            <label for="uf1">Unitat de convivència 1:</label>
            <?php $found1 = false; ?>
            <select name="uf1" required class="custom-select">
                <option></option>
                <?php while ($r1 = mysqli_fetch_array($duf1)) {
                    $sel = ($r1["uf"]==$uc1 ? "selected" : "");
                    if ($r1["uf"]==$uc1) $found1 = true;
                    echo "<option ".$sel." value=".$r1["uf"].">".$r1["descrip"]."</option>";
                }
                if (!$found1) 
                    echo "<option selected value=".$uc1.">".getdescrip($conn,$uc1)."</option>"; ?>
            </select>
            <?php if (!$found1) 
                echo '<span class="text-warning">ATENCIÓ! La UC assignada NO està activa</span>'; ?>
        </div>

        <div class="form-group" style="margin-top:10px">
            <label for="uf2">Unitat de convivència 2:</label>
            <?php $found2 = false; ?>
            <select name="uf2" required class="custom-select">
                <option></option>
                <?php while ($r2 = mysqli_fetch_array($duf2)) {
                    $sel = ($r2["uf"]==$uc2 ? "selected" : "");
                    if ($r2["uf"]==$uc2) $found2 = true;
                    echo "<option ".$sel." value=".$r2["uf"].">".$r2["descrip"]."</option>";
                } 
                if (!$found2) 
                    echo "<option selected value=".$uc2.">".getdescrip($conn,$uc2)."</option>"; ?>
            </select>
            <?php if (!$found2) 
                echo '<span class="text-warning">ATENCIÓ! La UC assignada NO està activa</span>'; ?>
        </div>

        <div class="form-group">
            <label for="coment">Comentari:</label>
            <input type="text" class="form-control" name="coment" value="<?php echo $com;?>">
        </div>

        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="tancat" id="tancat"
                    value="activado" <?php echo ($tancat ? "checked" : ""); ?>>
                <label class="custom-control-label" for="tancat">TANCAT</label>
            </div>
        </div>

        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="ass" id="ass"
                    value="activado" <?php echo ($ass ? "checked" : ""); ?>>
                <label class="custom-control-label" for="ass">Assemblea</label>
            </div>
        </div>

        <input type="text" class="form-control" hidden="true" name="fecha0" value=" <?php echo $fecha; ?> ">
        <button type="submit" class="btn btn-primary">Enviar</button>
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
