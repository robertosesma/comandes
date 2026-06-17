<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Dades Productora</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
$pswdErr = "";
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])
    && isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
    // només els administradors poden editar les productores
    $conn = connect();

    // obtenir llistat UF
    $stmt = $conn -> prepare("SELECT uf, descrip FROM uf ORDER BY descrip");
    $stmt->execute();
    $ufs = $stmt->get_result();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $add = clear_input($_POST["add"]);
        $descrip = clear_input($_POST["descrip"]);
        $uf = clear_input($_POST["uf"]);
        $uf = (strlen($uf)>0 ? $uf : null);
        $activado = 0;
        if (isset($_POST["act"])) $activado = (clear_input($_POST["act"])=="activado");
        $activado = ($activado == 1 ? 1 : 0);
        if ($add==1) {
            // afegir nova productora
            $prod = getnextprod($conn);
            $stmt = $conn -> prepare("INSERT INTO dgrupo (cod,descrip,uf,activado) VALUES (?,?,?,?)");
            $stmt->bind_param('isii', $prod, $descrip, $uf, $activado);
            $stmt->execute();
            // afegir usuari productora
            $uf = getmax($conn)+$prod;
            $stmt = $conn -> prepare("INSERT INTO uf (uf,descrip) VALUES (?,?)");
            $stmt->bind_param('is', $uf, $descrip);
            $stmt->execute();
        } else {
            // editar productora existent
            $prod = clear_input($_POST["prod"]);
            $stmt = $conn -> prepare("UPDATE dgrupo SET descrip=?, uf=?, activado =? WHERE cod=?");
            $stmt->bind_param('siii', $descrip, $uf, $activado, $prod);
            $stmt->execute();
        }
        header("Location: admin_prods.php");
    }
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $add = clear_input($_GET["add"]);
        if ($add==1) {
            $descrip = '';
            $contacte = '';
            $activado = 1;
        } else {
            // editar una productora que ja existeix: obtenir les dades
            $prod = clear_input($_GET["prod"]);
            $stmt = $conn -> prepare("SELECT * FROM dgrupo WHERE cod=?");
            $stmt->bind_param('i', $prod);
            $stmt->execute();
            $dades = $stmt->get_result();
            $nrows = $dades->num_rows;
            if ($nrows > 0) {
                while($r = $dades->fetch_assoc()) {
                    $descrip = $r["descrip"];
                    $uf = $r["uf"];
                    $activado = $r["activado"];
                }
            } else {
                $ok = false;
            }
            $dades->free();
        }
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Dades productora</h2>
        <a class="btn btn-link" href="admin_prods.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="descrip">Nom productora:</label>
            <input type="text" class="form-control" name="descrip" required value="<?php echo $descrip;?>">
        </div>
        <div class="form-group">
            <label for="uf">Contacte:</label>
            <select name="uf" class="custom-select">
                <option selected></option>
                <?php while ($r = mysqli_fetch_array($ufs)) {
                    echo "<option ".($uf == $r["uf"] ? "selected" : "")." value=".$r["uf"].">".$r["descrip"]."</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="act" id="act"
                    value="activado" <?php echo ($activado==1 ? "checked" : ""); ?>>
                <label class="custom-control-label" for="act">Activa</label>
            </div>
        </div>
        <input type="text" class="form-control" hidden="true" name="prod" value=" <?php echo $prod; ?> ">
        <input type="text" class="form-control" hidden="true" name="add" value=" <?php echo $add; ?> ">
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
