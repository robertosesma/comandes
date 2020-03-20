<?php
session_start();

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
include 'func_aux.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $fecha = clear_input($_GET["fecha"]);
    $conn = connect();
    // get resum PA
    $stmt = $conn -> prepare("SELECT * FROM resumen WHERE fecha = ? AND cgrupo = 4");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $data = $stmt->get_result();
    $nrows = $tipo->num_rows;
    if ($nrows>0) {
        $spreadsheet = IOFactory::load('PA.xls');

        $spreadsheet->getActiveSheet()->setCellValue('D2', $fecha);
        while ($d = mysqli_fetch_array($data)) {
            $row = $d["fila"];
            $n = $d["n"];
            $spreadsheet->getActiveSheet()->setCellValue('F'.$row, $n);
        }

        // Create Excel file and download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PA_'.$fecha.'.xls"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    }
}
exit();
?>
