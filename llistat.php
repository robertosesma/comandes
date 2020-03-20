<?php
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

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

    // get UCs
    $stmt = $conn -> prepare("SELECT uf, descrip FROM comanda WHERE fecha =? GROUP BY uf");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $ucs = $stmt->get_result();
    $nuc = $ucs->num_rows;

    $spreadsheet = new Spreadsheet();
    // Set document properties
    $spreadsheet->getProperties()->setCreator('la coope')
        ->setTitle('Llistat comanda '.$fecha)
        ->setDescription('Llistat per Unitat de Convivència');
    // Rename worksheet
    $spreadsheet->getActiveSheet()->setTitle('Llistat');
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $spreadsheet->setActiveSheetIndex(0);

    // Title
    $spreadsheet->getActiveSheet()
        ->setCellValue('A1', 'Comanda '.$fecha)
        ->setCellValue('A2', 'Unitats de Convivència: '.$nuc);

    // custom style with different bold and size
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
    $spreadsheet->getActiveSheet()->getStyle('A1:D2')->applyFromArray($sHeader);
    $spreadsheet->getActiveSheet()->mergeCells('A1:D1');
    $spreadsheet->getActiveSheet()->mergeCells('A2:D2');

    $count = 1;
    $row = 4;
    while ($r = mysqli_fetch_array($ucs)) {
        $uf = $r["uf"];
        $uctotal = gettotal($conn,$uf,$fecha);

        $spreadsheet->getActiveSheet()->setCellValue('A'.$row, $count.'. '.$r["descrip"].': '.$uctotal);
        $spreadsheet->getActiveSheet()->getStyle('A'.$row.':D'.$row)->applyFromArray($sUC);
        $spreadsheet->getActiveSheet()->mergeCells('A'.$row.':D'.$row);

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

        $stmt = $conn -> prepare("SELECT * FROM comanda WHERE fecha =? AND uf=?");
        $stmt->bind_param('si', $fecha, $uf);
        $stmt->execute();
        $items = $stmt->get_result();
        $nitems = $items->num_rows;
        $g0 = 0;
        while ($i = mysqli_fetch_array($items)) {
            if ($g0>0 && $g0<>$i["cgrupo"]) {
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
        // insert last SubTotal
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
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->SetAutoSize(true);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->SetAutoSize(true);
    $spreadsheet->getActiveSheet()->calculateColumnWidths();

    // print configuration
    $spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $spreadsheet->getActiveSheet()->getPageMargins()->setTop(1);
    $spreadsheet->getActiveSheet()->getPageMargins()->setRight(1);
    $spreadsheet->getActiveSheet()->getPageMargins()->setLeft(1);
    $spreadsheet->getActiveSheet()->getPageMargins()->setBottom(1);

    // Create Excel file and download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Comanda_'.$fecha.'.xls"');
    header('Cache-Control: max-age=0');
    $writer = IOFactory::createWriter($spreadsheet, 'Xls');
    $writer->save('php://output');
}
exit();
?>
