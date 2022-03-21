<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AdHocController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('AllocationByManagerModel');
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
    }

    public function freeItemBills(){
        $data['bills']=$this->AllocationByManagerModel->getdata('billsdetails_freeitems');
        $this->load->view('freeItemBillsView',$data);
    }


    public function retailerBillsExport()
    {
        $code="";
        if (isset($this->session->userdata['historyRetailer']) && (empty($retailerPost))) {
            $code=($this->session->userdata['historyRetailer']['code']);
        }
        $retailerBills=$this->AllocationByManagerModel->allRetailerBillsByCode('bills',$code);
        $extension = "xlsx";
        if(!empty($extension)){
          $extension = $extension;
        } else {
          $extension = 'xlsx';
        }
        $this->load->helper('download');  
        $data = array();
        $data['title'] = 'Export Excel Sheet | Coders Mag';
        
        $fileName = 'Retailers Bills -'.time(); 
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Bill No');
        $sheet->setCellValue('C1', 'Bill Date');
        $sheet->setCellValue('D1', 'Retailer');
        $sheet->setCellValue('E1', 'Bill Amount');
        $sheet->setCellValue('F1', 'Pending Amount');
        $sheet->setCellValue('G1', 'Salesman');
        $sheet->setCellValue('H1', 'Route');
        $sheet->setCellValue('I1', 'Company');
     
        $rowCount = 2;
        $no=0;
        foreach ($retailerBills as $element) {
            $no++;
            $dt=date_create($element['date']);
            $dt= date_format($dt,'d-M-Y');
            $sheet->setCellValue('A' . $rowCount, $no++);
            $sheet->setCellValue('B' . $rowCount, $element['billNo']);
            $sheet->setCellValue('C' . $rowCount, $dt);
            $sheet->setCellValue('D' . $rowCount, $element['retailerName']);
            $sheet->setCellValue('E' . $rowCount, $element['netAmount']);
            $sheet->setCellValue('F' . $rowCount, $element['pendingAmt']);
            $sheet->setCellValue('G' . $rowCount, $element['salesman']);
            $sheet->setCellValue('H' . $rowCount, $element['routeName']);
            $sheet->setCellValue('I' . $rowCount, $element['compName']);
            $rowCount++;
        }
     
        if($extension == 'csv'){          
          $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
          $fileName = $fileName.'.csv';
        } elseif($extension == 'xlsx') {
          $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
          $fileName = $fileName.'.xlsx';
        } else {
          $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
          $fileName = $fileName.'.xls';
        }
     
        $this->output->set_header('Content-Type: application/vnd.ms-excel');
        $this->output->set_header("Content-type: application/csv");
        $this->output->set_header('Cache-Control: max-age=0');
        $writer->save(ROOT_UPLOAD_PATH.$fileName); 
        //redirect(HTTP_UPLOAD_PATH.$fileName); 
        $filepath = file_get_contents(ROOT_UPLOAD_PATH.$fileName);
        force_download($fileName, $filepath);
    }

    public function billSearch(){
        $data['emp']=$this->AllocationByManagerModel->getdata('employee');
        $data['currentAllocations']=$this->AllocationByManagerModel->getCurrentOpenAllocations('allocations');
        $data['bills']=$this->AllocationByManagerModel->getBillsData('bills');
        $this->load->view('Manager/billSearchView',$data);
    }

    public function findBillsData(){
        $billNo=trim($this->input->post('billNo'));
        $billsData=$this->AllocationByManagerModel->findBills('bills',$billNo);
        if(!empty($billsData)){
            if(count($billsData)>1){
?>
        <table style="font-size: 12px" class="table table-bordered table-striped table-hover js-exportable dataTable" data-page-length='100'>
        <thead>
            <tr>
                <th>S. No.</th>
                <th>Bill No</th>
                <th>Bill Date</th>
                <th>Retailer</th>
                <th>Bill Amount</th>
                <th>Pending Amount</th>
                <th>Salesman</th>
                <th>Action</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>S. No.</th>
                <th>Bill No</th>
                <th>Bill Date</th>
                <th>Retailer</th>
                <th>Bill Amount</th>
                <th>Pending Amount</th>
                <th>Salesman</th>
                <th>Action</th>
            </tr>
        </tfoot>
        <tbody>
            <?php 
            $no=0;
                if(!empty($billsData)){
                foreach($billsData as $data){
                      $no++;
                      $dt=date_create($data['date']);
                      $dt= date_format($dt,'d-M-Y');

                    
            ?>
             <?php if($data['isAllocated']==1){ ?>
                     <tr style="background-color: #dcd6d5">
                <?php }else{ ?>
                     <tr>
                <?php } ?>
                <td><?php echo $no;?></td>
                <td><?php echo $data['billNo'];?></td>
                <td><?php echo $dt; ?></td>
                <td><?php echo $data['retailerName'];?></td>
                <td><?php echo $data['netAmount'];?></td>
                <td><?php echo $data['pendingAmt'];?></td>
                <td><?php echo $data['salesman'];?></td>
                 
                <td>
                     <?php if($data['isAllocated']!=1){ ?>

                      &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billHistoryInfo/'.$data['id']); ?>" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="bottom" title="View History"><i class="material-icons">info</i></a>
                   <?php  }else{
                        $allocations=$this->AllocationByManagerModel->getAllocationDetailsByBill('bills',$data['id']);
                        $officeAllocations=$this->AllocationByManagerModel->getOfficeAllocationDetailsByBill('bills',$data['id']);
                         if(!empty($allocations)){
                            echo "<p style='color:blue'>Allocated in : ".$allocations[0]['allocationCode']."</p>";
                            }else if(!empty($officeAllocations)){
                               echo "<p style='color:blue'>Allocated in : ".$officeAllocations[0]['allocationCode']."</p>";
                            }
                         }
                    ?>
                    
                
           </td>
            </tr>
            <?php 
                    
                }
               } 
            ?>
        
        </tbody>
    </table>
<?php
            }else{
                $billId=$billsData[0]['id'];

                $presentBill=$this->AllocationByManagerModel->load('bills',$billId);

                if(!empty($presentBill)){
                    $bills=$this->AllocationByManagerModel->getBillAllocationHistoryByBill('billpayments',$billId);
                    $billOfficeAdj=$this->AllocationByManagerModel->getBillOfficeAdjHistoryByBill('allocations_officebills',$billId);
                    $billSr=$this->AllocationByManagerModel->getBillAllocationSrByBill('allocation_sr_details',$billId);
                    $this->billAllocationInfo($billId,$bills,$billOfficeAdj,$billSr);
                }else{
                    echo "<span style='color:red'>Please select bill no.</span>";
                }
            }
        }else{
            echo "Bill not found";
        }
    }

    public function loadbillSearchBills(){
        $bills=$this->AllocationByManagerModel->getBillsData('bills');
        foreach($bills as $data){
            $name=$data['billNo'].' : '.$data['retailerName'];
        ?>   
        <option id="<?php echo $data['id'];?>" value="<?php echo $name;?>"/>
    <?php    
        }
    }

    public function billDetailsInfo($id){
        $data['bills']=$this->AllocationByManagerModel->load('bills',$id);
        $data['billsdetails']=$this->AllocationByManagerModel->getBillDetailInfo('billsdetails',$id);
        $this->load->view('commanBillView',$data);
    }

    public function checkValuesByBillSr(){
        $billNo=trim($this->input->post('billNo'));
        $cmpName=trim($this->input->post('comp'));

        $srNo=$this->AllocationByManagerModel->loadSrBillDetails('bill_serial_manage',$cmpName);
        $cmpSrNp=$srNo[0]['serialStartWith'];

        if(strpos($billNo, $cmpSrNp) !== false){
            echo "";
        }else{
            echo "Please use serial number '".$cmpSrNp."' for ".$cmpName.".";
        }

    }

    public function billInfo(){
        $billNo=trim($this->input->post('billNo'));
        $billId=trim($this->input->post('billId'));

        $presentBill=$this->AllocationByManagerModel->load('bills',$billId);

        if(!empty($presentBill)){
            $bills=$this->AllocationByManagerModel->getBillAllocationHistoryByBill('billpayments',$billId);
            $billOfficeAdj=$this->AllocationByManagerModel->getBillOfficeAdjHistoryByBill('allocations_officebills',$billId);
            $billSr=$this->AllocationByManagerModel->getBillAllocationSrByBill('allocation_sr_details',$billId);
            $this->billAllocationInfo($billId,$bills,$billOfficeAdj,$billSr);
        }else{
            echo "<span style='color:red'>Please select bill no.</span>";
        }
    }

    public function billInfoForAdmin(){
        $billNo=trim($this->input->post('billNo'));
        // $billId=trim($this->input->post('billId'));
        $billsData=$this->AllocationByManagerModel->findBills('bills',$billNo);
        if(!empty($billsData)){
            $billId=$billsData[0]['id'];
            $presentBill=$this->AllocationByManagerModel->load('bills',$billId);

            if(!empty($presentBill)){
                $bills=$this->AllocationByManagerModel->getBillAllocationHistoryByBill('billpayments',$billId);
                $billOfficeAdj=$this->AllocationByManagerModel->getBillOfficeAdjHistoryByBill('allocations_officebills',$billId);
                $billSr=$this->AllocationByManagerModel->getBillAllocationSrByBill('allocation_sr_details',$billId);
                $this->billAllocationInfo($billId,$bills,$billOfficeAdj,$billSr);
            }else{
                echo "<span style='color:red'>Please select bill no.</span>";
            }
        }else{
                echo "<span style='color:red'>Please select bill no.</span>";
        }
    }

    public function billHistoryInfo($id){

        $data['currentAllocations']=$this->AllocationByManagerModel->getCurrentOpenAllocations('allocations');
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['bank']=$this->AllocationByManagerModel->getdata('bank');
        $data['emp']=$this->AllocationByManagerModel->getdata('employee');

        $billId=trim($id);

        $presentBill=$this->AllocationByManagerModel->load('bills',$billId);

        if(!empty($presentBill)){
            $data['billId']=$billId;
            $data['bills']=$this->AllocationByManagerModel->getBillAllocationHistoryByBill('billpayments',$billId);
            $data['billOfficeAdj']=$this->AllocationByManagerModel->getBillOfficeAdjHistoryByBill('allocations_officebills',$billId);

            // $data['bills']=array_merge($data['bills'],$data['billOfficeAdj']);

            $data['billSr']=$this->AllocationByManagerModel->getBillAllocationSrByBill('allocation_sr_details',$billId);
            $data['billInfo']=$this->AllocationByManagerModel->load('bills',$billId);
            $data['retailerCode']=$this->AllocationByManagerModel->loadRetailer($data['billInfo'][0]['retailerCode']);
            $data['resendBill']=$this->AllocationByManagerModel->getResendBill('allocationsbills',$billId);
            $data['signedBill']=$this->AllocationByManagerModel->getSignedBill('allocationsbills',$billId);
            // $this->billAllocationInfo($billId,$bills,$billOfficeAdj,$billSr);
            $this->load->view('Manager/billHistorySearchView',$data);
        }else{
            echo "<span style='color:red'>Please select bill no.</span>";
        }
    }

    public function retailerbillInfo(){
        $billNo=trim($this->input->post('billNo'));
        $billId=trim($this->input->post('billId'));

        $presentBill=$this->AllocationByManagerModel->load('bills',$billId);

        if(!empty($presentBill)){
            $bills=$this->AllocationByManagerModel->getBillAllocationHistoryByBill('billpayments',$billId);
            $billOfficeAdj=$this->AllocationByManagerModel->getBillOfficeAdjHistoryByBill('allocations_officebills',$billId);

            $billSr=$this->AllocationByManagerModel->getBillAllocationSrByBill('allocation_sr_details',$billId);
            $this->retailerBillHistory($billId,$bills,$billOfficeAdj,$billSr);
        }else{
            echo "<span style='color:red'>Please select bill no.</span>";
        }
    }


    public function retailerHistory(){
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['retailer']=$this->AllocationByManagerModel->getRetailerDetails('bills');
        $this->load->view('Manager/showRetailerHistoryView',$data);
    }

    public function allRetailerHistory()
    {
        $data['currentAllocations']=$this->AllocationByManagerModel->getCurrentOpenAllocations('allocations');
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['bank']=$this->AllocationByManagerModel->getdata('bank');
        $data['emp']=$this->AllocationByManagerModel->getdata('employee');

        $this->load->library('pagination');

        $config['base_url'] = base_url('index.php/AdHocController/allRetailerHistory');
        
        $config['per_page'] = ($this->input->get('limitRows')) ? $this->input->get('limitRows') : 50;
        $config['enable_query_strings'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['reuse_query_string'] = TRUE;
        // $config['get'] = "?retailerGet=" .trim($this->input->post('retailer')); 

        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['retailer']=$this->AllocationByManagerModel->getRetailerDetails('bills');

        $retailerPost=trim($this->input->post('retailer'));
        $fromDate=trim($this->input->post('fromDate'));
        $toDate=trim($this->input->post('toDate'));

        $retailerGet=trim($this->input->get('retailer'));
        $retailerName="";
        $routeName="";
        $company="";
        $code="";


        if (isset($this->session->userdata['historyRetailer']) && (empty($retailerPost))) {
            $retailerName=($this->session->userdata['historyRetailer']['retailerName']);
            $routeName=($this->session->userdata['historyRetailer']['routeName']);
            $company=($this->session->userdata['historyRetailer']['company']);
            $code=($this->session->userdata['historyRetailer']['code']);
            $fromDate=($this->session->userdata['historyRetailer']['fromDate']);
            $toDate=($this->session->userdata['historyRetailer']['toDate']);
        }else{
            if(!empty($retailerPost)){
                $retailer=explode(' : ', $retailerPost); 
                if(count($retailer)>1){
                    $retailerName=$retailer[0];
                    $routeName=$retailer[1];
                    $company=$retailer[2];
                    $code=$retailer[3];

                    $session_data = array(
                        'retailerName' => $retailerName,
                        'routeName' => $routeName,
                        'company' => $company,
                        'code' => $code,
                        'fromDate' => $fromDate,
                        'toDate' => $toDate
                    );
                    $this->session->set_userdata('historyRetailer', $session_data);
                }
            }
        }

         // integrate bootstrap pagination
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
       
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = 'Prev';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = 'Next';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="'.$config['base_url'].'?per_page=0">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        $data['page'] = ($this->input->get('per_page')) ? $this->input->get('per_page') : 0;
        $data['searchFor'] = ($this->input->get('query')) ? $this->input->get('query') : NULL;
        $data['orderField'] = ($this->input->get('orderField')) ? $this->input->get('orderField') : '';
        $data['orderDirection'] = ($this->input->get('orderDirection')) ? $this->input->get('orderDirection') : '';
        $outstanding="";
        $rowConunts="";
        $bills = $this->AllocationByManagerModel->paginationRetailerBills('bills',$config["per_page"], $data['page'], $data['searchFor'], $data['orderField'], $data['orderDirection'],$code,$fromDate,$toDate);
        $rowCounts=$this->AllocationByManagerModel->countRetailerBills('bills',$config["per_page"], $data['page'], $data['searchFor'], $data['orderField'], $data['orderDirection'],$code,$fromDate,$toDate);

        $data['bills'] = $bills;
        $config['total_rows'] = $rowCounts;
        $data['retailerName']=$retailerName;
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $this->load->view('Manager/allRetailersHistoryView',$data);
    }

    public function OldallRetailerHistory(){
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['retailer']=$this->AllocationByManagerModel->getRetailerDetails('bills');

        $retailer=trim($this->input->post('retailer'));
        $fromDate=trim($this->input->post('fromDate'));
        $toDate=trim($this->input->post('toDate'));

        $retailer=explode(' : ', $retailer);
        $retailerName="";
        $routeName="";
        $company="";
        $code="";
        if(count($retailer)>1){
            $retailerName=$retailer[0];
            $routeName=$retailer[1];
            $company=$retailer[2];
            $code=$retailer[3];
            $bills=$this->AllocationByManagerModel->getBillsByRetailerCode('bills',$code,$company,$fromDate,$toDate);
            $data['bills']=$bills;
            $data['retailerName']=$retailerName;
            $this->load->view('Manager/allRetailersHistoryView',$data);
            // $this->retailerHistoryInformation($bills,$retailerName);
        }else{
            $this->load->view('Manager/allRetailersHistoryView',$data);
        }
        
    }

    public function retailerHistoryInfo(){
        $retailer=trim($this->input->post('retailer'));
        $fromDate=trim($this->input->post('fromDate'));
        $toDate=trim($this->input->post('toDate'));

        $retailer=explode(' : ', $retailer);
        $retailerName="";
        $routeName="";
        $company="";
        $code="";
        if(count($retailer)>1){
            $retailerName=$retailer[0];
            $routeName=$retailer[1];
            $company=$retailer[2];
            $code=$retailer[3];
            $bills=$this->AllocationByManagerModel->getBillsByRetailerCode('bills',$code,$company,$fromDate,$toDate);
            $this->retailerHistoryInformation($bills,$retailerName);
        }else{
            echo "<span style='color:red'>Please select retailer.</span>";
        }
    }

    public function retailerHistoryInfoByBillSearch($retailerName,$code,$routeName,$company,$fromDate,$toDate){
        // $retailerName=trim($this->input->post('retailer'));
        // $code=trim($this->input->post('retailerCode'));
        // $routeName=trim($this->input->post('routeName'));
        // $company=trim($this->input->post('compName'));
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['retailer']=$this->AllocationByManagerModel->getRetailerDetails('bills');
        // $fromDate=trim($this->input->post('fromDate'));
        // $toDate=trim($this->input->post('toDate'));
        
        $bills=$this->AllocationByManagerModel->getBillsByRetailerCode('bills',$code,$company,$fromDate,$toDate);
        $data['bills']=$bills;
        $data['retailerName']=$retailerName;
        $this->load->view('Manager/showRetailerHistoryForBillSearchView',$data);
    }

    public function adhocBills(){
        $data['currentAllocations']=$this->AllocationByManagerModel->getCurrentOpenAllocations('allocations');
        $data['adhocBills']=$this->AllocationByManagerModel->getNotAllocatedAdHocBillsByType('bills');
        // print_r($data['adhocBills']);exit;
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['emp']=$this->AllocationByManagerModel->getdata('employee');
        $data['employee']=$this->AllocationByManagerModel->getdata('employee');
        $this->load->view('Manager/adhocBillsDupView',$data);
    }

    public function officeAdjustmentBills(){
        $data['currentAllocations']=$this->AllocationByManagerModel->getCurrentOpenAllocations('allocations');
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['emp']=$this->AllocationByManagerModel->getdata('employee');
        $cmp=$this->input->post('cmp');
        $data['cmpName']=$cmp;
        $from_date=$this->input->post('from_date');
        $to_date=$this->input->post('to_date');

        if(($cmp=='General' || $cmp=="") && $from_date=='' && $to_date==''){
            $time = strtotime("-1 year", time());
            $from_date = date("Y-m-d", $time);
            $to_date=date('Y-m-d');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getOfficeBillsByType('bills','officeAdjustmentBill',$from_date,$to_date);
            $this->load->view('Manager/officeAdjustmentBillsView',$data);

        }else if($cmp=='General' && $from_date !='' && $to_date!=''){
            $time = strtotime("-1 year", time());
            $from_date = date("Y-m-d", $time);
            $to_date=date('Y-m-d');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getOfficeBillsByType('bills','officeAdjustmentBill',$from_date,$to_date);
            $this->load->view('Manager/officeAdjustmentBillsView',$data);
        }else {
            $from_date=$this->input->post('from_date');
            $to_date=$this->input->post('to_date');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getOfficeBillsByTypeWithComp('bills','officeAdjustmentBill',$from_date,$to_date,$cmp);
            $this->load->view('Manager/officeAdjustmentBillsView',$data);
        }
    }

    public function otherAdjustmentBills(){
        $data['currentAllocations']=$this->AllocationByManagerModel->getCurrentOpenAllocations('allocations');
        $data['emp']=$this->AllocationByManagerModel->getdata('employee');
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $cmp=$this->input->post('cmp');
        $data['cmpName']=$cmp;
        $from_date=$this->input->post('from_date');
        $to_date=$this->input->post('to_date');

        if(($cmp=='General' || $cmp=="") && $from_date=='' && $to_date==''){
            $time = strtotime("-1 year", time());
            $from_date = date("Y-m-d", $time);
            $to_date=date('Y-m-d');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getOtherBillsByType('bills','officeAdjustmentBill',$from_date,$to_date);
            $this->load->view('Manager/otherAdjustmentBillsView',$data);
        }else if($cmp=='General' && $from_date !='' && $to_date!=''){
            $time = strtotime("-1 year", time());
            $from_date = date("Y-m-d", $time);
            $to_date=date('Y-m-d');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getOtherBillsByType('bills','officeAdjustmentBill',$from_date,$to_date);
            $this->load->view('Manager/otherAdjustmentBillsView',$data);
        }else{
            $from_date=$this->input->post('from_date');
            $to_date=$this->input->post('to_date');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getOtherBillsByTypeWithComp('bills','officeAdjustmentBill',$from_date,$to_date,$cmp);
            $this->load->view('Manager/otherAdjustmentBillsView',$data);
        }
    }

    public function cashDiscountHistoryBills(){
        $data['currentAllocations']=$this->AllocationByManagerModel->getCurrentOpenAllocations('allocations');
        $data['emp']=$this->AllocationByManagerModel->getdata('employee');
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $cmp=$this->input->post('cmp');
        $data['cmpName']=$cmp;
        $from_date=$this->input->post('from_date');
        $to_date=$this->input->post('to_date');

        if(($cmp=='General' || $cmp=="") && $from_date=='' && $to_date==''){
            $time = strtotime("-1 year", time());
            $from_date = date("Y-m-d", $time);
            $to_date=date('Y-m-d');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getCashDiscountBillsByType('bills','officeAdjustmentBill',$from_date,$to_date);
            $this->load->view('Manager/cashDiscountBillsView',$data);
        }else if($cmp=='General' && $from_date !='' && $to_date!=''){
            $time = strtotime("-1 year", time());
            $from_date = date("Y-m-d", $time);
            $to_date=date('Y-m-d');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getCashDiscountBillsByType('bills','officeAdjustmentBill',$from_date,$to_date);
            $this->load->view('Manager/cashDiscountBillsView',$data);
        }else{
            $from_date=$this->input->post('from_date');
            $to_date=$this->input->post('to_date');
            $data['from']=$from_date;
            $data['to']=$to_date;
            $data['officeAdjustmentBills']=$this->AllocationByManagerModel->getCashDiscountBillsByTypeWithComp('bills','officeAdjustmentBill',$from_date,$to_date,$cmp);
            $this->load->view('Manager/cashDiscountBillsView',$data);
        }
    }

    public function adhocDeliveryBills(){
        $data['currentAllocations']=$this->AllocationByManagerModel->getCurrentOpenAllocations('allocations');
        $cmp=trim($this->input->post('cmp'));
        $data['company']=$this->AllocationByManagerModel->getdata('company');
        $data['emp']=$this->AllocationByManagerModel->getdata('employee');
        $data["cmpName"]=$cmp;
        if($cmp=="" || $cmp=="General"){
            $data['adhocBills']=$this->AllocationByManagerModel->getAdHocDeliveryBillsByType('bills','adHocDeliveryBill');
            $this->load->view('Manager/adhocBillsByEmployeeView',$data);
        }else{
            $data['adhocBills']=$this->AllocationByManagerModel->getAdHocDeliveryBillsByTypeWithCompany('bills','adHocDeliveryBill',$cmp);
            $this->load->view('Manager/adhocBillsByEmployeeView',$data);
        }
        
    }

    public function unalocatedAdHocBills(){
        $data['adhocBills']=$this->AllocationByManagerModel->getNotAllocatedAdHocBillsByType('bills');
        $this->load->view('Manager/showAdHocBillsView',$data);
    }

    public function insertAdhocBill(){
        $billNo=trim($this->input->post('billNo'));
        $netAmount=trim($this->input->post('netAmount'));
        $retailerName=trim($this->input->post('retailerName'));
        $cmpName=trim($this->input->post('cmpName'));
        $billOption=trim($this->input->post('billOption'));
        $allocationId=trim($this->input->post('allocationType'));
        $remark=trim($this->input->post('remark'));
        $remarkOffice=trim($this->input->post('remarkOffice'));
        $empId=trim($this->input->post('empId'));
        $empName=trim($this->input->post('empDetail'));
        $adjustmentAmount=trim($this->input->post('adjustmentAmount'));
        
        $srNo=$this->AllocationByManagerModel->loadSrBillDetails('bill_serial_manage',$cmpName);
        $cmpSrNp=$srNo[0]['serialStartWith'];

        $currentDate=date('Y-m-d');
        $updatedBy=$this->session->userdata['logged_in']['id'];
        
        if(strpos($billNo, $cmpSrNp) !== false){
            if(($billOption==="Direct Delivery") && ($empId !=="") && ($remark !=="")){
                $insertData=array('billNo'=>$billNo,'deliveryEmpName'=>$empName,'date'=>$currentDate,'retailerName'=>$retailerName,'compName'=>$cmpName,'remark'=>$remark,'netAmount'=>$netAmount,'pendingAmt'=>$netAmount,'billType'=>'adHocDeliveryBill');
                $this->AllocationByManagerModel->insert('bills',$insertData);
                if($this->db->affected_rows()>0){
                    $lastBillId=$this->db->insert_id();
                    $billRemark=array(
                        'billId'=>$lastBillId,'empId'=>$empId,'remark'=>$remark,'updatedAt'=>date('Y-m-d H:i:sa'),'updatedBy'=>$updatedBy
                    );
                    $this->AllocationByManagerModel->insert('bill_remark_history',$billRemark);//insert remark data

                    echo "Record inserted";
                }else{
                    echo "Record not inserted";
                }
                
            }else if(($billOption==="Office Adjustment Bill") && ($adjustmentAmount !=="") && ($remarkOffice !=="")){
                if($adjustmentAmount>$netAmount){
                    echo "Office ajustment amount is greater than net amount";
                }else{
                    $pendingAmount=0;
                    if($adjustmentAmount>0){
                        $pendingAmount=$netAmount-$adjustmentAmount;
                    }else{
                        $pendingAmount=$netAmount;
                    }
                    $insertData=array('billNo'=>$billNo,'date'=>$currentDate,'retailerName'=>$retailerName,'compName'=>$cmpName,'remark'=>$remarkOffice,'netAmount'=>$netAmount,'officeAdjustmentBillAmount'=>$adjustmentAmount,'pendingAmt'=>$pendingAmount,'billType'=>'officeAdjustmentBill');
                    $this->AllocationByManagerModel->insert('bills',$insertData);
                    if($this->db->affected_rows()>0){
                        $lastBillId=$this->db->insert_id();
                        $billRemark=array(
                            'billId'=>$lastBillId,'empId'=>$updatedBy,'amount'=>$adjustmentAmount,'remark'=>$remarkOffice,'updatedAt'=>date('Y-m-d H:i:sa'),'updatedBy'=>$updatedBy
                        );
                        $this->AllocationByManagerModel->insert('bill_remark_history',$billRemark);//insert remark data
                        echo "Record inserted";
                    }else{
                        echo "Record not inserted";
                    }
                }
                
            } else if($billOption==="Leave Unallocated"){
                $insertData=array('billNo'=>$billNo,'date'=>$currentDate,'retailerName'=>$retailerName,'compName'=>$cmpName,'netAmount'=>$netAmount,'pendingAmt'=>$netAmount);
                $this->AllocationByManagerModel->insert('bills',$insertData);
                if($this->db->affected_rows()>0){
                    echo "Record inserted";
                }else{
                    echo "Record not inserted";
                }
            }else if(($billOption==="Add To Open Allocation") && ($allocationId != "")){
                $alId=explode(' : ',$allocationId);
                $allocationDetails=array();
                if(!empty($alId[0])){
                    $allocationDetails=$this->AllocationByManagerModel->checkAllocationCode('allocations',$alId[0]);
                }

                if(!empty($allocationDetails)){
                    $billAllocationId=$allocationDetails[0]['id'];

                    $insertData=array('billNo'=>$billNo,'date'=>$currentDate,'retailerName'=>$retailerName,'compName'=>$cmpName,'netAmount'=>$netAmount,'pendingAmt'=>$netAmount);
                    $this->AllocationByManagerModel->insert('bills',$insertData);
                    if($this->db->affected_rows()>0){
                        $lastInsertedId=$this->db->insert_id(); 
                        $insAllocationDetail=array('billId'=>$lastInsertedId,'allocationId'=>$billAllocationId,'billStatus'=>1);
                        $this->AllocationByManagerModel->insert('allocationsbills',$insAllocationDetail);
                        if($this->db->affected_rows()>0){
                            $updBills=array('billType'=>'allocatedbillCurrent','isAllocated'=>1);
                            $this->AllocationByManagerModel->update('bills',$updBills,$lastInsertedId);
                            if($this->db->affected_rows()>0){
                                echo "Record inserted";
                            }else{
                                echo "Record not inserted";
                            }
                        }
                    }
                }else{
                    echo "Please enter correct allocation number";
                }
            }else{
                echo "Please enter all details";
            }
        }else{
            echo "Please use serial number '".$cmpSrNp."' for ".$cmpName.".";
        }
    }

    public function officeAdjustmentForm(){
        $id=trim($this->input->post('id'));
        $billDetail=$this->AllocationByManagerModel->load('bills',$id);
?>

        <div class="col-md-12">
            <p><span style="color:blue"> Bill Amount : </span> <?php if(!empty($billDetail)){ echo $billDetail[0]['netAmount']; } ?> &nbsp;&nbsp;
            <span style="color:blue"> Pending Amount : </span> <?php if(!empty($billDetail)){ echo $billDetail[0]['pendingAmt']; } ?> &nbsp;&nbsp;
            <span style="color:blue"> Bill Date : </span> <?php if(!empty($billDetail)){ echo $billDetail[0]['date']; } ?> </p>
        </div>
        <br>
        <div class="col-md-12">
          <input type="hidden" autocomplete="off" id="offcId" name="offcId" value="<?php echo $id; ?>">
          <input type="hidden" autocomplete="off" id="pendAmt" name="pendAmt" value="<?php if(!empty($billDetail)){ echo $billDetail[0]['pendingAmt']; } ?>">

          <input type="hidden" autocomplete="off" id="billAmt" name="billAmt" value="<?php if(!empty($billDetail)){ echo $billDetail[0]['netAmount']; } ?>">

          <input type="hidden" autocomplete="off" id="officeAdjBill" name="officeAdjBill" value="<?php if(!empty($billDetail)){ echo $billDetail[0]['officeAdjustmentBillAmount']; } ?>">
            <div class="col-md-6">
                <b> Amount</b>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="material-icons">account_box</i>
                    </span>
                    <div class="form-line">
                        <input type="text" onblur="amountCheck(this);" autocomplete="off" id="collectAmount" name="adjustmentAmount" class="form-control date" value="" onkeypress="return numbersonly(this, event);" placeholder="adjustment amount">
                    </div>
                    <p id="eror" style="color:red"></p>
                </div>
            </div>

            <div class="col-md-6">
                <button id="submitCollect" class="btn btn-primary btn-sm margin m-t-20">Collect</button>
                <button type="button" data-dismiss="modal" class="btn btn-danger btn-sm margin m-t-20">Close</button>
            </div>
        </div>

<?php
    } 


    public function adHociBillByEmpAdjustmentForm(){
        $id=trim($this->input->post('id'));
        $billDetail=$this->AllocationByManagerModel->load('bills',$id);
?>

        <div class="col-md-12">
            <p><span style="color:blue"> Bill Amount : </span> <?php if(!empty($billDetail)){ echo $billDetail[0]['netAmount']; } ?> &nbsp;&nbsp;
            <span style="color:blue"> Pending Amount : </span> <?php if(!empty($billDetail)){ echo $billDetail[0]['pendingAmt']; } ?> &nbsp;&nbsp;
            <span style="color:blue"> Bill Date : </span> <?php if(!empty($billDetail)){ echo $billDetail[0]['date']; } ?> </p>
        </div>
        <br><br>
        <div class="col-md-12">
          <input type="hidden" autocomplete="off" id="offcId" name="offcId" value="<?php echo $id; ?>">
          <input type="hidden" autocomplete="off" id="pendAmt" name="pendAmt" value="<?php if(!empty($billDetail)){ echo $billDetail[0]['pendingAmt']; } ?>">

          <input type="hidden" autocomplete="off" id="billAmt" name="billAmt" value="<?php if(!empty($billDetail)){ echo $billDetail[0]['netAmount']; } ?>">

          <input type="hidden" autocomplete="off" id="officeAdjBill" name="officeAdjBill" value="<?php if(!empty($billDetail)){ echo $billDetail[0]['officeAdjustmentBillAmount']; } ?>">
            <div class="col-md-12">
                <b> Amount</b>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="material-icons">account_box</i>
                    </span>
                    <div class="form-line">
                        <input type="text" onblur="amountCheck(this);" autocomplete="off" id="collectAmount" name="adjustmentAmount" class="form-control date" value="" onkeypress="return numbersonly(this, event);" placeholder="amount">
                    </div>
                    <p id="eror" style="color:red"></p>
                </div>
            </div>

            <div class="col-md-12">
                <button id="button" class="btn btn-primary btn-sm margin m-t-20">Cash</button>
                <button id="button" class="btn btn-primary btn-sm margin m-t-20">Cheque</button>
                <button id="button" class="btn btn-primary btn-sm margin m-t-20">NEFT</button>
                <button id="button" class="btn btn-primary btn-sm margin m-t-20">SR</button>
                <button id="button" class="btn btn-primary btn-sm margin m-t-20">FSR</button>
                <button id="button" class="btn btn-primary btn-sm margin m-t-20">CD</button>
                <button id="button" class="btn btn-primary btn-sm margin m-t-20">Debit</button>
                <button id="button" class="btn btn-primary btn-sm margin m-t-20">Bill</button>
                <button type="button" data-dismiss="modal" class="btn btn-danger btn-sm margin m-t-20">Close</button>
            </div>
        </div>

<?php
    } 


    public function insertOfficeAdjustmentAmount(){
        $id=trim($this->input->post('id'));
        $pendAmt=trim($this->input->post('pendAmt'));
        $billAmt=trim($this->input->post('billAmt'));
        $collectAmount=trim($this->input->post('collectAmount'));
        $officeAdjBill=trim($this->input->post('officeAdjBill'));
        

        $totalPending=$pendAmt-$collectAmount;
        $totalOfficeAdjustment=$officeAdjBill+$collectAmount;

        $data=array(
            'pendingAmt'=>$totalPending,'officeAdjustmentBillAmount'=>$totalOfficeAdjustment
        );

        $this->AllocationByManagerModel->update('bills',$data,$id);
        if($this->db->affected_rows()>0){
            echo "Record updated";
        }else{
            echo "Unable update record";
        }
    }


    public function billAllocationInfo($billId,$bills,$billOfficeAdj,$billSr){
        $billInfo=$this->AllocationByManagerModel->load('bills',$billId);
        $retailerCode=$this->AllocationByManagerModel->loadRetailer($billInfo[0]['retailerCode']);
        $resendBill=$this->AllocationByManagerModel->getResendBill('allocationsbills',$billId);
        $signedBill=$this->AllocationByManagerModel->getSignedBill('allocationsbills',$billId);

    ?>
    
     <table style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>
        <thead>
            <tr colspan="8">
                 <th>
                  <span style="color:blue"> Bill Information  </span>
              </th>
            </tr>
        </thead>
        <thead>
            <tr class="gray">
                <th colspan="3"> Retailer Name  </th>
                <th colspan="3">Retailer Code</th>
                <th colspan="3"> Route Name  </th>
                <th colspan="5">Retailer GST No.</th>
            </tr>
        </thead>
            <tbody>
            <?php
                if(!empty($billInfo)){
                    foreach ($billInfo as $data) 
                    {
                        $urlSite="/".$data['retailerName'].'/'.$data['retailerCode'].'/'.$data['routeName'].'/'.$data['compName'].'/'.date('Y-m-d',strtotime("-1 year")).'/'.date('Y-m-d');
                        // echo $urlSite;
            ?>
                    <tr>
                        <td colspan="3">
                            <a href="<?php echo base_url('index.php/AdHocController/retailerHistoryInfoByBillSearch'.$urlSite); ?>"><?php echo $data['retailerName']; ?></a>
                            <!-- <a id="searchRetailerInfo" href="javascript:void();"><?php echo $data['retailerName']; ?></a> -->
                            <!-- <input type="text" id="tbRetailerName" value="<?php echo $data['retailerName']; ?>">
                            <input type="text" id="tbRetailerCode" value="<?php echo $data['retailerCode']; ?>">
                            <input type="text" id="tbRouteName" value="<?php echo $data['routeName']; ?>">
                            <input type="text" id="tbCompName" value="<?php echo $data['compName']; ?>">
                            <input type="hidden" id="tbFromDate" value="<?php echo date('Y-m-d'); ?>">
                            <input type="hidden" id="tbToDate" value="<?php echo date('Y-m-d'); ?>"> -->
                        </td>
                        <td colspan="3"><?php echo $data['retailerCode']; ?></td>
                        <td colspan="3"><?php echo $data['routeName']; ?></td>
                        <td colspan="5"><?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?></td>
                    </tr>

            <?php
                    }
                } 
            ?>
        </tbody>
        <thead>
            <tr class="gray">
                 <th> Bill No  </th>
                 <th>Bill Date</th>
                 <th> Salesman </th>
                 <th> Net Amount </th>
                 <th> SR  </th>
                 <th> CD  </th>
                 <th> Collection </th>
                 <th> Office Adj  </th>
                 <th> Other Adj  </th>
                 <th> Debit </th>
                 <th> Remaining  </th>
                 <th> Cheque Penalty  </th>
                 <th> Status </th>
                 <th> Action  </th>
            </tr>
        </thead>
        <tbody>
            <?php
                if(!empty($billInfo)){
                    foreach ($billInfo as $data) 
                    {
                        $dt=date_create($data['date']);
                        $createdDate = date_format($dt,'d-M-Y');
                   
                        if($data['isAllocated']==1){ ?>
                             <tr style="background-color: #dcd6d5">
                        <?php }else{ ?>
                             <tr>
                        <?php } ?>

                        <td><?php echo $data['billNo']; ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo $data['salesman']; ?></td>
                        <td><?php echo $data['netAmount']; ?></td>
                        <?php if($data['isAllocated'] == 1){ ?>
                            <td><?php echo ($data['SRAmt']+$data['fsSrAmt']-$data['fsSrAmt']); ?></td>
                        <?php } else { ?>
                            <td><?php echo ($data['SRAmt']); ?></td>
                        <?php } ?>
                        
                         <td><?php echo $data['cd']; ?></td>
                         <td><?php echo ($data['receivedAmt']+$data['fsCashAmt']+$data['fsChequeAmt']+$data['fsNeftAmt']); ?></td>
                        <td><?php echo $data['officeAdjustmentBillAmount']; ?></td>
                        <td><?php echo $data['otherAdjustment']; ?></td>
                        <td><?php echo $data['debit']; ?></td>
                        <td><?php echo $data['pendingAmt']; ?></td>
                        <td><?php echo $data['chequePenalty']; ?></td>
                        <td>
                        <?php
                        if($data['deliveryStatus']=="cancelled"){
                            echo "Cancelled";
                        } else{     
                            $allocations=$this->AllocationByManagerModel->getAllocationDetailsByBill('bills',$data['id']);
                            $allocationsHistory=$this->AllocationByManagerModel->getAllocationDetailsByBillHistory('bills',$data['id']);
                            $officeAllocations=$this->AllocationByManagerModel->getOfficeAllocationDetailsByBill('bills',$data['id']);
                            $officeAllocationsHistory=$this->AllocationByManagerModel->getOfficeAllocationDetailsByBillHistory('bills',$data['id']);
                            // print_r($allocations);exit;
                            $status="";
                            $allocationNumber="";
                            $allocationName="";
                            $empName="";
                            if($data['deliveryStatus']=="cancelled"){
                                echo "Cancelled";
                            }else{
                                // if($data['isAllocated']==1){ 
                                //     if(!empty($allocations)){
                                //         $allocationNumber=$allocations[0]['id'];
                                //         $allocationName=$allocations[0]['allocationCode'];
                                //         $empName=trim($allocations[0]['ename1']).','.trim($allocations[0]['ename2']).','.trim($allocations[0]['ename3']).','.trim($allocations[0]['ename4']);
                                //         $status="Allocated : ".$allocationName;
                                //     }
    
                                //     if(!empty($officeAllocations)){
                                //         $allocationNumber=$allocations[0]['id'];
                                //         $allocationName=$allocations[0]['allocationCode'];
                                //         $status="Allocated : ".$allocationName;
                                //     }
                                // }else{
                                //     if(!empty($allocationsHistory) || !empty($officeAllocationsHistory)){
                                //       if(!empty($allocationsHistory)){
                                //         $status="Past Bill";
                                //       }
                                //       if(!empty($officeAllocationsHistory)){
                                //         $status="Past Bill";
                                //       }
                                //     }else{
                                //         if($data['pendingAmt'] == $data['netAmount']){
                                //             $status="Unaccounted";
                                //         }else if($data['deliveryEmpName'] !==""){
                                //             $status="Direct Delivery";
                                //         }else if($data['pendingAmt'] <=0){
                                //             $status= "Bill Cleared";
                                //         }
                                //     }
                                // } 
                                if($data['isAllocated']==1){
                                    if(!empty($allocations)){
                                        echo "<span style='color:blue'>Allocated in : ".$allocations[0]['allocationCode'].'</span>';
                                    }

                                    if(!empty($officeAllocations)){
                                        echo "<span style='color:blue'>Allocated in : ".$officeAllocations[0]['allocationCode'].'</span>';
                                    }
                                }else{
                                    if($data['pendingAmt']==0){
                                        echo "<span style='color:green'> Cleared</span>";
                                    }else if($data['isDirectDeliveryBill']==1){
                                        echo "<span style='color:green'> Direct Delivery</span>";
                                    }else{
                                      if(!empty($allocationsHistory) || !empty($officeAllocationsHistory)){
                                        if(!empty($allocationsHistory)){
                                          echo "<span style='color:green'>Earlier Allocated </span>";
                                        }

                                        if(!empty($officeAllocationsHistory)){
                                          echo "<span style='color:green'>Already Allocated in : ".$officeAllocationsHistory[0]['allocationCode'].'</span>';
                                        }
                                      }else{
                                          if($data['pendingAmt']==$data['netAmount']){
                                             echo "<span style='color:red'> Unaccounted</span>";
                                          }else{
                                             echo "<span style='color:green'> Accounted</span>";
                                          }
                                          
                                      }
                                    }
                                } 
                                // echo $status;
                            }
                        }
                         ?>
                        
                            
                        </td>
                        <td>
                        <?php
                        if($data['isAllocated']!=1 && $data['pendingAmt'] >0 && $data['deliveryStatus'] !=="cancelled"){

                            $designation = ($this->session->userdata['logged_in']['designation']);
                            $des=explode(',',$designation);
                            $des = array_map('trim', $des);

                            if ((in_array('operator', $des))) { 
                    ?>
                                &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>
                                               
                    <?php
                            }else{
                    ?>
                     <a id="prDetailsForAll" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-salesman="<?php echo $data['salesman']; ?>" data-billDate="<?php echo $createdDate; ?>" data-credAdj="<?php echo $data['creditAdjustment']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-route="<?php echo $data['routeName']; ?>" data-toggle="modal" data-target="#processModalForAll" ><button class="btn btn-xs btn-primary waves-effect waves-float" data-toggle="tooltip" data-placement="bottom" title="Process"><i class="material-icons">touch_app</i></button></a>

                            <!-- <a id="prDetails" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-code="<?php echo $data['retailerCode']; ?>" data-salesman="<?php echo $data['salesman']; ?>" data-route="<?php echo $data['routeName']; ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-toggle="modal" data-target="#processModal"><button class="btn btn-xs  btn-primary"><i class="material-icons">touch_app</i></button></a> -->
                    &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billHistoryInfo/'.$data['id']); ?>" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="bottom" title="View History"><i class="material-icons">info</i></a>
                    &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>
                                               
                      <?php }

                        }else{
                            
                    ?>
                        &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billHistoryInfo/'.$data['id']); ?>" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="bottom" title="View History"><i class="material-icons">info</i></a>
                        &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>
                   
                    <?php
                        }

                        ?>
                        </td>
                        
                      </tr>

                    <?php
                    }
                } 
                ?>

        </tbody>
</table>

<?php if((!empty($resendBill)) || (!empty($signedBill))){ ?>


<table style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>
        <thead>
            <tr colspan="8">
                 <th>
                  <span style="color:blue"> Bill Signed / Resend History  </span>
              </th>
            </tr>
        </thead>
        <thead>
            <tr class="gray">
                <th colspan="4">Allocation No </th>
                <th colspan="4">Employee </th>
                <th colspan="4">Date</th>
                 <th colspan="4"> Status  </th>
            </tr>
        <thead>
            <tbody>
            <?php
                if(!empty($resendBill)){
                    foreach($resendBill as $resend){
                         $paidAmountTill=$this->AllocationByManagerModel->getAllocationBillNetAmt('billpayments',$resend['billId'],$resend['allocationId']);

                    $remAmount=0;
                    if(!empty($paidAmountTill)){
                        foreach($paidAmountTill as $amt){
                          if(($amt['allocationId'] >0) && ($amt['paymentMode']=='NEFT') && ($amt['isLostStatus']==2)){
                              echo $amt['allocationId'];
                              $remAmount=$amt['paidAmount']+$remAmount;
                          } else if(($amt['allocationId'] >0) && ($amt['paymentMode']=='Cheque') && ($amt['isLostStatus']==2)){
                              $remAmount=$amt['paidAmount']+$remAmount;
                          } else if(($amt['allocationId'] >0) && (($amt['paymentMode']=='Cash'))){
                              $remAmount=$amt['paidAmount']+$remAmount;
                          }   
                          
                        }
                    }
                    $dt=date_create($resend['alDate']);
                      $createdDate = date_format($dt,'d-M-Y');
            ?>
                    <tr>
                        <td colspan="4"><a target="_blank" href="<?php echo base_url().'index.php/AllocationByManagerController/CloseCompleteAllocation/'.$resend['allocationId']; ?>"><?php echo $resend['alCode']; ?></a></td>
                        <td colspan="4"><?php echo ($resend['ename1'].' '.$resend['ename2'].$resend['ename3'].$resend['ename4']) ; ?></td>
                        <td colspan="4"><?php echo $createdDate; ?></td>
                        <td colspan="4">
                            <?php
                            if((($resend['netAmount']-($remAmount)) <= 0)){
                                echo "Cleared";
                            } else if($resend['isResendBill']==1){
                                echo 'Resend';
                            } else if((($resend['netAmount']-($remAmount)) > 0) && ($resend['isResendBill'] == 0)){ 
                                echo "Signed";
                            }
                             ?>
                        </td>
                    </tr>
            <?php
                    
                    }
                    
                } 

                  
            ?>
        </tbody>
</table>
<?php } ?>


<?php if((!empty($billInfo)) || (!empty($bills)) || (!empty($billOfficeAdj))){ ?>

        <table style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>
        <thead>
            <tr colspan="7" align="center">
                  <th>
                  <span style="color:blue"> Bill Payment History  </span>
              </th>
            </tr>
        </thead>

        <thead>
            <tr class="gray">
                <th> S. No.</th>
                 <th> Allocation No. </th>
                 <th>Employee</th>
                <th>  Date  </th>
                <th> Receivable Amount </th>
                 <th> Transaction</th>
                 <th>Amount</th>
                 <th>Remaining Amount</th>
                  <th> Status  </th>
                   <th> Details  </th>
               
            </tr>
        </thead>
        <tbody>
                <?php
                $no=0;

                  if(!empty($billInfo)){
                  foreach ($billInfo as $data) {
                    $no++;
                      $dt=date_create($data['date']);
                      $createdDate = date_format($dt,'d-M-Y');
                      if($data['isDirectDeliveryBill']==1){

                      
              ?>
                  <tr>
                        <td><?php echo $no; ?></td>
                        <td></td>
                        <td><?php echo $data['deliveryEmpName']; ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo ($data['SRAmt']+$data['receivedAmt']+$data['pendingAmt']); ?></td>
                        <td><?php echo "Direct Delivery"; ?></td>
                        <td><?php echo ""; ?></td>
                        <td><?php echo ($data['SRAmt']+$data['receivedAmt']+$data['pendingAmt']); ?></td>
                        <td></td>
                        <td> </td>
                        
                      </tr>
            <?php
                    }
                  }
                }
                 if(!empty($bills)){
                    foreach ($bills as $data) 
                    {
                       $no++; 


                      $dt=date_create($data['date']);
                      $createdDate = date_format($dt,'d-M-Y');
                      if($data['paidAmount'] != 0){
                    ?>
                      <tr>
                         <?php if($data['ownerApproval']==2){ ?>
                                    <td><?php echo $no.'<span class="logo_prov">Rejected</span> '; ?></td>
                                <?php }else{ ?>
                                    <td><?php echo $no; ?></td>
                                <?php } ?>
                        <td><?php 
                                if(empty($data['allocationCode'])){ 
                                    echo '<center>-</center>'; 
                                }else {
                                    $idForAllocation=$data['allocationId'];
                                    $codeForAllocation=$data['allocationCode'];
                                    $url= site_url("AllocationByManagerController/CloseCompleteAllocation/".$idForAllocation);
                                    
                                    echo "<a target='_blank' href='".$url."'>".$codeForAllocation."</a>";
                                }
                            ?>
                            
                        </td>
                    <?php if($data['empId']==1){ ?>
                        <td><?php echo "Admin"; ?></td>
                    <?php }else{ ?>
                        <td><?php echo $data['name']; ?></td>
                    <?php } ?>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo ($data['balanceAmount']+$data['paidAmount']); ?></td>
                        <td><?php echo $data['paymentMode']; ?></td>
                        <td><?php echo $data['paidAmount']; ?></td>
                        <td><?php echo $data['balanceAmount']; ?></td>
                        <td>
                        <?php 
                            if($data['paymentMode']=='Cash'){
                                echo '-';
                            }else{
                                if($data['paymentMode']=='Cheque'){
                                    
                                    if($data['isLostStatus']==1){
                                        echo "Not Received";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']==''){
                                        echo "Received";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='New'){
                                        echo "Received, but not banked";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Banked'){
                                        echo "Banked, But Not Cleared";
                                    }

                                    if($data['chequeStatus']=='Bounced' || $data['chequeStatus']=='Bounced&Returned'){
                                        echo "Bounced";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Cleared'){
                                        echo "Cleared";
                                    }
                                }


                                if($data['paymentMode']=='NEFT'){
                                    if($data['isLostStatus']==1){
                                        echo "Not Received";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']==''){
                                        echo "Received,But not cleared";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='New'){
                                        echo "Received,But not cleared";
                                    }
                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Received'){
                                        echo "Cleared";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Not Received'){
                                        echo "Rejected";
                                    }
                                }

                            }

                            ?>
                             
                        </td>
                        <td>
                            <?php

                                if($data['paidAmount'] < 0){
                                    echo "Transaction edited by owner ".$data['name'].'.';
                                }

                                if($data['paymentMode']=='CD'){
                                    $cdRemark=$this->AllocationByManagerModel->getCdRemark('bill_remark_history',$data['paidAmount'],$data['billId']);
                                    // print_r($cdRemark);
                                    if(!empty($cdRemark)){
                                        echo $cdRemark[0]['remark'];
                                    }
                                }

                                if($data['paymentMode']=='Office Adjustment'){
                                    $officeRemark=$this->AllocationByManagerModel->getCdRemark('bill_remark_history',$data['paidAmount'],$data['billId']);
                                    // print_r($cdRemark);
                                    if(!empty($officeRemark)){
                                        echo $officeRemark[0]['remark'];
                                    }
                                }

                                if($data['paymentMode']=='Other Adjustment'){
                                    $otherRemark=$this->AllocationByManagerModel->getCdRemark('bill_remark_history',$data['paidAmount'],$data['billId']);
                                    // print_r($cdRemark);
                                    if(!empty($otherRemark)){
                                        echo $otherRemark[0]['remark'];
                                    }
                                }

                                if($data['paymentMode']=='Emp Delivery'){
                                    $officeRemark=$this->AllocationByManagerModel->getCdRemark('bill_remark_history',$data['paidAmount'],$data['billId']);
                                    // print_r($cdRemark);
                                    if(!empty($officeRemark)){
                                        echo $officeRemark[0]['remark'];
                                    }
                                }

                                $chequeDate=date_create($data['chequeDate']);
                                $chequeCreatedDate = date_format($chequeDate,'d-M-Y');

                                $neftDate=date_create($data['neftDate']);
                                $neftCreatedDate = date_format($neftDate,'d-M-Y');

                                if($data['paymentMode']=='Cheque'){
                                    if($data['isLostStatus']==1){
                                        echo "-";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']==''){
                                        echo "Received but not saved ";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='New'){
                                        echo "Cheque No. ".$data['chequeNo'].' dated '.$chequeCreatedDate.' Bank '.$data['chequeBank'];
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Banked'){
                                        echo "Cheque No. ".$data['chequeNo'].' dated '.$chequeCreatedDate.' Bank '.$data['chequeBank'];
                                    }

                                    if($data['isLostStatus']==2 && (($data['chequeStatus']=='Bounced') || ($data['chequeStatus']=='Bounced&Returned'))){
                                        echo "Cheque No. ".$data['chequeNo'].' dated '.$chequeCreatedDate.' Bank '.$data['chequeBank'].',<br> Reason :  '.$data['statusBouncedReason'];
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Cleared'){
                                        echo "Cheque No. ".$data['chequeNo'].' dated '.$chequeCreatedDate.' Bank '.$data['chequeBank'];
                                    }
                                }

                                if($data['paymentMode']=='NEFT'){
                                    if($data['isLostStatus']==1){
                                        echo "-";
                                    } 

                                    if($data['isLostStatus']==2 && $data['chequeStatus']==''){
                                        echo "Received but not saved ";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='New'){
                                        echo "NEFT No. ".$data['neftNo'].' dated '.$neftCreatedDate;
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Received'){
                                        echo "NEFT No. ".$data['neftNo'].' dated '.$neftCreatedDate;
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Not Received'){
                                        echo "NEFT No. ".$data['neftNo'].' dated '.$neftCreatedDate;
                                    }
                                }

                            ?>
                        </td>
                        
                      </tr>

                        <?php
                        }
                    }
                }


                if(!empty($billOfficeAdj)){
                    foreach ($billOfficeAdj as $data) 
                    {
                       $no++; 

                      $dt=date_create($data['updatedAt']);
                      $createdDate = date_format($dt,'d-M-Y');
                      if($data['amount'] !=0){
                    ?>
                      <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php echo $data['allocationCode']; ?></td>
                        <td><?php echo $data['name']; ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td></td>
                        <td>Office Adjustment</td>
                        <td>
                        
                        <?php echo $data['amount']; ?>
                        </td>
                        <td></td>
                        <td><?php echo "Office Allocation - ".$data['transactionType']; ?></td>
                        <td><?php echo $data['remark']; ?></td>
                        
                      </tr>

                        <?php
                        }
                    }
                }
                ?> 
        </tbody>
       </table>

      <?php 
         }

      if(!empty($billSr)){ ?>
       <table style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>

        <thead>
            <tr colspan="6" align="center">
                <th>
                  <span style="color:blue"> Bill SR/FSR Details  </span>
              </th>
            </tr>
        </thead>
        <thead>
            <tr class="gray">
               <th> Sr No  </th>
                 <th> Allocation Code  </th>
                 <th> Employee Name  </th>
                 <th>Date</th>
                 <th>Item Name</th>
                <th> Quantity  </th>
                <th> SR Quantity </th>
                <th>SR Amount</th>

            </tr>
        </thead>
        <tbody>
            <?php
               
                
                    $no=0;
                    foreach ($billSr as $data) 
                    {
                       $no++;

                      $dt=date_create($data['createdDate']);
                      $createdDate = date_format($dt,'d-M-Y');
                   
                   ?>
                        <tr>
                   
                        <td><?php echo $no; ?></td>
                        <td><?php 
                            $idForAllocation=$data['allocationId'];
                                    $codeForAllocation=$data['allocationCode'];
                                    $url= site_url("AllocationByManagerController/CloseCompleteAllocation/".$idForAllocation);
                                    
                                    echo "<a target='_blank' href='".$url."'>".$codeForAllocation."</a>";
                         ?></td>
                       <td><?php echo trim($data['ename1'].','.$data['ename2'].','.$data['ename3'].','.$data['ename4'].','.$data['ename5'],","); ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo $data['productName']; ?></td>
                        <td><?php echo $data['qty']; ?></td>
                        <td><?php echo $data['sr_qty']; ?></td>
                        <td><?php echo number_format(($data['netAmount']/$data['qty']*$data['sr_qty']),2); ?></td>
                        
                      </tr>

                    <?php
                    }
                
                ?>

            </tbody>
        </table>
    <?php } 
    
    }


    public function loadBillsHistory($id){
        $id=urldecode($id);
        $data['bills']=$this->AllocationByManagerModel->load('bills',$id);
        echo json_encode( $data['bills']);
    }

    public function retailerHistoryInformation($bills,$retailerName){
        
    ?>
        
     <table style="font-size: 12px;" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100' id="xp">
        <thead>
            <tr colspan="8">
                 <th>
                  <span style="color:blue"> Bill Information  </span>
              </th>
            </tr>
        </thead>

        <thead>
            <tr class="gray">
               
                 <th colspan="3"> Retailer Name  </th>
                 <th colspan="3">Retailer Code</th>
                <th colspan="3"> Route Name  </th>
                <th colspan="5">Retailer GST No.</th>
                

            </tr>
        </thead>
            <tbody>
            <?php
               
                if(!empty($bills)){
                    $retailerCode=$this->AllocationByManagerModel->loadRetailer($bills[0]['retailerCode']);
            ?>
                   
                    
            <tr>
                <td colspan="3"><?php echo $bills[0]['retailerName']; ?></td>
                <td colspan="3"><?php echo $bills[0]['retailerCode']; ?></td>
                <td colspan="3"><?php echo $bills[0]['routeName']; ?></td>
                 <td colspan="5"><?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?></td>
            </tr>

            <?php
                    
                }else{
            ?>
                    <tr><td colspan="14">No data available.</td></tr>
           <?php     
                } 
                
            ?>

        </tbody>
        </table>
     <table style="font-size: 12px;" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100' id="xp">
        <thead>
            <tr class="gray">
                 <th> Bill No  </th>
                 <th>Bill Date</th>
                <th> Salesman </th>
                 <th> Net Amount </th>
                 <th> SR  </th>
                  <th> CD  </th>
                 <th> Collection </th>
                 <th> Office Adj  </th>
                 <th> Other Adj  </th>
                  <th> Debit </th>
                  <th> Remaining  </th>
                  <th> Cheque Penalty  </th>
                  <th> Action  </th>
                  <th> Status  </th>
            </tr>
        </thead>
        <tbody>
            <?php
              
                if(!empty($bills)){
                    foreach ($bills as $data) 
                    {
                       

                      $dt=date_create($data['date']);
                      $createdDate = date_format($dt,'d-M-Y');
                   
                    if($data['isAllocated']==1){ ?>
                         <tr style="background-color: #dcd6d5">
                    <?php }else{ ?>
                         <tr>
                    <?php } ?>
                        
                        <td><?php echo $data['billNo']; ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo $data['salesman']; ?></td>
                        <td><?php echo $data['netAmount']; ?></td>
                        <td><?php echo $data['SRAmt']; ?></td>
                         <td><?php echo $data['cd']; ?></td>
                         <td><?php echo $data['receivedAmt']; ?></td>
                        <td><?php echo $data['officeAdjustmentBillAmount']; ?></td>
                        <td><?php echo $data['otherAdjustment']; ?></td>
                        <td><?php echo $data['debit']; ?></td>
                        <td><?php echo $data['pendingAmt']; ?></td>
                        <td><?php echo $data['chequePenalty']; ?></td>
                        <td>
                        <?php 
                                
                            $allocations=$this->AllocationByManagerModel->getAllocationDetailsByBill('bills',$data['id']);
                            $allocationsHistory=$this->AllocationByManagerModel->getAllocationDetailsByBillHistory('bills',$data['id']);
                            $officeAllocations=$this->AllocationByManagerModel->getOfficeAllocationDetailsByBill('bills',$data['id']);
                            $officeAllocationsHistory=$this->AllocationByManagerModel->getOfficeAllocationDetailsByBillHistory('bills',$data['id']);
                            // print_r($allocations);exit;
                            $status="";
                            $allocationNumber="";
                            $allocationName="";
                            $empName="";
                            if($data['isAllocated']==1){ 
                                if(!empty($allocations)){
                                    $allocationNumber=$allocations[0]['id'];
                                    $allocationName=$allocations[0]['allocationCode'];
                                    $empName=trim($allocations[0]['ename1']).','.trim($allocations[0]['ename2']).','.trim($allocations[0]['ename3']).','.trim($allocations[0]['ename4']);
                                    $status="Allocated : ".$allocationName;
                                }

                                if(!empty($officeAllocations)){
                                    $allocationNumber=$allocations[0]['id'];
                                    $allocationName=$allocations[0]['allocationCode'];
                                    $status="Allocated : ".$allocationName;
                                }
                            }else{
                                if(!empty($allocationsHistory) || !empty($officeAllocationsHistory)){
                                  if(!empty($allocationsHistory)){
                                    $status="Past Bill";
                                  }
                                  if(!empty($officeAllocationsHistory)){
                                    $status="Past Bill";
                                  }
                                }else{
                                    if($data['pendingAmt'] == $data['netAmount']){
                                        $status="Unaccounted";
                                    }else if($data['deliveryEmpName'] !==""){
                                        $status="Direct Delivery";
                                    }else if($data['pendingAmt'] <=0){
                                        $status= "Bill Cleared";
                                    }
                                }
                            } 

                            echo $status;

                         ?>
                     </td>
                        <!-- <td>
                            <a id="billHistory" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-code="<?php echo $data['retailerCode']; ?>" data-salesman="<?php echo $data['salesman']; ?>" data-route="<?php echo $data['routeName']; ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-toggle="modal" data-target="#billprocessModal"><button class="btn btn-xs bg-primary margin"><i class="material-icons">visibility</i> </button></a>
                            <a id="billHistoryProcess" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-code="<?php echo $data['retailerCode']; ?>" data-salesman="<?php echo $data['salesman']; ?>" data-route="<?php echo $data['routeName']; ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-toggle="modal" data-target="#retailerprocessModal"><button class="btn btn-xs bg-primary margin">Process</button></a>
                        </td> -->
                        <td>
                        <?php 
                                $designation = ($this->session->userdata['logged_in']['designation']);
                                $des=explode(',',$designation);
                                $des = array_map('trim', $des);
                                if($data['isAllocated']!=1 && $data['pendingAmt'] >0){ 
                                    

                                    if ((in_array('operator', $des))) { 
                                ?>
                                &nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>


                                <?php    }else{ 

                                ?>
                            <a id="prDetails" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-salesman="<?php echo $data['salesman']; ?>"  data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-toggle="modal" data-target="#processModal"><button class="btn btn-xs btn-primary waves-effect"><i class="material-icons">touch_app</i> </button></a>
                                <!-- <a id="billHistory" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-code="<?php echo $data['retailerCode']; ?>" data-salesman="<?php echo $data['salesman']; ?>" data-route="<?php echo $data['routeName']; ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-toggle="modal" data-target="#billprocessModal"><button class="btn btn-xs btn-primary waves-effect"><i class="material-icons">info</i> </button></a> -->
                                &nbsp;<a href="<?php echo site_url('AdHocController/billHistoryInfo/'.$data['id']); ?>" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="bottom" title="View History"><i class="material-icons">info</i></a>
                                &nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>
                                                  
                            <?php   
                                }
                        }else{ 

                                if ((in_array('operator', $des))) { 
                                ?>
                                &nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>


                                <?php    }else{ 

                            ?>
                                    &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billHistoryInfo/'.$data['id']); ?>" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="bottom" title="View History"><i class="material-icons">info</i></a>
                                    &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>
                        <?php 
                            }
                                    
                                }
                          ?>             
                        </td>
                        
                      </tr>

                    <?php
                    }
                 }else{

                    ?>
                    <tr><td colspan="14">No data available.</td></tr>
           <?php     } 
                ?>

        </tbody>
</table>

<?php
    }


    public function retailerBillHistory($billId,$bills,$billOfficeAdj,$billSr){
        $billInfo=$this->AllocationByManagerModel->load('bills',$billId);
        $retailerCode=$this->AllocationByManagerModel->loadRetailer($billInfo[0]['retailerCode']);
        $resendBill=$this->AllocationByManagerModel->getResendBill('allocationsbills',$billId);
        $signedBill=$this->AllocationByManagerModel->getSignedBill('allocationsbills',$billId);
        
    ?>
    
     <table style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>
        <thead>
            <tr colspan="8">
                 <th>
                  <span style="color:blue"> Bill Information  </span>
              </th>
            </tr>
        </thead>
        <thead>
            <tr class="gray">
                <th colspan="3"> Retailer Name  </th>
                <th colspan="3">Retailer Code</th>
                <th colspan="3"> Route Name  </th>
                <th colspan="5">Retailer GST No.</th>
            </tr>
        </thead>
            <tbody>
            <?php
                if(!empty($billInfo)){
                    foreach ($billInfo as $data) 
                    {
            ?>
                    <tr>
                        <td colspan="3"><?php echo $data['retailerName']; ?></td>
                        <td colspan="3"><?php echo $data['retailerCode']; ?></td>
                        <td colspan="3"><?php echo $data['routeName']; ?></td>
                        <td colspan="5"><?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?></td>
                    </tr>

            <?php
                    }
                } 
            ?>
        </tbody>
        <thead>
            <tr class="gray">
                 <th> Bill No  </th>
                 <th>Bill Date</th>
                 <th> Salesman </th>
                 <th> Net Amount </th>
                 <th> SR  </th>
                 <th> CD  </th>
                 <th> Collection </th>
                 <th> Office Adj  </th>
                 <th> Other Adj  </th>
                 <th> Debit </th>
                 <th> Remaining  </th>
                 <th> Cheque Penalty  </th>
                 <th>Action</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
                if(!empty($billInfo)){
                    foreach ($billInfo as $data) 
                    {
                        $dt=date_create($data['date']);
                        $createdDate = date_format($dt,'d-M-Y');
                   
                        if($data['isAllocated']==1){ ?>
                             <tr style="background-color: #dcd6d5">
                        <?php }else{ ?>
                             <tr>
                        <?php } ?>

                        <td><?php echo $data['billNo']; ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo $data['salesman']; ?></td>
                        <td><?php echo $data['netAmount']; ?></td>
                        <td><?php echo ($data['SRAmt']+$data['fsSrAmt']); ?></td>
                         <td><?php echo $data['cd']; ?></td>
                         <td><?php echo ($data['receivedAmt']+$data['fsCashAmt']+$data['fsChequeAmt']+$data['fsNeftAmt']); ?></td>
                        <td><?php echo $data['officeAdjustmentBillAmount']; ?></td>
                        <td><?php echo $data['otherAdjustment']; ?></td>
                        <td><?php echo $data['debit']; ?></td>
                        <td><?php echo $data['pendingAmt']; ?></td>
                        <td><?php echo $data['chequePenalty']; ?></td>
                        <td>
                            &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billHistoryInfo/'.$data['id']); ?>" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="bottom" title="View History"><i class="material-icons">info</i></a>
                            &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>
                                               
                        </td>
                        
                      </tr>

                    <?php
                    }
                } 
                ?>

        </tbody>
</table>

<?php if((!empty($resendBill)) || (!empty($signedBill))){ ?>


<table style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>
        <thead>
            <tr colspan="8">
                 <th>
                  <span style="color:blue"> Bill Resend/Signed Information  </span>
              </th>
            </tr>
        </thead>
        <thead>
            <tr class="gray">
                <th colspan="4">Allocation No </th>
                <th colspan="4">Date</th>
                 <th colspan="4"> Status  </th>
            </tr>
        <thead>
            <tbody>
            <?php
                if(!empty($resendBill)){
                    $dt=date_create($resendBill[0]['alDate']);
                      $createdDate = date_format($dt,'d-M-Y');
            ?>
                    <tr>
                        <td colspan="4"><?php echo $resendBill[0]['alCode']; ?></td>
                        <td colspan="4"><?php echo $createdDate; ?></td>
                        <td colspan="4"><?php echo 'Resend'; ?></td>
                    </tr>
            <?php
                    
                } 

                 if(!empty($signedBill)){
                    $dt=date_create($signedBill[0]['alDate']);
                      $createdDate = date_format($dt,'d-M-Y');
                    
            ?>
                    <tr>
                        <td colspan="4"><?php echo $signedBill[0]['alCode']; ?></td>
                        <td colspan="4"><?php echo $createdDate; ?></td>
                        <td colspan="4"><?php echo 'Signed'; ?></td>
                    </tr>
            <?php
                    
                }  
            ?>
        </tbody>
</table>
<?php } ?>


<?php if((!empty($bills)) || (!empty($billOfficeAdj))){ ?>

        <table style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>
        <thead>
            <tr colspan="7" align="center">
                  <th>
                  <span style="color:blue"> Bill Transaction Details  </span>
              </th>
            </tr>
        </thead>

        <thead>
            <tr class="gray">
                <th> S. No.</th>
                 <th> Allocation No. </th>
                 <th>Employee</th>
                <th>  Date  </th>
                <th> Receivable Amount </th>
                 <th> Transaction</th>
                 <th>Amount</th>
                 <th>Remaining Amount</th>
                  <th> Status  </th>
                   <th> Details  </th>
               
            </tr>
        </thead>
        <tbody>
                <?php
                $no=0;
                 if(!empty($bills)){
                    foreach ($bills as $data) 
                    {
                       $no++; 

                      $dt=date_create($data['date']);
                      $createdDate = date_format($dt,'d-M-Y');
                      if($data['paidAmount']>0){
                    ?>
                      <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php 
                                if(empty($data['allocationCode'])){ 
                                    echo '<center>-</center>'; 
                                }else {
                                    echo $data['allocationCode']; 
                                }
                            ?>
                            
                        </td>
                        <td><?php echo $data['name']; ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo ($data['balanceAmount']+$data['paidAmount']); ?></td>
                        <td><?php echo $data['paymentMode']; ?></td>
                        <td><?php echo $data['paidAmount']; ?></td>
                        <td><?php echo $data['balanceAmount']; ?></td>
                        <td>
                        <?php 
                            if($data['paymentMode']=='Cash'){
                                echo '-';
                            }else{
                                if($data['paymentMode']=='Cheque'){
                                    
                                    if($data['isLostStatus']==1){
                                        echo "Not Received";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']==''){
                                        echo "Received";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='New'){
                                        echo "Received, but not banked";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Banked'){
                                        echo "Banked, But Not Cleared";
                                    }

                                    if($data['chequeStatus']=='Bounced' || $data['chequeStatus']=='Bounced&Returned'){
                                        echo "Bounced";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Cleared'){
                                        echo "Cleared";
                                    }
                                }


                                if($data['paymentMode']=='NEFT'){
                                    if($data['isLostStatus']==1){
                                        echo "Not Received";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']==''){
                                        echo "Received,But not cleared";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='New'){
                                        echo "Received,But not cleared";
                                    }
                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Received'){
                                        echo "Cleared";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Not Received'){
                                        echo "Rejected";
                                    }
                                }

                            }

                            ?>
                             
                        </td>
                        <td>
                            <?php

                                if($data['paymentMode']=='CD'){
                                    $cdRemark=$this->AllocationByManagerModel->getCdRemark('bill_remark_history',$data['paidAmount'],$data['billId']);
                                    // print_r($cdRemark);
                                    if(!empty($cdRemark)){
                                        echo $cdRemark[0]['remark'];
                                    }
                                }

                                if($data['paymentMode']=='Office Adjustment'){
                                    $officeRemark=$this->AllocationByManagerModel->getCdRemark('bill_remark_history',$data['paidAmount'],$data['billId']);
                                    // print_r($cdRemark);
                                    if(!empty($officeRemark)){
                                        echo $officeRemark[0]['remark'];
                                    }
                                }

                                if($data['paymentMode']=='Other Adjustment'){
                                    $otherRemark=$this->AllocationByManagerModel->getCdRemark('bill_remark_history',$data['paidAmount'],$data['billId']);
                                    // print_r($cdRemark);
                                    if(!empty($otherRemark)){
                                        echo $otherRemark[0]['remark'];
                                    }
                                }

                                if($data['paymentMode']=='Emp Delivery'){
                                    $officeRemark=$this->AllocationByManagerModel->getCdRemark('bill_remark_history',$data['paidAmount'],$data['billId']);
                                    // print_r($cdRemark);
                                    if(!empty($officeRemark)){
                                        echo $officeRemark[0]['remark'];
                                    }
                                }

                                $chequeDate=date_create($data['chequeDate']);
                                $chequeCreatedDate = date_format($chequeDate,'d-M-Y');

                                $neftDate=date_create($data['neftDate']);
                                $neftCreatedDate = date_format($neftDate,'d-M-Y');

                                if($data['paymentMode']=='Cheque'){
                                    if($data['isLostStatus']==1){
                                        echo "-";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']==''){
                                        echo "Received but not saved ";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='New'){
                                        echo "Cheque No. ".$data['chequeNo'].' dated '.$chequeCreatedDate.' Bank '.$data['chequeBank'];
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Banked'){
                                        echo "Cheque No. ".$data['chequeNo'].' dated '.$chequeCreatedDate.' Bank '.$data['chequeBank'];
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Bounced'){
                                        echo "Cheque No. ".$data['chequeNo'].' dated '.$chequeCreatedDate.' Bank '.$data['chequeBank'];
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Cleared'){
                                        echo "Cheque No. ".$data['chequeNo'].' dated '.$chequeCreatedDate.' Bank '.$data['chequeBank'];
                                    }
                                }

                                if($data['paymentMode']=='NEFT'){
                                    if($data['isLostStatus']==1){
                                        echo "-";
                                    } 

                                    if($data['isLostStatus']==2 && $data['chequeStatus']==''){
                                        echo "Received but not saved ";
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='New'){
                                        echo "NEFT No. ".$data['neftNo'].' dated '.$neftCreatedDate;
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Received'){
                                        echo "NEFT No. ".$data['neftNo'].' dated '.$neftCreatedDate;
                                    }

                                    if($data['isLostStatus']==2 && $data['chequeStatus']=='Not Received'){
                                        echo "NEFT No. ".$data['neftNo'].' dated '.$neftCreatedDate;
                                    }
                                }

                            ?>
                        </td>
                        
                      </tr>

                        <?php
                        }
                    }
                }


                if(!empty($billOfficeAdj)){
                    foreach ($billOfficeAdj as $data) 
                    {
                       $no++; 

                      $dt=date_create($data['updatedAt']);
                      $createdDate = date_format($dt,'d-M-Y');
                      if($data['amount']>0){
                    ?>
                      <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php echo $data['allocationCode']; ?></td>
                        <td><?php echo $data['name']; ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo $data['amount']; ?></td>
                        <td>Office Adjustment</td>
                        <td>
                        <?php echo $data['transactionType']; ?>
                        </td>
                        
                      </tr>

                        <?php
                        }
                    }
                }
                ?> 
        </tbody>
       </table>

      <?php 
         }

      if(!empty($billSr)){ ?>
       <table style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>

        <thead>
            <tr colspan="6" align="center">
                <th>
                  <span style="color:blue"> Bill SR/FSR Details  </span>
              </th>
            </tr>
        </thead>
        <thead>
            <tr class="gray">
               <th> Sr No  </th>
                 <th> Allocation Code  </th>
                 <th>Date</th>
                 <th>Item Name</th>
                <th> Quantity  </th>
                <th> SR Quantity </th>

            </tr>
        </thead>
        <tbody>
            <?php
               
                
                    $no=0;
                    foreach ($billSr as $data) 
                    {
                       $no++;

                      $dt=date_create($data['createdDate']);
                      $createdDate = date_format($dt,'d-M-Y');
                   
                   ?>
                        <tr>
                   
                        <td><?php echo $no; ?></td>
                        <td><?php echo $data['allocationCode']; ?></td>
                        <td><?php echo $createdDate; ?></td>
                        <td><?php echo $data['productName']; ?></td>
                        <td><?php echo $data['qty']; ?></td>
                        <td><?php echo $data['sr_qty']; ?></td>
                        
                      </tr>

                    <?php
                    }
                
                ?>

            </tbody>
        </table>
    <?php } 
    
    }

}

?>
