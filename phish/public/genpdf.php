<?php
session_start();
define('DROOT','../');
define('XROOT','../../');
require_once '../settings.inc.php';

global $UID;
$UID = (isset($_SESSION['UID'])!='')?$_SESSION['UID']:'';
global $USERINFO;
$USERINFO = $st->getUserInfo($UID);

//var_dump($_REQUEST);

try{
	$RTYPE = (trim($_REQUEST['typ'])!='')?$st->decrypt($_REQUEST['typ']):'';
	$OPTS = (trim($_REQUEST['pg'])!='')?$st->decrypt($_REQUEST['pg']):'';
}catch (Exception $e){
	header('Location: error/404/'.$st->encrypt('false'));
}



$DCID=explode('#',$OPTS);

$DTS=trim($DCID[0]);
$CID=trim($DCID[1]);
$STATUS = (trim($DCID[2])!='') ? $st->encrypt(trim($DCID[2])): '';
$OTH = (trim($DCID[3])!='') ? $st->encrypt(trim($DCID[3])): '';
$TITLE = (trim($DCID[4])!='') ? $st->decrypt(trim($DCID[4])): '';
$CHT = (trim($DCID[5])=='yes') ? true : false;
$FORMAT = (trim($DCID[6])!='') ? trim($DCID[6]) : '';

//var_dump($DCID,$FORMAT); die;

$HDRS=array('OVERALL_ANALYSIS'=>'Overall Details', 'TOTAL_TEST'=>'Test Synopsis', 'TEST_ANALYSIS'=>'Testing Report', 'DOMAIN_TEST_ANALYSIS'=>'Domain wise Test report', 'GROUP_TEST_ANALYSIS'=>'Group wise Test report', 'USER_EVENT_TEST_ANALYSIS'=>'Event wise Test report');
$HDRS_PRINT=array(
		'OVERALL_ANALYSIS'=>'Overall Details',
		'TOTAL_TEST'=>'Test Synopsis',
		'TEST_STATUS'=>'Test Synopsis',
		'TEST_ANALYSIS'=>'Testing Report',
		'DOMAIN_TEST_ANALYSIS'=>'Domain wise Test report',
		'GROUP_TEST_ANALYSIS'=>'Group wise Test report',
		'USER_EVENT_TEST_ANALYSIS'=>'Event wise Test report',
		'TOTAL-CLIENTS'=>'Clients',
		'ACTIVE-GROUPS'=>'Groups',
		'TOTAL-TESTS'=>'Tests',
		'TOTAL-TARGETS'=>'Targets',
);
switch(trim($RTYPE)){
	case "OVERALL_ANALYSIS":
		$REPORT_DATA = $st->showDBDataInfo("OVERALL_ANALYSIS",$USERINFO,$DTS,trim($CID), false, $STATUS, $OTH,$TITLE,$CHT);
	break;
	case "TOTAL_TEST":
		$REPORT_DATA = $st->showDBDataInfo("TOTAL_TEST",$USERINFO,$DTS,trim($CID), false, $STATUS, $OTH,$TITLE,$CHT);
	break;
	case "TEST_ANALYSIS":
		$REPORT_DATA = $st->showDBDataInfo("TEST_ANALYSIS",$USERINFO,$DTS,trim($CID), false, $STATUS, $OTH,$TITLE,$CHT);
	break;
	case "DOMAIN_TEST_ANALYSIS":
		$REPORT_DATA = $st->showDBDataInfo("DOMAIN_TEST_ANALYSIS",$USERINFO,$DTS,trim($CID), false, $STATUS, $OTH,$TITLE,$CHT);
	break;
	case "GROUP_TEST_ANALYSIS":
		$REPORT_DATA = $st->showDBDataInfo("GROUP_TEST_ANALYSIS",$USERINFO,$DTS,trim($CID), false, $STATUS, $OTH,$TITLE,$CHT);
	break;
	case "USER_EVENT_TEST_ANALYSIS":
		$REPORT_DATA = $st->showDBDataInfo("USER_EVENT_TEST_ANALYSIS",$USERINFO,$DTS,trim($CID), false, $STATUS, $OTH,$TITLE,$CHT);
	break;
	default:
		$REPORT_DATA = $st->showDBDataInfo($RTYPE,$USERINFO,$DTS,trim($CID), false, $STATUS, $OTH,$TITLE,$CHT);
	break;
}

$pattern = '/<a.*?>(.*?)<\/a>/si';
$replacement = '$1';
$REPORT_DATA = preg_replace($pattern, $replacement, $REPORT_DATA);

if(trim($FORMAT)=='PDF'){
	ob_start();
	echo $st->addAllScripts();
	echo $st->addAllStyles(); ?>
	<style type="text/css">
		/*@import url(https://fonts.googleapis.com/css?family=PT+Sans:400,700,700italic,400italic);@font-face{font-family:'proxima_novalight';src:url('../assets/fonts/proximanovalight-webfont.eot');src:url('../assets/fonts/proximanovalight-webfont.eot?#iefix') format('embedded-opentype'),
		url('../assets/fonts/proximanovalight-webfont.woff2') format('woff2'),
		url('../assets/fonts/proximanovalight-webfont.woff') format('woff'),
		url('../assets/fonts/proximanovalight-webfont.ttf') format('truetype'),
		url('../assets/fonts/proximanovalight-webfont.svg#proxima_novalight') format('svg');font-weight:normal;font-style:normal;}*/
		html{font-family:sans-serif; font-size:16px; -webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
		#header { margin: 0; padding: 0; position: fixed; top: -70px;  width: 100%; background: #f1f1f1; z-index: 1000; }
		#footer { margin: 0; padding: 2px 10px 2px 10px; position: fixed; bottom: -20px; font-size: 90%; font-weight: bold; width: 100%; background: #fff; height:20px; z-index: 1001; }
		#header, #footer { clear: both; float: none; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.3); -moz-box-shadow: 0 2px 3px rgba(0, 0, 0, 0.3); -webkit-box-shadow: 0 2px 3px rgba(0, 0, 0, 0.3); background: #D32F2F; color:#FFF;}
		h2 { text-align:center; font-size:110%; }
		.table-striped>tbody>tr:nth-of-type(odd) { background-color: #f9f9f9; }
		table.dataTable tbody tr { background-color: #ffffff; }
		.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {			padding: 15px 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #ddd; text-align:left; }
		@page { margin: 70px 0 20px 0; }
		.copy_lt { float:left; text-align:left; margin:0; padding:0; }
		#footer a { color:#f2f2f; }
		.foot_rt { float:right; text-align: right;  margin:0; padding:0; }
		.tbl-hint{margin:5px 5px 15px 5px; padding:0;}
	</style>
	<div id="header">
		<div id="logo">
			<?php echo preg_replace('/\.\.\/\.\.\//','',$st->getLogo(true));?>
			<hr/>
		</div>
    	</div>
	<div id="footer">
		<div class="copy_lt">Copyright © <a href="http://www.trushieldinc.com/">trushield inc.</a>, All rights reserved.</div><div class="foot_rt">Call Us: +1-678-5234534, &nbsp;&nbsp;&nbsp;Email Us: info@truphish.com</div>
	</div>
	<?php  echo $st->addScripts('includes/chart/'); ?>
	<div class="contents">
		<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
		<?php if($CHT==true){ ?>
		<div class="frm" style="page-break-after: always;">
			<canvas id="canvas" style="display: none;"></canvas>
			<div style="text-align:center; width:100%;">
				<img id="chtReport" src="<?php echo 'uploads/chart.png';?>" />
			</div>
		</div>
		<?php } ?>
	</div>
			
	<div class="frm page">
		<div title="Report Details">
			<?php if($CHT==false) echo '<h2>'.$TITLE.'</h2>'; ?>
			<p><?php echo $REPORT_DATA;  ?></p>
		</div>
	</div>
	<?php require_once '../'.$st->IncludePath.'tpdf/autoload.inc.php';
	$dompdf = new Dompdf\Dompdf();
	$dompdf->load_html(ob_get_clean());
	$dompdf->set_paper("a4", "landscape" );
	$dompdf->render();
	$dompdf->stream(strtolower(trim($RTYPE)).'.pdf');
	$output = $dompdf->output();
}elseif(trim($FORMAT)=='EXL'){
	header('Content-Disposition: attachment; filename="'.strtolower(trim($RTYPE)).'.xls";');
	header('Content-type: application/vnd.ms-excel');
	echo $REPORT_DATA;
}elseif(trim($FORMAT)=='CSV'){
	$CSV = new DOMDocument();
	$internalErrors = libxml_use_internal_errors(true);
	$CSV->loadHTML($REPORT_DATA);
	$rows = $CSV->getElementsByTagName('tr');
	//var_dump($RTYPE); exit;
	header('Content-Disposition: attachment; filename="'.strtolower(trim($RTYPE)).'.csv";');
	header('Content-type: application/vnd.ms-excel');
	$fp = fopen("php://output","w");
	foreach($rows as $row){
		$vals = array();
		foreach($row->childNodes as $cell){
			$vals [] = $cell->textContent;
		}
		fputcsv($fp,$vals);
	}
	fclose($fp);
}elseif(trim($FORMAT)=='XML'){
	$data=array();
	$doc = new DOMDocument();
	$internalErrors = libxml_use_internal_errors(true);
	$doc->loadHTML($REPORT_DATA);
	$hdr = array();
	//var_dump($hdr);
	$rows = $doc->getElementsByTagName('tr');
	$xml= simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><xml/>');
	$x=0;
	foreach($rows as $row){
		$i = 0;
		if($x>0) $node = $xml->addChild('section');	
		foreach($row->childNodes as $cell){
			if($x==0){
				$hdr [] = str_replace('#','Serial',str_replace(' ','_',$cell->textContent));
			}else{				
				$node->addChild($hdr[$i], $cell->textContent);
				$i++;
			}
		}
		$x++;
	}
	header('Content-Disposition: attachment; filename="'.strtolower(trim($RTYPE)).'.xml";');
	header('Content-type: text/xml');
	echo $xml->asXML();
}
?>
