<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Producte</title>
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
        $prod = clear_input($_POST["prod"]);
        $add = clear_input($_POST["add"]);
        $descrip = clear_input($_POST["descrip"]);
        $preu = clear_input($_POST["preu"]);
        $preu = (strlen($preu)>0 ? str_replace(",",".",$preu) : NULL);
        $desactivado = clear_input($_POST["desact"]=="desactivado");
        $desactivado = ($desactivado == 1 ? 1 : 0);
        // la Fleca Roca - PA (4) té fila en el excel del productor
        $fila = ($grupo==4 ? clear_input($_POST["fila"]) : NULL);
        if ($add==0) {
            // editar un registre ja existent
            $item = clear_input($_POST["item"]);
            $stmt = $conn -> prepare("UPDATE dtipo SET descrip=?, precio=?, fila=?, desactivado=? WHERE tipo=?");
            $stmt->bind_param('sdiii', $descrip, $preu, $fila, $desactivado, $item);
        } else {
            // afegir un item nou
            $item = getnextitem($conn);
            $stmt = $conn -> prepare("INSERT INTO dtipo (tipo, descrip, precio, grupo, fila, desactivado) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('isdiii', $item, $descrip, $preu, $prod, $fila, $desactivado);
        }
        $stmt->execute();
        echo '<script>window.location.href = "llista_items.php?prod='.$prod.'";</script>';
    } else {
        if (isset($_GET['prod']) && isset($_GET['add'])) {
            $prod = clear_input($_GET['prod']);
            $add = clear_input($_GET['add']);

            if ($add==0) {
                $item = clear_input($_GET['item']);
                $stmt = $conn -> prepare("SELECT * FROM dtipo WHERE tipo = ?");
                $stmt->bind_param('i', $item);
                $stmt->execute();
                $dades = $stmt->get_result();
                $nrows = $dades->num_rows;
                if ($nrows > 0) {
                    while($r = $dades->fetch_assoc()) {
                        $descrip = $r["descrip"];
                        $preu = $r["precio"];
                        $fila = $r["fila"];
                        $desactivado = $r["desactivado"];
                    }
                } else {
                    $ok = false;
                }
                $dades->free();
            } else {
                $descrip = '';
                $preu = '';
                $fila = '';
                $desactivado = 0;
            }
        } else {
            $ok = false;
        }
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
    <div class="container">
        <div class="container p-3 my-3 border">
            <h2><?php if ($add==0) {echo "Editar"; } else {echo "Afegir";} ?> producte</h2>
            <a class="btn btn-link" href=<?php echo "llista_items.php?prod=".$prod; ?>>Tornar</a>
            <a class="btn btn-link" href="logout.php">Sortir</a>
        </div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form-group">
                <label for="descrip">Nom producte:</label>
                <input type="text" class="form-control" name="descrip" required value="<?php echo $descrip;?>">
            </div>
            <div class="form-group">
                <label for="preu">Preu (€):</label>
                <input type="number" class="form-control" min=0 step=".01" name="preu" value="<?php echo $preu; ?>">
            </div>
            <?php if ($prod==4) { ?>
                <div class="form-group">
                    <label for="preu">Fila:</label>
                    <input type="number" class="form-control" min=0 step="1" name="fila" required value="<?php echo $fila;?>">
                </div>
            <?php } ?>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="desact" id="desact"
                        value="desactivado" <?php echo ($desactivado==1 ? "checked" : ""); ?>>
                    <label class="custom-control-label" for="desact">Desactivar</label>
                </div>
            </div>
            <input type="text" class="form-control" hidden="true" name="prod" value=" <?php echo $prod; ?> ">
            <input type="text" class="form-control" hidden="true" name="item" value=" <?php echo $item; ?> ">
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
