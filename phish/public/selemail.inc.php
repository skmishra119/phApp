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
	//$_POST['testid']=trim($TEST[0]['testid']);
	$_POST['clientid']=trim($USERINFO['userid']);
	//$_POST['signature']=nl2br(trim($_POST['signature']));
	//$_POST['fromemail']=trim($_POST['fromemail']).trim($_SESSION['TEST']['domain']);
	//var_dump($st->getDomainFromEmail(trim($USERINFO['emailid'])),$st->getDomainFromEmail(trim($_POST['fromemail']))); exit;
	$rules_array = 	array('fromname'=>array('type'=>'string', 'msg'=>'From Name', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true),
					'fromemail'=>array('type'=>'email', 'msg'=>'Email Address', 'required'=>true, 'min'=>10, 'max'=>100, 'options'=>false, 'trim'=>true));
    $st->addSource($_POST);
    $st->addRules($rules_array);
    $st->run();
    if(sizeof($st->errors) > 0)
    {
        $st->getError('main', 'Correct the marked fields below.');
        $ERR=$st->errors;
    }
    elseif(trim($_POST['fromemail'])==trim($USERINFO['emailid'])){
    	$st->getError('main', 'Correct the marked fields below.');
    	$st->getError('fromemail', 'You should not use your registered email address.');
        $ERR=$st->errors;
    }
    /*elseif(trim($st->getDomainFromEmail(trim($_POST['fromemail'])))==trim($st->getDomainFromEmail(trim($USERINFO['emailid'])))){
    	$st->getError('main', 'Correct the marked fields below.');
    	$st->getError('fromemail', 'Email Address should not be from your domain.');
        $ERR=$st->errors;
    }*/
	else
	{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('TEST-EMAIL',"",$USERINFO);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	}
    	else{
    		header("Location: campaign");
    		//require_once 'seltarget.inc.php';
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}elseif(isset($_SESSION['TEST']['fromname'])!='' || isset($_SESSION['TEST']['fromemail'])!=''){
	$_POST['fromname']=trim($_SESSION['TEST']['fromname']);
	$_POST['fromemail']=trim($_SESSION['TEST']['fromemail']);
	$_POST['signature']=trim($_SESSION['TEST']['signature']);
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmSelEmail").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["fromname","fromemail"];
		rtype = ["req","email"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
		//$("input,textarea").each(function(){ cleanup($(this)); });
		/*if(rv==false)
		{
			$("#msg").html(gerror);
		}*/
		return rv;
	});
	
});
//-->
</script>
<?php echo $st->showCampaignProgress('EMAIL'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Email [Step 3]</h3></div>
	<div class="main_content campaign_cont">
		<form autocomplete="Öff" id="frmSelEmail" method="post" class="main_form">
			<div class="camp_holder">
				<div class="camp_left">
					<div class="frm">
						<div class="uname"><?php echo ucwords(trim($USERINFO['fullname'])); ?></div>
						<div class="umail"><?php echo trim($USERINFO['emailid']); ?></div>
						<div class="org">Organization:</div>
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
								The campaign will run against the recipients displayed in the target list and the template selected. 
							</p>
						</div>
					</div>
				</div>
				<div class="camp_right">
					<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
					<div class="frm">
						<div class="lbl_label">
							<label>Prepare email to be sent</label>
						</div>
					</div>
					<div class="frm">
						<div class="lbl">
							<label for="fromname">From Name: <span>*</span></label>
						</div>
						<div class="txt">
							<input type="text" class="form-control" maxlength="100" id="fromname" name="fromname" value="<?php if(isset($_POST['fromname'])) echo $_POST['fromname'];?>" />
							<div class="erdx"><?php echo (isset($ERR['fromname'])!='')?trim($ERR['fromname']):''; ?></div>
						</div>
					</div>
					<div class="frm">
						<div class="lbl">
							<label for="fromemail">From Email: <span>*</span></label>
						</div>
						<div class="txt">
							<input type="email" class="form-control" maxlength="100" id="fromemail" name="fromemail" value="<?php if(isset($_POST['fromemail'])) echo $_POST['fromemail'];?>" />
							<div class="erdx"><?php echo (isset($ERR['fromemail'])!='')?trim($ERR['fromemail']):''; ?></div>
						</div>
					</div>
					<div class="frm">
						<div class="lbl">
							<label for="signature">Signature: &nbsp;</label>
						</div>
						<div class="txt">
							<textarea class="form-control" rows="3" id="signature" name="signature" ><?php if(isset($_POST['signature'])) echo $_POST['signature'];?></textarea>
							<div class="erdx"><?php echo (isset($ERR['signature'])!='')?trim($ERR['signature']):''; ?></div>
						</div>
					</div>
					<div class="frm">
						<div class="lbl">
							<label for="emailsubject">Email Subject: &nbsp;</label>
						</div>
						<div class="txt">
							<div class="form-control">
							<?php echo trim($SBJ)?>
							</div>
						</div>
					</div>
					<div class="frm">
						<div class="lbl">
							<label for="emailbody">Email Content: &nbsp;</label>
						</div>
						<div class="txt">
							<div class="ui-autocomplete-input ui-widget ui-corner-all email_content_cont" style="border:1px solid #ccc; padding:6px 12px; line-height: 1.75em;">
								<?php echo trim($BDY)?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ctrls">
				<a class="btn btn-prev" href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('TEMPLATE'); ?>"><span class="ui-button-text">< Back</span></a>
				<input type="submit" class="btn  btn-next" id="btnSubmit" name="btnSubmit" value="Next >" />
				<?php echo $st->get_token_id();?>
			</div>
			
		</form>
	</div>
</div>