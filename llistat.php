<?php
session_start();

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
include 'func_aux.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $fecha = $_GET["fecha"];
    $conn = connect();

    // obtenir llista d'UC
    $stmt = $conn -> prepare("SELECT uf, descrip FROM comanda WHERE fecha =? GROUP BY uf ORDER BY descrip");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $ucs = $stmt->get_result();
    $nuc = $ucs->num_rows;

    if ($nuc>0) {
        $spreadsheet = new Spreadsheet();
        // propietats del document xls
        $spreadsheet->getProperties()->setCreator('la coope')
            ->setTitle('Llistat comanda '.$fecha)
            ->setDescription('Llistat per Unitat de Convivència');
        // nom de la fulla
        $spreadsheet->getActiveSheet()->setTitle('Llistat');
        // la fulla de càlcul activa és la primera (l'única)
        $spreadsheet->setActiveSheetIndex(0);

        // títol
        $spreadsheet->getActiveSheet()
            ->setCellValue('A1', 'Comanda '.$fecha)
            ->setCellValue('A2', 'Unitats de Convivència: '.$nuc);

        // estils amb diferent tamany i en negreta
        $sHeader = array(
        'font'  => array(
            'bold' => true,
            'size'  => 14,
        ));
        $sUC = array(
        'font'  => array(
            'bold' => true,
            'size'  => 12,
        ));
        // capçaleres
        $spreadsheet->getActiveSheet()->getStyle('A1:E2')->applyFromArray($sHeader);
        $spreadsheet->getActiveSheet()->mergeCells('A1:E1');
        $spreadsheet->getActiveSheet()->mergeCells('A2:E2');

        $count = 1;
        $row = 4;       // fila d'inici després de les capçaleres
        while ($r = mysqli_fetch_array($ucs)) {
            $uf = $r["uf"];
            $uctotal = gettotal($conn,$uf,$fecha);

            // títol UC + total
            $spreadsheet->getActiveSheet()->setCellValue('A'.$row, $count.'. '.$r["descrip"].': '.$uctotal);
            $spreadsheet->getActiveSheet()->getStyle('A'.$row.':E'.$row)->applyFromArray($sUC);
            $spreadsheet->getActiveSheet()->mergeCells('A'.$row.':E'.$row);

            // capçalera de productes
            $row++;
            $spreadsheet->getActiveSheet()
                ->setCellValue('A'.$row, "Productor")
                ->setCellValue('B'.$row, "Producte")
                ->setCellValue('C'.$row, "Quantitat")
                ->setCellValue('D'.$row, "Preu")
                ->setCellValue('E'.$row, "Total");
            $spreadsheet->getActiveSheet()->getStyle('A'.$row.':E'.$row)->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('A'.$row.':E'.$row)
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
            $row++;
            // productes
            $stmt = $conn -> prepare("SELECT * FROM comanda WHERE fecha =? AND uf=?");
            $stmt->bind_param('si', $fecha, $uf);
            $stmt->execute();
            $items = $stmt->get_result();
            $nitems = $items->num_rows;
            $g0 = 0;
            while ($i = mysqli_fetch_array($items)) {
                if ($g0>0 && $g0<>$i["cgrupo"]) {
                    // subtotals per productor en canviar de productor
                    $subtotal = getsubtotal($conn,$uf,$fecha,$g0);
                    $spreadsheet->getActiveSheet()
                        ->setCellValue('D'.$row, "SubTotal")
                        ->setCellValue('E'.$row, $subtotal);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$row)->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getFont()->setBold(true);
                    $row++;
                }
                $g0 = $i["cgrupo"];
                // detall de productes demanats
                $preu = ($i["precio"]==NULL ? '' : $i["precio"]);
                $tot = ($i["total"]==NULL ? '' : $i["total"]);
                $spreadsheet->getActiveSheet()
                    ->setCellValue('A'.$row, $i["dgrupo"])
                    ->setCellValue('B'.$row, $i["item"])
                    ->setCellValue('C'.$row, $i["n"])
                    ->setCellValue('D'.$row, $preu)
                    ->setCellValue('E'.$row, $tot);
                $spreadsheet->getActiveSheet()
                        ->getStyle('D'.$row.':E'.$row)->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

                $row++;
            }
            // l'últim subtotal s'ha d'afegir en sortir de la UC
            $subtotal = getsubtotal($conn,$uf,$fecha,$g0);
            $spreadsheet->getActiveSheet()
                ->setCellValue('D'.$row, "SubTotal")
                ->setCellValue('E'.$row, $subtotal);
            $spreadsheet->getActiveSheet()->getStyle('D'.$row)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getFont()->setBold(true);

            $count++;
            $row = $row + 2;
        }
        // ample columnes
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->SetAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->SetAutoSize(true);
        $spreadsheet->getActiveSheet()->calculateColumnWidths();

        // configuració d'impressió
        $spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $spreadsheet->getActiveSheet()->getPageMargins()->setTop(1);
        $spreadsheet->getActiveSheet()->getPageMargins()->setRight(1);
        $spreadsheet->getActiveSheet()->getPageMargins()->setLeft(1);
        $spreadsheet->getActiveSheet()->getPageMargins()->setBottom(1);

        // crear l'arxiu xls i descarregar
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Comanda_'.$fecha.'.xls"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    }
} else {
    header("Location: logout.php");
}
exit();
?>
