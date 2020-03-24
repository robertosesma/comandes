<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Productes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])
    && isset($_GET['prod']) && isset($_SESSION['contacte']) && $_SESSION['contacte'] == 1) {
    $conn = connect();

    // obtenim les dades del productor
    $prod = clear_input($_GET["prod"]);
    $stmt = $conn -> prepare("SELECT * FROM dgrupo WHERE cod = ?");
    $stmt->bind_param('i', $prod);
    $stmt->execute();
    $dades = $stmt->get_result();
    $nrows = $dades->num_rows;
    if ($nrows > 0) {
        while($r = $dades->fetch_assoc()) {
            $descrip = $r["descrip"];
            $activado = $r["activado"];
        }
        // obtenirm els productes del productor
        $stmt = $conn -> prepare("SELECT * FROM dtipo WHERE grupo = ?");
        $stmt->bind_param('i', $prod);
        $stmt->execute();
        $items = $stmt->get_result();
    } else {
        $ok = false;
    }
    $dades->free();
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Llistat de productes</h1>
        <h3>Productora: <?php echo $descrip; ?></h3>
        <p><?php if ($activado) {
            echo '<a class="btn btn-link" href="toggle_prod.php?grupo='.$prod.'&act=0">Desactivar productora</a>';
        } else {
            echo '<a class="btn btn-link" href="toggle_prod.php?grupo='.$prod.'&act=1">Activar productora</a>';
        }
        echo '<a class="btn btn-link" href="edit_item.php?grupo='.$grupo.'&add=1">Afegir producte</a>'; ?></p>
        <p><a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a></p>
    </div>
</div>

<div class="container">
    <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Producte</th>
                <th><div class='text-right'>Preu</div></th>
                <?php if ($grupo==4) { ?><th><div class='text-center'>Fila</div></th><?php } ?>
            </tr>
        </thead>
        <?php while ($i = mysqli_fetch_array($items)) {
            if ($i["desactivado"]==0) {
                echo "<tr>";
            } else {
                echo "<tr class=table-secondary>";
            }
            echo '<td><a class="btn btn-link" href="edit_item.php?prod='.$prod.'&item='.$i["tipo"].'&add=0">'.$i["descrip"]."</td>";
            echo '<td><div class="text-right">'.getascurr($i["precio"],"€").'</div></td>';
            if ($prod == 4) { echo '<td><div class="text-center">'.$i["fila"].'</div></td>'; }
            echo "</tr>";
        } ?>
    </table>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
