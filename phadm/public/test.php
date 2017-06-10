<?php
ini_set('magic_quotes_runtime', 0);
include_once '../settings.inc.php';
require_once '../includes/dpdf/dompdf_config.inc.php';

$dompdf = new DOMPDF();
$html = <<<'ENDHTML'
<html>
 <body>
  <h1>Hello Dompdf</h1>
  <img src="../assets/refresh.gif"/>
 </body>
</html>
ENDHTML;
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream("hello.pdf");