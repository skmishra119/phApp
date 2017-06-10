<?php
define('DROOT','./');
define('XROOT','../../');
require_once '../settings.inc.php';
$st->setMetas("name", "TruShield Security Solutions");
error_reporting(E_ALL);
session_start();
global $UID;
$UID = (isset($_SESSION['UID'])!='')?$_SESSION['UID']:'';
global $USERINFO;
$USERINFO = $st->getUserInfo($UID);

$ERR=array();
$ECD=$_REQUEST['typ'];
$PGS=$st->decrypt($_REQUEST['pg']);
$st->getError('main',$st->ErrDesc[$ECD]);
$ERR = $st->errors;

//var_dump($st->encrypt('true'),$st->encrypt('false'));
if($PGS==true){
	if(isset($_SESSION['UID'])){
		unset($_SESSION['UID']);
		session_destroy();
		$USERINFO = $st->getUserInfo($UID);
	}
}
if(!defined('XROOT')) {
	define('XROOT','');
}
?>
<!DOCTYPE html>
<html class="<?php echo ((isset($_SESSION['UID'])=='')?'login':''); ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php print($st->Title); ?></title>
	<?php echo $st->Meta; ?>
	<?php echo $st->addAllStyles(); ?>
</head>
<body>
   <main>
        <header>
              <div class="wrapper">
                  <div class="logo_cont">
                      <?php echo $st->getLogo();?>
                  </div>
                  <nav>
                      <?php echo $st->getToolBox($USERINFO['role']); ?>
                  </nav>
              </div>
        </header>
        <div class="wrapper">
			<div class="content_cont_wrapper">
				<div class="header_cont"><h3>Error!</h3></div>
				<div class="main_content">
					<div id="msg">
						<div class="alert alert-danger">
							<?php echo $ERR['main']; ?>
							<?php if(trim($PGS)!='false'){ ?>
							<p><a href="<?php echo DROOT.XROOT; ?>">Click here</a> to login.</p>
							<?php } ?>
						</div>
					</div>	
				</div>
			</div>
		</div>
	</main>
</body>
</html>