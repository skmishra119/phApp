<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
$ERR=array();
$REPORT_DATA='';
//var_dump($USERINFO);
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])=='CLIENT') header("Location:./");

if(isset($_POST['fromdate']) && isset($_POST['todate'])){
	$DTS = (trim($_POST['fromdate'])!="" && trim($_POST['todate'])!="") ? trim($_POST['fromdate']).':'.trim($_POST['todate']):'';
}else{ $DTS=''; }

if(isset($_POST['btnSubmit'])=="Submit"){
	$rules_array = 	array('reptype'=>array('type'=>'string', 'msg'=>'Report Type', 'required'=>true, 'min'=>1, 'max'=>200, 'options'=>false, 'trim'=>true));
    $st->addSource($_POST);
    $st->addRules($rules_array);
    $st->run();
    if(sizeof($st->errors) > 0)
    {
        $st->getError('main', 'Correct the marked fields below.');
        $ERR=$st->errors;
    } else {
    	$CID = (trim($_POST['client'])!="") ? trim($_POST['client']) : "";
    	switch(trim($_POST['reptype'])){
    		case "OVERALL":
    			echo $st->addChartScript("OVERALL_ANALYSIS", trim($CID), $DTS, "chtReport", "pie", "Overall report", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)", true);
    			$REPORT_DATA = $st->showDBDataInfo("OVERALL_ANALYSIS",$USERINFO,$DTS,trim($CID),true,"","","",true);
    		break;
    		case "TOTAL_TEST":
    			echo $st->addChartScript("TOTAL_TEST", trim($CID), $DTS,  "chtReport", "pie", "Test Synopsis", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)", true);
    			$REPORT_DATA = $st->showDBDataInfo("TOTAL_TEST",$USERINFO,$DTS,trim($CID),true,"","","",true);
    		break;
    		case "ALL_TEST":
    			echo $st->addChartScript("TEST_ANALYSIS", trim($CID), $DTS,  "chtReport", "pie", "Testing report", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)", true);
    			$REPORT_DATA = $st->showDBDataInfo("TEST_ANALYSIS",$USERINFO,$DTS,trim($CID),true,"","","",true);
    		break;
    		case "TEST_DOMAIN":
    			echo $st->addChartScript("DOMAIN_TEST_ANALYSIS", trim($CID), $DTS, "chtReport", "column", "Domain wise testing report", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)",true);
    			$REPORT_DATA = $st->showDBDataInfo("DOMAIN_TEST_ANALYSIS",$USERINFO,$DTS,trim($CID),true,"","","",true);
    		break;
    		case "TEST_USER_EVENT":
    			echo $st->addChartScript("USER_EVENT_TEST_ANALYSIS", trim($CID), $DTS, "chtReport", "pie", "Event wise testing report", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)", true);
    			$REPORT_DATA = $st->showDBDataInfo("USER_EVENT_TEST_ANALYSIS",$USERINFO,$DTS,trim($CID),true,"","","",true);
    		break;
    		default:
    			$st->getError('main', 'Correct the marked fields below.');
    			$ERR=$st->errors;
    		break;
    	}
    	
    }
}
?>
<script type="text/javascript">
<!--

$(function(){
	$("a.openRecord").click(function(){
		var wWidth = $(window).width();
		var dWidth = wWidth * 0.9;
		var url = $(this).attr('href');
		var title = $(this).attr('page-title');
		$('<div>').dialog({
			open: function(event, ui) {
			   $(this).load(url);
			},
			modal: true,
			width: dWidth,
			title: title
		});
		return false;
	});
});
$(document).ready(function(){
	$("#frmdt,#todt").bind("cut copy paste keypress",function(e){e.preventDefault();});
	$(function(){ 
		$( "#frmdt" ).datepicker({
			defaultDate: "+1w",
		    dateFormat:"MM dd yy",
		    altField: "#fromdate",
		    altFormat: "yy-mm-dd",
		    changeMonth: true,
		    numberOfMonths: 1,
		    onClose: function( selectedDate ) {
		    	$( "#endt" ).datepicker( "option", "minDate", selectedDate );
		    }
	    });
		$( "#todt" ).datepicker({
			defaultDate: "+1w",
		    dateFormat:"MM dd yy",
		    altField: "#todate",
		    altFormat: "yy-mm-dd",
		    changeMonth: true,
		    numberOfMonths: 1,
		    onClose: function( selectedDate ) {
		    	$( "#tstdt" ).datepicker( "option", "maxDate", selectedDate );
		    }
		});
	});

	$("#frmREP").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields from left side form.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["reptype"];
		rtype = ["req"];
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rv=false;
		}
		if(rv==false)
		{
			$("#msg").html(gerror);
		}
		return rv;
	});
});
//-->
</script>
<?php echo $st->addScripts('includes/chart/'); ?>
<div id="container">
	<div class="contents main_dashboard" style="min-height:400px;">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-file-text fa-fw"></i>&nbsp; Reports</div>
			<div class="indicator"></div>
			<div class="clr"></div>
		</div>
		<div class="contents help_cont">
			<div class="report_cont">
				<div class="helplink">
					<form method="post" name="frmREP" id="frmREP" class="main_form date-selector">
						<div class="frm">
							<div class="lbl">
								<label for="reptype">Report Type: <span>*</span></label>
							</div>
							<div class="txt">
								<?php echo $st->showDrps("REPORT_TYPE", "", ((isset($_POST['reptype'])!='')?trim($_POST['reptype']):''), "reptype", "width:100%;"); ?>
								<div class="erdx"><?php echo (isset($ERR['reptype'])!='')?trim($ERR['reptype']):''; ?></div>
							</div>
						</div>
						<div class="frm">
							<div class="lbl">
								<label for="client">Client: </label>
							</div>
							<div class="txt">
								<?php echo $st->showDrps("REPORT_CLIENT", $USERINFO, ((isset($_POST['client'])!='')?trim($_POST['client']):''), "client", "width:100%;"); ?>
								<div class="erdx"><?php echo (isset($ERR['client'])!='')?trim($ERR['client']):''; ?></div>
							</div>
						</div>
						
						<div class="frm">
							<div class="lbl">
								<label for="startdate">From Date: </label>
							</div>
							<div class="txt date_picker">
								<input type="text" class="form-control" maxlength="100" id="frmdt" name="frmdt" value="<?php if(isset($_POST['frmdt'])) echo $_POST['frmdt'];?>"  autocomplete="off" />
								<input type="hidden" id="fromdate" name="fromdate" value="<?php if(isset($_POST['fromdate'])) echo $_POST['fromdate'];?>"/>
								<div class="erdx"><?php echo (isset($ERR['fromdate'])!='')?trim($ERR['fromdate']):''; ?></div>
							</div>
						</div>
						<div class="frm">
							<div class="lbl">
								<label for="enddate">To Date: </label>
							</div>
							<div class="txt date_picker">
								<input type="text" class="form-control select_date " maxlength="100" id="todt" name="todt" value="<?php if(isset($_POST['todt'])) echo $_POST['todt'];?>"  autocomplete="off" />
								<input type="hidden" id="todate" name="todate"  value="<?php if(isset($_POST['todate'])) echo $_POST['todate'];?>"/>
								<div class="erdx"><?php echo (isset($ERR['todate'])!='')?trim($ERR['todate']):''; ?></div>
							</div>
						</div>
						<input type="hidden" id="hdnRPT" name="hdnRPT" />
						<div class="frm">
							<div class="ctrls pull-right">
							<?php echo $st->get_token_id();?>
								<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit &nbsp;&#xf058;" />
							</div>
						</div>
					</form>
				</div>
				<div id="dvRPT" class="helpcontents">
					<div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
					<div class="frm">
						<canvas id="canvas" style="display: none;"></canvas>
						<div id="chtReport"></div>
					</div>
					<div class="frm">
						<div title="Report Details"">
							<p><?php 
								echo $REPORT_DATA;
								?>
							</p>
							
						</div>
					</div>
				</div>	
				<div class="clr"></div>
			</div>
		</div>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>