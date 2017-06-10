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
	$rules_array = 	array('client'=>array('type'=>'numeric', 'msg'=>'Client', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true));
	$st->addSource($_POST);
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
		$st->Submit('CAMPAIGN',$RID,$USERINFO);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: ".$st->ClientURL."?__uid=".$st->encrypt(trim($_POST['client'])));
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmCamp").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["client"];
		rtype = ["req"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
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
			<div class="frmname"><i class="fa fa-rocket fa-fw"></i>&nbsp; Campaign</div>
			<div class="indicator">* (Required)</div>
			<div class="clr"></div>
		</div>
		<form id="frmCamp" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="role">Select a client: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("REPORT_CLIENT", $USERINFO, ((isset($_POST['client'])!='')?trim($_POST['client']):''), "client", "width:100%;"); ?>
					<div class="erdx"><?php echo (isset($ERR['client'])!='')?trim($ERR['client']):''; ?></div>
				</div>
			</div>
			
			
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