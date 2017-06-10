 <?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.DROOT.$st->IncludePath.'header.inc.php';
//var_dump($USERINFO);
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])=='CLIENT') header("Location:./");
$FILTER=array();
$ERR=array();
var_dump($_POST);
$DATA=$st->getDataArray("t_pt_users", "userid, emailid, fullname, role, orgname, status, date_format(createdon,'%b %d %Y') as 'createdon'", "status!='DELETED'", "order by userid");

if(isset($_POST['hdnMode'])!=''){
	
	if(trim($_POST['hdnMode'])=='D'){
		$st->getModelData($_POST);
		$st->delete("USERS",$_POST['hdnRID'],$USERINFO);
		if(sizeof($st->errors) > 0)
	    {
	       	$ERR=$st->errors;
	    }else{
	    	header("Location: users");
	    }
	}elseif(trim($_POST['hdnMode'])=='RP'){
		$st->getModelData($_POST);
		$st->Submit("RESET_PWD",$_POST['hdnRID'],$USERINFO);
		if(sizeof($st->errors) > 0)
	    {
	       	$ERR=$st->errors;
	    }else{
	    	header("Location: users");
	    }
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
			<div class="frmname"><i class="fa fa-user fa-fw"></i> Users</div>
			<div class="pgtools"><?php echo $st->showPageToolBox('USERS','LIST',$USERINFO['role'],$DATA); ?></div>
			<div class="clr"></div>
		</div>
		<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="frm ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
		<div class="frm">
			<div class="grid">
				<?php echo $st->showGrid('USERS',$USERINFO, $FILTER, true); ?>	
			</div>
		</div>
	</div>
</div>
<?php require_once '../'.DROOT.$st->IncludePath.'footer.inc.php'; ?>