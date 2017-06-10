<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$ERR=array();
if(isset($_POST['btnSubmit'])=="Next >"){
	$_POST['clientid']=trim($USERINFO['userid']);
	if(trim($_POST['clientid'])=='') $st->getError('main', 'Sorry, unable to process this request. please sign in again.');
	if(trim($_FILES['target']['tmp_name'])=='')	$st->getError('main', 'No file selected. Select an excel file.');
	
	$ext = pathinfo($_FILES['target']['name'], PATHINFO_EXTENSION);
	if(!in_array(trim($ext),$st->extAllow)) $st->getError('main', 'Please select a valid excel file.');
	if(sizeof($st->errors)>0){
		$ERR=$st->errors;
	}
	else {
		$folder='uploads/';
		if(move_uploaded_file($_FILES['target']['tmp_name'], $folder.basename($_FILES['target']['name']))==false){
			$st->getError('main', 'unable to upload the file on server.');
			$ERR=$st->errors;	
		}else{
			//@chmod($folder,0755);
			$_POST['targetfile']=$folder.$_FILES['target']['name'];
			$p=$st->sanitized;
			$st->getModelData($_POST);
			$st->Submit("UPLOAD_TARGET", "", $USERINFO);
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
	$("div.target_cont a").click(function(e){
		$(".target_cont a").removeClass("selected");
		$(this).addClass("selected");
	});
	$("#frmGetTarget").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		$(".erd,.erdx").each(function(){$(this).remove();});
		$("#targettype").val($(".target_cont a.selected").attr("data-type"));
		if($.trim($("#targettype").val())==""){
			var emsg="<div class='erd'>Select at least one option to proceed.</div>";
			$(emsg).insertAfter($("#targettype"));
			rv=false;
		}
		return rv;
	});
	
});
//-->
</script>
<?php echo $st->showCampaignProgress('UPTARGET'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Target [Step 2]</h3></div>
	<div class="main_content campaign_cont">
		<form autocomplete="Off" id="frmUpTarget" method="post" class="main_form" enctype="multipart/form-data">
			<div class="camp_holder">
				<div class="camp_left">
					<div class="frm">
						<div class="uname"><?php echo ucwords(trim($USERINFO['fullname'])); ?></div>
						<div class="umail"><?php echo trim($USERINFO['emailid']); ?></div>
						<div class="org">Organization:</div>
						<div class="orgname"><?php echo ucwords(trim($USERINFO['orgname'])); ?></div>
						<div class="org">Domain:</div>
						<div class="orgname"><?php echo trim($_SESSION['TEST']['domain']); ?></div>
						<div class="sub">Campaign:</div>
						<div class="subname"><?php echo trim($_SESSION['TEST']['testname']); ?></div>
					</div>
					<div class="frm">
						<div class="otherinfo">
							<p>
								The campaign will run against the recipients uploaded and selected from the list. 
							</p>
						</div>
					</div>
				</div>
				<div class="camp_right">
					<div class="frm">
						<div class="lbl_label">
							<label>Upload recipients from Excel file</label>
							<div class="tip">
								<p>The file should be a valid Excel (.xls or .xlsx) file.</p>
								<p>The first row in the first worksheet should be <strong>Full Name</strong>, <strong>Email ID</strong> and <strong>Contact Phone</strong>.</p>
								<p>The subsequent rows should have values for the first row and column.</p>
								<p>The data in the file should be:
								<table width="90%" cellspacing="0" cellpadding="0" border="1">
									<thead>
										<tr>
											<th>Full Name</th>
											<th>Email ID</th>
											<th>Contact Phone</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Abc Xyz</td>
											<td>abcx@<?php print($_SESSION['TEST']['domain'])?></td>
											<td>9999999999</td>
										</tr>
										<tr>
											<td>Xyz Abc</td>
											<td>xabc@<?php print($_SESSION['TEST']['domain'])?></td>
											<td>8888888888</td>
										</tr>
										<tr>
											<td>Ddgh Mpls</td>
											<td>llds@<?php print($_SESSION['TEST']['domain'])?></td>
											<td>77777777</td>
										</tr>
									</tbody>
								</table>
								</p>
							</div>
						</div>
					</div>
					
					<div class="frm">
						<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
						<div class="lbl">
							<label for="target">Excel file: <span>*</span></label>
						</div>
						<div class="txt">
							<input type="file" id="target" name="target" size="100" />
							<div class="erdx"><?php echo (isset($ERR['target'])!='')?trim($ERR['target']):''; ?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="ctrls">
				<a class="btn btn-prev" href="<?php echo DROOT.XROOT; ?>campaign?pg=<?php echo $st->encrypt('GTARGET'); ?>"><span class="ui-button-text">< Back</span></a>
				<input type="submit" class="btn  btn-next" id="btnSubmit" name="btnSubmit" value="Next >" />
				<?php echo $st->get_token_id();?>
			</div>
			
		</form>
	</div>
</div>