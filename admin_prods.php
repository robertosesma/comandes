<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Administrar productores</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])
    && isset($_SESSION['admin']) && ($_SESSION['admin']==1)) {
    $conn = connect();

    // obtenir les dades dels productors
    $stmt = $conn -> prepare("SELECT cod, dgrupo.descrip, dgrupo.uf, uf.descrip AS duf, activado FROM dgrupo
        LEFT JOIN uf ON (uf.uf = dgrupo.uf)");
    $stmt->execute();
    $prods = $stmt->get_result();
} else {
    header("Location: logout.php");
}
?>

<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Productores</h2>
        <a class="btn btn-link" href="edit_prod.php?add=1">Nova productora</a>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Productores</th>
                <th>Contacte</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = mysqli_fetch_array($prods)) {
                $activado = ($p["activado"]==1 ? true : false);
                echo ($activado ? "<tr>" : "<tr class=table-secondary>");?>
                    <td><?php echo '<a class="btn btn-link" href="edit_prod.php?prod='.$p["cod"].'&add=0">'.$p["descrip"].'</a>'; ?></td>
                    <td><?php echo $p["duf"]; ?></td>
                </tr>
            <?php }
            $prods->free();
            ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); ?>

</body>
</html>
