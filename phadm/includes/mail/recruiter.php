<?php 
	session_start();
	include "settings.php";
	global $cr;
	$UID=$_SESSION['uid'];
	
	if($_SESSION['uid']=="")
	{
		header("Location:index.php");
	}
	$err=false;
function flag_error($fieldname,$errstr)
	{
		
		global $fields_with_errors,$errors;
		$fields_with_errors[$fieldname]=1;
		$errors[]=$errstr;
		
	}
	
	function has_error($fieldname)
	{
		global $fields_with_errors;
		if(isset($fields_with_errors[$fieldname]))
				return true;
		return false;
	}
	
	function createrandompwd()
	{
		$md5_hash1 = md5(rand(0,999)); 
		//We don't need a 32 character long string so we trim it down to 5 
		$activation_code1 = substr($md5_hash1, 5, 6); 
		return $activation_code1;
	}
	
	function createrandomcode()
	{
		$md5_hash1 = md5(rand(0,999)); 
		//We don't need a 32 character long string so we trim it down to 5 
		$activation_code1 = substr($md5_hash1, 5, 8); 
		return $activation_code1;
	}
	foreach($_POST as $k => $v) $_POST[$k] = trim(htmlspecialchars(addslashes($v)));	
	if(count($_POST)>0)
	{
			if(trim($_POST['ddlOrganisation'])=="") { $err=true; $errMain="<div class='Errd'>Please fill the marked fields below.</div><hr/>";  $errorganization="Please select Organization Name.";}
			else
			{
			  $errorganization="";
			}
			
			 $con=db_connect();
//$sql="select * from t_mps100211_applicantregistration where emailaddress='".$p['txtemail']."' and password='".$p['txtpwd']."'";
		
		$sql="select emailaddress from t_mps100212_recruiterteammemberregistration where emailaddress='".$_POST['txtemail']."'";
		
		$res=mysql_query($sql,$con);
		
			
		$row=mysql_fetch_row($res);
			
	    if(trim($row[0]) == trim($_POST['txtemail']))
		{
		$err=true;
		$errMain="<div class='Errd'>Please fill the marked fields below.</div><hr/>";
		$errmail = "User with this email address already exists";
		
        }
		else
		{
		 $errmail = "";
		
		}
	
	if(trim($_POST['txtemail'])=="") { $err=true; $errMain="<div class='Errd'>Please fill the marked fields below.</div><hr/>"; $errmail="Please enter email id.";}
	if(trim($_POST['txtuserrole'])=="") { $err=true; $errMain="<div class='Errd'>Please fill the marked fields below.</div><hr/>"; $errrole="Please select the user role."; }
		else
		{
		 $errrole="";
		}
		//echo $_POST['txtcap']."XX".$_SESSION['security_code'];
		if(trim($_POST['txtcap'])=="") { $err=true; $errMain="<div class='Errd'>Please fill the marked fields below.</div><hr/>"; $errcap="Please enter security code.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; }
		else if($_POST['txtcap']!=$_SESSION['security_code']) {$err=true; $errMain="<div class='Errd'>Please fill the marked fields below.</div><hr/>"; $errcap="Incorrect security code.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";}
			
	if(count($_POST)>0 && $err==false && $errMain=="")
	{
		
		$loc="http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
		$path=strpos($loc,"recruiter.php");
		$link=substr($loc,0,$path);
		$sl=$link;
		$pwd = createrandompwd();
		$actcode = createrandomcode();
		$link .= "change_password.php?__acd=".$actcode;
		$con=db_connect();			
$sqlins="insert into t_mps100212_recruiterteammemberregistration(organizationid,emailaddress,password,deleteflag,createdby,createdon,userrole,systemlink) values (".$_POST['ddlOrganisation'].",'".$_POST['txtemail']."','".$pwd."','PENDING','".$UID."','".$dt."','".$_POST['txtuserrole']."','".$actcode."')";
			$res=mysql_query($sqlins,$con);
			if($res)
			{
				$qry="select userid from t_mps100212_recruiterteammemberregistration where emailaddress='".trim($_POST['txtemail'])."'";
				$rsy=mysql_query($qry,$con);
				$rw=mysql_fetch_row($rsy);
				if($rw)
				{
					$sqlx="insert into t_mps100212_recruiterteammemberprofile(userid,organizationid,emailaddress,deleteflag,createdby,createdon) values ('".trim($rw[0])."','".$_POST['ddlOrganisation']."','".$_POST['txtemail']."','".$actcode."','".$UID."','".$dt."')";
					$sx=mysql_query($sqlx,$con);
					if(!$sx)
					{
						echo mysql_error();
						return;
					}
				}
				$to = $_POST['txtemail'];
				$subject = getOrg()." new user registration";
				$data=$actcode;
				
				$role;
				if($_SESSION['role'] == 'RECRUITER')
				{
				  $role = 'Team Member';
				}
				else if($_SESSION['role'] == 'SYSADMIN')
				{
				  $role = 'Recruiter';
				}
				
				$message = "Dear Registrant,<br/><br/>Thank you for registering in MagicalJobs.com. A ". $role." account has been created for you.<br/>Please find your user details to sign in.<br/><br/>User ID: ".$to."<br/>Password: ".$pwd."<br/><br/>To activate your account with MagicalJobs.com, follow this link:<br/>".$link."<br/><br/><br/>Regards,<br/>The team at<br/>http://www.magicaljobs.com.";
				
				sendNotify($to,$subject,$message);
				$errMain="<div class='Errd'>Information saved successfully and email notification sent.</div>";
				$_POST['ddlOrganisation'] = "";
				$_POST['txtemail'] = "";
				$_POST['txtuserrole'] = "";
				$_POST['txtcap'] = "";
				$mail = $to;
				$_SESSION['msg'] = "Information saved successfully and email notification sent.";
				if($_SESSION['role']=="SYSADMIN")
					header("Location:recruiterlist.php");
				else
					header("Location:teammemberlist.php");
			}
			else
			{
			    $errMain="<div class='Errd'>".mysql_error(). "</div>";
			} 
		}
	
	}
	
	function getPage()
	{
		global $PGS;
		if($_SESSION['role']=="SYSADMIN")
			$PGS="recruiterlist.php";
		else
			$PGS="teammemberlist.php";
		print $PGS;
	}
	
	include "head.html";
	
	echo $menuopt;
	?>
	<script type="text/javascript">
	$(document).ready(function(){
		$("#frmREC").bind("submit",function(){
			$(".erd").each(function(){$(this).remove();});
			rfield = ["ddlOrganisation","txtemail","txtuserrole"];
			rtype = ["req","email","req"];
			var rv=true;
			var rx;
			for (i=0;i<rfield.length;i++) 
			{
				if(getValidate(rfield[i],rtype[i])==false)	
					rx=false;
			}
			$("input,textarea").each(function(){ cleanup($(this)); });
			return rx;
		});
	});
	</script>	
	<div id="contents" class="wrapper">
		<div class="bread"><?php print(getOrgname()); ?> Reruiter/Team Members</div>
		<div class="bdr" style="margin:0 auto 10px; width:550px;">
			<div class="sechead sbg">Team Member/Recruiter</div>
			<form id="frmREC" name="frmREC" action="<?php print($PHP_SELF);?>" method="post">
				<?php print $errMain ?>
				<div class="clrfix">
					<div class="lft trght w25per f13px bld mt10">Organization: *</div>
					<div class="lft tlft w75per f13px reg mt10 ps5">
						<?php if($errorganization!="") print($errorganization."<br/>"); ?>
						<select id="ddlOrganisation" class="intxt" name="ddlOrganisation" style="width:98%;">
							<option value=''></option>
							<?php 
							if($_SESSION['role']=="SYSADMIN")
							{
								$con=db_connect();
								$sqll="select * from t_mps100212_organizationprofile where deleteflag='ACTIVE' order by organizationname"; // and organizationid not in (select organizationid from t_mps100212_recruiterteammemberregistration where userrole='SYSADMIN' and deleteflag='ACTIVE') order by organizationname";
								$resl=mysql_query($sqll,$con);
								while ($rwl=mysql_fetch_row($resl))
								{
									if(trim($_POST['ddlOrganisation'])==trim($rwl[0]))  
									{
										print("<option value=".$rwl[0]." selected = \"selected\" >".$rwl[1]."</option>");
									}
									else
									{
										print("<option value=".$rwl[0]." >".$rwl[1]."");
									}
								}
							}
							else
							{
								print("<option value='".$_SESSION['orgid']."' selected>".$_SESSION['org']."</option>");
							}
							?>
						</select>
					</div>
				</div>
				<div class="clrfix">
					<div class="lft trght w25per f13px bld mt10">Email ID: *</div>
					<div class="lft tlft w75per f13px reg mt10 ps5"><?php if($errmail!="") print($errmail."<br/>"); ?><input type="text" id="txtemail" name="txtemail" size="50" value="<?php print $_POST['txtemail'] ?>" style="width:97%;" /></div>
				</div>
				<div class="clrfix">
					<div class="lft trght w25per f13px bld mt10">User Role: *</div>
					<div class="lft tlft w75per f13px reg mt10 ps5">
						<?php if($errrole!="") print($errrole."<br/>"); ?>
						<select name="txtuserrole" class="intxt" id="txtuserrole" style="width:98%;">
							<?php
							if($_SESSION['role']=="SYSADMIN")
							{
								print("<option value='' ></option>");
								if($_POST['txtuserrole']=="RECRUITER")
									print("<option value='RECRUITER' selected>RECRUITER</option>");
								else
									print("<option value='RECRUITER'>RECRUITER</option>");
							}
							else if($_SESSION['role']=="RECRUITER")
							{
								print("<option value=''></option>");
								if($_POST['txtuserrole']=="TEAMMEMBER")
									print("<option value='TEAMMEMBER' selected>TEAMMEMBER</option>");
								else
									print("<option value='TEAMMEMBER'>TEAMMEMBER</option>");
							}
							?>
						</select>
					</div>
				</div>
				<div class="clrfix">
					<div class="lft trght w25per f13px bld mt10">Security Code: *</div>
					<div class="lft tlft w75per f13px reg mt10 ps5"><?php if($errcap!="") print($errcap."<br/>"); ?><input type="text" id="txtcap" name="txtcap" size="20" maxlength="10" /><div class="tip">Tip: Type the code shown below</div></div>
				</div>
				<div class="clrfix">
					<div class="lft trght w25per f13px bld mt10"></div>
					<div class="lft tlft w75per f13px reg mt10 ps5">
						<?php if($errs!="") print($errs."<br/>"); ?>
						<div class="lft tlft w60per">
							<img id="imgCaptcha" src="<?php print($root.'includes/cap'.DS.'captcha.php'); ?>" />
						</div>
						<div class="rght trght w30per ms10 cp">
							<img src="<?php print($root."assets".DS."refresh.gif"); ?>" alt="Refresh Captcha" title="Refresh Captcha" id="captcha-refresh" onClick="javascript:refreshCap();" />
						</div>
					</div>
				</div>
				<div class="clrfix">
					<div class="lft trght w25per f13px bld mt10"></div>
					<div class="lft tlft w75per f13px reg mt10 ps5">
						<input type="submit" id="btnsubmit" name="btnsubmit" onclick="SubmitFrm();" value="Submit" />
						&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="reset" onclick="javascript:CancelFrm('<?php getPage() ?>');" value="Cancel" />
					</div>
				</div>
				<div class="clrfix mt10">
					<input type="hidden" id="hdnorg" name="hdnorg" />
				</div>
				<script type="text/javascript" language="javascript">
				function SubmitFrm()
				{
					document.getElementById('hdnorg').value=document.getElementById('ddlOrganisation').options[document.getElementById('ddlOrganisation').selectedIndex].text;
				}
							
				function CancelFrm(frm)
				{	
					document.forms[0].action=frm;
					document.forms[0].submit();
				}
				function refreshCap()
				{
					document.getElementById('imgCaptcha').src="<?php print($root); ?>includes/cap/captcha.php?"+Math.random();
				}
				</script>
			</form>
		</div>
	</div>
	<?php
	include "foot.html";