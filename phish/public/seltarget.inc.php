<?php
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
global $USERINFO;
global $TEST;
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])!='CLIENT') header("Location:./");
$FILTER='';

$ERR=array();
if(isset($_POST['btnSubmit'])=="Next >"){
	//var_dump($_POST,$TEST);
	//$_POST['testid']=trim($TEST[0]['testid']);
	$_POST['clientid']=trim($USERINFO['userid']);
	$rules_array = 	array('listids'=>array('type'=>'string', 'msg'=>'Target', 'required'=>true, 'min'=>5, 'max'=>5000, 'options'=>false, 'trim'=>true));
    $st->addSource($_POST);
    $st->addRules($rules_array);
    $st->run();
    if(sizeof($st->errors) > 0)
    {
        $st->getError('main', 'No target selected, Select at least one or more targets.');
        $ERR=$st->errors;
    }
	else
	{
		$p=$st->sanitized;
		$st->getModelData($_POST);
		$st->Submit('TEST-SLTARGET',"",$USERINFO);
		if(sizeof($st->errors) > 0)
    	{
        	$ERR=$st->errors;
    	}
    	else{
    		header("Location: campaign");
    	}
	}
    /*** show the array of validated and sanitized variables ***/
    //print_r($st->sanitized['emailid']);
}
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmTools").bind("submit", function() {
		var rv=true;
		var vls=[];
		$("#msg").html("");$("#msg").val("");
		gerror = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> No Target selected. Select at least one or more target.</p></div></div>';
		$(".erd,.erdx").each(function(){$(this).remove();});
		//$('#listids').val(VL);
		$('.chkIDS:checked').each(function(){
			vls.push($(this).val());
		});
		$('#listids').val(vls.join(','));
		return rv;
	});
});
//-->
</script>
<?php echo $st->showCampaignProgress('SLTARGET'); ?>
<div class="content_cont_wrapper">
	<div class="header_cont"><h3>Select Targets</h3></div>
	<div class="main_content campaign_cont">
		<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="alert alert-danger">'.$ERR['main'].'</div>':''; ?></div>
		<div class="frm">
			<div class="grid">
				<?php echo $st->showGrid('TARGETS',$USERINFO, $FILTER, true); ?>
				 
			</div>
		</div>
	</div>
</div>

<script>

	
	/*$(window).resize(function(){
		$('.table-striped, #recTable').each(function() { 
	        var thetable=jQuery(this);
	        $(this).find('tbody td').each(function() {
	            $(this).attr('data-title',thetable.find('thead th:nth-child('+(jQuery(this).index()+1)+')').text()).addClass("booooooooooom");
	        });
	    });
	});*/
</script>