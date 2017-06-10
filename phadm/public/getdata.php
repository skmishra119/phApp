<?php
session_start();
require_once '../settings.inc.php';
global $UID;
$UID = (isset($_SESSION['AID'])!='')?$_SESSION['AID']:'';
global $USERINFO;
$USERINFO = $st->getUserInfo($UID);
$CIDS='';

$TYP = (isset($_REQUEST['tp'])!='') ? trim($_REQUEST['tp']) : '';
$VAL = (isset($_REQUEST['vl'])!='') ? trim($_REQUEST['vl']) : '';
$DrpId = (isset($_REQUEST['did'])!='') ? trim($_REQUEST['did']) : '';
$Opt='';
$DTS = (isset($_REQUEST['dt'])!='') ? trim($_REQUEST['dt']) : '';
$DTX = (trim($DTS)!='') ? explode(':',$DTS) : array();


if(trim($VAL)!='' && !is_numeric($VAL)) header('Location: error/404/'.$st->encrypt('false'));
if((count($DTX)> 0) && (trim($DTS[0])!='' || trim($DTS[1]!=''))){
	if(!$st->isDate(trim($DTX[0])) || !$st->isDate(trim($DTX[1]))) header('Location: error/404/'.$st->encrypt('false'));
}
$STRG = '{}';
$WHR='';
switch (trim($TYP))
{
	case "CLIENT_DOMAIN":
		$STRG ='';
		$CINF=$st->getUserInfo($VAL);
		$DMS=explode(',',trim($CINF['domains']));
		$STRG .= '<select class="form-control" id="'.trim($DrpId).'" name="'.trim($DrpId).'" '.((trim($Opt)!='') ? 'style="'.$Opt.'"' :'').'><option value="">Select Option</option>';
		foreach ($DMS as $DMN){
			if(trim($DMN)!=""){
				$STRG .= (trim($VAL)==trim($DMN)) ? '<option value="'.trim($DMN).'" selected>'.trim($DMN).'</option>' : '<option value="'.trim($DMN).'">'.trim($DMN).'</option>';
			}
		}
		$STRG .= '</select>';
		break;
	case "TESTS_RUNNING":
		if(sizeof($DTX)>0){
			$WHR = " and date_format(r.rundate,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(r.rundate,'%Y-%m-%d') <='".trim($DTX[1])."'";
		}
		$WHR .= (trim($VAL)!="") ? " and t.clientid='".trim($VAL)."'" : ((trim($USERINFO['role'])=="MANAGER") ? " and t.clientid in (".$st->getClientIDforManager($USERINFO).")" : "");
		$FDT=$st->getDataArray("t_pt_testrun r left join t_pt_test t on r.testid=t.testid", "r.runid, r.fullname, r.emailid, date_format(r.rundate,'%M %d %Y') as rundate, r.status, date_format(r.statusdate,'%M %d %Y') as statusdate", "r.status='RUNNING'".$WHR, "order by r.fullname");
		if(sizeof($FDT)>0){
			$STRG = json_encode($FDT[0]);
		}
		break;
	case "GROUP_CLIENT":
		if(sizeof($DTX)>0){
			$WHR = " and date_format(g.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(g.createdon,'%Y-%m-%d') <='".trim($DTX[1])."'";
		}
		$WHR .= (trim($USERINFO['role'])=="MANAGER") ? " and g.clientid in (".$st->getClientIDforManager($USERINFO).")" : "";
		$FDT=$st->getDataArray("t_pt_users u inner join t_pt_groups g on g.clientid=u.userid", "distinct u.userid, u.fullname, u.emailid", "g.groupid='".trim($VAL)."' and g.status='ACTIVE'".$WHR, "order by u.fullname");
		if(sizeof($FDT)>0){
			$STRG = json_encode($FDT[0]);
		}
		break;
	case "GROUP-MLIST":
		$VALS=array();
		$WHR = (trim($VAL)!="") ? " and g.clientid='".trim($VAL)."'" : ((trim($USERINFO['role'])=="MANAGER") ? " and g.clientid in (".$st->getClientIDforManager($USERINFO).")" : "");
		if(sizeof($DTX)>0){
			$WHR .= " and date_format(g.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(g.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$FDT=$st->getDataArray("t_pt_groups g", "distinct g.groupname as 'group', (select count(*) from t_pt_mailinglist m where m.groupid=g.groupid) as 'members'","g.status='ACTIVE'".$WHR, "order by g.groupname");
		if(sizeof($FDT)>0 && $FDT!=false){
			foreach($FDT as $FX){
				array_push($VALS,
					array(
						"name"=>$FX['group'],
						"y"=>floatval($FX['members'])
					)
				);
				//$VALS .= (trim($VALS)=='') ? '{"name"'.':"'.$FX['group'].'","y"'.':'.floatval($FX['members']).'}' : ',{"name"'.':"'.$FX['group'].'","y"'.':'.floatval($FX['members']).'}';
			}
		}
		//$VALS = (trim($VALS)!='') ? '[{"series":{"type":"pie","data":['.$VALS.']}}]' : '["series":{}]';
		$VALS = array("series"=>array(array("type"=>"pie","data"=>$VALS)));
		$STRG = json_encode($VALS);
		break;
	case "TOTAL_TEST":
		$VALS=array();
		$WHR = (trim($VAL)!="") ? " and ts.clientid='".trim($VAL)."'" : ((trim($USERINFO['role'])=="MANAGER") ? " and ts.clientid in (".$st->getClientIDforManager($USERINFO).")" : "");
		if(sizeof($DTX)>0){
			$WHR .= " and date_format(ts.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(ts.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$FDT=$st->getDataArray("t_pt_test ts", "ts.process as 'status', count(*) as 'tot'", "ts.process in ('SCHEDULED','CANCELLED','RUNNING','COMPLETED')".$WHR, "group by ts.process");
		if(sizeof($FDT)>0 && $FDT!=false){
			foreach($FDT as $FX){
				array_push($VALS,
					array(
						"name"=>$FX['status'],
						"y"=>floatval($FX['tot']),
						"typ"=>"TEST_STATUS",
						"status"=>$st->encrypt(strtoupper(trim($FX['status'])))
					)
				);
				//$VALS .= (trim($VALS)=='') ? '{"name"'.':"'.$FX['status'].'","y"'.':'.floatval($FX['tot']).',"typ":"TEST_STATUS", "status": "'.$st->encrypt(strtoupper(trim($FX['status']))).'"}' : ',{"name"'.':"'.$FX['status'].'","y"'.':'.floatval($FX['tot']).',"typ":"TEST_STATUS", "status": "'.$st->encrypt(strtoupper(trim($FX['status']))).'"}';
			}
		}
		//$VALS = (trim($VALS)!='') ? '{"series":[{"type":"pie","data":['.$VALS.']}}]' : '["series":{}]';
		//$STRG = $VALS;
		$VALS = array("series"=>array(array("type"=>"pie","data"=>$VALS)));
		$STRG = json_encode($VALS);
		break;
	case "TEST_ANALYSIS":
		$VALS=array();
		$WHR = (trim($VAL)!="") ? " and ts.clientid='".trim($VAL)."'" : ((trim($USERINFO['role'])=="MANAGER") ? " and ts.clientid in (".$st->getClientIDforManager($USERINFO).")" : "");
		if(sizeof($DTX)>0){
			$WHR .= " and date_format(tr.rundate,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(tr.rundate,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$FDT=$st->getDataArray("t_pt_testrun tr,t_pt_test ts", "distinct ifnull(tr.activity,'UNKNOWN') as 'status', count(*) as 'tot'", "tr.testid=ts.testid and tr.status in ('RUNNING','COMPLETED')".$WHR, "group by tr.activity");
		if(sizeof($FDT)>0  && $FDT!=false){
			foreach($FDT as $FX){
				array_push($VALS,
					array(
						"name"=>$FX['status'],
						"y"=>floatval($FX['tot']),
						"typ"=>"TEST_ANALYSIS",
						"status"=>$st->encrypt(strtoupper(trim($FX['status'])))			
					)
				);
				//$VALS .= (trim($VALS)=='') ? '{"name"'.':"'.$FX['status'].'","y"'.':'.floatval($FX['tot']).',"typ":"TEST_ANALYSIS","status":"'.$st->encrypt(strtoupper(trim($FX['status']))).'"}' : ',{"name"'.':"'.$FX['status'].'","y"'.':'.floatval($FX['tot']).',"typ":"TEST_ANALYSIS","status":"'.$st->encrypt(strtoupper(trim($FX['status']))).'"}';
			}
		}
		$VALS = array("series"=>array(array("type"=>"pie","data"=>$VALS)));
		$STRG = json_encode($VALS);
		break;
	case "OVERALL_ANALYSIS":
		$VALS=array();
		$WHX='';
		$WHR = (trim($VAL)!="") ? " and u.userid='".trim($VAL)."'" : ((trim($USERINFO['role'])=="MANAGER") ? " and u.userid in (".$st->getClientIDforManager($USERINFO).")" : "");
		/*if(sizeof($DTX)>0){
			$WHX = " and date_format(g.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(g.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$DTA=$st->getDataArray("t_pt_groups g, t_pt_users u","count(*) as 'tot'","g.clientid=u.userid and u.status='ACTIVE' and g.status='ACTIVE'".$WHR.$WHX,"");
		if($DTA[0]['tot']>0){
			foreach($DTA as $FX){
				array_push($VALS,
					array(
						"name"=>"Groups",
						"y"=>floatval($DTA[0]['tot']),
						"typ"=>"ACTIVE-GROUPS",
						"status"=>""
					)	
				);
				//$VALS .= (trim($VALS)=='') ? '{"name":"Groups","y":'.floatval($DTA[0]['tot']).',"typ":"ACTIVE-GROUPS", "status": ""}' : ',{"name":"Groups","y":'.floatval($DTA[0]['tot']).',"typ":"ACTIVE-GROUPS", "status": ""}';
			}
		}*/
		if(sizeof($DTX)>0){
			$WHX = " and date_format(m.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(m.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$DTA=$st->getDataArray("t_pt_mailinglist m, t_pt_users u","count(*) as 'tot'","m.clientid=u.userid and u.status='ACTIVE' and m.status='ACTIVE'".$WHR.$WHX,"");
		if($DTA[0]['tot']>0){
			foreach($DTA as $FX){
				array_push($VALS,
					array(
						"name"=>"Targets",
						"y"=>floatval($DTA[0]['tot']),
						"typ"=>"TOTAL-TARGETS",
						"status"=>""
					)	
				);
				//$VALS .= (trim($VALS)=='') ? '{"name":"Taegets","y":'.floatval($DTA[0]['tot']).',"typ":"TOTAL-TARGETS", "status": ""}' : ',{"name":"Targets","y":'.floatval($DTA[0]['tot']).',"typ":"TOTAL-TARGETS", "status": ""}';
			}
		}
		if(sizeof($DTX)>0){
			$WHX = " and date_format(t.createdon,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(t.createdon,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$DTA=$st->getDataArray("t_pt_test t, t_pt_users u","count(*) as 'tot'","t.clientid=u.userid and u.status='ACTIVE'".$WHR.$WHX,"");
		if($DTA[0]['tot']>0){
			foreach($DTA as $FX){
				array_push($VALS,
					array(
						"name"=>"Tests",
						"y"=>floatval($DTA[0]['tot']),
						"typ"=>"TOTAL-TESTS",
						"status"=>""
					)
				);
				//$VALS .= (trim($VALS)=='') ? '{"name":"Tests","y":'.floatval($DTA[0]['tot']).',"typ":"TOTAL-TESTS", "status": ""}' : ',{"name":"Tests","y":'.floatval($DTA[0]['tot']).',"typ":"TOTAL-TESTS", "status": ""}';
			}
		}	
		//$VALS = (trim($VALS)!='') ? '[{"series":{"data":['.$VALS.']}}]' : '["series":{}]';
		//$STRG = $VALS;
		$VALS = array("series"=>array(array("type"=>"pie","data"=>$VALS)));
		$STRG = json_encode($VALS);
		break;
	case "DOMAIN_TEST_ANALYSIS":
		$VALS=array();
		$CAT=array();
		$WHX='';
		$WHR = (trim($VAL)!="") ? " and ts.clientid='".trim($VAL)."'" : ((trim($USERINFO['role'])=="MANAGER") ? " and ts.clientid in (".$st->getClientIDforManager($USERINFO).")" : "");
// 		$WHR .= (trim($STATUS)!="" && trim($STATUS)!='UNKNOWN') ? " and r.activity='".trim($STATUS)."'" : ((trim($STATUS)=="UNKNOWN") ? " and r.activity is null" :"" );
		if(sizeof($DTX)>0){
			$WHX = " and date_format(tr.rundate,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(tr.rundate,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$FDT=$st->getDataArray("t_pt_testrun tr, t_pt_test ts", "SUBSTRING_INDEX(tr.emailid, '@', -1) as tdomain, SUM(if(tr.activity='PASSED',1,0)) as 'PASSED', sum(if(tr.activity='FAILED',1,0)) as 'FAILED', sum(if(tr.activity is null,1,0)) as 'UNKNOWN'", "tr.testid=ts.testid and tr.status in ('RUNNING','COMPLETED')".$WHR.$WHX, "group by tdomain");
		if(sizeof($FDT)>0 && $FDT!=false){
			$PAS = array('name'=>'PASSED','color' => '#0F0', 'colorByPoint'=>false);
			$FAL = array('name'=>'FAILED','color' => '#F00', 'colorByPoint'=>false);
			$UNO = array('name'=>'UNKNOWN','color' => '#00F', 'colorByPoint'=>false);
			foreach($FDT as $FX){
	    		array_push($CAT,$FX['tdomain']);
	    		$PAS['data'][] = array("name"=>"PASSED", "y"=>floatval($FX['PASSED']), "color"=>"#0F0", "typ"=>"DOMAIN_TEST_ANALYSIS", "status"=>$st->encrypt("PASSED"), "oth"=>$st->encrypt(trim($FX['tdomain'])));
	    		$FAL['data'][] = array("name"=>"FAILED", "y"=>floatval($FX['FAILED']), "color"=>"#F00", "typ"=>"DOMAIN_TEST_ANALYSIS", "status"=>$st->encrypt("FAILED"), "oth"=>$st->encrypt(trim($FX['tdomain'])));
	    		$UNO['data'][] = array("name"=>"UNKNOWN", "y"=>floatval($FX['UNKNOWN']), "color"=>"#00F", "typ"=>"DOMAIN_TEST_ANALYSIS", "status"=>$st->encrypt("UNKNOWN"), "oth"=>$st->encrypt(trim($FX['tdomain'])));
			}
			array_push($VALS,$PAS);
			array_push($VALS,$FAL);
			array_push($VALS,$UNO);
		}
		$STRG = json_encode(array_merge(array("categories"=>$CAT),array("series"=>$VALS))); //(trim($VALS)!='') ? $VALS : '[]';
		break;
	case "GROUP_TEST_ANALYSIS":
		$VALS=array();
		$CAT=array();
		$WHX='';
		$WHR = (trim($VAL)!="") ? " and ts.clientid='".trim($VAL)."'" : ((trim($USERINFO['role'])=="MANAGER") ? " and ts.clientid in (".$st->getClientIDforManager($USERINFO).")" : "");
		if(sizeof($DTX)>0){
			$WHX = " and date_format(tr.rundate,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(tr.rundate,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$FDT=$st->getDataArray("t_pt_testrun tr,t_pt_test ts,t_pt_groups gr", "distinct gr.groupname, ifnull(tr.activity,'UNKNOWN') as 'status', count(*) as 'tot'", "tr.testid=ts.testid and ts.groupid=gr.groupid and tr.status in ('RUNNING','COMPLETED')".$WHR.$WHX, "group by tr.activity, gr.groupid");
		if(sizeof($FDT)>0 && $FDT!=false){
			$ACT='';
			$x=0;
			foreach($FDT as $FX){
				$CLR = ((trim($FX['status'])=='PASSED') ? '#0F0' : ((trim($FX['status'])=='FAILED')?'#F00':'#00F'));
				if(trim($ACT)!=trim($FX['status'])){
					array_push($VALS, array("name"=>$FX['status'],
							"data"=>array(array("name"=>trim($FX['status']), "colorByPoint"=>false,
									"y"=>floatval($FX['tot']),
									"color"=>trim($CLR),
									"typ"=>"GROUP_TEST_ANALYSIS",
									"status"=>$st->encrypt(strtoupper(trim($FX['status']))),
									"oth"=>$st->encrypt(trim($FX['groupname']))
							)),"color"=>$CLR
					));
					$ACT=trim($FX['status']);
				}else{
					array_push($VALS[sizeof($VALS)-1]['data'],
						array("name"=>trim($FX['status']),
							"y"=>floatval($FX['tot']),
							"color"=>trim($CLR),
							"typ"=>"GROUP_TEST_ANALYSIS",
							"status"=>$st->encrypt(strtoupper(trim($FX['status']))),
							"oth"=>$st->encrypt(trim($FX['groupname']))
						)
					);
					//$VALS .= ',{"name":"'.trim($FX['status']).'","y":'.floatval($FX['tot']).',"typ":"GROUP_TEST_ANALYSIS", "status": "'.$st->encrypt(strtoupper(trim($FX['status']))).'","oth": "'.$st->encrypt(trim($FX['groupname'])).'"}';
				}
				array_push($CAT,trim($FX['groupname']));
				$x += 1;
			}
			//$VALS = (trim($VALS)!='') ? '['.$VALS.']}]' : '';
		}
		if(sizeof($CAT)>1) $CAT = array_unique($CAT);
		$CATS =  array_merge(array("categories"=>$CAT),array("series"=>$VALS));
		$STRG = json_encode($CATS); //(trim($VALS)!='') ? $VALS : '[]';
		//$STRG = (trim($VALS)!='') ? $VALS : '[]';
		break;
	case "USER_EVENT_TEST_ANALYSIS":
		$VALS=array();
		$WHX='';
		$DID=array('Link Clicked','Attachment Downloaded','Web Form Opened');
		$WHR = (trim($VAL)!="") ? " and ts.clientid='".trim($VAL)."'" : ((trim($USERINFO['role'])=="MANAGER") ? " and ts.clientid in (".$st->getClientIDforManager($USERINFO).")" : "");
		if(sizeof($DTX)>0){
			$WHX = " and date_format(tr.rundate,'%Y-%m-%d') >= '".trim($DTX[0])."' and date_format(tr.rundate,'%Y-%m-%d') <= '".trim($DTX[1])."'";
		}
		$FDT=$st->getDataArray("t_pt_testrun tr,t_pt_test ts", "distinct ifnull(tr.whatdid,'NOTHING') as 'status', count(*) as 'tot'", "tr.testid=ts.testid and tr.status in ('RUNNING','COMPLETED')".$WHR.$WHX, "group by tr.whatdid");
		if(sizeof($FDT)>0 && $FDT!=false){
			foreach($FDT as $FX){
				array_push($VALS,
					array(
						"name"=>$FX['status'],
						"y"=>floatval($FX['tot']),
						"typ"=>"USER_EVENT_TEST_ANALYSIS",
						"status"=>$st->encrypt(strtoupper(trim($FX['status'])))			
					)
				);
				//$VALS .= (trim($VALS)=='') ? '{"name"'.':"'.$FX['status'].'","y"'.':'.floatval($FX['tot']).',"typ":"USER_EVENT_TEST_ANALYSIS", "status": "'.$st->encrypt(strtoupper(trim($FX['status']))).'"}' : ',{"name"'.':"'.$FX['status'].'","y"'.':'.floatval($FX['tot']).',"typ":"USER_EVENT_TEST_ANALYSIS", "status": "'.$st->encrypt(strtoupper(trim($FX['status']))).'"}';
			}
		}
		$VALS = array("series"=>array(array("type"=>"pie","data"=>$VALS)));
		$STRG = json_encode($VALS);
		break;
}
echo $STRG;