<?php
session_start();

$dir = dirname(dirname(__FILE__));
require $dir.'/lib/PHPRtfLite.php';
include 'func_aux.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $fecha = clear_input($_GET['fecha']);
    $conn = connect();

    // obtenir llista d'UC
    $stmt = $conn -> prepare("SELECT uf, descrip FROM comanda WHERE fecha =? GROUP BY uf ORDER BY descrip");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $ucs = $stmt->get_result();
    $nuc = $ucs->num_rows;
    if ($nuc>0) {
        PHPRtfLite::registerAutoloader();       // register PHPRtfLite class loader
        $rtf = new PHPRtfLite();
        $sect = $rtf->addSection();

        $sect->writeText('<b>Llistat comanda '.$fecha.'</b>',
                    new PHPRtfLite_Font(14, 'Arial'), new PHPRtfLite_ParFormat());
        $sect->writeText('Unitats de Convivència: '.$nuc,
                    new PHPRtfLite_Font(14, 'Arial'), new PHPRtfLite_ParFormat());
        $sect->writeText('Els totals no inclouen alguns productes de preu variable',
                    new PHPRtfLite_Font(10, 'Arial'), new PHPRtfLite_ParFormat());

        $font = new PHPRtfLite_Font(10, 'Arial');
        $border = new PHPRtfLite_Border($rtf);
        $border->setBorderTop(new PHPRtfLite_Border_Format(1, '#000000'));
        $border->setBorderBottom(new PHPRtfLite_Border_Format(1, '#000000'));
        $borderTop = new PHPRtfLite_Border($rtf);
        $borderTop->setBorderTop(new PHPRtfLite_Border_Format(1, '#000000'));
        $borderBottom = new PHPRtfLite_Border($rtf);
        $borderBottom->setBorderBottom(new PHPRtfLite_Border_Format(1, '#000000'));

        $count = 1;
        while ($r = mysqli_fetch_array($ucs)) {
            $uf = $r["uf"];

            // get UC comandes
            $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
            $stmt->bind_param('is', $uf, $fecha);
            $stmt->execute();
            $com = $stmt->get_result();
            $nitems = $com->num_rows;

            $sect->writeText("\n".'<b>'.$count.'. '.$r["descrip"].': '.gettotal($conn,$uf,$fecha).'</b>',
                        new PHPRtfLite_Font(12, 'Arial'), new PHPRtfLite_ParFormat());

            // crear tabla
            $table = $sect->addTable();

            // capçalera
            $row = 1;
            $table->addRows(1, 0.7);
            $table->addColumnsList(array(3,5,2,2,2));
            $cell = $table->getCell($row, 1);
            $cell->writeText("<b>Productora</b>",$font);
            $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
            $cell = $table->getCell($row, 2);
            $cell->writeText("<b>Producte</b>",$font);
            $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
            $cell = $table->getCell($row, 3);
            $cell->writeText("<b>Quantitat</b>",$font);
            $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
            $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
            $cell = $table->getCell($row, 4);
            $cell->writeText("<b>Preu</b>",$font);
            $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
            $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
            $cell = $table->getCell($row, 5);
            $cell->writeText("<b>Total</b>",$font);
            $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
            $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
            // marges
            $table->setBorderForCellRange($border, 1, 1, 1, 5);

            // omplir la taula
            $g0 = 0;
            $j = 1;
            $row++;
            while ($i = mysqli_fetch_array($com)) {
                if (($i["cgrupo"] <> $g0 && $g0>0)) {
                    // subtotal de grup
                    $table->addRows(1, 0.7);
                    $cell = $table->getCell($row, 4);
                    $cell->writeText("Subtotal",$font);
                    $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
                    $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
                    $cell = $table->getCell($row, 5);
                    $cell->writeText("<b>".getascurr(getsubtotal($conn,$uf,$fecha,$g0),"€")."</b>",$font);
                    $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
                    $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
                    $table->setBorderForCellRange($border, $row, 4, $row, 5);

                    $row++;
                }
                $g0 = $i["cgrupo"];

                $table->addRows(1, 0.6);
                $cell = $table->getCell($row, 1);
                $cell->writeText($i["dgrupo"],$font);
                $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
                $cell = $table->getCell($row, 2);
                $cell->writeText($i["item"],$font);
                $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
                $cell = $table->getCell($row, 3);
                $cell->writeText($i["n"],$font);
                $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
                $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
                $cell = $table->getCell($row, 4);
                $cell->writeText(getascurr($i["precio"],"€"),$font);
                $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
                $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
                $cell = $table->getCell($row, 5);
                $cell->writeText(getascurr($i["total"],"€"),$font);
                $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
                $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);

                $row++;
                if ($j == $nitems) {
                    // últim subtotal de grup
                    $table->addRows(1, 0.7);
                    $cell = $table->getCell($row, 4);
                    $cell->writeText("Subtotal",$font);
                    $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
                    $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
                    $cell = $table->getCell($row, 5);
                    $cell->writeText("<b>".getascurr(getsubtotal($conn,$uf,$fecha,$g0),"€")."</b>",$font);
                    $cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
                    $cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
                    $table->setBorderForCellRange($borderTop, $row, 4, $row, 5);
                    $table->setBorderForCellRange($borderBottom, $row, 1, $row, 5);
                }
                $j = $j + 1;
            }
            $count++;
        }

        // descarregar el fitxer rtf
        header('Content-Type: application/vnd.ms-word');
        header('Content-Disposition: attachment;filename="llistat_'.$fecha.'.rtf"');
        header('Cache-Control: max-age=0');
        $rtf->save('php://output');
    }
    $ucs->free();
    $conn->close();
} else {
    header("Location: logout.php");
}
// exit();
?>
