<?php
session_start();
global $UID;
$UID = (isset($_SESSION['AID'])!='')?$_SESSION['AID']:'';
global $USERINFO;
$USERINFO = $st->getUserInfo($UID);
//$UPARAMS=$st->getUserParams($UID);
if(!defined('XROOT')) {
    define('XROOT','');
}
//var_dump($USERINFO);
?>
<!DOCTYPE html>
<html>
		<head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php print($st->Title); ?></title>
		<?php echo $st->Meta; ?>
		<?php echo $st->addAllScripts(); ?>
		<?php echo $st->addAllStyles(); ?>
	</head>
	<body>
	<div id="wrapper">
		<div id="header">
			<div id="logo">
				<?php echo $st->getLogo();?>
			</div>
            <div class="menu_icon_cont">
                <i class="fa fa-bars"></i>
            </div>
			<div id="rlink">
				<?php echo $st->getToolBox($USERINFO['role']); ?>
			</div>
			<div class="clr"></div>
			<div class="user_info">Welcome <?php echo (trim($USERINFO['fullname'])=="") ? "Guest" : ucwords(trim($USERINFO['fullname'])); ?>&nbsp; <?php echo (trim($USERINFO['role'])=="") ? "" : "[".strtoupper(trim($USERINFO['role']))."]"; ?></div>
		</div>
		<div class="clr"></div>
