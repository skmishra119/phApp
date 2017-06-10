<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//var_dump($USERINFO);
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$FILTER=array();
$ERR=array();
$DATA=$st->getDataArray("t_pt_groups", "groupid, clientid, groupname, date_format(createdon,'%b %d %Y') as 'createdon'", "clientid='".trim($USERINFO['userid'])."' and status!='DELETED'", "order by clientid");
if(isset($_POST['hdnMode'])=='D'){
	$st->getModelData($_POST);
	$st->delete("GROUPS",$_POST['hdnRID'],$USERINFO);
	if(sizeof($st->errors) > 0)
    {
       	$ERR=$st->errors;
    }else{
    	header("Location: groups");
    }
}
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	
});
//-->
</script>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Groups</h3><div class="pgtools"><?php echo $st->showPageToolBox('GROUPS','LIST',$USERINFO['role'],$DATA); ?></div></div>
	<div class="main_content">
		<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
		<div class="frm">
			<div class="grid">
				<?php echo $st->showGrid('GROUPS',$USERINFO, $FILTER, true); ?>
			</div>
		</div>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>