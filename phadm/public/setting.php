<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='SYSADMIN') header("Location:./");

//echo $cr->encrypt('Sushami1a').' XX '.$cr->decrypt('d0f23993eadb4684904a634d97e24ff1');
$RID = (isset($_POST['hdnRID'])!='') ? trim($_POST['hdnRID']) : '';
$ERR=array();

if(isset($_POST['btnSubmit'])=="Submit"){
	
	$PARMS=$_POST;
	unset($PARMS['params']);
	unset($PARMS['status']);
	unset($PARMS['hdnRID']);
	unset($PARMS['btnSubmit']);
	unset($PARMS['clientid']);
	unset($PARMS['clientid']);
	unset($PARMS['csrftoken']);
	
	$rules_array = 	array('clientid'=>array('type'=>'string', 'msg'=>'Client', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true),
			'maxTargets'=>array('type'=>'numeric', 'msg'=>'Maximum Targets', 'required'=>true, 'min'=>1, 'max'=>999, 'options'=>false, 'trim'=>true),
			'maxTests'=>array('type'=>'numeric', 'msg'=>'Maximum Tests', 'required'=>true, 'min'=>1, 'max'=>999, 'options'=>false, 'trim'=>true),
			'smtp'=>array('type'=>'string', 'msg'=>'Smtp Server', 'required'=>true, 'min'=>8, 'max'=>255, 'options'=>false, 'trim'=>true),
			'smtpPort'=>array('type'=>'numeric', 'msg'=>'SMTP Port', 'required'=>true, 'min'=>2, 'max'=>99999, 'options'=>false, 'trim'=>true),
			'status'=>array('type'=>'string', 'msg'=>'Status', 'required'=>true, 'min'=>6, 'max'=>20, 'options'=>array('ACTIVE','INACTIVE'), 'trim'=>true));
	$st->addSource($_POST);
	//$val = new validation();
    /*** use POST as the source ***/
    $st->addSource($_POST);
    /*** add a form field rule ***/
    $st->addRules($rules_array);
    /*** run the validation rules ***/
    $st->run();
    /*** if there are errors show them ***/
    if(sizeof($st->errors) > 0)
    {
        $st->getError('main', 'Correct the marked fields below.');
        $ERR=$st->errors;
    }
	else
	{
		
		$_POST['params']=json_encode($PARMS);
		//var_dump($_POST); exit;
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('SETTINGS',$RID,$USERINFO);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: settings");
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}else{
	if(trim($RID)!=''){
		$PVAL=$st->getDataArray("t_pt_settings", "settingid as 'RID', clientid, params, status", "settingid='".$st->decrypt(trim($RID))."'", "");
		//var_dump($PVAL);
		$_POST=$PVAL[0];
		if(trim($_POST['params'])!=''){
			$_POST['params'] = json_decode(html_entity_decode($PVAL[0]['params']),true);
			//var_dump($_POST['params']);
			foreach ($_POST['params'] as $k=>$v){
					var_dump($k,$v);
					$_POST[$k]=trim($v);
					$_POST[$k]=trim($v);
				
			}
		}
	} 
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmSettings").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["clientid","maxTargets","maxTests","smtp","status"];
		rtype = ["req","int","int","req","req"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
		//$("input,textarea").each(function(){ cleanup($(this)); });
		if(rv==false)
		{
			$("#msg").html(gerror);
		}
		return rv;
	});
	
});
//-->
</script>
<div id="container">
	<div class="login">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-gear fa-fw"></i>&nbsp; Settings </div>
			<div class="indicator">* (Required)</div>
			<div class="clr"></div>
		</div>
		<form id="frmSettings" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="clientid">Client: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("GROUP_CLIENT", "", ((isset($_POST['clientid'])!='')?trim($_POST['clientid']):''), "clientid", false, "width:100%;"); ?>
					<div class="erdx"><?php echo (isset($ERR['clientid'])!='')?trim($ERR['clientid']):''; ?></div>
				</div>
			</div>
			
			<div class="frm">
				<div class="lbl">
					<label for="maxTaregets">Maximum Targets: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="3" id="maxTargets" name="maxTargets" value="<?php if(isset($_POST['maxTargets'])) echo $_POST['maxTargets'];?>" />
					<div class="erdx"><?php echo (isset($ERR['maxTargets'])!='')?trim($ERR['maxTargets']):''; ?></div>
				</div>
			</div>
			
			<div class="frm">
				<div class="lbl">
					<label for="maxTests">Maximum Campaigns: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="3" id="maxTests" name="maxTests" value="<?php if(isset($_POST['maxTests'])) echo $_POST['maxTests'];?>" />
					<div class="erdx"><?php echo (isset($ERR['maxTests'])!='')?trim($ERR['maxTests']):''; ?></div>
				</div>
			</div>
			
			<h5>E-Mail Server Info.</h5>
			<div class="frm">
				<div class="lbl">
					<label for="smtp">Email Server: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="15" id="smtp" name="smtp" value="<?php if(isset($_POST['smtp'])) echo $_POST['smtp'];?>" />
					<div class="erdx"><?php echo (isset($ERR['smtp'])!='')?trim($ERR['smtp']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="smtpPort">Server Port: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="6" id="smtpPort" name="smtpPort" value="<?php if(isset($_POST['smtpPort'])) echo $_POST['smtpPort'];?>" />
					<div class="erdx"><?php echo (isset($ERR['smtpPort'])!='')?trim($ERR['smtpPort']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="smtpUser">User Name: &nbsp;</label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="255" id="smtpUser" name="smtpUser" autocomplete="off" value="<?php if(isset($_POST['smtpUser'])) echo $_POST['smtpUser'];?>" />
					<div class="erdx"><?php echo (isset($ERR['smtpUser'])!='')?trim($ERR['smtpUser']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="smtpPwd">Password: &nbsp;</label>
				</div>
				<div class="txt">
					<input type="password" class="form-control" maxlength="25" id="smtpPwd" name="smtpPwd" autocomplete="off" value="<?php if(isset($_POST['smtpPwd'])) echo $_POST['smtpPwd'];?>" />
					<div class="erdx"><?php echo (isset($ERR['smtpPwd'])!='')?trim($ERR['smtpPwd']):''; ?></div>
				</div>
			</div>
			
			<div class="frm">
				<div class="lbl">
					<label for="status">Status: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("SETTING_STATUS", "", ((isset($_POST['status'])!='')?trim($_POST['status']):''), "status", $RID, "width:100%"); ?>
					 <div class="erdx"><?php echo (isset($ERR['status'])!='')?trim($ERR['status']):''; ?></div>
				</div>
			</div>
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print($RID); ?>" />
			<div class="frm">
				<div class="ctrls pull-right">
				<?php echo $st->get_token_id();?>
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit &nbsp;&#xf058;" />
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT; ?>settings"><span class="ui-button-text">Cancel &nbsp;&#xf057;</span></a>
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>