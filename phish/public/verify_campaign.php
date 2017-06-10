<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//var_dump($_REQUEST,$_GET);
try {
	$SLINK = (isset($_REQUEST['key'])!='') ? trim($_REQUEST['key']) : '';
	if(trim($SLINK)==''){ 
		header("Location: ".DROOT.XROOT."error/404/".$st->encrypt('false'));
	}
	$ERR=array();
	
	$VDATA = $st->GetDataArray("t_pt_campaign","test_value","systemlink='".trim($SLINK)."' and verified='NO' and status='ACTIVE'","");
	if(sizeof($VDATA)<=0){
		$st->getError('main','Sorry! the campaign is not available for this user or email already has been verified.');
		$ERR = $st->errors;
	}else{
		$TEST_VALUE=json_decode($VDATA[0]['test_value'],true);
		if(sizeof($TEST_VALUE)<=0){
			$st->getError('main','Sorry! the campaign is not available for this user or email already has been verified.');
			$ERR = $st->errors;
		}else{
			$USRINFO = $st->getUserInfo($TEST_VALUE['clientid']);
			$EDTA=$st->getDataArray("t_pt_templates tm", "tm.templatename,tm.subject, tm.body", "tm.templateid='".trim($TEST_VALUE['templateid'])."'", "");
			if(sizeof($EDTA)>0){
				$SBJ=trim($EDTA[0]['subject']);
				$BDY=nl2br(html_entity_decode(trim($EDTA[0]['body'])));
			}
		}
	}
}catch(Exception $e){
	echo $e->getMessage();
}
//var_dump($VDATA,$TEST_VALUE);

if(isset($_POST['btnSubmit'])=="Next >" && sizeof($ERR)<=0){
	$_POST['SLINK'] = $SLINK;
	$st->addSource($_POST);
    $p=$st->sanitized;
	$st->getModelData($_POST);
	$st->Submit('EMAIL-VERIFIED',"",$USERINFO);
	if(count($st->errors) > 0)
    {
       	$ERR=$st->errors;
    } else {
   		header("Location: campaign");
   	}
}
//var_dump($_SESSION);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	
});
//-->
</script>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Email Verification</h3></div>
	<div class="main_content campaign_cont">
		<form autocomplete="Off" id="frmSntEmail" method="post" class="main_form">
			<div class="camp_holder">
				<div id="msg" class="top-msg">
					<?php 
						echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':'<div class="alert alert-success">You have successfully confirmed your email address!<div class="tip">You may click on <span>Next</span> button to schedule and run your campaign.</div></div>'; 
					?>
				</div>
				<?php if(sizeof($VDATA)>0 && isset($USRINFO)){ ?>
				<div class="camp_left">
					<div class="frm">
						<div class="uname"><?php echo ucwords(trim($USRINFO['fullname'])); ?></div>
						<div class="umail"><?php echo trim($USRINFO['emailid']); ?></div>
						<div class="org">Oganization:</div>
						<div class="orgname"><?php echo ucwords(trim($USRINFO['orgname'])); ?></div>
						<div class="org">Domain:</div>
						<div class="orgname"><?php echo trim($TEST_VALUE['domain']); ?></div>
						<div class="sub">Campaign:</div>
						<div class="subname"><?php echo trim($TEST_VALUE['testname']); ?></div>
						<div class="org">Targets:</div>
						<div class="umail"><?php echo $st->getReceipientsList(trim($TEST_VALUE['listids']),$USRINFO); ?></div>
					</div>
					<div class="frm">
						<div class="otherinfo">
							<p>
								The campaign will run against the receipients displayed in the target list and the template selected. 
							</p>
						</div>
					</div>
				</div>
				<div class="camp_right">
					<div class="frm">
						<div class="org">Template:</div>
						<div class="umail"><?php echo trim($EDTA[0]['templatename']); ?></div>
						<div class="org">Email From:</div>
						<div class="umail"><?php echo ucwords(trim($TEST_VALUE['fromname'])).' &lt;'.$TEST_VALUE['fromemail'].'&gt;'; ?></div>
						<div class="org">Subject:</div>
						<div class="umail"><?php echo trim($SBJ); ?></div>
						<div class="org">Email Body:</div>
						<div class="umail"><?php echo trim($BDY); ?></div>
						<?php if(trim($TEST_VALUE['signature'])!='') {?>
							<div class="org">Signature:</div>
							<div class="umail"><?php echo nl2br($TEST_VALUE['signature']); ?></div>
						<?php } ?>
												
					</div>
				</div>
				<?php } ?>				
			</div>
			<?php if(sizeof($ERR)<=0){ ?>
			<div class="ctrls">
				<input type="submit" id="btnSubmit" name="btnSubmit" class="btn  btn-next" value="Next >"/>
			</div>
			<?php } ?>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>
