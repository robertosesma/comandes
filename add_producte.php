<head>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous">
    </script>
</head>

<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username']) && isset($_SESSION['fecha'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['grupo']) && isset($_POST['n'])) {
            $uf = $_SESSION['username'];
            $fecha = $_SESSION['fecha'];

            $g = clear_input($_POST["grupo"]);
            $n = clear_input($_POST["n"]);
            $i = clear_input($_POST["item".$g]);

            if (strlen($i)==0) {
                echo '<script>alert("El producte no pot quedar buit!")</script>';
            } else {
                // afegir el producte
                $conn = connect();

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
                // comprovar si el producte ja s'ha afegit
                $stmt = $conn -> prepare("SELECT * FROM items WHERE fecha=? AND uf=? AND tipo=?");
                $stmt->bind_param('sii',$fecha,$uf,$i);
                $stmt->execute();
                $dades = $stmt->get_result();
                // afegir producte i tornar a la pàgina anterior
                if ($dades->num_rows == 0) {
                    // si el producte no existeix, l'afegim
                    $stmt = $conn -> prepare("INSERT INTO items (fecha,uf,tipo,n) VALUES (?,?,?,?)");
                    $stmt->bind_param('siii',$fecha,$uf,$i,$n);
                } else {
                    // si el producte ja existeix, sumem la quantitat
                    while ($r = mysqli_fetch_array($dades)) {
                        $n = $n + $r["n"];
                    }
                    $stmt = $conn -> prepare("UPDATE items SET n=? WHERE fecha=? AND uf=? AND tipo=?");
                    $stmt->bind_param('isii',$n,$fecha,$uf,$i);
                }
                $stmt->execute();
                $dades->free();

                $conn->close();
            }
            echo '<script>window.location.href = "new_comanda.php";</script>';
        }
    }
}
?>
