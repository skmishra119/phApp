<?php
//error_reporting(E_ALL &  ~E_NOTICE);
//ini_set('session.cookie_httponly',1);
//ini_set('session.use_only_cookies',1);
error_reporting(E_ALL);
define('ROOT',dirname(__FILE__).'/');
global $st;
global $xl;
global $ml;

//date_default_timezone_set('US/Eastern');

require_once 'includes/crypt/crypt.class.php';
require_once 'includes/validate.inc.php';
require_once 'model/models.inc.php';
require_once 'includes/excel/PHPExcel.php';
require_once 'includes/excel/PHPExcel/IOFactory.php';
//$xl=new PHPExcel();
require_once 'includes/mail/class.phpmailer.php'; 

function handleError($errno, $errstr,$error_file,$error_line) {
	throw new Exception('Error: '.$error_file.' ['.$error_line.'] as '.$errstr);	
	die();
}
set_error_handler("handleError");

class Setting extends DataModel
{
	protected $DBHost='localhost';
	protected $DBUser='root';
	protected $DBPass='DEVtrushield@123';
	protected $DBName='ptest';
	
	var $LoginAttempts=5;
	var $LoginBlockTime=5;
	
	protected $RecPerPage=20;
	
	var $MailFrom='development@trushieldinc.com';
	var $FromName='TruShield Team';
	
	var $AdminURL = 'http://104.130.239.178/phadm/';
	
	protected $MailHost= 'localhost'; //'10.1.14.25';
	protected $SmtpAuth = false; 
	protected $SmtpSecure = 'tls';
	protected $MailPort = 25; //587;  
	protected $MailUser = "";
	protected $MailPassword = "";
		
	
	var $Title='TruPhish - Trushield Inc. Phishing Test';
	var $Meta='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';

	var $StylePath='css/';
	var $ScriptPath='scripts/';
	var $IncludePath='includes/';
	var $FaviconPath='assets/icons/';
	var $ImagePath='assets/';
	var $AdminPath='admin/';
	
	var $popupHeading = 'TruPhish'; 
	
	var $tmpFolder='uploads/';
	var $extAllow=array('xls','xlsx');
	
	var $ErrDesc=array(
			'100'=>'Your session is expired. Kindly relogin to gain access of the application.',
			'202'=>'The resource you are trying to access is not found on the server. Check the url and try again later.',
			'404'=>'The resource you are trying to access is not found. Please check the url and try again later.',
			'500'=>'The document you are trying to access have been either not found or move permanently. Kindly check the url or contact administrator',
			'js-error'=>'Your borwser does not support javascript or javascript has been disabled. Kindly enable javascript to gain access this resource.',
			'no-auth'=>'You are not authorized to view this resource. Please review your authorization or ACL.',
			'csrf-error'=>'You are not authorized to access this resource or resource does not found.',
	);

	protected $saltKey='f8c077313153dcaaaa8728adc74a85d0';
	
	private $cr;
	
	public function __construct(){
		$this->cr=new Crypto($this->saltKey);
		//$this->ml=new PHPMailer();
		//$this->xl=new PHPExcel();
	}
	
	public function __call($method, $args) {
		//var_dump($method);
		if (method_exists($this->cr, $method)) {
            $reflection = new ReflectionMethod($this->cr, $method);
            if (!$reflection->isPublic()) {
                throw new RuntimeException("Call to not public method ".get_class($this)."::$method()");
            }

            return call_user_func_array(array($this->cr, $method), $args);
        } else {
            throw new RuntimeException("Call to undefined method ".get_class($this)."::$method()");
        } 
    }
	
    public function addAllStyles(){
		$STYLES='';
		$styleDir=ROOT.$this->StylePath;
		$StyleSheets=scandir($styleDir);		
		foreach($StyleSheets as $file){ 
			if($file != '.' && $file != '..') { 
				$STYLES .= '<link rel="stylesheet" type="text/css" href="'.DROOT.XROOT.$this->StylePath.$file.'" media="screen" />'."\n"; 
			}
		}
		return $STYLES.'<link rel="shortcut icon" href="'.DROOT.XROOT.$this->FaviconPath.'favicon.ico" type="image/x-icon" media="screen">'."\n";
	}
	
	public function addStyles($CssPath){
		$STYLES='';
		$styleDir=ROOT.$CssPath;
		$StyleSheets=scandir($styleDir);		
		foreach($StyleSheets as $file){ 
			if($file != '.' && $file != '..') { 
				$STYLES .= '<link rel="stylesheet" type="text/css" href="'.DROOT.XROOT.$CssPath.$file.'" media="screen" />'."\n"; 
			}
		}
		return $STYLES.'<link rel="shortcut icon" href="'.DROOT.XROOT.$this->FaviconPath.'favicon.ico" type="image/x-icon" media="screen">'."\n";
	}
	
	public function addAllScripts(){
		$SCRIPTS='';
		$scrDir=ROOT.$this->ScriptPath;
		$Scripts=scandir($scrDir);		
		foreach($Scripts as $file){ 
			if($file != '.' && $file != '..') { 
				$SCRIPTS .= '<script type="text/javascript" src="'.DROOT.XROOT.$this->ScriptPath.$file.'"></script>'."\n"; 
			}
		}
		return $SCRIPTS;
	}
	
	public function addScripts($ScrPath){
		$SCRIPTS='';
		$scrDir=ROOT.$ScrPath;
		$Scripts=scandir($scrDir);		
		foreach($Scripts as $file){ 
			if($file != '.' && $file != '..') { 
				$SCRIPTS .= '<script type="text/javascript" src="'.DROOT.XROOT.$ScrPath.$file.'"></script>'."\n"; 
			}
		}
		return $SCRIPTS;
	}
	
	public function addChartScript($TY, $VL, $DTS='', $DivID,$ChartType='pie',$ChartTitle,$pointFormat,$ChartImg=false, $RID = ""){
		$PGT='';
		$STRG = '<script type="text/javascript">
			<!--
			$(document).ready(function(){
				 var options = {
                    chart: {
                        renderTo: "'.$DivID.'",
                        type: "'.$ChartType.'",
                        options3d: {
                            enabled: true,
                            alpha: 0,
                            beta: 20
                        }

                    },
                    title: {
                        text: "<h2>'.$ChartTitle.'</h2>"
                    },
                    xAxis: {
            			categories : []
        			},
                    tooltip: {
                        pointFormat: "'.$pointFormat.'"
                    },
                    plotOptions: {
                        '.$ChartType.': {
                            allowPointSelect: false,
                        	cursor: "pointer",
                            depth: 35
                        },
                        series: {
            			    point: {
                    			events: {
                        			click: function () {
                            			var wWidth = $(window).width();
										var dWidth = wWidth * 0.9;
                        				var title = "'.$ChartTitle.' - "+this.name;
										var url = "'.DROOT.XROOT.'showData?typ="+this.typ+"&dts='.trim($DTS).'&RID='.trim($RID).'&status="+this.status+"&oth="+((this.oth != "" && this.oth !="undefined") ? this.oth : "")+"&pdf=true&fr="+encodeURIComponent(title);
										//console.log(url);
										//var dta=$.get(url);
										//console.log(dta);
										$("<div>").dialog({
											open: function(event, ui) {
			   									$(this).load(url);
											},
											modal: true,
											width: dWidth,
											title: title
										});
                        			}
                    			}
                			}
            			}
                    },
                    series: []
                };
                //{"type":"pie","name":"Pelawat","data":[["Tahun 2011",1518],["Tahun 2012",2092],["Tahun 2013",1345]]}
                //console.log("'.DROOT.XROOT.'getdata?tp='.trim($TY).'&vl='.trim($VL).'&dt='.trim($DTS).'");
                $.getJSON("'.DROOT.XROOT.'getdata?tp='.trim($TY).'&vl='.trim($VL).'&dt='.trim($DTS).'", function(json) {
                	options.series = json.series;';
					if(trim($ChartType)=='column'){
						$STRG .= 'options.xAxis.categories = json.categories;';
					}
               		$STRG .= 'Cht'.$DivID.' = new Highcharts.Chart(options);';
               		
               		if($ChartImg==true){
               			$STRG .= 'canvg(document.getElementById("canvas"), Cht'.$DivID.'.getSVG())
    					var canvas = document.getElementById("canvas");
						var img = canvas.toDataURL("image/png");
               			img = img.replace("data:image/png;base64,", "");
               			var data = "bin_data=" + img; 
               			$.ajax({
               					type: "POST", 
               					url: "'.DROOT.XROOT.'getimg", 
               					data: data,
               					success: function(data){
               						var dta=data.split(":");
               						if(dta.length>1){
               							jAlert(dta[1],"TruPhish");
               						}
               					} 
               			});';
               			//$STRG .= '$("#chtReport").attr("src", "'.DROOT.XROOT.'public/uploads/chart.png");';
               		}
               		$STRG .= 'if(!Cht'.$DivID.'.hasData()) { $("#btn'.$DivID.'").remove(); }
                });
			});
			//-->
		</script>';
		return $STRG;
	}
	
	public function setMetas($KEY,$VLU){
		$META='';
		$META .= '<meta name="'.$KEY.'" value="'.$VLU.'">';
		$this->Meta=$this->Meta.$META."\n";
	}
	
	public function getLogo($isPDF=false){
		$LOGO='<a href="'.DROOT.XROOT.'"><img src="'.DROOT.XROOT.$this->ImagePath.'logo.png" '.(($isPDF==true) ? 'height=40px':'').'/></a>';
		return $LOGO;
	}
	
	public function getToolBox($RLE){
		$TBX='';
		// <li><a href="'.DROOT.XROOT.$this->AdminPath.'reports"><i class="fa fa-file-text fa-fw"></i><br/>Reports</a></li>
		switch(trim($RLE)){
			case "MANAGER":
				$TBX='<ul class="tools"><li><a href="'.DROOT.XROOT.'dashboard">Home</a></li><li><a href="'.DROOT.XROOT.'campaigns">Campaigns</a></li><li><a href="'.DROOT.XROOT.'reports">Reports</a></li><li><a href="'.DROOT.XROOT.'signout">Sign Out</a></li><li><a href="'.DROOT.XROOT.'help" target="_blank">Help</a></li></ul>';
				break;
			case "CLIENT":
				$TBX='<ul class="tools"><li><a href="'.DROOT.XROOT.'campaign">Campaign</a></li><li><a href="'.DROOT.XROOT.'reports">Reports</a></li><li><a href="'.DROOT.XROOT.'signout">Sign Out</a></li><li><a href="'.DROOT.XROOT.'help" target="_blank">Help</a></li></ul>';
				break;
			default:
				$TBX='<ul class="tools"><li><a href="'.DROOT.XROOT.'help" target="_blank">Help</a></li></ul>';
				break;
		}
		return $TBX;
	}
	
	public function showPageToolBox($TYP, $PGS, $ROLE, $pData){
		//global $cr;
		$TBX='';
		if($pData!=false) $this->getModelData($pData);
		//var_dump($pData);
		switch(trim($TYP))
		{
			case 'USERS':
				if(trim($PGS)=='LIST'){
					$TBX = '<ul class="tools"><li><a class="btn btn-default" href="'.DROOT.XROOT.$this->AdminPath.'user"><span class="ui-button-text">Add New</span></a></li><li><input class="btn btn-default" type="button" id="btnEdit" name="btnEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnDelete" name="btnDelete" value="Delete"/></li><li><input class="btn btn-default" type="button" id="btnResetPwd" name="btnResetPwd" value="Reset Password"/></li></ul></form>';
				}
				if(trim($PGS)=='VIEW'){
					if(trim($ROLE)=="SYSADMIN")
						$TBX = '<ul class="tools"><li><input class="btn btn-default" type="button" id="btnViewEdit" name="btnViewEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnViewDelete" name="btnViewDelete" value="Delete"/></li></ul>';
					if($ROLE=="MANAGER" && $this->Data['role']=='CLIENT')
						$TBX = '<ul class="tools"><li><input class="btn btn-default" type="button" id="btnEdit" name="btnViewEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnViewDelete" name="btnDelete" value="Delete"/></li></ul>';
				}
				break;
			case "EMAIL-TEMPLATES":
				if(trim($PGS)=='LIST'){
					$TBX = '<ul class="tools"><li><a class="btn btn-default" href="'.DROOT.XROOT.$this->AdminPath.'email-template"><span class="ui-button-text">Add New</span></a></li><li><input class="btn btn-default" type="button" id="btnEdit" name="btnEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnDelete" name="btnDelete" value="Delete"/></li></ul></form>';
				}
				if(trim($PGS)=='VIEW'){
					if(trim($ROLE)=="SYSADMIN")
						$TBX = '<ul class="tools"><li><input class="btn btn-default" type="button" id="btnViewEdit" name="btnViewEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnViewDelete" name="btnViewDelete" value="Delete"/></li></ul>';
					if($ROLE=="MANAGER" && $this->Data['role']=='CLIENT')
						$TBX = '<ul class="tools"><li><input class="btn btn-default" type="button" id="btnViewEdit" name="btnViewEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnViewDelete" name="btnDelete" value="Delete"/></li></ul>';
				}
				break;
			case "TARGETS":
				if(trim($PGS)=='LIST'){
					$TBX = '<ul class="tools"><li><a class="btn btn-default" href="'.DROOT.XROOT.'target"><span class="ui-button-text">Add New</span></a></li><li><a class="btn btn-default" href="'.DROOT.XROOT.'upload-target"><span class="ui-button-text">Upload</span></a></li></ul></form>';
				}
				if(trim($PGS)=='VIEW'){
					if(trim($ROLE)=="SYSADMIN" || trim($ROLE)=="SYSADMIN")
						$TBX = '<ul class="tools"><li><input class="btn btn-default" type="button" id="btnViewEdit" name="btnViewEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnViewDelete" name="btnDelete" value="Delete"/></li></ul>';
				}
				break;
			case 'CMSS':
				if(trim($PGS)=='LIST'){
					$TBX = '<ul class="tools"><li><a class="btn btn-default" href="'.DROOT.XROOT.$this->AdminPath.'cms"><span class="ui-button-text">Add New</span></a></li><li><input class="btn btn-default" type="button" id="btnEdit" name="btnEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnDelete" name="btnDelete" value="Delete"/></li></ul></form>';
				}
				if(trim($PGS)=='VIEW'){
					$TBX = '<ul class="tools"><li><input class="btn btn-default" type="button" id="btnViewEdit" name="btnViewEdit" value="Edit"/></li><li><input class="btn btn-default" type="button" id="btnViewDelete" name="btnViewDelete" value="Delete"/></li></ul>';
				}
				break;
				
			case 'CAMPAIGNS':
				if(trim($PGS)=='LIST'){
					if(trim($ROLE)=="MANAGER")
						$TBX = '<ul class="tools"><li><a class="btn btn-default" href="'.DROOT.XROOT.'campaign"><span class="ui-button-text">Add New</span></a></li><li><input class="btn btn-default" type="button" id="btnCancel" name="btnCancel" value="Cancel"/></li></ul></form>';
				}
				if(trim($PGS)=='VIEW'){
					if($ROLE=="MANAGER")
						$TBX = '<ul class="tools"><li><input class="btn btn-default" type="button" id="btnViewCancel" name="btnCancel" value="Cancel"/></li></ul>';
				}
				break;
		}
		return $TBX;
	}
	
	public function showMsgs($TYP,$MSG){
		$STRG='';
		switch(trim($TYP))
		{
			case 'ALR':
				$STRG .= '<script type="text/javascript"> alert($MSG); </script>';
				break;
			case 'CNF':
				$STRG .= '<script type="text/javascript"> var r=confirm($MSG); return r; </script>';
				break;
		}  
		return $STRG;
	}  
	
	public function generatePwd($len=8,$SYM=true){
		$min = "abcdefghijklmnopqrstuvwxyz";
		$num = "0123456789";
		$maj = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$symb = "!@$^*()_-;:";
		$chars = $min.$num.$maj.(($SYM==true)?$symb:'');
		$password = substr( str_shuffle( $chars ), 0, $len );
		return $password;
	}
	
	public function getServerPath($SvrPath=0){
		if(isset($_SERVER['HTTP_REFERER'])!=''){
			$SPATH=$_SERVER['HTTP_REFERER'];
			if($SvrPath>0){
				for($i=0;$i<=$SvrPath;$i++){
					$SPATH = substr($SPATH,0,strpos($SPATH, strrchr($SPATH, '/')));
				}
			}else{
				$SPATH = substr($SPATH,0,strpos($SPATH, strrchr($SPATH, '/')));
			}
		}else{
			$SPATH = $_SERVER['PHP_SELF'];
			$SPATH = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].preg_replace('/\/public\/.*?$/is','',$SPATH);
		}
		
		return $SPATH.'/';
	}
	
	public function showCampaignProgress($CURR){
		$STEP=0;
		$PSTP=0;
		$CSTP=0;
		//$CURR='SCHEDULE';
		$TEST = array(
				0=>array('GROUP','DOMAIN'),
				1=>array('GTARGET','SLTARGET','UPTARGET','ADTARGET'),
				2=>array('TEMPLATE','EMAIL','VERIFY','VERISENT'),
				3=>array('SCHEDULE'),
				4=>array('AUTHORIZATION'),
				5=>array('ENDTEST')
		);
		$TVL= array(
				0=>array('0','Select domain'),
				1=>array('25','Select or upload targets'),
				2=>array('50','Prepare email template'),
				3=>array('75','Schedule your campaign'),
				4=>array('100','Authorize and run'),
				);
		$STRG = '<div class="progress_bar"><ul>';
		foreach($TEST as $ARR){
			if(!in_array($CURR,$ARR)) $PSTP += 1; else $CSTP = $STEP;
			$STEP += 1;
		}
		$STEP = sizeof($TEST)-1;
		$i=0;
		//if($CSTP!=$i){
		foreach($TEST as $ARR){
			if($i<$CSTP){
				$STRG .= '<li class="complete">
        			<span class="circle"></span>
        			<span class="number"><i class="fa fa-check-circle"></i><i class="num">'.($i+1).'</i></span>
        			<span class="percent">'.(($i==$STEP) ? $TVL[$i-1][0]: $TVL[$i][0]).'%</span>
        			<p class="tooltip">'.(($i==$STEP) ? $TVL[$i-1][1]: $TVL[$i][1]).'</p>';
        			$STRG .= (($i<($STEP-1)) ? '<span class="line"></span>':'').'</li>';
			}elseif ($i==$CSTP && $i<$STEP){
				$STRG .= '<li class="active">
        			<span class="circle"></span>
        			<span class="number"><i class="fa fa-check-circle"></i><i class="num">'.($i+1).'</i></span>
        			<span class="percent">'.$TVL[$i][0].'%</span>
        			<p class="tooltip">'.$TVL[$i][1].'</p>
        			<span class="line"></span>
				</li>';
			}elseif($i<$STEP){
				$STRG .= '<li class="">
        			<span class="circle"></span>
        			<span class="number"><i class="fa fa-check-circle"></i><i class="num">'.($i+1).'</i></span>
        			<span class="percent">'.$TVL[$i][0].'%</span>
        			<p class="tooltip">'.$TVL[$i][1].'</p>
        			<span class="line"></span>
				</li>';
			}
			$i += 1;
		}
		//}
		//$STRG = array_search($CURR,$TEST);
		$STRG .= '</ul></div>';
		return $STRG;
	}
	
	public function getDomainFromEmail($EmailId){
		if(trim($EmailId)!=''){
			if(preg_match('/@(.*?)$/', trim($EmailId), $MAT)){
				return trim(strtolower($MAT[1]));
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function sendMail($from,$fromnm,$mailto,$subj,$bdy,$athFile=false,$UseSmtp=array()){
		$ml=new PHPMailer();
		//globa$thiscr;
		$RES='SENT';
		//if($athHeader == true){
			//$tracker = $this->getServerPath(1).'tspadmin/truphish.php?typ=img&pg='.$this->encrypt($mailto);
			//$bdy = '<img alt="" src="'.$tracker.'" width="0" height="0" border="0" />'.$bdy;
		//}
		//$ml->IsSMTP(); 
		if(sizeof($UseSmtp)<=0){
			$ml->Host = $this->MailHost;
			//$ml->SMTPSecure = $this->SmtpSecure;
			$ml->Port = $this->MailPort; 
			$ml->SMTPAuth = $this->SmtpAuth; 
			$ml->Username = $this->MailUser;
			$ml->Password = $this->MailPassword;
		} else {
			$ml->Host = trim($UseSmtp['HOST']);
			//$ml->SMTPSecure = $this->SmtpSecure;
			$ml->Port = trim($UseSmtp['PORT']); 
			$ml->SMTPAuth = trim($UseSmtp['AUTH']); 
			$ml->Username = trim($UseSmtp['USER']);
			$ml->Password = trim($UseSmtp['PASS']);
		}
		$ml->From = $from;
		$ml->FromName = $fromnm;
		$mlto=explode(",",$mailto);
		foreach($mlto as $mto)
		{
			$ml->AddAddress(trim($mto));
			$ml->WordWrap = 50;
			$ml->IsHTML(true); 
			$ml->Subject = $subj;
			$ml->Body = nl2br($bdy);
			$ml->AltBody = nl2br($bdy);
			//if($athFile) $this->AddAttachment($this->getServerPath().'eml/fileindrive/'.$this->encrypt($athFile));;
			if(!$ml->Send())
				$RES=$ml->errorInfo;
		}
		return $RES;
	}
	
	public function file_fix_directory($dir, $mode, $nomask = array('.', '..')) {
		$RET=false;
		$dir=$dir;
		var_dump($dir);
  		if(is_dir($dir)){
	     		if(@chmod($dir, $mode)){
       				$RET=true;
     			}
  		}
  		if (is_dir($dir) && $handle = opendir($dir)) {
    			while (false !== ($file = readdir($handle))) {
      				if (!in_array($file, $nomask) && $file[0] != '.') {
        				if (is_dir("$dir/$file")) {
          					file_fix_directory("$dir/$file", $mode, $nomask);
        				} else {
          					$filename = "$dir/$file";
            					// Try to make each file world writable.
            					if(@chmod($filename, $mode)){
              						$RET=true;
        	    				}
        				}
      				}
    			}
		    	closedir($handle);
  		}
  		return $RET;
	}
	
	public function isDate($date)
	{
		$d = DateTime::createFromFormat('Y-m-d', $date);
		return $d && $d->format('Y-m-d') === $date;
	}
	
	
}
$st=new Setting();
//$cr=new Crypt();
