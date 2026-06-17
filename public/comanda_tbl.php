<?php 
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) { ?>
<div class="container">
    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Productora</th>
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
                    <?php $sub = getascurr(getsubtotal($conn,$uf,$fecha,$g0),"€"); ?>
                    <td><div class='text-right font-weight-bold'><?php echo $sub; ?></div></td>
                    <?php if ($open) { ?><td> </td><?php } ?>
                </tr>
            <?php }
            $g0 = $row["cgrupo"]; ?>
            <tr>
                <td><?php echo $row["dgrupo"]; ?></td>
                <td><?php echo $row["item"]; ?></td>
                <td><div class='text-center'><?php echo $row["n"]; ?></div></td>
                <td><div class='text-right'><?php echo ($row["desact"]==0 ? getascurr($row["precio"],"€") : "ANUL·LAT"); ?></div></td>
                <td><div class='text-right'><?php echo ($row["desact"]==0 ? getascurr($row["total"],"€") : "0€"); ?></div></td>
                <?php if ($open) {
                $del = 'del_producte.php?item='.$row["tipo"];
                echo "<td><a onClick=\"javascript: return confirm('Si us plau, confirma que vols esborrar');\" href='".$del."'>x</a></td><tr>"; } ?>
            </tr>
            <?php
            if ($j == $nitems) { ?>
                <tr>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td><div class='text-right'>Subtotal</div></td>
                    <?php $sub = getascurr(getsubtotal($conn,$uf,$fecha,$g0),"€"); ?>
                    <td><div class='text-right font-weight-bold'><?php echo $sub; ?></div></td>
                    <?php if ($open) { ?><td> </td><?php } ?>
                </tr>
            <?php }
            $j = $j + 1;
        } ?>
        </tbody>
    </table>
</div>
<?php } ?>
