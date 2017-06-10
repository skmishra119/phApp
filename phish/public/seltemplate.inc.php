<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
global $TEST;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$ERR=array();
if(isset($_POST['btnSubmit'])=="Next >"){
	//$_POST['testid']=trim($TEST[0]['testid']);
	$_POST['clientid']=trim($USERINFO['userid']);
	$rules_array = 	array('templateid'=>array('type'=>'string', 'msg'=>'Template', 'required'=>true, 'min'=>1, 'max'=>200, 'options'=>false, 'trim'=>true));
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
		$st->Submit('TEST-TEMPLATE',"",$USERINFO);
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
	//$('select').customSB();
	$("#frmSelTemplate").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["templateid"];
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
<?php echo $st->showCampaignProgress('TEMPLATE'); ?>                  
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Template [Step 3]</h3></div>
	<div class="main_content campaign_cont">
		<form id="frmSelTemplate" method="post" class="main_form">
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
					</div>
					<div class="frm">
						<div class="otherinfo">
							<p>
								The campaign will run against the recipients displayed in the target list. 
							</p>
						</div>
					</div>
				</div>
				<div class="camp_right">
					<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
					<div class="frm">
						<div class="lbl_label">
							<label for="templateid">Select Email Template</label>
						</div>
						<div class="txt customSB">
							<?php echo $st->showDrps("CLIENT_TEMPLATE", "", ((isset($_POST['templateid'])!='')?trim($_POST['templateid']):''), "templateid", trim($USERINFO['userid']), "width:100%; height:45px;border:2px solid color #377d9c;"); ?>
							<div class="erdx"><?php echo (isset($ERR['templateid'])!='')?trim($ERR['templateid']):''; ?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="ctrls">
				<a class="btn btn-prev" href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('GTARGET'); ?>"><span class="ui-button-text">< Back</span></a>
				<input type="submit" class="btn  btn-next" id="btnSubmit" name="btnSubmit" value="Next >" />
				<?php echo $st->get_token_id();?>
			</div>
		</form>
	</div>
</div>