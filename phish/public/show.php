<?php
define('DROOT','./');
define('XROOT','../../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';

$ERR=array();
$VLS=$_REQUEST;
$lnk=trim($VLS['typ']);
$PGTYP=array('wlnk'=>'Welcome', 'wath'=>'Download','wfrm'=>'Your Info');
$DID=array('wlnk'=>'Link Clicked', 'wath'=>'Attachment Downloaded','wfrm'=>'Web Form Opened');
$RIP=$_SERVER['REMOTE_ADDR'];
$INFO=explode(':',$st->decrypt(trim($VLS['pg'])));
$listid=trim($INFO[0]);
$testid=trim($INFO[2]);
$emailid=trim($INFO[1]);

$cnx=$st->connectDB();
//$sqx="update t_pt_testrun set whatdid=concat(whatdid,',','".trim($DID[$lnk])."'),remote_ip=concat(remote_ip,',','".trim($RIP)."'),activity='FAILED',activitydate=concat(activitydate,',','".date('Y-m-d H:i:s')."') where listid='".trim($listid)."' and testid='".trim($testid)."' and emailid='".trim($emailid)."' and status='RUNNING'";
$sqx="update t_pt_testrun set whatdid='".trim($DID[$lnk])."',remote_ip='".trim($RIP)."',activity='FAILED',activitydate='".date('Y-m-d H:i:s')."' where listid='".trim($listid)."' and testid='".trim($testid)."' and emailid='".trim($emailid)."' and status='RUNNING' and whatdid = 'Email Opened'";
//var_dump($sqx);
$rsx=mysqli_query($cnx,$sqx);
$nr = mysqli_affected_rows($cnx);
if($nr > 0){
	$sqlIns="INSERT INTO t_pt_testlogs (testid,listid,emailid,what,thedate,ip) VALUES ('".trim($testid)."','".trim($listid)."','".trim($emailid)."','".trim($DID[$lnk])."','".date('Y-m-d H:i:s')."','".trim($RIP)."')";
	mysqli_query($cnx, $sqlIns);
}
$st->closeDB($cnx);
$ERR=array();

if(trim($lnk)=='wath'){
	$file='uploads/download.zip';
	if(file_exists($file)) {
		header('Content-Type: application/octet-stream');
    	header('Content-Disposition: attachment; filename='.$file);
    	header('X-Sendfile: '.$file);
		$st->getError('main', 'Thank you for downloading the file.');
		$ERR=$st->errors;
	}else{
		$st->getError('main', 'Sorry, the download attachment page you have requested has not been found or the page you have requested has been moved permanently. Contact administrator.');
		$ERR=$st->errors;
	}
}
elseif(isset($_POST['btnSubmit'])=="Submit"){
// 	$rules_array = 	array('emailid'=>array('type'=>'email', 'msg'=>'Email address', 'required'=>true, 'min'=>5, 'max'=>100, 'options'=>false, 'trim'=>true),
// 			'password'=>array('type'=>'string', 'msg'=>'Password', 'required'=>true, 'min'=>6, 'max'=>20,'options'=>false, 'trim'=>true));
// 	/*** use POST as the source ***/
// 	$st->addSource($_POST);
// 	/*** add a form field rule ***/
// 	$st->addRules($rules_array);
// 	/*** run the validation rules ***/
// 	$st->run();
// 	/*** if there are errors show them ***/
// 	if(sizeof($st->errors) > 0)
// 	{
// 		$st->getError('main', 'Correct the marked fields below.');
// 		$ERR=$st->errors;
// 	}
// 	else
// 	{
		$st->getError('main','The Information you provided did not match with any record.');
		$ERR=$st->errors;
// 	}
}
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmShow").bind("submit", function(e) {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = [];
		rtype = [];
		$('input[req="true"]').each(function() {
         	rfield.push($(this).attr("id"));
         	rtype.push($(this).attr("valid"));
        });
        if(rfield.length <= 0 )
        {
    		rfield = ["emailid","password"];
    		rtype = ["email","pwd"];
        }
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
		//$("input,textarea").each(function(){ cleanup($(this)); });
		return rv;
	});
	
});
//-->
</script>
<?php if(trim($lnk)=='wlnk' || trim($lnk)=='wath') {?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3><?php echo ucfirst(trim($PGTYP[$lnk])); ?></h3></div>
	<div class="main_content">
		<form id="frmShow" name="frmShow" method="post" class="main_form">
			<?php if(trim($lnk)=='wlnk'){ ?>
			<div class="frm">
				<div class="alert alert-danger">Sorry, the page you have requested has not been found or the page you have requested has been moved permanently. Contact administrator.</div>
			</div>
			<?php }elseif(trim($lnk)=='wath'){ ?> 
			<div class="frm">
				<div class="alert alert-danger">Sorry, the page you have requested has not been found or the page you have requested has been moved permanently. Contact administrator.</div>
			</div>
			<?php } ?>
		</form>
	</div>
</div>
<?php }elseif(trim($lnk)=='wfrm'){ ?>
<div class="login_cont web_cont">
	<div class="form_cont">
		<h3><?php echo ucfirst(trim($PGTYP[$lnk])); ?></h3>
		<form id="frmShow" name="frmShow" method="post" class="main_form">
			<div class="msg" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
			<?php $LDATA = $st->getDataArray("t_pt_testrun","listid, formfields, logo","listid='".trim($listid)."' and testid='".trim($testid)."' and emailid='".trim($emailid)."' and status='RUNNING'","");
			if(sizeof($LDATA)>0 && trim($LDATA[0]['formfields'])!=''){
				echo $st->processFormFields(trim($LDATA[0]['formfields']), true);?>
			<div class="frm">
				<div class="ctrls pull-right">
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit" />
						<input type="reset" class="btn btn-default btn-cancel" id="btnCancel" name="btnCancel" value="Cancel" />
				</div>
	        </div>
			<?php } else { ?>
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
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Sign In &nbsp;&#xf090;" />
					<input type="reset" class="btn btn-default btn-cancel" id="btnCancel" name="btnCancel" value="Cancel &nbsp;&#xf057;" />
				</div>
                <div class="clear"></div>
			</div>
			<?php } ?>
		</form>
	</div>
</div>
<?php } require_once '../'.$st->IncludePath.'footer.inc.php'; ?>