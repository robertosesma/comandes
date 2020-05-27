<?php
session_start();

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
include 'func_aux.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username'])) {
    $conn = connect();

    // obtenir les dades de totes les comandes
    $stmt = $conn -> prepare("SELECT * FROM comanda");
    $stmt->execute();
    $data = $stmt->get_result();
    $nrows = $data->num_rows;
    // si hi ha productes
    if ($nrows>0) {
        // crear l'arxiu
        $spreadsheet = new Spreadsheet();
        // les dades van a la primera fulla
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('dades');                  // nom de la fulla
        // capçalera
        $sheet->setCellValue('A1', 'data')
            ->setCellValue('B1', 'UC')
            ->setCellValue('C1', 'productora')
            ->setCellValue('D1', 'producte')
            ->setCellValue('E1', 'n')
            ->setCellValue('F1', 'preu')
            ->setCellValue('G1', 'total');

        // omplir les dades fila a fila
        $i = 2;
        while ($d = mysqli_fetch_array($data)) {
            $sheet->setCellValue('A'.$i, $d['fecha'])
                ->setCellValue('B'.$i, $d['descrip'])
                ->setCellValue('C'.$i, $d['dgrupo'])
                ->setCellValue('D'.$i, $d['item'])
                ->setCellValue('E'.$i, $d['n'])
                ->setCellValue('F'.$i, $d['precio'])
                ->setCellValue('G'.$i, $d['total']);
            $i++;
        }

        // exportar com a xls
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="dades_comandes.xls"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    } else {
        echo "<h1>No hi ha dades</h1>";
    }
} else {
    header("Location: logout.php");
}
exit();
?>
