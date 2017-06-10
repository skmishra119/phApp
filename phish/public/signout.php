<?php
try{
session_start();
define('DROOT','./');
if(isset($_SESSION['UID'])!=''){ unset($_SESSION['UID']); session_destroy(); }
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
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Signed Out</h3></div>
	<div class="main_content">
		<div class="alert alert-success" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-success ui-corner-all" style="padding: 0 .7em;"><p>'.$ERR['main'].'</p></div></div>':''; ?></div>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>