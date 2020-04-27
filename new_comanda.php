<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Nova comanda</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
    <style type="text/css" media="screen">
    .hidden_control{
        display:none;
    }
    </style>
</head>

<body>
<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $conn = connect();
    $uf = $_SESSION['username'];
    $descrip = getdescrip($conn,$uf);

    $fecha = getnext($conn);
    $_SESSION["fecha"] = $fecha;
    $open = isopen($conn);

    // get UC comanda
    $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $com = $stmt->get_result();

    $uctotal = gettotal($conn,$uf,$fecha);

    if ($open) {
        // carregar el formulari
        // obtenir les dades de les productores actives
        $stmt = $conn -> prepare("SELECT * FROM dgrupo WHERE dgrupo.activado = 1 ORDER BY dgrupo.descrip;");
        $stmt->execute();
        $dades = $stmt->get_result();

        // html dels combos de productes
        $items_combos = "";
        // html del combo de productores
        $prod_combo = '<select name="grupo" id="productor" class="custom-select" required>';
        $prod_combo .= '<option selected></option>';
        while ($r = mysqli_fetch_array($dades)) {
            $g = $r["cod"];
            $prod_combo .= "<option value=".$g.">".$r["descrip"]."</option>";
            // obtenir els items per cada productor
            $stmt = $conn -> prepare("SELECT dtipo.tipo, dtipo.descrip, dtipo.precio
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
                $preu = getascurr($i["precio"],"€");
                $preu = ($preu!='' ? " (".$preu.")" : '');
                $items_combos .= "<option value=".$i["tipo"].">".$i["descrip"].$preu."</option>";
            }
            $items_combos .= "</select>";
        }
        $prod_combo .= "</select>";
        $dades->free();
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container pt-3 my-3 border">
        <h1>Comanda <?php echo $fecha; ?></h1>
        <h2>UC: <?php echo $descrip; ?></h2>
        <h3>Total: <?php echo $uctotal; ?></h3>
        <p>Aquest total no inclou alguns productes de preu variable</p>
        <?php if (!$open) {
            echo "<h2 class='text-warning'>Comanda tancada</h2>";
        } else {
            echo "<p><a class='btn btn-link' href='repetir_comanda.php'>Repetir comanda anterior</a></p>";
        }?>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <?php if ($open) { ?>
        <div class="container p-3 my-3 border">
            <h4>Afegir producte</h4>
            <form method="post" action="add_producte.php">
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
                    <input type="number" min="1" step="1" class="form-control hidden_control" name="n" id="qnt" value="1" required>
                </div>
                <button type="submit" class="btn btn-primary">Afegir</button>
            </form>
        </div>
    <?php } ?>
</div>

<?php
    include 'comanda_tbl.php';

    $conn->close();
} else {
    header("Location: logout.php");
}?>

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
