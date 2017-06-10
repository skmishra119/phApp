<?php
define('DROOT','../');
define('XROOT','');
require_once DROOT.'settings.inc.php';
$con = $st->connectDB();
$sql="update t_pt_testrun set whatdid='Email Opened',remote_ip='".$_SERVER['REMOTE_ADDR']."',activity='FAILED',activitydate='".date('Y-m-d H:i:s')."' where listid='".$st->decrypt(trim($_REQUEST['pg']))."' and testid='".$st->decrypt(trim($_REQUEST['typ']))."' and emailid='".$st->decrypt(trim($_REQUEST['vl']))."' and status='RUNNING' and activity is null";
$RES = mysqli_query($con,$sql);
$nr = mysqli_affected_rows($con);
if($nr > 0){
	$sqlIns="INSERT INTO t_pt_testlogs (testid,listid,emailid,what,thedate,ip) VALUES ('".$st->decrypt(trim($_REQUEST['typ']))."','".$st->decrypt(trim($_REQUEST['pg']))."','".$st->decrypt(trim($_REQUEST['vl']))."','Email Opened','".date('Y-m-d H:i:s')."','".$_SERVER['REMOTE_ADDR']."')";
	mysqli_query($con, $sqlIns);
}
$st->closeDB($con);
header( 'Content-Type: image/gif' );
$graphic_http = $st->getServerPath().'assets/eml.gif';
$filesize = filesize('eml.gif');
header( 'Pragma: public' );
header( 'Expires: 0' );
header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header( 'Cache-Control: private',false );
header( 'Content-Disposition: attachment; filename="eml.gif"' );
header( 'Content-Transfer-Encoding: binary' );
header( 'Content-Length: '.$filesize );
readfile( $graphic_http );

exit;
?>
