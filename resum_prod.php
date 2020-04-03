<?php session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $stmt = $conn -> prepare("SELECT * FROM resumen WHERE fecha = ? AND cgrupo = ?");
    $stmt->bind_param('si', $fecha, $productor);
    $stmt->execute();
    $data = $stmt->get_result();

    $stmt = $conn -> prepare("SELECT fecha, cgrupo, SUM(t) AS total FROM resumen
    WHERE fecha = ? AND cgrupo = ?
    GROUP BY fecha, cgrupo");
    $stmt->bind_param('si', $fecha, $productor);
    $stmt->execute();
    $total = $stmt->get_result();

    $nrows = $total->num_rows;
    if ($nrows > 0) {
        while($t = $total->fetch_assoc()) {
            $subtotal = getascurr($t["total"],"€");
        }
        ?>

        <h3> <?php echo $descrip.": ".$subtotal; ?> </h3>
        <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Producte</th>
                    <th><div class='text-center'>Quantitat</div></th>
                    <th><div class='text-right'>Preu</div></th>
                    <th><div class='text-right'>Total</div></th>
                </tr>
            </thead>
            <tbody>
            <?php while ($d = mysqli_fetch_array($data)) {
                $preu = getascurr($d["precio"],"€");
                $tot = getascurr($d["t"],"€"); ?>
                <tr>
                    <td><?php echo $d["item"]; ?></td>
                    <td><div class='text-center'><?php echo $d["n"]; ?></div></td>
                    <td><div class='text-right'><?php echo $preu; ?></div></td>
                    <td><div class='text-right'><?php echo $tot; ?></div></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
<?php } else {
        echo "<h3>".$descrip.": Sense productes</h3>";
    }
} ?>
