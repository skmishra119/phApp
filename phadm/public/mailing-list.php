<?php
define('DROOT','./');

require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])=='CLIENT') header("Location:./");

//echo $cr->encrypt('Sushami1a').' XX '.$cr->decrypt('d0f23993eadb4684904a634d97e24ff1');
$RID = (isset($_POST['hdnRID'])!='') ? trim($_POST['hdnRID']) : '';
$ERR=array();

if(isset($_POST['btnSubmit'])=="Submit"){
	$rules_array = 	array('clientid'=>array('type'=>'string', 'msg'=>'Client', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true),
				'emailid'=>array('type'=>'email', 'msg'=>'Email Address', 'required'=>true, 'min'=>6, 'max'=>100, 'options'=>false, 'trim'=>true),
				'fullname'=>array('type'=>'string', 'msg'=>'Full Name', 'required'=>true, 'min'=>6, 'max'=>100, 'options'=>false, 'trim'=>true)
	);
	$st->addSource($_POST);
	//$val = new validation();
    /*** use POST as the source ***/
    $st->addSource($_POST);
    //var_dump($_POST);
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
        $st->getError('main', 'Correct the marked fields below.');
        $ERR=$st->errors;
    }
	else
	{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		var_dump($st->errors);
		$st->Submit('MAILING-LISTS',$RID,$USERINFO);
		var_dump($_POST);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: mailing-lists");
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}else{
	if(trim($RID)!=''){
		$PVAL=$st->getDataArray("t_pt_mailinglist", "listid as 'RID', clientid, groupid, fullname, emailid, contactphone", "listid='".$st->decrypt(trim($RID))."'", "");
		$_POST=$PVAL[0];
	} 
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#clientid").focus(function(){$(this).blur();});

	if($("#groupid").val()!=''){
  		$.getJSON('getdata?tp=GROUP_CLIENT&vl='+$('#groupid').val(), function(result){
			$("#clientid").val(result.userid);
			$('#clientInfo').html('Client Name: '+result.fullname+'<br/>'+'Email Address: '+result.emailid);
	 	});
	}
	   
	$("#groupid").change(
  		function( event ) {
	    	if($.trim($(this).val())!=""){
		 		$.getJSON('getdata?tp=GROUP_CLIENT&vl='+$.trim($(this).val()), function(result){
					$("#clientid").val(result.userid);
					$('#clientInfo').html('Client Name: <span class="result">'+result.fullname+'</span><br/>'+'Email Address: <span class="result">'+result.emailid+'</span>');
			 	});
	    	}
	   	}
     );

	$("#frmML").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["clientid","fullname","emailid"];
		rtype = ["req","req","email"];
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
			<div class="frmname"><i class="fa fa-building fa-fw"></i>&nbsp; Target </div>
			<div class="indicator">* (Required)</div>
			<div class="clr"></div>
		</div>
		<form id="frmML" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="clientid">Client: <span>*</span></label>
				</div>
				<div class="txt" id="txtClient">
					<?php echo $st->showDrps("CLIENT_ONLY", "", ((isset($_POST['clientid'])!='')?trim($_POST['clientid']):''), "clientid", false, "width:100%;"); ?>
					<div class="erdx"><?php echo (isset($ERR['clientid'])!='')?trim($ERR['clientid']):''; ?></div>
				</div>
			</div>
			
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
					<label for="contactphone">Contact Phone: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="contactphone" name="contactphone" value="<?php if(isset($_POST['contactphone'])) echo $_POST['contactphone'];?>" />
					<div class="erdx"><?php echo (isset($ERR['contactphone'])!='')?trim($ERR['contactphone']):''; ?></div>
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
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print($RID); ?>" />
			<div class="frm">
				<div class="ctrls pull-right">
				<?php echo $st->get_token_id();?>
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit &nbsp;&#xf058;" />
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT; ?>mailing-lists"><span class="ui-button-text">Cancel &nbsp;&#xf057;</span></a>
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>