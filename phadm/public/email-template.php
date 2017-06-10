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
	$rules_array = 	array('templatetype'=>array('type'=>'string', 'msg'=>'Template Type', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>array('WEBLINK','ATTACHMENT','WEBFORM'), 'trim'=>true),
				'templatename'=>array('type'=>'string', 'msg'=>'Template Name', 'required'=>true, 'min'=>3, 'max'=>100, 'options'=>false, 'trim'=>true),
				'subject'=>array('type'=>'string', 'msg'=>'Subject', 'required'=>true, 'min'=>10, 'max'=>200, 'options'=>false, 'trim'=>true),
				'body'=>array('type'=>'string', 'msg'=>'Contents', 'required'=>true, 'min'=>6, 'max'=>18000,'options'=>false, 'trim'=>true),
				'pagename'=>array('type'=>'string', 'msg'=>'Page name', 'required'=>true, 'min'=>3, 'max'=>50, 'options'=>false, 'trim'=>true),
				'pageurl'=>array('type'=>'url', 'msg'=>'URL', 'required'=>true, 'min'=>10, 'max'=>200, 'options'=>false, 'trim'=>true),
				'status'=>array('type'=>'string', 'msg'=>'Status', 'required'=>true, 'min'=>6, 'max'=>8, 'options'=>array('DRAFT','ACTIVE'), 'trim'=>true),
	);
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
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('EMAIL-TEMPLATES',$RID,$USERINFO);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: email-templates");
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}else{
	if(trim($RID)!=''){
		$PVAL=$st->getDataArray("t_pt_templates", "templateid as 'RID', clientid, templatetype, templatename, subject, body, status, pagename, pageurl, logo", "templateid='".$st->decrypt(trim($RID))."'", "");
		$_POST=$PVAL[0];
	} 
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#body").jqte();
	var jqteStatus = true;
	$(".status").click(function()
	{
		jqteStatus = jqteStatus ? false : true;
		$('#body').jqte({"status" : jqteStatus})
	});
	
	$("#frmTemplate").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["templatetype","templatename","subject","body","pagename","pageurl","status"];
		rtype = ["req","req","req","req","req","url","req"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		} 
// 		if($.trim($("textarea").html())==''){
		if($.trim($('#body').val())==''){
 			var emsg="<div class='erd'>Field should not be empty.</div>";
 			$('.jqte .jqte_editor').addClass('errX');
 			$(emsg).insertAfter($('.jqte'));
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
	<div class="login" style="width:90%;">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-envelope fa-fw"></i>&nbsp; Email Template </div>
			<div class="indicator">* (Required)</div>
			<div class="clr"></div>
		</div>
		<form id="frmTemplate" method="post" class="main_form" enctype="multipart/form-data">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
			<div class="form_left">
			<div class="frm">
				<div class="lbl">
					<label for="clientid">Client: &nbsp;</label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("TEMPLATE_CLIENT", "", ((isset($_POST['clientid'])!='')?trim($_POST['clientid']):''), "clientid", false, "width:98%;"); ?>
					 <div class="erdx"><?php echo (isset($ERR['clientid'])!='')?trim($ERR['clientid']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="templatetype">Template Type: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("TEMPLATE_TYPE", "", ((isset($_POST['templatetype'])!='')?trim($_POST['templatetype']):''), "templatetype", false, "width:98%;"); ?>
					 <div class="erdx"><?php echo (isset($ERR['templatetype'])!='')?trim($ERR['templatetype']):''; ?></div>
				</div>
			</div>
			</div>
			<div class="form_right">
			<div class="frm">
				<div class="lbl">
					<label for="templatename">Template Name: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="templatename" name="templatename" value="<?php if(isset($_POST['templatename'])) echo $_POST['templatename'];?>" />
					<div class="erdx"><?php echo (isset($ERR['templatename'])!='')?trim($ERR['templatename']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="subject">Subject: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="subject" name="subject" value="<?php if(isset($_POST['subject'])) echo $_POST['subject'];?>" />
					<div class="erdx"><?php echo (isset($ERR['subject'])!='')?trim($ERR['subject']):''; ?></div>
				</div>
			</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="body">Body: <span>*</span></label>
				</div>
				<div class="txt">
					<textarea class="form-control" rows="10" id="body" name="body"><?php if(isset($_POST['body'])) echo $_POST['body'];?></textarea>
					<div class="erdx"><?php echo (isset($ERR['body'])!='')?trim($ERR['body']):''; ?></div>
				</div>
			</div>
			<div class="frm choose-logo">
				<div class="lbl">
					<label for="status">Logo:</label>
				</div>
				<div class="txt">
					<input type="file" class="form-control" id="logo" name="logo" value="<?php if(isset($_POST['logo'])) echo $_POST['logo'];?>"/>
					<div class="erdx"><?php echo (isset($ERR['logo'])!='')?trim($ERR['logo']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="pagename">Page Name: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="50" id="pagename" name="pagename" value="<?php if(isset($_POST['pagename'])) echo $_POST['pagename'];?>" />
					<div class="erdx"><?php echo (isset($ERR['pagename'])!='')?trim($ERR['pagename']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="pageurl">Page URL: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="250" id="pageurl" name="pageurl" value="<?php if(isset($_POST['pageurl'])) echo $_POST['pageurl'];?>" />
					<div class="erdx"><?php echo (isset($ERR['pageurl'])!='')?trim($ERR['pageurl']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="status">Status: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("TEMPLATE_STATUS", "", ((isset($_POST['status'])!='')?trim($_POST['status']):''), "status", false, "width:100%;"); ?>
					 <div class="erdx"><?php echo (isset($ERR['status'])!='')?trim($ERR['status']):''; ?></div>
				</div>
			</div>
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print($RID); ?>" />
			<div class="frm">
				<div class="ctrls pull-right">
				<?php echo $st->get_token_id();?>
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit &nbsp;&#xf058;" />
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT; ?>email-templates"><span class="ui-button-text">Cancel &nbsp;&#xf057;</span></a>
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>