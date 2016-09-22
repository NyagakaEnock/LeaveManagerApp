<?php
class Home extends CI_Controller 
{
		
        public function __construct()
        {	
			parent::__construct();
			$this->load->model('Home_model');
			$this->load->helper('url_helper');
			//Leave API KEY  AIzaSyCiujq0A1B-LZXJ2-rVreIZlnWSGFuLtv4
			//PUSH Notification API key AIzaSyDiWCJzXTSX328vRxqbVopwg9guSOSNxPA
        }
		public function index()
        {
		//$ParamHoliday =  $this->Home_model->Recalls();//55146
		$ParamHoliday = $this->Home_model->LeaveSummary('55146');
		
		print_r($ParamHoliday);
		
        }
		
		public function NewApplication()
		{
		$StaffIDNO = $this->Home_model->cleanData($this->input->post('StaffIDNO'));
		$Leave = $this->Home_model->cleanData($this->input->post('Leave'));
		$LeaveType = $this->Home_model->GlobalRecordCheck("ParamLeaveTypes",$Leave,"Descriptions")[0]->LeaveType;	
		$Responce = $this->Home_model->NewApplication($LeaveType,$StaffIDNO);
		print_r($Responce);
		}
		public function GlobalRecordCheck()
		{
			$table = $this->Home_model->cleanData($this->input->post('table'));
			$value = $this->Home_model->cleanData($this->input->post('value'));
			$field = $this->Home_model->cleanData($this->input->post('field'));
			$array = $this->Home_model->GlobalRecordCheck($table,$value,$field);
			$result = array();
			echo json_encode(array("result"=>$array));
		}
		public function RefreshNotifications()
		{
		$StaffIDNO = $this->Home_model->cleanData($this->input->post('StaffIDNO'));	
		$Notifications = $this->Home_model->Notifications($StaffIDNO);
		$data = array();
		$data['Notifications']=$Notifications;
		echo json_encode($data);
		}
		
		public function DeleteNotifications()
		{
		$ID = $this->Home_model->cleanData($this->input->post('ID'));
		$Notifications = $this->Home_model->DeleteNotifications($ID);
		echo "Notification Deleted Successfully";
		}
		public function Login()
		{
			$UserName = $this->Home_model->cleanData($this->input->post('txtUsername'));
			$Password2 = $this->Home_model->cleanData($this->input->post('txtPassWord'));
			$Password = $this->Home_model->GetFullDecryption($Password2);
	
			$array1 = $this->Home_model->LoginFuncx($UserName,$Password);
			$array = json_encode($array1);
			if($array1!=null)
			{
				$arrayEMP = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$array1[0]->StaffIDNo,"StaffIDNo");
				$ParamHoliday =  $this->Home_model->ParamHoliday();
				$LeaveArray = $this->Home_model->GetLeaveTypes($array1[0]->StaffIDNo);
				$TownArray =  $this->Home_model->GetTowns();
				$CoutryArray =  $this->Home_model->GetCountries();
				$LeaveApplication =  $this->Home_model->LeaveApplication($arrayEMP[0]->StaffIDNO);
				$getYears =  $this->Home_model->getYears();
				$LeaveSummary =  $this->Home_model->LeaveSummary($arrayEMP[0]->StaffIDNO);
				$LeaveDetails =  $this->Home_model->LeaveDetails($arrayEMP[0]->StaffIDNO);
				$Notifications = $this->Home_model->Notifications($arrayEMP[0]->StaffIDNO);
				$FirstApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->FirstApprover,"StaffIDNo");
				$SecondApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->SecondApprover,"StaffIDNo");
				
				$AltSecondApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->AltSecondApprover,"StaffIDNo");
				$ThirdApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->ThirdApprover,"StaffIDNo");
				$AltThirdApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->AltThirdApprover,"StaffIDNo");
				$AltFirstApprover =  $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->AltFirstApprover,"StaffIDNo");
				
				$Depts =  $this->Home_model->GlobalRecordCheck("ParamCompanyDepts",$arrayEMP[0]->DeptCode,"DeptCode");
				$Recalls = array();
				$Designations =$this->Home_model->GlobalRecordCheck("ParamDesignations",$arrayEMP[0]->JobCode,"DesCode");
				$PendingSecondApproval = $this->Home_model->PendingSecondApproval($arrayEMP[0]->StaffIDNO);
				$PendingThirdApproval = $this->Home_model->PendingThirdApproval($arrayEMP[0]->StaffIDNO);
				
				foreach($this->Home_model->Recalls($arrayEMP[0]->DeptCode) as $key)
				{
					$AllowedToRecall = $this->Home_model->AllowedToRecall($arrayEMP[0]->StaffIDNO,$key->DeptCode);
					if($AllowedToRecall==TRUE)
					{
						$Recalls =  $this->Home_model->Recalls($key->DeptCode);
					}
				}
				$data = array();
				$data['result']=$arrayEMP;
				$data['LeaveResult']=$LeaveArray;
				$data['TownResult']=$TownArray;
				$data['CountryResult']=$CoutryArray;
				$data['LeaveApplicationResult']=$LeaveApplication;
				$data['getYears']=$getYears;
				$data['LeaveSummary']=$LeaveSummary;
				$data['ParamHoliday']=$ParamHoliday;
				$data['LeaveDetails']=$LeaveDetails;
				$data['Recalls']=$Recalls;
				$data['Notifications']=$Notifications;
				$data['FirstApprover']=$FirstApprover;
				$data['AltFirstApprover']=$AltFirstApprover;
				$data['Depts']=$Depts;
				$data['Designations']=$Designations;
				$data['PendingSecondApproval']=$PendingSecondApproval;
				$data['PendingThirdApproval']=$PendingThirdApproval;
				$data['SecondApprover']=$SecondApprover;
				$data['AltSecondApprover']=$AltSecondApprover;
				$data['ThirdApprover']=$ThirdApprover;
				$data['AltThirdApprover']=$AltThirdApprover;
				echo json_encode($data);
			}else{
				echo "False";
			}
		}
		
		public function UploadProfilePic()
		{
			$this->Home_model->UploadProfilePic($this->input->post('StaffIDNO'));

		}
		public function RegisterGCMID()
		{
			$UniqueId = $this->Home_model->cleanData($this->input->post('UniqueId'));
			$StaffIDNO = $this->Home_model->cleanData($this->input->post('StaffIDNO'));
			$this->Home_model->RegisterGCMID($UniqueId,$StaffIDNO);
			 
			
		}
		public function FirstApproval()
		{
		$Responce = $this->Home_model->FirstApproval();	
		print_r($Responce);
		}
		public function SecondApproval()
		{
		$Responce = $this->Home_model->SecondApproval();	
		print_r($Responce);
		}
		public function FinalApproval()
		{
		$Responce = $this->Home_model->FinalApproval();	
		print_r($Responce);
		}			
		public function ChangePass()
		{
		$StaffIDNO = $this->Home_model->cleanData($this->input->post('StaffIDNO'));	
		$Current = $this->Home_model->cleanData($this->input->post('Current'));	
		$Confirm = $this->Home_model->cleanData($this->input->post('Confirm'));	
		$Current = $this->Home_model->GetFullDecryption($Current);
		$Confirm = $this->Home_model->GetFullDecryption($Confirm);
		$arrayEMP = $this->Home_model->GlobalRecordCheck2("AdminUserRegister",$Current,"Password",$StaffIDNO,"StaffIDNo");
		if($arrayEMP==null)
		{
			echo "You Entered wrong Password";
		}else
		{
			$this->db->query("UPDATE AdminUserRegister SET Password = '{$Confirm}' WHERE StaffIDNo='{$StaffIDNO}'");
			echo "Password has been Changed Successfully";
		}
		}
		public function sendNotifications()
		{
			
			$this->Home_model->sendNotifications();
			
		}
		
		public function GetCurrentLeaveInfo()
		{
			$StaffIDNO = $this->Home_model->cleanData($this->input->post('StaffIDNO'));
			$Leave = $this->Home_model->cleanData($this->input->post('Leave'));
			$GetCurrentLeaveInfo =  $this->Home_model->GetCurrentLeaveInfo($Leave,$StaffIDNO );
			$data = array();
			$data['GetCurrentLeaveInfo']=$GetCurrentLeaveInfo;
				echo json_encode($data);
		}
		
		public function Refresh()
		{
				$StaffIDNO = $this->Home_model->cleanData($this->input->post('StaffIDNO'));
				$arrayEMP = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$StaffIDNO,"StaffIDNo");
				$ParamHoliday =  $this->Home_model->ParamHoliday();
				$LeaveArray = $this->Home_model->GetLeaveTypes($StaffIDNO);
				$TownArray =  $this->Home_model->GetTowns();
				$CoutryArray =  $this->Home_model->GetCountries();
				$LeaveApplication =  $this->Home_model->LeaveApplication($arrayEMP[0]->StaffIDNO);
				$getYears =  $this->Home_model->getYears();
				$LeaveSummary =  $this->Home_model->LeaveSummary($arrayEMP[0]->StaffIDNO);
				$LeaveDetails =  $this->Home_model->LeaveDetails($arrayEMP[0]->StaffIDNO);
				$Notifications = $this->Home_model->Notifications($arrayEMP[0]->StaffIDNO);
				$FirstApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->FirstApprover,"StaffIDNo");
				$SecondApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->SecondApprover,"StaffIDNo");
				
				$AltSecondApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->AltSecondApprover,"StaffIDNo");
				$ThirdApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->ThirdApprover,"StaffIDNo");
				$AltThirdApprover = $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->AltThirdApprover,"StaffIDNo");
				$AltFirstApprover =  $this->Home_model->GlobalRecordCheck("ParamEmpMaster",$arrayEMP[0]->AltFirstApprover,"StaffIDNo");
				
				$Depts =  $this->Home_model->GlobalRecordCheck("ParamCompanyDepts",$arrayEMP[0]->DeptCode,"DeptCode");
				$Recalls = array();
				$Designations =$this->Home_model->GlobalRecordCheck("ParamDesignations",$arrayEMP[0]->JobCode,"DesCode");
				$PendingSecondApproval = $this->Home_model->PendingSecondApproval($arrayEMP[0]->StaffIDNO);
				$PendingThirdApproval = $this->Home_model->PendingThirdApproval($arrayEMP[0]->StaffIDNO);
				
				foreach($this->Home_model->Recalls($arrayEMP[0]->DeptCode) as $key)
				{
					$AllowedToRecall = $this->Home_model->AllowedToRecall($arrayEMP[0]->StaffIDNO,$key->DeptCode);
					if($AllowedToRecall==TRUE)
					{
						$Recalls =  $this->Home_model->Recalls($key->DeptCode);
					}
				}
				$data = array();
				$data['result']=$arrayEMP;
				$data['LeaveResult']=$LeaveArray;
				$data['TownResult']=$TownArray;
				$data['CountryResult']=$CoutryArray;
				$data['LeaveApplicationResult']=$LeaveApplication;
				$data['getYears']=$getYears;
				$data['LeaveSummary']=$LeaveSummary;
				$data['ParamHoliday']=$ParamHoliday;
				$data['LeaveDetails']=$LeaveDetails;
				$data['Recalls']=$Recalls;
				$data['Notifications']=$Notifications;
				$data['FirstApprover']=$FirstApprover;
				$data['AltFirstApprover']=$AltFirstApprover;
				$data['Depts']=$Depts;
				$data['Designations']=$Designations;
				$data['PendingSecondApproval']=$PendingSecondApproval;
				$data['PendingThirdApproval']=$PendingThirdApproval;
				$data['SecondApprover']=$SecondApprover;
				$data['AltSecondApprover']=$AltSecondApprover;
				$data['ThirdApprover']=$ThirdApprover;
				$data['AltThirdApprover']=$AltThirdApprover;
				echo json_encode($data);
			
		}
}
?>