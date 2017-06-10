<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$ERR=array();
if(isset($_POST['btnSubmit'])=="Save & Next >"){
	$_POST['clientid']=trim($USERINFO['userid']);
	$rules_array = 	array('clientid'=>array('type'=>'string', 'msg'=>'Client', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true),
				'groupid'=>array('type'=>'string', 'msg'=>'Group Name', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true));
    $st->addSource($_POST);
    $st->addRules($rules_array);
    $st->run();
    if(sizeof($st->errors) > 0)
    {
        $st->getError('main', 'Correct the marked fields below.');
        $ERR=$st->errors;
    }
	else
	{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('TEST-GROUP',"",$USERINFO);
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
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmSelGroup").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["groupid"];
		rtype = ["req"];
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
<?php echo $st->showCampaignProgress('GROUP'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Select Group</h3></div>
	<div class="main_content">
		<form id="frmSelGroup" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="groupid">Select Group: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("GROUP_CLIENT_MAILING", "", ((isset($_POST['groupid'])!='')?trim($_POST['groupid']):''), "groupid", $st->encrypt(trim($USERINFO['userid'])), "width:100%;"); ?>
					<div class="erdx"><?php echo (isset($ERR['groupid'])!='')?trim($ERR['groupid']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="ctrls pull-right">
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT.XROOT; ?>testing?pg=<?php echo $st->encrypt('XXXX'); ?>"><span class="ui-button-text">Cancel</span></a>
					<input type="submit" class="btn  btn-default" id="btnSubmit" name="btnSubmit" value="Save & Next >" />
				</div>
			</div>
		</form>
	</div>
</div>