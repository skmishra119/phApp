<?php
try{
	session_start();
	define('DROOT','./');
	if(isset($_SESSION['AID'])!=''){ unset($_SESSION['AID']); session_destroy(); }
	require_once '../settings.inc.php';
	$st->setMetas("name", "TruShield Security Solutions");
	require_once '../'.$st->IncludePath.'header.inc.php';
}catch (Exception $e){
	header('Location: error/404/'.$st->encrypt('true'));
}
$ERR=array('main'=>' You have signed out successfully. <a href="./">Click here</a> to sign in again.');
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	
});
//-->
</script>
<div id="container">
	<div class="login">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-sign-out fa-fw"></i> Signed Out</div>
			<div class="indicator"></div>
			<div class="clr"></div>
		</div>
		<div class="frm signout" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-success ui-corner-all" style="padding: 0 .7em;"><p>'.$ERR['main'].'</p></div></div>':''; ?></div>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>