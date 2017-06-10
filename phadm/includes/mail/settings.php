<?php
session_start();
$UID=$_SESSION['uid'];
require_once("conf.php");
require_once("getpermit.php");
require_once("includes/crypt/crypt.php");
require_once("includes/mail/class.phpmailer.php");

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

$url=$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

$root=getRoot($_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);


$lang="en";
$title="The comprehensive job portal";
$meta="";
$dt=date('Y-m-d H:i:s A');
$errMain;
$errs;
$clr;
$username=$_SESSION['username'];
$umail=$_SESSION['umail'];
$orgid=$_SESSION['orgid'];
$orgname=$_SESSION['org'];
$urole=$_SESSION['role'];
$menuopt;
$upload;
$msg=$_SESSION['msg'];
$utype=$_SESSION['utyp'];
$bdytextads="";
$rowsPerPage=25;
$minSrch=1;
global $cr;
$cr=new crypt();
$cr->crypt_key('whateveryouwant');

$cal = "<link rel='stylesheet' href='".$root."includes/cal/themes/base/jquery.ui.all.css' />\n"; 
$cal .= "<script src='".$root."includes/cal/ui/jquery.ui.core.js'></script>\n";
$cal .= "<script src='".$root."includes/cal/ui/jquery.ui.widget.js'></script>\n";
$cal .= "<script src='".$root."includes/cal/ui/jquery.ui.datepicker.js'></script>\n";
$cal .= "<link rel='stylesheet' href='".$root."/includes/cal/demos/demos.css' />\n";

$dialog = "<script type=\"text/javascript\" src=\"".$root."includes/dialog/jquery.mousewheel-3.0.4.pack.js\"></script>\n<script type=\"text/javascript\" src=\"".$root."includes/dialog/jquery.fancybox-1.3.4.pack.js\"></script>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"".$root."includes/dialog/jquery.fancybox-1.3.4.css\" media=\"screen\" />\n";

$alert = "<link href='".$root."css/jquery.alerts.css' rel='stylesheet' type='text/css' media='screen' />\n";
$alert .= "<script type='text/javascript' src='".$root."scripts/jquery-corner.js'></script>\n";
$alert .= "<script type='text/javascript' src='".$root."scripts/jquery.alerts.js'></script>\n";
$alert .= "<link href='".$root."css/combo.css' rel='stylesheet' type='text/css' media='screen,print' />\n";
$alert .= "<script type='text/javascript' src='".$root."scripts/jquery.combobox.js'></script>\n";
$alert .= "<script type='text/javascript' src='".$root."scripts/mjvalidator.js'></script>\n";
function getEditor($ctrl)
{
	$editor = "<script type='text/javascript' src='".$root."includes/editor/tiny_mce.js' ></script>\n<script type=\"text/javascript\" language=\"javascript\">\n tinyMCE.init({ mode : \"exact\", elements: \"".$ctrl."\", theme : \"advanced\", plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist\",\ntheme_advanced_buttons1 : \"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleprops,styleselect,formatselect,fontselect,fontsizeselect\",\ntheme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,media,cleanup,code,preview,|,forecolor,backcolor\",\ntheme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,insertlayer,moveforward,movebackward,absolute,|,attribs,visualchars,nonbreaking\",\ntheme_advanced_buttons4 : \"\",\ntheme_advanced_toolbar_location : \"top\", theme_advanced_toolbar_align : \"left\", theme_advanced_statusbar_location : \"bottom\", theme_advanced_resizing : true, theme_advanced_path:false, skin : \"o2k7\",\n skin_variant : \"silver\",\n content_css : \"".$root."css/editor.css\",\ntemplate_external_list_url : \"js/template_list.js\",\nexternal_link_list_url : \"js/link_list.js\",\nexternal_image_list_url : \"js/image_list.js\",\n media_external_list_url : \"js/media_list.js\",\n template_replace_values : { username : \"Some User\", staffid : \"991234\" } });</script>\n";
	return $editor;
}

$vx=strstr($_SERVER['SCRIPT_NAME'],"index.php");
$vy=strstr($_SERVER['SCRIPT_NAME'],"forgot_password.php");
$vz=strstr($_SERVER['SCRIPT_NAME'],"reset_password.php");

if(!$vx && !vy && !vz)
{
	if($appcode!=$_SESSION['code'])
	{
		$_SESSION['msg']="Sorry! Unauthorized access.";
		header("Location:index.php");
		exit;
	}
}

if($UID!="")
{
	$con=db_connect();
	if(!$con){ $errMain ="<div class='Errd'>Unable to connect with the database, Contact administrator.</div></hr/>"; return;}
	$menuopt="<script type=\"text/javascript\">$(function(){ \n $(\"#jsddm > li\").hover(function(){ \n $(this).addClass(\"hover\"); \n $(\"ul:first\",this).css(\"visibility\", \"visible\"); \n }, function(){ \n $(this).removeClass(\"hover\"); \n $(\"ul:first\",this).css(\"visibility\", \"hidden\"); \n }); \n }); </script>\n";
	$lsql="select p.firstname,p.lastname,r.userrole,r.userid from t_mps100212_recruiterteammemberprofile p inner join t_mps100212_recruiterteammemberregistration r on p.userid=r.userid where p.userid='".$UID."' and p.deleteflag='ACTIVE' and r.deleteflag='ACTIVE'";
	$lrs=mysql_query($lsql,$con);
	if (!$lrs){ $errMain ="<div class='Errd'>Unable to get the valid information or invalid user, Contact administrator.</div></hr/>"; return;}
	$row=mysql_fetch_row($lrs);
	if($row)
	{ 
		if(trim($row[2])=="SYSADMIN")
		{
			$menuopt .= "<div id=\"mnu\">\n<ul id=\"jsddm\"><li><a href='home.php'>Home</a></li><li class=\"sub\"><a href=\"#\">Profiles</a><ul><li><a href='profilepreview.php'>My Profile</a></li><li><a href='recruiterlist.php'>Other Profiles</a></li></ul></li><li><a href='organizationlist.php'>Organization</a></li><li class=\"sub\"><a href=\"#\">Control Panel</a><ul><li><a href='appsetting.php'>Display Settings</a></li><li><a href='appconfiglist.php'>Configuration</a></li><li><a href='recruiterlist.php'>Recruiters</a></li><li><a href='#'>Applicants</a></li></ul></li></ul></div>";
		}
		else if(trim($row[2])=="RECRUITER")
		{
			if(trim($row[0])!="")
			{
				$menuopt .= "<div id=\"mnu\">\n<ul id=\"jsddm\"><li><a href='home.php'>Home</a></li><li class=\"sub\"><a href=\"#\">Profiles</a><ul><li><a href=\"profilepreview.php\">My Profile</a></li><li><a href='teammemberlist.php'>Other Profiles</a></li></ul></li><li><a href='#'>Job Postings</a><ul><li><a href='form.php?pg=jobpostinglist&ftyp=Draft'>Draft</a></li><li><a href='form.php?pg=jobpostinglist&ftyp=Published'>Published</a></li><li><a href='form.php?pg=jobpostinglist&ftyp=Unpublished'>Unpublished</a></li><li><a href='form.php?pg=jobpostinglist&ftyp=Expired'>Expired</a></li><li><a href='form.php?pg=jobpostinglist&ftyp=All'>All Postings</a></li></ul></li><li><a href='bookmarklist.php'>Bookmarks</a></li><li><a href='invitationlist.php'>Invitations</a></li><li><a href='applicationlist.php'>Applications</a></li><li class=\"sub\"><a href=\"#\">Test and Remarks</a><ul><li><a href='questionbanklist.php'>Question Bank</a></li><li><a href='onlinetestlist.php'>Create Test</a></li><li><a href='testschedulelist.php'>Test Schedule</a></li><li><a href='inittest.php'>Initiate Test</a></li><li><a href='testresult.php'>Test Result</a></li><li><a href='interviewpanellist.php'>Interview Panel</a></li><li><a href='interviewschedulelist.php'>Interview Schedule</a></li><li><a href='#'>Interviewer Comments</a></li></ul></li><li><a href=\"#\">Control Panel</a><ul><li><a href='appsetting.php'>Display Settings</a></li><li><a href='appconfiglist.php'>Configuration</a></li><li><a href='#'>Applicants</a></li></ul></ul></div>";
			}
			else
			{
				$menuopt .= "<div id=\"mnu\">\n<ul id=\"jsddm\"><li><a href=\"#\">Home</a></li><li class=\"sub\"><a href=\"#\">Profiles</a><ul><li><a href='profilepreview.php'>My Profile</a></li><li></li><li><a href=\"#\">Other Profiles</a></li></ul><li><a href='#'>Job Postings</a></li><li class=\"sub\"><a href='#'>Control Panel</a><ul><li><a href='#'>Team Members</a></li><li><a href='#'>Applicants</a></li></ul></li></ul></div>";
			}
		}
		else
		{
			if(trim($row[0])!="")
			{
				$menuopt .= "<div id=\"mnu\">\n<ul id=\"jsddm\"><li><a href=\"home.php\">Home</a></li><li class=\"sub\"><a href=\"#\">Profiles</a><ul><li><a href='profilepreview.php'>My Profile</a></li><li><a href='teammemberlist.php'>Other Profiles</a></li></ul></li><li><a href='#'>Job Postings</a><ul><li><a href='form.php?pg=jobpostinglist&ftyp=Draft'>Draft</a></li><li><a href='form.php?pg=jobpostinglist&ftyp=Published'>Published</a></li><li><a href='form.php?pg=jobpostinglist&ftyp=Unpublished'>Unpublished</a></li><li><a href='form.php?pg=jobpostinglist&ftyp=Expired'>Expired</a></li><li><a href='form.php?pg=jobpostinglist&ftyp=All'>All Postings</a></li></ul></li><li><a href='bookmarklist.php'>Bookmarks</a></li><li><a href='invitationlist.php'>Invitations</a></li><li><a href='applicationlist.php'>Applications</a></li><li class=\"sub\"><a href=\"#\">Test and Remarks</a><ul><li><a href='questionbanklist.php'>Question Bank</a></li><li><a href='onlinetestlist.php'>Create Test</a></li><li><a href='testschedulelist.php'>Test Schedule</a></li><li><a href='inittest.php'>Initiate Test</a></li><li><a href='testresult.php'>Test Result</a></li><li><a href='interviewpanellist.php'>Interview Panel</a></li><li><a href='interviewschedulelist.php'>Interview Schedule</a></li><li><a href='#'>Interviewer Comments</a></li></ul></li><li class=\"sub\"><a href=\"#\">Control Panel</a><ul><li><a href='#'>Applicants</a></li></ul></li></ul></div>";
			}
			else
			{
				$menuopt .= "<div id=\"mnu\">\n<ul id=\"jsddm\"><li><a href=\"#\">Home</a></li><li class=\"sub\"><a href=\"#\">Profiles</a><ul><li><a href='profilepreview.php'>My Profile</a></li><li><a href=\"#\">Other Profiles</a></li></ul></li><li><a href=\"#\">Job Postings</a></li><li><a href=\"#\">Bookmarks</a></li><li><a href=\"#\">Invitations</a></li><li><a href=\"#\">Applications</a></li><li class=\"sub\"><a href=\"#\">Test and Remarks</a><ul><li><a href=\"#\">Test Schedule</a></li><li><a href=\"#\">Interview Panel</a></li><li><a href=\"#\">Interview Schedule</a></li><li><a href=\"#\">Interviewer Comments</a></li></ul></il><li class=\"sub\"><a href=\"#\">Control Panel</a><ul><li><a href=\"#\">Applicants</a></li></ul></div>";
			}
		}
	}
}
else
{
	$menuopt .= "<div id=\"mnu\"></div>";
}

function getPager($pgr)
{
	$STG="<div id=\"".$pgr."\" class=\"pager\">\n<form>\n<img src='".$root."assets/first.png' class=\"first\"/>\n<img src='".$root."assets/prev.png' class=\"prev\"/>\n<span class=\"pagedisplay\"></span><img src='".$root."assets/next.png' class=\"next\"/>\n<img src='".$root."assets/last.png' class=\"last\"/>\n<select class=\"pagesize\"><option selected=\"selected\"  value=\"25\">25</option>\n<option value=\"30\">30</option>\n<option value=\"40\">40</option>\n<option value=\"50\">50</option>\n<option value=\"999\">All</option>\n</select>\n</form>\n</div>";
	return $STG;
	
}

$pager="<div id=\"pager\" class=\"pager\">\n<form>\n<img src='".$root."assets/first.png' class=\"first\"/>\n<img src='".$root."assets/prev.png' class=\"prev\"/>\n<span class=\"pagedisplay\"></span><img src='".$root."assets/next.png' class=\"next\"/>\n<img src='".$root."assets/last.png' class=\"last\"/>\n<select class=\"pagesize\"><option selected=\"selected\"  value=\"25\">25</option>\n<option value=\"30\">30</option>\n<option value=\"40\">40</option>\n<option value=\"50\">50</option>\n<option value=\"999\">All</option>\n</select>\n</form>\n</div>";

$clr="<link rel=\"stylesheet\" href=\"".$root."includes/color/css/colorpicker.css\" type=\"text/css\" />\n<script type=\"text/javascript\" src=\"".$root."includes/color/js/colorpicker.js\"></script>\n";
/*  <script type=\"text/javascript\" src=\"".$root."includes/color/js/eye.js\"></script>\n<script type=\"text/javascript\" src=\"".$root."includes/color/js/utils.js\"></script>\n<script type=\"text/javascript\" src=\"".$root."includes/color/js/layout.js?ver=1.0.2\"></script>\n";*/

$upload="<script type='text/javascript' src='".$root."scripts/uploadfile.js'></script>\n<script type=\"text/javascript\">\n$(function(){\n	var btnUpload=$('#upload');\nvar status=$('#status');\nnew AjaxUpload(btnUpload, {\n	action: 'logo_upload.php',\nname: 'uploadfile',\n onSubmit: function(file, ext){\n $('#txtlogo').val(''); if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){ \n status.text('Only jpg, png, jpeg and gif files are allowed'); \n return false; \n	} \n status.text('Uploading...'); \n }, \n onComplete: function(file, response){ \n status.text(''); \n if(response===\"success\"){ \n  $('#txtlogo').val(file); \n } else{ \n status.text(response); \n } \n } \n }); \n }); \n </script>";

function getRoot($url)
{
	if(substr($url,0,4)!="http") $url = "http://".$url;
	//$ur=preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	if(substr($url,strlen($url)-4,4)==".php")
	{
		$fname=basename($url);
		$ur=str_replace($fname,"",$url);
	}
	else
	{
		$ur=$url;
	}
	return $ur;
}

function getImages($dir)
{
	global $appcode;
	if(substr($dir, -1) != "/") $dir .= "/";
	$fulldir = "{$_SERVER['DOCUMENT_ROOT']}/".$appcode."/$dir";
	$d = @dir($fulldir) or die("getImages: Failed opening directory $dir for reading");
	while(false !== ($entry = $d->read())) 
	{
		if($entry[0] == ".") continue;
		$retval="$entry";
	}
	$d->close();
	return $retval;
}

function getLogo($rt)
{
	global $appcode;
	$dir="assets/logo/";
	$fulldir = "{$_SERVER['DOCUMENT_ROOT']}/".$appcode."/$dir";
	$d = @dir($fulldir) or die("getLogos: Failed opening directory $dir for reading");
	while(false !== ($entry = $d->read())) 
	{
		if($entry[0] == ".") continue;
		$retval="$dir$entry";
	}
	$d->close();
	return $rt.$retval;
}

function getOrg()
{
	if(isset($_SESSION['org'])!="")
		return $_SESSION['org'];
	else
		return "Majical Jobs";
}

function getOrgname()
{
	if(isset($_SESSION['org'])!="")
		return $_SESSION['org']."&nbsp;<img src=\"".$root."assets/arrows/arrow2.gif\" border=\"0\" />&nbsp;";
	else
		return "Majical Jobs"."&nbsp;<img src=\"".$root."assets/arrows/arrow2.gif\" border=\"0\" />&nbsp;";
}

function getemailfrom()
{
	if(isset($_SESSION['org'])!="")
	{
		$qry="select organizationname,organizationemail from t_mps100212_organizationprofile where deleteflag='ACTIVE'";
		$cnx=db_connect();
		$rsx=mysql_query($qry,$cnx);
		$rwx=mysql_fetch_array($rsx);
		return $rwx;
	}
	else
	{
		$arr=array("Majical Jobs","support@nevagroup.com");	
		return arr;
	}
}


function getSectorIndustry($TYP,$VAL)
{
	$VL="";
	if($VAL!="")
		$qry="select organizationname,sector,industrytype from t_mps100212_organizationprofile where organizationid='".$VAL."'";
	else
		$qry="select organizationname,sector,industrytype from t_mps100212_organizationprofile where deleteflag='ACTIVE'";
	$cnx=db_connect();
	$rsx=mysql_query($qry,$cnx);
	$rwx=mysql_fetch_array($rsx);
	switch($TYP)
	{
		case "SEC": $VL=$rwx['sector']; break;
		case "IND": $VL=$rwx['industrytype']; break;
		case "ORG": $VL=$rwx['organizationname']; break;
	}
	return $VL;
}

function getUserInfo($uid)
{
	$sqz="select firstname,lastname,emailaddress from t_mps100212_recruiterteammemberprofile where userid='".trim($uid)."'";
	$cnz=db_connect();
	$rsz=mysql_query($sqz,$cnz);
	$rwz=mysql_fetch_array($rsz);
	return $rwz;
}

function sendNotify($to,$subj,$bdy)
{
	$RES=true;
	$mail = new PHPMailer();
	$mail->IsSMTP();                                   // send via SMTP
	$mail->Host     = "localhost"; // SMTP servers
	$mail->SMTPAuth = true;     // turn on SMTP authentication
	$mail->Username = "";  // SMTP username
	$mail->Password = ""; // SMTP password
	$mlfrm=getemailfrom();
	$mail->From = $mlfrm[1];
	$mail->FromName = $mlfrm[0];
	$mail->AddAddress($to);  
	//$mail->AddReplyTo($frm,$frmnm);
	$mail->WordWrap = 50;                              // set word wrap
	$mail->IsHTML(true);                               // send as HTML

	$mail->Subject  =  $subj;
	$mail->Body     =  $bdy;
	$mail->AltBody  =  $bdy;
	if(!$mail->Send())
		$RES=false;
	return $RES;
}

function checkJob($jid,$stat)
{
	$tf=false;
	$cnx=db_connect();
	$sqx="select jobid,jobcode from t_mps100212_recruiterjobposting where deleteflag='ACTIVE' and jobdescription!='' and gradelevel!='' and noofopenings!='' and division!='' and department !='' and experiencerequired!='' and educationdegreerequired!='' and (salaryrangefrom!='' or salaryrangeupto!='') and jobpostingdate!='' and jobstartdate!='' and jobexpirationdate!='' and jobid in(".$jid.")";
	$rsx=mysql_query($sqx,$cnx);
	if(mysql_num_rows($rsx)>0)
		$tf=true;
	return $tf;
}

function stripslashes_deep($value)
{
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}
?>