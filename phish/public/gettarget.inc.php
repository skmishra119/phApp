<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$DMN=explode(', ',$_SESSION['TEST']['domain']);
$GDMNS='';
foreach ($DMN as $dms){
	$GDMNS .= (trim($GDMNS)=='') ? "'".trim($dms)."'" : ",'".trim($dms)."'";
}
$MDATA = $st->getDataArray("t_pt_mailinglist","emailid,fullname,contactphone","SUBSTRING_INDEX(emailid,'@',-1) in (".$GDMNS.") and clientid='".trim($USERINFO['userid'])."' and status='ACTIVE'","");
$ERR=array();
if(isset($_POST['btnSubmit'])=="Next >"){
	$_POST['clientid']=trim($USERINFO['userid']);
	$rules_array = 	array('clientid'=>array('type'=>'string', 'msg'=>'Client', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true),
				'targettype'=>array('type'=>'string', 'msg'=>'Option', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>array('SLTARGET','UPTARGET','ADTARGET'), 'trim'=>true));
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
		$st->Submit('TEST-GTARGET',"",$USERINFO);
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
}
//var_dump($_POST);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("div.target_cont a").click(function(e){
		$(".target_cont a").removeClass("selected");
		$(this).addClass("selected");
	});
	$("#frmGetTarget").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		$(".erd,.erdx").each(function(){$(this).remove();});
		$("#targettype").val($(".target_cont a.selected").attr("data-type"));
		if($.trim($("#targettype").val())==""){
			var emsg="<div class='erd'>Select at least one option to proceed.</div>";
			$(emsg).insertAfter($("#targettype"));
			rv=false;
		}
		return rv;
	});
	
});
//-->
</script>
<?php echo $st->showCampaignProgress('GTARGET'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Target [Step 2]</h3></div>
	<div class="main_content campaign_cont">
		<form id="frmGetTarget" method="post" class="main_form">
			<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
			<div class="camp_holder">
				<div class="camp_left">
					<div class="frm">
						<div class="uname"><?php echo ucwords(trim($USERINFO['fullname'])); ?></div>
						<div class="umail"><?php echo trim($USERINFO['emailid']); ?></div>
						<div class="org">Organization:</div>
						<div class="orgname"><?php echo ucwords(trim($USERINFO['orgname'])); ?></div>
						<div class="org">Domain:<div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('DOMAIN'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="orgname"><?php echo trim($_SESSION['TEST']['domain']); ?></div>
						<div class="sub">Campaign:</div>
						<div class="subname"><?php echo trim($_SESSION['TEST']['testname']); ?></div>
					</div>
					<div class="frm">
						<div class="otherinfo">
							<p>
								The campaign will run against the selected recipients.
								<br/>Based on previous campaigns against selected domain(s), a list of potential targets are already prepared. 
							</p>
						</div>
					</div>
				</div>
				<div class="camp_right">
					<div class="frm">
						<div class="lbl_label">
							<label>Add Recipients with one of these options:</label>
						</div>
						<div class="txt_label">
							<?php if(sizeof($MDATA)>0){ ?>
							<div class="target_cont">
								<a href="javascript:;" data-type="SLTARGET"><i class="fa fa-list-ul"></i><br/>Select from list</a>
							</div>
							<?php } ?>
							<div class="target_cont">
								<a href="javascript:;" data-type="ADTARGET"><i class="fa fa-user-plus"></i><br/>Manual entry</a>
							</div>
							<div class="target_cont">
								<a href="javascript:;" data-type="UPTARGET"><i class="fa fa-file-text"></i><br/>Upload from Excel</a>
							</div>
							<input type="hidden" id="targettype" name="targettype" />
							<div class="erdx"><?php echo (isset($ERR['targettype'])!='')?trim($ERR['targettype']):''; ?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="ctrls">
				<a class="btn btn-prev" href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('DOMAIN'); ?>"><span class="ui-button-text">< Back</span></a>
				<input type="submit" class="btn  btn-next" id="btnSubmit" name="btnSubmit" value="Next >" />
				<?php echo $st->get_token_id();?>
			</div>
			
		</form>
	</div>
</div>