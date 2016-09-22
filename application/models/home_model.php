<?php
class Home_model extends CI_Model {

		public $responceMSG = "";
		public function __construct()
        {
                $this->load->database();
        }
		public function cleanData($input)
		 {
			$cleanData = $this->security->xss_clean($input );
			$cleanData = trim(str_replace(array("&","'","<script>","</script>"),array("and","`","<!--","-->"),$cleanData));
			if (get_magic_quotes_gpc()) { $cleanData = stripslashes($cleanData); }
			$cleanData = strtr($cleanData,array_flip(get_html_translation_table(HTML_ENTITIES)));
			$cleanData = strip_tags($cleanData);
			$cleanData = htmlspecialchars($cleanData);
		 return $cleanData;
		 }
		public function GlobalRecordCheck($table,$value,$field)
		{
			$query = $this->db->query("SELECT * FROM {$table} WHERE {$field}='{$value}'"); 			
			return $query->result();			
		}
		public function GlobalRecordCheck2($table,$value,$field,$value2,$field2)
		{
			$query = $this->db->query("SELECT * FROM {$table} WHERE {$field}='{$value}' AND {$field2}='{$value2}' "); return $query->result();			
		}
		public function UploadProfilePic($StaffID)
		{
			$image = $_FILES["file"]["name"];
			  $size=filesize($_FILES['file']['tmp_name']);
			  if($size>1048576)
			  {
				 echo "The Image Size you selected is too Large"; 
			  }else{
			move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/" . $_FILES["file"]["name"]);
			$query = $this->db->query("UPDATE ParamEmpMaster SET Photo ='{$image}' WHERE StaffIDNO='{$StaffID}'");
			echo "Profile Picture Updated Successfully";
			  }
		}
		public function LoginFuncx($UserName,$Password)
		{
		$query = $this->db->query("SELECT * FROM AdminUserRegister WHERE UserName='{$UserName}' AND Password='{$Password}'");	
		return $query->result();
		}
		
		public function RegisterGCMID($ID,$StaffIDNO)
		{
		$query = $this->db->query("UPDATE ParamEmpMaster SET GCMID='{$ID}' WHERE StaffIDNO='{$StaffIDNO}'");	
		if($query){
			echo "SUCCESS";
		}else{
			echo "failure";
		}
		}
		public function GetLeaveTypes($StaffIDNO)
		{
			$arrayEMP = $this->GlobalRecordCheck("ParamEmpMaster",$StaffIDNO,"StaffIDNO");
			$Gender = $arrayEMP[0]->Gender;
			$query = $this->db->query("SELECT * FROM ParamLeaveTypes P WHERE Active='Y' AND openApplication='Y' AND (IsGenderSensitive='N' OR IsGenderSensitive IS NULL OR (IsGenderSensitive='Y' AND Gender='{$Gender}'))");
			return $query->result();
		}
		public function GetCountries()
		{
			$query = $this->db->query("SELECT * FROM ParamCountries WHERE len(CountryName)>0");
			return $query->result();
		}
		
		public function GetTowns()
		{
			$query = $this->db->query("SELECT * FROM ParamTowns");
			return $query->result();
		}
		public function LeaveApplication($StaffIDNO)
		{
			
			$query = $this->db->query("SELECT ParamCompanyDepts.DeptName,ParamEmpMaster.Surname,ParamEmpMaster.AllNames,ParamLeaveTypes.Descriptions,LeaveApplications.* FROM ParamCompanyDepts, ParamEmpMaster,LeaveApplications,ParamLeaveTypes WHERE ParamCompanyDepts.DeptCode=ParamEmpMaster.DeptCode AND ParamEmpMaster.StaffIDNo=LeaveApplications.StaffIDNo AND LeaveApplications.Approved IS NULL AND LeaveApplications.Authorized IS NULL AND  LeaveApplications.Cancelled IS NULL AND LeaveApplications.FirstApprover='{$StaffIDNO}' AND ParamLeaveTypes.LeaveType = LeaveApplications.LeaveType ORDER BY LeaveApplications.AppNum DESC");
			return $query->result();
		}
		public function getYears()
		{
			$FirstPeriod="";
			$query = $this->db->query("SELECT top(1) Firstperiod FROM ParamCompanyMaster");
			if($query->result()!=null)
			{
			$FirstPeriod=$query->result()[0]->Firstperiod;
			}
			$query2 = $this->db->query("SELECT Distinct CurrentYear as CurrentYear FROM PCurrentPeriod PCD WHERE  CurrentPeriod>='{$FirstPeriod}' ORDER BY CurrentYear DESC");
			return $query2->result();
		}
		public function ParamHoliday()
		{
			$CurrentYear = date('Y');
			$HolidayMonth = date('m');
			$HolidayDate = date('d');
			$query = $this->db->query("SELECT *  FROM ParamHoliday WHERE CurrentYear='{$CurrentYear}' AND HolidayMonth='{$HolidayMonth}' OR (CurrentYear>'{$CurrentYear}' AND HolidayMonth>'{$HolidayMonth}')");
			return $query->result();		
		}
		public function LeaveSummary($StaffIDNO)
		{
		$query = $this->db->query("Select LeaveApplications.DateApplied,LeaveApplications.CurrentLeave,LeaveApplications.LeaveCFWD,LeaveApprovals.Authorized,LeaveApprovals.Approved,LeaveApplications.AppNum,ParamLeaveTypes.Descriptions,LeaveApplications.DaysApplied,LeaveApprovals.dayscancelled ,LeaveApprovals.AuthStartDate, LeaveApprovals.AprStartDate, LeaveApprovals.AprLastDate, LeaveApprovals.AprDateExpected, LeaveApplications.StartDate, LeaveApplications.LastDate,LeaveApplications.DateExpected, LeaveApprovals.AuthLastDate,LeaveApprovals.reasonCancelled,LeaveApprovals.SignOutDate,LeaveApprovals.Recalled,LeaveApprovals.Recalled, LeaveApprovals.cancelled,LeaveApprovals.ReturnDate,LeaveApprovals.AuthDateExpected, LeaveApprovals.ReturnDelay, LeaveApprovals.ReturnComments,LCF.LeaveEntitlement,ParamLeaveTypes.Dependent, ParamLeaveTypes.ParentLeaveType, LeaveApplications.Currentperiod, LeaveApplications.StaffIDNo,LCF.LeaveType FROM LeaveApplications LEFT OUTER JOIN LeaveApprovals ON LeaveApprovals.AppNum=LeaveApplications.AppNum INNER JOIN ParamLeaveTypes ON LeaveApplications.LeaveType = ParamLeaveTypes.LeaveType LEFT OUTER JOIN LeaveControlFile LCF ON LeaveApplications.StaffIDNo=LCF.StaffIDNo AND LeaveApplications.CurrentPeriod=LCF.CurrentPeriod AND LeaveApplications.LeaveType=LCF.leaveType WHERE  LeaveApplications.StaffIDNo='{$StaffIDNO}' ORDER BY LeaveApplications.StartDate DESC,LeaveApplications.AppNum Desc");
			return $query->result();
		}
		public function sendNotifications($message,$StaffIDNo,$Subject,$strMSG)
		{
		/*
		
Server API Key
AIzaSyC5KacPqnrFFkDr4bqqsWp9JbWnMeuQ9XM
Sender ID
1095213901064

Token
e9VubIXKfVs:APA91bGSYPnFP5uvqRS1NRzUMEdGS6MWr4c0Dcb2tr7cCHWiHdZqPNDTx8QcyplsspzxcfoEHxuaiM51ELBKd1mOs3bVS2TeMbTdSXl0ub51CcJrN9Q66qka5pfsCGIQHQaGNbZW3B4Y
		*/
//Getting api key 
$today = date('Y-m-d');
$api_key = 'AIzaSyC5KacPqnrFFkDr4bqqsWp9JbWnMeuQ9XM'; 
	$query = $this->db->query("SELECT *  FROM ParamEmpMaster WHERE StaffIDNo='{$StaffIDNo}'");
	foreach($query->result() as $key)
	{	
				$reg_token = $key->GCMID;
				if($reg_token!="")
				{
			 //$api_key = 'AIzaSyC5KacPqnrFFkDr4bqqsWp9JbWnMeuQ9XM'; 
			// $reg_token = array('e9VubIXKfVs:APA91bGSYPnFP5uvqRS1NRzUMEdGS6MWr4c0Dcb2tr7cCHWiHdZqPNDTx8QcyplsspzxcfoEHxuaiM51ELBKd1mOs3bVS2TeMbTdSXl0ub51CcJrN9Q66qka5pfsCGIQHQaGNbZW3B4Y');
			$reg_token = array($reg_token);
			 $msg = array
			 (
			 'message' => $message,
			 'Subject' => $Subject
			 ); 
			 $fields = array
			 (
			 'registration_ids' => $reg_token,
			 'data' => $msg
			 );
			 $headers = array
			 (
			 'Authorization: key=' . $api_key,
			 'Content-Type: application/json'
			 ); 
			 $ch = curl_init();
			 curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
			 curl_setopt( $ch,CURLOPT_POST, true );
			 curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			 curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			 curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			 curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			 $result = curl_exec($ch );
			 curl_close( $ch );
			 $res = json_decode($result);
			 $flag = $res->success; 
			 if($flag == 1){
			 echo "Notification Sent Successfully ".$strMSG;
			 }else{
			echo "Notification failed ".$strMSG;
			 }	
			 
					$this->db->query("INSERT INTO MobileNotifications(StaffID,Subject,Message,NotificationRead,Active,DateCreated) VALUES('{$StaffIDNo}','{$Subject}','{$message}','N','Y','{$today}')");			 
				}
				}	
		}
		
		public function GetFullDecryption($input)
        {
              $charARR = str_split($input);
               for ($i = 0; $i < count($charARR); $i++)
               {
                  
                   if ($charARR[$i] == '!'){
                       $char[] = 'a';
                   }elseif ($charARR[$i] == '@'){
                       $char[] = 'b';
                   }elseif ($charARR[$i] == '#'){
                       $char[] = 'c';
                   }elseif ($charARR[$i] == '$'){
                       $char[] = 'd';
                   }elseif ($charARR[$i] == '%'){
                       $char[] = 'e';
                   }elseif ($charARR[$i] == '^'){
                       $char[] = 'f';
                   }elseif ($charARR[$i] == '&'){
                       $char[] = 'g';
                   }elseif ($charARR[$i] == '*'){
                       $char[] = 'h';
                   }elseif ($charARR[$i] == '('){
                       $char[] = 'i';
                   }elseif ($charARR[$i] == ')'){
                       $char[] = 'j';
                   }elseif ($charARR[$i] == '-'){
                       $char[] = 'k';
                   }elseif ($charARR[$i] == '_'){
                       $char[] = 'l';
                   }elseif ($charARR[$i] == '='){
                       $char[] = 'm';
                   }elseif ($charARR[$i] == '+'){
                       $char[] = 'n';
                   }elseif ($charARR[$i] == '\\'){
                       $char[] = 'o';
                   }elseif ($charARR[$i] == '|'){
                       $char[] = 'p';
                   }elseif ($charARR[$i] == '/'){
                       $char[] = 'q';
                   }elseif ($charARR[$i] == '>'){
                       $char[] = 'r';
                   }elseif ($charARR[$i] == '<'){
                       $char[] = 's';
                   }elseif ($charARR[$i] == '?'){
                       $char[] = 't';
                   }elseif ($charARR[$i] == '['){
                       $char[] = 'u';
                   }elseif ($charARR[$i] == ']'){
                       $char[] = 'v';
                   }elseif ($charARR[$i] == '~'){
                       $char[] = 'w';
                   }elseif ($charARR[$i] == '{'){
                       $char[] = 'x';
                  }elseif ($charARR[$i] == '}'){
                       $char[] = 'y';
                   }elseif ($charARR[$i] == 'Z'){
                       $char[] = '0';
                   }elseif ($charARR[$i] == '0'){
                       $char[] = 'Z';
                   }elseif ($charARR[$i] == '1'){
                       $char[] = 'Y';
                   }elseif ($charARR[$i] == '2'){
                       $char[] = 'X';
                   }elseif ($charARR[$i] == '3'){
                       $char[] = 'W';
                   }elseif ($charARR[$i] == '4'){
                       $char[] = 'V';
                   }elseif ($charARR[$i] == '5'){
                       $char[] = 'U';
                   }elseif ($charARR[$i] == '6'){
                       $char[] = 'T';
                   }elseif ($charARR[$i] == '7'){
                       $char[] = 'S';
                   }elseif ($charARR[$i] == '8'){
                       $char[] = 'R';
                   }elseif ($charARR[$i] == '9'){
                       $char[] = 'Q';
                   }elseif ($charARR[$i] == 'A'){
                       $char[] = 'q';
                   }elseif ($charARR[$i] == 'B'){
                       $char[] = 'w';
                   }elseif ($charARR[$i] == 'C'){
                       $char[] = 'e';
                   }elseif ($charARR[$i] == 'D'){
                       $char[] = 'r';
                   }elseif ($charARR[$i] == 'E'){
                       $char[] = 't';
                   }elseif ($charARR[$i] == 'F'){
                       $char[] = 'y';
                   }elseif ($charARR[$i] == 'G'){
                       $char[] = 'u';
                   }elseif ($charARR[$i] == 'H'){
                       $char[] = 'i';
                   }elseif ($charARR[$i] == 'I'){
                       $char[] = 'o';
                   }elseif ($charARR[$i] == 'J'){
                       $char[] = 'p';
                   }elseif ($charARR[$i] == 'K'){
                       $char[] = 'a';
                   }elseif ($charARR[$i] == 'L'){
                       $char[] = 's';
                   }elseif ($charARR[$i] == 'M'){
                       $char[] = 'd';
                   }elseif ($charARR[$i] == 'N'){
                       $char[] = 'f';
                   }elseif ($charARR[$i] == 'O'){
                       $char[] = 'g';
                   }elseif ($charARR[$i] == 'P'){
                       $char[] = 'h';
                   }elseif ($charARR[$i] == 'Q'){
                       $char[] = 'j';
                   }elseif ($charARR[$i] == 'R'){
                       $char[] = 'k';
                   }elseif ($charARR[$i] == 'S'){
                       $char[] = 'l';
                   }elseif ($charARR[$i] == 'T'){
                       $char[] = 'z';
                   }elseif ($charARR[$i] == 'U'){
                       $char[] = 'x';
                   }elseif ($charARR[$i] == 'V'){
                       $char[] = 'c';
                   }elseif ($charARR[$i] == 'W'){
                       $char[] = 'v';
                   }elseif ($charARR[$i] == 'X'){
                       $char[] = 'b';
                   }elseif ($charARR[$i] == 'Y'){
                       $char[] = 'n';
                   }elseif ($charARR[$i] == 'Z'){
                       $char[] = 'm';
                   }elseif ($charARR[$i] == '~'){
                       $char[] = 'P';
                   }elseif ($charARR[$i] == '`'){
                       $char[] = 'O';
                   }elseif ($charARR[$i] == '!'){
                       $char[] = 'N';
                   }elseif ($charARR[$i] == '@'){
                       $char[] = 'M';
                   }elseif ($charARR[$i] == '#'){
                       $char[] = 'L';
                   }elseif ($charARR[$i] == '$'){
                       $char[] = 'K';
                   }elseif ($charARR[$i] == '%'){
                       $char[] = 'J';
                   }elseif ($charARR[$i] == '^'){
                       $char[] = 'I';
                   }elseif ($charARR[$i] == '&'){
                       $char[] = 'H';
                   }elseif ($charARR[$i] == '*'){
                       $char[] = 'G';
                   }elseif ($charARR[$i] == '('){
                       $char[] = 'F';
                   }elseif ($charARR[$i] == ')'){
                       $char[] = 'E';
                   }elseif ($charARR[$i] == '-'){
                       $char[] = 'D';
                   }elseif ($charARR[$i] == '_'){
                       $char[] = 'C';
                   }elseif ($charARR[$i] == '+'){
                       $char[] = 'B';
                   }elseif ($charARR[$i] == '='){
                       $char[] = 'A';
                   }elseif ($charARR[$i] == '{'){
                       $char[] = '0';
                   }elseif ($charARR[$i] == '['){
                       $char[] = '1';
                   }elseif ($charARR[$i] == '}'){
                       $char[] = '2';
                   }elseif ($charARR[$i] == ']'){
                       $char[] = '3';
                   }elseif ($charARR[$i] == '|'){
                       $char[] = '4';
                   }elseif ($charARR[$i] == substr('\\',0,1)){
                       $char[] = '5';
                   }elseif ($charARR[$i] == ':'){
                       $char[] = '6';
                   }elseif ($charARR[$i] == ';'){
                       $char[] = '7';
                   }elseif ($charARR[$i] == '"'){
                       $char[] = '8';
                   }elseif ($charARR[$i] == '\''){
                       $char[] = '9';
                   }elseif ($charARR[$i] == '<'){
                       $char[] = ':';
                   }elseif ($charARR[$i] == ','){
                       $char[] = ';';
                   }elseif ($charARR[$i] == '>'){
                       $char[] = '"';
                   }elseif ($charARR[$i] == '.'){
                       $char[] = '`';
                   }elseif ($charARR[$i] == '?'){
                       $char[] = '.';
                   }elseif ($charARR[$i] == '/'){
                       $char[] = '\'';
					}elseif ($charARR[$i] == 'a'){
                       $char[] = '!';
                   }elseif ($charARR[$i] == 'b'){
                       $char[] = '@';
                   }elseif ($charARR[$i] == 'c'){
                       $char[] = '#';
                   }elseif ($charARR[$i] == 'd'){
                       $char[] = '$';
                   }elseif ($charARR[$i] == 'e'){
                       $char[] = '%';
                   }elseif ($charARR[$i] == 'f'){
                       $char[] = '^';
                   }elseif ($charARR[$i] == 'g'){
                       $char[] = '&';
                   }elseif ($charARR[$i] == 'h'){
                       $char[] = '*';
                   }elseif ($charARR[$i] == 'i'){
                       $char[] = '(';
                   }elseif ($charARR[$i] == 'j'){
                       $char[] = ')';
                   }elseif ($charARR[$i] == 'k'){
                       $char[] = '-';
                   }elseif ($charARR[$i] == 'l'){
                       $char[] = '_';
                   }elseif ($charARR[$i] == 'm'){
                       $char[] = '=';
                   }elseif ($charARR[$i] == 'n'){
                       $char[] = '+';
                   }elseif ($charARR[$i] == 'o'){
                       $char[] = substr('\\',0,1);
                   }elseif ($charARR[$i] == 'p'){
                       $char[] = '|';
                   }elseif ($charARR[$i] == 'q'){
                       $char[] = '/';
                   }elseif ($charARR[$i] == 'r'){
                       $char[] = '>';
                   }elseif ($charARR[$i] == 's'){
                       $char[] = '<';
                   }elseif ($charARR[$i] == 't'){
                       $char[] = '?';
                   }elseif ($charARR[$i] == 'u'){
                       $char[] = '[';
                   }elseif ($charARR[$i] == 'v'){
                       $char[] = ']';
                   }elseif ($charARR[$i] == 'w'){
                       $char[] = '~';
                   }elseif ($charARR[$i] == 'x'){
                       $char[] = '{';
                  }elseif ($charARR[$i] == 'y'){
                       $char[] = '}';
                   }elseif ($charARR[$i] == 'z'){
                       $char[] = 'T';
                   }
              
           }
		   if(isset($char))
		   {
			$json_encode = json_encode($char);
		    $json_encode = str_replace("[","",$json_encode);
			$json_encode = str_replace("]","",$json_encode);
			$json_encode = str_replace("\"","",$json_encode);
			$json_encode = str_replace(",","",$json_encode);
			return $json_encode;
		   }
			


}	
		
		public function UseCalenderYear()
		{
			
			$query = $this->db->query("SELECT * FROM ParamCompanyMaster");
			if($query->result()==null)
			{
				return TRUE;
			}else if($query->result()[0]->UseCalenderYear=="")
			{
				return TRUE;
			}else if($query->result()[0]->UseCalenderYear=="Y")
			{
				return TRUE;
			}else{
				return False;
			}
		}
		Public Function getClosingPeriod($selectedDate, $boolCalendarYear)
		{
			$query = $this->db->query("SELECT * FROM ParamCompanyMaster");
			$date = DateTime::createFromFormat("Y-m-d", $selectedDate);
			//$date = date_create_from_format("Y-m-d", $selectedDate);
			if($query->result()==null)
			{
				$getClosingPeriod= "";
			}else if($query->result()[0]->OpeningMonth=="" || $query->result()[0]->ClosingMonth=="")
			{
				$getClosingPeriod= "";
			}else{
				$OpeningMonth = $query->result()[0]->OpeningMonth;
				$ClosingMonth = $query->result()[0]->ClosingMonth;
				if($boolCalendarYear==true)
				{
					$getClosingPeriod = date_format($date,"Y")."/".$ClosingMonth;
					
				}else{
					if(date_format($date,"m")<=$ClosingMonth)
					{
					$getClosingPeriod = date_format($date,"Y")."/".$ClosingMonth;	
					}else{
						$newYr = date_format($date,"Y")+1;
						$getClosingPeriod = $newYr."/".$ClosingMonth;
					}
					
				}
			}
			return $getClosingPeriod;
		}
		Public Function getOpeningPeriod($selectedDate, $boolCalendarYear)
		{
			$query = $this->db->query("SELECT * FROM ParamCompanyMaster");
			//$date = DateTime::createFromFormat("Y-m-d", $selectedDate);
			$date = date_create_from_format("Y-m-d", $selectedDate);
			if($query->result()==null)
			{
				$getOpeningPeriod= "";
			}else if($query->result()[0]->OpeningMonth=="" || $query->result()[0]->ClosingMonth=="")
			{
				$getOpeningPeriod= "";
			}else{
				$OpeningMonth = $query->result()[0]->OpeningMonth;
				$ClosingMonth = $query->result()[0]->ClosingMonth;
				if($boolCalendarYear==true)
				{
					$getOpeningPeriod = date_format($date,"Y")."/".$OpeningMonth;
				}else{
					if(date_format($date,"m")<=$ClosingMonth)
					{
					$getOpeningPeriod = date_format($date,"Y")."/".$OpeningMonth;	
					}else{
						$newYr = date_format($date,"Y")-1;
						$getOpeningPeriod = $newYr."/".$OpeningMonth;
					}
					
				}
			}
			return $getOpeningPeriod;
		}		
		public function getCurrentYear($OpeningPeriod, $ClosingPeriod)
		{
			
			$query = $this->db->query("SELECT * FROM ParamCompanyMaster");
			if($query->result()==null)
			{
				$getCurrentYear ="";
			}else if($query->result()[0]->OpeningMonth=="" || $query->result()[0]->ClosingMonth=="")
			{
				$getCurrentYear= "";
			}else if($query->result()[0]->UseFinancialYear=="Y")
			{
				$getCurrentYear = substr($OpeningPeriod,0,4)."/".substr($ClosingPeriod,0,4);
			}else{
				$getCurrentYear = substr($ClosingPeriod,0,4);
			}
		}
		
		public function GetCurrentLeaveInfo($Leave,$StaffIDNO)
		{
		$strQuery = $this->db->query("SELECT * FROM ParamLeaveTypes WHERE Descriptions='{$Leave}'");	
		$strQuery->result();
		$leaveType = $strQuery->result()[0]->LeaveType;
		$ParentLeaveType =  $strQuery->result()[0]->ParentLeaveType;
		$date = date('Y-m-d');
		$strClosingPeriod = $this->getClosingPeriod($date,$this->UseCalenderYear());
		$strOpeningPeriod = $this->getOpeningPeriod($date,$this->UseCalenderYear());
		$strCurrentPeriod = date('Y')."/".date('m');
		
		If(!$this->UseCalenderYear())
		{
			$strCurrentYear = $this->getCurrentYear($strOpeningPeriod,$strClosingPeriod);
		}
		else{
		$date = DateTime::createFromFormat("Y-m-d", date('Y-m-d'));
		$strCurrentYear = $date->format("Y");
		}
			$RoundDownLeaveDays="";
			$query = $this->db->query("SELECT * FROM ParamCompanyMaster");
			if($query->result()!=null)
			{
			$RoundDownLeaveDays	=$query->result()[0]->RoundDownLeaveDays;
			}
			
			if($RoundDownLeaveDays=='Y')
			{
			$query2 = $this->db->query("SELECT  PEM.DateHired as [Employment Date],PCD.DeptCode, '{$strCurrentYear}' AS [Current Year],PLT.Descriptions AS [Leave Type],PLT.*,(SELECT   LeaveEntitlement FROM LeaveControlFile  WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS [LeaveEntitlement], (SELECT  ROUND(LeaveBFwd,0,1) FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS [LeaveBfwd], ROUND(SUM(LCF.LeaveEarned),0,1)  AS [LeaveEarned], SUM(LCF.LeaveTaken) AS [LeaveTaken],CAST((SELECT ROUND(LeaveCFwd,0,1) FROM LeaveControlFile AS LCF1 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS DECIMAL(8,2)) AS [LeaveCfwd],CAST((SELECT ROUND(CurrentLeave,0,1) FROM LeaveControlFile AS LCF0 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS DECIMAL(8,2)) AS [CurrentLeave] FROM  LeaveControlFile AS LCF INNER JOIN ParamEmpMaster AS PEM ON LCF.StaffIDNO = PEM.StaffIDNO INNER JOIN ParamCompanyDepts AS PCD ON LCF.DepartmentCode = PCD.DeptCode  INNER JOIN ParamLeaveTypes AS PLT ON LCF.LeaveType = PLT.LeaveType WHERE  (LCF.CurrentYear = '{$strCurrentYear}') AND (PEM.StaffIDNo='{$StaffIDNO}' ) AND (LCF.LeaveType = '{$leaveType}' or LCF.LeaveType ='{$ParentLeaveType}' ) GROUP BY LCF.StaffIDNO, PEM.AllNames, PCD.DeptName, PCD.DeptCode, PEM.DateHired,PLT.Descriptions,LCF.LeaveType,PLT.LeaveType,PLT.OverrideStartdateSkipping, PLT.docrequired, PLT.OpenApplication, PLT.IsLeaveEarned, PLT.IsLeaveAwarded, PLT.AllowBackDating, PLT.Gender, PLT.PayAllowance, PLT.AnnualLeave, PLT.MaternityLeave, PLT.StudyLeave, PLT.CompassionateLeave, PLT.DefaultDays, PLT.SickLeave, PLT.PaternityLeave, PLT.UseExperienceToCalcLeave, PLT.AllowRecall, PLT.MaximumCFWDLeaveDays, PLT.NoticeDays, PLT.LeaveNoticeBasedOnDept, PLT.MinDaysForAllowance, PLT.CountSaturday, PLT.CountSunday, PLT.CountHoliday, PLT.CreatedBy, PLT.ApprovalLevels, PLT.IsGenderSensitive, PLT.AllowLeaveAllowancePayment, PLT.AllowanceLimit, PLT.AllowanceEquivalentToBasic, PLT.AllowanceFrequency, PLT.Active, PLT.AllowNegativeLeaveDays, PLT.Dependent, PLT.ParentLeaveType, PLT.ChildLeaveType, PLT.AllowRedirection, PLT.daysBeforeRedirection, PLT.DateCreated, PLT.AccPeriod, PLT.ReverseTallying, PLT.AddOffDays, PLT.UseCummulatedLeaveDays, PLT.UseProratedLeaveDays, PLT.AllowLeaveCfwd, PLT.ForfeitLeaveDays, PLT.ForfeitureDate, PLT.ForfeitureMessage, PLT.DurationBeforeFirstLeave, PLT.MinimumDaysForRecall,PLT.LimitDaysForApplication,PLT.MaxDaysForApplication,PLT.MinDaysForApplication,PLT.ModeOfAccrual,PLT.Reserved,PLT.ReservedMonth,PLT.ReservedLeaveDays,PLT.RequireHandOver,PLT.LastReportingDayForEarningLeave ORDER BY PCD.DeptCode");	
			}else{
			$query2 = $this->db->query("SELECT  PEM.DateHired as [Employment Date],PCD.DeptCode, '{$strCurrentYear}' AS [Current Year],PLT.Descriptions AS [Leave Type],PLT.*,(SELECT   LeaveEntitlement FROM LeaveControlFile  WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS [LeaveEntitlement], (SELECT  round(LeaveBFWD*2,0)/2 FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS [LeaveBfwd], ROUND(SUM(LCF.LeaveEarned)*2,0)/2  AS [LeaveEarned], SUM(LCF.LeaveTaken) AS [LeaveTaken],CAST((SELECT ROUND(LeaveCFwd*2,0)/2 FROM LeaveControlFile AS LCF1 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS DECIMAL(8,2)) AS [LeaveCfwd],CAST((SELECT ROUND(CurrentLeave*2,0)/2 FROM LeaveControlFile AS LCF0 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS DECIMAL(8,2)) AS [CurrentLeave] FROM  LeaveControlFile AS LCF INNER JOIN ParamEmpMaster AS PEM ON LCF.StaffIDNO = PEM.StaffIDNO INNER JOIN ParamCompanyDepts AS PCD ON LCF.DepartmentCode = PCD.DeptCode  INNER JOIN ParamLeaveTypes AS PLT ON LCF.LeaveType = PLT.LeaveType WHERE  (LCF.CurrentYear = '{$strCurrentYear}') AND (PEM.StaffIDNo='{$StaffIDNO}' ) AND (LCF.LeaveType = '{$leaveType}' or LCF.LeaveType ='{$ParentLeaveType}' ) GROUP BY LCF.StaffIDNO, PEM.AllNames, PCD.DeptName, PCD.DeptCode, PEM.DateHired,PLT.Descriptions,LCF.LeaveType,PLT.LeaveType,PLT.OverrideStartdateSkipping, PLT.docrequired, PLT.OpenApplication, PLT.IsLeaveEarned, PLT.IsLeaveAwarded, PLT.AllowBackDating, PLT.Gender, PLT.PayAllowance, PLT.AnnualLeave, PLT.MaternityLeave, PLT.StudyLeave, PLT.CompassionateLeave, PLT.DefaultDays, PLT.SickLeave, PLT.PaternityLeave, PLT.UseExperienceToCalcLeave, PLT.AllowRecall, PLT.MaximumCFWDLeaveDays, PLT.NoticeDays, PLT.LeaveNoticeBasedOnDept, PLT.MinDaysForAllowance, PLT.CountSaturday, PLT.CountSunday, PLT.CountHoliday, PLT.CreatedBy, PLT.ApprovalLevels, PLT.IsGenderSensitive, PLT.AllowLeaveAllowancePayment, PLT.AllowanceLimit, PLT.AllowanceEquivalentToBasic, PLT.AllowanceFrequency, PLT.Active, PLT.AllowNegativeLeaveDays, PLT.Dependent, PLT.ParentLeaveType, PLT.ChildLeaveType, PLT.AllowRedirection, PLT.daysBeforeRedirection, PLT.DateCreated, PLT.AccPeriod, PLT.ReverseTallying, PLT.AddOffDays, PLT.UseCummulatedLeaveDays, PLT.UseProratedLeaveDays, PLT.AllowLeaveCfwd, PLT.ForfeitLeaveDays, PLT.ForfeitureDate, PLT.ForfeitureMessage, PLT.DurationBeforeFirstLeave, PLT.MinimumDaysForRecall,PLT.LimitDaysForApplication,PLT.MaxDaysForApplication,PLT.MinDaysForApplication,PLT.ModeOfAccrual,PLT.Reserved,PLT.ReservedMonth,PLT.ReservedLeaveDays,PLT.RequireHandOver,PLT.LastReportingDayForEarningLeave ORDER BY PCD.DeptCode");
			}
			return $query2->result();
		}
	
		public function getSystemOpeningPeriod1()
		{
			$getSystemOpeningPeriod1 = "";
			$query = $this->db->query("SELECT top(1) Firstperiod FROM ParamCompanyMaster");
			if($query->result()!=null)
			{
			$getSystemOpeningPeriod1=$query->result()[0]->Firstperiod;
			}
			return $getSystemOpeningPeriod1;			
		}
		public function LeaveDetails($strStaffIDNo)
		{
			$RoundDownLeaveDays="";
			$query = $this->db->query("SELECT * FROM ParamCompanyMaster");
			if($query->result()!=null)
			{
			$RoundDownLeaveDays	=$query->result()[0]->RoundDownLeaveDays;
			}

			$date = date('Y-m-d');
			$strClosingPeriod = $this->getClosingPeriod($date,$this->UseCalenderYear());
			$strOpeningPeriod = $this->getOpeningPeriod($date,$this->UseCalenderYear());
			$strSystemOpeningPeriod = $this->getSystemOpeningPeriod1();
			$strCurrentPeriod = date('Y')."/".date('m');
			
			If(!$this->UseCalenderYear())
			{
				$strCurrentYear = $this->getCurrentYear($strOpeningPeriod,$strClosingPeriod);
			}
			else{
			$date = DateTime::createFromFormat("Y-m-d", date('Y-m-d'));
			$strCurrentYear = $date->format("Y");
			}
		
			
			if($RoundDownLeaveDays=='Y')
			{
				 if ($strOpeningPeriod<$strSystemOpeningPeriod)
				 {
				 $strQuery = $this->db->query("SELECT  PEM.DateHired as [Employment Date], '{$strCurrentYear}' AS [Current Year],PLT.Descriptions AS [Leave Type],LCF.LeaveType,(SELECT   LeaveEntitlement FROM LeaveControlFile  WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS [Ent'ment], ISNULL((SELECT  ROUND(LeaveBFwd,0,0) FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strSystemOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)),0) AS [Leave Bfwd], ROUND(SUM(LCF.LeaveEarned),0,1) AS [Leave Earned], SUM(LCF.LeaveTaken) AS [Leave Taken],ROUND(SUM(LCF.LeaveForfeited)*2,0)/2 AS [Leave Forfeited],CAST((SELECT ROUND(LeaveCFwd,0,1) FROM LeaveControlFile AS LCF1 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS DECIMAL(8,2)) AS [Leave Bal],(SELECT CASE WHEN (max(LeaveEntitlement)=Min(LeaveEntitlement)) THEN max(LeaveEntitlement) ELSE round(avg(LeaveEntitlement),0,0) END as LeaveEntitlement FROM LeaveControlFile WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod <= '{$strCurrentPeriod}' AND CurrentYear='{$strCurrentYear}') AND (StaffIDNO = LCF.StaffIDNO))-SUM(LCF.LeaveTaken)-SUM(LCF.LeaveForfeited)+ISNULL((SELECT ROUND(LeaveBFWD,0,0) FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strSystemOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)),0) AS [End Yr. Bal] FROM  LeaveControlFile AS LCF INNER JOIN ParamEmpMaster AS PEM ON LCF.StaffIDNO = PEM.StaffIDNO INNER JOIN ParamCompanyDepts AS PCD ON LCF.DepartmentCode = PCD.DeptCode  INNER JOIN ParamLeaveTypes AS PLT ON LCF.LeaveType = PLT.LeaveType WHERE  (LCF.CurrentYear = '{$strCurrentYear}') AND (PEM.StaffIDNo='{$strStaffIDNo}' ) GROUP BY LCF.StaffIDNO, PEM.AllNames, PCD.DeptName, PCD.DeptCode, PEM.DateHired,PLT.Descriptions,LCF.LeaveType ORDER BY PCD.DeptCode");
				 }else{
				 $strQuery = $this->db->query("SELECT  PEM.DateHired as [Employment Date], '{$strCurrentYear}' AS [Current Year],PLT.Descriptions AS [Leave Type],LCF.LeaveType,(SELECT   LeaveEntitlement FROM LeaveControlFile  WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS [Ent'ment], ISNULL((SELECT  ROUND(LeaveBFwd,0,0) FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)),0) AS [Leave Bfwd], ROUND(SUM(LCF.LeaveEarned),0,1) AS [Leave Earned], SUM(LCF.LeaveTaken) AS [Leave Taken],ROUND(SUM(LCF.LeaveForfeited)*2,0)/2 AS [Leave Forfeited],CAST((SELECT ROUND(LeaveCFwd,0,1) FROM LeaveControlFile AS LCF1 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS DECIMAL(8,2)) AS [Leave Bal],(SELECT CASE WHEN (max(LeaveEntitlement)=Min(LeaveEntitlement)) THEN max(LeaveEntitlement) ELSE round(avg(LeaveEntitlement),0,0) END as LeaveEntitlement FROM LeaveControlFile WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod <= '{$strCurrentPeriod}' AND CurrentYear='{$strCurrentYear}') AND (StaffIDNO = LCF.StaffIDNO))-SUM(LCF.LeaveTaken)-SUM(LCF.LeaveForfeited)+ISNULL((SELECT ROUND(LeaveBFWD,0,0) FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)),0) AS [End Yr. Bal] FROM  LeaveControlFile AS LCF INNER JOIN ParamEmpMaster AS PEM ON LCF.StaffIDNO = PEM.StaffIDNO INNER JOIN ParamCompanyDepts AS PCD ON LCF.DepartmentCode = PCD.DeptCode  INNER JOIN ParamLeaveTypes AS PLT ON LCF.LeaveType = PLT.LeaveType WHERE  (LCF.CurrentYear = '{$strCurrentYear}') AND (PEM.StaffIDNo='{$strStaffIDNo}' ) GROUP BY LCF.StaffIDNO, PEM.AllNames, PCD.DeptName, PCD.DeptCode, PEM.DateHired,PLT.Descriptions,LCF.LeaveType ORDER BY PCD.DeptCode"); 
				 }
			}else{
				
				if ($strOpeningPeriod<$strSystemOpeningPeriod)
				{
				$strQuery = $this->db->query("SELECT  PEM.DateHired as [Employment Date], '{$strCurrentYear}' AS [Current Year],PLT.Descriptions AS [Leave Type],LCF.LeaveType,(SELECT   LeaveEntitlement FROM LeaveControlFile  WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS [Ent'ment], ISNULL((SELECT  ROUND(LeaveBFWD*2,0)/2 FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strSystemOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)),0) AS [Leave Bfwd], ROUND(SUM(LCF.LeaveEarned)*2,0)/2 AS [Leave Earned], SUM(LCF.LeaveTaken) AS [Leave Taken], ROUND(SUM(LCF.LeaveForfeited)*2,0)/2 AS [Leave Forfeited],CAST((SELECT ROUND(LeaveCFwd*2,0)/2 FROM LeaveControlFile AS LCF1 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS DECIMAL(8,2)) AS [Leave Bal],(SELECT CASE WHEN (max(LeaveEntitlement)=Min(LeaveEntitlement)) THEN max(LeaveEntitlement) ELSE round(avg(LeaveEntitlement),0,0) END as LeaveEntitlement FROM LeaveControlFile WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod <= '{$strCurrentPeriod}' AND CurrentYear='{$strCurrentYear}') AND (StaffIDNO = LCF.StaffIDNO))-SUM(LCF.LeaveTaken)-SUM(LCF.LeaveForfeited)+ISNULL((SELECT ROUND(LeaveBFWD*2,0)/2 FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strSystemOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)),0) AS [End Yr. Bal] FROM  LeaveControlFile AS LCF INNER JOIN ParamEmpMaster AS PEM ON LCF.StaffIDNO = PEM.StaffIDNO INNER JOIN ParamCompanyDepts AS PCD ON LCF.DepartmentCode = PCD.DeptCode  INNER JOIN ParamLeaveTypes AS PLT ON LCF.LeaveType = PLT.LeaveType WHERE  (LCF.CurrentYear = '{$strCurrentYear}') AND (PEM.StaffIDNo='{$strStaffIDNo}' ) GROUP BY LCF.StaffIDNO, PEM.AllNames, PCD.DeptName, PCD.DeptCode, PEM.DateHired,PLT.Descriptions,LCF.LeaveType ORDER BY PCD.DeptCode"); 
				}else{
				$strQuery = $this->db->query("SELECT  PEM.DateHired as [Employment Date], '{$strCurrentYear}' AS [Current Year],PLT.Descriptions AS [Leave Type],LCF.LeaveType,(SELECT   LeaveEntitlement FROM LeaveControlFile  WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS [Ent'ment], ISNULL((SELECT  ROUND(LeaveBFWD*2,0)/2 FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)),0) AS [Leave Bfwd], ROUND(SUM(LCF.LeaveEarned)*2,0)/2 AS [Leave Earned], SUM(LCF.LeaveTaken) AS [Leave Taken], ROUND(SUM(LCF.LeaveForfeited)*2,0)/2 AS [Leave Forfeited],CAST((SELECT ROUND(LeaveCFwd*2,0)/2 FROM LeaveControlFile AS LCF1 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strCurrentPeriod}') AND (StaffIDNO = LCF.StaffIDNO)) AS DECIMAL(8,2)) AS [Leave Bal],(SELECT CASE WHEN (max(LeaveEntitlement)=Min(LeaveEntitlement)) THEN max(LeaveEntitlement) ELSE round(avg(LeaveEntitlement),0,0) END as LeaveEntitlement FROM LeaveControlFile WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod <= '{$strCurrentPeriod}' AND CurrentYear='{$strCurrentYear}') AND (StaffIDNO = LCF.StaffIDNO))-SUM(LCF.LeaveTaken)-SUM(LCF.LeaveForfeited)+ISNULL((SELECT ROUND(LeaveBFWD*2,0)/2 FROM LeaveControlFile AS LCF2 WHERE (LeaveType = LCF.LeaveType) AND (CurrentPeriod = '{$strOpeningPeriod}') AND (StaffIDNO = LCF.StaffIDNO)),0) AS [End Yr. Bal] FROM  LeaveControlFile AS LCF INNER JOIN ParamEmpMaster AS PEM ON LCF.StaffIDNO = PEM.StaffIDNO INNER JOIN ParamCompanyDepts AS PCD ON LCF.DepartmentCode = PCD.DeptCode  INNER JOIN ParamLeaveTypes AS PLT ON LCF.LeaveType = PLT.LeaveType WHERE  (LCF.CurrentYear = '{$strCurrentYear}') AND (PEM.StaffIDNo='{$strStaffIDNo}' ) GROUP BY LCF.StaffIDNO, PEM.AllNames, PCD.DeptName, PCD.DeptCode, PEM.DateHired,PLT.Descriptions,LCF.LeaveType ORDER BY PCD.DeptCode"); 
				}
			
			}
			return $strQuery->result();
		}
		
		public function Notifications($StaffIDNO)
		{
		$strQuery = $this->db->query("SELECT *  FROM MobileNotifications WHERE StaffID='{$StaffIDNO}' AND Active='Y' AND NotificationRead='N' ORDER BY ID DESC");	
		return $strQuery->result();	
		}
		public function FirstApproval()
		{
			$resonceMsg = "";
			$today = date('Y-m-d');
			$aA = $this->cleanData($this->input->post('ApprovedStartDate'));
			$bB=$this->cleanData($this->input->post("txtApprovedLastDate"));
			$CC=$this->cleanData($this->input->post("txtDateExpected"));
			$dD=$this->cleanData($this->input->post("chkReject"));
			$strCurrentYear = date('Y');
			$txtDaysApplied = $this->cleanData($this->input->post("txtDaysApplied"));
			$txtLeaveType = $this->cleanData($this->input->post("txtLeaveType"));
			$txtLeaveType = $this->Home_model->GlobalRecordCheck("ParamLeaveTypes",$txtLeaveType,"Descriptions")[0]->LeaveType;	
			$GetCurrentStaffID = $this->cleanData($this->input->post("GetCurrentStaffID"));
			$txtComments = $this->cleanData($this->input->post("txtComments"));
			$CurrentUserName =  $GetCurrentStaffID;
			$MyCurrentPeriod = date('Y/m');
			$txtAppNumber = $this->cleanData($this->input->post("txtAppNumber"));
			$txtStaffIDNo = $this->cleanData($this->input->post("txtStaffIDNo"));
			if($dD=='0')
			{
				$strApproved="0";
			$this->db->query("UPDATE LeaveApprovals SET staffidno='{$txtStaffIDNo}',currentyear='{$strCurrentYear}',daysapplied='{$txtDaysApplied}',approved='{$strApproved}',approvedby='{$GetCurrentStaffID}',aprcomments='{$txtComments}',aprcompname='Mobile',createdby='{$CurrentUserName}',datecreated='{$today}',accperiod='{$MyCurrentPeriod}',ApprovalDate='{$today}' WHERE AppNum='{$txtAppNumber}'");
				echo "Leave Application Rejected Successfully. ";
			}else
			{
			$strApproved="1";
			
			
			$data = array("staffidno"=>$txtStaffIDNo,
						"ApprovalDate"=>$today,
						"currentyear"=>$strCurrentYear,
						"daysapplied"=>$txtDaysApplied,
						"leavetype"=>$txtLeaveType,
						"Approved"=>$strApproved,
						"approvedby"=>$GetCurrentStaffID,
						"aprstartdate"=>$aA,
						"aprlastdate"=>$bB,
						"aprdateexpected"=>$CC,
						"aprcomments"=>$txtComments,
						"aprcompname"=>'Mobile',
						"createdby"=>$CurrentUserName,
						"datecreated"=>$today,
						"accperiod"=>$MyCurrentPeriod);
			$this->db->where("AppNum",$txtAppNumber);
			$this->db->update("LeaveApprovals",$data);
						
			//=============================
	$this->db->query("UPDATE ParamEmpMaster SET LeaveDate='{$aA}',DateExpected='{$CC}' WHERE StaffIdNo='{$txtStaffIDNo}'");
			$this->db->query("UPDATE LeaveApplications SET Approved='1' WHERE AppNum='{$txtAppNumber}'");
			$strSelectedStaff=$txtStaffIDNo;
			$EmpArray = $this->GlobalRecordCheck('ParamEmpMaster',$txtStaffIDNo,'StaffIDNo');
			foreach($EmpArray as $key);
			$firstApprover = $key->FirstApprover;
			$SecondApprover = $key->SecondApprover;
			$ThirdApprover = $key->ThirdApprover;
			if(($this->CheckStaffApprovalLevels($txtStaffIDNo)==1)||($firstApprover==$SecondApprover)&&($SecondApprover==$ThirdApprover)||($this->CheckStaffApprovalLevels($txtStaffIDNo)==2)&&($firstApprover==$SecondApprover))
			{
				$strAuthorized="1";
				$this->db->query("UPDATE LeaveApplications SET SecondApproval='1' WHERE AppNum='{$txtAppNumber}'");
			$this->db->query("UPDATE LeaveApplications SET Authorized='1' WHERE AppNum='{$txtAppNumber}'");
			$this->db->query("UPDATE LeaveApplications SET Authorized='1' WHERE AppNum='{$txtAppNumber}'");
			$this->db->query("UPDATE ParamEmpMaster SET LVStatus='1', LeaveDate='{$aA}',DateExpected='{$CC}' WHERE StaffIdNo='{$txtStaffIDNo}'");
			$this->MailOnAuthorization($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID);
			//$this->SendMailToAdministrators();
			
			}
			
			if(($firstApprover==$SecondApprover)&&($this->CheckStaffApprovalLevels($txtStaffIDNo)==2))
			{
				$this->db->query("UPDATE LeaveApplications SET SecondApproval='1' WHERE AppNum='{$txtAppNumber}' ");
				$this->MailOnAuthorization($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID);
			
			}else
			{
				$this->SendMailToSecondApprover($txtStaffIDNo,$txtAppNumber);
					
			}
			
			$this->db->query("UPDATE LeaveApplications SET Approved='{$strApproved}',ApprovalDate='{$today}' WHERE AppNum='{$txtAppNumber}' ");
			if($dD=='0')
			{
				echo  "Leave Application Rejected Successfully. ";
				$this->MailOnRejection($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID,$txtComments);
				
			}else
			{
				
				$this->MailOnApproval($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID);
				
			}
			
			echo "Leave Approved Successfully";
			}
			
		
			
			
		}
		public function DeleteNotifications($ID)
		{
			$this->db->query("UPDATE MobileNotifications SET Active='N' WHERE ID='{$ID}'");
		}
	public function MailOnRejection($txtStaffIDNo,$txtAppNumber,$CurrentUser,$Reason)
		{
		 $rsFindRecord = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveApplications A where E.StaffIDNo=A.StaffIDNo AND E.StaffIDNo ='{$txtStaffIDNo}' AND A.AppNum like '{$txtAppNumber}'");
			if($rsFindRecord->result()==null)
			 {
				 return;
			 }	
			 foreach($rsFindRecord->result() as $key);
			 $AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$txtStaffIDNo,'StaffIDNo')[0]->AllNames;
			 $CurrentUserID= $this->GlobalRecordCheck('ParamEmpMaster',$CurrentUser,'StaffIDNo')[0]->AllNames;
			  $From= $this->GlobalRecordCheck('ParamEmpMaster',$CurrentUser,'StaffIDNo')[0]->ContEmail;
			if($key->ContEmail!="")
			{
				$today = date('Y-m-d');
				$Subject = "Leave Application Rejected";
				$TextBody = "Dear ".$AllNames." Your leave Application Has been rejected!. Reason.".$Reason." Have a Nice Day.";
				$to_email = $key->ContEmail;
				
				if($this->send_mail($to_email,$Subject,$TextBody,$From)==true)
			{
				$strMSG="Sending Mail To Applicant Finished. ";
			}else
			{
			
				$strMSG="An error has been encountered.  ";
			}
				echo $strMSG; 
				$this->sendNotifications($TextBody,$txtStaffIDNo,$Subject,$strMSG);	
			}
			
		}	
	public function MailOnApproval($txtStaffIDNo,$txtAppNumber,$CurrentUser)
		{
		 $rsFindRecord = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveApplications A where E.StaffIDNo=A.StaffidNo AND E.StaffIDNo ='{$txtStaffIDNo}' AND A.AppNum like '{$txtAppNumber}'");
			if($rsFindRecord->result()==null)
			 {
				 return;
			 }	
			 foreach($rsFindRecord->result() as $key);
			 $AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$txtStaffIDNo,'StaffIDNo')[0]->AllNames;
			 $CurrentUserID= $this->GlobalRecordCheck('ParamEmpMaster',$CurrentUser,'StaffIDNo')[0]->AllNames;
			  $From= $this->GlobalRecordCheck('ParamEmpMaster',$CurrentUser,'StaffIDNo')[0]->ContEmail;
			if($key->ContEmail!="")
			{
				$today = date('Y-m-d');
				$Subject = "Leave Application Approved";
				$TextBody = "Dear ".$AllNames." YOUR LEAVE APLICATION HAS GONE THROUGH THE FIRST APPROVAL PHASE. IT WAS APPROVED ON ".$today."  BY ".$CurrentUserID." Have a Nice Day.";
				$to_email = $key->ContEmail;
				
			if($this->send_mail($to_email,$Subject,$TextBody,$From)==true)
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='Y',DateMailSent='{$getDate}' WHERE AppNum='{$txtAppNumber}'");
				$msg = "Sending Mail To Applicant Finished. ";
			}else
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='N' WHERE AppNum='{$txtAppNumber}' ");
				$msg = "Sending Mail To Applicant Failed. ";
			}
			$this->sendNotifications($TextBody,$txtStaffIDNo,$Subject,$msg);
			}
		}	
		
	public function MailOnSecondApproval($txtStaffIDNo,$txtAppNumber,$CurrentUser)
		{
			$getDate = date('Y-m-d');
		 $rsFindRecord = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveApplications A where E.StaffIDNo=A.StaffidNo AND E.StaffIDNo ='{$txtStaffIDNo}' AND A.AppNum like '{$txtAppNumber}'");
			if($rsFindRecord->result()==null)
			 {
				 return;
			 }	
			 foreach($rsFindRecord->result() as $key);
			 $AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$txtStaffIDNo,'StaffIDNo')[0]->AllNames;
			 $CurrentUserID= $this->GlobalRecordCheck('ParamEmpMaster',$CurrentUser,'StaffIDNo')[0]->AllNames;
			  $From= $this->GlobalRecordCheck('ParamEmpMaster',$CurrentUser,'StaffIDNo')[0]->ContEmail;
			if($key->ContEmail!="")
			{
				$today = date('Y-m-d');
				$Subject = "Leave Application Approved";
				$TextBody = "Dear ".$AllNames." YOUR LEAVE APLICATION HAS GONE THROUGH THE SECOND APPROVAL PHASE. IT WAS APPROVED ON ".$today."  BY ".$CurrentUserID." Have a Nice Day.";
				$to_email = $key->ContEmail;
				
			if($this->send_mail($to_email,$Subject,$TextBody,$From)==true)
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='Y',DateMailSent='{$getDate}' WHERE AppNum='{$txtAppNumber}'");
				$msg = "Sending Mail To Applicant Finished. ";
			}else
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='N' WHERE AppNum='{$txtAppNumber}' ");
				$msg = "Sending Mail To Applicant Failed. ";
			}
			$this->sendNotifications($TextBody,$txtStaffIDNo,$Subject,$msg);
			}
		}	
		
		public function MailOnAuthorization($txtStaffIDNo,$txtAppNumber,$CurrentUser)
		{
		 $rsFindRecord = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveApplications A,LeaveApprovals Ap where A.AppNum=Ap.AppNum AND Ap.StaffIDNo=E.StaffIDNo AND  E.StaffIDNo=A.StaffidNo AND E.StaffIDNo ='{$txtStaffIDNo}' AND A.AppNum like '{$txtAppNumber}'");
			if($rsFindRecord->result()==null)
			 {
				 return;
			 }	
			 foreach($rsFindRecord->result() as $key);
			 $AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$txtStaffIDNo,'StaffIDNo')[0]->AllNames;
			 $CurrentUserID= $this->GlobalRecordCheck('ParamEmpMaster',$CurrentUser,'StaffIDNo')[0]->AllNames;
			  $From= $this->GlobalRecordCheck('ParamEmpMaster',$CurrentUser,'StaffIDNo')[0]->ContEmail;
			if($key->ContEmail!="")
			{
				$today = date('Y-m-d');
				$Subject = "Leave Application Approval for ".$AllNames;
				$TextBody = "Dear ".$AllNames." YOUR LEAVE APLICATION HAS GONE THROUGH THE FINAL APPROVAL PHASE. YOU MAY NOW PROCEED ON FOR LEAVE WHEN THE DATE IS DUE. IT WAS APPROVED ON ".$today."  BY ".$CurrentUserID." THE LEAVE START DATE is ".$key->AuthStartDate." Have a Nice Day.";
				$to_email = $key->ContEmail;
				
				if($this->send_mail($to_email,$Subject,$TextBody,$From)==true)
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='Y',DateMailSent='{$getDate}' WHERE AppNum='{$txtAppNumber}'");
				$strMSG="Sending Mail To Applicant Finished. ";
			}else
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='N' WHERE AppNum='{$txtAppNumber}' ");
				$strMSG="An error has been encountered.  ";
			}
				$this->sendNotifications($TextBody,$txtStaffIDNo,$Subject,$strMSG); 
			}
		}
		public function CheckStaffApprovalLevels($StaffIDNO)
		{
		$query = $this->db->query("SELECT ApprovalLevel FROM ParamEmpMaster where StaffIDNo='{$StaffIDNO}'");
		return $query->result()[0]->ApprovalLevel;
		
		}
		public function Recalls($DeptCode)
		{
			$today = date('Y-m-d');
			$query = "SELECT ParamCompanyDepts.Deptname,ParamEmpMaster.DeptCode,ParamEmpMaster.AllNames,ParamEmpMaster.OtherNames,LeaveApprovals.*,ParamLeaveTypes.Descriptions FROM ParamEmpMaster,LeaveApprovals,ParamCompanydepts,ParamLeaveTypes WHERE ParamcompanyDepts.DeptCode=ParamEmpMaster.deptCode AND ParamEmpMaster.StaffIDNo=LeaveApprovals.StaffIDNo AND LeaveApprovals.SignOutDate IS NOT NULL AND LeaveApprovals.Authorized='1' AND LeaveApprovals.Recalled IS NULL AND (leaveApprovals.AuthDateExpected >='{$today}' AND leaveApprovals.authStartDate <='{$today}') AND (LeaveApprovals.Cancelled IS NULL OR LeaveApprovals.Cancelled='N') AND ParamLeaveTypes.LeaveType  = LeaveApprovals.LeaveType AND ParamcompanyDepts.DeptCode='{$DeptCode}' ORDER BY LeaveApprovals.AppNum";
			$query2 = $this->db->query($query);
			return $query2->result();	
		}
		public Function AllowedToRecall($StaffIDNO,$strDeptCode)
		{
			$AllowedToRecall = false;
			$query = $this->db->query("SELECT Count(StaffIDNo) AS TCount FROM LeaveSetApprovalData WHERE StaffIDNo='{$StaffIDNO}' AND Recall='1' and DeptCode='{$strDeptCode}'"); 			
			if($query->result()[0]->TCount>=1)
			{
				$AllowedToRecall = true;
				
			}else{
				$AllowedToRecall = false;
				
			}

			return $AllowedToRecall;
		}
		Public Function GetNextApplicationNo()
		{
			$strLastID = "SELECT MAX(AppNum) AS lastid FROM LeaveApplications";
			$query = $this->db->query($strLastID);
			$result = $query->result();
			foreach($result as $key);
			$strTemp = $key->lastid;
			$strTemp = $strTemp+1;
            $GetNextIndentNumber = $strTemp;
			return $GetNextIndentNumber;	
		}
		public function AllowNegativeDays($LeaveType)
		{
			
			$query = $this->db->query("SELECT AllownegativeLeaveDays FROM ParamLeaveTypes WHERE  Leavetype='{$LeaveType}'"); 			
			if($query->result()[0]->AllownegativeLeaveDays=='Y')
			{
				$AllownegativeLeaveDays = true;
			}else{
				$AllownegativeLeaveDays = false;
			}
			return $AllownegativeLeaveDays;
		}
		
	public function CheckleaveDaysPreApprovalStatus($LeaveType,$myStaffID)
		{
			$PreApprovalTransNo ="";
			$NegDaysPreApproved ="False";
			$WaitingPeriodPreApproved ="False";
			$NoExperiencePreApproved ="False";
			$query = $this->db->query("SELECT * FROM ParamEmpMaster, LeavePreApproval WHERE  ParamEmpMaster.StaffIdNo = LeavePreApproval.StaffIdNo and LeavePreApproval.staffIDNo='{$myStaffID}' AND LeavePreApproval.LeaveType='{$LeaveType}' AND ParamEmpMaster.EmpStatus = '1' and ParamEmpMaster.LVStatus is  NULL and ParamEmpMaster.DateHired <= dbo.stripTimeFromDate(getDate()) AND LeavePreApproval.Applied='N' ");
			foreach($query->result() as $key)
			{
				if($key->OverideNegativeDays=='Y' && $key->OverideAll=='Y')
				{
					$NegDaysPreApproved = "True";
					$PreApprovalTransNo =$key->TransactionNo;
				}else{
					$NegDaysPreApproved ="False";
				}
				
				if($key->OverideWaitingPeriod=='Y' && $key->OverideAll=='Y')
				{
					$WaitingPeriodPreApproved = "True";
					$PreApprovalTransNo =$key->TransactionNo;
				}else{
					$WaitingPeriodPreApproved ="False";
				}
				
				if($key->OverideAll=='Y')
				{
					$NoExperiencePreApproved = "True";
					$PreApprovalTransNo =$key->TransactionNo;
				}else{
					$NoExperiencePreApproved ="False";
				}
			}
			$array = array('PreApprovalTransNo'=>$PreApprovalTransNo,'NegDaysPreApproved'=>$NegDaysPreApproved,'WaitingPeriodPreApproved'=>$WaitingPeriodPreApproved,'NoExperiencePreApproved'=>$NoExperiencePreApproved);
			
			return $array;
		}
	public function LeaveSettings($LeaveType)
		{
			$query = $this->db->query("SELECT * FROM ParamLeaveTypes WHERE  Leavetype='{$LeaveType}'"); 
			$LimitDaysForApplication = "";
			$NumDaysApplied = "";
			$MaxDaysForApplication = "";
			$MinDaysForApplication = "";
			foreach($query->result() as $key);
			
				$LimitDaysForApplication = $key->LimitDaysForApplication;
				$MaxDaysForApplication = $key->MaxDaysForApplication;
				$MinDaysForApplication = $key->MinDaysForApplication;
				$array = array(
				'LimitDaysForApplication'=>$LimitDaysForApplication,
				'NumDaysApplied'=>$NumDaysApplied,
				'MaxDaysForApplication'=>$MaxDaysForApplication,
				'MinDaysForApplication'=>$MinDaysForApplication
				);
			   
			
			 return $array;
		}
		
	public function validateApplication($LeaveType,$StaffIDNO)
		{
			$NumDaysApplied = $this->input->post('NumDaysApplied');
			$LeaveSettings = $this->LeaveSettings($LeaveType);
			$MaxDaysForApplication = $LeaveSettings['MaxDaysForApplication'];
			$LimitDaysForApplication = $LeaveSettings['LimitDaysForApplication'];
			$MinDaysForApplication = $LeaveSettings['MinDaysForApplication'];
			$query = $this->db->query("SELECT * FROM ParamLeaveTypes WHERE LeaveType='{$LeaveType}'");
			$Leave = $query->result()[0]->Descriptions;
			$ReverseTallying = $this->GetCurrentLeaveInfo($Leave,$StaffIDNO)[0]->ReverseTallying;
			$LeaveCfwd = $this->GetCurrentLeaveInfo($Leave,$StaffIDNO)[0]->LeaveCfwd;
			$CheckleaveDaysPreApprovalStatus=$this->CheckleaveDaysPreApprovalStatus($LeaveType,$StaffIDNO);
			$NegDaysPreApproved = $CheckleaveDaysPreApprovalStatus['NegDaysPreApproved'];
			$state = false;
			if($LimitDaysForApplication=="Y" && $NumDaysApplied>$MaxDaysForApplication)
			{
				
				echo "You cannot Apply for more than ".$MaxDaysForApplication." Days..";
				$state= false;
			}elseif($LimitDaysForApplication=='Y' && $NumDaysApplied<$MinDaysForApplication)
			{
				
				echo "You cannot Apply for Less than ".$MinDaysForApplication." Days..";
				$state = false;
			}elseif($NumDaysApplied > $LeaveCfwd && $ReverseTallying=='N'){
				
				
				if ($this->AllowNegativeDays($LeaveType)==false)
				{
					if($NegDaysPreApproved=='false')
					{
						echo "The Leave Days You applied for Are Greater than Your Current Leave Balances.Please Contact the HR department";
						$state = false;
					}else{
						$state = true;
					
					}
				}else{
						$state = true;
						
					}
			}else{
				$state =  true;
				
					}
		return $state;					
		}
	public function NewApplication($LeaveType,$StaffIDNO)
		{
				$strCurrentLeave ="";
				$strLeaveEarned ="";
				$strLeaveEntitlement ="";
				$strLeaveBfwd ="";	
				$strLeaveTaken ="";
				$strLeaveCfwd ="";
				$strAppNumber = $this->GetNextApplicationNo();
				$blnIsApplying="True";
				$sendApplication=false;
			$query = $this->db->query("SELECT * FROM ParamLeaveTypes WHERE LeaveType='{$LeaveType}'");
			$Leave = $query->result()[0]->Descriptions;
			$ReverseTallying = $this->GetCurrentLeaveInfo($Leave,$StaffIDNO)[0]->ReverseTallying;	
			if($this->validateApplication($LeaveType,$StaffIDNO)==true)
			{
			$EmpArray = $this->GlobalRecordCheck('ParamEmpMaster',$StaffIDNO,'StaffIDNo');
			if($EmpArray!=null)
			{
			foreach($EmpArray as $key);
			$FirstApprover=$key->FirstApprover;	
			$secondApprover=$key->SecondApprover;	
			$ThirdApprover=$key->ThirdApprover;
			$AltFirstApprover = $key->AltFirstApprover;
			$AltSecondApprover= $key->AltSecondApprover;
			$AltThirdApprover = $key->AltThirdApprover;
			
			$txtPostalAddress = $key->PostalAddress;
			$cboTownCity = $key->ContTownCity;
			$txtPhoneNo = $key->ContTelephone;
			$txtMobileNo = $key->contmobile;
			$txtEmailAddress = $key->ContEmail;
			$txtPhyAddress = $key->PhysicalAddress;
			$CurrentUserName = $StaffIDNO;
			$cboCountry = $key->ContCountry;
			
			$SecondApproverEmail ="";
			$SecondApproverAllNames = "";
			$AltSecondApproverAllNames="";
			$AltSecondApproverEmail="";
			$ThirdApproverAllNames = "";
			$ThirdApproverEmail = "";
			$AltThirdApproverEmail="";
			$AltThirdApproverAllNames ="";
			$FirstApproverEmail = $this->GlobalRecordCheck('ParamEmpMaster',$FirstApprover,'StaffIDNo')[0]->ContEmail;
			if($this->GlobalRecordCheck('ParamEmpMaster',$secondApprover,'StaffIDNo')!=null)
			{
			$SecondApproverEmail = $this->GlobalRecordCheck('ParamEmpMaster',$secondApprover,'StaffIDNo')[0]->ContEmail;
			}
			
			if($this->GlobalRecordCheck('ParamEmpMaster',$AltFirstApprover,'StaffIDNo')!=null)
			{
			$AltFirstApproverEmail = $this->GlobalRecordCheck('ParamEmpMaster',$AltFirstApprover,'StaffIDNo')[0]->ContEmail;
			$AltFirstApproverAllNames = $this->GlobalRecordCheck('ParamEmpMaster',$AltFirstApprover,'StaffIDNo')[0]->AllNames;

			}
			
			$deptCode = $this->GlobalRecordCheck('ParamEmpMaster',$FirstApprover,'StaffIDNo')[0]->DeptCode;
			$AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$FirstApprover,'StaffIDNo')[0]->AllNames;
			if($this->GlobalRecordCheck('ParamEmpMaster',$secondApprover,'StaffIDNo')!=null)
			{
			$SecondApproverAllNames = $this->GlobalRecordCheck('ParamEmpMaster',$secondApprover,'StaffIDNo')[0]->AllNames;
			}
			if($this->GlobalRecordCheck('ParamEmpMaster',$AltSecondApprover,'StaffIDNo')!=null)
			{
			$AltSecondApproverAllNames = $this->GlobalRecordCheck('ParamEmpMaster',$AltSecondApprover,'StaffIDNo')[0]->AllNames;
			$AltSecondApproverEmail = $this->GlobalRecordCheck('ParamEmpMaster',$AltSecondApprover,'StaffIDNo')[0]->ContEmail;
			}
			
			if($this->GlobalRecordCheck('ParamEmpMaster',$ThirdApprover,'StaffIDNo')!=null)
			{
			$ThirdApproverAllNames = $this->GlobalRecordCheck('ParamEmpMaster',$ThirdApprover,'StaffIDNo')[0]->AllNames;
			$ThirdApproverEmail = $this->GlobalRecordCheck('ParamEmpMaster',$ThirdApprover,'StaffIDNo')[0]->ContEmail;
			}
			if($this->GlobalRecordCheck('ParamEmpMaster',$ThirdApprover,'StaffIDNo')!=null)
			{
			$AltThirdApproverAllNames = $this->GlobalRecordCheck('ParamEmpMaster',$AltThirdApprover,'StaffIDNo')[0]->AllNames;
			$AltThirdApproverEmail = $this->GlobalRecordCheck('ParamEmpMaster',$AltThirdApprover,'StaffIDNo')[0]->ContEmail;
			}
			$checkApprovalStatus = $this->checkApprovalStatus($FirstApprover,$deptCode);
				 if($FirstApprover=="")
				 {
					 $responceMSG = "Your First Approver has not been set. Your leave application will not go through. Kindly contact the HR Department";
				 }elseif($FirstApproverEmail=="")
				 {
					  $responceMSG = "Your First Approver's email address has not been set. Your leave application will not go through. Kindly contact the HR Department"; 
				 }elseif($checkApprovalStatus['AllowedToApprove']=="False")
				 {
					  $responceMSG = "The person set as Your First Approver[" .$AllNames. "] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";  
				 }else
				 {
						$FirstApproverOnLeave = $this->ApproverOnleave($FirstApprover);
						$SecondApproverOnLeave = $this->ApproverOnleave($secondApprover);
						$ThirdApproverOnLeave = $this->ApproverOnleave($ThirdApprover);
						if($FirstApproverOnLeave==true)
						{
					
							$checkApprovalStatus = $this->checkApprovalStatus($AltFirstApprover,$deptCode);
							if($AltFirstApprover=="" ||$AltFirstApprover==$FirstApprover)
							{
								 $responceMSG = "Your First Approver [".$AllNames."] is On Leave and the Alternate Approver has not been set, Your leave application will not go through. Kindly contact the HR Department.";
								$sendApplication=false;
							}elseif($AltFirstApproverEmail=="")
							{
								 $responceMSG = "Your Alternate First Approver's email address has not been set. Your leave application will not go through. Kindly contact the HR Department";
								$sendApplication=false;
							}elseif($checkApprovalStatus['AllowedToApprove']=="False")
							{
								 $responceMSG = "The person set as Your Alternate First Approver[" .$AltFirstApproverAllNames. "] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";
								$sendApplication=false;
							}else
							{
								 $responceMSG = "Your First Approver [".$AllNames. "] is On Leave, Your leave application will be redirected to " .$AltFirstApproverAllNames;
								$FirstApprover = $AltFirstApprover;
							}
						}else
						{
							$query = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveSetApprovalData A  WHERE E.StaffIDNo='.{$FirstApprover}.' AND A.DeptCode='.{$deptCode}.' AND A.FirstApproval='1' AND E.StaffidNo=A.StaffIDNo ");
							if($query->result()!=null)
							{
								 $responceMSG = "The person set as Your First Approver[" .$AllNames. "] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";
								$sendApplication=false;
							}else
							{
								$sendApplication=false;
							}
						}
						$checkApprovalStatus = $this->checkApprovalStatus($secondApprover,$deptCode);
						if($this->CheckStaffApprovalLevels($StaffIDNO)>1 && $FirstApprover != $secondApprover)
						{
							if($secondApprover=="")
							{
								 $responceMSG = "Your Second Approver has not been set. Your leave application will not go through. Kindly contact the HR Department";
								$sendApplication=false;
							}elseif($SecondApproverEmail=="")
							{
								 $responceMSG = "Your Second Approver's email address has not been set. Your leave application will not go through. Kindly contact the HR Department";
								$sendApplication=false;
							}elseif($checkApprovalStatus['AllowedToSecondApprove']=="False")
							{
								 $responceMSG = "The person set as Your Second Approver[" .$SecondApproverAllNames. "] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";
								$sendApplication=false;
							}else
							{
								if($SecondApproverOnLeave=="True")
								{
									$checkApprovalStatus = $this->checkApprovalStatus($AltSecondApprover,$deptCode);
									if($AltSecondApprover=="")
									{
										 $responceMSG = "Your Alternate Second Approver [" .$AltSecondApproverAllNames. "] is On Leave and the alternate Second Approver has not been set, Kindly Contact the HR Department for advice";
										$sendApplication=false;
									}elseif($AltSecondApproverEmail=="")
									{
										 $responceMSG = "Your Alternate Second Approver's email address has not been set. Your leave application will not go through. Kindly contact the HR Department";
										$sendApplication=false;
									}elseif($checkApprovalStatus['AllowedToSecondApprove']=="False")
									{
										 $responceMSG = "The person set as Your Alternate second Approver[" .$AltSecondApproverAllNames. "] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";
									}else
									{
									$query = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveSetApprovalData A  WHERE E.StaffIDNo='{$AltSecondApprover}' AND A.DeptCode='{$deptCode}' AND A.SecondApproval='1' AND E.StaffidNo=A.StaffIDNo");
										if($query->result()==null)
										{
											 $responceMSG = "The person set as Your Alternate Second Approver [ " .$AltSecondApprover. "] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";
										}else
										{
											 $responceMSG = "Your Second Approver [".$SecondApproverAllNames. "] is On Leave, Your leave application will be redirected to ".$AltSecondApprover;
											$SecondApprover=$AltSecondApprover;
																				
											$sendApplication=true;
										}
									}
								}
							}
						}
						if($this->CheckStaffApprovalLevels($StaffIDNO)>2 && $SecondApprover != $ThirdApprover)
						{
							$checkApprovalStatus = $this->checkApprovalStatus($ThirdApprover,$deptCode);
							if($ThirdApprover=="")
							{
								 $responceMSG = "Your Third Approver has not been set. Your leave application will not go through. Kindly contact the HR Department";
								$sendApplication=false;
							}elseif($ThirdApproverEmail=="")
							{
								 $responceMSG = "Your Third Approver's email address has not been set. Your leave application will not go through. Kindly contact the HR Department";
								$sendApplication=false;
							}elseif($checkApprovalStatus['AllowedToAuthorize']=="false")
							{
								 $responceMSG = "The person set as Your Third Approver[".$ThirdApproverAllNames. "] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";
							}else
							{
								if($ThirdApproverOnLeave==true)
								{
									$checkApprovalStatus = $this->checkApprovalStatus($AltThirdApprover,$deptCode);
									if($AltThirdApprover=="")
									{
										 $responceMSG = "Your Third Approver [" .$ThirdApproverAllNames."] is On Leave and the alternate Third Approver has not been set, Kindly Contact the HR Department for advice....";
										$sendApplication=false;
									}elseif($AltThirdApproverEmail=="")
									{
										 $responceMSG = "Your Alternate Third Approver's email address has not been set. Your leave application will not go through. Kindly contact the HR Department";
										$sendApplication=false;
									}elseif($checkApprovalStatus['AllowedToAuthorize']=="false")
									{
										 $responceMSG = "The person set as Your Alternate Third Approver[" .$AltThirdApproverAllNames. "] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";
										$sendApplication=false;
									}else
									{
										$query = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveSetApprovalData A  WHERE E.StaffIDNo='{$ThirdApprover}' AND A.DeptCode='{$deptCode}' AND A.ThirdApproval='1' AND E.StaffidNo=A.StaffIDNo");
										if($query->result()==null)
										{
											 $responceMSG = "The person set as Your Third Approver [ ".$ThirdApproverAllNames. " ] does not have the rights to approve Leave, Your leave application will not go through. Kindly contact the HR department for advice";
										}else
										{
											 $responceMSG = "Your Third Approver [".$ThirdApproverAllNames."] is On Leave, Your leave application will be redirected to " .$AltThirdApproverAllNames;
											$ThirdApprover  = $AltThirdApprover;
																				
											$sendApplication=true;
										}
									}
							
								}
							}
								
						}
				 }
				 
				$FirstApproverOnLeave = $this->ApproverOnleave($FirstApprover);
				
				if($this->CurrentlyApplied($LeaveType,$StaffIDNO)==false)
				{
					$strAppNumber = $this->GetNextApplicationNo();
					$strStartdate = $this->cleanData($this->input->post('strStartdate'));
					$strLastDate = $this->cleanData($this->input->post('strLastDate'));
					$strDateExpected = $this->cleanData($this->input->post('strDateExpected'));
					$strLeaveCfwd = $this->cleanData($this->input->post('strLeaveCfwd'));
					if($strLeaveCfwd=="")
					{
					$strLeaveCfwd = 0;	
					}
					$txtDaysApplied = $this->cleanData($this->input->post('txtDaysApplied'));
					$LeaveBFwd = $this->cleanData($this->input->post('LeaveBFwd'));
					$strDateApplied= date('Y-m-d');
					$NumDaysApplied = $txtDaysApplied;
					$chkRequireLeaveAllowance = $this->cleanData($this->input->post('chkRequireLeaveAllowance'));
					$txtComments = $this->cleanData($this->input->post('txtComments'));
					if($ReverseTallying=='Y')
					{
						$LeaveCFwd = $LeaveBFwd + $NumDaysApplied;
					}else
					{
						$LeaveCFwd = $strLeaveCfwd-$txtDaysApplied;
					}
							$aA = $strDateApplied;
							$bB = $strStartdate;
							$CC = $strLastDate;
							$dD = $strDateExpected;	
							$dC = $chkRequireLeaveAllowance;
							$strCurrentYear = date('Y');
					
							$intranet = "Mobile";
							$MyCurrentPeriod = date('Y/m');
				$data = array("AppNum"=>$strAppNumber,
							"staffidno" =>$StaffIDNO,
							"leavetype" =>$LeaveType,
							"deptcode" =>$deptCode,
							"currentyear" =>$strCurrentYear,
							"dateapplied" =>$aA,
							"startdate" =>$bB,
							"currentLeave" =>$strLeaveCfwd,
							"daysapplied" =>$NumDaysApplied,
							"lastdate" =>$CC,
							"dateexpected" =>$dD,
							"postaladdress" =>$txtPostalAddress,
							"towncity" =>$cboTownCity,
							"countrycode" =>$cboCountry,
							"phoneno" =>$txtPhoneNo,
							"mobileno" =>$txtMobileNo,
							"emailaddress" =>$txtEmailAddress,
							"phyaddress" =>$txtPhyAddress,
							"compname" =>$intranet,
							"createdby" =>$CurrentUserName,
							"datecreated" =>date('Y-m-d'),
							"LeaveCFWD" =>$LeaveCFwd,
							"FirstApprover" =>$FirstApprover,
							"SecondApprover" =>$secondApprover,
							"ThirdApprover" =>$ThirdApprover,
							"CurrentPeriod" =>date('Y/m'),
							"RequireLeaveAllowance" =>$dC,
							"Comment" =>$txtComments
							);
							$this->db->insert("LeaveApplications",$data);
							$data = array("AppNum"=>$strAppNumber,
										"StaffIDNo"=>$StaffIDNO,	
										"CurrentYear"=>$strCurrentYear,	
										"CurrentPeriod"=>date('Y/m'),
										"LeaveType"=>$LeaveType,
										"DaysApplied"=>$NumDaysApplied,
										"FirstApprover"=>$FirstApprover,
										"SecondApprover"=>$secondApprover,
										"ThirdApprover"=>$ThirdApprover,
										"AttachedDoc"=>""
										);
										$this->db->insert("LeaveApprovals",$data);
							if($dC==1)
							{
						$this->ProcessLeaveAllowance($StaffIDNO,$bB,$strAppNumber,$NumDaysApplied);
							}
							$this->SendMailToApprover($StaffIDNO,$strAppNumber);
							
						if($this->CheckStaffApprovalLevels($StaffIDNO)>1 && $FirstApprover != $secondApprover)
						{
							$this->SendMailToSecondApprover($StaffIDNO,$strAppNumber);
						}
						if($this->CheckStaffApprovalLevels($StaffIDNO)>2 && $secondApprover != $ThirdApprover)
						{
							$this->SendMailToThirdApprover($StaffIDNO,$strAppNumber);
						}
						
						$responceMSG = "Leave Application Successful";
						
						
				}
				
				}
				
			}
			return  $responceMSG;
		}
	public function SendMailToApprover($StaffIDNo,$strAppNumber)
		{
			 $strApprover = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveSetApprovalData A  WHERE E.StaffIDNo='{$StaffIDNo}' AND E.StaffidNo=A.StaffIDNo");
			 if($strApprover->result()==null)
			 {
				 return;
			 }
			 $rsFindRecord = $this->db->query("SELECT * FROM LeaveSetCompanyData");
			 if($rsFindRecord->result()==null)
			 {
				 return;
			 }
			 $EmpArray = $this->GlobalRecordCheck('ParamEmpMaster',$StaffIDNo,'StaffIDNo');
			
			foreach($EmpArray as $key);
			$FirstApprover=$key->FirstApprover;	
			$secondApprover=$key->SecondApprover;	
			$ThirdApprover=$key->ThirdApprover;
			$AltFirstApprover = $key->AltFirstApprover;
			$From= $key->ContEmail;
			$FirstApproverOnLeave = $this->ApproverOnleave($FirstApprover);
			$SecondApproverOnLeave = $this->ApproverOnleave($secondApprover);
			$ThirdApproverOnLeave = $this->ApproverOnleave($ThirdApprover);
				If ($FirstApproverOnLeave = True && $AltFirstApprover!="")
				{
			$strRedirectionMessage="This Leave Application Has been redirected to you since the first approver is on Leave";
				}else
				{
			$strRedirectionMessage="";
				}
				$ApprovingMessage = $rsFindRecord->result()[0]->ApprovingMessage;
			$AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$FirstApprover,'StaffIDNo')[0]->AllNames;
			$txtMessage = $this->input->post('txtMessage');
			$TextBody = "Dear ".$AllNames."<br/><br/>".$ApprovingMessage."<br/><br/>Remarks ".$txtMessage."<br/><br/> Have a Nice Day";
			$to_email = $strApprover->result()[0]->ContEmail;
			$subject = "Leave Application To Approve";
			$getDate = date('Y-m-d');
			
			if($this->send_mail($to_email,$subject,$TextBody,$From)==true)
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='Y',DateMailSent='{$getDate}' WHERE AppNum='{$strAppNumber}'");
				$strMSG="Mail Sending to Approver Finished";
			}else
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='N' WHERE AppNum='{$strAppNumber}' ");
				$strMSG="Mail Sending Failed.";
			}
			
				$strMSG="Leave Application Sent Successfully. ". $strMSG;
			$this->sendNotifications($TextBody,$FirstApprover,$subject, $strMSG);
		}	
	public function SendMailToSecondApprover($StaffIDNo,$strAppNumber)
		{
			 $strApprover = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveSetApprovalData A  WHERE E.StaffIDNo='{$StaffIDNo}' AND E.StaffidNo=A.StaffIDNo");
			 if($strApprover->result()==null)
			 {
				 
				 return;
			 }
			 $rsFindRecord = $this->db->query("SELECT * FROM LeaveSetCompanyData");
			 if($rsFindRecord->result()==null)
			 {
				 return;
			 }
			 $EmpArray = $this->GlobalRecordCheck('ParamEmpMaster',$StaffIDNo,'StaffIDNo');
			
			foreach($EmpArray as $key);
			$FirstApprover=$key->FirstApprover;	
			$secondApprover=$key->SecondApprover;	
			$ThirdApprover=$key->ThirdApprover;
			$AltFirstApprover = $key->AltFirstApprover;
			$From = $key->ContEmail;
			$FirstApproverOnLeave = $this->ApproverOnleave($FirstApprover);
			$SecondApproverOnLeave = $this->ApproverOnleave($secondApprover);
			$ThirdApproverOnLeave = $this->ApproverOnleave($ThirdApprover);
			$strApprover = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveSetApprovalData A  WHERE E.StaffIDNo='{$secondApprover}' AND E.StaffidNo=A.StaffIDNo");
			
			if($this->GlobalRecordCheck('ParamEmpMaster',$secondApprover,'StaffIDNo')!=null)
			{
			$AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$secondApprover,'StaffIDNo')[0]->AllNames;
			$ApprovingMessageToAuthorizer = $rsFindRecord->result()[0]->ApprovingMessageToAuthorizer;
			
			$txtMessage = $this->input->post('txtMessage');
			$TextBody = "Dear ".$AllNames."<br/><br/>".$ApprovingMessageToAuthorizer."<br/><br/>Remarks ".$txtMessage."<br/><br/> Have a Nice Day";
			if($strApprover->result()!=null)
			{
			$to_email = $strApprover->result()[0]->ContEmail;
			$subject = "Leave Application To Approve";
			$getDate = date('Y-m-d');
			
			if($this->send_mail($to_email,$subject,$TextBody,$From)==true)
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='Y',DateMailSent='{$getDate}' WHERE AppNum='{$strAppNumber}'");
				$strMSG="Mail Sending to Approver Finished";
			}else
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='N' WHERE AppNum='{$strAppNumber}' ");
				$strMSG="Mail Sending Failed.";
			}
			}
			
			$strMSG ="Leave Application Sent Successfully. ".$strMSG;
			$this->sendNotifications($TextBody,$secondApprover,$subject,$strMSG);
			}
			
		}
	
	public function SendMailToThirdApprover($StaffIDNo,$strAppNumber)
		{
			 $strApprover = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveSetApprovalData A  WHERE E.StaffIDNo='{$StaffIDNo}' AND E.StaffidNo=A.StaffIDNo");
			 if($strApprover->result()==null)
			 {
				 return;
			 }
			 $rsFindRecord = $this->db->query("SELECT * FROM LeaveSetCompanyData");
			 if($rsFindRecord->result()==null)
			 {
				 return;
			 }
			 $EmpArray = $this->GlobalRecordCheck('ParamEmpMaster',$StaffIDNo,'StaffIDNo');
			
			foreach($EmpArray as $key);
			$FirstApprover=$key->FirstApprover;	
			$secondApprover=$key->SecondApprover;	
			$ThirdApprover=$key->ThirdApprover;
			$AltFirstApprover = $key->AltFirstApprover;
			$From = $key->ContEmail;
			$FirstApproverOnLeave = $this->ApproverOnleave($FirstApprover);
			$SecondApproverOnLeave = $this->ApproverOnleave($secondApprover);
			$ThirdApproverOnLeave = $this->ApproverOnleave($ThirdApprover);
			$strApprover = $this->db->query("SELECT * FROM ParamEmpMaster E,LeaveSetApprovalData A  WHERE E.StaffIDNo='{$ThirdApprover}' AND E.StaffidNo=A.StaffIDNo");
 
			$ApprovingMessageToAuthorizer = $rsFindRecord->result()[0]->ApprovingMessageToAuthorizer;
			$AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$secondApprover,'StaffIDNo')[0]->AllNames;
			$txtMessage = $this->input->post('txtMessage');
			$TextBody = "Dear ".$AllNames."<br/><br/>".$ApprovingMessageToAuthorizer."<br/><br/>Remarks ".$txtMessage."<br/><br/> Have a Nice Day";
			$to_email = $strApprover->result()[0]->ContEmail;
			$subject = "Leave Application To Approve";
			$getDate = date('Y-m-d');
			
			if($this->send_mail($to_email,$subject,$TextBody,$From)==true)
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='Y',DateMailSent='{$getDate}' WHERE AppNum='{$strAppNumber}'");
				$strMSG="Mail Sending to Approver Finished";
			}else
			{
				$query = $this->db->query("Update LeaveApplications SET mailSent='N' WHERE AppNum='{$strAppNumber}' ");
				$strMSG="Mail Sending Failed.";
			}
			$this->sendNotifications($TextBody,$ThirdApprover,$subject,$strMSG);
			echo 	$strMSG="Leave Application Sent Successfully. ". $strMSG;
		}	
		 public function send_mail($to_email,$subject,$TextBody, $from_email) { 
		 $Name = $this->GlobalRecordCheck('ParamEmpMaster',$to_email,'ContEmail')[0]->AllNames;
		 
		$this->load->library('email');
		$this->email->from($from_email, $Name);//your mail address and name
		$this->email->to($to_email);
		$this->email->subject($subject);
		$this->email->message($TextBody);
         if($this->email->send()) 
		 {
			$state =  true;
		 }else
		 {
			$state = false; 
			echo "Mail has not been sent".$this->email->print_debugger();
		 }
        return $state;
      } 
	public function ApproverOnleave($strStaffID)
		{
			$state = false;
			$query = $this->db->query("Select * from ParamEmpMaster Where ParamEmpMaster.StaffIdNo = '{$strStaffID}' and ParamEmpMaster.EmpStatus = '1'");
			if($query->result()==null)
			{
			$state = true;	
			}else
			{
				if($query->result()[0]->LVStatus=='1')
				{
					$state = true;	
				}else{
					$state = false;
				}
			}
			$state = false;
			return $state;
		}
	public function checkApprovalStatus($ApproverID,$deptCode)
		{
		$query = $this->db->query("SELECT * FROM LeaveSetApprovalData WHERE StaffIDNo='{$ApproverID}' AND deptCode='{$deptCode}'");
		$AllowedToApprove ="False";
		$AllowedToAuthorize="False";
		$AllowedToCancel="False";
		$AllowedToRecall="False";
		$AllowedToSecondApprove="False";
		
		if($query->result()!=null)
		{
			foreach($query->result() as $key);
			if($key->FirstApproval=='1')
			{
				$AllowedToApprove ="True";
			}
			if($key->SecondApproval=='1')
			{
				$AllowedToSecondApprove ="True";
			}
			if($key->ThirdApproval=='1')
			{
				$AllowedToAuthorize ="True";
			}
			if($key->Cancel=='1')
			{
				$AllowedToCancel ="True";
			}
			if($key->Recall=='1')
			{
				$AllowedToRecall ="True";
			}
		}
		$array = array('AllowedToApprove'=>$AllowedToApprove,'AllowedToAuthorize'=>$AllowedToAuthorize,'AllowedToCancel'=>$AllowedToCancel,'AllowedToRecall'=>$AllowedToRecall,'AllowedToSecondApprove'=>$AllowedToSecondApprove);
		
		return $array;
		}
	
	public function CurrentlyApplied($strLeaveType,$StaffIDNo)
		{
			$query = $this->db->query("SELECT COUNT(AppNum) AS TCOunt FROM LeaveApplications WHERE StaffIDNo='{$StaffIDNo}' AND LeaveType='{$strLeaveType}' AND (Authorized IS NULL ) AND (Approved IS NULL OR Approved='1') and Cancelled is NULL ");
			
			if($query->result()[0]->TCOunt>=1)
			{
				$CurrentlyApplied = true;
			}else
			{
				$CurrentlyApplied = false;
			}
			
			if($CurrentlyApplied==true)
			{
				$AllNames = $this->GlobalRecordCheck('ParamEmpMaster',$StaffIDNo,'StaffIDNo')[0]->AllNames;
				$Leave = $this->GlobalRecordCheck('ParamLeaveTypes',$strLeaveType,'LeaveType')[0]->Descriptions;
				echo "SORRY!! There is Another Pending [" .$Leave. "] Application for: ".$AllNames." You cannot Apply Again until after Signing In/Return from that Leave or until after that Application is Rejected or Cancelled!!";
				$CurrentlyApplied = true;
			}
			return $CurrentlyApplied;
		}
	Public Function getPreviousYear($OpeningPeriod, $ClosingPeriod)
		{
				$query = $this->db->query("SELECT * FROM ParamCompanyMaster");
			if($query->result()==null)
			{
				$getPreviousYear ="";
			}else if($query->result()[0]->OpeningMonth=="" || $query->result()[0]->ClosingMonth=="")
			{
				$getPreviousYear= "";
			}else 
			{
				$Open = substr($OpeningPeriod,0,4)-1;
				$getPreviousYear = $Open."/".substr($ClosingPeriod,0,4)-1;
			}	
			return $getPreviousYear;
		}
	public function ProcessLeaveAllowance($strSelectedStaff,$strStartDate,$strAppNumber,$NumDaysApplied)
	{
		$dtLastDate = date('Y-m-d');
		$ClosingPeriod = $this.getClosingPeriod($dtLastDate, False);
		$OpeningPeriod = $this.getOpeningPeriod($dtLastDate, False);
		$strCurrentYear = $this.getCurrentYear($OpeningPeriod, $ClosingPeriod);
		$strPreviousYEAR = $this.getPreviousYear($OpeningPeriod, $ClosingPeriod);
		$strDeptCode = $this->GlobalRecordCheck('ParamEmpMaster',$strSelectedStaff,'StaffIDNo')[0]->DeptCode;
		$strGradeCode = $this->GlobalRecordCheck('ParamEmpMaster',$strSelectedStaff,'StaffIDNo')[0]->GradeCode;
		$strAllowanceRate = $this->GlobalRecordCheck('LeaveAllowanceRate',$strGradeCode,'GradeCode')[0]->PGrade;
		$CurrentUserName = $this->GlobalRecordCheck('AdminUserRegister',$strSelectedStaff,'StaffIDNo')[0]->UserName;
		$AllNames = $this->GlobalRecordCheck('AdminUserRegister',$strSelectedStaff,'StaffIDNo')[0]->AllNames;
		if(strlen($strAllowanceRate==0))
		{
			$strAllowanceRate = 0;
		}
		$query = $this->db->query("SELECT * FROM LeaveAllowancePayment WHERE StaffIDNo LIKE '{$strSelectedStaff}' AND CurrentYear LIKE '{$strCurrentYear}'");
		if($query->result()==null)
		{	
				$data = array("StaffIDNo"=>$strSelectedStaff,
							"DeptCode"=>$strDeptCode,
							"AppNum"=>$strAppNumber,
							"LeaveStartDate"=>$strStartDate,
							"NoofDays"=>$NumDaysApplied,
							"AllowanceRate"=>$strAllowanceRate,
							"AllowanceAmount"=>$strAllowanceRate,
							"CurrentYear"=>$strCurrentYear,
							"CurrentPeriod"=>date('Y/m'),
							"DateCreated"=>date('Y-m-d'),
							"CreatedBy"=>$CurrentUserName
							);
				$this->db->insert("LeaveAllowancePayment",$data);
				$this->db->query("UPDATE LeaveApplications SET AllowanceAmount='${strAllowanceRate}' WHERE AppNum LIKE '{$strAppNumber}'");
				//Send Mail to Relevant persons.
				$textBODY=$this->getNotificationMessage(7);
				$query = $this->db->query("SELECT * FROM LeaveManagementAccessRights WHERE ProcessLeaveAllowance='1'");
				$Subject = "Request to process Leave Allowance for " .$AllNames;
				foreach($query->result() as $key)
				{
					$EmailAddress = $this->GlobalRecordCheck('ParamEmpMaster',$key->StaffIDNo,'StaffIDNo')[0]->ContEmail;
					
					$this->SendMail($EmailAddress,$Subject,$textBODY);
				}
		}

	}
	Public Function getNotificationMessage($notificationNo)
	{
	$query = $this->db->query("SELECT * FROM LeaveNotificationConfig where NotificationNo = '{$notificationNo}' AND Active='Y'");	
	if($query->result()!=null)
	{
		$getNotificationMessage=$query->result()[0]->NotificationMessage;
	}else{
		$getNotificationMessage="";
	}
	return $getNotificationMessage;
	}

	
	public function computeLEAVEEARNED($SelectedPeriod, $StaffIDNo, $strLeaveType)
	{
		$strCurrentPERIOD=$strcurrentMONTH=$strPreviousPeriod= $dtstartDate=$dtLastDate=$ClosingPeriod=$OpeningPeriod=$OpeningMonth=$strCurrentYear=$strPreviousYear=$updatingforPrevperiod=$DaysInTheMonth=$CurrentDays=$CurrentRatio=$numLeaveBFWD=$numLeaveCFWD=$numLeaveEarned=$numLeaveEntitlement=$numDaysWorked=$numALLDays=$numLeaveDays=$numLeaveTodate=$numLeaveCancelled=$numExpiredDuration=$numTotalLeaveDays=$numApplications=$numApprovals=$numAuthorizations=$numCancellations=$numRejections=$numRecalls=$numAllowancePaid= $numLeaveDaysApplied= $numDaysRecalled= $numLeaveDaysRejected="";
		
		if($SelectedPeriod=="")
		{
		$strCurrentYear = date('Y');
		$strcurrentMONTH = date('m');
		$strCurrentPERIOD = $strCurrentYear."/".$strcurrentMONTH;
		}else
		{
			$strCurrentPERIOD = $SelectedPeriod;
		}
				
		$query = $this->db->query("SELECT * FROM PCurrentPeriod WHERE PCurrentPeriod.CurrentPeriod  = '{$strCurrentPERIOD}'");
		if($query->result()==null)
		{
			$strTransactionDate=date('Y-m-d');	
			$date=date_create($strTransactionDate);
			$daystoadd = -1*date('d')+1;
			date_add($date,date_interval_create_from_date_string($daystoadd." days"));
			$dtstartDate = date_format($date,"Y-m-d");
			$date=date_create($dtstartDate);
			date_add($date,date_interval_create_from_date_string("1 days"));
			$dtLastDate = date_format($date,"Y-m-d");
			$date2=date_create($dtLastDate);
			$diff=date_diff($date,$date2);
			$DaysInTheMonth = $diff->format("%R%a")+1;
			$dateX=date_create($strTransactionDate);
			$diff=date_diff($date,$dateX);
			$currentDAYS = $diff->format("%R%a")+1;
			$currentRATIO = $currentDAYS / $DaysInTheMonth;
			$strCurrentYear = substr($dtstartDate,0,4);
			$strcurrentMONTH = substr($dtstartDate,4,2);
			$data = array("CurrentPeriod" => $strCurrentPERIOD,
						"PreparedBy" => $CurrentUserName,
						"DatePrepared" => $strTransactionDate,
						"currentmonth" => $strcurrentMONTH,
						"CurrentYear" => $strCurrentYear,
						"StartDate" => $dtstartDate,
						"LastDate" => $dtLastDate,
						"MonthName" => $this->MonthName($strcurrentMONTH),
						"WorkingDays" => $DaysInTheMonth,
						"TransactionDate" => $strTransactionDate,
						"currentRATIO"=> $currentRATIO);
			$this->db->insert("PCurrentPeriod",$data);

		}
		
		$query = $this->db->query("SELECT * FROM PCurrentPeriod WHERE PCurrentPeriod.CurrentPeriod  = '{$strCurrentPERIOD}'");
		foreach($query->result() as $key);
		$dtstartDate = $key->StartDate;
		$dtLastDate = $key->LastDate;
		$strcurrentMONTH = $key->CurrentMonth;
		
			If ($strLeaveType == "")
			{
			$query = $this->db->query("SELECT * FROM ParamLeaveTypes  WHERE (ParamLeaveTypes.Active = 'Y' or ParamLeaveTypes.Active = '1') And (Dependent = 'N' or Dependent IS NULL) ORDER BY ParamLeaveTypes.LeaveType ASC");
			}Else{
			$query = $this->db->query("SELECT * FROM ParamLeaveTypes  WHERE (ParamLeaveTypes.Active = 'Y' or ParamLeaveTypes.Active = '1') And (Dependent = 'N' or Dependent IS NULL) AND ParamLeaveTypes.LeaveType='{$strLeaveType}' ORDER BY ParamLeaveTypes.LeaveType ASC");
			}
			
				If(!$this->UseCalenderYear())
				{
						$ClosingPeriod = $this->getClosingPeriod($dtLastDate, False);
						$OpeningPeriod = $this->getOpeningPeriod($dtLastDate, False);
						$strCurrentYear = $this->getCurrentYear($OpeningPeriod, $ClosingPeriod);
						$strPreviousYEAR = $this->getPreviousYear($OpeningPeriod, $ClosingPeriod);
				}else
				{
						$ClosingPeriod = $this->getClosingPeriod($dtLastDate, True);
						$OpeningPeriode = $this->getOpeningPeriod($dtLastDate, True);
						$strCurrentYear = substr($dtLastDate,0,4);
						$strPreviousYEAR = substr($dtLastDate,0,4)-1;
				}
				
				
				If ($SelectedPeriod == "")
				{
						$strCurrentYear = Date('Y');
						$strcurrentMONTH = Date('m');
						$strCurrentPERIOD = $strCurrentYear."/".$strcurrentMONTH;
				}else
				{
						$strCurrentPERIOD = $SelectedPeriod;
				}
				$updatingforPrevperiod = false;
				If ($SelectedPeriod=="" )
				{
					If ($strLastUpdatePeriod!= $strCurrentPERIOD && $dtstartDate > $strLastUpdateDate)
					{
						$strCurrentPERIOD = $strLastUpdatePeriod;														
						$updatingforPrevperiod = True;
					}
				}
				//This step is used when updating a past period i.e one that is not the current period
				If ($SelectedPeriod!="")
				{
					If ($SelectedPeriod != $strCurrentPERIOD)
					{
						$updatingforPrevperiod = True;
					}
				
				}				
				$strPreviousPeriod = $this->getPrevperiod($strCurrentPERIOD);
	$strPERIOD = $this->db->query("SELECT * FROM PCurrentPeriod WHERE PCurrentPeriod.CurrentPeriod  = '{$strCurrentPERIOD}'");
	if($strPERIOD->result()==null)
	{
		return false;
	}
				foreach($strPERIOD->result() as $key);
				$dtstartDate = $key->StartDate;
				$dtLastDate = $key->LastDate;
				$strcurrentMONTH = $key->CurrentMonth;
				$strCurrentYear = $key->CurrentYear;
	
		
			foreach($query->result() as $key1);
						If ($updatingforPrevperiod == True)
						{
							$DaysInTheMonth = DateDiff("d", dtstartDate, dtLastDate) + 1;
							$currentDAYS = DateDiff("d", dtstartDate, dtLastDate) + 1;
							$currentRATIO = $currentDAYS / $DaysInTheMonth;
						}else
						{
						//if mode of computation has been set to monthly
						
						If (strtoupper($key1->ModeOfAccrual) == "MN")
						{
							$date1=date_create($dtstartDate);
							$date2=date_create($dtLastDate);
							$diff=date_diff($date1,$date2);
							$DaysInTheMonth= $diff->format("%R%a")+1;
								$date1=date_create($dtstartDate);
							$date2=date_create(date('Y-m-d'));
							$diff=date_diff($date1,$date2);
							$currentDAYS= $diff->format("%R%a")+1;
								
								If ($currentDAYS = $DaysInTheMonth)
								{
										$currentRATIO = 1;
								}Else
								{
										$currentRATIO = 0;
								}
								
								
						}Else
						{
							$date1=date_create($dtstartDate);
							$date2=date_create($dtLastDate);
							$diff=date_diff($date1,$date2);
							$DaysInTheMonth= $diff->format("%R%a")+1;
							
							$date1=date_create($dtstartDate);
							$date2=date_create(date('Y-m-d'));
							$diff=date_diff($date1,$date2);
							$currentDAYS= $diff->format("%R%a")+1;
							$currentRATIO = $currentDAYS / $DaysInTheMonth;
							$dtLastDate = date('Y-m-d');
						}
						}
		
				$ClosingPeriod = $this->getClosingPeriod($dtLastDate, $this->UseCalenderYear());
				$OpeningPeriod = $this->getOpeningPeriod($dtLastDate, $this->UseCalenderYear());
				
				foreach($query->result() as $key)
				{
					if($StaffIDNo!="")
					{
					$strSTAFF = $this->db->query("SELECT * FROM ParamEmpMaster LEFT OUTER JOIN ParamLeaveConfig ON ParamEmpMaster.EmployType = ParamLeaveConfig.ContractType AND ParamLeaveConfig.LeaveType = '{$key->LeaveType}' AND ParamLeaveConfig.CurrentYear='{$strCurrentYear}' WHERE ParamEmpMaster.EmpStatus ='1' AND ParamEmpMaster.DateHired <= '{$dtLastDate}' AND PAramEMpMaster.StaffIDNo='{$StaffIDNo}'");
					}else
					{
						$strSTAFF = $this->db->query("SELECT * FROM ParamEmpMaster LEFT OUTER JOIN ParamLeaveConfig ON ParamEmpMaster.EmployType = ParamLeaveConfig.ContractType AND ParamLeaveConfig.LeaveType = '{$key->LeaveType}' AND ParamLeaveConfig.CurrentYear='{$strCurrentYear}' WHERE ParamEmpMaster.EmpStatus ='1' AND ParamEmpMaster.DateHired <= '{$dtLastDate}' ");
					}
					
					if($strSTAFF->result()==null)
					{
							If ($StaffIDNo = "") 
							{
								$strSTAFF=$this->db->query("SELECT  *,PLT.DefaultDays as Entitlement FROM  ParamEmpMaster PEM CROSS JOIN ParamLeaveTypes PLT   WHERE LeaveType='{$key->LeaveType}' and PEM.DateHired <= '{$dtLastDate}'");	
							}else
							{
								$strSTAFF=$this->db->query("SELECT  *,PLT.DefaultDays as Entitlement FROM  ParamEmpMaster PEM CROSS JOIN ParamLeaveTypes PLT   WHERE LeaveType='{$key->LeaveType}' and PEM.DateHired <= '{$dtLastDate}'  AND StaffIDNo='{$StaffIDNo}'");	
							}
							
					}
				}
		}
	
	Public Function getPrevperiod($CurrentPeriod)
		{
			$prevmonth="";
			$prevYear="";
			$prevmonth = substr($CurrentPeriod, 4,2) - 1;
			$prevYear = substr($CurrentPeriod,0, 4);
			If ($prevmonth < 1)
			{
				$prevYear = $prevYear - 1;
				$prevmonth = 12;
			}
		
			If($prevmonth = 1)
			{
				$prevmonth = "0". $prevmonth;
			}
		$getPrevperiod = $prevYear ."/". $prevmonth;
		return $getPrevperiod;
		}
		
	public function MonthName($month)
		{
			$MonthName = "";
			if($month=='01')
			{
			$MonthName = "January";	
			}elseif($month=='02')
			{
			$MonthName = "February";		
			}elseif($month=='03')
			{
			$MonthName = "March";		
			}elseif($month=='04')
			{
			$MonthName = "April";		
			}elseif($month=='05')
			{
			$MonthName = "May";		
			}elseif($month=='06')
			{
			$MonthName = "June";		
			}elseif($month=='07')
			{
			$MonthName = "July";		
			}elseif($month=='08')
			{
			$MonthName = "August";		
			}elseif($month=='09')
			{
			$MonthName = "September";		
			}elseif($month=='10')
			{
			$MonthName = "October";		
			}elseif($month=='11')
			{
			$MonthName = "November";		
			}elseif($month=='12')
			{
			$MonthName = "December";		
			}
			
			return $MonthName;
		}
	
	Public function PendingSecondApproval($MyStaffID)
	{
		$query = $this->db->query("SELECT ParamCompanyDepts.DeptName,ParamEmpMaster.Surname,ParamEmpMaster.AllNames,ParamLeaveTypes.Descriptions,LeaveApplications.* FROM ParamCompanyDepts, ParamEmpMaster,LeaveApplications,ParamLeaveTypes WHERE ParamCompanyDepts.DeptCode=ParamEmpMaster.DeptCode AND ParamEmpMaster.StaffIDNo=LeaveApplications.StaffIDNo AND LeaveApplications.Approved='1' AND LeaveApplications.SecondApproval IS NULL AND LeaveApplications.Authorized IS NULL AND  LeaveApplications.Cancelled IS NULL AND LeaveApplications.SecondApprover='{$MyStaffID}' AND ParamLeaveTypes.LeaveType = LeaveApplications.LeaveType ORDER BY LeaveApplications.AppNum DESC");
		
		return $query->result();
	}
	Public function PendingThirdApproval($MyStaffID)
	{
		$query = $this->db->query("SELECT ParamLeaveTypes.Descriptions,ParamEmpmaster.DeptCode,ParamEmpMaster.AllNames,LeaveApprovals.*,LeaveApplications.* FROM ParamEmpMaster,LeaveApprovals,ParamLeaveTypes,LeaveApplications WHERE ParamLeaveTypes.LeaveType=LeaveApprovals.LeaveType  AND ParamEmpMaster.StaffIDNo=LeaveApprovals.StaffIDNo AND LeaveApprovals.Authorized IS NULL AND LeaveApprovals.SecondApproval='1' AND LeaveApprovals.Cancelled IS NULL AND LeaveApprovals.ThirdApprover='{$MyStaffID}' AND LeaveApplications.AppNum=LeaveApprovals.AppNum ORDER BY LeaveApprovals.AppNum");
		return $query->result();
	}	
	public function SecondApproval()
	{
			$today = date('Y-m-d');
			$aA = $this->cleanData($this->input->post('ApprovedStartDate'));
			$bB=$this->cleanData($this->input->post("txtApprovedLastDate"));
			$CC=$this->cleanData($this->input->post("txtDateExpected"));
			$dD=$this->cleanData($this->input->post("chkReject"));
			$strCurrentYear = date('Y');
			$txtDaysApplied = $this->cleanData($this->input->post("txtDaysApplied"));
			$txtLeaveType = $this->cleanData($this->input->post("txtLeaveType"));
			$txtLeaveType = $this->Home_model->GlobalRecordCheck("ParamLeaveTypes",$txtLeaveType,"Descriptions")[0]->LeaveType;	
			$GetCurrentStaffID = $this->cleanData($this->input->post("GetCurrentStaffID"));
			$txtComments = $this->cleanData($this->input->post("txtComments"));
			$CurrentUserName =  $GetCurrentStaffID;
			$MyCurrentPeriod = date('Y/m');
			$txtAppNumber = $this->cleanData($this->input->post("txtAppNumber"));
			$txtStaffIDNo = $this->cleanData($this->input->post("txtStaffIDNo"));
			if($dD=='0')
			{
				$strApproved="0";
			$this->db->query("UPDATE LeaveApprovals SET currentyear='{$strCurrentYear}',daysapplied='{$txtDaysApplied}',SecondApproval='{$strApproved}',SecondApprovalBy='{$GetCurrentStaffID}',aprcomments='{$txtComments}',aprcompname='Mobile',createdby='{$CurrentUserName}',datecreated='{$today}',accperiod='{$MyCurrentPeriod}',ApprovalDate='{$today}' WHERE AppNum='{$txtAppNumber}'");
		
			
				echo "Leave Application Rejected Successfully. ";
			}else
			{
			$strApproved="1";
			
						
			$data = array("secondApprovalDate"=>$today,
						"currentyear"=>$strCurrentYear,
						"daysapplied"=>$txtDaysApplied,
						"leavetype"=>$txtLeaveType,
						"SecondApproval"=>$strApproved,
						"SecondApprovalBy"=>$GetCurrentStaffID,
						"secondApprovalDays"=>$txtDaysApplied,
						"secondApprovalStartDate"=>$aA,
						"secondApprovalLastDate"=>$bB,
						"secondApprovalDateExpected"=>$CC,
						"aprcomments"=>$txtComments,
						"aprcompname"=>'Mobile',
						"createdby"=>$CurrentUserName,
						"datecreated"=>$today,
						"accperiod"=>$MyCurrentPeriod);
			$this->db->where("AppNum",$txtAppNumber);
			$this->db->update("LeaveApprovals",$data);
						
			//=============================
	$this->db->query("UPDATE ParamEmpMaster SET LeaveDate='{$aA}',DateExpected='{$CC}' WHERE StaffIdNo='{$txtStaffIDNo}'");
			$this->db->query("UPDATE LeaveApplications SET Approved='1' WHERE AppNum='{$txtAppNumber}'");
			$strSelectedStaff=$txtStaffIDNo;
			$EmpArray = $this->GlobalRecordCheck('ParamEmpMaster',$txtStaffIDNo,'StaffIDNo');
			foreach($EmpArray as $key);
			$firstApprover = $key->FirstApprover;
			$SecondApprover = $key->SecondApprover;
			$ThirdApprover = $key->ThirdApprover;
			if(($this->CheckStaffApprovalLevels($txtStaffIDNo)==2)||($ThirdApprover==$SecondApprover))
			{
				$strAuthorized="1";
			$this->db->query("UPDATE LeaveApprovals SET SecondApproval='1', Authorized='{$strAuthorized}',AuthorizedBy='{$GetCurrentStaffID}',daysauthorized='{$txtDaysApplied}',authstartdate='{$aA}',authlastdate='{$bB}',authdateexpected='{$CC}',authcomments='{$txtComments}',authcompname='Mobile',dateauthorized='{$today}' WHERE AppNum='{$txtAppNumber}'");
			
			$this->db->query("UPDATE LeaveApplications SET Authorized='1' WHERE AppNum='{$txtAppNumber}'");
			$this->db->query("UPDATE ParamEmpMaster SET LVStatus='1', LeaveDate='{$aA}',DateExpected='{$CC}' WHERE StaffIdNo='{$txtStaffIDNo}'");
			$this->MailOnAuthorization($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID);
			//$this->SendMailToAdministrators();
			
			}	
			if($this->CheckStaffApprovalLevels($txtStaffIDNo)>2 && $SecondApprover != $ThirdApprover)
			{
				$this->SendMailToThirdApprover($txtStaffIDNo,$txtAppNumber);
			}
			
			$this->db->query("UPDATE LeaveApplications SET SecondApproval='{$strApproved}' WHERE AppNum='{$txtAppNumber}' ");
			if($dD=='0')
			{
				echo  "Leave Application Rejected Successfully. ";
				$this->MailOnRejection($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID,$txtComments);
				
			}else
			{
				$this->MailOnSecondApproval($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID);
				
			}
			
			echo "Leave Approved Successfully";
			}
			
		
					
	}
	public function FinalApproval()
	{
			$today = date('Y-m-d');
			$aA = $this->cleanData($this->input->post('ApprovedStartDate'));
			$bB=$this->cleanData($this->input->post("txtApprovedLastDate"));
			$CC=$this->cleanData($this->input->post("txtDateExpected"));
			$dD=$this->cleanData($this->input->post("chkReject"));
			$strCurrentYear = date('Y');
			$txtDaysApplied = $this->cleanData($this->input->post("txtDaysApplied"));
			$txtLeaveType = $this->cleanData($this->input->post("txtLeaveType"));
			$txtLeaveType = $this->Home_model->GlobalRecordCheck("ParamLeaveTypes",$txtLeaveType,"Descriptions")[0]->LeaveType;	
			$GetCurrentStaffID = $this->cleanData($this->input->post("GetCurrentStaffID"));
			$txtComments = $this->cleanData($this->input->post("txtComments"));
			$CurrentUserName =  $GetCurrentStaffID;
			$MyCurrentPeriod = date('Y/m');
			$txtAppNumber = $this->cleanData($this->input->post("txtAppNumber"));
			$txtStaffIDNo = $this->cleanData($this->input->post("txtStaffIDNo"));
			if($dD=='0')
			{
				$strApproved="0";
			$this->db->query("UPDATE LeaveApprovals SET Authorized='{$strApproved}',AuthorizedBy='{$GetCurrentStaffID}',authcomments='{$txtComments}',authcompname='Mobile',dateauthorized='{$today}' WHERE AppNum='{$txtAppNumber}'");
				echo "Leave Application Rejected Successfully. ";
			}else
			{
			$strApproved="1";
			
						
			$data = array("Authorized"=>$strApproved,
						"AuthorizedBy"=>$GetCurrentStaffID,
						"daysauthorized"=>$txtDaysApplied,
						"authstartdate"=>$aA,
						"authlastdate"=>$bB,
						"authdateexpected"=>$CC,
						"authcomments"=>$txtComments,
						"authcompname"=>'Mobile',
						"dateauthorized"=>$today
					);
			$this->db->where("AppNum",$txtAppNumber);
			$this->db->update("LeaveApprovals",$data);
				
			//=============================
	$this->db->query("UPDATE ParamEmpMaster SET LeaveDate='{$aA}',DateExpected='{$CC}' WHERE StaffIdNo='{$txtStaffIDNo}'");
			$this->db->query("UPDATE LeaveApplications SET Approved='1' WHERE AppNum='{$txtAppNumber}'");
			$strSelectedStaff=$txtStaffIDNo;
			$EmpArray = $this->GlobalRecordCheck('ParamEmpMaster',$txtStaffIDNo,'StaffIDNo');
			foreach($EmpArray as $key);
			$firstApprover = $key->FirstApprover;
			$SecondApprover = $key->SecondApprover;
			$ThirdApprover = $key->ThirdApprover;

			$this->db->query("UPDATE LeaveApplications SET Authorized='1' WHERE AppNum='{$txtAppNumber}'");
			
			if($dD=='0')
			{
				echo  "Leave Application Rejected Successfully. ";
				$this->MailOnRejection($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID,$txtComments);
				
			}else
			{
				$this->MailOnAuthorization($txtStaffIDNo,$txtAppNumber,$GetCurrentStaffID);
				
			}
			
			echo "Leave Approved Successfully";
			}
			
		
					
	}	
	
	}