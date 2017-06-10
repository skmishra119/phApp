<?php
error_reporting(0);
define('DROOT','./');
define('XROOT', '../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//if(trim($USERINFO['userid'])!='') header("Location: dashboard");
$HLP_PG = (isset($_REQUEST['pg'])!='')?trim($_REQUEST['pg']):'index';
//echo $cr->encrypt('Sushami1a').' XX '.$cr->decrypt('d0f23993eadb4684904a634d97e24ff1');
$ERR=array();

?>
<script type="text/javascript">
<!--
$(document).ready(function(){
});
//-->
</script>
<div id="container">
	<div class="contents">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-question fa-fw"></i> Help</div>
			<div class="indicator"></div>
			<div class="clr"></div>
		</div>
		<div class="contents help_cont">	
			<div class="helplink"><?php echo $st->showHelpLinks($USERINFO); ?></div>
			<div class="helpcontents"><?php echo $st->showHelpContents($_REQUEST,$USERINFO);?>	</div>
			<div class="clr"></div>
		</div>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>