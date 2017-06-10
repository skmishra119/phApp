<?php
define('DROOT','./');
define('XROOT','../../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
require_once '../'.$st->IncludePath.'header.inc.php';
//var_dump($USERINFO);
if(trim($USERINFO['userid'])=='' || trim($USERINFO['role'])=='CLIENT') header("Location:../../");
$text='';
switch(strtolower(trim($_REQUEST['typ']))){
	case "users": $text='fa fa-user'; break;
    case "groups": $text='fa fa-object-group fa-fw'; break;
    case "mailing-lists": $text='fa fa-building fa-fw'; break;
    case "email-templates": $text='fa fa-envelope fa-fw'; break;
    case "cmss": $text='fa fa-book fa-fw'; break;
    case "settings": $text='fa fa-gear fa-fw'; break;
    case "helps": $text='fa fa-question fa-fw'; break;
}
$ERR=array();
try{
	$OUTPUT_DATA = $st->showInformation(strtoupper($_REQUEST['typ']), $st->decrypt($_REQUEST['pg']), true);
	if(sizeof($st->errors)>0)
		header('Location: error/404/'.$st->encrypt('false'));
}catch (Exception $e){
	header('Location: error/404/'.$st->encrypt('false'));
}
//var_dump($_REQUEST,$_GET);
?>
<div id="container">

	<div class="contents" style="min-height:400px;">
		<div class="frmhead">
        
        
          
			<div class="frmname"><i class="<?php echo trim($text); ?>"></i>&nbsp; <?php echo (trim($_REQUEST['typ'])!='mailing-lists') ? ucfirst($_REQUEST['typ']) : "Target"; ?></div>
			<div class="pgtools"><?php echo $st->showPageToolBox(strtoupper($_REQUEST['typ']),'VIEW',$USERINFO['role'],false); ?></div>
			
			<div class="clr"></div>
		</div>
		<div id="msg"><?php echo (isset($ERR['main'])!='') ? '<div class="frm ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>'.$ERR['main'].'</p></div></div>':''; ?></div>
		<div class="frm">
			<div class="grid">
				<form id="frmTools" method="post" action="<?php echo XROOT.substr(strtolower($_REQUEST['typ']),0,strlen($_REQUEST['typ'])-1); ?>">
					<?php echo $OUTPUT_DATA; ?>
					<input type="hidden" id="hdnRID" name="hdnRID" value="<?php print(trim($_REQUEST['pg'])); ?>" />
					<input type="hidden" id="hdnMode" name="hdnMode" />
				</form>
			</div>
		</div>	
	</div>
</div>
<?php require_once '../'.$st->IncludePath.'footer.inc.php'; ?>