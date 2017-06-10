<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//var_dump($USERINFO);
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$FILTER=array();
$ERR=array();
$DATA=$st->getDataArray("t_pt_groups g, t_pt_users u, t_pt_test t, t_pt_templates tp", "t.testid as 'testid', u.fullname as 'Client Name', g.groupname as 'Group Name', g.domain as 'Domain', tp.templatename as 'Template', (case when t.process='TESTRUN' then 'SCHEDULED' else t.process end) as 'Status', date_format(t.createdon,'%M %d %Y %H:%i:%s') as 'Created On', date_format(t.startdate,'%M %d %Y %H:%i:%s') as 'Stated On', date_format(t.enddate,'%M %d %Y %H:%i:%s') as 'Ended On', (CHAR_LENGTH(t.listids) - CHAR_LENGTH(REPLACE(t.listids, ',', '')) + 1) as 'Targets'", "t.clientid = u.userid and t.groupid = g.groupid and t.templateid = tp.templateid and u.userid = ".trim($USERINFO['userid']), "");
if(isset($_POST['hdnMode'])=='D'){
	$st->getModelData($_POST);
	$st->delete("TESTS",$_POST['hdnRID'],$USERINFO);
	if(sizeof($st->errors) > 0)
    {
       	$ERR=$st->errors;
    }else{
    	header("Location: tests");
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
		$('.ui-dialog, .ui-widget-overlay').remove();
		$("<div>").dialog({
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
	
});
//-->
</script>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Tests</h3><div class="pgtools"><?php echo $st->showPageToolBox('TESTS','LIST',$USERINFO['role'],$DATA); ?></div></div>
	<div class="main_content">
		<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
		<div class="frm">
			<div class="grid">
				<?php echo $st->showGrid('TESTS',$USERINFO, $FILTER, true); ?>
			</div>
		</div>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>