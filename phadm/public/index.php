<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
if(trim($USERINFO['userid'])!='' && trim($USERINFO['role'])!='CLIENT') header("Location: dashboard");

//echo $cr->encrypt('Sushami1a').' XX '.$cr->decrypt('d0f23993eadb4684904a634d97e24ff1');
$ERR=array();

if(isset($_POST['btnSubmit'])=="Sign In"){
	$rules_array = 	array('emailid'=>array('type'=>'email', 'msg'=>'Email address', 'required'=>true, 'min'=>5, 'max'=>100, 'options'=>false, 'trim'=>true),
				'password'=>array('type'=>'string', 'msg'=>'Password', 'required'=>true, 'min'=>6, 'max'=>20,'options'=>false, 'trim'=>true));
	//$st->addSource($_POST);
	//$val = new validation();
    /*** use POST as the source ***/
    $st->addSource($_POST);
    /*** add a form field rule ***/
    //$val->addRule('emailid', 'email', 'Email address', true, 1, 255, true);
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
		$st->Authenticate('ADMIN');
		if(count($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: dashboard");
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmLogin").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["emailid","password"];
		rtype = ["email","pwd"];
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
			<div class="frmname"><i class="fa fa-sign-in"></i>&nbsp; Sign In</div>
			<div class="indicator">* (Required)</div>
			<div class="clr"></div>
		</div>
		<form id="frmLogin" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="emailid">Email Address: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="emailid" name="emailid" />
					<div class="erdx"><?php echo (isset($ERR['emailid'])!='')?trim($ERR['emailid']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<label for="password">Password: <span>*</span></label>
				<input type="password" class="form-control" maxlength="20" id="password" name="password" autocomplete="off" />
				<div class="erdx"><?php echo (isset($ERR['password'])!='')?trim($ERR['password']):''; ?></div>
			</div>
			<div class="frm">
				<div class="ctrls pull-right">
					<?php echo $st->get_token_id();?>
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Sign In &nbsp;&#xf090;" />
					<input type="reset" class="btn btn-default btn-cancel" id="btnCancel" name="btnCancel" value="Cancel &nbsp;&#xf057;" />
				</div>
                <div class="clear"></div>
				<div class="ctrls pull-right">
					<a href="forgot_password">Forgot password?</a>
					<!--  <a href="register">Need account?</a> -->
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.DROOT.$st->IncludePath.'footer.inc.php'; ?>