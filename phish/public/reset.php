<?php
define('DROOT','./');
define('XROOT','../../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
if(trim($USERINFO['userid'])!='' && trim($USERINFO['role'])!='CLIENT') header("Location: dashboard");

$SLINK=(trim($_REQUEST['pg'])!='')?trim($_REQUEST['pg']):'';

if(trim($SLINK)==''){
	header("Location: ".XROOT."showmsg/error/".$st->encrypt('RESET_PASSWORD')); 
}else{
	$MDATA=$st->getDataArray("t_pt_users", "count(*) as tot", "systemlink='".trim($SLINK)."'", "");
	if(sizeof($MDATA)<=0 || trim($MDATA[0]['tot'])<=0){
		header("Location: ".XROOT."showmsg/error/".$st->encrypt('RESET_PASSWORD'));
	}
}

//echo $st->encrypt('Sushami1a').' XX '.$st->decrypt('d0f23993eadb4684904a634d97e24ff1');
$ERR=array();

if(isset($_POST['btnSubmit'])=="Submit"){
	$_POST['systemlink']=$SLINK;
	$rules_array = 	array('emailid'=>array('type'=>'email', 'msg'=>'Email Address', 'required'=>true, 'min'=>6, 'max'=>20, 'options'=>false, 'trim'=>true),
				'password'=>array('type'=>'string', 'msg'=>'Password', 'required'=>true, 'min'=>6, 'max'=>20, 'options'=>false, 'trim'=>true),
				'retype'=>array('type'=>'string', 'msg'=>'Password', 'required'=>true, 'min'=>6, 'max'=>20,'options'=>false, 'trim'=>true));
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
	else if(trim($_POST['password'])!=trim($_POST['retype'])){
		$st->getError('main', 'Passwords does not match.');
        $ERR=$st->errors;
	}else{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('RESET_PASSWORD',0,0,'CLIENT');
		if(count($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	} else {
    		header("Location: ".XROOT."showmsg/success/".$st->encrypt('RESET_PASSWORD'));
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
	$("#frmForgot").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["emailid","password","retype"];
		rtype = ["email","pwd","pwd"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
		return rv;
	});
	
});
//-->
</script>
<div class="login_cont register">
	<div class="form_cont">
    	<h3>Reset your password</h3>
		<form id="frmForgot" method="post" class="main_form">
			<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
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
				<label for="retype">Retype Password: <span>*</span></label>
				<input type="password" class="form-control" maxlength="20" id="retype" name="retype" autocomplete="off" />
				<div class="erdx"><?php echo (isset($ERR['retype'])!='')?trim($ERR['retype']):''; ?></div>
			</div>
			<div class="frm">
				<div class="ctrls pull-right">
				<?php echo $st->get_token_id();?>
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit" />
					<input type="reset" class="btn btn-default btn-cancel" id="btnCancel" name="btnCancel" value="Cancel" />
				</div>
				
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>