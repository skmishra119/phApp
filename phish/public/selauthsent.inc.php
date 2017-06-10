<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
global $TEST;
$ERR=array();
$st->getError('main', 'Congratulations!!!, An email has been sent for authorization. Once authorized, the campaign will run on the scheduled date and time.');
$ERR=$st->errors;
unset($_SESSION['TEST']);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
});
//-->
</script>
<?php echo $st->showCampaignProgress('ENDTEST'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Success [Finish]</h3></div>
	<div class="main_content campaign_cont">
		<form id="frmSelAuthSent" method="post" class="main_form">
			<div class="camp_holder">
				<div id="msg" class="top-msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-success">'.$ERR['main'].'</div>':''; ?></div>
			</div>
		</form>
		<?php if(isset($_SESSION['AID'])!='') {?>
		<div class="ctrls">
			<a class="btn btn-next" href="<?php echo $st->AdminURL; ?>"><span class="ui-button-text">Dashboard</span></a>
		</div>
		<?php } ?>
	</div>
</div>