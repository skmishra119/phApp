<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$ERR=array();
if(isset($_POST['btnSubmit'])=="Next >"){
	$_POST['domain'] = (isset($_POST['hdnDom'])!='')?trim($_POST['hdnDom']): '';
	$_POST['clientid']=trim($USERINFO['userid']);
	$rules_array = 	array('clientid'=>array('type'=>'string', 'msg'=>'Client', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true),
			'testname'=>array('type'=>'string', 'msg'=>'Campaign Name', 'required'=>true, 'min'=>1, 'max'=>200, 'options'=>false, 'trim'=>true),
			'domain'=>array('type'=>'string', 'msg'=>'Domain Name', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>array_map('trim',explode(',',$USERINFO['domains'])), 'trim'=>true));
    $st->addSource($_POST);
    $st->addRules($rules_array);
    $st->run();
    if(sizeof($st->errors) > 0)
    {
        $ERR=$st->errors;
    }
	else
	{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('TEST-DOMAIN',"",$USERINFO);
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
}elseif(isset($_SESSION['TEST']['domain'])!=''){
	$_POST['testname']=trim($_SESSION['TEST']['testname']);
	$_POST['domain']=trim($_SESSION['TEST']['domain']);
}
	
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmSelDomain").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		var hDom='';
		var mulDom = $("#domain").select2('data').length;
		for(i=0; i<mulDom; i++){
			hDom += ($.trim(hDom)=='') ? $("#domain").select2('data')[i].text :', '+$("#domain").select2('data')[i].text;
		}
		$("#hdnDom").val(hDom);
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["testname","hdnDom"];
		rtype = ["req","req"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
		return rv;
	});
	
	$(".jqMulti select").select2();
});
//-->
</script>
<?php echo $st->showCampaignProgress('DOMAIN'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Select Domain [Step 1]</h3></div>
	<div class="main_content campaign_cont">
		<form id="frmSelDomain" method="post" class="main_form">
			<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
			<div class="camp_holder">
				<div class="camp_left">
					<div class="frm">
						<div class="uname"><?php echo ucwords(trim($USERINFO['fullname'])); ?></div>
						<div class="umail"><?php echo trim($USERINFO['emailid']); ?></div>
						<div class="org">Organization:</div>
						<div class="orgname"><?php echo ucwords(trim($USERINFO['orgname'])); ?></div>
					</div>
					<div class="frm">
						<div class="otherinfo">
							<p>
								The campaign will run against the selected domain(s) only.
								<br/>The email address of all the targeted recipients must belong to the selected domain(s).
							</p>
						</div>
					</div>
				</div>
				<div class="camp_right">
					<div class="frm">
						<div class="lbl">
							<label for="groupid">Select Domain: <span>*</span></label>
						</div>
						<div class="txt">
							<div class="jqMulti">
							<?php echo $st->showDrps("CLIENT_DOMAIN", $USERINFO, ((isset($_POST['domain'])!='')?trim($_POST['domain']):''), "domain", $st->encrypt(trim($USERINFO['userid'])), "width:100%;",true); ?>
							<input type="hidden" id="hdnDom" name="hdnDom"> 
							<div class="erdx"><?php echo (isset($ERR['domain'])!='')?trim($ERR['domain']):''; ?></div>
							</div>
						</div>
					</div>
					<div class="frm">
						<div class="lbl">
							<label for="testname">Campaign Name: <span>*</span></label>
						</div>
						<div class="txt">
							<input class="form-control" type="text" id="testname" name="testname" maxlength="200" />
							<div class="erdx"><?php echo (isset($ERR['testname'])!='')?trim($ERR['testname']):''; ?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="ctrls">
				<a class="btn btn-prev" href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('XXXX'); ?>"><span class="ui-button-text">< Back</span></a>
				<input type="submit" class="btn  btn-next" id="btnSubmit" name="btnSubmit" value="Next >" />
				<?php echo $st->get_token_id();?>
			</div>
			
		</form>
	</div>
</div>