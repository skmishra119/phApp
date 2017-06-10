<?php
session_start();
global $UID;
$UID = (isset($_SESSION['UID'])!='')?$_SESSION['UID']:'';
if(isset($_REQUEST['__uid'])!=''){
	unset($_SESSION['TEST']);
	$UID=$st->decrypt($_REQUEST['__uid']);
}
//var_dump($UID);
$_SESSION['UID']=$UID;
global $USERINFO;
$USERINFO = $st->getUserInfo($UID);
//$UPARAMS=$st->getUserParams($UID);
if(!defined('XROOT')) {
    define('XROOT','');
}
//var_dump($_SESSION['UID']);
//exit;
?>
<!DOCTYPE html>
<html class="<?php echo 'login'/*(($_SESSION['UID']=='')?'login': '')*/; ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php print($st->Title); ?></title>
	<?php echo $st->Meta; ?>
	<?php echo $st->addAllScripts(); ?>
	<?php echo $st->addAllStyles(); ?>
	<noscript>
		<meta http-equiv="refresh" content="0;url=<?php print(DROOT.XROOT.'error/js-error/'.$st->encrypt('true'));?>">	
	</noscript>
	<script> 
		$(document).ready(function(){
			$(document).on('click','.toggle_menu', function(){
				$(this).closest('header').find('nav').slideToggle();
			});
		});
	</script>
</head>
<body>
   <main>
        <header>
              <div class="wrapper">
                  <div class="logo_cont">
                      <?php echo $st->getLogo();?>
                  </div>
                  <div class="toggle_menu"><i class="fa fa-bars"></i></div>
                  <nav>
                      <?php echo $st->getToolBox($USERINFO['role']); ?>
                  </nav>
                  <div class="clr"></div>
              </div>
        </header>
        <div class="wrapper">