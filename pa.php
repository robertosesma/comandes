<?php
session_start();

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
include 'func_aux.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $fecha = clear_input($_GET["fecha"]);
    $conn = connect();
    // obtenir el resum del productor 4 (Fleca Roca - PA)
    // aquest productor necessita una fulla d'excel amb un format concret, segons la plantilla PA.xls
    $stmt = $conn -> prepare("SELECT * FROM resumen WHERE fecha = ? AND cgrupo = 4");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $data = $stmt->get_result();
    $nrows = $data->num_rows;
    // si hi ha productes per aquesta comanda
    if ($nrows>0) {
        // la plantilla està al servidor
        $spreadsheet = IOFactory::load('PA.xls');

        $spreadsheet->getActiveSheet()->setCellValue('D2', $fecha);
        while ($d = mysqli_fetch_array($data)) {
            $row = $d["fila"];
            $n = $d["n"];
            $spreadsheet->getActiveSheet()->setCellValue('F'.$row, $n);
        }

        // crear l'arxiu d'excel i descarregar
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PA_'.$fecha.'.xls"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    } else {
        echo "<h1>No hi ha comanda de pa per aquesta data</h1>";
    }
} else {
    header("Location: logout.php");
}
exit();
?>
