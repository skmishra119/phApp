<?php
error_reporting(E_ALL);
define('DROOT','./');
define('XROOT','../../');
require_once '/var/www/html/phish/settings.inc.php';
$CNR=$st->connectDB();
$SQR="select ts.clientid, ts.fromname, ts.fromemail, tr.testid, tr.runid, tr.fullname, tr.emailid, tr.subject, tr.body from t_pt_test ts inner join t_pt_testrun tr on ts.testid=tr.testid where tr.status='SCHEDULED' and tr.activity is null and ts.startdate<='".date('Y-m-d H:i:s')."' and ts.enddate>='".date('Y-m-d H:i:s')."' and ts.process='SCHEDULED'";
$RSR=mysqli_query($CNR,$SQR);
while($RWR=mysqli_fetch_assoc($RSR)){
	$UsrEmail = $st->getUserEmailServerInfo(trim($RWR['clientid']));
	$mal=$st->sendMail(trim($RWR['fromemail']), trim($RWR['fromname']), trim($RWR['emailid']), trim($RWR['subject']), trim($RWR['body']),$UsrEmail);
	if(trim($mal)=='SENT'){
		$SQU="update t_pt_testrun set mailsenton='".date('Y-m-d h:i:s')."',status='RUNNING',statusdate='".date('Y-m-d H:i:s')."' where runid='".trim($RWR['runid'])."'";
		$RWU=mysqli_query($CNR,$SQU);
		$SQV="update t_pt_test set process='RUNNING' where testid='".trim($RWR['testid'])."' and process='SCHEDULED'";
		$RSV=mysqli_query($CNR,$SQV);
	}
}


$SQR="update t_pt_testrun set status='COMPLETED',activity=(case when activity is null then 'PASSED' else activity end), activitydate=(case when activitydate is null then '".date('Y-m-d H:i:s')."' else activitydate end) where testid in (select testid from t_pt_test where  enddate<'".date('Y-m-d H:i:s')."' and process in ('RUNNING'))";
$RSR=mysqli_query($CNR,$SQR);
if($RSR){
	$SQT="update t_pt_test set process='COMPLETED' where enddate<'".date('Y-m-d H:i:s')."' and process in ('RUNNING')";
	mysqli_query($CNR,$SQT);
	//$rid=$mysqli_insert_id($CNR);
}

$sql_admin="select emailid from t_pt_users where role='SYSADMIN' and status='ACTIVE'";
$rs_admin=mysqli_query($CNR,$sql_admin);
$MLTO='';
while($rw_adm=mysqli_fetch_assoc($rs_admin)){
	$MLTO .= (trim($MLTO)=='')?trim($rw_adm['emailid']):','.trim($rw_adm['emailid']);
}

$subj='TruPhish - Training required on phishing';
$bdy='<h1>Hello SYSADMIN,</h1>Thsre are list of the clients need training on phishing, The list is hereunder:';

$sql_ml="select distinct tr.listid, tr.fullname, tr.emailid, tr.contactphone, ts.clientid, tu.fullname as clientname, tu.emailid as clientemail, tu.phone as clientphone, tu.orgname from t_pt_testrun tr, t_pt_test ts, t_pt_users tu where tr.testid=ts.testid and ts.clientid=tu.userid and ts.enddate<'".date('Y-m-d H:i:s')."' and ts.process='COMPLETED' and tr.activity='FAILED' and tr.training=1 and tr.notified=0 order by tu.userid";
$rs_ml=mysqli_query($CNR,$sql_ml);
$nr_ml=mysqli_num_rows($rs_ml);

$CLINT='';
while($rw_ml=mysqli_fetch_assoc($rs_ml)){
	if(trim($CLINT) != trim($rw_ml['clientid'])){
		if(trim($CLINT)!=""){
			$bdy .= '</tbody></table><br/><hr/><br/><strong>Client Information</strong><br/>Client Name: '.trim($rw_ml['clientname']).'<br/>Email Address: '.trim($rw_ml['clientemail']).'<br/>Contact Number: '.trim($rw_ml['clientphone']).'<br/>Organization: '.trim($rw_ml['orgname']).'<br/><table border="1" cellspacing="0" cellpadding="0" width="80%"><thead><tr><th>Target Name</th><th><Email Address</th><th>Contact Number</th></tr></thead><tbody>';
		}else{
			$bdy .= '<br/><strong>Client Information</strong><br/>Client Name: '.trim($rw_ml['clientname']).'<br/>Email Address: '.trim($rw_ml['clientemail']).'<br/>Contact Number: '.trim($rw_ml['clientphone']).'<br/>Organization: '.trim($rw_ml['orgname']).'<br/><table border="1" cellspacing="0" cellpadding="0" width="80%"><thead><tr><th>Target Name</th><th>Email Address</th><th>Contact Number</th></tr></thead><tbody>';
		}
		$CLINT=trim($rw_ml['clientid']);
	}
	$bdy .= '<tr><td>'.trim($rw_ml['fullname']).'</td><td>'.trim($rw_ml['emailid']).'</td><td>'.trim($rw_ml['contactphone']).'</td></tr>';
}

$bdy .= ($nr_ml>0) ? '</tbody></table>' :'';
$bdy .= '<br/><br/>Warm Regards,<br/>Team @ TruPhish';

if($nr_ml>0) $mal=$st->sendMail($st->MailFrom, $st->fromName, $MLTO, trim($subj), trim($bdy));

$sql_uml="update t_pt_testrun tr join t_pt_test ts on tr.testid=ts.testid join t_pt_users tu on ts.clientid=tu.userid set tr.notified=1, tr.notifiedon='".date('Y-m-d h:i:s')."' where ts.enddate<'".date('Y-m-d H:i:s')."' and ts.process='COMPLETED' and tr.activity='FAILED' and tr.training=1 and tr.notified=0";
mysqli_query($CNR,$sql_uml);

$st->closeDB($CNR);