<?php
define('DROOT','./');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
global $USERINFO;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$GID = (isset($_POST['groupid'])!="") ? $_POST['groupid'] : $_SESSION['TEST']['groupid'];
//echo $st->encrypt('Sushami1a').' XX '.$st->decrypt('d0f23993eadb4684904a634d97e24ff1');
$RID = (isset($_POST['hdnRID'])!='') ? trim($_POST['hdnRID']) : '';
$ERR=array();
$_POST['groupid']=trim($GID);

if(isset($_POST['btnSubmit'])=="Submit"){
	$_POST['clientid']=trim($USERINFO['userid']);
	//$_POST['groupid']=$GID;
	if(trim($_POST['clientid'])=='') $st->getError('main', 'Sorry, unable to process this request. please sign in again.');
	if(trim($_POST['groupid'])=='') $st->getError('main', 'No group selected for upload the target.');
	if(trim($_FILES['target']['tmp_name'])=='')	$st->getError('main', 'No file selected. Select an excel file.');
	
	$ext = pathinfo($_FILES['target']['name'], PATHINFO_EXTENSION);
	//var_dump($ext,$st->extAllow);
	if(!in_array(trim($ext),$st->extAllow)) $st->getError('main', 'Please select a valid excel file.');
	if(sizeof($st->errors)>0){
		$ERR=$st->errors;
	}
	else {
		$folder='uploads/';
		
		//$RET=$st->file_fix_directory($folder,0777);
		//var_dump($RET);
		/*if(is_writable($folder)==false){
			if(@chmod($folder,0777)==false){
				$st->getError('main', 'Folder is not writable, unable to upload and process the file.');
				$ERR=$st->errors;
			}
		}else*/ 
		if(move_uploaded_file($_FILES['target']['tmp_name'], $folder.basename($_FILES['target']['name']))==false){
			$st->getError('main', 'unable to upload the file on server.');
			$ERR=$st->errors;	
		}else{
			@chmod($folder,0755);
			$_POST['targetfile']=$folder.$_FILES['target']['name'];
			$p=$st->sanitized;
			$st->getModelData($_POST);
			$st->Submit("UPLOAD_TARGET", $RID, $USERINFO);
			if(sizeof($st->errors) > 0)
	    	{
	        	$ERR=$st->errors;
	    	} else {
	    		header("Location: campaign");
	    	}
		}
	}	
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
<div id="container">
	<div class="login">
		<div class="frmhead">
			<div class="frmname"><i class="fa fa-upload"></i>&nbsp; Upload Target </div>
			<div class="indicator">* (Required)</div>
			<div class="clr"></div>
		</div>
		<form id="frmUploadTraget" method="post" enctype="multipart/form-data" class="main_form" >
			<div class="frm" id="msg"><?php echo ((isset($ERR['main'])!='') ? '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''); ?></div>
			<div class="frm">
				<div class="lbl">
					<label for="groupid">Group: <span>*</span></label>
				</div>
				<div class="txt">
					<?php echo $st->showDrps("GROUP_CLIENT_MAILING", "", ((isset($_POST['groupid'])!='')?trim($_POST['groupid']):''), "groupid", $st->encrypt($USERINFO['userid']), "width:100%;"); ?>
					 <div class="erdx"><?php echo (isset($ERR['groupid'])!='')?trim($ERR['groupid']):''; ?></div>
				</div>
			</div>
			<div class="frm">
				<div class="lbl">
					<label for="target">Excel file: <span>*</span></label>
				</div>
				<div class="txt">
					<input type="file" id="target" name="target" size="100" />
					<div class="erdx"><?php echo (isset($ERR['target'])!='')?trim($ERR['target']):''; ?></div>
				</div>
			</div>
			<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print($RID); ?>" />
			<div class="frm">
				<div class="ctrls pull-right">
				<?php echo $st->get_token_id();?>
					<input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Submit &nbsp;&#xf058;" />
					<a class="btn btn-default btn-cancel" href="<?php echo DROOT.XROOT; ?>testing"><span class="ui-button-text">Cancel &nbsp;&#xf057;</span></a>
				</div>
			</div>
		</form>
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>
