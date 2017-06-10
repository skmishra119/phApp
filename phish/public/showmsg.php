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
		$PGMSG='Thank you for initiating forgot password request. An email has been sent to your inbox with reset password link. Check your mailbox to reset your password. <a href="'.XROOT.'">Click here</a> to login again.';
	break;
	case "RESET_PASSWORD":
		if(trim($_REQUEST['typ'])=="error"){
			$PGTYPE="Reset Password";
			$PGMSG='The link has been expired or you have already reset your password earlier by using this link. <a href="'.XROOT.'">Click here</a> to login again.';
		}elseif(trim($_REQUEST['typ'])=="success"){
			$PGTYPE="Reset Password";
			$PGMSG='Congratulations!!!, You have successfully changed your password. Use this password to access your account. <a href="'.XROOT.'">Click here</a> to login again.';
		}else{
			header('Location: '.DROOT.XROOT.'error/404/'.$st->encrypt('false'));
		}
	break;
	case "AUTH_TEST":
		if(trim($_REQUEST['typ'])=="error"){
			$PGTYPE="Test Authorization";
			$PGMSG='The link has been expired or you have already authorized this test earlier by using this link. <a href="'.XROOT.'">Click here</a> to login again.';
		}elseif(trim($_REQUEST['typ'])=="success"){
			$PGTYPE="Test Authorization";
			$PGMSG='The test has been authorized. <a href="'.XROOT.'">Click here</a> to login again.';
		}else{
			header('Location: '.DROOT.XROOT.'error/404/'.$st->encrypt('false'));
		}
	break;
	case "TESTING_ERROR":
		if(trim($_REQUEST['typ'])=="error"){
			$PGTYPE="Testing";
			$PGMSG='Either you have exceeded the test limit or test limit for you was not defined in the system. Contact Administrator. <a href="'.XROOT.'">Click here</a> to login again.';
		}else{
			header('Location: '.DROOT.XROOT.'error/404/'.$st->encrypt('false'));
		}
	break;
	case "REGISTER_NEW":
		if(trim($_REQUEST['typ'])=="error"){
			$PGTYPE="Register";
			$PGMSG='The link has been expired or you have already reset your password earlier by using this link. <a href="'.XROOT.'">Click here</a> to login again.';
		}elseif(trim($_REQUEST['typ'])=="success"){
			$PGTYPE="Register";
			$PGMSG='Thank you for register. We are reviewing your request. One of our executive will contact you shortly.';
		}else{
			header('Location: '.DROOT.XROOT.'error/404/'.$st->encrypt('false'));
		}
	break;
	default:
		header('Location: '.DROOT.XROOT.'error/404/'.$st->encrypt('false'));
	break;
}

$ERR=array();
//var_dump($_REQUEST,$_GET);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	
});
//-->
</script>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3><?php echo $PGTYPE ?></h3></div>
	<div class="main_content">
		<?php if(trim($_REQUEST['typ'])=="success"){ ?>
			<div id="msg"><?php echo (isset($PGMSG)!='') ? '<div class="alert alert-success">'.$PGMSG.'</div>':''; ?></div>
		<?php }else{ ?>
			<div id="msg"><?php echo (isset($PGMSG)!='') ? '<div class="alert alert-danger">'.$PGMSG.'</div>':''; ?></div>
		<?php } ?>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>