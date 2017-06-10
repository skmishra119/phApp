<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//var_dump($USERINFO);
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='SYSADMIN') header("Location:./");
$FILTER=array();
$ERR=array();
$DATA=$st->getDataArray("t_pt_settings", "settingid, clientid, params, status, date_format(createdon,'%b %d %Y') as 'createdon'", "status!='DELETED'", "order by settingid");
if(isset($_POST['hdnMode'])=='D'){
	$st->getModelData($_POST);
	$st->delete("SETTINGS",$_POST['hdnRID'],$USERINFO);
	if(sizeof($st->errors) > 0)
    {
       	$ERR=$st->errors;
    }else{
    	header("Location: settings");
    }
}
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	
});
//-->
</script>
<div id="container">
	<div class="contents">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-gear fa-fw"></i>&nbsp; Settings</div>
			<div class="pgtools"><?php echo $st->showPageToolBox('SETTINGS','LIST',$USERINFO['role'],$DATA); ?></div>
			<div class="clr"></div>
		</div>
		<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="frm ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
		<div class="frm">
			<div class="grid">
				<?php echo $st->showGrid('SETTINGS',$USERINFO, $FILTER, true); ?>	
			</div>
		</div>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>
