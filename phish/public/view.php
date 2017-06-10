<?php
define('DROOT','./');
define('XROOT','../../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';

if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:../../");

$ERR=array();

try{
$OUTPUT_DATA = $st->showInformation(strtoupper($_REQUEST['typ']), $st->decrypt($_REQUEST['pg']), true);
if(sizeof($st->errors)>0)
	header('Location: error/404/'.$st->encrypt('false'));
}catch (Exception $e){
	header('Location: error/404/'.$st->encrypt('false'));
}
//var_dump($_REQUEST,$_GET);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	
});
//-->
</script>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3><?php echo ucfirst($_REQUEST['typ']); ?></h3><div class="pgtools"><?php echo $st->showPageToolBox(strtoupper($_REQUEST['typ']),'VIEW',$USERINFO['role'],false); ?></div></div>
	<div class="main_content">
		<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
		<div class="frm">
			<div class="grid">
				<form id="frmTools" method="post" action="<?php echo XROOT.substr(strtolower($_REQUEST['typ']),0,strlen($_REQUEST['typ'])-1); ?>">
					<?php echo $OUTPUT_DATA; ?>
					<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print(trim($_REQUEST['pg'])); ?>" />
					<input type="hidden" id="hdnMode" name="hdnMode" />
				</form>
			</div>
		</div>	
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>