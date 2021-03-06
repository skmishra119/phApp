<?php

class DataModel extends validation{
	private $Data=array();
	private $PGS='';
	private $pgSrch='';
	//var $AdminPath='admin/';
	
	public function connectDB(){
		$con=mysqli_connect($this->DBHost,$this->DBUser,$this->DBPass,$this->DBName);
		if(!$con){
			parent::getError('main', 'Error: '.mysqli_connect_error());
			return false;
		}
		return $con;
	}
	
	public function closeDB($con){
		mysqli_close($con);
	}
	public function getModelData($pData){
		$this->Data=$this->htmlentities_deep($pData);
	}
	
	protected function htmlentities_deep($value){
	    if(is_array($value)){
			$value = array_map(array( 'DataModel','htmlentities_deep'), $value); 
		} else {
			$value = htmlentities($value, ENT_QUOTES);
		}
    	return $value;
	}
	
	public function getDataArray($tbl,$cols,$whr,$ord){
		$data=array();
		$cnx=$this->connectDB();
		$sqx="select ".$cols." from ".$tbl." ".((trim($whr)!='')? 'where '.$whr:'')." ".$ord;
//    	var_dump($sqx);
		$rsx=mysqli_query($cnx,$sqx);
		if(!$rsx){
			parent::getError('main', mysqli_error($cnx));
			return false;
		}
		while($rwx=mysqli_fetch_assoc($rsx)){
			if(count($data)<=0) $data[]=$rwx; else array_push($data,$rwx);
		}
		$this->closeDB($cnx);
		//var_dump($data);
		return $data;
	}
	
	public function getUserInfo($UID){
		$UINFO=array();
		if(trim($UID)!=''){
			$MDATA=$this->getDataArray("t_pt_users u left join t_pt_settings s on u.userid=s.clientid", "u.userid, u.parentid, u.emailid, u.fullname, u.phone, u.role, u.domains, u.status, u.orgname, u.isdefault, u.authname, u.authemail, u.systemlink, s.params", "u.userid='".trim($UID)."' and u.status='ACTIVE'","");
			$UINFO = (sizeof($MDATA)>0) ? $MDATA[0] : array('userid'=>'','emailid'=>'','fullname'=>'','phone'=>'','role'=>'','domains'=>'','status'=>'','orgname'=>'','isdefault'=>'','authname'=>'','authemail'=>'','systemlink'=>'','params'=>'');
		}
		else
		{
			$UINFO = array('userid'=>'','emailid'=>'','fullname'=>'','phone'=>'','role'=>'','domains'=>'','status'=>'','orgname'=>'','isdefault'=>'','systemlink'=>'','authname'=>'','authemail'=>'','params'=>'');
		}
		return $UINFO;
	}
	
	function getUserSettingsByParams($TYP,$PTYP,$CID,$RCNT=false){
		$RET=true;
		$PVAL=0;
		$UDTA=$this->getUserInfo(trim($CID));
		//var_dump($UDTA);
		if(trim($UDTA['userid'])!='')
		{
			if(trim($UDTA['params'])!=''){
				$PARAMS=json_decode(html_entity_decode($UDTA['params']));
			}
			foreach ($PARAMS as $k=>$v){
				if(trim(strtoupper($k))==trim($PTYP)){
					$PVAL=intval(trim($v));
				}
			}
			if($PVAL!=0){
				switch(trim($TYP)){
					case "TARGETS":
						if($PVAL==-1) return true;
						$CDT=$this->getDataArray("t_pt_mailinglist", "count(*) as tot", "clientid='".trim($CID)."'", "");
						if((intval($CDT[0]['tot'])+intval($RCNT))>intval($PVAL)) $RET=false;
					break;
					case "TESTS":
						if($PVAL==-1) return true;
						$CDT=$this->getDataArray("t_pt_test", "count(*) as tot", "clientid='".trim($CID)."'", "");
						if(intval($CDT[0]['tot'])>intval($PVAL)) $RET=false;
					break;
				}
			}else{
				$RET=false;
			}
		}
		if(intval($PVAL==0) || $RET==false) $RET=false;
		return $RET;
	}
	
	public function Authenticate($TYP){
		//global $cr;
		$MDATA=$this->getDataArray("t_pt_users", "userid, password, role, status, systemlink, open, opendon", "emailid='".$this->Data['emailid']."'".((trim($TYP)=='CLIENT')?" and role='CLIENT'":" and role!='CLIENT'"),"");
		
		if(sizeof($MDATA)<=0)
		{
			parent::getError('main', ' User does not exists or credentials mismatched.');
			return false;
		}
		
		if(trim($MDATA[0]['status'])!='ACTIVE'){
			parent::getError('main', ' User account has not been activated.');
			return false;		
		}
		
		if(trim($MDATA[0]['password'])!=$this->encrypt($this->Data['password']))
		{
			if($MDATA[0]['open'] < $this->LoginAttempts)
			{
				if($this->saveRecord("ATTEMPT", $this->Data, "",$MDATA[0])==false)
				{
					parent::getError('main',' Unable to update user information.');
					return false;
				}
				parent::getError('main', ' Email address and Password did not match. You have only '.($this->LoginAttempts - $MDATA[0]['open']).' attempt(s) left.');
				return false;
			}
			if($MDATA[0]['open']==$this->LoginAttempts)
			{
				if($this->saveRecord("LOGINBLOCK", $this->Data, "",$MDATA[0])==false)
				{
					parent::getError('main',' Unable to update user information.');
					return false;
				} 
				parent::getError('main', ' Your account has been blocked due to multiple wrong attempt. Please try again after '.$this->LoginBlockTime.' minutes');
				return false;
			}
			if($MDATA[0]['open'] > $this->LoginAttempts)
			{
				parent::getError('main', ' Your account has been blocked due to multiple wrong attempt.');
				return false;
			}
			parent::getError('main', ' Email address and Password did not match.');
			return false;
		}
		//parent::getError('main', 'Wrong attempt '.$bdt.' CUR '.$cdt);
		//return false;
		if(date('Y-m-d H:i:s', strtotime($MDATA[0]['opendon']. ' +'.$this->LoginBlockTime.' minute')) >= date('Y-m-d H:i:s') && trim($MDATA[0]['opendon'])!='')
		{
			parent::getError('main', ' Your account has been blocked due to multiple wrong attempts.');
			return false;
		}
		
		if($this->saveRecord("SIGNIN", $this->Data, "",$MDATA[0])==false){
			parent::getError('main', ' Sorry, unable to sign In.');
			return false;
		}
		
		$_SESSION['AID']=trim($MDATA[0]['userid']);
	}
	
	public function is_duplicate($TYP,$COND,$RID){
		//global $cr;
		switch(trim($TYP)){
			case 'USERS':
				if($RID!='') $COND .= " and userid!='".$this->decrypt($RID)."'";
				$CDATA=$this->getDataArray("t_pt_users", "count(*) as tot", $COND,"");
			break;
			case 'SETTINGS':
				$CDATA=$this->getDataArray("t_pt_settings", "count(*) as tot", $COND,"");
			break;
			case "MAILING-LISTS":
				if($RID!='') $COND .= " and listid!='".$this->decrypt($RID)."'";
				$CDATA=$this->getDataArray("t_pt_mailinglist", "count(*) as tot", $COND,"");
			break;
			case "EMAIL-TEMPLATES":
				if($RID!='') $COND .= " and templateid!='".$this->decrypt($RID)."'";
				$CDATA=$this->getDataArray("t_pt_templates", "count(*) as tot", $COND,"");
			break;
			case "CMS":
				if($RID!='') $COND .= " and cmsid!='".$this->decrypt($RID)."'";
				$CDATA=$this->getDataArray("t_pt_cms", "count(*) as tot", $COND,"");
			break;
			case "TARGET":
				$CDATA=$this->getDataArray("t_pt_mailinglist", "count(*) as tot", $COND,"");
			break;
			case "TESTS":
				//if($RID!='') $COND .= " and listid!='".$this->decrypt($RID)."'";
				$CDATA=$this->getDataArray("t_pt_testrun", "count(*) as tot", $COND,"");
			break;
				
		}
		//return $CDATA[0]['tot'];
		//if(trim($RID)==''){
			if($CDATA[0]['tot']==0) return true; else return false;
		//}else{
			//if($CDATA[0]['tot']<=1) return true; else return false;
		//}
	}
	
	public function showDrps($TYP, $VAL, $SEL, $DrpId, $RID=false, $Opt=''){
		//global $cr;
		$STRG='';
		switch(trim($TYP)){
			case "ROLE":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'SYSADMIN','VALUE'=>'SYSADMIN'),'2'=>array('KEY'=>'MANAGER', 'VALUE'=>'MANAGER'),'3'=>array('KEY'=>'CLIENT','VALUE'=>'CLIENT'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "USER_STATUS":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'ACTIVE','VALUE'=>'ACTIVE'),'2'=>array('KEY'=>'INACTIVE', 'VALUE'=>'INACTIVE'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "YESNO":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'YES','VALUE'=>'YES'),'2'=>array('KEY'=>'NO', 'VALUE'=>'NO'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "TEMPLATE_TYPE":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'WEBLINK','VALUE'=>'Web Link'),'2'=>array('KEY'=>'ATTACHMENT', 'VALUE'=>'Attachment'),'3'=>array('KEY'=>'WEBFORM', 'VALUE'=>'Web Form'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "TEMPLATE_STATUS":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'DRAFT','VALUE'=>'Draft'),'2'=>array('KEY'=>'ACTIVE', 'VALUE'=>'Active'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case 'CLIENT_ONLY':
				$WHR = (trim($RID)!="") ? "status='ACTIVE' and role='CLIENT'" : "status='ACTIVE' and role='CLIENT'";
				$DDATA=$this->getDataArray("t_pt_users", "userid, fullname", $WHR, "order by fullname");
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">Select Option</option>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['userid'])) ? '<option value="'.trim($K['userid']).'" selected>'.trim($K['fullname']).'</option>' : '<option value="'.trim($K['userid']).'">'.trim($K['fullname']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case 'TEMPLATE_CLIENT':
				$WHR = (trim($RID)!="") ? "status='ACTIVE' and role='CLIENT'" : "status='ACTIVE' and role='CLIENT'";
				$DDATA=$this->getDataArray("t_pt_users", "userid, fullname, emailid", $WHR, "order by fullname");
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">All Clients</option>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['userid'])) ? '<option value="'.trim($K['userid']).'" selected>'.trim($K['fullname']).' ['.trim($K['emailid']).']</option>' : '<option value="'.trim($K['userid']).'">'.trim($K['fullname']).' ['.trim($K['emailid']).']</option>';
				}
				$STRG .= '</select>';
				break;
			case 'CLIENT_MANAGER':
				$WHR = (trim($RID)!="") ? "status='ACTIVE' and role='MANAGER' and userid!='".trim($this->decrypt($RID))."'" : "status='ACTIVE' and role='MANAGER'";
				$DDATA=$this->getDataArray("t_pt_users", "userid, fullname", $WHR, "order by fullname");
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">Select Option</option>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['userid'])) ? '<option value="'.trim($K['userid']).'" selected>'.trim($K['fullname']).'</option>' : '<option value="'.trim($K['userid']).'">'.trim($K['fullname']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case 'GROUP_CLIENT':
				$WHR = (trim($RID)!="") ? "status='ACTIVE' and role='CLIENT' and userid!='".trim($this->decrypt($RID))."'" : "status='ACTIVE' and role='CLIENT'";
				$DDATA=$this->getDataArray("t_pt_users", "userid, fullname", $WHR, "order by fullname");
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">Select Option</option>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['userid'])) ? '<option value="'.trim($K['userid']).'" selected>'.trim($K['fullname']).'</option>' : '<option value="'.trim($K['userid']).'">'.trim($K['fullname']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case 'GROUP_CLIENT_MAILING':
				$WHR = (trim($RID)!="") ? "status='ACTIVE' and clientid='".trim($this->decrypt($RID))."'" : "status='ACTIVE'";
				//var_dump($WHR,$RID);
				$DDATA=$this->getDataArray("t_pt_groups", "groupid, concat_ws(' - ',groupname,domain) as groupname", $WHR, "order by groupname");
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">Select Option</option>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['groupid'])) ? '<option value="'.trim($K['groupid']).'" selected>'.trim($K['groupname']).'</option>' : '<option value="'.trim($K['groupid']).'">'.trim($K['groupname']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case 'CLIENT_TEMPLATE':
				$WHR = (trim($RID)!="") ? "status='ACTIVE' and clientid in(0,".trim($RID).")" : "status='ACTIVE'";
				//var_dump($WHR);
				$DDATA=$this->getDataArray("t_pt_templates", "templateid, templatename", $WHR, "order by templatetype,templatename");
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">Select Option</option>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['templateid'])) ? '<option value="'.trim($K['templateid']).'" selected>'.trim($K['templatename']).'</option>' : '<option value="'.trim($K['templateid']).'">'.trim($K['templatename']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "CMS_TYPE":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'FAQ','VALUE'=>'Faq'),'2'=>array('KEY'=>'HELP', 'VALUE'=>'Help'),'3'=>array('KEY'=>'OTHER', 'VALUE'=>'Others'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "CMS_USER":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'ADMIN','VALUE'=>'Backend User'),'2'=>array('KEY'=>'CLIENT', 'VALUE'=>'Frontend User'),'3'=>array('KEY'=>'BOTH', 'VALUE'=>'All Users'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "SETTING_STATUS":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'ACTIVE','VALUE'=>'ACTIVE'),'2'=>array('KEY'=>'INACTIVE', 'VALUE'=>'INACTIVE'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "CLIENT_DOMAIN":
				$DMS=explode(',',trim($VAL['domains']));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">Select Option</option>';
				foreach ($DMS as $DMN){
					if(trim($DMN)!=""){
						$STRG .= (trim($SEL)==trim($DMN)) ? '<option value="'.trim($DMN).'" selected>'.trim($DMN).'</option>' : '<option value="'.trim($DMN).'">'.trim($DMN).'</option>';
					}
				}
				$STRG .= '</select>';
			break;
			case "REPORT_TYPE":
				$DDATA=array('0'=>array('KEY'=>'','VALUE'=>'Select Option'),'1'=>array('KEY'=>'OVERALL','VALUE'=>'Overall Details'),'2'=>array('KEY'=>'TOTAL_TEST', 'VALUE'=>'Campaign Synopsis'),'3'=>array('KEY'=>'ALL_TEST', 'VALUE'=>'Campaign Report'),'4'=>array('KEY'=>'TEST_DOMAIN','VALUE'=>'Domain wise Campaign report'), '5'=>array('KEY'=>'TEST_USER_EVENT','VALUE'=>'Event wise Campaign report'));
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['KEY'])) ? '<option value="'.trim($K['KEY']).'" selected>'.trim($K['VALUE']).'</option>' : '<option value="'.trim($K['KEY']).'">'.trim($K['VALUE']).'</option>';
				}
				$STRG .= '</select>';
			break;
			case "REPORT_CLIENT":
				$WHR = (trim($VAL['role'])!="SYSADMIN") ? "status='ACTIVE' and role='CLIENT' and parentid='".trim($VAL['userid'])."'" : "status='ACTIVE' and role='CLIENT'";
				//var_dump($WHR,$VAL);
				$DDATA=$this->getDataArray("t_pt_users", "userid, fullname", $WHR, "order by fullname");
				$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">Select Option</option>';
				foreach($DDATA as $K){
					$STRG .= (trim($SEL)==trim($K['userid'])) ? '<option value="'.trim($K['userid']).'" selected>'.trim($K['fullname']).'</option>' : '<option value="'.trim($K['userid']).'">'.trim($K['fullname']).'</option>';
				}
				$STRG .= '</select>';
			break;
		}
		return $STRG;
	}
	
	public function Submit($TYP,$RID=0,$UINFO,$UTYP=null){
		//var_dump($this->Data);
		//global $cr;
		switch(trim($TYP)){
			case "FORGOT_PASSWORD":
				$MDATA=$this->getDataArray("t_pt_users", "userid, fullname, role", "status='ACTIVE' and emailid='".$this->Data['emailid']."'","");
				//var_dump($MDATA,$UTYP);
				if(sizeof($MDATA)>0)
				{
					$SLINK=$this->generatePwd(10,false);
					$subj="Forgot password notification";
					$bdy='<div width="75%"><p>Dear '.$MDATA[0]['fullname'].',<br/><br/>We received a forgot password request from you.<br/><br/>You need to reset your password by visiting this link from your browser:<br/><a href="'.$this->getServerPath().'reset/user-pwd/'.trim($SLINK).'">'.$this->getServerPath().'reset/user-pwd/'.trim($SLINK).'</a><br/><br/>Warm Regards,<br/>Team @ TruShield<br/>http://www.trushieldinc.com/</p></div>';
					$mal=$this->sendMail($this->MailFrom, $this->FromName, $this->Data['emailid'], $subj, $bdy, true);
					if(trim($mal)!='SENT'){
						parent::getError('main', 'Notification email has not been sent. '.$mal);
						return false;
					}
					$this->Data['systemlink']=trim($SLINK);
					if($this->saveRecord("FORGOT_PASSWORD", $this->Data, $RID,$UINFO)==false){
						parent::getError('main', 'Unable to update record.');
						return false;
					}
				}else{
					parent::getError('main', 'The data you have provided, did not match with our records.');
					return false;
				}
				break;
			case "RESET_PWD":
				$MDATA=$this->getDataArray("t_pt_users", "userid, emailid, fullname, role, status", "userid='".$this->decrypt($RID)."'","");
				//var_dump($MDATA,$UTYP);
				if(sizeof($MDATA)>0)
				{
					$SLINK=$this->generatePwd(10,false);
					$subj="Reset password notification";
					$bdy='<div width="75%"><p>Dear '.$MDATA[0]['fullname'].',<br/><br/>We received a reset password request from you.<br/><br/>You need to reset your password by visiting this link from your browser:<br/><a href="'.((trim($MDATA[0]['role'])=="CLIENT")? $this->ClientURL : $this->getServerPath()).'reset/user-pwd/'.trim($SLINK).'">'.((trim($MDATA[0]['role'])=="CLIENT")? $this->ClientURL : $this->getServerPath()).'reset/user-pwd/'.trim($SLINK).'</a><br/><br/>Warm Regards,<br/>Team @ TruShield<br/>http://www.trushieldinc.com/</p></div>';
					$mal=$this->sendMail($this->MailFrom, $this->FromName, $MDATA[0]['emailid'], $subj, $bdy);
					if(trim($mal)!='SENT'){
						parent::getError('main', 'Notification email has not been sent. '.$mal);
						return false;
					}
					$this->Data['systemlink']=trim($SLINK);
					if($this->saveRecord("RESET_PWD", $this->Data, $RID,$UINFO)==false){
						parent::getError('main', 'Unable to update record.');
						return false;
					}
				}else{
					parent::getError('main', 'The data you have provided, did not match with our records.');
					return false;
				}
				break;
				break;
			case "RESET_PASSWORD":
				$MDATA=$this->getDataArray("t_pt_users", "userid, fullname, status", "emailid='".$this->Data['emailid']."' and systemlink='".$this->Data['systemlink']."'","");
				if(sizeof($MDATA)>0)
				{
					if(trim($MDATA[0]['status'])=="PENDING"){
						if($this->saveRecord("ACTIVATE_USER", $this->Data, $RID,$UINFO)==false){
							parent::getError('main', 'Unable to update record.');
							return false;
						}	
					}elseif(trim($MDATA[0]['status'])=="ACTIVE"){
						if($this->saveRecord("RESET_PASSWORD", $this->Data, $RID,$UINFO)==false){
							parent::getError('main', 'Unable to update record.');
							return false;
						}
					}
				} else {
					parent::getError('main', 'The data you have provided, did not match with our records.');
					return false;
				}
				break;
			case "USERS":
				if($this->is_duplicate("USERS", "emailid='".$this->Data['emailid']."'", $RID)==false){
					parent::getError('main', 'Duplicate record exists.');
					return false;
				}
				if(trim($this->Data['role'])=='CLIENT'){
					if($this->getDomainFromEmail(trim($this->Data['emailid'])) != $this->getDomainFromEmail(trim($this->Data['authemail']))){
						parent::getError('main', 'Authorizer Email must be from '.$this->getDomainFromEmail(trim($this->Data['emailid'])).' domain.');
						return false;
					}
				}
				if(trim($RID)==''){
					$SLINK=$this->generatePwd(10,false);
					$subj="New account created for you";
					$bdy='<div width="75%"><p>Dear '.$this->Data['fullname'].',<br/><br/>Your account has been created with our system. Your email address for accessing aaccount is: '.$this->Data['emailid'].'<br/><br/>Next, you need to activate your account by setting your password.<br/><br/>You need to reset your password by visiting this link from your browser:<br/><a href="'.((trim($this->Data['role'])=="CLIENT")? $this->ClientURL : $this->getServerPath()).'reset/newuser/'.trim($SLINK).'" target="_blank">'.((trim($this->Data['role'])=="CLIENT")? $this->ClientURL : $this->getServerPath()).'reset/newuser/'.trim($SLINK).'<br/><br/>Warm Regards,<br/>Team @ TruShield<br/>http://www.trushieldinc.com/</p></div>';
					$mal=$this->sendMail($this->MailFrom, $this->FromName, $this->Data['emailid'], $subj, $bdy);
					if(trim($mal)!='SENT'){
						parent::getError('main', 'Notification email has not been sent. '.$mal);
						return false;
					}else{
						$this->Data["systemlink"]=trim($SLINK);
					}
				}
				if($this->saveRecord("USERS",$this->Data,$RID,$UINFO)==false){
					parent::getError('main', 'Unable to update record.');
					return false;
				}	
				break;
			case "CAMPAIGN":
				$GDOM=$this->getDataArray("t_pt_users","userid, fullname","userid='".trim($this->Data['client'])."' and role='CLIENT' and status='ACTIVE'","");
				if(sizeof($GDOM)<=0){
					parent::getError('main', 'No record found.');
					return false;
				}
				break;
			case "SETTINGS":
				if($this->saveRecord("SETTINGS",$this->Data,$RID,$UINFO)==false){
					parent::getError('main', 'Unable to update record.');
					return false;
				}	
				break;
			case "MAILING-LISTS":
				if($this->getUserSettingsByParams('TARGETS', 'MAXTARGETS', $this->Data['clientid'])==false){
					parent::getError('main', 'Maximum limit exceeded.');
					return false;
				}
				$GDOMAIN='';
				$GDOM = $this->getDataArray("t_pt_users", "userid, domains", "userid='".trim($this->Data['clientid'])."' and status='ACTIVE'","");
				if(sizeof($GDOM)<=0){
					parent::getError('main', 'No domian information available for this client.');
					return false;
				}else{
					$GDOMAIN=trim($GDOM[0]['domains']);
				}
				
				if(!in_array($this->getDomainFromEmail(trim(strtolower($this->Data['emailid']))), array_map('trim',explode(', ',trim(strtolower($GDOMAIN)))))){
					parent::getError('main', 'Domain of the email address must be '.trim($GDOMAIN));
					return false;
				}
				if($this->is_duplicate("MAILING-LISTS", "emailid='".$this->Data['emailid']."' and clientid='".$this->Data['clientid']."'", $RID)==false){
					parent::getError('main', 'Duplicate record exists.');
					return false;
				}
				if($this->saveRecord("MAILING-LISTS",$this->Data,$RID,$UINFO)==false){
					parent::getError('main', 'Unable to update record.');
					return false;
				}
			break;
			case "EMAIL-TEMPLATES":
				if($this->is_duplicate("EMAIL-TEMPLATES", "clientid='".$this->Data['clientid']."' and templatename='".$this->Data['templatename']."'", $RID)==false){
					parent::getError('main', 'Duplicate record exists.');
					return false;
				}
				if($this->is_duplicate("EMAIL-TEMPLATES", "clientid='".$this->Data['clientid']."' and pagename='".$this->Data['pagename']."'", $RID)==false){
					parent::getError('main', 'Duplicate page exists.');
					return false;
				}
				/*$PGURL=$this->doesUrlExists($this->Data['pageurl']);
				if($PGURL!='OK'){
					parent::getError('main', $PGURL);
					return false;
				}*/				
				if($_FILES["logo"]["name"] != ''){
					$target_dir = "../public/uploads/logos/";
					$target_file = $target_dir . basename($_FILES["logo"]["name"]);
					$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
					
					$check = getimagesize($_FILES["logo"]["tmp_name"]);
					if($check == false) {
						parent::getError('main', 'Not an image file.');
						return false;
					}
					// Check if file already exists
					if (file_exists($target_file)) {
						parent::getError('main', 'Logo file already exists.');
						return false;
					}
					// Check file size
					if ($_FILES["fileToUpload"]["size"] > 102400) {
						parent::getError('main', 'File size is more than 100MB');
						return false;
					}
					// Allow certain file formats
					if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
						parent::getError('main', 'Only JPG, JPEG, PNG and GIF files are allowed.');
						return false;
					}
					
					if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
						parent::getError('main', 'Unable to upload file.');
						return false;
					}
				}
		
				if($this->saveRecord("EMAIL-TEMPLATES",$this->Data,$RID,$UINFO)==false){
					parent::getError('main', 'Unable to update record.');
					return false;
				}
			break;
			case "CMS":
				if($this->is_duplicate("CMS", "user='".$this->Data['user']."' and page='".$this->Data['page']."' and type='".$this->Data['type']."'", $RID)==false){
					parent::getError('main', 'Duplicate record exists.');
					return false;
				}
				if($this->saveRecord("CMS",$this->Data,$RID,$UINFO)==false){
					parent::getError('main', 'Unable to update record.');
					return false;
				}
			break;
		}
	}
	
	protected function saveRecord($TYP,$DATA,$RID,$UINFO){
		//global $this;
		$COLS='';
		$VALS='';
		$SQL='';
		$pg='';
		$DESC='';
		$NID='';
		unset($DATA['hdnRID']);
		unset($DATA['btnSubmit']);
		switch(trim($TYP)){
			case "ATTEMPT":
				$SQL = "update t_pt_users set open=open+1 where emailid='".trim($this->Data['emailid'])."'";
				$pg='./';
				$DESC='Wrong login attempt by client '.$this->Data['emailid'];
				break;
			case "LOGINBLOCK":
				$SQL = "update t_pt_users set open=open+1, opendon='".date('Y-m-d H:i:s')."' where emailid='".trim($this->Data['emailid'])."'";
				$pg='./';
				$DESC='Wrong login attempt by client '.$this->Data['emailid'];
				break;
			case "SIGNIN":
				$SQL = "update t_pt_users set systemlink=NULL, open=0, opendon=null where emailid='".trim($this->Data['emailid'])."'";
				$pg='./';
				$DESC='User signed in';
			break;
			case "RESET_PASSWORD":
				$SQL = "update t_pt_users set password='".trim($this->encrypt($this->Data['password']))."',systemlink=NULL where emailid='".trim($this->Data['emailid'])."' and systemlink='".trim($this->Data['systemlink'])."'";
				$pg='reset/user-pwd';
				$DESC='User '.$this->Data['emailid'].' has changed his password.';
			break;
			case "ACTIVATE_USER":
				$SQL = "update t_pt_users set password='".trim($this->encrypt($this->Data['password']))."',status='ACTIVE',systemlink=NULL where emailid='".trim($this->Data['emailid'])."' and systemlink='".trim($this->Data['systemlink'])."'";
				$pg='reset/newuser';
				$DESC='User '.$this->Data['emailid'].' has changed his password.';
			break;
			case "RESET_PWD":
				$SQL = "update t_pt_users set systemlink='".trim($DATA['systemlink'])."' where userid='".trim($this->decrypt($RID))."'";
				$pg='reset_password';
				$DESC='Reset password for '.trim($this->decrypt($RID)).' initiated by '.$UINFO['emailid'].' - '.$UINFO['fullname'];
			break;
			case "USERS":
				$pg='user';
				unset($DATA['csrftoken']);
				if(trim($RID)==''){
					foreach($DATA as $K=>$V){
						$COLS .= (trim($COLS)=='')?$K:",".$K;
						$VALS .= (trim($VALS)=='')?"'".$V."'":",'".$V."'";		
					}
					$SLINK=$this->generatePwd(10);
					$SQL = "insert into t_pt_users (".$COLS.",status) values (".$VALS.",'PENDING')";
					$DESC='User account created for '.$this->Data['fullname'].' - '.$this->Data['emailid']; 	
				}else{
					foreach($DATA as $K=>$V){
						$COLS .= (trim($COLS)=='')?$K."='".$V."'":", ".$K."='".$V."'";		
					}
					$SQL = "update t_pt_users set ".$COLS." where userid='".trim($this->decrypt($RID))."'";
					$DESC='User account updated for '.$this->Data['fullname'].' - '.$this->Data['emailid'];
				}
			break;
			case "SETTINGS":
				$pg='Settings';
				$SQL = "update t_pt_settings set clientid='".trim($this->Data['clientid'])."',params='".trim($this->Data['params'])."',status='".trim($this->Data['status'])."' where settingid='".trim($this->decrypt($RID))."'";
				$DESC='Settings for '.$this->Data['clientid'].' updated.';
			break;
			case "FORGOT_PASSWORD":
				$SQL = "update t_pt_users set systemlink='".trim($DATA['systemlink'])."' where emailid='".trim($this->Data['emailid'])."'";
				$pg='forgot_password';
				$DESC='Forgot password initiated by '.$this->Data['emailid'];
			break;
			case "MAILING-LISTS";
				$pg='mailing list';
				unset($DATA['csrftoken']);
				if(trim($RID)==''){
					foreach($DATA as $K=>$V){
						$COLS .= (trim($COLS)=='')?$K:",".$K;
						$VALS .= (trim($VALS)=='')?"'".$V."'":",'".$V."'";		
					}
					$SQL = "insert into t_pt_mailinglist (".$COLS.",createdby) values (".$VALS.",'".trim($UINFO['userid'])."')";
					$DESC='New mailing account for '.$this->Data['fullname'].' - '.$this->Data['emailid'].' created.'; 	
				}else{
					foreach($DATA as $K=>$V){
						$COLS .= (trim($COLS)=='')?$K."='".$V."'":", ".$K."='".$V."'";		
					}
					$SQL = "update t_pt_mailinglist set ".$COLS." where listid='".trim($this->decrypt($RID))."'";
					$DESC='Mailing account '.$this->Data['fullname'].' - '.$this->Data['emailid'].' updated.';
				}
			break;
			case "EMAIL-TEMPLATES";
				$pg='email-templates';
				unset($DATA['csrftoken']);
				if(trim($RID)==''){
					foreach($DATA as $K=>$V){
						$COLS .= (trim($COLS)=='')?$K:",".$K;
						$VALS .= (trim($VALS)=='')?"'".$V."'":",'".$V."'";		
					}
					$SQL = "insert into t_pt_templates (".$COLS.",createdby) values (".$VALS.",'".trim($UINFO['userid'])."')";
					$DESC='New template '.$this->Data['templatename'].' created.'; 	
				}else{
					foreach($DATA as $K=>$V){
						$COLS .= (trim($COLS)=='')?$K."='".$V."'":", ".$K."='".$V."'";		
					}
					$SQL = "update t_pt_templates set ".$COLS." where templateid='".trim($this->decrypt($RID))."'";
					$DESC='Template '.$this->Data['templatename'].' updated.';
				}
			break;
			case "UPLOAD-TARGET":
				$TREC=0;
				$pg='test->UPLOAD_TARGET';
				$DESC = 'Target '.$this->Data['targetfile'].' uploaded by '.$UINFO['fullname'].' ['.$UINFO['emailid'].']';
				$COLS = implode(",",array_keys($DATA[0]));
				$VALS='';
				unset($DATA['csrftoken']);
				foreach($DATA as $K=>$V){
					$VALS .= " ('".implode("','",array_values($DATA[$K]))."'),";		
				}
				$VALS = substr($VALS,0,strlen($VALS)-1).";";
				$SQL = "insert into t_pt_mailinglist (".$COLS.") values ".$VALS."";
			break;
			case "CMS":
				$DATA['url_alias']=$this->createAlias($this->Data);
				unset($DATA['csrftoken']);
				if(trim($RID)==''){
					foreach($DATA as $K=>$V){
						$COLS .= (trim($COLS)=='')?$K:",".$K;
						$VALS .= (trim($VALS)=='')?"'".$V."'":",'".$V."'";		
					}
					$SQL = "insert into t_pt_cms (".$COLS.",createdby) values (".$VALS.",'".trim($UINFO['userid'])."')";
					$DESC='New cms page '.$DATA['url_alias'].' created.'; 	
				}else{
					foreach($DATA as $K=>$V){
						$COLS .= (trim($COLS)=='')?$K."='".$V."'":", ".$K."='".$V."'";		
					}
					$SQL = "update t_pt_cms set ".$COLS." where cmsid='".trim($this->decrypt($RID))."'";
					$DESC='Cms page '.$DATA['url_alias'].' updated.';
				}
			break;	
		}	
		if(trim($SQL)=="") return false;
		$CNX=$this->connectDB();
		$RSX=mysqli_query($CNX,$SQL);
		if(trim($TYP)=="USERS" && trim($DATA['role'])=='CLIENT'){
			if(trim($RID)=="")
			{
				$NID=$this->getNewlyInsertedId($CNX);
				$SQY="insert into t_pt_settings (clientid,status,createdby) values ('".trim($NID)."','ACTIVE','".trim($UINFO['userid'])."')";
				$RSY=mysqli_query($CNX,$SQY);
			}else{
				if($this->is_duplicate("SETTINGS", "clientid='".trim($this->decrypt($RID))."'", $RID)==true){
					$SQY="insert into t_pt_settings (clientid,status,createdby) values ('".trim($this->decrypt($RID))."','ACTIVE','".trim($UINFO['userid'])."')";
					$RSY=mysqli_query($CNX,$SQY);
				}	
			}
		}
		//var_dump($SQY);
		$this->prepareLogs($pg,$UINFO['userid'], $pg, $DESC);
		if($RSX) return true; else return false;
		$this->closeDB($CNX);
	}
	
	protected function createAlias($DATA){
		$ALS=trim(preg_replace("/[\s]+/", "-",preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($DATA['type'])))).'/'.trim(preg_replace("/[\s]+/", "-",preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($DATA['page']))));
		return trim($ALS);
	}
	
	protected function getNewlyInsertedId($CON){
		$rid=mysqli_insert_id($CON);
		return $rid;
	}
	
	public function delete($TYP,$RID,$UINFO){
		//globa$thiscr;
		$PGN='';
		$DESC='';
		switch(trim($TYP)){
			case "USERS":
				$PGN='user';
				
				if(trim($RID)==''){
					parent::getError('main', 'No record selected for delete.');
					return false;
				}
				if($this->is_duplicate("USERS", "parentid='".trim($this->decrypt($RID))."'", $RID)==false){
					parent::getError('main', 'Client record exists, unable to delete.');
					return false;
				}
				/*if($this->is_duplicate("GROUPS", "clientid='".trim($this->decrypt($RID))."'", $RID)==false){
					parent::getError('main', 'Transaction exists, unable to delete.');
					return false;
				}*/
				$SQL="delete from t_pt_users where userid='".trim($this->decrypt($RID))."'";
				//parent::getError('main', $DESC); 
				$UDT=$this->getDataArray("t_pt_users", "concat_ws(' - ',fullname,emailid) as uname", "userid='".trim($this->decrypt($RID))."'", "");
				$DESC='User '.trim($UDT[0]['uname']).' has been deleted.';
						
				$CNX=$this->connectDB();
				$RSX=mysqli_query($CNX,$SQL);
				//parent::getError('main', $RSX); return false;
				if(!$RSX){
					parent::getError('main', 'Unable to delete this record.');
					return false;
				}  
				$SQL="delete from t_pt_settings where clientid='".trim($this->decrypt($RID))."'";
				  
				$RSX=mysqli_query($CNX,$SQL);
				$this->closeDB($CNX);
				$this->prepareLogs("user", trim($UINFO['userid']), $PGN, $DESC);
			break;
			case "MAILING-LISTS":
				$PGN='mailing-lists';
				if(trim($RID)==''){
					parent::getError('main', 'No record selected for delete.');
					return false;
				}
				if($this->is_duplicate("TESTS", "listid='".trim($this->decrypt($RID))."'", $RID)==false){
					parent::getError('main', 'Transaction exists, unable to delete.');
					return false;
				}
				
				$SQL="delete from t_pt_mailinglist where listid='".trim($this->decrypt($RID))."'";
				$GDT=$this->getDataArray("t_pt_mailinglist", "concat_ws(' - ',fullname, emailid) as lname", "listid='".trim($this->decrypt($RID))."'", "");
				$DESC='Mailing list '.$GDT[0]['lname'].' has been deleted ';
				$CNX=$this->connectDB();
				$RSX=mysqli_query($CNX,$SQL);
				if(!$RSX){
					parent::getError('main', 'Unable to delete this record.');
					return false;
				}
				$this->closeDB($CNX);
				$this->prepareLogs("mailing-list", trim($UINFO['userid']), $PGN, $DESC);
			break;
			case "EMAIL-TEMPLATES":
				$PGN='email-templates';
				if(trim($RID)==''){
					parent::getError('main', 'No record selected for delete.');
					return false;
				}
				$SQL="delete from t_pt_templates where templateid='".trim($this->decrypt($RID))."'";
				$GDT=$this->getDataArray("t_pt_templates", "templatename", "templateid='".trim($this->decrypt($RID))."'", "");
				$DESC='Email template '.$GDT[0]['templatename'].' has been deleted ';
				$CNX=$this->connectDB();
				$RSX=mysqli_query($CNX,$SQL);
				if(!$RSX){
					parent::getError('main', 'Unable to delete this record.');
					return false;
				}
				$this->closeDB($CNX);
				$this->prepareLogs("email-templates", trim($UINFO['userid']), $PGN, $DESC);
			break;
			case "CMS":
				$PGN='cms page';
				if(trim($RID)==''){
					parent::getError('main', 'No record selected for delete.');
					return false;
				}
				$SQL="delete from t_pt_cms where cmsid='".trim($this->decrypt($RID))."'";
				$GDT=$this->getDataArray("t_pt_cmss", "url_alias", "cmsid='".trim($this->decrypt($RID))."'", "");
				$DESC='Cms page '.$GDT[0]['url_alias'].' has been deleted ';
				$CNX=$this->connectDB();
				$RSX=mysqli_query($CNX,$SQL);
				if(!$RSX){
					parent::getError('main', 'Unable to delete this record.');
					return false;
				}
				$this->closeDB($CNX);
				$this->prepareLogs("CMS", trim($UINFO['userid']), $PGN, $DESC);
			break;
			case "SETTINGS":
				$PGN='Settings';
				if(trim($RID)==''){
					parent::getError('main', 'No record selected for delete.');
					return false;
				}
				$SDT=$this->getDataArray("t_pt_settings s, t_pt_users u", "count(*) as 'tot'", "s.clientid=u.userid and u.status='ACTIVE' and s.settingid='".trim($this->decrypt($RID))."'", "");
				if(sizeof($SDT)<=0){
						parent::getError('main', 'Appropraite record not found.');
					return false;
				}elseif(intval($SDT[0]['tot'])>0){
						parent::getError('main', 'Active client exists, Unable to delete.');
					return false;
				}
				$SQL="delete from t_pt_settings where settingid='".trim($this->decrypt($RID))."'";
				$DESC='Settings for '.trim($this->decrypt($RID)).' has been deleted ';
				$CNX=$this->connectDB();
				$RSX=mysqli_query($CNX,$SQL);
				if(!$RSX){
					parent::getError('main', 'Unable to delete this record.');
					return false;
				}
				$this->closeDB($CNX);
				$this->prepareLogs("SETTINGS", trim($UINFO['userid']), $PGN, $DESC);
			break;
			case "TESTS":
				$PGN='TESTS';
				if(trim($RID)==''){
					parent::getError('main', 'No record selected for delete.');
					return false;
				}
				$SDT=$this->getDataArray("t_pt_test t", "count(*) as 'tot'", "t.testid=".trim($this->decrypt($RID))." and t.process='TESTRUN'", "");
				if(sizeof($SDT)<=0){
					parent::getError('main', 'Appropraite record not found.');
					return false;
				}elseif(intval($SDT[0]['tot']) <= 0){
					parent::getError('main', 'Unable to cancel this test.');
					return false;
				}
				
				$CNX=$this->connectDB();
				$SQL="update t_pt_test set process ='CANCELLED', statusdate = '".date('Y-m-d H:i:s')."' where testid='".trim($this->decrypt($RID))."'";
				$RSX=mysqli_query($CNX,$SQL);
				if($RSX){
					$SQL1="update t_pt_testrun set status ='CANCELLED', statusdate = '".date('Y-m-d H:i:s')."' where testid='".trim($this->decrypt($RID))."'";
					$RSX1=mysqli_query($CNX,$SQL1);
					if(!RSX1){
						$SQL2="update t_pt_test set process ='TESTRUN', statusdate = '".date('Y-m-d H:i:s')."' where testid='".trim($this->decrypt($RID))."'";
						$RSX2=mysqli_query($CNX,$SQL2);
						parent::getError('main', 'Unable to cancel this test.');
						return false;
					}
				} else{
					parent::getError('main', 'Unable to cancel this test.');
					return false;
				}
				
				$DESC='Test for '.trim($this->decrypt($RID)).' has been cancelled ';
				$this->closeDB($CNX);
				$this->prepareLogs("TESTS", trim($UINFO['userid']), $PGN, $DESC);
			break;
		}
	}
	
	protected function prepareLogs($TYP=null,$UID,$PG,$DESC){
		$SQY="insert into t_pt_logs (userid,pgname,description) values('".trim($UID)."','".trim($PG)."','".trim($DESC)."')";
		$CNY=$this->connectDB();
		$RSY=mysqli_query($CNY,$SQY);
		return true;		
	}
	
	protected function paginate($MDATA){
		$this->PGS='';
		$tot_rec=count($MDATA);
		//$recPerPG = (trim($_GET['cnt'])!='') ? $_GET['cnt'] : $tot_rec;
		$recPerPG = $this->RecPerPage;
		$PG = (isset($_GET['pg'])!='') ? $_GET['pg'] : 1;
		if(preg_match('/\?pg=\d+/is',DROOT,$mat))
		{
			$ROOT=preg_replace('/\?pg=\d+/is','',DROOT);
		}
		$start=0;
		$end=$recPerPG;
		$paginate="";
		//var_dump($ROOT); exit;
		/*****************PREPARE PAGINATION********************/
		if($tot_rec > $recPerPG)
		{
			if($PG < ($tot_rec/$recPerPG))
			{
				if($PG>1)
				{
					$start = (($PG-1)*$recPerPG)+1;
					$end = $start + ($recPerPG-1);
					$paginate = '<a class="pgx btn btn-default" href="?pg='.($PG-1).'"><i class="fa fa-angle-double-left"></i> Prev</a><a class="pgx btn btn-default" href="?pg='.($PG+1).'">Next <i class="fa fa-angle-double-right"></i></a>';
				}
				else
				{
					$paginate = '<a class="pgx btn btn-default" href="?pg='.($PG+1).'">Next <i class="fa fa-angle-double-right"></i></a>';
				}
			}
			else
			{ 
				$start = (($PG-1)*$recPerPG)+1;
				$end = $start + ($tot_rec%$recPerPG)-1;
				$paginate = '<a class="pgx btn btn-default" href="?pg='.($PG-1).'"><i class="fa fa-angle-double-left"></i> Prev</a>';
			}
			$recs = array_slice($MDATA, (($start>0)?$start-1:$start), $recPerPG);
			$this->PGS = $paginate;
		}
		else
		{
			$start=1;
			$recs=$MDATA;
			$end=count($recs);
		}
		return $recs;
	}
	
	protected function showPageSearch($TYP,$MDATA){
		$recs=$MDATA;
		$results=array();
		$SRCH = (isset($_POST['__s'])!='') ? $_POST['__s'] : '';
		$this->pgSrch='<div class="pager"><form id="frmPgSrch" name="frmPgSrch" method="post" class="dataTables_filter"><label><input placeholder="Search..." class="form-control input-sm" type="text" id="__s" name="__s" value="'.$SRCH.'" /><input  type="submit" id="btnPgSrch" name="btnSrch" value="&#xf002;" class="btn btn-default"><label></form>';
		if(trim($SRCH)!=''){
			foreach($recs as $R){
				foreach($R as $ky=>$vl){
					if(stristr($vl, $SRCH)!=false){
						array_push($results,$R);
						break;
					}
				}
			}
		}else{
			$results=$MDATA;
		}
		return $results;
	}
	
	protected function showStatusClass($status) {
		$addClass=''; 
		if(strtolower(trim($status))==="active" || strtolower(trim($status))==="passed"){
			$addClass = "active_class";
		}elseif(strtolower(trim($status))==="pending") {
			  $addClass = "pending_class";
		}elseif(strtolower(trim($status))==="inactive" || strtolower(trim($status))==="failed") {
			  $addClass = "inactive_class";
		}
		return $addClass;  
   	}
	
   	public function getClientIDforManager($UINFO){
   		$STRG='';
   		$CDATA=$this->getDataArray("t_pt_users", "userid", "status!='DELETED' and parentid='".trim($UINFO['userid'])."'", "order by userid");
   		//var_dump($CDATA);
		if(sizeof($CDATA)>0){
			foreach($CDATA as $K=>$V){
				$STRG .= (trim($STRG)=='') ? trim($V['userid']) : ','.trim($V['userid']);
			}
		}
		return $STRG;
   	}
   	
	public function showGrid($TYP,$UINFO,$FILTER,$PGS=true){
		//global $cr;
		$TBL='';
		$WHR='';
		$UROLE=trim($UINFO['role']);
		switch(trim($TYP)){
			case "SETTINGS":
				$MDATA=$this->getDataArray("t_pt_settings s", "s.settingid, (select concat_ws(' - ',u.fullname,u.emailid) as name from t_pt_users u where s.clientid=u.userid) as client, s.params, s.status, date_format(s.createdon,'%b %d %Y') as 'createdon'", "s.status!='DELETED'", "order by s.clientid, s.settingid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$TBL = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>No record found as per the criteria defined.</p></div></div>';
					return $TBL;
				}
				/*$SDATA=$this->showPageSearch(trim($TYP),$MDATA);
				$TBL = $this->pgSrch;
				$PDATA=$this->paginate($SDATA);$this->PGS.'</div>*/
				$TBL .= '<form id="frmTools" method="post" action="setting"><table width="100%" id="repTable" class="table table-striped" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%">#</th><th width="30%">Client</th><th width="50%">Parameters</th><th width="10%">created On</th><th width="8%">Status</th></tr></thead><tbody>';
				foreach($MDATA as $rwx){
					
					$TBL .= '<tr><td><div class="check_design"><div class="square"></div></div><input type="checkbox" name="chkIDS[]" class="chkIDS" value="'.$this->encrypt(trim($rwx['settingid'])).'"/></td><td><a href="view/settings/'.$this->encrypt(trim($rwx['settingid'])).'">'.trim($rwx['client']).'</a></td><td>';
					if(trim($rwx['params'])!=''){
						$PARMS = json_decode(html_entity_decode($rwx['params']),true);
						//var_dump($PARMS);
						if(sizeof($PARMS)>0){
							foreach ($PARMS as $k=>$v){
								if(trim($v)!=''){
									$TBL .= '<div><span>'.$k.': </span>&nbsp;<span>'.(($k!='smtpPwd')?$v:'###############').'</span></div>';
								}
							}
						} else {
							$TBL .= "";
						}
					}
					else $TBL .= "";
				
					$TBL .= '</td><td>'.trim($rwx['createdon']).'</td><td class="'.$this->showStatusClass($rwx['status']).'">'.trim($rwx['status']).'</td></tr>';
				}
				
				$TBL .= '</body></table><input type="hidden" id="hdnRID" name="hdnRID" /><input type="hidden" id="hdnMode" name="hdnMode" /></form><div class="pager">'.$this->PGS.'</div>';
			break;
			case "USERS":
				$WHR = (trim($UROLE)=="MANAGER") ? " u.userid in (".$this->getClientIDforManager($UINFO).")": "";
				$MDATA=$this->getDataArray("t_pt_users u", "u.userid, u.emailid, u.fullname, u.role, u.domains, u.orgname, u.status, date_format(u.createdon,'%b %d %Y') as 'createdon', (select concat_ws('<br/>',x.fullname,x.emailid) from t_pt_users x where u.parentid=x.userid and x.role='MANAGER' and x.status='ACTIVE') as members", $WHR, "order by u.userid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$TBL = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>No record found as per the criteria defined.</p></div></div>';
					return $TBL;
				}
				/*$SDATA=$this->showPageSearch(trim($TYP),$MDATA);
				$TBL = $this->pgSrch;
				$PDATA=$this->paginate($SDATA);$this->PGS.'</div>*/
				$TBL .= '<form id="frmTools" method="post" action="user"><table width="100%" id="repTable" class="table table-striped" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%">#</th><th width="15%">Email Address</th><th width="15%">Full Name</th><th width="8%">Role</th><th width="15%">Organization</th><th width="8%">Domains</th><th width="8%">Status</th><th width="15%">Member Of</th><th width="12%">Member Since</th></tr></thead><tbody>';
				foreach($MDATA as $rwx){
				
					$TBL .= '<tr><td><div class="check_design"><div class="square"></div></div><input type="checkbox" name="chkIDS[]" class="chkIDS" value="'.$this->encrypt(trim($rwx['userid'])).'"/></td><td><a href="view/users/'.$this->encrypt(trim($rwx['userid'])).'">'.trim($rwx['emailid']).'</a></td><td>'.trim($rwx['fullname']).'</td><td>'.trim($rwx['role']).'</td><td>'.trim($rwx['orgname']).'</td><td>'.trim($rwx['domains']).'</td><td class="'.$this->showStatusClass($rwx['status']).'">'.trim($rwx['status']).'</td><td>'.trim($rwx['members']).'</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				
				$TBL .= '</body></table><input type="hidden" id="hdnRID" name="hdnRID" /><input type="hidden" id="hdnMode" name="hdnMode" /></form><div class="pager">'.$this->PGS.'</div>';
			break;
			case "MAILING_LISTS":
				$WHR = (trim($UROLE)=="MANAGER") ? " and ux.userid in (".$this->getClientIDforManager($UINFO).")": "";
				$MDATA=$this->getDataArray("t_pt_mailinglist m inner join t_pt_users ux", "distinct m.listid, (select g.groupname from t_pt_groups g where m.groupid=g.groupid) as groupname, (select concat_ws(' - ',u.fullname,u.emailid) from t_pt_users u where m.clientid=u.userid) as clientname, m.emailid, m.fullname, date_format(m.createdon,'%b %d %Y') as 'createdon'", "m.status!='DELETED' and m.clientid=ux.userid".$WHR, "order by m.listid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$TBL = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>No record found as per the criteria defined.</p></div></div>';
					return $TBL;
				}
				/*$SDATA=$this->showPageSearch(trim($TYP),$MDATA);
				$TBL = $this->pgSrch;
				$PDATA=$this->paginate($SDATA);$this->PGS.'</div>*/
				$TBL .= '<form id="frmTools" method="post" action="mailing-list"><table width="100%" id="repTable" class="table table-striped" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%">#</th><th width="18%">Email Address</th><th width="20%">Full Name</th><th width="20%">Group Name</th><th width="20%">Client Name</th><th width="20%">Created On</th></tr></thead><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= '<tr><td><div class="check_design"><div class="square"></div></div><input type="checkbox" name="chkIDS[]" class="chkIDS" value="'.$this->encrypt(trim($rwx['listid'])).'"/></td><td><a href="view/mailing-lists/'.$this->encrypt(trim($rwx['listid'])).'">'.trim($rwx['emailid']).'</a></td><td>'.trim($rwx['fullname']).'</td><td>'.trim($rwx['groupname']).'</td><td>'.trim($rwx['clientname']).'</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table><input type="hidden" id="hdnRID" name="hdnRID" /><input type="hidden" id="hdnMode" name="hdnMode" /></form><div class="pager">'.$this->PGS.'</div>';
			break;
			case "EMAIL-TEMPLATES":
				$MDATA=$this->getDataArray("t_pt_templates t", "t.templateid, (select concat_ws('<br/>',fullname,emailid) from t_pt_users u where t.clientid=u.userid) as client, t.templatetype, t.templatename, t.subject, t.body, t.status, date_format(t.createdon,'%b %d %Y') as 'createdon'", "t.status!='DELETED'", "order by t.templateid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$TBL = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>No record found as per the criteria defined.</p></div></div>';
					return $TBL;
				}
				/*$SDATA=$this->showPageSearch(trim($TYP),$MDATA);
				$TBL = $this->pgSrch;
				$PDATA=$this->paginate($SDATA);$this->PGS.'</div>*/
				$TBL .= '<form id="frmTools" method="post" action="email-template"><table width="100%" id="repTable" class="table table-striped" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%">#</th><th width="13%">Template Name</th><th width="15%">Template Type</th><th width="15%">Subject</th><th width="30%">Body</th><th width="15%">Client Name</th><th width="10%">Status</th></tr></thead><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= '<tr><td><div class="check_design"><div class="square"></div></div><input type="checkbox" name="chkIDS[]" class="chkIDS" value="'.$this->encrypt(trim($rwx['templateid'])).'"/></td><td><a href="view/email-templates/'.$this->encrypt(trim($rwx['templateid'])).'">'.trim($rwx['templatename']).'</a></td><td>'.trim($rwx['templatetype']).'</td><td>'.trim($rwx['subject']).'</td><td>'.html_entity_decode(trim($rwx['body'])).'</td><td>'.trim($rwx['client']).'</td><td class="'.$this->showStatusClass($rwx['status']).'">'.trim($rwx['status']).'</td></tr>';
				}
				$TBL .= '</body></table><input type="hidden" id="hdnRID" name="hdnRID" /><input type="hidden" id="hdnMode" name="hdnMode" /></form><div class="pager">'.$this->PGS.'</div>';
			break;
			case "TARGETS":
				$MDATA=$this->getDataArray("t_pt_mailinglist m inner join t_pt_users ux", "distinct m.listid, (select g.groupname from t_pt_groups g where m.groupid=g.groupid) as groupname, (select concat_ws(' - ',u.fullname,u.emailid) from t_pt_users u where m.clientid=u.userid) as clientname, m.emailid, m.fullname, date_format(m.createdon,'%b %d %Y') as 'createdon'", "m.status!='DELETED' and m.clientid='".trim($UINFO['userid'])."' and m.groupid='".trim($_SESSION['TEST']['groupid'])."'", "order by m.listid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$TBL = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>No record found as per the criteria defined.</p></div></div><div class="frm"><div class="ctrls pull-right" style="padding-right:10px;padding-bottom:18px;"><a class="btn btn-default btn-cancel" href="'.DROOT.XROOT.'testing?pg='.trim($this->encrypt('GROUP')).'"><span class="ui-button-text">Cancel &nbsp;&#xf057;</span></a></div></div>';
					return $TBL;
				}
				/*$PDATA=$this->showPageSearch(trim($TYP),$MDATA);
				$TBL = $this->pgSrch;
				//$PDATA=$this->paginate($SDATA);$this->PGS.'</div>*/
				$TBL .= '<form id="frmTools" method="post" action="testing"><input type="hidden" id="listids" name="listids" /><input type="hidden" id="hdnRID" name="hdnRID" /><table width="100%" id="repTable" class="table table-striped" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%"><div class="check_design"><div id="chkHDR" class="square"></div></div><input type="checkbox" id="chkALL" class="chkALL" name="chkALL" /></th><th width="18%">Email Address</th><th width="20%">Full Name</th><th width="20%">Group Name</th><th width="20%">Client Name</th><th width="20%">Created On</th></tr></thead><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= '<tr><td><div class="check_design"><div class="square"></div></div><input type="checkbox" name="chkIDS[]" class="chkIDS" value="'.$this->encrypt(trim($rwx['listid'])).'"/></td><td><a href="view/targets/'.$this->encrypt(trim($rwx['listid'])).'">'.trim($rwx['emailid']).'</a></td><td>'.trim($rwx['fullname']).'</td><td>'.trim($rwx['groupname']).'</td><td>'.trim($rwx['clientname']).'</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table><input type="hidden" id="hdnMode" name="hdnMode" /><div class="frm"><div class="ctrls pull-right" style="padding-right:10px;padding-bottom:18px;"><a class="btn btn-default btn-cancel" href="'.DROOT.XROOT.'testing?pg='.trim($this->encrypt('GROUP')).'"><span class="ui-button-text">Cancel &nbsp;&#xf057;</span></a><input type="submit" class="btn btn-default" id="btnSubmit" name="btnSubmit" value="Save & Next &nbsp;&#xf0a9;" /></div></div></form><div class="pager">'.$this->PGS.'</div>';
			break;
			case "CMSS":
				$MDATA=$this->getDataArray("t_pt_cms", "cmsid, type, page, url_alias, heading, contents, user, date_format(createdon,'%b %d %Y') as 'createdon'", "status!='DELETED'", "order by type, cmsid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$TBL = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>No record found as per the criteria defined.</p></div></div>';
					return $TBL;
				}
				/*$PDATA=$this->showPageSearch(trim($TYP),$MDATA);
				$TBL = $this->pgSrch;
				//$PDATA=$this->paginate($SDATA);$this->PGS.'</div>*/
				$TBL .= '<form id="frmTools" method="post" action="cms"><table width="100%" id="repTable" class="table table-striped" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%">#</th><th width="15%">Page Name</th><th width="10%">Type</th><th width="8%">User for</th><th width="20%">Heading</th><th width="30%">Content</th><th width="15%">Created On</th></tr></thead><tbody>';
				foreach($MDATA as $rwx){
					$DESC = (strlen(trim(strip_tags(html_entity_decode($rwx['contents']))))>200) ? substr(trim(strip_tags(html_entity_decode($rwx['contents']))),0,200).'...' : trim(strip_tags(html_entity_decode($rwx['contents'])));
					$TBL .= '<tr><td><div class="check_design"><div class="square"></div></div><input type="checkbox" name="chkIDS[]" class="chkIDS" value="'.$this->encrypt(trim($rwx['cmsid'])).'"/></td><td><a href="view/cmss/'.$this->encrypt(trim($rwx['cmsid'])).'">'.trim($rwx['page']).'</a></td><td>'.trim($rwx['type']).'</td><td>'.trim($rwx['user']).'</td><td>'.trim($rwx['heading']).'</td><td>'.trim($DESC).'</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table><input type="hidden" id="hdnRID" name="hdnRID" /><input type="hidden" id="hdnMode" name="hdnMode" /></form><div class="pager">'.$this->PGS.'</div>';
			break;
			case "TESTS":
				if(trim($UINFO['role'])=='CLIENT'){
					$MDATA=$this->getDataArray("t_pt_groups g, t_pt_users u, t_pt_test t, t_pt_templates tp", "t.testid as 'testid', u.fullname as 'Client Name', g.groupname as 'Group Name', g.domain as 'Domain', tp.templatename as 'Template', (case when t.process='TESTRUN' then 'SCHEDULED' when t.process = 'CANCELLED' then concat(t.process,'<br/><span class=\"tip\">[',date_format(t.statusdate,'%b %d %Y %H:%i:%s'),']</span>') else t.process end) as 'Status',  date_format(t.createdon,'%M %d %Y %H:%i:%s') as 'Created On', date_format(t.startdate,'%M %d %Y %H:%i:%s') as 'Started On', date_format(t.enddate,'%M %d %Y %H:%i:%s') as 'Ended On', (CHAR_LENGTH(t.listids) - CHAR_LENGTH(REPLACE(t.listids, ',', '')) + 1) as 'Targets'", "t.clientid = u.userid and t.groupid = g.groupid and t.templateid = tp.templateid and u.userid = ".trim($UINFO['userid'])."", " order by t.startdate desc, t.enddate desc");
					if(sizeof($MDATA)<=0 || $MDATA==false){
						$TBL = '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>No record found as per the criteria defined.</p></div></div>';
						return $TBL;
					}
					/*$SDATA=$this->showPageSearch(trim($TYP),$MDATA);
						$TBL = $this->pgSrch;
						$PDATA=$this->paginate($SDATA);$this->PGS.'</div>*/
					$TBL .= '<form id="frmTools" method="post" action="tests"><table width="100%" id="repTable" class="table table-striped" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%">#</th><th width="15%">Client Name</th><th width="15%">Group Name</th><th width="10%">Domain</th><th width="15%">Template</th><th width="8%">Status</th><th width="10%">Created On</th><th width="10%">Started On</th><th width="10%">Ended On</th><th width="5%">#Targets</th></tr></thead><tbody>';
					foreach($MDATA as $rwx){
						$TBL .= '<tr><td>'.((trim($rwx['Status']) =='SCHEDULED')?'<div class="check_design"><div class="square"></div></div><input type="checkbox" name="chkIDS[]" class="chkIDS" value="'.$this->encrypt(trim($rwx['testid'])).'"/>':'&nbsp;').'</td><td><a href="view/tests/'.$this->encrypt(trim($rwx['testid'])).'">'.trim($rwx['Client Name']).'</a></td><td>'.trim($rwx['Group Name']).'</td><td>'.trim($rwx['Domain']).'</td><td>'.trim($rwx['Template']).'</td><td>'.trim($rwx['Status']).'</td><td>'.trim($rwx['Created On']).'</td><td>'.trim($rwx['Started On']).'</td><td>'.trim($rwx['Ended On']).'</td><td><a class="openRecord" page-title="Targets" href="'.DROOT.XROOT.'showData?typ=TEST_TARGETS&dts=&RID='.((trim($rwx['testid'])!='')?$this->encrypt(trim($rwx['testid'])):'').'">'.trim($rwx['Targets']).'</a></td></tr>';
					}
					$TBL .= '</body></table><input type="hidden" id="hdnRID" name="hdnRID" /><input type="hidden" id="hdnMode" name="hdnMode" /></form><div class="pager">'.$this->PGS.'</div>';
				}
			break;
		}
		return $TBL;
	} 
	
	
	public function showInformation($TYP, $RID, $PGS=true){
		$TBL='';
		//var_dump($TYP,$RID);
		switch(trim($TYP)){
			case "USERS":
				$MDATA=$this->getDataArray("t_pt_users", "userid, parentid, emailid, fullname, role, phone, orgname, isdefault, domains, authname,authemail, status, date_format(createdon,'%b %d %Y') as 'createdon'", "userid='".$RID."'", "");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$PID = trim($rwx['parentid']);
					$XDATA = (trim($PID)!='') ? $this->getDataArray("t_pt_users", "emailid, fullname", "userid='".trim($PID)."'", "") : array();
					//var_dump($XDATA);
					$TBL .= '<tr><td>Email Address:</td><td>'.trim($rwx['emailid']).'</td></tr>';
					$TBL .= '<tr><td>Full Name:</td><td>'.trim($rwx['fullname']).'</td></tr>';
					$TBL .= '<tr><td>Contact Phone:</td><td>'.trim($rwx['phone']).'</td></tr>';
					$TBL .= '<tr><td>Organization:</td><td>'.trim($rwx['orgname']).'</td></tr>';
					$TBL .= '<tr><td>Role:</td><td>'.trim($rwx['role']).'</td></tr>';
					$TBL .= (sizeof($XDATA)>0) ? '<tr><td>Member of:</td><td>'.trim($XDATA[0]['fullname']).' ['.trim($XDATA[0]['emailid']).']</td></tr>' : '';
					$TBL .= '<tr><td>Member Since:</td><td>'.trim($rwx['createdon']).'</td></tr>';
					$TBL .= (trim($rwx['role'])=='MANAGER')?'<tr><td>Default Manager?:</td><td>'.trim($rwx['isdefault']).'</td></tr>':'';
					$TBL .= (trim($rwx['role'])=='CLIENT')?'<tr><td>Authorizer:</td><td>'.trim($rwx['authname']).' ['.trim($rwx['authemail']).']</td></tr>':'';
				}
				$TBL .= '</body></table>';
			break;
			case "SETTINGS":
				$MDATA=$this->getDataArray("t_pt_settings", "settingid, clientid, params, status, date_format(createdon,'%b %d %Y') as 'createdon'", "settingid='".$RID."'", "");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$CID = trim($rwx['clientid']);
					$XDATA = (trim($CID)!='') ? $this->getDataArray("t_pt_users", "emailid, fullname", "userid='".trim($CID)."'", "") : array();
					$TBL .= '<tr><td>Client:</td><td>'.trim($XDATA[0]['fullname']).' - '.trim($XDATA[0]['fullname']).'</td></tr>';
					$PARAMS=array();
					if(trim($rwx['params'])!=''){
						$PARAMS=json_decode(trim(str_replace('&quot;','"',$rwx['params'])));
					}
					//print_r($PARAMS);
					$TBL .= '<tr><td>Parameters:</td><td>';
					foreach ($PARAMS as $VLS){
						foreach ($VLS as $k=>$v){
							$TBL .= $k.' = '.$v.'<br/>';
						}
					}
					$TBL .= '</td></tr>';
					$TBL .= '<tr><td>Status:</td><td>'.trim($rwx['status']).'</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			case "MAILING-LISTS":
				$MDATA=$this->getDataArray("t_pt_mailinglist m", "distinct m.listid, m.fullname, m.emailid, (select g.groupname from t_pt_groups g where g.groupid=m.groupid) as gname, (select concat_ws(' - ',u.fullname,u.emailid) from t_pt_users u where m.clientid=u.userid) as uname, date_format(m.createdon,'%b %d %Y') as 'createdon'", "listid='".$RID."'", "");
				//print_r($MDATA);
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= '<tr><td>Group Name:</td><td>'.trim($rwx['gname']).'</td></tr>';
					$TBL .= '<tr><td>Client:</td><td>'.trim($rwx['uname']).'</td></tr>';
					$TBL .= '<tr><td>Full Name:</td><td>'.trim($rwx['fullname']).'</td></tr>';
					$TBL .= '<tr><td>Email Address:</td><td>'.trim($rwx['emailid']).'</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			case "EMAIL-TEMPLATES":
				$MDATA=$this->getDataArray("t_pt_templates t", "distinct t.templateid, (select concat_ws('<br/>',fullname,emailid) from t_pt_users u where t.clientid=u.userid) as client, t.templatetype, t.templatename, t.subject, t.body, t.pagename, t.pageurl, t.logo, t.status, date_format(t.createdon,'%b %d %Y') as 'createdon'", "t.status!='DELETED' and templateid = '".$RID."'", "order by t.templateid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= (trim($rwx['client'])!='') ? '<tr><td>Client Name:</td><td>'.trim($rwx['client']).'</td></tr>':'';
					$TBL .= '<tr><td width="18%">Template Type:</td><td>'.trim($rwx['templatetype']).'</td></tr>';
					$TBL .= '<tr><td>Template Name:</td><td>'.trim($rwx['templatename']).'</td></tr>';
					$TBL .= '<tr><td>Subject:</td><td>'.trim($rwx['subject']).'</td></tr>';
					$TBL .= '<tr><td>Email Body:</td><td>'.html_entity_decode(trim($rwx['body'])).'</td></tr>';
					//$TBL .= (trim($rwx['templatetype']) == 'WEBFORM') ? '<tr><td>Form Fields:</td><td>'.$this->processFormFields(trim($rwx['formfields']), false).'</td></tr>' : '';
					$TBL .= '<tr><td>Page:</td><td>'.trim($rwx['pagename']).' - '.trim($rwx['pageurl']).'</td></tr>';
					$TBL .= (trim($rwx['logo'])!='') ?'<tr><td>Logo:</td><td><img src="'.$this->getServerPath().'../public/uploads/logos/'.trim($rwx['logo']).'" alt="Logo"/></td></tr>':'';
					$TBL .= '<tr><td>Status:</td><td>'.trim($rwx['status']).'</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			case "TARGETS":
				$MDATA=$this->getDataArray("t_pt_mailinglist m", "distinct m.listid, m.fullname, m.emailid, (select g.groupname from t_pt_groups g where g.groupid=m.groupid) as gname, (select concat_ws(' - ',u.fullname,u.emailid) from t_pt_users u where m.clientid=u.userid) as uname, date_format(m.createdon,'%b %d %Y') as 'createdon'", "listid='".$RID."'", "");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= '<tr><td>Group Name:</td><td>'.trim($rwx['gname']).'</td></tr>';
					$TBL .= '<tr><td>Client:</td><td>'.trim($rwx['uname']).'</td></tr>';
					$TBL .= '<tr><td>Full Name:</td><td>'.trim($rwx['fullname']).'</td></tr>';
					$TBL .= '<tr><td>Email Address:</td><td>'.trim($rwx['emailid']).'</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			case "CMSS":
				$MDATA=$this->getDataArray("t_pt_cms", "cmsid, type, page, url_alias, heading, contents, user, date_format(createdon,'%b %d %Y') as 'createdon'", "status!='DELETED'", "order by type, cmsid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= '<tr><td width="120px">Page Type:</td><td>'.trim($rwx['type']).'</td></tr>';
					$TBL .= '<tr><td>Page Name:</td><td>'.trim($rwx['page']).'</td></tr>';
					$TBL .= '<tr><td>Page Alias:</td><td>'.trim($rwx['url_alias']).'</td></tr>';
					$TBL .= '<tr><td>Heading:</td><td>'.trim($rwx['heading']).'</td></tr>';
					$TBL .= '<tr><td>Content:</td><td>'.nl2br(html_entity_decode(trim($rwx['contents']))).'</td></tr>';
					$TBL .= '<tr><td>Intended for:</td><td>'.nl2br(trim($rwx['user'])).' USER</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.nl2br(trim($rwx['createdon'])).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			case "TESTS":
				$MDATA=$this->getDataArray("t_pt_groups g, t_pt_users u, t_pt_test t, t_pt_templates tp", "t.testid as 'testid', concat(concat_ws(' - ',u.fullname,u.emailid),' <b>[',u.orgname,']</b>') as 'client', concat_ws(' - ', g.groupname, g.domain) as 'group', concat('<b>',tp.templatetype,'</b> - ', tp.templatename, '<br/><b>Subject: </b>', tp.subject, '<br/> <p>', tp.body,'</p>') as 'template', (case when t.process='TESTRUN' then 'SCHEDULED' else t.process end) as 'status', t.fromname, t.fromemail, date_format(t.createdon,'%M %d %Y %H:%i:%s') as 'createdon', date_format(t.startdate,'%M %d %Y %H:%i:%s') as 'startedon', date_format(t.enddate,'%M %d %Y %H:%i:%s') as 'endedon', (CHAR_LENGTH(t.listids) - CHAR_LENGTH(REPLACE(t.listids, ',', '')) + 1) as 'targets'", "t.clientid = u.userid and t.groupid = g.groupid and t.templateid = tp.templateid and t.testid = ".trim($RID), "");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= '<tr><td width="120px">Client:</td><td>'.trim($rwx['client']).'</td></tr>';
					$TBL .= '<tr><td>Group Name:</td><td>'.trim($rwx['group']).'</td></tr>';
					$TBL .= '<tr><td>Template:</td><td>'.html_entity_decode(trim($rwx['template'])).'</td></tr>';
					$TBL .= '<tr><td>From Name:</td><td>'.trim($rwx['fromname']).'</td></tr>';
					$TBL .= '<tr><td>From Email:</td><td>'.trim($rwx['fromemail']).'</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.trim($rwx['createdon']).'</td></tr>';
					$TBL .= '<tr><td><b>Status:</b></td><td><b>'.trim($rwx['status']).'</b></td></tr>';
					$TBL .= '<tr><td>Started On:</td><td>'.trim($rwx['startedon']).'</td></tr>';
					$TBL .= '<tr><td>Ended On:</td><td>'.trim($rwx['endedon']).'</td></tr>';
					$TBL .= '<tr><td>#Targets:</td><td>'.trim($rwx['targets']).'</td></tr>';
				}
				$TBL .= '</body></table>';
				
				$MDATAR = $this->getDataArray("t_pt_testrun tr", "tr.fullname, tr.emailid, tr.contactphone, date_format(tr.rundate,'%M %d %Y %H:%i:%s') as 'rundate', date_format(tr.mailsenton,'%M %d %Y %H:%i:%s') as 'mailsenton', tr.activity, tr.whatdid, tr.remote_ip, date_format(tr.activitydate,'%M %d %Y %H:%i:%s') as 'activitydate'", "tr.testid = ".trim($RID), "");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL .= '<table width="100%" id="infTargetsTable" class="table table-striped" cellpadding="0" cellspacing="0"><thead>';
				$TBL .= '<tr><th width="120px">Full Name</th><th>Email Id</th><th>Contact Phone</th><th>Activity</th>';
				$TBL .= '<th>What Did</th><th>Remote IP</th><th>Activity Date</th><th>Run Date</th><th>Mail Sent On</th></tr></thead><tbody>';
				foreach($MDATAR as $rwxr){
					$TBL .= '<tr><td>'.trim($rwxr['fullname']).'</td>';
					$TBL .= '<td>'.trim($rwxr['emailid']).'</td>';
					$TBL .= '<td>'.trim($rwxr['contactphone']).'</td>';
					$TBL .= '<td>'.trim($rwxr['activity']).'</td>';
					$TBL .= '<td>'.trim($rwxr['whatdid']).'</td>';
					$TBL .= '<td>'.trim($rwxr['remote_ip']).'</td>';
					$TBL .= '<td>'.trim($rwxr['activitydate']).'</td>';
					$TBL .= '<td>'.trim($rwxr['rundate']).'</td>';
					$TBL .= '<td>'.trim($rwxr['mailsenton']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			default:
				$this->getError('main', 'The resource you are trying to reach does not exists.');
				return false;
				break;
		}
		return $TBL;
	}
	
	public function processFormFields($formFields, $out = false){
		$STRG = '';
		$KEYS = array('label', 'type', 'name', 'valid', 'req', 'maxlength');
		$RES = array();
		if(trim($formFields) != ''){
			if(!preg_match("/;/is", $formFields, $matched)){
				$STRG = '';
				return $SRTG;
			}
			
			$arr = explode(";", $formFields);
			foreach ($arr as $fields){
				if(trim($fields) != ''){
					$values = explode(",", $fields);
					$RES[] = array_combine($KEYS, $values);
				}
			}
// 			$STRG = json_encode($RES);
			if($out == false){
				foreach ($RES as $fields){
					foreach ($fields as $K => $V){
						if($K == 'label'){
							$STRG .= '<label for="'.$fields['name'].'">'.$V.'</label>';
						} elseif($K == 'type')
							$STRG .= '<input '.$K.'="'.$V.'" id="'.$fields['name'].'"';
						else
							$STRG .= ' '.$K.'="'.$V.'"';
					}
					$STRG .= '/>'."\n";
				}
				return nl2br(htmlentities($STRG));
			} else{
				foreach ($RES as $fields){
					$STRG .= '<div class="frm">';
					foreach ($fields as $K => $V){
						if($K == 'label'){
							$STRG .= '<div class="lbl"><label for="'.$fields['name'].'">'.$V.((trim($fields['req']) == 'true') ? '<span>*</span>' : '').'</label></div>';
						} elseif($K == 'type')
						$STRG .= '<div class="txt"><input '.$K.'="'.$V.'" id="'.$fields['name'].'"';
						else
							$STRG .= ' '.$K.'="'.$V.'"';
					}
					$STRG .= '/><div class="erdx"></div></div></div>';
				}
				return $STRG;
			}
		}
	}
	
	public function showDashboardInfo($TYP, $role, $PGS=true){
		$TBL='';
// 		var_dump($TYP,$RID);
		switch(trim($TYP)){
			case "TOTAL-CLIENTS":
				$MDATA=$this->getDataArray("t_pt_users", "userid, parentid, emailid, fullname, role, phone, orgname, isdefault, status, date_format(createdon,'%b %d %Y') as 'createdon'", "role='".$role."'", "");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$PID = trim($rwx['parentid']);
					$XDATA = (trim($PID)!='') ? $this->getDataArray("t_pt_users", "emailid, fullname", "userid='".trim($PID)."'", "") : array();
					//var_dump($XDATA);
					$TBL .= '<tr><td>Email Address:</td><td>'.trim($rwx['emailid']).'</td></tr>';
					$TBL .= '<tr><td>Full Name:</td><td>'.trim($rwx['fullname']).'</td></tr>';
					$TBL .= '<tr><td>Contact Phone:</td><td>'.trim($rwx['phone']).'</td></tr>';
					$TBL .= '<tr><td>Organization:</td><td>'.trim($rwx['orgname']).'</td></tr>';
					$TBL .= '<tr><td>Role:</td><td>'.trim($rwx['role']).'</td></tr>';
					$TBL .= (sizeof($XDATA)>0) ? '<tr><td>Member of:</td><td>'.trim($XDATA[0]['fullname']).' ['.trim($XDATA[0]['emailid']).']</td></tr>' : '';
					$TBL .= '<tr><td>Member Since:</td><td>'.trim($rwx['createdon']).'</td></tr>';
					$TBL .= '<tr><td>Default User?:</td><td>'.trim($rwx['isdefault']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			case "ACTIVE-CLIENTS":
				$MDATA=$this->getDataArray("t_pt_settings", "settingid, clientid, params, status, date_format(createdon,'%b %d %Y') as 'createdon'", "settingid='".$RID."'", "");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$CID = trim($rwx['clientid']);
					$XDATA = (trim($CID)!='') ? $this->getDataArray("t_pt_users", "emailid, fullname", "userid='".trim($CID)."'", "") : array();
					$TBL .= '<tr><td>Client:</td><td>'.trim($XDATA[0]['fullname']).' - '.trim($XDATA[0]['fullname']).'</td></tr>';
					$PARAMS=array();
					if(trim($rwx['params'])!=''){
						$PARAMS=json_decode(trim(str_replace('&quot;','"',$rwx['params'])));
					}
					//print_r($PARAMS);
					$TBL .= '<tr><td>Parameters:</td><td>';
					foreach ($PARAMS as $VLS){
						foreach ($VLS as $k=>$v){
							$TBL .= $k.' = '.$v.'<br/>';
						}
					}
					$TBL .= '</td></tr>';
					$TBL .= '<tr><td>Status:</td><td>'.trim($rwx['status']).'</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			case "TOTAL-TARGETS":
				$MDATA=$this->getDataArray("t_pt_mailinglist m", "distinct m.listid, m.fullname, m.emailid, (select g.groupname from t_pt_groups g where g.groupid=m.groupid) as gname, (select concat_ws(' - ',u.fullname,u.emailid) from t_pt_users u where m.clientid=u.userid) as uname, date_format(m.createdon,'%b %d %Y') as 'createdon'", "listid='".$RID."'", "");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= '<tr><td>Group Name:</td><td>'.trim($rwx['gname']).'</td></tr>';
					$TBL .= '<tr><td>Client:</td><td>'.trim($rwx['uname']).'</td></tr>';
					$TBL .= '<tr><td>Full Name:</td><td>'.trim($rwx['fullname']).'</td></tr>';
					$TBL .= '<tr><td>Email Address:</td><td>'.trim($rwx['emailid']).'</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
			case "TOTAL-TESTS":
				$MDATA=$this->getDataArray("t_pt_templates t", "distinct t.templateid, (select concat_ws('<br/>',fullname,emailid) from t_pt_users u where t.clientid=u.userid) as client, t.templatetype, t.templatename, t.subject, t.body, t.status, date_format(t.createdon,'%b %d %Y') as 'createdon'", "t.status!='DELETED'", "order by t.templateid");
				if(sizeof($MDATA)<=0 || $MDATA==false){
					$this->getError('main', 'No record found as per the criteria defined.');
					return false;
				}
				$TBL = '<table width="100%" id="infTable" class="table table-striped" cellpadding="0" cellspacing="0"><tbody>';
				foreach($MDATA as $rwx){
					$TBL .= (trim($rwx['client'])!='') ? '<tr><td>Client Name:</td><td>'.trim($rwx['client']).'</td></tr>':'';
					$TBL .= '<tr><td width="18%">Template Type:</td><td>'.trim($rwx['templatetype']).'</td></tr>';
					$TBL .= '<tr><td>Template Name:</td><td>'.trim($rwx['templatename']).'</td></tr>';
					$TBL .= '<tr><td>Subject:</td><td>'.trim($rwx['subject']).'</td></tr>';
					$TBL .= '<tr><td>Email Body:</td><td>'.html_entity_decode(trim($rwx['body'])).'</td></tr>';
					$TBL .= '<tr><td>Status:</td><td>'.trim($rwx['status']).'</td></tr>';
					$TBL .= '<tr><td>Created On:</td><td>'.trim($rwx['createdon']).'</td></tr>';
				}
				$TBL .= '</body></table>';
			break;
		}
		return $TBL;
	}
	
	public function showDashbordInformation($TEST_TYP,$UINFO,$DTS){
		$TBL = '';
		$WHR = '';
		$WHX='';
		$UROLE=trim($UINFO['role']);
		$DTX = (trim($DTS)!='') ? explode(':',$DTS) : array();
		foreach ($TEST_TYP as $TYP)
		{
			$STYP .= (trim($STYP)=="")?"'".trim($TYP)."'":",'".trim($TYP)."'";
		}
		if(sizeof($DTX)>0){
			$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <='".trim($DTX[1])."'";
		}
		if(trim($UINFO['role'])=='CLIENT'){
			$FDT=$this->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid", "r.runid, r.fullname, r.emailid, date_format(r.rundate,'%M %d %Y') as rundate, r.activity, date_format(r.activitydate,'%M %d %Y') as statusdate", "t.clientid='".trim($UINFO['userid'])."' and r.status in (".trim($STYP).")".$WHX, "order by r.fullname");
		}else{
			$WHR = (trim($UROLE)=="MANAGER") ? " and t.clientid in (".$this->getClientIDforManager($UINFO).")": "";
			$FDT=$this->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid", "r.runid, r.fullname, r.emailid, date_format(r.rundate,'%M %d %Y') as rundate, r.activity, date_format(r.activitydate,'%M %d %Y') as statusdate", "r.status in (".trim($STYP).")".$WHR.$WHX, "order by r.fullname");
		}
		$TBL .= '<div class="frm">';
		if(sizeof($FDT)<=0){
			$TBL .= '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>No record found as per the criteria defined.</p></div></div>';
		}else{
			$TBL .= '<div class="grid"><table width="100%" id="repTable" class="tablesorter" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%">#</th><th width="25%">Full Name</th><th width="25%">Email Address</th><th width="18%">Run Date</th><th width="10%">Result</th><th width="20%">Result Date</th></tr></thead><tbody>';
			$RW=0;
			foreach($FDT as $rwx){
				$RW++;
				$TBL .= '<tr><td>'.($RW).'</td><td>'.trim($rwx['fullname']).'</td><td>'.trim($rwx['emailid']).'</td><td>'.trim($rwx['rundate']).'</td><td>'.trim($rwx['activity']).'</td><td>'.trim($rwx['statusdate']).'</td></tr>';
			}
			$TBL .= '</tbody></table></div>';
		}
		$TBL .= '</div>';
		return $TBL;
	}
	
	public function showDashboardOtherInfo($UINFO,$DTS,$CID="",$PGT=""){
		$WHR='';
		$WHX='';
		$UROLE=trim($UINFO['role']);
		$DTX = (trim($DTS)!='') ? explode(':',$DTS) : array();
		$TBL='<div class="frm"><table width="100%" id="dtlTable" class="tablesorter" cellpadding="0" cellspacing="0"><tbody>';
		if(trim($UINFO['role'])!='CLIENT'){
			if(trim($CID)==""){
				$WHR = (trim($CID)!=false) ? " and userid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and userid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$DTA=$this->getDataArray("t_pt_users","count(*) as 'tot'","role='CLIENT'".$WHR.$WHX,"");
				if(sizeof($DTA)>0){
					$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=TOTAL-CLIENTS&dts='.$DTS.'&pdf=true&pg='.$this->encrypt('Total Clients').'" page-title="Total Clients">Total Clients</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'</span></td></tr>';
				}
				if(sizeof($DTX)>0){
					$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$DTA=$this->getDataArray("t_pt_users","count(*) as 'tot'","role='CLIENT' and status='ACTIVE'".$WHR.$WHX,"");
				if(sizeof($DTA)>0){
					$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=ACTIVE-CLIENTS&dts='.$DTS.'&pdf=true&pg='.$this->encrypt('Active Clients').'" page-title="Active Clients">Active Clients</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'</span></td></tr>';
				}
				if(sizeof($DTX)>0){
					$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$DTA=$this->getDataArray("t_pt_users","count(*) as 'tot'","role='CLIENT' and status!='ACTIVE'".$WHR.$WHX,"");
				if(sizeof($DTA)>0){
					$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=INACTIVE-CLIENTS&dts='.$DTS.'&pdf=true&pg='.$this->encrypt('Inactive Clients').'" page-title="Inactive Clients">Inactive Clients</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'</span></td></tr>';
				}
			}
			/*$WHR = (trim($CID)!=false) ? " and clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and clientid in (".$this->getClientIDforManager($UINFO).")": "");
			if(sizeof($DTX)>0){
				$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
			}
			$DTA=$this->getDataArray("t_pt_groups","count(*) as 'tot'","status='ACTIVE'".$WHR.$WHX,"");
			if(sizeof($DTA)>0){
				$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=ACTIVE-GROUPS&dts='.$DTS.'&pdf=true&pg='.$this->encrypt('Active Groups').'" page-title="Active Groups">Active Groups</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'<span></td></tr>';
			}*/
			$WHR = (trim($CID)!=false) ? " and clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and clientid in (".$this->getClientIDforManager($UINFO).")": "");
			if(sizeof($DTX)>0){
				$WHX = " and date_format(m.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(m.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
			}
			$DTA=$this->getDataArray("t_pt_mailinglist m, t_pt_users u","count(*) as 'tot'","m.clientid=u.userid and u.status='ACTIVE' and m.status='ACTIVE'".$WHR.$WHX,"");
			if(sizeof($DTA)>0){
				$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=TOTAL-TARGETS&dts='.$DTS.'&pdf=true&pg='.$this->encrypt('Total Targets').'" page-title="Total Targets">Total Targets</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'</span></td></tr>';
			}
			$WHR = (trim($CID)!=false) ? " and t.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and t.clientid in (".$this->getClientIDforManager($UINFO).")": "");
			if(sizeof($DTX)>0){
				$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
			}
			$DTA=$this->getDataArray("t_pt_test t, t_pt_users u","count(*) as 'tot'","t.clientid=u.userid and u.status='ACTIVE'".$WHR.$WHX,"");
			if(sizeof($DTA)>0){
				$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=TOTAL_TEST&dts='.$DTS.'&pdf=true&pg='.$this->encrypt('Total Campaigns').'" page-title="Total Campaigns">Total Campaigns</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'</span></td></tr>';
			}	
		}elseif(trim($UINFO['role'])=='CLIENT'){
			if(sizeof($DTX)>0){
				$WHX = " and date_format(g.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(g.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
			}
			$DTA=$this->getDataArray("t_pt_groups g, t_pt_users u","count(*) as 'tot'","g.clientid=u.userid and u.role='CLIENT' and u.status='ACTIVE' and g.status='ACTIVE' and u.userid='".trim($UINFO['userid'])."'".$WHX,"");
			if(sizeof($DTA)>0){
				$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=ACTIVE-GROUPS&dts='.$DTS.'&CID='.$CID.'&pdf=true&pg='.$this->encrypt('Active Groups').'" page-title="Active Groups">Active Groups</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'</span></td></tr>';
			}
			if(sizeof($DTX)>0){
				$WHX = " and date_format(m.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(m.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
			}
			$DTA=$this->getDataArray("t_pt_mailinglist m, t_pt_users u","count(*) as 'tot'","m.clientid=u.userid and u.role='CLIENT' and u.status='ACTIVE' and m.status='ACTIVE' and u.userid='".trim($UINFO['userid'])."'".$WHX,"group by u.userid");
			if(sizeof($DTA)>0){
				$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=TOTAL-TARGETS&dts='.$DTS.'&CID='.$CID.'&pdf=true&pg='.$this->encrypt('Total Targets').'" page-title="Total Targets">Total Targets</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'</span></td></tr>';
			}
			if(sizeof($DTX)>0){
				$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
			}
			$DTA=$this->getDataArray("t_pt_test t, t_pt_users u","count(*) as 'tot'","t.clientid=u.userid and u.role='CLIENT' and u.status='ACTIVE'  and t.clientid='".trim($UINFO['userid'])."'".$WHX,"");
			if(sizeof($DTA)>0){
				$TBL .= '<tr><td><a class="openRecord" href="'.DROOT.XROOT.'showData?typ=TOTAL_TEST&dts='.$DTS.'&CID='.$CID.'&pdf=true&pg='.$this->encrypt('Total Campaigns').'" page-title="Total Campaigns">Total Campaigns</a></td><td><span class="number_cont">'.trim($DTA[0]['tot']).'</span></td></tr>';
			}
		}
		$TBL .= '</tbody></table>';
		$TBL .= '<div id="cliDialog" title="Total Clients" style="display: none;">';
		$TBL .= '<p>'.$this->showDashboardInfo("OVERALL_ANALYSIS",$UINFO,$DTS).'</p></div></div>';
		return $TBL;
	}
	
	protected function showTestResult($TEST_TYP, $RTYPE, $UINFO, $DTS, $CID="", $pdf=false,  $STATUS = "", $GRPDMN = "", $PGT="",$CHT=false){
		$TBL = '';
		$WHR = '';
		$WHX='';
		$STYP='';
		$TTL='';
		$COLW=array();
		$UROLE=trim($UINFO['role']);
		$DTX = (trim($DTS)!='') ? explode(':',$DTS) : array();
		foreach ($TEST_TYP as $TYP)
		{
			$STYP .= (trim($STYP)=="")?"'".trim($TYP)."'":",'".trim($TYP)."'";
		}
		if(sizeof($DTX)>0){
			$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		//if(trim($UROLE)=='CLIENT' && $CID=='') $CID=trim($UINFO['userid']);
 		//var_dump($STATUS,$GRPDMN);
		switch(trim($RTYPE)){
			case "OVERALL_ANALYSIS":
				$FDT=array();
				$COLW = array(70,28);
				if(trim($CID)==""){
					$WHR = (trim($CID)!="") ? " and userid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and userid in (".$this->getClientIDforManager($UINFO).")": "");
					if(sizeof($DTX)>0){
						$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
					}
					$DTA=$this->getDataArray("t_pt_users","count(*) as 'tot'","role='CLIENT'".$WHR.$WHX,"");
					if(sizeof($FDT)<=0) array_push($FDT,array('Description'=>'<a class="openRecord" href="'.DROOT.XROOT.'showData?typ=TOTAL-CLIENTS&dts='.$DTS.'" page-title="Total Clients">Total Clients</a>','Value'=>trim($DTA[0]['tot'])));
					if(sizeof($DTX)>0){
						$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
					}
					$DTA=$this->getDataArray("t_pt_users","count(*) as 'tot'","role='CLIENT' and status='ACTIVE'".$WHR.$WHX,"");
					array_push($FDT,array('Description'=>'<a class="openRecord" href="'.DROOT.XROOT.'showData?typ=ACTIVE-CLIENTS&dts='.$DTS.'" page-title="Active Clients">Active Clients</a>','Value'=>trim($DTA[0]['tot'])));
					if(sizeof($DTX)>0){
						$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
					}
					$DTA=$this->getDataArray("t_pt_users","count(*) as 'tot'","role='CLIENT' and status!='ACTIVE'".$WHR.$WHX,"");
					array_push($FDT,array('Description'=>'<a class="openRecord" href="'.DROOT.XROOT.'showData?typ=INACTIVE-CLIENTS&dts='.$DTS.'" page-title="Inactive Clients">Inactive Clients<a>','Value'=>trim($DTA[0]['tot'])));
				}
				/*$WHR = (trim($CID)!="") ? " and clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$DTA=$this->getDataArray("t_pt_groups","count(*) as 'tot'","status='ACTIVE'".$WHR.$WHX,"");
				array_push($FDT,array('Description'=>'<a class="openRecord" href="'.DROOT.XROOT.'showData?typ=ACTIVE-GROUPS&dts='.$DTS.'" page-title="Active Groups">Active Groups</a>','Value'=>trim($DTA[0]['tot'])));*/
				
				$WHR = (trim($CID)!="") ? " and clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(m.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(m.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$DTA=$this->getDataArray("t_pt_mailinglist m, t_pt_users u","count(*) as 'tot'","m.clientid=u.userid and u.status='ACTIVE' and m.status='ACTIVE'".$WHR.$WHX,"");
				array_push($FDT,array('Description'=>'<a class="openRecord" href="'.DROOT.XROOT.'showData?typ=TOTAL-TARGETS&dts='.$DTS.'" page-title="Total Targets">Total Targets</a>','Value'=>trim($DTA[0]['tot'])));
				$WHR = (trim($CID)!="") ? " and t.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and t.clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$DTA=$this->getDataArray("t_pt_test t, t_pt_users u","count(*) as 'tot'","t.clientid=u.userid and u.status='ACTIVE'".$WHR.$WHX,"");
				array_push($FDT,array('Description'=>'<a class="openRecord" href="'.DROOT.XROOT.'showData?typ=TOTAL-TESTS&dts='.$DTS.'" page-title="Total Campaigns">Total Campaigns</a>','Value'=>trim($DTA[0]['tot'])));
			break;
			case "TOTAL_TEST":
				$WHR = (trim($CID)!="") ? " and ts.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and ts.clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(ts.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(ts.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$FDT=$this->getDataArray("t_pt_test ts", "case when ts.process='TESTRUN' then 'SCHEDULED' else ts.process end as 'Status', count(*) as 'Total'", "ts.process in ('TESTRUN', 'CANCELLED', 'RUNNING', 'COMPLETED')".$WHR.$WHX, "group by ts.process");
				$COLW=array(70,28);
				$anc = array('Status' => array('pg'=>'Campaigns','typ'=>'TEST_STATUS','col'=>'Status'));
			break;	
			case "TEST_ANALYSIS":
				$WHR = (trim($STATUS)!="" && trim($STATUS)!='UNKNOWN') ? " and r.activity='".trim($STATUS)."'" : ((trim($STATUS)=="UNKNOWN") ? " and r.activity is null" :"" );
				$WHR .= (trim($CID)!="") ? " and t.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and clientid in (".$this->getClientIDforManager($UINFO).")": "");
				
				if(sizeof($DTX)>0){
					$WHX = " and date_format(r.rundate,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(r.rundate,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				if(trim($STATUS) == 'FAILED'){
					$FDT=$this->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid", "r.fullname as 'Full Name', r.emailid as 'Email Address', date_format(r.rundate,'%M %d %Y') as 'Run Date', r.activity as 'Result', CASE WHEN r.whatdid is not null then 'Y' else '' end as 'A', CASE WHEN r.whatdid = 'Link Clicked' THEN 'Y' ELSE '' END as 'B', CASE WHEN r.whatdid = 'Web Form Opened' THEN 'Y' ELSE '' END as 'C', CASE WHEN r.whatdid = 'Attachment Downloaded' THEN 'Y' ELSE '' END as 'D', r.remote_ip as 'IP Address', date_format(r.activitydate,'%M %d %Y') as 'Result Date'", "r.status in (".trim($STYP).")".$WHR.$WHX, "order by r.fullname");
					$COLW=array(19,19,15,7,3,4,3,4,9,15);
				}else{
					$FDT=$this->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid", "r.fullname as 'Full Name', r.emailid as 'Email Address', date_format(r.rundate,'%M %d %Y') as 'Run Date', r.activity as 'Result', date_format(r.activitydate,'%M %d %Y') as 'Result Date'", "r.status in (".trim($STYP).")".$WHR.$WHX, "order by r.fullname");
					$COLW=array(25,30,15,15,15);
				}
			break;
			case "DOMAIN_TEST_ANALYSIS":
				$WHR = (trim($GRPDMN)!="") ? " and SUBSTRING_INDEX(r.emailid, '@', -1)='".trim($GRPDMN)."'" : "";
				$WHR .= (trim($STATUS)!="" && trim($STATUS)!='UNKNOWN') ? " and r.activity='".trim($STATUS)."'" : ((trim($STATUS)=="UNKNOWN") ? " and r.activity is null" :"" );
				$WHR .= (trim($CID)!="") ? " and t.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and t.clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(r.rundate,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(r.rundate,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				if(trim($STATUS) == 'FAILED'){
					$FDT=$this->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid ", "r.fullname as 'Full Name', r.emailid as 'Email Address', date_format(r.rundate,'%M %d %Y') as 'Run Date', r.activity as 'Result',  CASE WHEN r.whatdid is not null then 'Y' else '' end as 'A', CASE WHEN r.whatdid = 'Link Clicked' THEN 'Y' ELSE '' END as 'B', CASE WHEN r.whatdid = 'Web Form Opened' THEN 'Y' ELSE '' END as 'C', CASE WHEN r.whatdid = 'Attachment Downloaded' THEN 'Y' ELSE '' END as 'D', r.remote_ip as 'IP Address', date_format(r.activitydate,'%M %d %Y') as 'Result Date'", "r.status in (".trim($STYP).")".$WHR.$WHX, "order by r.fullname");
					$COLW=array(15,15,18,10,7,3,4,3,4,9,12);
				}else{
					$FDT=$this->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid ", "r.fullname as 'Full Name', r.emailid as 'Email Address', date_format(r.rundate,'%M %d %Y') as 'Run Date', r.activity as 'Result', date_format(r.activitydate,'%M %d %Y') as 'Result Date'", "r.status in (".trim($STYP).")".$WHR.$WHX, "order by r.fullname");
					$COLW=array(20,20,18,10,15,15);
				}
			break;
			case "USER_EVENT_TEST_ANALYSIS":
				$WHR = (trim($STATUS)!="" && trim($STATUS)!='NOTHING') ? " and r.whatdid='".trim($STATUS)."'" : ((trim($STATUS)=="NOTHING") ? " and r.whatdid is null" :"" );
				$WHR .= (trim($CID)!="") ? " and t.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and t.clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(r.rundate,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(r.rundate,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				if(trim($STATUS) == 'FAILED' || trim($STATUS)!='NOTHING'){
					$FDT=$this->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid", "r.fullname as 'Full Name', r.emailid as 'Email Address', date_format(r.rundate,'%M %d %Y') as 'Run Date', r.activity as 'Result', CASE WHEN r.whatdid is not null then 'Y' else '' end as 'A', CASE WHEN r.whatdid = 'Link Clicked' THEN 'Y' ELSE '' END as 'B', CASE WHEN r.whatdid = 'Web Form Opened' THEN 'Y' ELSE '' END as 'C', CASE WHEN r.whatdid = 'Attachment Downloaded' THEN 'Y' ELSE '' END as 'D', r.remote_ip as 'IP Address', date_format(r.activitydate,'%M %d %Y') as 'Result Date'", "r.status in (".trim($STYP).")".$WHR.$WHX, "order by r.fullname");
					$COLW=array(19,19,15,7,3,4,3,4,9,15);
				}else{
					$FDT=$this->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid", "r.fullname as 'Full Name', r.emailid as 'Email Address', date_format(r.rundate,'%M %d %Y') as 'Run Date', r.activity as 'Result', date_format(r.activitydate,'%M %d %Y') as 'Result Date'", "r.status in (".trim($STYP).")".$WHR.$WHX, "order by r.fullname");
					$COLW=array(20,25,15,15,10,15);
				}
				break;
			case "TOTAL-CLIENTS":
				$WHR = (trim($CID)!="") ? " and userid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and userid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$FDT=$this->getDataArray("t_pt_users","fullname as 'Full Name',emailid as 'Email Id',phone,orgname as 'Organisation Name',domains","role='CLIENT'".$WHR.$WHX,"");
				$COLW=array(20,20,20,20,20);
				break;
			case "ACTIVE-CLIENTS":
				$WHR = (trim($CID)!="") ? " and userid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and userid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$FDT=$this->getDataArray("t_pt_users","fullname as 'Full Name',emailid as 'Email Id',phone,orgname as 'Organisation Name',domains","role='CLIENT' and status='ACTIVE'".$WHR.$WHX,"");
				$COLW=array(20,20,20,20,20);
			break;
			case "INACTIVE-CLIENTS":
				$WHR = (trim($CID)!="") ? " and userid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and userid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$FDT=$this->getDataArray("t_pt_users","fullname as 'Full Name',emailid as 'Email Id',phone,orgname as 'Organisation Name',domains","role='CLIENT' and status!='ACTIVE'".$WHR.$WHX,"");
				$COLW=array(20,20,20,20,20);
			break;
			case "TOTAL-TARGETS":
				$WHR = (trim($CID)!="") ? " and t.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and t.clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$FDT=$this->getDataArray("t_pt_mailinglist t, t_pt_users u","u.fullname as 'Client Name', t.fullname as 'Full Name', t.emailid as 'Email Id',t.contactphone as 'Contact Phone'","t.status='ACTIVE' and t.clientid = u.userid".$WHR.$WHX,"");
				$COLW=array(20,20,20,20,20);
			break;
			case "TOTAL-TESTS":
				$WHR = (trim($CID)!="") ? " and t.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and t.clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$FDT=$this->getDataArray("t_pt_test t, t_pt_users u, t_pt_templates tp","testid as id, fullname as 'client name', date_format(t.createdon, '%M %d %Y %H:%i:%s') as 'Created On', date_format(t.startdate, '%M %d %Y %H:%i:%s') as 'Started On', date_format(t.enddate, '%M %d %Y %H:%i:%s') as 'Ended On', tp.templatename as 'Template Name', (CHAR_LENGTH(t.listids) - CHAR_LENGTH(REPLACE(t.listids, ',', '')) + 1) as '#Targets'","t.clientid=u.userid and u.status='ACTIVE' and tp.templateid = t.templateid".$WHR.$WHX,"");
				$COLW=array(20,15,15,15,15,10,10);
				$anc = array('#Targets'=> array('pg'=>'Targets','typ'=>'TEST_TARGETS','col'=>'id'));
			break;
			case "TEST_TARGETS":
				$WHR = (trim($CID)!="") ? " and t.testid='".trim($this->decrypt($CID))."'" : "";
				if(sizeof($DTX)>0){
					$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$FDT=$this->getDataArray("t_pt_test t, t_pt_testrun r, t_pt_users u ","u.fullname as 'Client Name', r.fullname as 'Full Name', r.emailid as 'Email Id',r.contactphone as 'Contact Phone'","r.testid = t.testid and t.clientid = u.userid ".$WHR.$WHX,"");
				$COLW=array(20,20,20,20,20);
			break;
			case "TEST_STATUS":
				$WHR = (trim($CID)!="") ? " and t.clientid='".trim($CID)."'" : ((trim($UROLE)=="MANAGER") ? " and t.clientid in (".$this->getClientIDforManager($UINFO).")": "");
				if(sizeof($DTX)>0){
					$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
				}
				$STX = (trim($STATUS)!='')?$STATUS:'';
				$sts = (trim($STX) != '') ? ((trim($STX) == 'SCHEDULED') ? 'TESTRUN' : trim($STX)) : '';
				$FDT=$this->getDataArray("t_pt_test t, t_pt_users u, t_pt_templates tp ","testid as id, fullname as 'client name', date_format(t.createdon, '%M %d %Y %H:%i:%s') as 'Created On', date_format(t.startdate, '%M %d %Y %H:%i:%s') as 'Started On', date_format(t.enddate, '%M %d %Y %H:%i:%s') as 'Ended On', tp.templatename as 'Template Name', (CHAR_LENGTH(t.listids) - CHAR_LENGTH(REPLACE(t.listids, ',', '')) + 1) as '#Targets'","t.clientid=u.userid and u.status='ACTIVE' and t.process ='".trim($sts)."' and tp.templateid = t.templateid".$WHR.$WHX,"");
				$COLW=array(20,15,15,15,15,10,10);
				$anc = array('#Targets'=> array('pg'=>'Targets','typ'=>'TEST_TARGETS','col'=>'id'));
			break;
		}
		$TBL .= '<div class="frm">';
		if(sizeof($FDT)<=0 || $FDT==false){
			$TBL .= '<div class="ui-widget"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert: </strong>No record found as per the criteria defined.</p></div></div>';
		}else{
			if((trim($RTYPE) == 'USER_EVENT_TEST_ANALYSIS' && trim($STATUS)!='NOTHING') || trim($STATUS) == 'FAILED'){
				$TBL .= '<div class="tbl-hint"><div class="hint-legend">A. Email Opened</div><div class="hint-legend">B. Link Clicked</div><div class="hint-legend">C. Web Form Opened</div><div class="hint-legend">D. Attachment Downloaded</div></div>';
			}
			$TBL .= '<div class="grid">';
			$TBL .= '<table width="100%" id="repTable_'.trim($RTYPE).(($STATUS!='')?'_'.$STATUS:(($GRPDMN!='')?'_'.$GRPDMN:'')).'" class="table table-striped dataTable no-footer" cellpadding="0" cellspacing="0"><thead><tr><th class="no-sort" width="2%">#</th>';
			$cw=0;
			$KYS=array_keys($FDT[0]);
			foreach ($KYS as $KY){
				if(trim($KY) != 'id'){
					$TBL .= '<th width="'.$COLW[$cw].'%">'.trim($KY).'</th>';
					$cw += 1;
				}
			}
			
			$TBL .= '</tr></thead><tbody>';
			$RW=0;
			foreach($FDT as $KYS){
				$RW += 1;
				$TBL .= '<tr><td>'.$RW.'</td>';
				foreach($KYS as $K=>$V){
					if(trim($K) != 'id'){
						if(isset($anc) && array_key_exists(trim($K),$anc)){
							$VLX=$anc[trim($K)];
							//var_dump($VLX,$V);
							if(trim($K) == 'Status'){
								$TTL = $this->encrypt(trim($VLX['pg']).' - '.trim($V));
								$REF = 'page-title="'.trim($VLX['pg']).' - '.trim($V).'" href="'.DROOT.XROOT.'showData?typ='.$VLX['typ'].'&dts='.$DTS.'&status='.((trim($VLX['col'])!='')?$this->encrypt(trim($KYS[trim($VLX['col'])])):'').'&pdf=true&pg='.trim($TTL).'"';
							} else{
								$TTL = $this->encrypt(trim($VLX['pg']));
								$REF = 'page-title="'.trim($VLX['pg']).'" href="'.DROOT.XROOT.'showData?typ='.$VLX['typ'].'&dts='.$DTS.'&RID='.((trim($VLX['col'])!='')?$this->encrypt(trim($KYS[trim($VLX['col'])])):'').'&pdf=true&pg='.trim($TTL).'"';
							}
							$TBL .= '<td><a class="openRecord" '.trim($REF).'>'.$KYS[$K].'</a></td>';
						} elseif(strtolower(trim($K)) === 'result'){
							$TBL .= '<td class="'.$this->showStatusClass($KYS[$K]).'">'.$KYS[$K].'</td>';
						} else{
							$TBL .= '<td>'.$KYS[$K].'</td>';
						}
					}
				}
				$TBL .= '</tr>';
			}
			$TBL .= '</tbody></table></div>';
			if($pdf==true){
				$PLNK = $this->encrypt(trim($DTS.'#'.$CID.'#'.$STATUS.'#'.(($GRPDMN != 'undefined') ? $GRPDMN : '').'#'.$PGT.'#'.(($CHT==true)?'yes':'no').'#PDF'));
				$CLNK = $this->encrypt(trim($DTS.'#'.$CID.'#'.$STATUS.'#'.(($GRPDMN != 'undefined') ? $GRPDMN : '').'#'.$PGT.'#no#CSV'));
				$XLNK = $this->encrypt(trim($DTS.'#'.$CID.'#'.$STATUS.'#'.(($GRPDMN != 'undefined') ? $GRPDMN : '').'#'.$PGT.'#'.(($CHT==true)?'yes':'no').'#EXL'));
				$MLNK = $this->encrypt(trim($DTS.'#'.$CID.'#'.$STATUS.'#'.(($GRPDMN != 'undefined') ? $GRPDMN : '').'#'.$PGT.'#no#XML'));
				//$TBL .= $PLINK.' : '.$this->decrypt($PLNK);
				$TBL .= '<div class="frm"><div class="ctrls pull-right"><a id="btnRepPDF" class="btn btn-default" href="'.$this->getServerPath().'genpdf/'.$this->encrypt(trim($RTYPE)).'/'.trim($CLNK).'" target="_blank">CSV &nbsp;&#xf0f6;</a><a id="btnRepPDF" class="btn btn-default" href="'.$this->getServerPath().'genpdf/'.$this->encrypt(trim($RTYPE)).'/'.trim($XLNK).'" target="_blank">Excel &nbsp;&#xf1c3;</a><a id="btnRepPDF" class="btn btn-default" href="'.$this->getServerPath().'genpdf/'.$this->encrypt(trim($RTYPE)).'/'.trim($MLNK).'" target="_blank">XML &nbsp;&#xf1c9;</a><a id="btnRepPDF" class="btn btn-default" href="'.$this->getServerPath().'genpdf/'.$this->encrypt(trim($RTYPE)).'/'.trim($PLNK).'" target="_blank">PDF &nbsp;&#xf1c1;</a></div></div>';
			}
		}
		$TBL .= '</div>';
		return $TBL;
	}
		
	public function showDBDataInfo($TYP, $UINFO, $DTS, $CID="", $pdf=false, $STATUS="", $GRPDMN = "", $PGT="", $CHT=false){
		$STRG ='';
		$TEST_TYP = (trim($TYP)=='TOTAL_TEST') ?  array('SCHEDULED','RUNNING','COMPLETED') : array('RUNNING','COMPLETED');
		$STATUS = ($STATUS!="") ? $this->decrypt($STATUS) : '';
		$GRPDMN = ($GRPDMN!="") ? $this->decrypt($GRPDMN) : '';
		$STRG = $this->showTestResult($TEST_TYP, $TYP, $UINFO, $DTS, $CID, $pdf, $STATUS, $GRPDMN, $PGT, $CHT);
		return $STRG;
	}
	
	public function showHelpLinks($UINFO){
		$typ='';
		$STRG='';
		$X=0;
		if(trim($UINFO['role'])=="SYSADMIN" || trim($UINFO['role'])=="MANAGER") $TYP="in ('BOTH','ADMIN')"; elseif(trim($UINFO['role'])=="CLIENT") $TYP="in ('BOTH','CLIENT')"; else $TYP="in ('BOTH')";   
		$LDATA=$this->getDataArray("t_pt_cms", "type, url_alias, heading", "status!='DELETED' and user ".$TYP." and type!='OTHER'", "order by type, cmsid");
		//var_dump($LDATA);
		foreach ($LDATA as $list){
			if(trim($typ)!=trim($list['type'])){
				if($X!=0) $STRG .= '</ul>';
				$STRG ='<div class="frmname heading_cont" style="margin:0; padding:0; float:none;">'.((trim($list['type'])=="HELP")?"Topic":ucfirst(strtolower(trim($list['type'])))).'</div><ul class="hlinks">';
				$typ=trim($list['type']);
			}
			$STRG .= '<li><a href="'.DROOT.XROOT.trim($list['url_alias']).'">'.trim($list['heading']).'</a></li>';
			$X++;
		}		
		$STRG .= '</ul>';
		return $STRG;
	}
	
	public function showHelpContents($REQ,$UINFO){
		$TYP='';
		$STRG='';
		$RS=sizeof($REQ);
		if($RS>=2) $PG=trim($REQ['typ']).'/'.trim($REQ['pg']); else $PG='';
		if(trim($PG)!=''){
			if(trim($UINFO['role'])=="SYSADMIN" || trim($UINFO['role'])=="MANAGER") $TYP="in ('BOTH','ADMIN')"; elseif(trim($UINFO['role'])=="CLIENT") $TYP="in ('BOTH','CLIENT')"; else $TYP="in ('BOTH')";   
			$RDATA=$this->getDataArray("t_pt_cms", "heading, contents", "url_alias='".trim($PG)."' and status!='DELETED' and user ".$TYP, "group by type order by type, cmsid");
			if(sizeof($RDATA)>0){
				foreach($RDATA as $cnt){
					$STRG .= '<h2>'.trim($cnt['heading']).'</h2>';
					$STRG .= '<p>'.nl2br(html_entity_decode(trim($cnt['contents']))).'</p>';
				}
			}else{
				$STRG .= '<h2>ERROR: The page you have requested did not found.</h2>';
			}
		}
		return $STRG;
	}
	
	public function get_token_id()
	{
		$htm='';
		if(isset($_SESSION['token_id'])) {
			$htm='<input type="hidden" name="csrftoken" id="csrftoken" value="'.$_SESSION['token_id'].'" />';
		} else {
			$token_id = md5(uniqid(mt_rand(), true));
			$_SESSION['token_id'] = $token_id;
			$htm='<input type="hidden" name="csrftoken" id="csrftoken" value="'.$token_id.'" />';
		}
		return $htm;
	}
	
	/*public function showTestForm($TST,$UINFO){
		$STRG = '';
		switch(strtoupper(trim($TST['process']))){
			case "START":
				$STRG .= '<div class="login">
					<div class="frmhead">
						<div class="frmname">Group </div>
						<div class="indicator">* (Required)</div>
						<div class="clr"></div>
					</div>
					<div class="frm">
					<div class="lbl">
						<label for="groupid">Select Group: *</label>
					</div>
					<div class="txt">
						'.$this->showDrps("GROUP_CLIENT", "", ((isset($_POST['groupid'])!='')?trim($_POST['groupid']):''), "groupid", trim($UINFO['userid']), "width:98%;").'
						<div class="erdx">'.((isset($ERR['groupid'])!='')?trim($ERR['groupid']):'').'</div>
					</div>
				</div>
			</div>';
				break;
		}
		return $STRG;
	}*/
}