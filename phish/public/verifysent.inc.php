<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
global $TEST;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$ERR=array();

//var_dump($_POST);
//var_dump($_SESSION['TEST']);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	
});
//-->
</script>
<?php echo $st->showCampaignProgress('VERISENT'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Verify Email [Step 3]</h3></div>
	<div class="main_content campaign_cont">
		<form autocomplete="Öff" id="frmSntEmail" method="post" class="main_form">
			<div class="camp_holder">
				<div class="camp_left">
					<div class="frm">
						<div class="uname"><?php echo ucwords(trim($USERINFO['fullname'])); ?></div>
						<div class="umail"><?php echo trim($USERINFO['emailid']); ?></div>
						<div class="org">Oganization:</div>
						<div class="orgname"><?php echo ucwords(trim($USERINFO['orgname'])); ?></div>
					</div>
					<div class="frm">
						<div class="otherinfo">
						</div>
					</div>
				</div>
				<div class="camp_right">
					<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
					<div class="frm">
						<div class="lbl_label">
							<label>Check your mailbox</label>
							<div class="tip">
								<p>Verification email has been sent to <span><?php echo trim($USERINFO['emailid']); ?></span></p>
								<p>Check your mailbox and click on the Verify Campaign to process the campaign.</p>
								<p>After a successful verification, you will be able to schedule and run your campaign.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ctrls pull-right" style="text-align:right;">
				<div class="btn btn-prev cursor-disable"><span class="ui-button-text">< Back</span></div>
				<div class="btn  btn-next cursor-disable">Next ></div>
			</div>
			
		</form>
	</div>
</div>