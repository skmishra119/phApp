<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
if(trim($USERINFO['userid'])!='') header("Location: dashboard");

//echo $st->encrypt('Sushami1a').' XX '.$st->decrypt('d0f23993eadb4684904a634d97e24ff1');
$ERR=array();

if(isset($_POST['btnSubmit'])=="Submit"){
	$rules_array = 	array('emailid'=>array('type'=>'email', 'msg'=>'Email address', 'required'=>true, 'min'=>5, 'max'=>100, 'trim'=>true));
	//$st->addSource($_POST);
	//$val = new validation();
    /*** use POST as the source ***/
    $st->addSource($_POST);
    /*** add a form field rule ***/
    //$val->addRule('txtemail', 'email', 'Email address', true, 1, 255, true);
    /*** add an array of rules ***/
    $st->addRules($rules_array);
    /*** run the validation rules ***/
    $st->run();
    /*** if there are errors show them ***/
    if(sizeof($st->errors) > 0)
    {
        //$st->getError('main', 'Correct the marked fields below.');
        $ERR=$st->errors;
    }
	else
	{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('FORGOT_PASSWORD',0,0,'CLIENT');
		//var_dump($st->errors);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: showmsg/success/".$st->encrypt('FORGOT_PASSWORD'));
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['txtemail']);
}
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmFP").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["emailid"];
		rtype = ["email"];
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
<div class="login_cont register">
	<div class="form_cont">
		<h3>Forgot Password</h3>
		<form autocomplete="Off" id="frmFP" name="frmFP" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="emailid">Email Address: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="emailid" name="emailid" maxlength="100" />
					<div class="erdx"><?php echo (isset($ERR['emailid'])!='')?trim($ERR['emailid']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="pull-right">
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit" />
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT; ?>"><span class="ui-button-text">Cancel</span></a>
					<?php echo $st->get_token_id();?>
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>