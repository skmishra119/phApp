<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//var_dump($USERINFO);
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])=='CLIENT') header("Location:./");
$FILTER=array();
$ERR=array();
if($USERINFO['role']=="SYSADMIN")
	$DATA=$st->getDataArray("t_pt_mailinglist m", "m.listid, (select g.groupname from t_pt_groups g where m.groupid=g.groupname) as groupname, (select concat_ws(' - ',u.fullname,u.emailid) from t_pt_users u where m.clientid=u.userid) as clientname, m.emailid, m.fullname", "m.status!='DELETED'", "order by m.listid");
if($USERINFO['role']=="MANAGER")
	$DATA=$st->getDataArray("t_pt_mailinglist m inner join t_pt_users ux", "m.listid, (select g.groupname from t_pt_groups g where m.groupid=g.groupname) as groupname, (select concat_ws(' - ',u.fullname,u.emailid) from t_pt_users u where m.clientid=u.userid) as clientname, m.emailid, m.fullname", "m.status!='DELETED' and (m.clientid=ux.userid or m.clientid=ux.parentid) and (u.userid ='".trim($USERINFO['userid'])."')", "order by m.listid");
    
if(isset($_POST['hdnMode'])=='D' && sizeof($st->errors)<=0){
	$st->getModelData($_POST);
	$st->delete("MAILING-LISTS",$_POST['hdnRID'],$USERINFO);
	if(sizeof($st->errors) > 0)
    {
       	$ERR=$st->errors;
    }else{
    	header("Location: mailing-lists");
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
			<div class="frmname"><i class="fa fa-building fa-fw"></i>&nbsp; Targets</div>
			<div class="pgtools"><?php echo $st->showPageToolBox('MAILING-LISTS','LIST',$USERINFO['role'],$DATA); ?></div>
			<div class="clr"></div>
		</div>
		<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="frm ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
		<div class="frm">
			<div class="grid">
				<?php echo $st->showGrid('MAILING_LISTS',$USERINFO, $FILTER, true); ?>
			</div>
		</div>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>