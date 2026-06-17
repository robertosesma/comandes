<?php
session_start();

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\IOFactory;
include 'func_aux.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $fecha = clear_input($_GET['fecha']);
    $conn = connect();
    
    try {
        // obtenir llista d'UC
        $stmt = $conn -> prepare("SELECT uf, descrip FROM comanda WHERE fecha =? GROUP BY uf ORDER BY descrip");
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $ucs = $stmt->get_result();
        $nuc = $ucs->num_rows;
        if ($nuc>0) {
            $word = new PhpOffice\PhpWord\PhpWord();        // crear el document
            $sect = $word->addSection();                    // afegir una secció
            $style = $sect->getStyle();                     // estil de la secció
            $style->setPortrait();
            $style->setPaperSize('A4');
            $style->setMarginTop(1130);
            $style->setMarginBottom(1130);
            $style->setMarginRight(1200);
            $style->setMarginLeft(1200);

            // font styles
            $Arial14bold = ['name' => 'Arial', 'size' => 14, 'bold' => true];
            $Arial14 = ['name' => 'Arial', 'size' => 14];
            $Arial12bold = ['name' => 'Arial', 'size' => 12, 'bold' => true];
            $Arial10bold = ['name' => 'Arial', 'size' => 10, 'bold' => true];
            $Arial10 = ['name' => 'Arial', 'size' => 10];
            // paragraph styles
            $parDef = ['spaceBefore' => 0, 'spaceAfter' => 120, 'spacingLineRule' => 'auto', 'lineHeight' => 1];
            $parLeft = ['align' => 'left', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacingLineRule' => 'auto', 'lineHeight' => 1];
            $parRight = ['align' => 'right', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacingLineRule' => 'auto', 'lineHeight' => 1];
            $parCenter = ['align' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacingLineRule' => 'auto', 'lineHeight' => 1];
            // cell styles
            $cell = ['valign'=>'center'];
            $cellBottom = ['valign' => 'center','borderBottomSize' => 6];
            $cellTop = ['valign' => 'center','borderTopSize' => 6];
            $cellGrey = ['valign'=>'center','bgColor'=>'#E2E2E2'];
            $cellTopGrey = ['valign'=>'center','borderTopSize'=>6,'bgColor'=>'#E2E2E2'];
            
            // sizes
            $rowTitleHeight = 500;
            $rowHeight = 350;
            $col1 = 2500;
            $col2 = 4000;
            $col3 = 1000;
            $col4 = 1000;
            $col5 = 800;

            // Títol del document
            $sect->addText('Llistat comanda '.date_format(date_create($fecha),"d/m/Y"), $Arial14bold, $parDef);
            $sect->addText('Unitats de Convivència: '.$nuc, $Arial14, $parDef);
            $text = $sect->addText('**Els totals no inclouen alguns productes de preu variable', 
                $Arial10, $parDef);
            
            $count = 1;
            while ($r = mysqli_fetch_array($ucs)) {
                $uf = $r["uf"];

                // obtenir comandes per cada UC
                $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
                $stmt->bind_param('is', $uf, $fecha);
                $stmt->execute();
                $com = $stmt->get_result();
                $nitems = $com->num_rows;

                 // comprovar si hi ha preus buits
                $nota = "";
                while ($i = mysqli_fetch_array($com)) {
                    if (getascurr($i["precio"],"€")=="") $nota = "**";
                }
                mysqli_data_seek($com, 0);      // situar el cursor a l'inici de les dades

                // afegir text amb contador. nom UC, total i nota
                $sect->addTextBreak(1);     // canvi de línia abans de cada UC
                $sect->addText($count.'. '.$r["descrip"].': '.gettotal($conn,$uf,$fecha).$nota, 
                    $Arial12bold, $parDef);
                
                // insertar taula, definint les vores superiors e inferiors
                $table = $sect->addTable(['layout' => 'fixed', 'borderColor' => '000000', 
                    'borderTopSize' => 6, 'borderBottomSize' => 6]);
                // afegir fila de títols
                $table->addRow($rowTitleHeight);
                $table->addCell($col1,$cellBottom)->addText("Productora",$Arial10bold,$parLeft);
                $table->addCell($col2,$cellBottom)->addText("Producte",$Arial10bold,$parLeft);
                $table->addCell($col3,$cellBottom)->addText("Quantitat",$Arial10bold,$parCenter);
                $table->addCell($col4,$cellBottom)->addText("Preu",$Arial10bold,$parRight);
                $table->addCell($col5,$cellBottom)->addText("Total",$Arial10bold,$parRight);

                // omplir la taula
                $g0 = 0;
                while ($i = mysqli_fetch_array($com)) {
                    $precio = ($i["desact"]==1 ? "ANUL·LAT" : getascurr($i["precio"],"€"));
                    $total = ($i["desact"]==1 ? "0" : getascurr($i["total"],"€"));

                    // decidir si la cel·la ha de tenir vora i color de fons
                    $cellSt = null;
                    if (($i["cgrupo"] <> $g0 && $g0>0)) {
                        if ($precio == "" || $precio == "ANUL·LAT") $cellSt = $cellTopGrey;
                        else $cellSt = $cellTop;
                    } else {
                        if ($precio == "" || $precio == "ANUL·LAT") $cellSt = $cellGrey;
                        else $cellSt = $cell;
                    }

                    // afegir dades fila a fila
                    $table->addRow($rowHeight);
                    $table->addCell($col1,$cellSt)->addText($i["dgrupo"],$Arial10,$parLeft);
                    $table->addCell($col2,$cellSt)->addText($i["item"],$Arial10,$parLeft);
                    $table->addCell($col3,$cellSt)->addText($i["n"],$Arial10,$parCenter);
                    $table->addCell($col4,$cellSt)->addText($precio,$Arial10,$parRight);
                    $table->addCell($col5,$cellSt)->addText($total,$Arial10,$parRight);
                    
                    $g0 = $i["cgrupo"];
                }
                // contador UC
                $count++;
            }

             // generar el docx i descarregar-lo
            header('Content-Type: application/msword');
            header('Content-Disposition: attachment;filename="llistat_'.$fecha.'.docx"');
            header('Cache-Control: max-age=0');
            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007');
            $writer->save('php://output');

            $ucs->free();
        }
    } catch (Exception $e) {
        echo 'Message: ' .$e->getMessage();
    }
    $conn->close();
} else {
    header("Location: logout.php");
}
?>
