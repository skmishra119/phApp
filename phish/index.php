<?php
session_start();
define('DROOT','./');
//if(isset($_REQUEST['__uid'])!='') $_SESSION['UID']=$st->decrypt($_REQUEST['__uid']);
header("Location: public/index.php");