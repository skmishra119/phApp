<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$MDATA = $st->getDataArray("t_pt_mailinglist","emailid,fullname,contactphone","SUBSTRING_INDEX(emailid,'@',-1)='".trim($_SESSION['TEST']['domain'])."' and clientid='".trim($USERINFO['userid'])."' and status='ACTIVE'","");
$ERR=array();
if(isset($_POST['btnSubmit'])=="Next >"){
	//var_dump(json_decode($_POST['hdnDTA']));
	$_POST['clientid']=trim($USERINFO['userid']);
	//$_POST['hdnDTA']=json_decode($_POST['hdnDTA'], true);
	$_POST['domain'] = (isset($_SESSION['TEST']['domain'])!='') ?  trim($_SESSION['TEST']['domain']) : '';
	$rules_array = 	array('clientid'=>array('type'=>'string', 'msg'=>'Client', 'required'=>true, 'min'=>1, 'max'=>100, 'options'=>false, 'trim'=>true),
				'hdnDTA'=>array('type'=>'string', 'msg'=>'Recipient', 'required'=>true, 'min'=>1, 'max'=>1000000, 'options'=>false, 'trim'=>true));
    $st->addSource($_POST);
    $st->addRules($rules_array);
    $st->run();
    if(sizeof($st->errors) > 0)
    {
        $ERR=$st->errors;
    }
	else
	{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('TEST-ADTARGET',"",$USERINFO);
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
//var_dump($_SESSION);
//var_dump($ERR);
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("div.target_cont a").click(function(e){
		$(".target_cont a").removeClass("selected");
		$(this).addClass("selected");
	});
	$("#frmAddTarget").bind("submit", function() {
		var rv=true;
		$("#msg").html("");$("#msg").val("");
		$(".erd,.erdx").each(function(){$(this).remove();});
		$('#recTable tbody').find('a#btnSave').trigger('click');
		if($.trim($("#hdnDTA").val())==""){
			var emsg="<div class='erd'>There is no record for receipients. Unable to proceed.</div>";
			$(emsg).insertAfter($("#recTable"));
			rv=false;
		}
		return rv;
	});
	
});
//-->
</script>
<?php echo $st->showCampaignProgress('ADTARGET'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Target [Step 2]</h3></div>
	<div class="main_content campaign_cont">
		<form autocomplete="Off" id="frmAddTarget" method="post" class="main_form">
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
								The campaign will run against the recipients added and selected from the list. 
							</p>
						</div>
					</div>
				</div>
				<div class="camp_right">
					<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
					<input type="hidden" id="hdnDTA" name="hdnDTA" value="" />
					<div class="erdx"><?php echo (isset($ERR['hdnDTA'])!='')?'<div class="alert alert-danger">'.trim($ERR['hdnDTA']).'</div>':''; ?></div>
					<div class="frm">
						<div class="lbl_label">
							<label>Add Recipients</label>
						</div>
					</div>
					<div class="frm dataTables_wrapper">
						<table id="recTable" width="99%" border="0" cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <th width="25%">Full Name</th>
                                <th width="35%">Email Address</th>
                                <th width="25%">Contact Phone</th>
                                <th width="15%">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>    
                        </tbody>
                   		</table>
                   	</div>
                   	<div class="frm">
                   		<a id="addJL" href="javascript:;"><i class="fa fa-plus-circle"></i></a>
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
<script>
$(document).ready(function(){
	$('a#addJL').click(function(e) {
		var cl=$('#recTable tbody').find('input').length;
		if(cl>0) {
			jAlert('Another record is already active, unable to do this operation.','TruPhish');
			return false;
		}
		//$('#hdnmode').val("ADD");
	    var fnm='<input type="text" id="fullname" name="fullname" tabindex="1" class="form-control" maxlength="100" />';
		var eml='<input type="email" id="emailid" name="emailid" tabindex="2" class="form-control" maxlength="200" />';
		var phn='<input type="tel" id="contactphone" name="contactphone" tabindex="3" class="form-control" maxlength="100" />'; /***@<?php print(trim($_SESSION['TEST']['domain']));?>***/
		var itm='<tr id="TN"><td data-title="Full Name">'+fnm+'</td><td data-title="Email Address">'+eml+'</td><td data-title="Contact Phone">'+phn+'</td><td class="frm_ctrl" align="right"><a href="javascript:;" id="btnSave"><i class="fa fa-save"></i></a><a href="javascript:;" id="btnCancel"><i class="fa fa-minus-circle"></i></a></td></tr>';
		$(itm).appendTo($('#recTable tbody'));
		$("#btnSave").click(function(e) {
			$(".erd").each(function(){$(this).remove();});
			rfield =  ["fullname","emailid"];
			rtype = ["name","email"];
			var rx=true;
			for (i=0;i<rfield.length;i++) 
			{
				if(getValidate(rfield[i],rtype[i])==false)	
				rx=false;
			} 
			if(rx==false) return rx;
			if(rx==true) rx=checkDuplicate("fullname","emailid","contactphone");
	        if(rx==false) return rx;
	        
			var itm='<tr><td data-title="Full Name">'+$("#fullname").val()+'</td><td data-title="Email Address">'+$("#emailid").val()+'</td><td data-title="Contact Phone">'+$("#contactphone").val()+'</td><td class="frm_ctrl" align="right"><a class="editJL" href="javascript:;" onClick="editJL(this);"><i class="fa fa-edit"></i></a><a class="delJL" href="javascript:;" onClick="delJL(this);"><i class="fa fa-trash"></i></a></td></tr>';
			$("#TN").replaceWith(itm);
			$("#recTable").remove("#fullname,#emailid,#contactphone");
			fillJL();
		});
	
		$("#btnCancel").click(function(e) { 
			$("#TN").replaceWith("");
			$("#recTable").remove("#fullname,#emailid,#contactphone");	
			fillJL();
		});
		
	});
});

function editJL(that)
{
	var cl=$('#recTable tbody').find('input').length;
	if(cl>0) {
		jAlert('Another record is already active, unable to do this operation.','TruPhish');
		return false;
	}
	$('#hdnmode').val("EDIT");
	var el=$(that).parent().parent();
	var htm="<tr>"+el.html()+"</tr>";
	var nm=$.trim(el.find("td:eq(0)").html());
	var ml=$.trim(el.find("td:eq(1)").html());
	var cn=$.trim(el.find("td:eq(2)").html());

	var fnm='<input type="text" id="fullname" name="fullname" tabindex="1" class="form-control" maxlength="100" value="'+nm+'" />';
	var eml='<input type="email" id="emailid" name="emailid" tabindex="2" class="form-control" maxlength="200" value="'+ml+'" />';
	var phn='<input type="tel" id="contactphone" name="contactphone" tabindex="3" class="form-control" maxlength="100" value="'+cn+'" />';
	/****@<?php print(trim($_SESSION['TEST']['domain']));?>***/
	var itm='<tr id="TN"><td data-title="Full Name">'+fnm+'</td><td data-title="Email Address">'+eml+'</td><td data-title="Contact Phone">'+phn+'</td><td class="frm_ctrl" align="right"><a href="javascript:;" id="btnSave"><i class="fa fa-save"></i></a><a href="javascript:;" id="btnCancel"><i class="fa fa-minus-circle"></i></a></td></tr>';
	el.replaceWith(itm);

	$("#btnSave").click(function(e) { 
		$(".erd").each(function(){$(this).remove();});
		rfield =  ["fullname","emailid"];
		rtype = ["name","email"];
		var rx=true;
		for (i=0;i<rfield.length;i++) 
		{
			if(getValidate(rfield[i],rtype[i])==false)	
			rx=false;
		}
		if(rx==true) rx=checkDuplicate("fullname","emailid","contactphone");
        if(rx==false) return rx;

		var itm='<tr><td data-title="Full Name">'+$("#fullname").val()+'</td><td data-title="Email Address">'+$("#emailid").val()+'</td><td data-title="Contact Phone">'+$("#contactphone").val()+'</td><td class="frm_ctrl" align="right"><a class="editJL" href="javascript:;" onClick="editJL(this);"><i class="fa fa-edit"></i></a><a class="delJL" href="javascript:;" onClick="delJL(this);"><i class="fa fa-trash"></i></td></tr>';
		$("#TN").replaceWith(itm);
		$("#recTable").remove("#fullname,#emailid,#contactphone");	
		fillJL();
	});

	$("#btnCancel").click(function(e) { 
		$("#TN").replaceWith(htm);
		$("#recTable").remove("#fullname,#emailid,#contactphone");	
		fillJL();
	});
}

function checkDuplicate(cnt,stt,lcn)
{
	var rx=true;
	var msg='';
    $("#recTable tbody tr").each(function(r,v){
		var rc=true;
	    var rs=true;
		    $(this).find('td').each(function(c,h){
			if(c==0)
			{
				if($.trim($("#"+cnt).val())==$.trim($(h).html()))
				{
					msg='Duplicate record exists'; rx=false;
				}
			}
			if(c==1)
			{
				if($.trim($("#"+stt).val())==$.trim($(h).html()))
				{
					msg='Duplicate record exists'; rx=false;
				}
			    /*str = $.trim($("#"+stt).val()).split('@').slice(1);
			    //var allowedDomains = ['<?php print($_SESSION['TEST']['domain']); ?>'];
			    if ($.inArray(str[0], allowedDomains) == -1) {
					msg='Invalid domain entered.'; rx=false;
				}*/
			}
		});
	});
    if(rx==false)
    {
    	jAlert(msg,'TruPhish');			                
    }
    return rx;
}
function delJL(that)
{
	jConfirm('Are you sure to delete this information?','TruPhish',function(r){
		if(r==true)
		{
			$('#hdnmode').val("DEL");
			$(that).parent().parent().remove();
			fillJL();
		}
	});
}


function fillJL()
{
	var dtr='';
	var dtc='';
	var clm=['fullname','emailid','contactphone']
	$('#recTable tbody tr').each(function(i,val){
		dtr += (dtr=='') ? '{' : '}, {';
		dtc='';
		$(this).find('td').each(function(id,val){
			if(id<3)
			{
				dtc += (dtc=='') ? '"'+clm[id]+'":"'+$(this).html()+'"' : ', "'+clm[id]+'":"'+$(this).html()+'"';
			}
		});
		dtr += dtc;
	});
	dtr = (dtr!='') ? '['+dtr+'}]' : '';
	$('#hdnDTA').val(dtr);
    //if($("#recTable tbody tr").length<=0) $("#recTable").hide();
}
</script>