<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
global $TEST;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$ERR=array();

$EDTA=$st->getDataArray("t_pt_templates tm", "tm.templatename,tm.subject, tm.body", "tm.templateid='".trim($TEST['templateid'])."'", "");
if(sizeof($EDTA)>0){
	$SBJ=trim($EDTA[0]['subject']);
	$BDY=html_entity_decode(trim($EDTA[0]['body']));
}

			
if(isset($_POST['btnSubmit'])=="Next >"){
	$_SESSION['TEST']['SID']=session_id();
	$_SESSION['TEST']['SLINK']=$st->encrypt(trim($_SESSION['TEST']['SID']).':'.trim($USERINFO['userid']));
	//var_dump($_SESSION); die;
	$_POST['RAW_TEST']=$_SESSION['TEST'];
	$p=$st->sanitized;
	$st->getModelData($_POST['RAW_TEST']);
	$st->Submit('TEST-VERIFY',"",$USERINFO);
	if(sizeof($st->errors) > 0)
    {
       	$ERR=$st->errors;
    }
    else{
   		header("Location: campaign");
   	}
}
//var_dump($_POST);
//var_dump($_SESSION['TEST']);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	
});
//-->
</script>
<?php echo $st->showCampaignProgress('VERIFY'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Verify Email [Step 3]</h3></div>
	<div class="main_content campaign_cont">
		<form autocomplete="Öff" id="frmVerEmail" method="post" class="main_form">
			<div class="camp_holder">
				<div class="camp_left">
					<div class="frm">
						<div class="uname"><?php echo ucwords(trim($USERINFO['fullname'])); ?></div>
						<div class="umail"><?php echo trim($USERINFO['emailid']); ?></div>
						<div class="org">Oganization:</div>
						<div class="orgname"><?php echo ucwords(trim($USERINFO['orgname'])); ?></div>
						<div class="org">Domain:<div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('DOMAIN'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="orgname"><?php echo trim($_SESSION['TEST']['domain']); ?></div>
						<div class="sub">Campaign:</div>
						<div class="subname"><?php echo trim($_SESSION['TEST']['testname']); ?></div>
						<div class="org">Targets:<div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('SLTARGET'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="umail"><?php echo $st->getReceipientsList(trim($_SESSION['TEST']['listids']),$USERINFO); ?></div>
						<div class="org">Template:<div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('TEMPLATE'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="umail"><?php echo trim($EDTA[0]['templatename']); ?></div>
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
					<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
					<div class="frm">
						<div class="lbl_label">
							<label>Verify your email</label>
							<div class="tip">
								<p>Before we can send any phishes, we need to verify that you own <span><?php echo trim($USERINFO['emailid']); ?></span></p>
								<p>The system will send a confirmation email to your mailbox.</p>
								<p>After a successful confirmation, You will be able to schedule and run your campaign.</p>
							</div>
						</div>
						<div class="org">Email From:<div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('EMAIL'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="umail"><?php echo ucwords(trim($_SESSION['TEST']['fromname'])).' &lt;'.$_SESSION['TEST']['fromemail'].'&gt;'; ?></div>
						<div class="org">Subject:</div>
						<div class="umail"><?php echo trim($SBJ); ?></div>
						<div class="org">Email Body:</div>
						<div class="umail"><?php echo trim($BDY); ?></div>
						<?php if(trim($_SESSION['TEST']['signature'])!='') {?>
							<div class="org">Signature:</div>
							<div class="umail"><?php echo nl2br($_SESSION['TEST']['signature']); ?></div>
						<?php } ?>
												
					</div>
				</div>
			</div>
			<div class="ctrls">
				<a class="btn btn-prev" href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('EMAIL'); ?>"><span class="ui-button-text">< Back</span></a>
				<input type="submit" class="btn  btn-next" id="btnSubmit" name="btnSubmit" value="Next >" />
			</div>
			
		</form>
	</div>
</div>