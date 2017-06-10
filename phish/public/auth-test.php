<?php
define('DROOT','./');
define('XROOT','../../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
$TYP=trim($_REQUEST['typ']);
$SYSLNK=trim($_REQUEST['pg']);

if(trim($SYSLNK)==''){
	header("Location: ".XROOT."error/404/".$st->encrypt('false'));
}

$WHR="authlink='".trim($SYSLNK)."' and verified='YES' and PGS='WAITING'";
$VDATA=$st->getDataArray("t_pt_campaign", "sessionid,test_value,systemlink,authlink", $WHR, "");

//$tst_val = json($VDATA[0]['test_value']);
//var_dump(json_decode($tst_val));
//var_dump(json_decode('{"PROCESS":"AUTHTEST","domain":"trushieldinc.com, gmail.com","hdnDom":"trushieldinc.com, gmail.com","testname":"Test Campaign on Jan 20 2017"}',true));



$ERR=array();

if(sizeof($VDATA)<=0){
	header("Location: ".XROOT."showmsg/error/".$st->encrypt('AUTH_TEST'));
}
$TEST=json_decode($VDATA[0]['test_value'],true);
//var_dump($TEST);
if(isset($_POST['btnSubmit'])=="Authorize"){
	//var_dump($TEST);
	if(sizeof($TEST)<=0){
		header("Location: ".XROOT."error/404/".$st->encrypt('false'));
	}else{
		$p=$st->sanitized;
		$st->getModelData($TEST);
		$st->Submit('TEST-RUN',"",$USERINFO);
		if(sizeof($st->errors) > 0){
			$ERR=$st->errors;
		}else{
			header("Location: ".XROOT."showmsg/success/".$st->encrypt('AUTH_TEST'));
		}
	}
}
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
});
//-->
</script>
<div class="login_cont register">
	<div class="form_cont">
		<h3>Authorize Campaign</h3>
		<form id="frmTestAuth" method="post" class="main_form">
			<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
			<?php $CDATA=$st->getUserInfo(trim($TEST['clientid']));
			if(sizeof($CDATA)>0):?> 
			<div class="frm">
				<div id="clientInfo" style="color:#000;">Client Name: <?php echo trim($CDATA['fullname']).'<br/>Email Address: '.trim($CDATA['emailid']); ?><br/>Organization: <?php echo trim($CDATA['orgname']); ?><br/>Contact Phone:<?php echo trim($CDATA['phone']);?></div>
			</div>
			<?php endif; ?>
			<div class="frm">
				<div class="lbl">
					<label for="authemail">Your Email Address: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="text" class="form-control" maxlength="100" id="authemail" name="authemail" value="<?php if(isset($_POST['authemail'])) echo $_POST['authemail'];?>" />
					<div class="erdx"><?php echo (isset($ERR['authemail'])!='')?trim($ERR['authemail']):''; ?></div>
				</div>
			</div>
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php echo trim($SYSLNK); ?>" />
			<div class="frm">
				<div class="ctrls pull-right">
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Authorize" />
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>