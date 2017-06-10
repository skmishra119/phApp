<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
global $TEST;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$ERR=array();
$EDTA=$st->getDataArray("t_pt_templates tm", "tm.templatename,tm.subject, tm.body", "tm.templateid='".trim($_SESSION['TEST']['templateid'])."'", "");
if(sizeof($EDTA)>0){
	$SBJ=trim($EDTA[0]['subject']);
	$BDY=nl2br(html_entity_decode(trim($EDTA[0]['body'])));
}
if(isset($_POST['btnSubmit'])=="Submit"){
	
	$signtxt = $_SESSION['TEST']['signature'];
	$signtxt = preg_replace('/\s\s+/is', '\n',$signtxt);
	$_SESSION['TEST']['signature'] = $signtxt;

	if(sizeof($st->errors) > 0)
    {
        $ERR=$st->errors;
    }
	else{
		$_SESSION['TEST']['authname']=trim($USERINFO['authname']);
		$_SESSION['TEST']['authemail']=trim($USERINFO['authemail']);
		$_SESSION['TEST']['SID']=session_id();
		$_SESSION['TEST']['SLINK']=$st->encrypt(trim($_SESSION['TEST']['SID']).':'.trim($USERINFO['userid']));
		$st->getModelData($_SESSION['TEST']);
		$st->Submit('AUTH-TEST',"",$USERINFO);
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
});
//-->
</script>
<?php echo $st->showCampaignProgress('AUTHORIZATION'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Authorization [Step 5]</h3></div>
	<div class="main_content campaign_cont">
		<form autocomplete="Öff" id="frmSelAuth" method="post" class="main_form date-selector" >
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
							<label><strong><?php echo trim($USERINFO['authname'])?></strong> needs to authorize this campaign before run</label>
						</div>
					</div>
					<div class="frm">
						<div class="org">Email From: <span class="umail"><?php echo ucwords(trim($_SESSION['TEST']['fromname'])).' &lt;'.$_SESSION['TEST']['fromemail'].'&gt;'; ?></span><div class="edtLnk"><a href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('EMAIL'); ?>"><i class="fa fa-edit"></i></a></div></div>
						<div class="org">Subject: <span class="umail"><?php echo trim($SBJ); ?></span></div>
						<div class="org">Body:</div> 
						<div class="umail"><?php echo trim($BDY); ?></div>
						<div class="org">Campaign Will Run : <span class="umail">From <?php echo trim($_SESSION['TEST']['startdate']); ?> to <?php echo trim($_SESSION['TEST']['enddate']); ?></span></div>
					</div>
				</div>
			</div>
			<div class="ctrls">
				<a class="btn btn-prev" href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('SCHEDULE'); ?>"><span class="ui-button-text">< Back</span></a>
				<input type="submit" class="btn  btn-next" id="btnSubmit" name="btnSubmit" value="Submit" />
				<?php echo $st->get_token_id();?>
			</div>
		</div>
	</form>
</div>