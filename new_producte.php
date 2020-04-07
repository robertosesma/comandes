<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Afegir producte</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <style type="text/css" media="screen">
    .hidden_control{
        display:none;
    }
</style>
</head>

<body>
<?php
include 'func_aux.php';
$err="";
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $conn = connect();
    $uf = $_SESSION['username'];
    $descrip = getdescrip($conn,$uf);
    $fecha = $_SESSION['fecha'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $g = clear_input($_POST["grupo"]);
        $n = clear_input($_POST["n"]);
        $i = clear_input($_POST["item".$g]);

        if (strlen($i)==0) {
            $err = "El producte no pot quedar buit. Torni a introduir les dades.";
        } else {
            // comprobar si ja s'ha iniciat la comanda
            $stmt = $conn -> prepare("SELECT * FROM comandes WHERE fecha=? AND uf=?");
            $stmt->bind_param('si',$fecha,$uf);
            $stmt->execute();
            $comandes = $stmt->get_result();
            if ($comandes->num_rows == 0) {
                // si no existeix la comanda, afegir-la
                $stmt = $conn -> prepare("INSERT INTO comandes (fecha,uf) VALUES (?,?)");
                $stmt->bind_param('si',$fecha,$uf);
                $stmt->execute();
            }
            $comandes->free();
            // afegir producte i tornar a la pàgina anterior
            $stmt = $conn -> prepare("INSERT INTO items (fecha,uf,tipo,n) VALUES (?,?,?,?)");
            $stmt->bind_param('siii',$fecha,$uf,$i,$n);
            $stmt->execute();
            echo '<script>window.location.href = "new_comanda.php";</script>';
        }
    }

    // carregar el formulari
    // obtenir les dades de les productores actives
    $stmt = $conn -> prepare("SELECT dtipo.grupo, dgrupo.descrip
                            FROM dgrupo RIGHT JOIN dtipo ON dgrupo.cod = dtipo.grupo
                            WHERE dgrupo.activado = 1
                            GROUP BY dtipo.grupo, dgrupo.descrip
                            ORDER BY dtipo.grupo;");
    $stmt->execute();
    $dades = $stmt->get_result();

    // html dels combos de productes
    $items_combos = "";
    // html del combo de productores
    $prod_combo = '<select name="grupo" id="productor" class="custom-select" required>';
    $prod_combo .= '<option selected></option>';
    while ($r = mysqli_fetch_array($dades)) {
        $g = $r["grupo"];
        $prod_combo .= "<option value=".$g.">".$r["descrip"]."</option>";
        // obtenir els items per cada productor
        $stmt = $conn -> prepare("SELECT dtipo.tipo, dtipo.descrip
                        FROM dtipo
                        WHERE (dtipo.grupo = ? AND dtipo.desactivado = 0)
                        ORDER BY dtipo.tipo");
        $stmt->bind_param('i', $g);
        $stmt->execute();
        $items = $stmt->get_result();
        // construïm els combos de productes ocults
        $items_combos .= '<select id="'.$g.'" name="item'.$g.'" class="custom-select hidden_control">';
        $items_combos .= '<option selected></option>';
        while ($i = mysqli_fetch_array($items)) {
            $items_combos .= "<option value=".$i["tipo"].">".$i["descrip"]."</option>";
        }
        $items_combos .= "</select>";
    }
    $prod_combo .= "</select>";
    $dades->free();

    $conn->close();
} else {
    header("Location: logout.php");
}
?>

<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Afegir producte</h2>
        <h3>Comanda <?php echo $fecha; ?></h3>
        <h4>UC: <?php echo $descrip; ?></h4>
        <a class="btn btn-link" href=<?php echo "new_comanda.php"; ?>>Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="productor">Productora:</label>
            <?php echo $prod_combo ?>
        </div>
        <div class="form-group">
            <span class="error text-danger"><?php echo $err;?></span>
        </div>
        <div class="form-group">
            <label id=lbl1 class="hidden_control">Producte:</label>
            <?php echo $items_combos ?>
        </div>
        <div class="form-group">
            <label id=lbl2 class="hidden_control">Quantitat:</label>
            <input type="number" min="0" step="1" class="form-control hidden_control" name="n" id="qnt" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<script type="text/javascript">
    // seleccionar una productora
    $("#productor").change(function(){
        // obtenir el productor seleccionat
        var selectedValue = $(this).find(":selected").val();
        // ocultar TOTS els controls ocults
        $('.hidden_control').each(function(){
           $(this).hide();
        });
        // mostrar el control corresponent a la productora seleccionada
        $('#'+selectedValue).show();
        // mostrar etiquetes i control de quantitat
        $('#lbl1').show();
        $('#lbl2').show();
        $('#qnt').show();
    });
</script>

</body>
</html>
