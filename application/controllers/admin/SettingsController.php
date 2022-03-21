<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SettingsController extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('SettingsModel');
        date_default_timezone_set('Asia/Kolkata');
        $this->load->library('session');
         ini_set('memory_limit', '-1');
    }

    public function bouncedAdhocCheques(){
        $data['days']=$this->SettingsModel->getInfo('highlighting_days');
        
        $this->load->view('admin/bouncedAdhocChequesView',$data);
    }

    public function billsHighlightingDays(){
        $data['days']=$this->SettingsModel->getInfo('highlighting_days');
        
        $this->load->view('admin/billsHighlightLimitView',$data);
    }

    public function retailersHighlightingDays(){
        $data['days']=$this->SettingsModel->getInfo('highlighting_days');
        
        $this->load->view('admin/retailerHighlightLimitView',$data);
    }

    public function allocationsHighlightingDays(){
        $data['days']=$this->SettingsModel->getInfo('highlighting_days');
        
        $this->load->view('admin/allocationsHighlightLimitView',$data);
    }

    public function chequesHighlightingDays(){
        $data['days']=$this->SettingsModel->getInfo('highlighting_days');
        
        $this->load->view('admin/chequesHighlightLimitView',$data);
    }

    public function highlightingDays(){
        $data['days']=$this->SettingsModel->getInfo('highlighting_days');
        $days=$this->SettingsModel->getInfo('highlighting_days');
        $specificCompany=$this->SettingsModel->getInfo('admin_login');
        $companyDaysForBills="";
        $companyDaysForRetailers="";
        $companyName="";
        if(!empty($specificCompany)){
            if($days[0]['id']==1){
                 $companyDaysForBills=trim($days[0]['days']);
                 $companyDaysForBills= explode(",",$companyDaysForBills);
            }

            if($days[3]['id']==4){
                 $companyDaysForRetailers=trim($days[3]['days']);
                 $companyDaysForRetailers= explode(",",$companyDaysForRetailers);
            }
            $companyName=trim($specificCompany[0]['company']);
            $companyName= explode(",",$companyName);
        }

        $data['companyName']=$companyName;
        $data['companyDaysForBills']=$companyDaysForBills;
        $data['companyDaysForRetailers']=$companyDaysForRetailers;
        $this->load->view('admin/allLimitsView',$data);
    }

    public function thresholdLimit(){
        $data['expenseLimit']=$this->SettingsModel->getInfo('expenses_limit');
        $data['resendData']=$this->SettingsModel->getInfo('resend_limit');
        $this->load->view('admin/thresholdLimitView',$data);
    }

    public function updatedDaysLimit(){
        $limitId=trim($this->input->post('id'));
        $days=trim($this->input->post('days'));
        $data=array('days'=>$days);
        $this->SettingsModel->update('highlighting_days',$data,$limitId);
        // redirect('admin/EmployeeRelationController/cashierExpensesLimit');
    }

    public function updatedCompanyDaysLimit(){
        $limitId=trim($this->input->post('id'));
        $days=trim($this->input->post('days'));
        $days = trim($days,",");
        $data=array('days'=>$days);
        $this->SettingsModel->update('highlighting_days',$data,$limitId);
        // redirect('admin/EmployeeRelationController/cashierExpensesLimit');
    }

     public function insertBillClearenceLimit(){
        $limitId=trim($this->input->post('id'));
        $amount=trim($this->input->post('amount'));
        $data=array('expenseLimit'=>$amount);
        $this->SettingsModel->update('expenses_limit',$data,$limitId);
        // redirect('admin/EmployeeRelationController/cashierExpensesLimit');
    }

    //// For Resend Limit
    public function resendLimit()
    {
        $data['resendData']=$this->SettingsModel->getInfo('resend_limit');
        $this->load->view('admin/resendBillLimitView',$data);
    }

    public function updateResendLimit(){
        $limitId=trim($this->input->post('id'));
        $recend_percent=trim($this->input->post('recend_percent'));
        $data=array('resendLimit'=>$recend_percent);
        $this->SettingsModel->update('resend_limit',$data,$limitId);
        // redirect('admin/EmployeeRelationController/cashierExpensesLimit');
    }

    public function loginTime()
    {
        $data['loginData']=$this->SettingsModel->getInfo('login_limit');
        $this->load->view('admin/addLoginTimeView',$data);
    }

    public function updatedLoginTimeLimit(){
        $userId = $this->session->userdata['logged_in']['id'];
        $id=trim($this->input->post('id'));
        $fromTime=trim($this->input->post('fromTime'));
        $toTime=trim($this->input->post('toTime'));
        $data=array(
            'fromTime'=>$fromTime,
            'toTime'=>$toTime,
            'createdBy'=>$userId
        );
        $this->SettingsModel->update('login_limit',$data,$id);
        redirect('DashbordController');
    }
    

}
?>
