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
	$rules_array = 	array('type'=>array('type'=>'string', 'msg'=>'Type', 'required'=>true, 'min'=>5, 'max'=>10, 'options'=>array('FAQ','HELP','OTHER'), 'trim'=>true),
				'page'=>array('type'=>'string', 'msg'=>'Page Name', 'required'=>true, 'min'=>4, 'max'=>100, 'options'=>false, 'trim'=>true),
				'heading'=>array('type'=>'string', 'msg'=>'Page Heading', 'required'=>true, 'min'=>6, 'max'=>200, 'options'=>false, 'trim'=>true),
				'contents'=>array('type'=>'string', 'msg'=>'Contents', 'required'=>true, 'min'=>6, 'max'=>18000,'options'=>false, 'trim'=>true),
				'user'=>array('type'=>'string', 'msg'=>'Intended User', 'required'=>true, 'min'=>6, 'max'=>100, 'options'=>array('ADMIN','CLIENT','BOTH'), 'trim'=>true)
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
		$st->Submit('CMS',$RID,$USERINFO);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: cmss");
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}else{
	if(trim($RID)!=''){
		$PVAL=$st->getDataArray("t_pt_cms", "cmsid as 'RID', type, page, heading, contents, user", "cmsid='".$st->decrypt(trim($RID))."'", "");
		$_POST=$PVAL[0];
	} 
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$('#contents').jqte();
	
	// settings of status
	var jqteStatus = true;
	$(".status").click(function()
	{
		jqteStatus = jqteStatus ? false : true;
		$('#contents').jqte({"status" : jqteStatus})
	});
	
	$("#frmCms").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["type","page","heading","contents","user"];
		rtype = ["req","req","req","req","req"];
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
	<div class="login cms_form_cont">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-book fa-fw"></i>&nbsp; CMS </div>
			<div class="indicator">* (Required)</div>
			<div class="clr"></div>
		</div>
		<form id="frmUser" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
			<div class="form_left">
			<div class="frm">
				<div class="lbl">
					<label for="user">Content For: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("CMS_USER", "", ((isset($_POST['user'])!='')?trim($_POST['user']):''), "user", false, "width:100%;"); ?>
					<div class="erdx"><?php echo (isset($ERR['user'])!='')?trim($ERR['user']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="type">Content Type: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("CMS_TYPE", "", ((isset($_POST['type'])!='')?trim($_POST['type']):''), "type", false, "width:100%;"); ?>
					<div class="erdx"><?php echo (isset($ERR['type'])!='')?trim($ERR['type']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="page">Page Name: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="page" name="page" value="<?php if(isset($_POST['page'])) echo $_POST['page'];?>" />
					<div class="erdx"><?php echo (isset($ERR['page'])!='')?trim($ERR['page']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="heading">Page Heading &amp; Link: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="heading" name="heading" value="<?php  if(isset($_POST['heading'])) echo $_POST['heading'];?>" />
					<div class="erdx"><?php echo (isset($ERR['heading'])!='')?trim($ERR['heading']):''; ?></div>
				</div>
			</div>
			</div>
			<div class="form_right">
			<div class="frm">
				<div class="lbl">
					<label for="contents">Content: <span>*</span></label>
				</div>
				<div class="txt">
					<textarea class="form-control" rows="14" id="contents" name="contents"><?php  if(isset($_POST['contents'])) echo $_POST['contents'];?></textarea>
					<div class="erdx"><?php echo (isset($ERR['contents'])!='')?trim($ERR['contents']):''; ?></div>
				</div>
			</div>
			</div>
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print($RID); ?>" />
			<div class="frm">
				<div class="ctrls pull-right">
				<?php echo $st->get_token_id();?>
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit &nbsp;&#xf058;" />
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT; ?>cmss"><span class="ui-button-text">Cancel &nbsp;&#xf057;</span></a>
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>