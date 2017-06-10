<?php
define('DROOT','./');
//define('XROOT', '../');
//session_start();
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
//if(isset($_REQUEST['__uid'])!='') $_SESSION['UID']=$st->decrypt($_REQUEST['__uid']);

//var_dump($_REQUEST,$_SESSION);

require_once '../'.$st->IncludePath.'header.inc.php';
if(trim($USERINFO['userid'])!='') {
	if(trim($USERINFO['role'])=='MANAGER')
		header("Location: dashboard");
	elseif(trim($USERINFO['role'])=='CLIENT')
		header("Location: campaign");
	else
		header("Location: ".DROOT.XROOT."error/no-auth/".$st->encrypt('false'));
}
//echo $st->encrypt('Sushami1a').' XX '.$st->decrypt('d0f23993eadb4684904a634d97e24ff1');
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
    //var_dump($st);
    if(sizeof($st->errors) > 0)
    {
        $st->getError('main', 'Correct the marked fields below.');
        $ERR=$st->errors;
    }
	else
	{
		//$st->no_csrf(trim($_SESSION['token_id']), trim($_POST['csrftoken']));
		if(sizeof($st->errors) == 0)
		{
			$p=$st->sanitized;
			$st->getModelData($_POST);
			$st->Authenticate('CLIENT');
			if(count($st->errors) > 0)
	    	{
	        	$ERR=$st->errors;
	    	} else {
	    		header("Location: campaign");
	    	}
		}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}
//var_dump($_SESSION);
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
<?php //print_r($_SESSION);?>
<div class="login_cont">
	<div class="form_cont">
    	<h3>Login to your Account</h3>
        <form autocomplete="Off" id="frmLogin" method="post" class="main_form">
        	<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
        	
			<div class="form_field">
               	<span class="icon_cont"><i class="fa fa-user"></i></span>
               	<input type="text" class="form-control" maxlength="100" id="emailid" name="emailid" />
				<div class="erdx"><?php echo (isset($ERR['emailid'])!='')?trim($ERR['emailid']):''; ?></div>
            </div>
            <div class="form_field">
               	<span class="icon_cont"><i class="fa fa-lock"></i></span>
               	<input type="password" class="form-control" maxlength="20" id="password" name="password" autocomplete="off" />
				<div class="erdx"><?php echo (isset($ERR['password'])!='')?trim($ERR['password']):''; ?></div>
            </div>
            <div class="form_field">
               	<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Sign In" />
            </div>
            <div class="form_field last">
            	<a href="register" class="signup_btn">Register</a>
                <a href="forgot_password" class="signup_btn pull-right">Forgot Password?</a>
                <?php echo $st->get_token_id();?>
            </div>
        </form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>
