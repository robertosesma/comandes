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
$error = false;
$pswdErr = "";
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $conn = connect();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $grupo = clear_input($_POST["grupo"]);
        $item = clear_input($_POST["item"]);
        $add = clear_input($_POST["add"]);
        $descrip = clear_input($_POST["descrip"]);
        $preu = clear_input($_POST["preu"]);
        if ($grupo==4) {
            $fila = clear_input($_POST["fila"]);
        } else {
            $fila = NULL;
        }

        if ($add==0) {
            // editar un registro ya existente
            if ($grupo==4) {
                // el tipo 4 (Fleca Roca - PA) tiene número de fila en el excel del productor
                $stmt = $conn -> prepare("UPDATE dtipo SET descrip=?, precio=?, fila=? WHERE tipo=?");
                $stmt->bind_param('sdii', $descrip, str_replace(",",".",$preu), $fila, $item);
            } else {
                $stmt = $conn -> prepare("UPDATE dtipo SET descrip=?, precio=? WHERE tipo=?");
                $stmt->bind_param('sdi', $descrip, str_replace(",",".",$preu), $item);
            }
        } else {
            // crear un registro nuevo
            // obtener el siguiente tipo: max + 1
            $next = getnextitem($conn);
            $stmt = $conn -> prepare("INSERT INTO dtipo (tipo, descrip, precio, grupo, fila) VALUES (?,?,?,?,?)");
            $stmt->bind_param('isdii', $next, $descrip, str_replace(",",".",$preu), $grupo, $fila);
        }
        $stmt->execute();
        echo '<script>window.location.href = "llista_items.php?grupo='.$grupo.'";</script>';
    }

    if (isset($_GET['grupo']) && isset($_GET['item']) && isset($_GET['add'])) {
        $grupo = clear_input($_GET['grupo']);
        $item = clear_input($_GET['item']);
        $add = clear_input($_GET['add']);

        if ($add==0) {
            $stmt = $conn -> prepare("SELECT * FROM dtipo WHERE tipo = ?");
            $stmt->bind_param('i', $item);
            $stmt->execute();
            $prod = $stmt->get_result();
            $nrows = $prod->num_rows;
            if ($nrows > 0) {
                while($p = $prod->fetch_assoc()) {
                    $descrip = $p["descrip"];
                    $preu = $p["precio"];
                    $fila = $p["fila"];
                }
            } else {
                $ok = false;
            }
        } else {
            $descrip = '';
            $preu = '';
            $fila = '';
        }
    }
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h2><?php if ($add==0) {echo "Editar"; } else {echo "Afegir";} ?> producte</h2>
        <a class="btn btn-link" href=<?php echo "llista_items.php?grupo=".$grupo; ?>>Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="descrip">Descripció:</label>
            <input type="text" class="form-control" name="descrip" required value="<?php echo $descrip;?>">
        </div>
        <div class="form-group">
            <label for="preu">Preu (€):</label>
            <input type="number" class="form-control" min=0 step=".01" name="preu" value="<?php echo $preu;?>">
        </div>
        <?php if ($grupo==4) { ?>
            <div class="form-group">
                <label for="preu">Fila:</label>
                <input type="number" class="form-control" min=0 step="1" name="fila" required value="<?php echo $fila;?>">
            </div>
        <?php } ?>
        <input type="text" class="form-control" hidden="true" name="grupo" value=" <?php echo $grupo; ?> ">
        <input type="text" class="form-control" hidden="true" name="item" value=" <?php echo $item; ?> ">
        <input type="text" class="form-control" hidden="true" name="add" value=" <?php echo $add; ?> ">
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php
    $conn->close();
} else {
    header("Location: index.php");
}?>


</body>
</html>
