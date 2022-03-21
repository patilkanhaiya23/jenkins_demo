<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DistributorController extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('CompanyModel');
        date_default_timezone_set('Asia/Kolkata');
         ini_set('memory_limit', '-1');
    }

    public function index()
    {
        $data['distributors_details']=$this->CompanyModel->getdata('distributors_details');
        $this->load->view('distributors/distributorsView',$data);
    }

    public function addDistributor()
    {
        $data['company']=$this->CompanyModel->getdata('distributors_details');
        $this->load->view('distributors/addDistributorView');
    }

    public function loadDistributor($id)
    {
        $data['company']=$this->CompanyModel->load('distributors_details',$id);
        $this->load->view('distributors/addDistributorView',$data);
    }

    public function insert(){
        $code="KIAS".(rand(100000, 999999));
        $name=trim($this->input->post('name'));
        $mobile=trim($this->input->post('mobile'));
        $telephone=trim($this->input->post('telephone'));
        $email=trim($this->input->post('email'));
        $address=trim($this->input->post('address'));
        $city=trim($this->input->post('city'));
        $state=trim($this->input->post('state'));
        $country=trim($this->input->post('country'));
        $pincode=trim($this->input->post('pincode'));
        $baseUrl=trim($this->input->post('baseUrl'));
        $databaseName=trim($this->input->post('databaseName'));
        $status=1;
        $createdAt=date('Y-m-d H:i:sa');
        $createdBy=trim($this->session->userdata['logged_in']['id']);

        $user = $this->CompanyModel->getRecordByDistributorCode('distributors_details', $distributorCode); //check user exist or not
        if(empty($user)) {
            $insertData=array(
                'code'=>$code,
                'name'=>$name,
                'mobile'=>$mobile,
                'telephone'=>$telephone,
                'email'=>strtolower($email),
                'address'=>$address,
                'city'=>$city,
                'state'=>$state,
                'country'=>$country,
                'pincode'=>$pincode,
                'baseUrl'=>$baseUrl,
                'databaseName'=>$databaseName,
                'status'=>$status,
                'createdAt'=>$createdAt,
                'createdBy'=>$createdBy
            );
           $this->CompanyModel->insert('distributors_details',$insertData); 
        }
        redirect('owner/DistributorController');
    }

    public function update(){
        $id=trim($this->input->post('id'));
        $name=trim($this->input->post('name'));
        $mobile=trim($this->input->post('mobile'));
        $telephone=trim($this->input->post('telephone'));
        $email=trim($this->input->post('email'));
        $address=trim($this->input->post('address'));
        $city=trim($this->input->post('city'));
        $state=trim($this->input->post('state'));
        $country=trim($this->input->post('country'));
        $pincode=trim($this->input->post('pincode'));
        $baseUrl=trim($this->input->post('baseUrl'));
        $databaseName=trim($this->input->post('databaseName'));
        $status=1;
        $updatedOn=date('Y-m-d H:i:sa');
        $updatedBy=trim($this->session->userdata['logged_in']['id']);

        $updateData=array(
            'name'=>$name,
            'mobile'=>$mobile,
            'telephone'=>$telephone,
            'email'=>strtolower($email),
            'address'=>$address,
            'city'=>$city,
            'state'=>$state,
            'country'=>$country,
            'pincode'=>$pincode,
            'baseUrl'=>$baseUrl,
            'databaseName'=>$databaseName,
            'status'=>$status,
            'updatedOn'=>$updatedOn,
            'updatedBy'=>$updatedBy
        );
       $this->CompanyModel->update('distributors_details',$updateData,$id);
       redirect('owner/DistributorController'); 
    }

     public function updateStatus($id, $status) {
        if($status==1) {
            $data = array('status' => 0);
            $this->CompanyModel->update('distributors_details',$data,$id);
        } else {
            $data = array('status' => 1);
            $this->CompanyModel->update('distributors_details',$data,$id);
        }
       
        if($this->db->affected_rows()>0){
           redirect('owner/DistributorController'); 
        } else {
           redirect('owner/DistributorController'); 
        }
    }

}