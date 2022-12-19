<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Administrar UC</title>
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

    // obtenir les dades de les UC
    $stmt = $conn -> prepare("SELECT * FROM uf ORDER BY descrip");
    $stmt->execute();
    $ucs = $stmt->get_result();
    $nucs = $ucs->num_rows;
} else {
    header("Location: index.php");
}
?>

<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Administrar UC</h2>
        <h4><?php echo $nucs; ?> UCs</h4>
        <a class="btn btn-link" href="edit_uc.php?add=1">Nova UC</a>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Descripció</th>
                <th>Correu</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = mysqli_fetch_array($ucs)) {
                $activado = ($u["act"]==1 ? true : false);
                $tres = ($u["tresorer"] ? " (Tresorera)" : "");
                echo ($activado ? "<tr>" : "<tr class=table-secondary>");?>
                    <td><?php echo '<a class="btn btn-link" href="edit_uc.php?uc='.$u["uf"].'&add=0">'.$u["descrip"].$tres.'</a>'; ?></td>
                    <td><?php echo $u["email"]; ?></td>
                </tr>
            <?php }
            $ucs->free();
            ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); ?>

</body>
</html>
