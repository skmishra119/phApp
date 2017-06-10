<?php
define('DROOT','./');
define('XROOT','../../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//var_dump($USERINFO);
//if(trim($USERINFO['userid'])!='' || trim($USERINFO['role'])=='CLIENT') header("Location:./");
$PGX=$st->decrypt(trim($_REQUEST['pg']));
$PGTYPE='';
$PGMSG='';
switch(trim($PGX)){
	case "FORGOT_PASSWORD":
		$PGTYPE='Forgot Password';
		$PGMSG='Thank you for initiating forgot password request. An email has been sent to your inbox with reset password link. Check your mailbox to reset your password.<p><a href="'.XROOT.'">Click here</a> to login again.</p>';
		break;
	case "RESET_PASSWORD":
		if(trim($_REQUEST['typ'])=="error"){
			$PGTYPE="Reset Password";
			$PGMSG='Error: The link has been expired or you have already reset your password earlier by using this link.<p><a href="'.XROOT.'">Click here</a> to login again.</p>';
		}elseif(trim($_REQUEST['typ'])=="success"){
			$PGTYPE="Reset Password";
			$PGMSG='Congratulations!!!, You have successfully changed your password.  Use this password to access your account.<p><a href="'.XROOT.'">Click here</a> to login again.</p>';
		}
		break;
	case "404":
		$PGTYPE="Error 404";
		$PGMSG='Error: The resource your are trying to access has not been found. Please check the url and try again later';
		break;
}

$ERR=array();
var_dump($_REQUEST,$_GET);
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
			<div class="frmname"><?php echo $PGTYPE ?></div>
			<div class="pgtools"></div>
			<div class="clr"></div>
		</div>
		<div id="msg"><?php echo (isset($PGMSG)!='') ? '<div class="frm ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:  </strong>'.$PGMSG.'</p></div></div>':''; ?></div>
			
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>