<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
global $TEST;
$ERR=array();

$EDTA=$st->getDataArray("t_pt_templates tm", "tm.templatename,tm.subject, tm.body", "tm.templateid='".trim($_SESSION['TEST']['templateid'])."'", "");
if(sizeof($EDTA)>0){
	$SBJ=trim($EDTA[0]['subject']);
	$BDY=nl2br(html_entity_decode(trim($EDTA[0]['body'])));
}
//var_dump(date('Y-m-d H:i:s'), system('date'));
//die;
if(isset($_POST['btnSubmit'])=="Next >"){
	$STDT=date('Y-m-d H:i:s',strtotime(trim($_POST['startdate'])));
	$ENDT=date('Y-m-d H:i:s',strtotime(trim($_POST['enddate'])));
	if($STDT>=$ENDT){
		$st->getError('main','Start and end date is not valid.');
		$ERR=$st->errors;
	}elseif($STDT<date('Y-m-d H:i:s')){
		$st->getError('main','Start date and time should not be less than current time.');
		$ERR=$st->errors;
	}else{
		$rules_array = 	array('startdate'=>array('type'=>'string', 'msg'=>'From Date', 'required'=>true, 'min'=>10, 'max'=>20, 'options'=>false, 'trim'=>true),
					'enddate'=>array('type'=>'string', 'msg'=>'To Date', 'required'=>true, 'min'=>10, 'max'=>20, 'options'=>false, 'trim'=>true),
					'training'=>array('type'=>'string', 'msg'=>'Tarining', 'required'=>true, 'min'=>1, 'max'=>5, 'options'=>array('YES','NO'), 'trim'=>true));
    	$st->addSource($_POST);
    	$st->addRules($rules_array);
    	$st->run();
    	if(sizeof($st->errors) > 0)
    	{
        	$st->getError('main', 'Correct the marked fields below.');
        	$ERR=$st->errors;
    	}else{
    		$p=$st->sanitized;
			$_SESSION['TEST']['startdate']=trim($_POST['startdate']);
			$_SESSION['TEST']['enddate']=trim($_POST['enddate']);
			$_SESSION['TEST']['training']=trim($_POST['training']);
			/*$_SESSION['TEST']['SID']=session_id();
			$_SESSION['TEST']['SLINK']=$st->encrypt(trim($_SESSION['TEST']['SID']).':'.trim($USERINFO['userid']));*/
			$st->getModelData($_SESSION['TEST']);
			$st->Submit('TEST-SCHEDULE',"",$USERINFO);
			if(sizeof($st->errors) > 0)
    		{
        		$ERR=$st->errors;
    		}
    		else{
    			header("Location: campaign");
    		//require_once 'seltarget.inc.php';
    		}
	   	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}
if(count($_POST)>0 && trim($_POST['hdnMode'])=='D'){
	$st->getModelData($_SESSION['TEST']);
	$st->Submit('TEST-RESET',"",$USERINFO);
	//var_dump($st->errors);
	if(sizeof($st->errors) > 0)
	{
		$ERR=$st->errors;
	}
	else{
		header("Location: campaign");
		//require_once 'seltarget.inc.php';
	}
}
//var_dump($_POST);
//var_dump($ERR);
echo $st->addScripts('includes/timep/');
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#tstdt,#endt").bind("cut copy paste keypress",function(e){e.preventDefault();});
	$(function(){
		$("#tstdt").datetimepicker({
			dateFormat:"MM dd yy",
			timeFormat: "HH:mm", 
			controlType: 'select',
			altField: "#startdate",
			altFieldTimeOnly: false,
			altFormat: "yy-mm-dd",
			altTimeFormat: "HH:mm:ss",
			altSeparator: " "
		});
		$("#endt").datetimepicker({
			dateFormat:"MM dd yy",
			timeFormat: "HH:mm", 
			controlType: 'select',
			altField: "#enddate",
			altFieldTimeOnly: false,
			altFormat: "yy-mm-dd",
			altTimeFormat: "HH:mm:ss",
			altSeparator: " "
		});
		
	});
		  
	$("#btnSubmit").click(function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["tstdt","endt","training"];
		rtype = ["tdatetime","tdatetime","req"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
		return rv;
	});
	$("#btnCancel").click(function(){
		jConfirm("Campaign once cancelled can not be undone.<br/>Are you sure you want to cancel this campaign?","TruPhish",function(r){
			if(r==true){
				$("#hdnMode").val("D");
				$("#frmSelSch").submit();	
			}
		});
	});
	
});
//-->
</script>
<?php echo $st->showCampaignProgress('SCHEDULE'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Schedule [Step 4]</h3></div>
	<div class="main_content campaign_cont"">
		<form autocomplete="Öff" id="frmSelSch" method="post" class="main_form date-selector" >
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
						<div class="org">Targets:<div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('GTARGET'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="umail"><?php echo $st->getReceipientsList(trim($_SESSION['TEST']['listids']),$USERINFO); ?></div>
						<div class="org">Template:<div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('TEMPLATE'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="umail"><?php echo trim($EDTA[0]['templatename']); ?></div>
					</div>
				</div>
				<div class="camp_right">
					<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
					<div class="frm">
						<div class="lbl_label">
							<label>Schedule your campaign</label>
						</div>
					</div>
					<div class="frm">
						<div class="org">Email From: <span class="umail"><?php echo ucwords(trim($_SESSION['TEST']['fromname'])).' &lt;'.$_SESSION['TEST']['fromemail'].'&gt;'; ?></span><div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('EMAIL'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="org">Subject: <span class="umail"><?php echo trim($SBJ); ?></span></div>
					</div>
					<div class="frm start-date-cont">
						<div class="lbl">
							<label for="startdate">Start Date: <span>*</span></label>
						</div>
						<div class="txt">
							<input type="text" class="form-control" maxlength="100" id="tstdt" name="tstdt" value="<?php if(isset($_POST['tstdt'])) echo $_POST['tstdt'];?>"  />
							<input type="hidden" id="startdate" name="startdate" value="<?php if(isset($_POST['startdate'])) echo $_POST['startdate'];?>"/>
							<div class="erdx"><?php echo (isset($ERR['startdate'])!='')?trim($ERR['startdate']):''; ?></div>
						</div>
					</div>
					<div class="frm end-date-cont">
						<div class="lbl">
							<label for="enddate">End Date: <span>*</span></label>
						</div>
						<div class="txt">
							<input type="text" class="form-control" maxlength="100" id="endt" name="endt" value="<?php if(isset($_POST['endt'])) echo $_POST['endt'];?>"  />
							<input type="hidden" id="enddate" name="enddate" value="<?php if(isset($_POST['enddate'])) echo $_POST['enddate'];?>" />
							<div class="erdx"><?php echo (isset($ERR['enddate'])!='')?trim($ERR['enddate']):''; ?></div>
						</div>
					</div>
					<div class="frm">
						<div class="lbl">
							<label for="training">Need Training (for targets who will fail the test): <span>*</span></label>
						</div>
						<div class="txt">
							<?php echo $st->showDrps("YESNO", "", ((isset($_POST['training'])!='')?trim($_POST['training']):''), "training", $st->encrypt(trim($USERINFO['userid'])), "width:100%;"); ?>
							<div class="erdx"><?php echo (isset($ERR['training'])!='')?trim($ERR['training']):''; ?></div>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" id="hdnMode" name="hdnMode" value="" />
			<div class="ctrls">
				<input type="button" class="btn btn-prev" id="btnCancel" name="btnCancel" value="Cancel"/>
				<input type="submit" class="btn  btn-next" id="btnSubmit" name="btnSubmit" value="Next >" />
				<?php echo $st->get_token_id();?>
			</div>
		</form>
	</div>
</div>