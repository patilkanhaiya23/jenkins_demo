<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
class CompanyDataUploadingController extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('ExcelModel');
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
    }

    //check dates for uploading data
    public function checkDatesForCompany(){
        $company=$this->input->post('company');
        if($company==="Nestle"){
            $data=$this->ExcelModel->getPendingDatesFromBills('bills',$company);
            // print_r($data);
            if(!empty($data)){
                $excelDate=$data[0]['date'];
                // $next_date= date('Y-m-d', strtotime($excelDate. ' -15 days'));
                $next_date= date('Y-m-d', strtotime($excelDate));
                $date= date('d M, Y', strtotime($next_date));
                $message ="Please upload data from date: ".$date;
                $dataRes= ['date'=>$next_date,'message'=>$message];
                echo json_encode($dataRes);
            }else{
                $data=$this->ExcelModel->getDeliveredDatesFromBills('bills',$company); 
                if(!empty($data)){
                    $excelDate=$data[0]['date'];
                    // $next_date= date('Y-m-d', strtotime($excelDate. ' -15 days'));
                    $next_date= date('Y-m-d', strtotime($excelDate));
                    $date= date('d M, Y', strtotime($next_date));
                    $message ="Please upload data from date: ".$date;
                    $dataRes= ['date'=>$next_date,'message'=>$message];
                    echo json_encode($dataRes);
                }
            }
        }else if($company==="Parle"){
            $data=$this->ExcelModel->getDeliveredDatesFromBills('bills',$company); 
            if(!empty($data)){
                $excelDate=$data[0]['date'];
                // $next_date= date('Y-m-d', strtotime($excelDate. ' -15 days'));
                $next_date= date('Y-m-d', strtotime($excelDate));
                $date= date('d M, Y', strtotime($next_date));
                $message ="Please upload data from date: ".$date;
                $dataRes= ['date'=>$next_date,'message'=>$message];
                echo json_encode($dataRes);
            }
        }else if($company==="ITC"){
            $data=$this->ExcelModel->getDeliveredDatesFromBills('bills',$company); 
            if(!empty($data)){
                $excelDate=$data[0]['date'];
                // $next_date= date('Y-m-d', strtotime($excelDate. ' -15 days'));
                $next_date= date('Y-m-d', strtotime($excelDate));
                $date= date('d M, Y', strtotime($next_date));
                $message ="Please upload data from date: ".$date;
                $dataRes= ['date'=>$next_date,'message'=>$message];
                echo json_encode($dataRes);
            }
        }
    }

    //loading data
    public function index(){
        $data['company']=$this->ExcelModel->getAllCompanies('company');
        $this->load->view('dataUploadingView',$data);
    }

    public function uploadFilesForImport(){
        $userId = $this->session->userdata['logged_in']['id'];
        $compName=trim($this->input->post('company'));
        $dateForUploadBills=$this->input->post('dateForUpload');
        
        //file name
        $bill=$_FILES['billFile']['name'];
        $billDetail=$_FILES['billDetailFile']['name'];
        $retailerDetail=$_FILES['retailerDetailFile']['name'];
        
        //file type
        $billType=$_FILES['billFile']['type'];
        $billDetailType=$_FILES['billDetailFile']['type'];
        $retailerDetailType=$_FILES['retailerDetailFile']['type'];

        //file temp_name
        $billTempName=$_FILES['billFile']['tmp_name'];
        $billDetailTempName=$_FILES['billDetailFile']['tmp_name'];
        $retailerDetailTempName=$_FILES['retailerDetailFile']['tmp_name'];

        if($compName == "Nestle"){
            $nestleErr= $this->nestleExcelUploading($bill,$billType,$billTempName,$dateForUploadBills);
            if(trim($nestleErr) !==""){
                echo $nestleErr;
            }else{
                if($bill !=="" && $billDetail !==""){
                    $billFileName="";
                    $billDetailFileName="";
                    $retailerFileName="";
                    if($bill !==""){
                        $billFileName = trim($this->uploadFile('billFile'));
                    }
                    if($billDetail !==""){
                        $billDetailFileName = trim($this->uploadFile('billDetailFile'));
                    }

                    if($retailerDetail !==""){
                        $retailerFileName = trim($this->uploadFile('retailerDetailFile'));
                    }

                    if($billFileName=="" || $billDetailFileName==""){
                        echo "Files are not uploaded properly...Please upload files again...!";
                    }else{
                        $insertData=array(
                            'billFile'=>$billFileName,
                            'billDetailFile'=>$billDetailFileName,
                            'retailerFile'=>$retailerFileName,
                            'company'=>$compName,
                            'uploadedDate'=>$dateForUploadBills,
                            'uploadedBy'=>$userId,
                            'uploadedAt'=>date('Y-m-d H:i:sa')
                        );
                        // print_r($insertData);exit;

                        $this->ExcelModel->insert('uploaded_files_details',$insertData);
                        if($this->db->affected_rows() > 0){
                            echo "Files uploaded successfully";
                            // $this->importUploadedFiles();
                        }
                    }
                }else{
                    echo "Please select file";
                }
            } 
        }else if($compName == "ITC"){
            $itcErr= $this->checkItcExcelUploading($bill,$billType,$billTempName,$dateForUploadBills);
            // $itcErr= $this->itcExcelUploading($bill,$billType,$billTempName,$dateForUploadBills);

            // print_r($itcErr);exit;
            if(trim($itcErr) !==""){
                echo $itcErr;
            }else{
                if($bill !==""){
                    $billFileName="";
                    $billDetailFileName="";
                    $retailerFileName="";
                    if($bill !==""){
                        $billFileName = trim($this->uploadFile('billFile'));
                    }

                    if($billFileName==""){
                        echo "Files are not uploaded properly...Please upload files again...!";
                    }else{
                        $insertData=array(
                            'billFile'=>$billFileName,
                            'company'=>$compName,
                            'uploadedDate'=>$dateForUploadBills,
                            'uploadedBy'=>$userId,
                            'uploadedAt'=>date('Y-m-d H:i:sa')
                        );
                        $this->ExcelModel->insert('uploaded_files_details',$insertData);
                        if($this->db->affected_rows() > 0){
                            echo "Files uploaded successfully";
                        }
                    }
                }else{
                    echo "Please select file";
                }
            }
        }else if($compName == "Parle"){
            $parleErr= $this->checkParleExcelUploading($bill,$billType,$billTempName,$dateForUploadBills);
            if(trim($parleErr) !==""){
                echo $parleErr;
            }else{
                if($bill !==""){
                    $billFileName="";
                    $billDetailFileName="";
                    $retailerFileName="";
                    if($bill !==""){
                        $billFileName = trim($this->uploadFile('billFile'));
                    }
                    if($billDetail !==""){
                        $billDetailFileName = trim($this->uploadFile('billDetailFile'));
                    }

                    if($retailerDetail !==""){
                        $retailerFileName = trim($this->uploadFile('retailerDetailFile'));
                    }

                    if($billFileName=="" || $billDetailFileName==""){
                        echo "Files are not uploaded properly...Please upload files again...!";
                    }else{
                        $insertData=array(
                            'billFile'=>$billFileName,
                            'billDetailFile'=>$billDetailFileName,
                            'retailerFile'=>$retailerFileName,
                            'company'=>$compName,
                            'uploadedDate'=>$dateForUploadBills,
                            'uploadedBy'=>$userId,
                            'uploadedAt'=>date('Y-m-d H:i:sa')
                        );
                        $this->ExcelModel->insert('uploaded_files_details',$insertData);
                        if($this->db->affected_rows() > 0){
                            echo "Files uploaded successfully";
                        }
                    }
                }else{
                    echo "Please select file";
                }
            }
        }
    }

    //data uploading
    public function uploadFile($fileName) 
    {
        $upload_path='./assets/uploads/excels'; 
        $config = array(
            'upload_path' => $upload_path,
            'overwrite' => true,
            'allowed_types' => '*',
            // 'allowed_types' => 'xlsx|xls|csv',
            'max_size' => 51200,
            'file_ext_tolower' => true,
            'remove_spaces' => true
        );
        $this->load->library('upload', $config);
        if(!$this->upload->do_upload($fileName)) {
            return "";
        } else {
            $uploadData = $this->upload->data();
            $fileName =  $uploadData['file_name'];
            return $fileName;
        }
    }

    //import function for data uploading using CronJob 
    public function importUploadedFiles(){
        $uploadedData=$this->ExcelModel->getLatesttUploadedFileData('uploaded_files_details');
        $compName="";
        if(!empty($uploadedData)){
            $compName=$uploadedData[0]['company'];

            if($compName=="Nestle"){
                $billFile=trim($uploadedData[0]['billFile']);
                $billDetailFile=trim($uploadedData[0]['billDetailFile']);
                $retailerFile=trim($uploadedData[0]['retailerFile']);
                $company=trim($uploadedData[0]['company']);
                $uploadedDate=trim($uploadedData[0]['uploadedDate']);
    
                if(($billFile !=="") && ($billDetailFile!=="")){
                    $billFilePath='./assets/uploads/excels/'.$billFile;
                    $billDetailFilePath='./assets/uploads/excels/'.$billDetailFile;
                    $retailerBillDetailFilePath="";
                    if($retailerFile !=""){
                        $retailerBillDetailFilePath='./assets/uploads/excels/'.$retailerFile;
                    }
                    
                    if($company==="Nestle"){
                        $this->uploadNestleBillData($billFilePath);
                        $this->uploadBillDetailsData($billDetailFilePath);
                        if($retailerBillDetailFilePath !==""){
                            $this->uploadNestleRetailerBillData($retailerBillDetailFilePath);
                        }
                    }
                }
            }
    
            //import Parle Data
            if($compName=="Parle"){
                $billFile=trim($uploadedData[0]['billFile']);
                $billDetailFile=trim($uploadedData[0]['billDetailFile']);
                $retailerFile=trim($uploadedData[0]['retailerFile']);
                $company=trim($uploadedData[0]['company']);
                $uploadedDate=trim($uploadedData[0]['uploadedDate']);
                
                // echo $billFile.' '.$billDetailFile.' '.$retailerFile;exit;
    
                if(($billFile !=="") && ($billDetailFile !=="")){
                    $billFilePath='./assets/uploads/excels/'.$billFile;
                    $billDetailFilePath='./assets/uploads/excels/'.$billDetailFile;

                    $retailerBillDetailFilePath="";
                    if($retailerFile !=""){
                        $retailerBillDetailFilePath='./assets/uploads/excels/'.$retailerFile;
                    }

                    if($company==="Parle"){
                        $this->uploadParleExcelUploading($billFilePath);
                        $this->uploadParleBillDetailsExcelUploading($billDetailFilePath);
                        if($retailerBillDetailFilePath !==""){
                            $this->uploadParleRetailerExcelUploading($retailerBillDetailFilePath);
                        }
                    }
                }
            }
    
            //import ITC Data
            if($compName=="ITC"){
                $billFile=trim($uploadedData[0]['billFile']);
                $billDetailFile=trim($uploadedData[0]['billDetailFile']);
                $company=trim($uploadedData[0]['company']);
                $uploadedDate=trim($uploadedData[0]['uploadedDate']);
    
                if(($billFile !=="")){
                    $billFilePath='./assets/uploads/excels/'.$billFile;
                    // $billDetailFilePath='./assets/uploads/excels/'.$billDetailFile;
                    if($company==="ITC"){
                        $this->itcExcelUploading($billFilePath);
                    }
                }
            }
        }
        // $uploadedNestleData=$this->ExcelModel->getUploadedFileData('uploaded_files_details','Nestle');
        // $uploadedParleData=$this->ExcelModel->getUploadedFileData('uploaded_files_details','Parle');

        // print_r($uploadedParleData);exit;
        // $uploadedItcData=$this->ExcelModel->getUploadedFileData('uploaded_files_details','ITC');

        //import Nestle Data
        
    }

     //import function for data uploading using CronJob 
    public function importUploadedBillsFiles(){
        $uploadedNestleData=$this->ExcelModel->getUploadedFileData('uploaded_files_details','Nestle');
        $uploadedParleData=$this->ExcelModel->getUploadedFileData('uploaded_files_details','Parle');
        $uploadedItcData=$this->ExcelModel->getUploadedFileData('uploaded_files_details','ITC');

        //import Nestle Data
        if(!empty($uploadedNestleData)){
            $billFile=trim($uploadedNestleData[0]['billFile']);
            $company=trim($uploadedNestleData[0]['company']);
            $uploadedDate=trim($uploadedNestleData[0]['uploadedDate']);

            if(($billFile !=="")){
                $billFilePath='./assets/uploads/excels/'.$billFile;
                
                if($company==="Nestle"){
                    $this->uploadNestleBillData($billFilePath);
                }
            }
        }

        //import Parle Data
        if(!empty($uploadedParleData)){
            $billFile=trim($uploadedParleData[0]['billFile']);
            $company=trim($uploadedParleData[0]['company']);
            $uploadedDate=trim($uploadedParleData[0]['uploadedDate']);

            if(($billFile !=="")){
                $billFilePath='./assets/uploads/excels/'.$billFile;
                if($company==="Parle"){
                    $this->uploadParleBillData();
                }
            }
        }

        //import ITC Data
        if(!empty($uploadedItcData)){
            $billFile=trim($uploadedItcData[0]['billFile']);
            $company=trim($uploadedItcData[0]['company']);
            $uploadedDate=trim($uploadedItcData[0]['uploadedDate']);

            if(($billFile !=="")){
                $billFilePath='./assets/uploads/excels/'.$billFile;
                if($company==="ITC"){
                    $this->uploadItcBillData();
                }
            }
        }
    }

    //Import Nestle Bills
    public function uploadNestleBillData($billFilePath){
        //for get last 5 days records 
        // $dateForUploading = date('Y-m-d',strtotime('-5 day'));
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        $cnt=0;
        $billNumberHeader="";
        $billDateHeader="";
        $retailerNameHeader = "";
        $retailerCodeHeader = "";
        $billNetAmountHeader = "";
        $netAmountHeader = "";
        $creditAdjustmentHeader="";
        $grossAmountHeader="";
        $taxAmountHeader="";
        $billNumber="";
        for ($row = 1; $row <= $highestRow; ++$row) {
            //A row selected
            $cnt++;
            for($i=1;$i<=$highestColumnIndex;$i++){
                // echo $worksheet->getCellByColumnAndRow($i, $row)->getValue().'<br>';
                if($row==1){
                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill Number"){
                        $billNumberHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill Date"){
                        $billDateHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Retailer Code"){
                        $retailerCodeHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Customer Name"){
                        $retailerNameHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Net Amount"){
                        $billNetAmountHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill receivable amount"){
                        $netAmountHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Credit Adjustment"){
                        $creditAdjustmentHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Gross Amount"){
                        $grossAmountHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Tax Amount"){
                        $taxAmountHeader= $i;
                    }
                }
            }

            if(($row==1) && (empty($billNumberHeader) || empty($taxAmountHeader) || empty($grossAmountHeader) || empty($billDateHeader) || empty($retailerCodeHeader) || empty($retailerNameHeader) || empty($billNetAmountHeader) || empty($netAmountHeader) || empty($creditAdjustmentHeader))){
                echo "Please select correct files for uploading";
                exit;
            }               
            
            $billNumber = $worksheet->getCellByColumnAndRow($billNumberHeader, $row)->getValue();
            $billDate = $worksheet->getCellByColumnAndRow($billDateHeader, $row)->getValue();
            

            $retailerName = $worksheet->getCellByColumnAndRow($retailerNameHeader, $row)->getValue();
            $retailerCode = $worksheet->getCellByColumnAndRow($retailerCodeHeader, $row)->getValue();
            $amount = $worksheet->getCellByColumnAndRow($billNetAmountHeader, $row)->getValue();
            $netAmount = $worksheet->getCellByColumnAndRow($netAmountHeader, $row)->getValue();
            $creditAdjustment=$worksheet->getCellByColumnAndRow($creditAdjustmentHeader, $row)->getValue();

            $grossAmount=$worksheet->getCellByColumnAndRow($grossAmountHeader, $row)->getValue();
            $taxAmount=$worksheet->getCellByColumnAndRow($taxAmountHeader, $row)->getValue();


            $excelDate="";
            // echo $billDate;
            $extension="";
            if($extension==='csv'){
                if(!empty($billDate)){
                    $excelDate=date('Y-m-d', strtotime($billDate));
                }
            }else{
                if(!empty($billDate) && ($billDate !=='Bill Date')){
                    $billDate =str_replace("/","-",$billDate);
                    $excelDate=date('Y-m-d', strtotime($billDate));
                }
                // if(!empty($billDate) && $billDate !=='Bill Date'){
                //     $billDate =str_replace("/","-",$billDate);
                //     $date = ($billDate - 25569) * 86400;
                //     $excelDate=date('Y-m-d', $date);//convert date from excel data
                // }
            }

            // if($dateForUploadBills !==""){
            //     $excelDate= date('Y-m-d', strtotime($excelDate. ' -15 days'));
            //     if($excelDate > $dateForUploadBills && $billDate !=='Bill Date'){
            //         echo "Please upload bills from date: ".$dateForUploadBills;
            //         exit;
            //     }
            // }
            // get 1st day
            $timestamp = strtotime($excelDate);
            $day= date("d", $timestamp);
            // echo $excelDate.' '.$day;exit;

            // if(($day != "01" || $day != "1") && $cnt == 1){
            //     echo "date not starting from 1st";
            //     exit;
            // }
            // echo $excelDate.'    '.$billDate;
            // check bill exist or not
            $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNumber);
            if(empty($billExist)){
                if((!empty($excelDate)) && ($excelDate != "1970-01-01") && ($billDate !=='Bill Date')){
                    $data = array(
                      'date'=>$excelDate,
                      'billNo'=>$billNumber,
                      'retailerName'=>$retailerName,
                      'retailerCode'=>$retailerCode,
                      'grossAmount'=>$grossAmount,
                      'taxAmount'=>$taxAmount,
                      'creditAdjustment'=>$creditAdjustment,
                      'billNetAmount'=>round($amount),
                      'netAmount'=>round($netAmount),
                      'pendingAmt'=>round($netAmount),
                      'compName'=>'Nestle'
                    );
                    $this->ExcelModel->insert('bills',$data);
                    // array_push($batchInsert, $data);
                }
            }else{
                $billId=$billExist[0]['id'];
                
                $billDeliveryStatus=$billExist[0]['deliveryStatus'];
                $billNetAmount=$billExist[0]['billNetAmount'];
                $billPendingAmt=$billExist[0]['pendingAmt'];

                $billSrAmt=$billExist[0]['SRAmt'];
                $billReceivedAmt=$billExist[0]['receivedAmt'];
                $billCd=$billExist[0]['cd'];
                $billDebit=$billExist[0]['debit'];
                $billOfficeAdjustment=$billExist[0]['officeAdjustmentBillAmount'];
                $billOtherAdjustment=$billExist[0]['otherAdjustment'];
                //  
                if(($billNetAmount != $amount) && ($billDeliveryStatus==="pending" || $billDeliveryStatus==="")){
                    $totalRecAmt=$billSrAmt+$billReceivedAmt+$billCd+$billDebit+$billOfficeAdjustment+$billOtherAdjustment;
                    $newPendingAmt=$netAmount-$totalRecAmt;
                    
                    if((!empty($excelDate)) && ($excelDate != "1970-01-01")){
                        $data = array(
                          'date'=>$excelDate,
                          'billNo'=>$billNumber,
                          'retailerName'=>$retailerName,
                          'retailerCode'=>$retailerCode,
                          'grossAmount'=>$grossAmount,
                          'taxAmount'=>$taxAmount,
                          'creditAdjustment'=>$creditAdjustment,
                          'billNetAmount'=>round($amount),
                          'netAmount'=>round($netAmount),
                          'pendingAmt'=>round($newPendingAmt),
                          'compName'=>'Nestle'
                        );
                        $this->ExcelModel->update('bills',$data,$billId);
                    }
                }
            }
        }

        $lastBill=$this->ExcelModel->getlastBills('bills','Nestle');
        if(!empty($lastBill)){
            echo " \n\nFound ".$cnt." records. Total records uploaded : ".$cnt;
            echo " \n\nLast bill No : ".$lastBill[0]['billNo'];
        }else{
            echo " \n\nFound ".$cnt." records. Total records uploaded : ".$cnt;
        }
    }

    //Import Nestle Bill Details
    public function uploadBillDetailsData($billFilePath){
        $tempBillNumber="";
        $tempRetailerName="";
        $tempRouteName="";
        $billNumber="";

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        
        $cnt=0;
        for ($row = 2; $row <= $highestRow; ++$row) {
            $cnt++;
            $salesmanCode = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $salesmanName = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
            $routeCode = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
            $routeName = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
            $billNumber = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
            $billDate=$worksheet->getCellByColumnAndRow(6, $row)->getValue();
            $deliveryStatus=$worksheet->getCellByColumnAndRow(7, $row)->getValue();
            $retailerCode=$worksheet->getCellByColumnAndRow(8, $row)->getValue();
            $retailerName=$worksheet->getCellByColumnAndRow(10, $row)->getValue();
            $productCode=$worksheet->getCellByColumnAndRow(19, $row)->getValue();
            $productName=$worksheet->getCellByColumnAndRow(20, $row)->getValue();
            $mrp=$worksheet->getCellByColumnAndRow(21, $row)->getValue();
            $quantity=$worksheet->getCellByColumnAndRow(24, $row)->getValue();
            $freeQuantity=$worksheet->getCellByColumnAndRow(27, $row)->getValue();
            $netAmount=$worksheet->getCellByColumnAndRow(41, $row)->getValue();

            //new added columns
            $itemChannel=$worksheet->getCellByColumnAndRow(11, $row)->getValue();
            $subChannel=$worksheet->getCellByColumnAndRow(12, $row)->getValue();
            $business=$worksheet->getCellByColumnAndRow(14, $row)->getValue();
            $brandName=$worksheet->getCellByColumnAndRow(16, $row)->getValue();
            $motherPackName=$worksheet->getCellByColumnAndRow(18, $row)->getValue();
            $purchaseRateWithoutTax=$worksheet->getCellByColumnAndRow(22, $row)->getValue();
            $purchaseRateWithTax=$worksheet->getCellByColumnAndRow(23, $row)->getValue();
            $freeValue=$worksheet->getCellByColumnAndRow(28, $row)->getValue();
            $grossRate=$worksheet->getCellByColumnAndRow(30, $row)->getValue();
            $schemaDisc=$worksheet->getCellByColumnAndRow(31, $row)->getValue();
            $keyDisc=$worksheet->getCellByColumnAndRow(32, $row)->getValue();
            $rdDisc=$worksheet->getCellByColumnAndRow(33, $row)->getValue();
            $cddbDisc=$worksheet->getCellByColumnAndRow(34, $row)->getValue();
            $splDisc=$worksheet->getCellByColumnAndRow(35, $row)->getValue();
            $taxableValue=$worksheet->getCellByColumnAndRow(36, $row)->getValue();
            $taxPercent=$worksheet->getCellByColumnAndRow(37, $row)->getValue();
            $cessPercent=$worksheet->getCellByColumnAndRow(38, $row)->getValue();
            $taxAmount=$worksheet->getCellByColumnAndRow(39, $row)->getValue();
            $cessAmount=$worksheet->getCellByColumnAndRow(40, $row)->getValue();
            $grossNetVolume=$worksheet->getCellByColumnAndRow(42, $row)->getValue();

            // check bill exist or not
            $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNumber);
            if(!empty($billExist)){
                $billId=$billExist[0]['id'];
                $getBillDetail=$this->ExcelModel->getAllBillDetailsInLastEntries('billsdetails',$billId,$productCode,$productName,$mrp,$quantity,$netAmount);
                $statusForNestleBills=strtolower($deliveryStatus);
                if(empty($getBillDetail)){
                    if($statusForNestleBills==='pending'){
                        if(trim($billNumber) !== trim($tempBillNumber)){
                            $data = array(
                              'salesmanCode'=>$salesmanCode,
                              'salesman'=>$salesmanName,
                              'routeCode'=>$routeCode,
                              'routeName'=>str_replace(",","-",$routeName),
                              'deliveryStatus'=>$statusForNestleBills,
                              'isTempCancelled'=>1,
                              'retailerCode'=>$retailerCode,
                              'retailerName'=>$retailerName
                            );
                            $this->ExcelModel->update('bills',$data,$billId);
                        }
                    }else{
                        //inserting bill details for specific bill
                        if($quantity > 0){
                            $billDetailData=array(
                                'billId'=>$billId,
                                'productCode'=>$productCode,
                                'productName'=>$productName,
                                'mrp'=>$mrp,
                                'qty'=>$quantity,
                                'netAmount'=>$netAmount,
                                'itemChannel'=>$itemChannel,
                                'subChannel'=>$subChannel,
                                'business'=>$business,
                                'brandName'=>$brandName,
                                'motherPackName'=>$motherPackName,
                                'purchaseRateWithoutTax'=>$purchaseRateWithoutTax,
                                'purchaseRateWithTax'=>$purchaseRateWithTax,
                                'freeValue'=>$freeValue,
                                'grossRate'=>$grossRate,
                                'schemaDisc'=>$schemaDisc,
                                'keyDisc'=>$keyDisc,
                                'rdDisc'=>$rdDisc,
                                'cddbDisc'=>$cddbDisc,
                                'splDisc'=>$splDisc,
                                'taxableValue'=>$taxableValue,
                                'taxPercent'=>$taxPercent,
                                'cessPercent'=>$cessPercent,
                                'taxAmount'=>$taxAmount,
                                'cessAmount'=>$cessAmount,
                                'grossNetVolume'=>$grossNetVolume
                            );
                            $this->db->insert('billsdetails', $billDetailData); 
                            // array_push($batchInsert, $billDetailData);
                        }else{
                            $billDetailData=array(
                                'billId'=>$billId,
                                'billNo'=>$billNumber,
                                'productCode'=>$productCode,
                                'productName'=>$productName,
                                'freeQuantity'=>$freeQuantity,
                                'quantity'=>$quantity,
                            );
                            $this->db->insert('billsdetails_freeitems', $billDetailData); 
                        }
                       

                        if(trim($billNumber) !== trim($tempBillNumber)){
                            $data = array(
                              'salesmanCode'=>$salesmanCode,
                              'salesman'=>$salesmanName,
                              'routeCode'=>$routeCode,
                              'routeName'=>str_replace(",","-",$routeName),
                              'deliveryStatus'=>$statusForNestleBills,
                              'isTempCancelled'=>0,
                              'retailerCode'=>$retailerCode,
                              'retailerName'=>$retailerName
                            );
                            $this->ExcelModel->update('bills',$data,$billId);
                        }
                    }
                }
                
                if(trim($retailerName) !== trim($tempRetailerName)){
                    // check retailer exist or not
                    $retailerExist=$this->ExcelModel->getInfoByCode('retailer',$retailerCode);
                    if(empty($retailerExist)){
                        $retailerData=array(
                            'name'=>$retailerName,
                            'code'=>$retailerCode,
                        );
                        $this->ExcelModel->insert('retailer',$retailerData);
                    }else{
                        $retailerData=array(
                            'name'=>$retailerName
                        );
                        $this->ExcelModel->update('retailer',$retailerData,$retailerExist[0]['id']);
                    }
                }
                
                if(trim($routeName) !== trim($tempRouteName)){
                    // check route exist or not
                    $routeExist=$this->ExcelModel->getInfoByCode('route',$routeCode);
                    if(empty($routeExist)){
                        $routeData=array(
                            'name'=>str_replace(",","-",$routeName),
                            'code'=>$routeCode
                        );
                        $this->ExcelModel->insert('route',$routeData);
                    }else{
                        $routeData=array(
                            'name'=>str_replace(",","-",$routeName)
                        );
                        $this->ExcelModel->update('route',$routeData,$routeExist[0]['id']);
                    }
                }
            }
            
            //store data in temp variables to skip duplicate entries
            $tempBillNumber=$billNumber;
            $tempRetailerName=$retailerName;
            $tempRouteName=$routeName;
        }
    }

    //Import Nestle Retailer data
    public function uploadNestleRetailerBillData($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        $cnt=0;
        for ($row = 6; $row <= $highestRow; ++$row) {
            //A row selected
            $cnt++;
            
            $retailerName = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
            $retailerCode = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
            $retailerGstNo = $worksheet->getCellByColumnAndRow(27, $row)->getValue();

            // check retailer exist or not
            $retailerExist=$this->ExcelModel->retailerInfo('retailer',$retailerName,$retailerCode);
            if(!empty($retailerExist)){
                $retailerId=$retailerExist[0]['id'];
                $retailerData=array(
                    'code'=>$retailerCode,
                    'gstIn'=>$retailerGstNo
                );
                $this->ExcelModel->update('retailer',$retailerData,$retailerId);
            }
        }
    }

    //Import ITC Bills
    public function uploadItcBillData($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        $lastBillNumber="";
        $cnt=0;
        for ($row = 1; $row <= $highestRow; ++$row) {

            
            
            //A row selected
            $cnt++;
            if($worksheet->getCellByColumnAndRow(1, $row)->getValue() != "GrandTotal:"){
                $lastBillNumber = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            }
            $billNumber = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $billDate = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
            $salesman = $worksheet->getCellByColumnAndRow(27, $row)->getValue();
            $retailerName = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
            $retailerCode = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
            $netAmount = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
            $route=$worksheet->getCellByColumnAndRow(26, $row)->getValue();
            $retailerGstNo=$worksheet->getCellByColumnAndRow(33, $row)->getValue();

            if($billNumber != "GrandTotal:"){
                $excelDate="";
                if($extension==='csv'){
                    if(!empty($billDate)){
                        $excelDate=date('Y-m-d', strtotime($billDate));
                    }
                }else{
                    if(!empty($billDate)){
                        $date = ($billDate - 25569) * 86400;
                        $excelDate=date('Y-m-d', $date);//convert date from excel data
                    }
                }

                if($dateForUploadBills !==""){
                    if($excelDate > $dateForUploadBills){
                        echo "Please upload bills from date: ".$dateForUploadBills;
                        exit;
                    }
                }
                
                // get 1st day
                $string = $excelDate;
                $timestamp = strtotime($string);
                $day= date("d", $timestamp);

                if(($day != "01" || $day != "1") && $cnt == 1){
                    echo "date not starting from 1st";
                    exit;
                }
                // check bill exist or not
                $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNumber);
                if(empty($billExist)){
                    if((!empty($excelDate)) && ($excelDate!="1970-01-01")){
                        $data = array(
                          'date'=>$excelDate,
                          'billNo'=>$billNumber,
                          'retailerName'=>$retailerName,
                          'retailerCode'=>$retailerCode,
                          'routeName'=>str_replace(",","-",$route),
                          'salesman'=>$salesman,
                          'deliveryStatus'=>'delivered',
                          'billNetAmount'=>$netAmount,
                          'netAmount'=>$netAmount,
                          'pendingAmt'=>$netAmount,
                          'compName'=>'ITC'
                        );
                        $this->ExcelModel->insert('bills',$data);

                        // check retailer exist or not
                        $retailerExist=$this->ExcelModel->retailerInfo('retailer',$retailerName,$retailerCode);
                        if(empty($retailerExist)){
                            $retailerData=array(
                                'name'=>$retailerName,
                                'code'=>$retailerCode,
                                'gstIn'=>$retailerGstNo
                            );
                            $this->ExcelModel->insert('retailer',$retailerData);
                        }

                        // check retailer exist or not
                        $routeExist=$this->ExcelModel->getRouteInfo('route',$route,"");
                        if(empty($routeExist)){
                            $routeData=array(
                                'name'=>$route
                            );
                            $this->ExcelModel->insert('route',$routeData);
                        }
                    }
                }else{
                    $billId=$billExist[0]['id'];
                
                    $billDeliveryStatus=$billExist[0]['deliveryStatus'];
                    $billNetAmount=$billExist[0]['billNetAmount'];
                    $billPendingAmt=$billExist[0]['pendingAmt'];

                    $billSrAmt=$billExist[0]['SRAmt'];
                    $billReceivedAmt=$billExist[0]['receivedAmt'];
                    $billCd=$billExist[0]['cd'];
                    $billDebit=$billExist[0]['debit'];
                    $billOfficeAdjustment=$billExist[0]['officeAdjustmentBillAmount'];
                    $billOtherAdjustment=$billExist[0]['otherAdjustment'];
                    if(($billNetAmount != $netAmount)){
                        $totalRecAmt=$billSrAmt+$billReceivedAmt+$billCd+$billDebit+$billOfficeAdjustment+$billOtherAdjustment;
                        $newPendingAmt=$netAmount-$totalRecAmt;
                    
                        if((!empty($excelDate)) && ($excelDate!="1970-01-01")){
                            $data = array(
                              'date'=>$excelDate,
                              'billNo'=>$billNumber,
                              'retailerName'=>$retailerName,
                              'retailerCode'=>$retailerCode,
                              'routeName'=>str_replace(",","-",$route),
                              'salesman'=>$salesman,
                              'deliveryStatus'=>'delivered',
                              'billNetAmount'=>$netAmount,
                              'netAmount'=>$netAmount,
                              'pendingAmt'=>$newPendingAmt,
                              'compName'=>'ITC'
                            );
                            $this->ExcelModel->update('bills',$data,$billId);

                            // check retailer exist or not
                            $retailerExist=$this->ExcelModel->retailerInfo('retailer',$retailerName,$retailerCode);
                            if(empty($retailerExist)){
                                $retailerData=array(
                                    'name'=>$retailerName,
                                    'code'=>$retailerCode,
                                    'gstIn'=>$retailerGstNo
                                );
                                $this->ExcelModel->insert('retailer',$retailerData);
                            }
                        }
                    }
                }
            }
        }
        echo " \n\nFound ".$cnt." records. Total records uploaded : ".$cnt;
        echo " \n\nLast bill No : ".$lastBillNumber;
    }

     //Import ITC bill details
    public function uploadItcBillDetailData($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        $cnt=0;
        for ($row = 12; $row <= $highestRow; ++$row) {
            //A row selected
            $cnt++;
            
            $billNumber = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $billDate=$worksheet->getCellByColumnAndRow(3, $row)->getValue();

            $productCode=$worksheet->getCellByColumnAndRow(37, $row)->getValue();
            $productName=$worksheet->getCellByColumnAndRow(38, $row)->getValue();
            $mrp=$worksheet->getCellByColumnAndRow(42, $row)->getValue();
            $quantity=$worksheet->getCellByColumnAndRow(40, $row)->getValue();
            $sellingPrice=$worksheet->getCellByColumnAndRow(42, $row)->getValue();
            $netAmount=$worksheet->getCellByColumnAndRow(49, $row)->getValue();
            
            if($productCode != "GrandTotal:"){
                // check bill exist or not
                $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNumber);
                if(!empty($billExist)){
                    $billId=$billExist[0]['id'];
                    $billDetailData=array(
                        'billId'=>$billId,
                        'productCode'=>$productCode,
                        'productName'=>$productName,
                        'mrp'=>$mrp,
                        'qty'=>$quantity,
                        'sellingRate'=>$sellingPrice,
                        'netAmount'=>$netAmount
                    );
                    $this->ExcelModel->insert('billsdetails',$billDetailData);
                }
            }
        }
    }

     //Import parle bills
    public function uploadParleBillData($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7

        $lastBillNumber="";
        $cnt=0;
        for ($row = 10; $row <= $highestRow; ++$row) {
            //A row selected
            $cnt++;

            if($worksheet->getCellByColumnAndRow(1, $row)->getValue() != "Total:"){
                $lastBillNumber = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            }
            $billNumber = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
            $billDate = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
           
            $retailerName = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
            $netAmount = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
            if($billDate != "Total:"){
                $excelDate="";
                if($extension==='csv'){
                    if(!empty($billDate)){
                        $excelDate=date('Y-m-d', strtotime($billDate));
                    }
                }else{
                    if(!empty($billDate)){
                        $date = ($billDate - 25569) * 86400;
                        $excelDate=date('Y-m-d', $date);//convert date from excel data
                    }
                }

                if($dateForUploadBills !==""){
                    if($excelDate > $dateForUploadBills){
                        echo "Please upload bills from date: ".$dateForUploadBills;
                        exit;
                    }
                }

                // get 1st day
                $string = $excelDate;
                $timestamp = strtotime($string);
                $day= date("d", $timestamp);
                if(($day != "01" || $day != "1") && $cnt == 1){
                    echo "date not starting from 1st";
                    exit;
                }

                // check bill exist or not
                $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNumber);
                if(empty($billExist)){
                    if((!empty($excelDate)) && ($excelDate!="1970-01-01")){
                        if($retailerName !="(cancelled)"){
                            $data = array(
                              'date'=>$excelDate,
                              'billNo'=>$billNumber,
                              'retailerName'=>$retailerName,
                              'deliveryStatus'=>'delivered',
                              'billNetAmount'=>$netAmount,
                              'netAmount'=>$netAmount,
                              'pendingAmt'=>$netAmount,
                              'compName'=>'Parle'
                            );
                            $this->ExcelModel->insert('bills',$data);
                        }
                    }
                }else{
                    $billId=$billExist[0]['id'];
                
                    $billDeliveryStatus=$billExist[0]['deliveryStatus'];
                    $billNetAmount=$billExist[0]['billNetAmount'];
                    $billPendingAmt=$billExist[0]['pendingAmt'];

                    $billSrAmt=$billExist[0]['SRAmt'];
                    $billReceivedAmt=$billExist[0]['receivedAmt'];
                    $billCd=$billExist[0]['cd'];
                    $billDebit=$billExist[0]['debit'];
                    $billOfficeAdjustment=$billExist[0]['officeAdjustmentBillAmount'];
                    $billOtherAdjustment=$billExist[0]['otherAdjustment'];
                    if(($billNetAmount != $netAmount)){
                        $totalRecAmt=$billSrAmt+$billReceivedAmt+$billCd+$billDebit+$billOfficeAdjustment+$billOtherAdjustment;
                        $newPendingAmt=$netAmount-$totalRecAmt;

                        if((!empty($excelDate)) && ($excelDate!="1970-01-01")){
                            if($retailerName !="(cancelled)"){
                                $data = array(
                                  'date'=>$excelDate,
                                  'billNo'=>$billNumber,
                                  'retailerName'=>$retailerName,
                                  'deliveryStatus'=>'delivered',
                                  'billNetAmount'=>$netAmount,
                                  'netAmount'=>$netAmount,
                                  'pendingAmt'=>$newPendingAmt,
                                  'compName'=>'Parle'
                                );
                                $this->ExcelModel->update('bills',$data,$billId);
                            }
                        }
                    }
                }
            }
        }
        echo " \n\nFound ".$cnt." records. Total records uploaded : ".$cnt;
        echo " \n\nLast bill No : ".$lastBillNumber;
    }

    //Import parle bill details 
    public function uploadParleBillDetailData($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        $cnt=0;
        $sesBillNumber="";
        for ($row = 9; $row <= $highestRow; ++$row) {
            //A row selected
            $cnt++;
            
            $billNumber = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
            $billDate=$worksheet->getCellByColumnAndRow(1, $row)->getValue();

            $productName=$worksheet->getCellByColumnAndRow(2, $row)->getValue();
            $mrp=$worksheet->getCellByColumnAndRow(10, $row)->getValue();
            $quantity=$worksheet->getCellByColumnAndRow(8, $row)->getValue();
            $sellingPrice=$worksheet->getCellByColumnAndRow(10, $row)->getValue();
            $netAmount=$worksheet->getCellByColumnAndRow(11, $row)->getValue();
            
            if($productName != "Grand Total"){
                $billId="";
                if($billNumber !="" && $billDate !=""){
                    $sesBillNumber=$billNumber;
                }

                if($sesBillNumber != ""){
                    // check bill exist or not
                    $billExist=$this->ExcelModel->getBillByLastRecords('bills',$sesBillNumber);
                    if(!empty($billExist)){
                        if($billNumber=="" && $billDate==""){
                            $billId=$billExist[0]['id'];
                            $billDetailData=array(
                                'billId'=>$billId,
                                'productName'=>$productName,
                                'mrp'=>$mrp,
                                'qty'=>$quantity,
                                'sellingRate'=>$sellingPrice,
                                'netAmount'=>$netAmount
                            );
                            $this->ExcelModel->insert('billsdetails',$billDetailData);
                        }
                    }
                }
            }
        }
    }

    //check date for nestle bills data uploading
    public function nestleExcelUploading($fileName,$fileType,$fileTempName,$dateForUploadBills){
        $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if(isset($fileName) && in_array($fileType, $file_mimes)) {
            $arr_file = explode('.', $fileName); //get file
            $extension = end($arr_file); //get file extension
            // select spreadsheet reader depends on file extension
            if('csv' == $extension) {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            } else if ('xlsx'){
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }

            $reader->setReadDataOnly(true);
            $objPHPExcel = $reader->load($fileTempName);//Get filename
            
            $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
            $highestRow = $worksheet->getHighestRow(); // e.g. 12
            $highestColumn = $worksheet->getHighestColumn(); // e.g M'

            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
            $cnt=0;
            $billNumberHeader="";
            $billDateHeader="";
            $retailerNameHeader = "";
            $retailerCodeHeader = "";
            $billNetAmountHeader = "";
            $netAmountHeader = "";
            $creditAdjustmentHeader="";
            $billNumber="";
            for ($row = 1; $row <= $highestRow; ++$row) {
                $cnt++;
                for($i=1;$i<=$highestColumnIndex;$i++){
                    if($row==1){
                        if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill Number"){
                            $billNumberHeader= $i;
                        }

                        if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill Date"){
                            $billDateHeader= $i;
                        }

                        if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Retailer Code"){
                            $retailerCodeHeader= $i;
                        }

                        if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Customer Name"){
                            $retailerNameHeader= $i;
                        }

                        if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Net Amount"){
                            $billNetAmountHeader= $i;
                        }

                        if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill receivable amount"){
                            $netAmountHeader= $i;
                        }

                        if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Credit Adjustment"){
                            $creditAdjustmentHeader= $i;
                        }
                    }
                }

                if(($row==1) && (empty($billNumberHeader) || empty($billDateHeader) || empty($retailerCodeHeader) || empty($retailerNameHeader) || empty($billNetAmountHeader) || empty($netAmountHeader) || empty($creditAdjustmentHeader))){
                    echo "Please select correct files for uploading";
                    exit;
                }               
                
                $billNumber = $worksheet->getCellByColumnAndRow($billNumberHeader, $row)->getValue();
                $billDate = $worksheet->getCellByColumnAndRow($billDateHeader, $row)->getValue();
                $retailerName = $worksheet->getCellByColumnAndRow($retailerNameHeader, $row)->getValue();
                $retailerCode = $worksheet->getCellByColumnAndRow($retailerCodeHeader, $row)->getValue();
                $amount = $worksheet->getCellByColumnAndRow($billNetAmountHeader, $row)->getValue();
                $netAmount = $worksheet->getCellByColumnAndRow($netAmountHeader, $row)->getValue();
                $creditAdjustment=$worksheet->getCellByColumnAndRow($creditAdjustmentHeader, $row)->getValue();

                $excelDate="";
                if($extension==='csv'){
                    if(!empty($billDate)){
                        $excelDate=date('Y-m-d', strtotime($billDate));
                    }
                }else{
                    if(!empty($billDate) && ($billDate !=='Bill Date')){
                        $billDate =str_replace("/","-",$billDate);
                        $excelDate=date('Y-m-d', strtotime($billDate));
                    }
                }
                if($dateForUploadBills !==""){
                    if($billDate !=="Bill Date"){
                        $excelDate = date("Y-m-d", strtotime('-15 days', strtotime($excelDate)));
                        if(($excelDate > $dateForUploadBills) && ($billDate !=='Bill Date')){
                            return "Please uploads bills from date: ".$dateForUploadBills;
                        }else{
                            return "";
                        }
                    }
                }
            }
        }
    }

    //Import ITC Bills
    public function itcExcelUploading($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7

        $cnt=0;
        $total="";

        $billDateHeader="";
        $billNumberHeader="";
        $retailerCodeHeader="";
        $voucherTypeHeader="";
        $getPercentHeader="";
        $gstNumberHeader="";
        $panNumberHeader="";
        $quantityHeader="";
        $valueHeader="";
        $grossTotalHeader="";
        $cgstHeader="";
        $sgstHeader="";

        for ($row = 1; $row <= $highestRow; ++$row) {
            $cnt++;
            for($i=1;$i<=$highestColumnIndex;$i++){
                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Date"){
                    $billDateHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Particulars"){
                    $retailerCodeHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Voucher Type"){
                    $voucherTypeHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Voucher No."){
                    $billNumberHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="GST %"){
                    $getPercentHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="GSTIN/UIN"){
                    $gstNumberHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="PAN No."){
                    $panNumberHeader= $i;
                }
                

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Quantity"){
                    $quantityHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Value"){
                    $valueHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Gross Total"){
                    $grossTotalHeader= $i;
                    $total=$cnt;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="CGST"){
                    $cgstHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="SGST"){
                    $sgstHeader= $i;
                }
            }
        }
        if((empty($billDateHeader) || empty($cgstHeader) || empty($sgstHeader) || empty($billNumberHeader) || empty($retailerCodeHeader) || empty($voucherTypeHeader) || empty($getPercentHeader) || empty($gstNumberHeader) || empty($panNumberHeader) || empty($quantityHeader) || empty($valueHeader) || empty($grossTotalHeader))){
            echo "Source file not in correct order. Please select correct files for uploading.";
            exit;
        }

        for ($row = ($total+1); $row <= $highestRow; ++$row) {
            $billDate = trim($worksheet->getCellByColumnAndRow($billDateHeader, $row)->getValue());
            $retailerName = trim($worksheet->getCellByColumnAndRow($retailerCodeHeader, $row)->getValue());
            $voucherType = trim($worksheet->getCellByColumnAndRow($voucherTypeHeader, $row)->getValue());
            $billNo = trim($worksheet->getCellByColumnAndRow($billNumberHeader, $row)->getValue());
            $gstPercent = trim($worksheet->getCellByColumnAndRow($getPercentHeader, $row)->getValue());
            $gstNo = trim($worksheet->getCellByColumnAndRow($gstNumberHeader, $row)->getValue());
            $panNo = trim($worksheet->getCellByColumnAndRow($panNumberHeader, $row)->getValue());
            $quantity = trim($worksheet->getCellByColumnAndRow($quantityHeader, $row)->getValue());
            $amoutWithoutTax = trim($worksheet->getCellByColumnAndRow($valueHeader, $row)->getValue());
            $netAmount = trim($worksheet->getCellByColumnAndRow($grossTotalHeader, $row)->getValue());

            $cgst = trim($worksheet->getCellByColumnAndRow($cgstHeader, $row)->getValue());
            $sgst = trim($worksheet->getCellByColumnAndRow($sgstHeader, $row)->getValue());
                
            $excelDate="";
            if(($billDate !=="") && ($retailerName !== "(cancelled)")){
                if(!empty($billDate) && $billDate !=='Bill Date'){
                    $billDate =str_replace("/","-",$billDate);
                    $date = ($billDate - 25569) * 86400;
                    $excelDate=date('Y-m-d', $date);//convert date from excel data

                    $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNo);

                    // $pan="";
                    if($panNo==""){
                        $retailerCount=$this->ExcelModel->getdata('retailer');

                        $retailerDataExist=$this->ExcelModel->getDataByName('retailer',$retailerName);
                        if(empty($retailerDataExist)){
                            $panNo="RETNO1000".count($retailerCount);
                        }else{
                            $panNo=$retailerDataExist[0]['code'];
                        }
                        
                    }

                    if(empty($billExist)){
                        $arrayRes=array(
                            'date'=>$excelDate,
                            'billNo'=>$billNo,
                            'deliveryStatus'=>'delivered',
                            'retailerCode'=>$panNo,
                            'retailerName'=>$retailerName,
                            'grossAmount'=>$netAmount,
                            'taxAmount'=>($cgst+$sgst),
                            'billNetAmount'=>round($netAmount),
                            'netAmount'=>round($netAmount),
                            'pendingAmt'=>round($netAmount),
                            'compName'=>'ITC',
                            'invoiceType'=>$voucherType,
                            'routeCode'=>'NOROUTE',
                            'routeName'=>'NO ROUTE'
                        );
                        $this->ExcelModel->insert('bills',$arrayRes);
                    }else{
                        $billId=$billExist[0]['id'];
                    
                        $billDeliveryStatus=$billExist[0]['deliveryStatus'];
                        $billNetAmount=$billExist[0]['billNetAmount'];
                        $billPendingAmt=$billExist[0]['pendingAmt'];

                        $billSrAmt=$billExist[0]['SRAmt'];
                        $billReceivedAmt=$billExist[0]['receivedAmt'];
                        $billCd=$billExist[0]['cd'];
                        $billDebit=$billExist[0]['debit'];
                        $billOfficeAdjustment=$billExist[0]['officeAdjustmentBillAmount'];
                        $billOtherAdjustment=$billExist[0]['otherAdjustment'];
                        //  
                        if(($billNetAmount != $billPendingAmt)){
                            $totalRecAmt=$billSrAmt+$billReceivedAmt+$billCd+$billDebit+$billOfficeAdjustment+$billOtherAdjustment;
                            $newPendingAmt=$netAmount-$totalRecAmt;
                            if((!empty($excelDate)) && ($excelDate != "1970-01-01")){
                                $data = array(
                                    'date'=>$excelDate,
                                    'billNo'=>$billNo,
                                    'retailerCode'=>$panNo,
                                    'retailerName'=>$retailerName,
                                    'grossAmount'=>$grossAmount,
                                    'taxAmount'=>($cgst+$sgst),
                                    'billNetAmount'=>round($netAmount),
                                    'netAmount'=>round($netAmount),
                                    'pendingAmt'=>round($newPendingAmt),
                                    'compName'=>'ITC',
                                    'invoiceType'=>$voucherType,
                                    'routeCode'=>'NOROUTE',
                                    'routeName'=>'NO ROUTE'
                                );
                                $this->ExcelModel->update('bills',$data,$billId);
                            }
                        }
                    }

                    $retailerDetails=$this->ExcelModel->retailerInfo('retailer',$retailerName,$panNo);
                    if(empty($retailerDetails)){
                        $retailerData=array(
                            'code'=>$panNo,
                            'name'=>$retailerName,
                            'gstIn'=>$gstNo
                        );
                        $this->ExcelModel->insert('retailer',$retailerData);
                    }

                    $routeDetails=$this->ExcelModel->routeInfo('route','NO ROUTE','NOROUTE');
                    if(empty($routeDetails)){
                        $routeData=array(
                            'code'=>'NOROUTE',
                            'name'=>'NO ROUTE'
                        );
                        $this->ExcelModel->insert('route',$routeData);
                    }
                }
            }else if(($billDate !=="") && ($retailerName == "(cancelled)")){
                $arrayRes=array(
                    'date'=>$excelDate,
                    'billNo'=>$billNo,
                    'deliveryStatus'=>'cancelled',
                    'compName'=>'ITC',
                    'invoiceType'=>$voucherType
                );
                $this->ExcelModel->insert('bills',$arrayRes);
            }
        }

        $billId=0;
        //upload bill details value
        for ($row = ($total+1); $row <= $highestRow; ++$row) {
            $billDate = $worksheet->getCellByColumnAndRow($billDateHeader, $row)->getValue();
            $billNo = $worksheet->getCellByColumnAndRow($billNumberHeader, $row)->getValue();
            $excelDate="";

            if($billDate !==""){
                $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNo);
                // print_r($billExist);exit;
                if(!empty($billExist)){
                    $billId=$billExist[0]['id'];
                }
            }

            if($billDate ==""){
                $itemName = trim($worksheet->getCellByColumnAndRow($retailerCodeHeader, $row)->getValue());
                $gstPercent = trim($worksheet->getCellByColumnAndRow($getPercentHeader, $row)->getValue());
                $quantity = trim($worksheet->getCellByColumnAndRow($quantityHeader, $row)->getValue());
                $amoutWithoutTax = trim($worksheet->getCellByColumnAndRow($valueHeader, $row)->getValue());
                if($itemName != "Grand Total"){
                    $checkItemDetails=$this->ExcelModel->checkBillDetailsData('billsdetails',$billId,$itemName,$gstPercent,$quantity);
                    if(empty($checkItemDetails)){
                        if($gstPercent==""){
                            $rateWithGst=(($amoutWithoutTax/100)*0);
                            $productArray=array(
                                'billId'=>$billId,
                                'productName'=>$itemName,
                                'gstPercent'=>$gstPercent,
                                'qty'=>$quantity,
                                'netAmount'=>($amoutWithoutTax+$rateWithGst)
                            );
                            $this->ExcelModel->insert('billsdetails',$productArray);
                        }else{
                            $rateWithGst=(($amoutWithoutTax/100)*$gstPercent);
                            $productArray=array(
                                'billId'=>$billId,
                                'productName'=>$itemName,
                                'gstPercent'=>$gstPercent,
                                'qty'=>$quantity,
                                'netAmount'=>($amoutWithoutTax+$rateWithGst)
                            );
                            $this->ExcelModel->insert('billsdetails',$productArray);
                        }
                    }
                }
            }
            
        }
    }

    //check date for ITC bills data uploading
    public function checkItcExcelUploading($fileName,$fileType,$fileTempName,$dateForUploadBills){
        $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if(isset($fileName) && in_array($fileType, $file_mimes)) {
            $arr_file = explode('.', $fileName); //get file
            $extension = end($arr_file); //get file extension
            // select spreadsheet reader depends on file extension
            if('csv' == $extension) {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            } else if ('xlsx'){
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }

            $reader->setReadDataOnly(true);
            $objPHPExcel = $reader->load($fileTempName);//Get filename
            
            $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
            $highestRow = $worksheet->getHighestRow(); // e.g. 12
            $highestColumn = $worksheet->getHighestColumn(); // e.g M'

            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
            
            $cnt=0;
            $total="";

            $billDateHeader="";
            $billNumberHeader="";
            $retailerCodeHeader="";
            $voucherTypeHeader="";
            $getPercentHeader="";
            $gstNumberHeader="";
            $quantityHeader="";
            $valueHeader="";
            $grossTotalHeader="";
            $cgstHeader="";
            $sgstHeader="";
            
            for ($row = 1; $row <= $highestRow; ++$row) {
                $cnt++;
                for($i=1;$i<=$highestColumnIndex;$i++){
                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Date"){
                        $billDateHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Particulars"){
                        $retailerCodeHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Voucher Type"){
                        $voucherTypeHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Voucher No."){
                        $billNumberHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="GST %"){
                        $getPercentHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="GSTIN/UIN"){
                        $gstNumberHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Quantity"){
                        $quantityHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Value"){
                        $valueHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Gross Total"){
                        $grossTotalHeader= $i;
                        $total=$cnt;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="CGST"){
                        $cgstHeader= $i;
                    }
    
                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="SGST"){
                        $sgstHeader= $i;
                    }
                }
            }
            if((empty($billDateHeader) || empty($cgstHeader) || empty($sgstHeader) || empty($billNumberHeader) || empty($retailerCodeHeader) || empty($voucherTypeHeader) || empty($getPercentHeader) || empty($gstNumberHeader) || empty($quantityHeader) || empty($valueHeader) || empty($grossTotalHeader))){
                    echo "Source file not in correct order. Please select correct files for uploading.";
                    exit;
            }

            for ($row = ($total+1); $row <= $highestRow; ++$row) {

                $billDate = $worksheet->getCellByColumnAndRow($billDateHeader, $row)->getValue();
                $retailerName = $worksheet->getCellByColumnAndRow($retailerCodeHeader, $row)->getValue();
                $voucherType = $worksheet->getCellByColumnAndRow($voucherTypeHeader, $row)->getValue();
                $billNo = $worksheet->getCellByColumnAndRow($billNumberHeader, $row)->getValue();
                $gstPercent = $worksheet->getCellByColumnAndRow($getPercentHeader, $row)->getValue();
                $gstNo = $worksheet->getCellByColumnAndRow($gstNumberHeader, $row)->getValue();
                $quantity = $worksheet->getCellByColumnAndRow($quantityHeader, $row)->getValue();
                $amoutWithoutTax = $worksheet->getCellByColumnAndRow($valueHeader, $row)->getValue();
                $netAmount = $worksheet->getCellByColumnAndRow($grossTotalHeader, $row)->getValue();
                $excelDate="";
                if($billDate !==""){
                    if(!empty($billDate) && $billDate !=='Bill Date'){
                        $billDate =str_replace("/","-",$billDate);
                        $date = ($billDate - 25569) * 86400;
                        $excelDate=date('Y-m-d', $date);//convert date from excel data
                    }
                }

                // $excelDate="";
                if($extension==='csv'){
                    if(!empty($billDate)){
                        $excelDate=date('Y-m-d', strtotime($billDate));
                    }
                }else{
                    if(!empty($billDate) && ($billDate !=='Bill Date')){
                        $billDate =str_replace("/","-",$billDate);
                        $excelDate=date('Y-m-d', strtotime($billDate));
                    }
                }
                if($dateForUploadBills !==""){
                    if($billDate !=="Bill Date"){
                        $excelDate = date("Y-m-d", strtotime('-15 days', strtotime($excelDate)));
                        if(($excelDate > $dateForUploadBills) && ($billDate !=='Bill Date')){
                            return "Please uploads bills from date: ".$dateForUploadBills;
                        }else{
                            return "";
                        }
                    }
                }

            }
        }
    }


    //check date for Parle bills data uploading
    public function checkParleExcelUploading($fileName,$fileType,$fileTempName,$dateForUploadBills){
        $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if(isset($fileName) && in_array($fileType, $file_mimes)) {
            $arr_file = explode('.', $fileName); //get file
            $extension = end($arr_file); //get file extension
            // select spreadsheet reader depends on file extension
            if($extension == 'csv') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            } else if ($extension =='xlsx'){
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }

            $reader->setReadDataOnly(true);
            $objPHPExcel = $reader->load($fileTempName);//Get filename
            
            $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
            $highestRow = $worksheet->getHighestRow(); // e.g. 12
            $highestColumn = $worksheet->getHighestColumn(); // e.g M'

            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
            
            $cnt=0;
            $total="";

            $billNumberHeader="";
            $billDateHeader="";
            $deliveryStatusHeader="";
            $salesmanHeader="";
            $retailerCodeHeader="";
            $retailerNameHeader="";
            $cashDiscountHeader="";
            $creditAdjustmentHeader="";
            $netAmountHeader="";
            $grossAmountHeader="";
            $taxAmountHeader="";
            
            for ($row = 1; $row <= $highestRow; ++$row) {
                $cnt++;
                for($i=1;$i<=$highestColumnIndex;$i++){
                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill Number"){
                        $billNumberHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill Date"){
                        $billDateHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Delivery Status"){
                        $deliveryStatusHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Salesman"){
                        $salesmanHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Retailer Code"){
                        $retailerCodeHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Retailer Name"){
                        $retailerNameHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Cash Discount"){
                        $cashDiscountHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Credit Adjustment"){
                        $creditAdjustmentHeader= $i;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Net Amount"){
                        $netAmountHeader= $i;
                        $total=$cnt;
                    }

                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Gross Amount"){
                        $grossAmountHeader= $i;
                    }
    
                    if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Tax Amount"){
                        $taxAmountHeader= $i;
                    }
                }
            }
            if((empty($billNumberHeader) || empty($taxAmountHeader) || empty($grossAmountHeader) || empty($billDateHeader) || empty($deliveryStatusHeader) || empty($salesmanHeader) || empty($retailerCodeHeader) || empty($retailerNameHeader) || empty($cashDiscountHeader) || empty($creditAdjustmentHeader) || empty($netAmountHeader))){
                    echo "Source file not in correct order. Please select correct files for uploading.";
                    exit;
            }

            for ($row = ($total+2); $row <= $highestRow; ++$row) {

                $billNumber = $worksheet->getCellByColumnAndRow($billNumberHeader, $row)->getValue();
                $billDate = $worksheet->getCellByColumnAndRow($billDateHeader, $row)->getValue();
                $deliveryStatus = $worksheet->getCellByColumnAndRow($deliveryStatusHeader, $row)->getValue();
                $salesman = $worksheet->getCellByColumnAndRow($salesmanHeader, $row)->getValue();
                $retailerCode = $worksheet->getCellByColumnAndRow($retailerCodeHeader, $row)->getValue();
                $retailerName = $worksheet->getCellByColumnAndRow($retailerNameHeader, $row)->getValue();
                $cashDiscount = $worksheet->getCellByColumnAndRow($cashDiscountHeader, $row)->getValue();
                $creditAdjustment = $worksheet->getCellByColumnAndRow($creditAdjustmentHeader, $row)->getValue();
                $netAmount = $worksheet->getCellByColumnAndRow($netAmountHeader, $row)->getValue();
               
                if($dateForUploadBills !==""){
                    if($billDate !=="Bill Date"){
                        $excelDate = date("Y-m-d", strtotime('-15 days', strtotime($billDate)));
                        if(($excelDate > $dateForUploadBills) && ($billDate !=='Bill Date')){
                            return "Please uploads bills from date: ".$dateForUploadBills;
                        }else{
                            return "";
                        }
                    }
                }

            }
        }
    }

    //Import Parle Bills
    public function uploadParleExcelUploading($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        
        $cnt=0;
        $total="";

        $billNumberHeader="";
        $billDateHeader="";
        $deliveryStatusHeader="";
        $salesmanHeader="";
        $retailerCodeHeader="";
        $retailerNameHeader="";
        $cashDiscountHeader="";
        $creditAdjustmentHeader="";
        $netAmountHeader="";
        $grossAmountHeader="";
        $taxAmountHeader="";
        
        for ($row = 1; $row <= $highestRow; ++$row) {
            $cnt++;
            for($i=1;$i<=$highestColumnIndex;$i++){
                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill Number"){
                    $billNumberHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Bill Date"){
                    $billDateHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Delivery Status"){
                    $deliveryStatusHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Salesman"){
                    $salesmanHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Retailer Code"){
                    $retailerCodeHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Retailer Name"){
                    $retailerNameHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Cash Discount"){
                    $cashDiscountHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Credit Adjustment"){
                    $creditAdjustmentHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Net Amount"){
                    $netAmountHeader= $i;
                    $total=$cnt;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Gross Amount"){
                    $grossAmountHeader= $i;
                }

                if($worksheet->getCellByColumnAndRow($i, $row)->getValue()==="Tax Amount"){
                    $taxAmountHeader= $i;
                }
            }
        }
        if((empty($billNumberHeader) || empty($taxAmountHeader) || empty($grossAmountHeader) || empty($billDateHeader) || empty($deliveryStatusHeader) || empty($salesmanHeader) || empty($retailerCodeHeader) || empty($retailerNameHeader) || empty($cashDiscountHeader) || empty($creditAdjustmentHeader) || empty($netAmountHeader))){
                echo "Source file not in correct order. Please select correct files for uploading.";
                exit;
        }

        for ($row = ($total+2); $row <= $highestRow; ++$row) {

            $billNumber = trim($worksheet->getCellByColumnAndRow($billNumberHeader, $row)->getValue());
            $billDate = trim($worksheet->getCellByColumnAndRow($billDateHeader, $row)->getValue());
            $deliveryStatus = trim($worksheet->getCellByColumnAndRow($deliveryStatusHeader, $row)->getValue());
            $salesman = trim($worksheet->getCellByColumnAndRow($salesmanHeader, $row)->getValue());
            $retailerCode = trim($worksheet->getCellByColumnAndRow($retailerCodeHeader, $row)->getValue());
            $retailerName = trim($worksheet->getCellByColumnAndRow($retailerNameHeader, $row)->getValue());
            $cashDiscount = trim($worksheet->getCellByColumnAndRow($cashDiscountHeader, $row)->getValue());
            $creditAdjustment = trim($worksheet->getCellByColumnAndRow($creditAdjustmentHeader, $row)->getValue());
            $netAmount = trim($worksheet->getCellByColumnAndRow($netAmountHeader, $row)->getValue());
            $grossAmount = trim($worksheet->getCellByColumnAndRow($grossAmountHeader, $row)->getValue());
            $taxAmount = trim($worksheet->getCellByColumnAndRow($taxAmountHeader, $row)->getValue());
            

            if(trim($deliveryStatus)=="Delivered"){
                $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNumber);
                if(empty($billExist)){
                    $readArray=array(
                        'billNo'=>$billNumber,
                        'date'=>$billDate,
                        'deliveryStatus'=>$deliveryStatus,
                        'retailerCode'=>$retailerCode,
                        'retailerName'=>$retailerName,
                        'salesman'=>$salesman,
                        'grossAmount'=>$grossAmount,
                        'taxAmount'=>$taxAmount,
                        'billNetAmount'=>round($netAmount),
                        'cashDiscount'=>$cashDiscount,
                        'creditAdjustment'=>round($creditAdjustment),
                        'netAmount'=>round($netAmount),
                        'pendingAmt'=>round($netAmount),
                        'compName'=>'Parle'
                    );
                    $this->ExcelModel->insert('bills',$readArray);
                }
            }

        }
    }

    //Import Parle Bills details
    public function uploadParleBillDetailsExcelUploading($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(0);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        
       
        for ($row = 3; $row <= $highestRow; ++$row) {
            $billNo = trim($worksheet->getCellByColumnAndRow(10, $row)->getValue());
            $productCode = trim($worksheet->getCellByColumnAndRow(16, $row)->getValue());
            $productName = trim($worksheet->getCellByColumnAndRow(17, $row)->getValue());
            $mrp = trim($worksheet->getCellByColumnAndRow(19, $row)->getValue());
            $sellingRate = trim($worksheet->getCellByColumnAndRow(20, $row)->getValue());
            $qty = trim($worksheet->getCellByColumnAndRow(21, $row)->getValue());
            $grossAmount = trim($worksheet->getCellByColumnAndRow(22, $row)->getValue());
            $schemeDiscount = trim($worksheet->getCellByColumnAndRow(25, $row)->getValue());
            $distributorDiscount = trim($worksheet->getCellByColumnAndRow(26, $row)->getValue());
            $cashDiscount = trim($worksheet->getCellByColumnAndRow(27, $row)->getValue());
            $taxAmount = trim($worksheet->getCellByColumnAndRow(28, $row)->getValue());
            $netAmount = trim($worksheet->getCellByColumnAndRow(29, $row)->getValue());
            
            $billExist=$this->ExcelModel->getBillByLastRecords('bills',$billNo);
            if(!empty($billExist)){
                $readArray=array(
                    'billId'=>$billExist[0]['id'],
                    'productCode'=>$productCode,
                    'motherPackName'=>$productName,
                    'productName'=>$productName,
                    'mrp'=>$mrp,
                    'sellingRate'=>$sellingRate,
                    'qty'=>$qty,
                    'netAmount'=>$netAmount,
                    'purchaseRateWithoutTax'=>$grossAmount,
                    'purchaseRateWithTax'=>$netAmount,
                    'grossRate'=>$grossAmount,
                    'schemaDisc'=>$schemeDiscount,
                    'cddbDisc'=>($cashDiscount+$distributorDiscount),
                    'taxableValue'=>$taxAmount
                );
                $this->ExcelModel->insert('billsdetails',$readArray);
            }
        }
    }

    //Import Parle Bills details
    public function uploadParleRetailerExcelUploading($billFilePath){
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($billFilePath);
        $reader->setReadDataOnly(TRUE);
        $objPHPExcel = $reader->load($billFilePath);

        $worksheet = $objPHPExcel->getSheet(3);//Get sheet 
        $highestRow = $worksheet->getHighestRow(); // e.g. 12
        $highestColumn = $worksheet->getHighestColumn(); // e.g M'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 7
        for ($row = 2; $row <= $highestRow; ++$row) {
            $salesmanCode = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
            $salesmanName = trim($worksheet->getCellByColumnAndRow(3, $row)->getValue());
            $routeCode = trim($worksheet->getCellByColumnAndRow(4, $row)->getValue());
            $routeName = trim($worksheet->getCellByColumnAndRow(5, $row)->getValue());
            $retailerCode = trim($worksheet->getCellByColumnAndRow(10, $row)->getValue());
            $retailerName = trim($worksheet->getCellByColumnAndRow(11, $row)->getValue());

            // echo "hy ".$salesmanCode.' '.$salesmanName.' '.$routeCode.' '.$routeName.' '.$retailerCode.' '.$retailerName;
            // exit;
            if(trim($retailerName)){
                // check retailer exist or not
                $retailerExist=$this->ExcelModel->getInfoByCode('retailer',$retailerCode);
                if(empty($retailerExist)){
                    $retailerData=array(
                        'name'=>$retailerName,
                        'code'=>$retailerCode,
                    );
                    $this->ExcelModel->insert('retailer',$retailerData);
                }else{
                    $retailerData=array(
                        'name'=>$retailerName
                    );
                    $this->ExcelModel->update('retailer',$retailerData,$retailerExist[0]['id']);
                }
            }
            
            if(trim($routeName)){
                // check route exist or not
                $routeExist=$this->ExcelModel->getInfoByCode('route',$routeCode);
                if(empty($routeExist)){
                    $routeData=array(
                        'name'=>str_replace(",","-",$routeName),
                        'code'=>$routeCode
                    );
                    $this->ExcelModel->insert('route',$routeData);
                }else{
                    $routeData=array(
                        'name'=>str_replace(",","-",$routeName)
                    );
                    $this->ExcelModel->update('route',$routeData,$routeExist[0]['id']);
                }
            }
        }
    }

}