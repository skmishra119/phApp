<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.DROOT.$st->IncludePath.'header.inc.php';
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])=='CLIENT') header("Location:./");

//echo $cr->encrypt('Sushami1a').' XX '.$cr->decrypt('d0f23993eadb4684904a634d97e24ff1');
$RID = (isset($_POST['hdnRID'])!='') ? trim($_POST['hdnRID']) : '';
$ERR=array();

if(isset($_POST['btnSubmit'])=="Submit"){
	$rules_array = 	array('emailid'=>array('type'=>'email', 'msg'=>'Email address', 'required'=>true, 'min'=>5, 'max'=>100, 'options'=>false, 'trim'=>true),
				'fullname'=>array('type'=>'string', 'msg'=>'Full Name', 'required'=>true, 'min'=>6, 'max'=>100, 'options'=>false, 'trim'=>true),
				'phone'=>array('type'=>'phone', 'msg'=>'Phone number', 'required'=>true, 'min'=>6, 'max'=>20, 'options'=>false, 'trim'=>true),
				'orgname'=>array('type'=>'string', 'msg'=>'Organization', 'required'=>true, 'min'=>6, 'max'=>100, 'options'=>false, 'trim'=>true),
	            'role'=>array('type'=>'string', 'msg'=>'Role', 'required'=>true, 'min'=>6, 'max'=>8, 'options'=>array('SYSADMIN','MANAGER','CLIENT'), 'trim'=>true));
	$st->addSource($_POST);
	//$val = new validation();
    /*** use POST as the source ***/
    $st->addSource($_POST);
    /*** add a form field rule ***/
    if(trim($_POST['role'])=='MANAGER'){
    	$st->addRule('isdefault', 'string', 'Default', true, 3, 4, array('YES','NO'), true);
    }elseif(trim($_POST['role'])=='CLIENT'){
    	$st->addRule('parentid', 'string', 'Manager', true, 0, 100, false, true);
    	$st->addRule('domains', 'string', 'Domains', true, 5, 200, false, true);
    	$st->addRule('authname', 'string', 'Authorizer Name', true, 5, 100, false, true);
    	$st->addRule('authemail', 'email', 'Authorizer Email', true, 5, 100, false, true);
  	}elseif(trim($RID)!='' && isset($_POST['status'])!=''){
		$st->addRule('status', 'string', 'Status', true, 1, 20, array('ACTIVE','INACTIVE'), true);
	}
    /*** add an array of rules ***/
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
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('USERS',$RID,$USERINFO);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: users");
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}else{
	if(trim($RID)!=''){
		$PVAL=$st->getDataArray("t_pt_users", "userid as 'RID', fullname, emailid, phone, orgname, role, domains, isdefault, parentid, authname,authemail, status", "userid='".$st->decrypt(trim($RID))."'", "");
		$_POST=$PVAL[0];
	} 
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
function setRole(RVL){
	switch($.trim(RVL)){
		case "CLIENT":
			$("#trDefault").hide();
			$("#trManager").show();
			$("#trDomain").show();
		break;
		case "MANAGER":
			$("#trDefault").show();
			$("#trManager").hide();
			$("#trDomain").hide();
		break;
		case "SYSADMIN":
			$("#trDefault").hide();
			$("#trManager").hide();
			$("#trDomain").hide();
		break;
	}
}
$(document).ready(function(){
	setRole($("#role").val());
	$("#role").change(function(event){setRole($.trim($(this).val()));});
	$("#frmUser").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["fullname","emailid","phone","orgname","role"];
		rtype = ["req","email","phone","req","req"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
		if($.trim($("#role").val())=="MANAGER"){
			if(getValidate("isdefault","req")==false)	
				rv=false;
		}
		
		if($.trim($("#role").val())=="CLIENT"){
			if(getValidate("parentid","req")==false)	
				rv=false;
			if(getValidate("domains","req")==false)	
				rv=false;
			if(getValidate("authname","name")==false)	
				rv=false;
			if(getValidate("authemail","email")==false)	
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
			<div class="frmname"><i class="fa fa-user fa-fw"></i>&nbsp; User</div>
			<div class="indicator">* (Required)</div>
			<div class="clr"></div>
		</div>
		<form id="frmUser" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="fullname">Full Name: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="fullname" name="fullname" value="<?php if(isset($_POST['fullname'])) echo $_POST['fullname'];?>" />
					<div class="erdx"><?php echo (isset($ERR['fullname'])!='')?trim($ERR['fullname']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="emailid">Email Address: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="emailid" name="emailid" value="<?php if(isset($_POST['emailid'])) echo $_POST['emailid'];?>" />
					<div class="erdx"><?php echo (isset($ERR['emailid'])!='')?trim($ERR['emailid']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="phone">Phone Number: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="phone" name="phone" value="<?php  if(isset($_POST['phone'])) echo $_POST['phone'];?>" />
					<div class="erdx"><?php echo (isset($ERR['phone'])!='')?trim($ERR['phone']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="orgname">Organization: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="orgname" name="orgname" value="<?php  if(isset($_POST['orgname'])) echo $_POST['orgname'];?>" />
					<div class="erdx"><?php echo (isset($ERR['orgname'])!='')?trim($ERR['orgname']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="role">Role: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("ROLE", "", ((isset($_POST['role'])!='')?trim($_POST['role']):''), "role", false, "width:100%;"); ?>
					 <div class="erdx"><?php echo (isset($ERR['role'])!='')?trim($ERR['role']):''; ?></div>
				</div>
			</div>
			<div class="frm" id="trDefault" style="display:none;">
				<div class="lbl">
					<label for="isdefault">Default?: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("YESNO", "", ((isset($_POST['isdefault'])!='')?trim($_POST['isdefault']):''), "isdefault", false, "width:100%;"); ?>
					 <div class="erdx"><?php echo (isset($ERR['isdefault'])!='')?trim($ERR['isdefault']):''; ?></div>
				</div>
			</div>
			<div class="frm" id="trManager" style="display:none;">
				<div class="lbl">
					<label for="parentid">Manager: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("CLIENT_MANAGER", "", ((isset($_POST['parentid'])!='')?trim($_POST['parentid']):''), "parentid", $RID, "width:100%"); ?>
					 <div class="erdx"><?php echo (isset($ERR['parentid'])!='')?trim($ERR['parentid']):''; ?></div>
				</div>
			</div>
			<div class="frm" id="trDomain" style="display:none;">
				<div class="frm">
					<div class="lbl">
						<label for="domains">Domains: <span>*</span></label>
					</div>
					<div class="txt">
						<input type="text" class="form-control" maxlength="200" id="domains" name="domains" value="<?php  if(isset($_POST['domains'])) echo $_POST['domains'];?>" />
					 	<div class="erdx"><?php echo (isset($ERR['domains'])!='')?trim($ERR['domains']):''; ?></div>
					</div>
				</div>
				<div class="frm">
					<div class="lbl">
						<label for="authname">Authorizer Name: <span>*</span></label>
					</div>
					<div class="txt">
						<input type="text" class="form-control" maxlength="100" id="authname" name="authname" value="<?php  if(isset($_POST['authname'])) echo $_POST['authname'];?>" />
					 	<div class="erdx"><?php echo (isset($ERR['authname'])!='')?trim($ERR['authname']):''; ?></div>
					</div>
				</div>
				<div class="frm">
					<div class="lbl">
						<label for="authemail">Authorizer Email: <span>*</span></label>
					</div>
					<div class="txt">
						<input type="email" class="form-control" maxlength="100" id="authemail" name="authemail" value="<?php  if(isset($_POST['authemail'])) echo $_POST['authemail'];?>" />
					 	<div class="erdx"><?php echo (isset($ERR['authemail'])!='')?trim($ERR['authemail']):''; ?></div>
					</div>
				</div>
			</div>
			<?php if(trim($RID)!='' && (trim($_POST['status'])=='ACTIVE' || trim($_POST['status'])=='INACTIVE')) { ?>
			<div class="frm">
				<div class="lbl">
					<label for="Status">Status: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("USER_STATUS", "", ((isset($_POST['status'])!='')?trim($_POST['status']):''), "status", $RID, "width:100%"); ?>
					 <div class="erdx"><?php echo (isset($ERR['status'])!='')?trim($ERR['status']):''; ?></div>
				</div>
			</div>
			<?php } ?>
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print($RID); ?>" />
			<div class="frm">
				<div class="ctrls pull-right">
				<?php echo $st->get_token_id();?>
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit &nbsp;&#xf058;" />
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT; ?>users"><span class="ui-button-text">Cancel &nbsp;&#xf057;</span></a>
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.DROOT.$st->IncludePath.'footer.inc.php'; ?>