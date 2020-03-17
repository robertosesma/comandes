<?php
header('Content-Type: text/rtf');
header('Content-Disposition: attachment; filename="sample.rtf"');

require_once 'PHPRtfLite.php';

// registers PHPRtfLite autoloader (spl)
PHPRtfLite::registerAutoloader();
// rtf document instance
$rtf = new PHPRtfLite();

// add section
$sect = $rtf->addSection();
// write text
$sect->writeText('Hello world!', new PHPRtfLite_Font(), new PHPRtfLite_ParFormat());

// save rtf document 
$rtf->save('php://output');

echo "Hola";
?>
