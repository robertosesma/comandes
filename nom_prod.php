<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Nom productor</title>
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
        $grupo = clear_input($_POST["grupo"]);
        $nom = clear_input($_POST["nom"]);

        $stmt = $conn -> prepare("UPDATE dgrupo SET descrip=? WHERE cod=?");
        $stmt->bind_param('si', $nom, $grupo);
        $stmt->execute();
        echo '<script>window.location.href = "llista_items.php?grupo='.$grupo.'";</script>';
    }

    // init the form: get current name
    if (isset($_GET['grupo'])) {
        $grupo = clear_input($_GET['grupo']);
        $stmt = $conn -> prepare("SELECT * FROM dgrupo WHERE cod = ?");
        $stmt->bind_param('i', $grupo);
        $stmt->execute();
        $prod = $stmt->get_result();
        $nrows = $prod->num_rows;
        if ($nrows > 0) {
            while($p = $prod->fetch_assoc()) {
                $nom = $p["descrip"];
            }
        } else {
            $ok = false;
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
        <h2>Nom productor</h2>
        <a class="btn btn-link" href=<?php echo "llista_items.php?grupo=".$grupo; ?>>Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" class="form-control" name="nom" id="nom" required value="<?php echo $nom; ?>">
        </div>
        <input type="text" class="form-control" hidden="true" name="grupo" value="<?php echo $grupo; ?>">
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
