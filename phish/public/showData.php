<?php
require_once '../settings.inc.php';
define('DROOT','./');
$st->setMetas("name", "TruShield Security Solutions");
session_start();
global $UID;
$UID = (isset($_SESSION['UID'])!='')?$_SESSION['UID']:'';
global $USERINFO;
$USERINFO = $st->getUserInfo($UID);
//$UPARAMS=$st->getUserParams($UID);
if(!defined('XROOT')) {
	define('XROOT','');
}
//var_dump($USERINFO);

//try{
	if(trim($USERINFO['role'])!='CLIENT') header("Location:./");
	$pdf = (isset($_REQUEST['pdf']) != '') ? $_REQUEST['pdf'] : false;
	$PGT = (isset($_REQUEST['pg'])!='') ? $_REQUEST['pg'] : ((isset($_REQUEST['fr'])!='')? urldecode($st->encrypt(trim($_REQUEST['fr']))):'');
	$PGX = (trim($PGT)!='') ? $st->decrypt($PGT):'';
//}catch (Exception $e){
	//header('Location:'.DROOT.XROOT.'error/404/'.$st->encrypt('false'));
//}



//echo $PGX;
$text='';
//var_dump($_REQUEST);
$VTYPS = array('TOTAL-CLIENTS','ACTIVE-CLIENTS','INACTIVE-CLIENTS','TOTAL-TARGETS','TOTAL_TEST','TEST_TARGETS','TOTAL-TESTS','TEST_STATUS','OVERALL_ANALYSIS','TEST_ANALYSIS','DOMAIN_TEST_ANALYSIS','USER_EVENT_TEST_ANALYSIS');
try{
	if(!in_array(strtoupper(trim($_REQUEST['typ'])),$VTYPS)) header('Location:'.DROOT.XROOT.'error/404/'.$st->encrypt('false'));
}catch (Exception $e){
	echo $e->getMessage();
	header('Location:'.DROOT.XROOT.'error/404/'.$st->encrypt('false'));
}
//var_dump($_REQUEST);
switch(strtoupper(trim($_REQUEST['typ']))){
	case "TOTAL-CLIENTS": $text='fa fa-user'; break;
    case "TOTAL-TARGETS": $text='fa fa-object-group fa-fw'; break;
    case "ACTIVE-CLIENTS": $text='fa fa-building fa-fw'; break;
    case "INACTIVE-CLIENTS": $text='fa fa-building fa-fw'; break;
    case "TOTAL-TESTS": $text='fa fa-book fa-fw'; break;
}


$ERR=array();

$_REQUEST['status'] = (isset($_REQUEST['status'])!='' && trim($_REQUEST['status'])!='undefined') ? trim($_REQUEST['status']) :'';
$_REQUEST['oth'] = (isset($_REQUEST['oth'])!='' && trim($_REQUEST['oth'])!='undefined') ? trim($_REQUEST['oth']) :'';
//var_dump($_REQUEST);
//echo $st->addAllStyles();
//echo $st->addAllScripts();
?>
<script type="text/javascript">
<!--
var title = '<?php print($PGX); ?>';
$(function(){
	$('.table-striped').each(function() {
        var thetable=jQuery(this);
        $(this).find('tbody td').each(function() {
            $(this).attr('data-title',thetable.find('thead th:nth-child('+(jQuery(this).index()+1)+')').text());
        });
    });
});
$(document).ready(function(){
	 var table = $('#repTable_<?php echo strtoupper(trim($_REQUEST['typ'])).((trim($_REQUEST['status'])!='')?'_'.$st->decrypt(trim($_REQUEST['status'])):((trim($_REQUEST['oth'])!='')?'_'.$st->decrypt(trim($_REQUEST['oth'])):'')); ?>').DataTable({"columnDefs":[{'targets': 0, 'searchable': false, 'orderable': false }]});
	 $('button.ui-dialog-titlebar-close').on('click', function(e){
		 table.destroy();
		 $('.ui-dialog, .ui-widget-overlay').remove();
	});
});
//-->
</script>
<div class="frm">
	<div class="grid">
		<form id="frmTools" method="post" action="<?php echo XROOT.substr(strtolower($_REQUEST['typ']),0,strlen($_REQUEST['typ'])-1); ?>">
			<?php echo $st->showDBDataInfo(strtoupper($_REQUEST['typ']), $USERINFO, $_REQUEST['dts'], ((isset($_REQUEST['RID'])!='')?$_REQUEST['RID']:''), $pdf, $_REQUEST['status'], $_REQUEST['oth'], $PGT,false); ?>
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print(trim($PGT)); ?>" />
			<input type="hidden" id="hdnMode" name="hdnMode" />
		</form>
	</div>
</div>	
<script type="text/javascript">
$(document).ready(function(){
	$("a.openRecord").on('click',function(){
		var wWidth = $(window).width();
		var dWidth = wWidth * 0.9;
		var url = $(this).attr('href');
		var title = $(this).attr('page-title');
		$('.ui-dialog, .ui-widget-overlay, .ui-dialog-titlebar').remove();
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
</script>