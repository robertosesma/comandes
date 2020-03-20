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
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username']) && isset($_GET['grupo'])) {
    $grupo = clear_input($_GET["grupo"]);
    $conn = connect();

    // get descrip from productor
    $stmt = $conn -> prepare("SELECT * FROM dgrupo WHERE cod = ?");
    $stmt->bind_param('i', $grupo);
    $stmt->execute();
    $tipo = $stmt->get_result();
    $nrows = $tipo->num_rows;
    if ($nrows > 0) {
        while($t = $tipo->fetch_assoc()) {
            $descrip = $t["descrip"];
            $activado = $t["activado"];
        }

        $stmt = $conn -> prepare("SELECT * FROM dtipo WHERE grupo = ?");
        $stmt->bind_param('i', $grupo);
        $stmt->execute();
        $prods = $stmt->get_result();
    } else {
        $ok = false;
    }
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Llistat de productes</h1>
        <h3>Productor: <?php echo $descrip; ?></h3>
        <p><?php if ($activado) {
            echo '<a class="btn btn-link" href="toggle_prod.php?grupo='.$grupo.'&act=0">Desactivar</a>';
        } else {
            echo '<a class="btn btn-link" href="toggle_prod.php?grupo='.$grupo.'&act=1">Activar</a>';
        }?>
        <?php echo '<a class="btn btn-link" href="nom_prod.php?grupo='.$grupo.'">Canviar nom</a>'; ?>
        <?php echo '<a class="btn btn-link" href="edit_item.php?grupo='.$grupo.'&item=0&add=1">Afegir producte</a>'; ?>
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
                <th> </th>
                <th> </th>
            </tr>
        </thead>
        <?php while ($row = mysqli_fetch_array($prods)) {
            $preu = ($row["precio"]==NULL ? '' : number_format($row["precio"], 2, ",", ".")."€");
            if ($row["desactivado"]==0) {
                echo "<tr>";
            } else {
                echo "<tr class=table-secondary>";
            } ?>
                <td><?php echo $row["descrip"]; ?></td>
                <td><div class='text-right'><?php echo $preu; ?></div></td>
                <?php if ($grupo == 4) { echo "<td><div class='text-center'>".$row["fila"]."</div></td>"; } ?>
                <?php echo "<td><a href='edit_item.php?&grupo=".$grupo."&item=".$row["tipo"]."&add=0'>Editar</a></td>"; ?>
                <?php
                if ($row["desactivado"]==0) {
                    echo "<td><a href='toggle_item.php?&grupo=".$grupo."&item=".$row["tipo"]."&desact=1'>Desactivar</a></td>";
                } else {
                    echo "<td><a href='toggle_item.php?&grupo=".$grupo."&item=".$row["tipo"]."&desact=0'>Activar</a></td>";
                } ?>
            </tr>
        <?php } ?>
    </table>
</div>

<?php
    $conn->close();
} else {
    header("Location: index.php");
}?>

</body>
</html>
