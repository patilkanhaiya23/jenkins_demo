<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PenaltyController extends CI_Controller {
	
	public function __construct() {
        parent::__construct();
        $this->load->model('PenaltyModel');
        date_default_timezone_set('Asia/Kolkata');
         ini_set('memory_limit', '-1');
    }
	public function index()
	{
		$data['penalty']=$this->PenaltyModel->getdata('penalty');
		$this->load->view('admin/PenaltyView',$data);
	}


    public function srPenalty()
    {
        $data['penalty']=$this->PenaltyModel->getPenalty('penalty_sr_cash','sr');
        $this->load->view('admin/penaltySrView',$data);
    }

    public function otherPenalty(){
        $data['penalty']=$this->PenaltyModel->getdata('penalty_sr_cash');
        $this->load->view('admin/otherPenaltyView',$data);
    }

    public function insertSrPenalty(){
        $limitId=trim($this->input->post('id'));
        $amount=trim($this->input->post('value'));
        $percentOrFixed =trim($this->input->post('radioValue'));
        $data=array('percentOrFixed'=>$percentOrFixed,'multiplier'=>$amount,'type'=>'sr');
        $this->PenaltyModel->update('penalty_sr_cash',$data,$limitId);

        $penalty=$this->PenaltyModel->load('penalty_sr_cash',$limitId);
        
    }

    public function cashPenalty()
    {
        $data['penalty']=$this->PenaltyModel->getPenalty('penalty_sr_cash','cash');
        $this->load->view('admin/penaltyCashView',$data);
    }

    public function insertCashPenalty(){
        $limitId=trim($this->input->post('id'));
        $amount=trim($this->input->post('value'));
        $percentOrFixed =trim($this->input->post('radioValue'));
        $data=array('percentOrFixed'=>$percentOrFixed,'multiplier'=>$amount,'type'=>'cash');
        $this->PenaltyModel->update('penalty_sr_cash',$data,$limitId);

        $penalty=$this->PenaltyModel->load('penalty_sr_cash',$limitId);
        
    }


	public function Add()
	{
		$this->load->view('admin/AddPenaltyView');
	}
	public function insert()
    {
        $data = array('name' => $this->input->post('name'),
            'amount' => $this->input->post('amount')          
             ); 
        $this->PenaltyModel->insert('penalty',$data); 
       	if($this->db->affected_rows()>0){                
            redirect("admin/PenaltyController");
        }   
        else{
            redirect("admin/PenaltyController");
        }
    }
    public function load($id) 
    {
        $data['penalty']=$this->PenaltyModel->load('penalty', $id);
        $this->load->view('admin/AddPenaltyView',$data);
    }
    public function update() {
        $id = $this->input->post('id');
            $data = array('name' => $this->input->post('name'),
                     'amount' => $this->input->post('amount')     
                    ); 
            $this->PenaltyModel->update('penalty',$data, $id);
            if($this->db->affected_rows()>0){       
                redirect("admin/PenaltyController");
            }else {
                redirect("admin/PenaltyController");
            }
    }
    public function delete()
    {
        $id =$this->input->post('id');
        $this->PenaltyModel->delete('penalty',$id);  
        if($this->db->affected_rows()>0){  
            echo "Your record has been deleted!";                
        }else{
            echo "Deleted Fail..";
        }
    }  

    //// For Expenses Limit

    public function expenseLimit()
    {
        $data['expenseLimit']=$this->PenaltyModel->getdata('expenses_limit');
        $this->load->view('admin/admin_cashierExpensesLimitView',$data);
    }

     

    public function insertExpenseLimit(){
        $updatedBy = $this->session->userdata['logged_in']['id'];
        $updatedAt=date('Y-m-d H:i:sa');

        $limitId=trim($this->input->post('id'));
        $expLimit=trim($this->input->post('expLimit'));
        $data=array('expenseLimit'=>$expLimit,'updatedBy'=>$updatedBy,'updatedAt'=>$updatedAt);
        
        $this->PenaltyModel->update('expenses_limit',$data,$limitId);
    }
}
