<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");

//echo $st->encrypt('Sushami1a').' XX '.$st->decrypt('d0f23993eadb4684904a634d97e24ff1');
$RID = (isset($_POST['hdnRID'])!='') ? trim($_POST['hdnRID']) : '';
$ERR=array();

if(isset($_POST['btnSubmit'])=="Submit"){
	$_POST['clientid']=trim($USERINFO['userid']);
	$rules_array = 	array('clientid'=>array('type'=>'string', 'msg'=>'Client', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true),
				'groupname'=>array('type'=>'string', 'msg'=>'Group Name', 'required'=>true, 'min'=>3, 'max'=>100, 'options'=>false, 'trim'=>true),
				'domain'=>array('type'=>'string', 'msg'=>'Domain', 'required'=>true, 'min'=>3, 'max'=>100, 'options'=>false, 'trim'=>true));
	$st->addSource($_POST);
	//$val = new validation();
    /*** use POST as the source ***/
    $st->addSource($_POST);
    /*** add a form field rule ***/
    //if(trim($_POST['role'])=='MANAGER')
    //	$st->addRule('isdefault', 'string', 'Default', true, 3, 4, array('YES','NO'), true);
    //if(trim($_POST['role'])=='CLIENT')
    //	$st->addRule('parentid', 'string', 'Manager', true, 0, 100, false, true);
    /*** add an array of rules ***/
    $st->addRules($rules_array);
    /*** run the validation rules ***/
    $st->run();
    /*** if there are errors show them ***/
    if(sizeof($st->errors) > 0)
    {
        $st->getError('main', ' Correct the marked fields below.');
        $ERR=$st->errors;
    }
	else
	{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('GROUPS',$RID,$USERINFO);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: groups");
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}else{
	if(trim($RID)!=''){
		$PVAL=$st->getDataArray("t_pt_groups", "groupid as 'RID', groupname, domain", "groupid='".$st->decrypt(trim($RID))."'", "");
		$_POST=$PVAL[0];
	} 
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmGroup").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["groupname","domain"];
		rtype = ["req","req"];
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
<div class="content_cont_wrapper small_form">
	<div class="header_cont"><h3>Group</h3></div>
	<div class="main_content">
		<form id="frmGroup" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="groupname">Group Name: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="groupname" name="groupname" value="<?php if(isset($_POST['groupname'])) echo $_POST['groupname'];?>" />
					<div class="erdx"><?php echo (isset($ERR['groupname'])!='')?trim($ERR['groupname']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="domain">Domain: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("CLIENT_DOMAIN", $USERINFO, ((isset($_POST['domain'])!='')?trim($_POST['domain']):''), "domain", $RID, "width:100%"); ?>
					 <div class="erdx"><?php echo (isset($ERR['domain'])!='')?trim($ERR['domain']):''; ?></div>
				</div>
			</div>
			
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print($RID); ?>" />
			<div class="frm">
				<div class="ctrls pull-right">
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit" />
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT.XROOT; ?>groups"><span class="ui-button-text">Cancel</span></a>
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>