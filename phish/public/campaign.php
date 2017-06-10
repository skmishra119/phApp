<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//var_dump($USERINFO);
if(trim($USERINFO['userid'])==''){
	header("Location:./");
}else{
	$WHR="clientid='".trim($USERINFO['userid'])."' and process not in ('TESTRUN','COMPLETED')";
	
	if($st->getUserSettingsByParams('TESTS', 'MAXTESTS', trim($USERINFO['userid']))==false){
		unset($_SESSION['TEST']);
		header("Location:showmsg/error/".$st->encrypt('TESTING_ERROR')); exit;
	}elseif(isset($_REQUEST['pg'])!=""){ 
		if($st->decrypt(trim($_REQUEST['pg']))=="XXXX") unset($_SESSION['TEST']); else $_SESSION['TEST']['PROCESS']=$st->decrypt(trim($_REQUEST['pg']));
		header("Location:campaign");
	}
	$TEST = (isset($_SESSION["TEST"])!="") ?  $_SESSION["TEST"] : array("PROCESS"=>"START");
}
$PHEAD='';
$PGSX='';
/**/
//var_dump($_SESSION);
//exit;

switch(strtoupper(trim($TEST['PROCESS']))){
	case "START":
		$PHEAD="Start Test";
		$PGSX='start_test.inc.php';
		break;
	case "DOMAIN":
		$PHEAD="Select Domain";
		$PGSX='seldomain.inc.php';
		break;
	case "GTARGET":
		$PHEAD="Select Target";
		$PGSX='gettarget.inc.php';
		break;
	/*case "GROUP":
		$PHEAD="Select Group";
		$PGSX='selgroup.inc.php';
		break;*/
	case "SLTARGET":
		$PHEAD="Select Target";
		$PGSX='seltarget.inc.php';
		break;
	case "ADTARGET":
		$PHEAD="Add Target";
		$PGSX='addtarget.inc.php';
		break;
	case "UPTARGET":
		$PHEAD="Upload Target from CSV";
		$PGSX='upltarget.inc.php';
		break;
	case "TEMPLATE":
		$PHEAD="Select Template";
		$PGSX='seltemplate.inc.php';
		break;
	case "EMAIL":
		$PHEAD="Email Form";
		$PGSX='selemail.inc.php';
		break;
	/*case "VERIFY":
		$PHEAD="Verify Email";
		$PGSX='selverify.inc.php';
		break;
	case "VERISENT":
		$PHEAD="Check Your Mailbox";
		$PGSX='verifysent.inc.php';
		break;*/
	case "SCHEDULE":
		$PHEAD="Schedule";
		$PGSX='selschedule.inc.php';
		break;
	case "AUTHTEST":
		$PHEAD="Authorize";
		$PGSX='selauthorize.inc.php';
		break;
	case "ENDTEST":
		$PHEAD="End Test";
		$PGSX='selauthsent.inc.php';
		break;
	default:
		header("Location: ./");
		break;
}
//$ERR=array('main'=>' Your contents will be displayed here.');
//echo $st->addChartScript("GROUP-MLIST", trim($USERINFO['userid']), "charting", "Sample Chart", "Members: <b>{point.y}</b> ({point.percentage:.1f}%)");
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$('.progress_bar li span.number').hover(function(){
		$(this).closest('li').find('p.tooltip').stop().fadeIn(200);
	}, function(){
		$(this).closest('li').find('p.tooltip').stop().fadeOut(200);
	});
});
//-->
</script>
<?php echo $st->addScripts('includes/chart/'); ?>
<div id="container">

	
		<?php require_once $PGSX; ?>
     
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>