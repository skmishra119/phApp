<?php
define('DROOT','./');
define('XROOT','../../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
error_reporting(0);
$ECD=$_REQUEST['typ'];
$PGS=$st->decrypt($_REQUEST['pg']);
//var_dump($st->encrypt('true'),$st->encrypt('false'));
if($PGS=='true' && isset($_SESSION['AID'])){
	unset($_SESSION['AID']);
	session_destroy();
}
require_once '../'.$st->IncludePath.'header.inc.php';
$ERR=array();
$st->getError('main',$st->ErrDesc[$ECD]);
$ERR = $st->errors;
//var_dump($ERR);
?>
<div id="container" class="password_msg_cont">
	<div class="contents">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-unlock-alt"></i>&nbsp; Error!</div>
			<div class="pgtools"></div>
			<div class="clr"></div>
		</div>
		<div id="msg">
			<div class="frm ui-widget main_form">
				<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
					<p><i class="fa fa-exclamation-triangle"></i>&nbsp; <strong>Error: </strong><?php echo $ERR['main']; ?></p>
					<?php if(trim($PGS)=="true"){ ?>
						<p><a href="<?php echo DROOT.XROOT; ?>">Click here</a> to login.</p>
					<?php } ?>
				</div>
			</div>
		</div>	
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; die(); ?>