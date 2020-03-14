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
$ok = true;
$err = "";
$error = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $conn = connect();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $uf = clear_input($_POST["uf"]);
        $fecha = clear_input($_POST["fecha"]);
        if (empty($_POST["grupo"])) {
            $err = "El productor no pot quedar buit. S'han de tornar a introduir les dades.";
            $error = true;
        } else {
            $g = clear_input($_POST["grupo"]);
            if (empty($_POST["item".$g])) {
                $err = "El producte no pot quedar buit. S'han de tornar a introduir les dades.";
                $error = true;
            } else {
                $i = clear_input($_POST["item".$g]);
                if (empty($_POST["n"])) {
                    $err = "La quantitat no pot quedar buida. S'han de tornar a introduir les dades.";
                    $error = true;
                } else {
                    $n = clear_input($_POST["n"]);
                    // check if comanda exists
                    $stmt = $conn -> prepare("SELECT * FROM comandes WHERE fecha=?");
                    $stmt->bind_param('s',$fecha);
                    $stmt->execute();
                    $comandes = $stmt->get_result();
                    if ($comandes->num_rows == 0) {
                        // insert comanda with the first producte
                        $stmt = $conn -> prepare("INSERT INTO comandes (fecha,uf) VALUES (?,?)");
                        $stmt->bind_param('si',$fecha,$uf);
                        $stmt->execute();
                    }
                    // insert producte
                    $stmt = $conn -> prepare("INSERT INTO items (fecha,uf,tipo,n) VALUES (?,?,?,?)");
                    $stmt->bind_param('siii',$fecha,$uf,$i,$n);
                    $stmt->execute();
                    echo '<script>window.location.href = "comanda_new.php?uf='.$uf.'";</script>';
                }
            }
        }
    }

    // init the form
    if ((isset($_GET['uf']) && isset($_GET['fecha'])) || $error) {
        $uf = $_GET["uf"];
        $fecha = $_GET["fecha"];

        // get productors
        $stmt = $conn -> prepare("SELECT dtipo.grupo, dgrupo.descrip
                                FROM dgrupo RIGHT JOIN dtipo ON dgrupo.cod = dtipo.grupo
                                GROUP BY dtipo.grupo, dgrupo.descrip
                                ORDER BY dtipo.grupo;");
        $stmt->execute();
        $prod = $stmt->get_result();

        // build items comboboxes
        $items_combobox = "";
        while ($r = mysqli_fetch_array($prod)) {
            // get items for one productor
            $g = $r["grupo"];
            $stmt = $conn -> prepare("SELECT dtipo.tipo, dtipo.descrip
                            FROM dtipo
                            WHERE (dtipo.grupo = ?)
                            ORDER BY dtipo.tipo");
            $stmt->bind_param('i', $g);
            $stmt->execute();
            $items = $stmt->get_result();
            // build html custom-select combo box
            $items_combobox .= '<select id="'.$g.'" name="item'.$g.'" class="custom-select hidden_control">';
            $items_combobox .= '<option selected></option>';
            while ($i = mysqli_fetch_array($items)) {
                $items_combobox .= "<option value=".$i["tipo"].">".$i["descrip"]."</option>";
            }
            $items_combobox .= "</select>";
        }
        // go to the beggining of the dataset
        mysqli_data_seek($prod, 0);
    }
    $descrip = getdescrip($conn,$uf);
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Afegir producte</h2>
        <p>Unitat de Convivència: <?php echo $descrip; ?></p>
        <p>Comanda <?php echo $fecha; ?></p>
        <a class="btn btn-link" href=<?php echo "comanda_new.php?uf=".$uf; ?>>Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <span class="error text-danger"><?php echo $err;?></span>
        </div>
        <div class="form-group">
            <label for="productor">Productor:</label>
            <select name="grupo" id="productor" class="custom-select">
                <option selected></option>
                <?php while ($r = mysqli_fetch_array($prod)) {
                    echo "<option value=".$r["grupo"].">".$r["descrip"]."</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <label id=lbl1 class="hidden_control">Producte:</label>
            <?php echo $items_combobox ?>
        </div>
        <div class="form-group">
            <label id=lbl2 class="hidden_control">Quantitat:</label>
            <input type="number" min="0" step="1" class="form-control hidden_control" name="n" id="qnt">
        </div>
        <input type="text" class="hidden_control" name="uf" value=" <?php echo $uf; ?> ">
        <input type="text" class="hidden_control" name="fecha" value=" <?php echo $fecha; ?> ">
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<script type="text/javascript">
    //when you select something from category box
    $("#productor").change(function(){
        //get selected category
        var selectedValue = $(this).find(":selected").val();
        //hide all hidden_combobox controls
        $('.hidden_control').each(function(){
           $(this).hide();
        });
        //show combobox for this select
        $('#'+selectedValue).show();
        //show label and quantity
        $('#lbl1').show();
        $('#lbl2').show();
        $('#qnt').show();
    });
</script>

<?php $conn->close(); ?>

</body>
</html>
