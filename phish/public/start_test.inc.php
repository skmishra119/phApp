<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
if(isset($_SESSION['TEST'])){ unset($_SESSION['TEST']); }
$ERR=array();
if(isset($_POST['btnSubmit'])=="Getting Started"){
	$_POST['clientid']=trim($USERINFO['userid']);
	$_SESSION['TEST']=array("PROCESS"=>"DOMAIN");
	header("Location: campaign");
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){	
});
//-->
</script>
<?php echo $st->showCampaignProgress('START'); ?>                  
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Begin Campaign [Start]</h3></div>
	<div class="main_content">
		<form id="frmSelGroup" method="post" class="main_form">
			<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
			<div class="frm">
				<ul class="list_content">
					<li><strong>Step 1: [Domain] </strong>Select the targeted domain for which the campaign will run.</li>
					<li><strong>Step 2: [Targets] </strong>Select/Upload/Add targets (recipients) for which the campaign will run.</li>
					<li><strong>Step 3: [Template] </strong>Select the email template and provide the sender information for the template to be send to the recipients.</li>
					<li><strong>Step 4: [Schedule] </strong>Schedule the campaign by providing the start date and time and end date and time for running the campaign.</li>
					<li><strong>Step 5: [Authorize] </strong>Verified approval for the target domain will need to authorize the campaign.  Campaigns will only launch at the requested scheduled time if the approval has occurred.</li>
				</ul>
				<div class="btn_cont"><input class="btn btn-default" type="submit" id="btnSubmit" name="btnSubmit" value="Getting Started"/></div>           
			</div>
		</form>
	</div>
</div>