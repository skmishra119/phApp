<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.DROOT.$st->IncludePath.'header.inc.php';
$ERR=array();
$LOCN = '';

if(trim($USERINFO['userid'])=='') $LOCN='./';
elseif(trim($USERINFO['role'])=='CLIENT') $LOCN = DROOT.'./';
if(trim($LOCN)!="") header("Location:".trim($LOCN));
$DTS='';
if(isset($_POST['btnSubmit'])=="Submit"){
	$rules_array = 	array('fromdate'=>array('type'=>'date', 'msg'=>'From Date', 'required'=>true, 'min'=>10, 'max'=>20, 'options'=>false, 'trim'=>true),
			'todate'=>array('type'=>'date', 'msg'=>'To Date', 'required'=>true, 'min'=>10, 'max'=>20, 'options'=>false, 'trim'=>true));
	$st->addSource($_POST);
	$st->addRules($rules_array);
	$st->run();
	if(sizeof($st->errors) > 0)
	{
		$st->getError('main', 'Correct the marked fields below.');
		$ERR=$st->errors;
	}else{
		$DTS = (isset($_POST['fromdate'])!="" && isset($_POST['todate'])!="") ? trim($_POST['fromdate']).':'.trim($_POST['todate']):'';
	}
}
echo $st->addChartScript("OVERALL_ANALYSIS", "", $DTS, "chtOATest", "pie", "Overall Analysis", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)");
echo $st->addChartScript("TEST_ANALYSIS", "", $DTS, "chtTSTest", "pie", "Campaign Analysis", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)");
echo $st->addChartScript("DOMAIN_TEST_ANALYSIS", "", $DTS, "chtDMTest", "column", "Domain wise Campaign Analysis", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)");
echo $st->addChartScript("USER_EVENT_TEST_ANALYSIS", "", $DTS, "chtGRTest", "column", "Event wise Campaign Analysis", "Result: <b>{point.y}</b> ({point.percentage:.1f}%)");
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

	$("#frmDB").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> Correct the marked fields below.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		rfield = ["frmdt","todt"];
		rtype = ["tdate","tdate"];
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
			<div class="frmname"><i class="fa fa-dashboard"></i>&nbsp; Dashboard</div>
			<div class="indicator"></div>
			<div class="clr"></div>
		</div>
		
		<div class="frm border_gap">
			<div  class="dashboard_left_cont">
				<div class="graph_cont">
					<div id="chtOATest"></div>
					<div class="pull-right">
						<a id="btnchtOATest" page-title="Overall Analysis" class="openRecord btn btn-default" href="<?php echo DROOT.XROOT; ?>showData?typ=OVERALL_ANALYSIS&dts=<?php echo $DTS; ?>&pdf=true&pg=<?php echo $st->encrypt('Overall Analysis'); ?>&cht=false" rel="ovrDialog">Show Data &nbsp;<i class="fa fa-bar-chart"></i></a>
					</div>
				</div>
				<div class="graph_cont">
					<div id="chtTSTest"></div>
					<div class="pull-right">
						<a page-title="Campaign Analysis" id="btnchtTSTest" class="openRecord  btn btn-default" rel="anlDialog" href="<?php echo DROOT.XROOT; ?>showData?typ=TEST_ANALYSIS&dts=<?php echo $DTS; ?>&pdf=true&pg=<?php echo $st->encrypt('Test Analysis'); ?>&cht=false">Show Data &nbsp;<i class="fa fa-bar-chart"></i></a>
						
					</div>
				</div>
				<div class="graph_cont">
					<div id="chtGRTest"></div>
					<div class="pull-right">
						<a page-title="Event wise Campaign Analysis" id="btnchtGRTest" class="openRecord  btn btn-default" rel="grpDialog" href="<?php echo DROOT.XROOT; ?>showData?typ=USER_EVENT_TEST_ANALYSIS&dts=<?php echo $DTS; ?>&pdf=true&pg=<?php echo $st->encrypt('Event wise Test Analysis'); ?>&cht=false">Show Data &nbsp;<i class="fa fa-bar-chart"></i></a>
					</div>
				</div>
				<div class="graph_cont">
					<div id="chtDMTest"></div>
					<div class="pull-right">
						<a id="btnchtDMTest" page-title="Domain wise Campaign Analysis" class="openRecord  btn btn-default" rel="dmnDialog" href="<?php echo DROOT.XROOT; ?>showData?typ=DOMAIN_TEST_ANALYSIS&dts=<?php echo $DTS; ?>&pdf=true&pg=<?php echo $st->encrypt('Domain wise Test Analysis'); ?>&cht=false">Show Data &nbsp;<i class="fa fa-bar-chart"></i></a>
					</div>
				</div>
			</div>
			<div class="dashboard_right_cont">
				<div class="frm form_cont">
					<div class="frmhead select_date_text">
						<div class="frmname ">Select Date</div>
						<div class="indicator">* (Required)</div>
						<div class="clr"></div>
					</div>
                    
					<div class="frm">
						<form method="post" name="frmDB" id="frmDB" class="main_form date-selector"><div class="frm" id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
							<div class="frm">
								<div class="lbl">
									<label for="startdate">From Date: <span>*</span></label>
								</div>
								<div class="txt date_picker">
									<input type="text" class="form-control" maxlength="100" id="frmdt" name="frmdt" value="<?php if(isset($_POST['frmdt'])) echo $_POST['frmdt'];?>" autocomplete="off"  />
									<input type="hidden" id="fromdate" name="fromdate" value="<?php if(isset($_POST['fromdate'])) echo $_POST['fromdate'];?>"/>
									<div class="erdx"><?php echo (isset($ERR['fromdate'])!='')?trim($ERR['fromdate']):''; ?></div>
								</div>
							</div>
							<div class="frm">
								<div class="lbl">
									<label for="enddate">To Date: <span>*</span></label>
								</div>
								<div class="txt date_picker">
									<input type="text" class="form-control" maxlength="100" id="todt" name="todt" value="<?php if(isset($_POST['todt'])) echo $_POST['todt'];?>"  autocomplete="off" />
									<input type="hidden" id="todate" name="todate" value="<?php if(isset($_POST['todate'])) echo $_POST['todate'];?>"/>
									<div class="erdx"><?php echo (isset($ERR['todate'])!='')?trim($ERR['todate']):''; ?></div>
								</div>
							</div>
							<div class="frm">
								<div class="ctrls pull-right">
								<?php echo $st->get_token_id();?>
									<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit &nbsp;&#xf058;" />
								</div>
							</div>
						</form>
					</div>
				</div>
				<?php echo $st->showDashboardOtherInfo($USERINFO,$DTS).'<hr/>';?>
			</div>
			<div class="clr"></div>
		</div>
	</div>
</div>
<?php require_once '../'.DROOT.$st->IncludePath.'footer.inc.php'; ?>