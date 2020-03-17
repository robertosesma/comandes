<?php session_start();
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) { ?>
<div class="container">
    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered" id="listaCDs">
        <thead class="thead-dark">
            <tr>
                <th>Productor</th>
                <th>Producte</th>
                <th><div class='text-center'>Quantitat</div></th>
                <th><div class='text-right'>Preu</div></th>
                <th><div class='text-right'>Total</div></th>
                <?php if ($open) { ?><th><div class='text-right'></div></th><?php } ?>
            </tr>
        </thead>
        <tbody>
        <?php
        $nitems = $com->num_rows;
        $j = 1;
        $g0 = 0;
        while ($row = mysqli_fetch_array($com)) { ?>
            <?php
            if (($row["cgrupo"] <> $g0 && $g0>0)) { ?>
                <tr>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td><div class='text-right'>Subtotal</div></td>
                    <td><div class='text-right font-weight-bold'><?php echo getsubtotal($conn,$uf,$fecha,$g0); ?></div></td>
                    <?php if ($open) { ?><td> </td><?php } ?>
                </tr>
            <?php }
            $g0 = $row["cgrupo"];

            $preu = ($row["precio"]==NULL ? '' : number_format($row["precio"], 2, ",", ".")."€");
            $tot = ($row["total"]==NULL ? '' : number_format($row["total"], 2, ",", ".")."€");
            ?>
            <tr>
                <td><?php echo $row["dgrupo"]; ?></td>
                <td><?php echo $row["item"]; ?></td>
                <td><div class='text-center'><?php echo $row["n"]; ?></div></td>
                <td><div class='text-right'><?php echo $preu; ?></div></td>
                <td><div class='text-right'><?php echo $tot; ?></div></td>
                <?php if ($open) {
                $del = 'delete.php?&fecha='.$fecha.'&item='.$row["tipo"];
                echo "<td><a onClick=\"javascript: return confirm('Si us plau, confirma que vols esborrar');\" href='".$del."'>x</a></td><tr>"; } ?>
            </tr>
            <?php
            if ($j == $nitems) { ?>
                <tr>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td><div class='text-right'>Subtotal</div></td>
                    <td><div class='text-right font-weight-bold'><?php echo getsubtotal($conn,$uf,$fecha,$row["cgrupo"]); ?></div></td>
                    <?php if ($open) { ?><td> </td><?php } ?>
                </tr>
            <?php }
            $j = $j + 1;
        } ?>
        </tbody>
    </table>
</div>
<?php } ?>
