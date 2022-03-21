<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RetailerController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->model('RetailerModel');
        date_default_timezone_set('Asia/Kolkata');
         ini_set('memory_limit', '-1');
    }

    public function index(){
      $getRetailerInfo=$this->RetailerModel->getdata('retailer_kia');
      $data['retailerCode']="KIA100".count($getRetailerInfo);
        
    	$data['retailer']=$this->RetailerModel->getAllRetailers('retailer_kia');
        // $data['blockRetailer']=$this->RetailerModel->getBlockRetailers('retailer_kia');
    	$this->load->view('RetailerView',$data);
    }

    public function blockedRetailers(){
    	$data['retailer']=$this->RetailerModel->getRetailers('retailer_kia');
        $data['blockRetailer']=$this->RetailerModel->getBlockRetailers('retailer_kia');
    	$this->load->view('blockedRetailerView',$data);
    }

    public function Add(){
        $data['retNames']=$this->RetailerModel->getName('retailer');
        $data['emp']=$this->RetailerModel->getdata('employee');
         $data['route']=$this->RetailerModel->getData('route');
    	$this->load->view('AddRetailerView',$data);
    }

    public function load($id) 
    {
        $data['retailer']=$this->RetailerModel->load('retailer', $id);
        $this->load->view('AddRetailerView',$data);
    }

    public function insert()
    {
        $retailerName=$this->input->post('retailerName');
        $area=$this->input->post('area');
        $retailerCode=$this->input->post('retailerCode');

        // $getRetailerInfo=$this->RetailerModel->getdata('retailer_kia');
        // $retailerCode="KIA100".count($getRetailerInfo);
        
        $retailerExist=$this->RetailerModel->getDetails('retailer_kia',$retailerName);
        if(empty($retailerExist)){//check route present or not
            $retailerData = array('name' => $retailerName,'retailerCode'=>$retailerCode,'area'=> $area,'isActive'=>'1');
            $this->RetailerModel->insert('retailer_kia',$retailerData); 
            if($this->db->affected_rows()>0){
                echo "Record Added";
            }else{
                echo "Unable to Insert Record";
            }
        }else{
            echo "Retailer Name already Present";
        }
        
    }
   
    public function update() {
        $id = $this->input->post('id');
             $data = array
            ('name' => $this->input->post('prodName')
             );  
             $this->RetailerModel->update('retailer',$data, $id);
            if($this->db->affected_rows()>0){
                return redirect("RetailerController");
            } else {
                echo "Fail";
            }
    }
    
     public function updateRetailerDetail() {
        $rtId = trim($this->input->post('retailerId'));
        $name=trim($this->input->post('retailerName'));
        $area=trim($this->input->post('area'));
        $retailerCode=trim($this->input->post('retailerCode'));

        $retailerExist=$this->RetailerModel->getDetails('retailer_kia',$name);
        if(!empty($retailerExist)){
            echo "Retailer already present.";
            exit;
        }
        
        $data = array('name' => $name,'area'=>$area);  
        $this->RetailerModel->update('retailer_kia',$data, $rtId);
        // return redirect("RetailerController");
        if($this->db->affected_rows()>0){
            $data = array('retailerName' => $name);  
            $this->RetailerModel->updateByRetailerId('bills',$data, $rtId);
            echo "Record updated";
        } else {
            echo "Record not updated";
        }
    }
    
    
    public function deactivateRetailer($id)
    {
        $up=array('isActive'=>0);
        $this->RetailerModel->update('retailer_kia',$up,$id);  
        if ($this->db->affected_rows()>0)
        {
          return redirect("RetailerController");                   
        }
        else
        {
            echo "Deleted Fail..";
        }
    }
    
    public function activateRetailer($id)
    {
        $up=array('isActive'=>1);
        $this->RetailerModel->update('retailer_kia',$up,$id);  
        if ($this->db->affected_rows()>0)
        {
          return redirect("RetailerController");                   
        }
        else
        {
            echo "Deleted Fail..";
        }
    }
    
    public function delete()
    {
        $id =$this->input->post('id');
        $up=array('isActive'=>2);
        $this->RetailerModel->update('retailer_kia',$up,$id);  
        if ($this->db->affected_rows()>0)
        {
            echo "Your record has been deleted!";                
        }
        else
        {
            echo "Deleted Fail..";
        }
    }
    
    public function editRetailer(){
        $id=trim($this->input->post('id'));
        $retailer=$this->RetailerModel->getRetailersById('retailer_kia',$id);
        $name=$retailer[0]['name'];
        $area=$retailer[0]['area'];
        $code=$retailer[0]['retailerCode'];

        
        ?>
          <div class="modal-header">
           <h4 class="modal-title">Update Retailer</h4>
          </div>
          
          <input type="hidden" id='retailerInfoIdU' autocomplete="off" value="<?php echo $id; ?>" name="retailerInfoId">
          <div class="modal-body">
                        <div class="body">
                            <div class="demo-masked-input">
                                <div class="row clearfix">
                                  <div class="col-md-4">
                                        <b>Retailer Code</b>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                 <i class="material-icons">check_circle</i>
                                            </span>
                                            <div class="form-line">
                                                <input type="text" id='retailerCodeU' readonly value="<?php echo $code; ?>" name="retailerCode" class="form-control date" placeholder="Enter retailer code" required>
                                            </div>
                                        </div>
                                    </div> 
                                  <div class="col-md-4">
                                        <b>Retailer Name</b>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                 <i class="material-icons">check_circle</i>
                                            </span>
                                            <div class="form-line">
                                                <input type="text" id='rtNameU'  name="rtName" class="form-control date" value="<?php echo $name; ?>" placeholder="Enter retailer name" required>
                                            </div>
                                        </div>
                                    </div> 

                                    <div class="col-md-4">
                                        <b>Area</b>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                 <i class="material-icons">check_circle</i>
                                            </span>
                                            <div class="form-line">
                                                <input id="areaU" type="text" name="area" class="form-control date" value="<?php echo $area; ?>" placeholder="Enter area" required>
                                            </div>
                                        </div>
                                    </div> 

                                  <div id="recStatus1"></div>
                                     <div class="col-md-12">
                                        <div class="row clearfix">
                                            <div class="col-md-4">
                                                <button id="updRetInfo" class="btn btn-primary m-t-15 waves-effect">
                                                    <i class="material-icons">save</i> 
                                                    <span class="icon-name">Save</span>
                                                </button>
                                               
                                                    <button data-dismiss="modal" type="button" class="btn btn-primary m-t-15 waves-effect">
                                                        <i class="material-icons">cancel</i> 
                                                        <span class="icon-name"> Cancel</span>
                                                    </button>
                                               
                                            </div>

                                        </div>
                                    </div>                            
                                </div>
                            </div>
                        </div>
          </div>
        <?php
    }
}

?>