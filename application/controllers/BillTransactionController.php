<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BillTransactionController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('AllocationByManagerModel');
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
    }

    public function loadRetailerBills($name){
        $name=urldecode($name);
        $data['bills']=$this->AllocationByManagerModel->retailerBillsByCode('bills',$name);
        echo json_encode( $data['bills']);
    }

    public function retailerwiseDetails()
    {
         $data['bills']=$this->AllocationByManagerModel->retailerOutstandingBills('bills');
         // print_r($data['bills']);
         $this->load->view('allRetailerwiseOutstandingView',$data);
    }

    public function cancelledBills(){
        $data['bills']=$this->AllocationByManagerModel->getCancelledBills('bills');
        $this->load->view('cancelledBillsView',$data);
    }

    public function tempCancelledBills(){
        $data['bills']=$this->AllocationByManagerModel->getTempCancelledBills('bills');
        $this->load->view('temporaryCancelcancelledBillsView',$data);
    }

    public function updatedTempCancelBillStatus(){
        $billId=$this->input->post('id');
        $updateData=array('isTempCancelled'=>0,'deliveryStatus'=>'cancelled');
        $this->AllocationByManagerModel->update('bills',$updateData,$billId);
    }
    
    public function addBillToAllocation(){
        $currentBillId=trim($this->input->post('currentBillId'));
        $allocationId=trim($this->input->post('allocationId'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));

        $allocationTotalAmount=0;
        $allocationBillCount=0;
        $allocationSalesman="";

        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);
        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] <= 0){
                echo "Pending amount is 0.";
            }else{
                // $allocationDetail=$this->AllocationByManagerModel->load('allocations',$allocationId);
                if($billInfo[0]['netAmount']==$currentPendingAmt){
                    if(!empty($allocationId)){
                        $allocationDetails=$this->AllocationByManagerModel->load('allocations',$allocationId);
                        if(!empty($allocationDetails)){
                            if(!empty($currentBillId)){
                                $billInformation=$this->AllocationByManagerModel->getUnallocatedBillById('bills',$currentBillId);
                                if(!empty($billInformation)){
                                    $allocationSalesman=$allocationDetails[0]['allocationSalesman'].','.$billInformation[0]['salesman'];
                                    $allocationTotalAmount=$allocationDetails[0]['allocationTotalAmount']+$billInformation[0]['pendingAmt'];
                                    $allocationBillCount=1+$allocationDetails[0]['allocationBillCount'];

                                    $updateAllocationDetails=array(
                                        "allocationTotalAmount"=>$allocationTotalAmount,
                                        "allocationBillCount"=>$allocationBillCount,
                                        "allocationSalesman"=>$allocationSalesman
                                    );
                                    $this->AllocationByManagerModel->update('allocations',$updateAllocationDetails,$allocationId);

                                }       
                            }
                
                            $insAllocationDetail=array('billId'=>$currentBillId,'allocationId'=>$allocationId,'billStatus'=>1);
                            $this->AllocationByManagerModel->insert('allocationsbills',$insAllocationDetail);
                            if($this->db->affected_rows()>0){
                                
                                $updBills=array('billCurrentStatus'=>'Allocated','billType'=>'allocatedbillCurrent','isAllocated'=>1,'deliveryslipOwnerApproval'=>'1','isLostBill'=>'0');
                                $this->AllocationByManagerModel->update('bills',$updBills,$currentBillId);
                                if($this->db->affected_rows()>0){
                                    $history=array(
                                        'billId'=>$currentBillId,
                                        'allocationId'=>$allocationId,
                                        'transactionStatus' =>'Added to Allocation',
                                        'transactionDate'=>date('Y-m-d H:i:sa'),
                                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                                    );
                                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                                    echo "Record inserted";
                                }else{
                                    echo "Record not inserted";
                                }
                            }
                        }
                    }
                }else{

                    if(!empty($allocationId)){
                        $allocationDetails=$this->AllocationByManagerModel->load('allocations',$allocationId);
                        if(!empty($allocationDetails)){

                            if(!empty($currentBillId)){
                                $billInformation=$this->AllocationByManagerModel->getUnallocatedBillById('bills',$currentBillId);
                                if(!empty($billInformation)){
                                    $allocationSalesman=$allocationDetails[0]['allocationSalesman'].','.$billInformation[0]['salesman'];
                                    $allocationTotalAmount=$allocationDetails[0]['allocationTotalAmount']+$billInformation[0]['pendingAmt'];
                                    $allocationBillCount=1+$allocationDetails[0]['allocationBillCount'];

                                    $updateAllocationDetails=array(
                                        "allocationTotalAmount"=>$allocationTotalAmount,
                                        "allocationBillCount"=>$allocationBillCount,
                                        "allocationSalesman"=>$allocationSalesman
                                    );
                                    $this->AllocationByManagerModel->update('allocations',$updateAllocationDetails,$allocationId);
 
                                }       
                            }

                            $insAllocationDetail=array('billId'=>$currentBillId,'allocationId'=>$allocationId,'billStatus'=>2);
                            $this->AllocationByManagerModel->insert('allocationsbills',$insAllocationDetail);
                            if($this->db->affected_rows()>0){
                                
                                $updBills=array('billCurrentStatus'=>'Allocated','billType'=>'allocatedbillPass','isAllocated'=>1,'deliveryslipOwnerApproval'=>'1','isLostBill'=>'0');
                                $this->AllocationByManagerModel->update('bills',$updBills,$currentBillId);
                                if($this->db->affected_rows()>0){
                                    $history=array(
                                        'billId'=>$currentBillId,
                                        'allocationId'=>$allocationId,
                                        'transactionStatus' =>'Added to Allocation',
                                        'transactionDate'=>date('Y-m-d H:i:sa'),
                                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                                    );
                                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                                    echo "Record inserted";
                                }else{
                                    echo "Record not inserted";
                                }
                            }
                        }


                    }
                }
            }
        }else{  
            echo "Bill not found.";
        }
    }

    public function cashCollection(){
        $empName=trim($this->input->post('empName'));
        $empId=trim($this->input->post('empId'));
        $collectedAmt=trim($this->input->post('collectedAmt'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));
        $currentBillId=trim($this->input->post('currentBillId'));

        $a2000=trim($this->input->post('a2000'));
        // $a1000=trim($this->input->post('a1000'));
        $a500=trim($this->input->post('a500'));
        $a200=trim($this->input->post('a200'));
        $a100=trim($this->input->post('a100'));
        $a50=trim($this->input->post('a50'));
        $a20=trim($this->input->post('a20'));
        $a10=trim($this->input->post('a10'));
        $coin=trim($this->input->post('coin'));

        $updatedAt=date('Y-m-d H:i:sa');
        $updatedBy=$this->session->userdata['logged_in']['id'];

        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);//get bill detail
        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] > 0){
                $netAmount=$billInfo[0]['netAmount'];
                $pendingAmt=$billInfo[0]['pendingAmt'];
                $receivedAmt=$billInfo[0]['receivedAmt'];
                $compName=$billInfo[0]['compName'];
                $billNo=$billInfo[0]['billNo'];
                $bill_no="Bill No. ".$billNo;

                $lastBal=$this->AllocationByManagerModel->lastRecordValue();//get closing balance
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }

                $totalClosingBalance=$openCloseBal+$collectedAmt;//final closing balance

                $finalPending=$pendingAmt-$collectedAmt;//final pending for current bill
                $finalReceived=$receivedAmt+$collectedAmt;//final received for current bill
                
                $billUpdate=array();
                if($netAmount==$pendingAmt){
                    $billUpdate=array(
                        'billType'=>'allocatedbillCurrent','isResendBill'=>'0','deliveryslipOwnerApproval'=>'1','receivedAmt'=>$finalReceived,'pendingAmt'=>$finalPending
                    );
                }else{
                    $billUpdate=array(
                        'receivedAmt'=>$finalReceived,'isResendBill'=>'0','pendingAmt'=>$finalPending
                    );
                }

                $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                if($this->db->affected_rows()>0){

                    $history=array(
                        'billId'=>$currentBillId,
                        'transactionAmount' =>$collectedAmt,
                        'transactionStatus' =>'Cash',
                        'transactionMode' =>'dr',
                        'transactionDate'=>date('Y-m-d H:i:sa'),
                        'empId'=>$empId,
                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                    );
                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);


                    $billPayment=array(
                        'empId'=>$empId,'billId'=>$currentBillId,'date'=>$updatedAt,'paidAmount'=>$collectedAmt,'billAmount'=>$netAmount,'balanceAmount'=>$finalPending,'paymentMode'=>'Cash','updatedBy'=>$updatedBy
                    );
                    $this->AllocationByManagerModel->insert('billpayments',$billPayment);//insert billpayment data

                    $notesDetails=array('transactionType'=>"Office Collection",
                        'billId'=>$currentBillId,'empId'=>$empId,'note2000'=>$a2000,'note500'=>$a500,'note200'=>$a200,'note100'=>$a100,'note50'=>$a50,'note20'=>$a20,'note10'=>$a10,'coins'=>$coin,'collectedAmt'=>$collectedAmt,'updatedAt'=>$updatedAt,'updatedBy'=>$updatedBy
                    );
                    $this->AllocationByManagerModel->insert('notesdetails',$notesDetails);//insert notes details
                    if($this->db->affected_rows()>0){
                        $notesId=$this->db->insert_id();
                        $marketCollection=array(
                            'company'=>$compName,'billId'=>$currentBillId,'date'=>$updatedAt,'employeeId'=>$empId,'notesId'=>$notesId,'amount'=>$collectedAmt,'nature'=>'Office Collection','narration'=>$bill_no,'inoutStatus'=>'Inflow','openCloseBalance'=>$totalClosingBalance,'updatedBy'=>$updatedBy
                        );

                        $this->AllocationByManagerModel->insert('expences',$marketCollection);//insert expenses
                        if($this->db->affected_rows()>0){
                            echo "Record updated";
                        }
                    }

                    $employeeDetails=$this->AllocationByManagerModel->load('employee',$empId);
                    $employeeMobile=$employeeDetails[0]['mobile'];
                    $employeeName=$employeeDetails[0]['name'];
                    $transactionDate=date('M d, Y H:i a');

                    $office=$this->AllocationByManagerModel->load('office_details','1');
                    $jsonData=array(
                        "flow_id"=>"618d062949757c6da46c6d82",
                        "sender"=>"SIAInc",
                        "mobiles"=>'91'.$employeeMobile,
                        "mode"=>"Cash",
                        "amount"=>number_format($collectedAmt),
                        "name"=>$employeeName,
                        "distributorname"=>$office[0]['distributorName'],
                        "dateandtime"=>date('d M,Y H:i A'),
                        "company"=>$compName,
                        "billnumber"=>$billNo
                    );

                    $jsonData=json_encode($jsonData);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.msg91.com/api/v5/flow/",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $jsonData,
                        CURLOPT_HTTPHEADER => array("authkey: 291106Atbm2KHoWhat5d99ec46","content-type: application/JSON"),
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                }else{
                    echo "Record not updated";
                }
            }else{
                echo "Pending amount is 0.";
            }
        }else{
            echo "Bill not found.";
        }
    }

      public function chequeCollection(){
        $empName=trim($this->input->post('empName'));
        $empId=trim($this->input->post('empId'));
        $collectedAmt=trim($this->input->post('chequeAmount'));
        $chequeNumber=trim($this->input->post('chequeNumber'));
        $chequeDate=trim($this->input->post('chequeDate'));
        $chequeBank=trim($this->input->post('chequeBank'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));
        $currentBillId=trim($this->input->post('currentBillId'));

        $updatedAt=date('Y-m-d H:i:sa');
        $updatedBy=$this->session->userdata['logged_in']['id'];

        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);//get bill detail
        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] > 0){
                $newBillNo=$billInfo[0]['billNo'];
                $netAmount=$billInfo[0]['netAmount'];
                $pendingAmt=$billInfo[0]['pendingAmt'];
                $receivedAmt=$billInfo[0]['receivedAmt'];

                $billCompName=$billInfo[0]['compName'];
                $billRetailerName=$billInfo[0]['retailerName'];

                $finalPending=$pendingAmt-$collectedAmt;//final pending for current bill
                $finalReceived=$receivedAmt+$collectedAmt;//final received for current bill

                $clubBillNo="";
                $clubRetailer="";
                $isNeftNoPresent=$this->AllocationByManagerModel->findChequeWithBank('billpayments',$chequeNumber,$chequeDate,$chequeBank);
                if((empty($isNeftNoPresent))){
                    echo "";
                }else{
                    foreach($isNeftNoPresent as $itm){
                        $clubBillNo=$clubBillNo.",".$itm['billNo'];
                        $clubRetailer=$clubRetailer.",".$itm['retailerName'];
                    }
                }
                $clubBillNo=$clubBillNo.','.$newBillNo;
                $clubRetailer=$clubRetailer.','.$billRetailerName;
                $clubBillNo=trim($clubBillNo,',');
                $clubRetailer=trim($clubRetailer,',');

                $clubBillNo=array_unique(explode(',',$clubBillNo));
                $clubBillNo=implode(',',$clubBillNo);
                $clubBillNo=trim($clubBillNo,',');

                $clubRetailer=array_unique(explode(',',$clubRetailer));
                $clubRetailer=implode(',',$clubRetailer);
                $clubRetailer=trim($clubRetailer,',');

                // echo $clubBillNo.' '.$clubRetailer;exit;

                
                $billUpdate=array();
                if($netAmount==$pendingAmt){
                    $billUpdate=array(
                        'billType'=>'allocatedbillCurrent','isResendBill'=>'0','deliveryslipOwnerApproval'=>'1','receivedAmt'=>$finalReceived,'pendingAmt'=>$finalPending
                    );
                }else{
                    $billUpdate=array(
                        'receivedAmt'=>$finalReceived,'isResendBill'=>'0','pendingAmt'=>$finalPending
                    );
                }

                if($empId !=="" && $collectedAmt !=="" && $chequeNumber ==="" && $chequeDate === "" && $chequeBank ===""){
                    // echo "hye";exit;
                    $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                    if($this->db->affected_rows()>0){

                        $history=array(
                            'billId'=>$currentBillId,
                            'transactionAmount' =>$collectedAmt,
                            'transactionStatus' =>'Cheque',
                            'transactionMode' =>'dr',
                            'transactionDate'=>date('Y-m-d H:i:sa'),
                            'empId'=>$empId,
                            'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                        );
                        $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                        $billPayment=array(
                            'empId'=>$empId,
                            'billId'=>$currentBillId,
                            'chequeNo'=>$chequeNumber,
                            'chequeReceivedDate'=>$updatedAt,
                            'date'=>$updatedAt,
                            'paidAmount'=>$collectedAmt,
                            'billAmount'=>$netAmount,
                            'balanceAmount'=>$finalPending,
                            'paymentMode'=>'Cheque',
                            'isLostStatus'=>2,
                            'compName'=>$billCompName,
                            'billNo'=>$clubBillNo,
                            'retailerName'=>$clubRetailer,
                            'updatedBy'=>$updatedBy
                        );
                        $this->AllocationByManagerModel->insert('billpayments',$billPayment);//insert billpayment data

                        // $updChequeDetail=array(
                        //     'billNo'=>$clubBillNo,
                        //     'retailerName'=>$clubRetailer
                        // );
                        // $this->AllocationByManagerModel->updateChequeData('billpayments',$updChequeDetail,$chequeNumber,$chequeDate,$chequeBank);//update billpayment data

                        $employeeDetails=$this->AllocationByManagerModel->load('employee',$empId);
                        $employeeMobile=$employeeDetails[0]['mobile'];
                        $employeeName=$employeeDetails[0]['name'];
                        $transactionDate=date('M d, Y H:i a');
                        //send sms to employee
                        // $this->sendChequeSMS($employeeName,$employeeMobile,$collectedAmt,$transactionDate,$billCompName,$clubBillNo);

                        echo "Record updated";
                    }else{
                        echo "Record not updated";
                    }
                }else if($empId !=="" && $collectedAmt !=="" && $chequeNumber !=="" && $chequeDate !== "" && $chequeBank !==""){
                    $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                    if($this->db->affected_rows()>0){

                        $history=array(
                            'billId'=>$currentBillId,
                            'transactionAmount' =>$collectedAmt,
                            'transactionStatus' =>'Cheque',
                            'transactionMode' =>'dr',
                            'transactionDate'=>date('Y-m-d H:i:sa'),
                            'empId'=>$empId,
                            'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                        );
                        $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                        $billPayment=array(
                            'empId'=>$empId,
                            'billId'=>$currentBillId,
                            'chequeNo'=>$chequeNumber,
                            'chequeDate'=>$chequeDate,
                            'chequeReceivedDate'=>$updatedAt,
                            'chequeStatus'=>'New',
                            'chequeStatusDate'=>$updatedAt,
                            'date'=>$updatedAt,
                            'paidAmount'=>$collectedAmt,
                            'billAmount'=>$netAmount,
                            'balanceAmount'=>$finalPending,
                            'paymentMode'=>'Cheque',
                            'isLostStatus'=>2,
                            'compName'=>$billCompName,
                            'billNo'=>$clubBillNo,
                            'retailerName'=>$clubRetailer,
                            'chequeBank'=>$chequeBank,
                            'updatedBy'=>$updatedBy
                        );
                        $this->AllocationByManagerModel->insert('billpayments',$billPayment);//insert billpayment data

                        if($chequeNumber !=="" && $chequeDate !== "" && $chequeBank !==""){
                            $updChequeDetail=array(
                                'billNo'=>$clubBillNo,
                                'retailerName'=>$clubRetailer
                            );
                            $this->AllocationByManagerModel->updateChequeData('billpayments',$updChequeDetail,$chequeNumber,$chequeDate,$chequeBank);//update billpayment data
                        }
                        
                        $employeeDetails=$this->AllocationByManagerModel->load('employee',$empId);
                        $employeeMobile=$employeeDetails[0]['mobile'];
                        $employeeName=$employeeDetails[0]['name'];
                        $transactionDate=date('M d, Y H:i a');
                        //send sms to employee
                        $this->sendChequeSMS($employeeName,$employeeMobile,$collectedAmt,$transactionDate,$billCompName,$clubBillNo);

                        echo "Record updated";
                    }else{
                        echo "Record not updated";
                    }
                }
            }else{
                echo "Pending amount is 0.";
            }
        }else{
            echo "Bill not found.";
        }
    }

    public function neftCollection(){
        $empName=trim($this->input->post('empName'));
        $empId=trim($this->input->post('empId'));
        $collectedAmt=trim($this->input->post('neftAmount'));
        $neftNumber=trim($this->input->post('neftNumber'));
        $neftDate=trim($this->input->post('neftDate'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));
        $currentBillId=trim($this->input->post('currentBillId'));

        $updatedAt=date('Y-m-d H:i:sa');
        $updatedBy=$this->session->userdata['logged_in']['id'];

        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);//get bill detail

        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] > 0){
                $newBillNo=$billInfo[0]['billNo'];
                $netAmount=$billInfo[0]['netAmount'];
                $pendingAmt=$billInfo[0]['pendingAmt'];
                $receivedAmt=$billInfo[0]['receivedAmt'];
                $billCompName=$billInfo[0]['compName'];
                $billRetailerName=$billInfo[0]['retailerName'];

                $finalPending=$pendingAmt-$collectedAmt;//final pending for current bill
                $finalReceived=$receivedAmt+$collectedAmt;//final received for current bill

                $clubBillNo="";
                $clubRetailer="";
                $isNeftNoPresent=$this->AllocationByManagerModel->findNeft('billpayments',$neftNumber,$neftDate);
                if((empty($isNeftNoPresent))){
                    echo "";
                }else{
                    foreach($isNeftNoPresent as $itm){
                        $clubBillNo=$clubBillNo.",".$itm['billNo'];
                        $clubRetailer=$clubRetailer.",".$itm['retailerName'];
                    }
                }
                $clubBillNo=$clubBillNo.','.$newBillNo;
                $clubRetailer=$clubRetailer.','.$billRetailerName;
                $clubBillNo=trim($clubBillNo,',');
                $clubRetailer=trim($clubRetailer,',');

                // echo $clubBillNo." ".$clubRetailer;exit;
                
                if($empId !=="" && $collectedAmt !=="" && $neftNumber ==="" && $neftDate ===""){
                    $billUpdate=array();
                    if($netAmount==$pendingAmt){
                        $billUpdate=array(
                            'billType'=>'allocatedbillCurrent','isResendBill'=>'0','deliveryslipOwnerApproval'=>'1','receivedAmt'=>$finalReceived,'pendingAmt'=>$finalPending
                        );
                    }else{
                        $billUpdate=array(
                            'receivedAmt'=>$finalReceived,'isResendBill'=>'0','pendingAmt'=>$finalPending
                        );
                    }

                    $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                    if($this->db->affected_rows()>0){

                        $history=array(
                            'billId'=>$currentBillId,
                            'transactionAmount' =>$collectedAmt,
                            'transactionStatus' =>'NEFT',
                            'transactionMode' =>'dr',
                            'transactionDate'=>date('Y-m-d H:i:sa'),
                            'empId'=>$empId,
                            'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                        );
                        $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                        $billPayment=array(
                            'empId'=>$empId,'billNo'=>$clubBillNo,'compName'=>$billCompName,'retailerName'=>$clubRetailer,'billId'=>$currentBillId,'date'=>$updatedAt,'paidAmount'=>$collectedAmt,'billAmount'=>$netAmount,'balanceAmount'=>$finalPending,'paymentMode'=>'NEFT','isLostStatus'=>2,'updatedBy'=>$updatedBy
                        );
                        $this->AllocationByManagerModel->insert('billpayments',$billPayment);//insert billpayment data

                        // $updChequeDetail=array(
                        //     'billNo'=>$clubBillNo,
                        //     'retailerName'=>$clubRetailer
                        // );
                        // $this->AllocationByManagerModel->updateNeftData('billpayments',$updChequeDetail,$neftNumber,$neftDate);//update billpayment data

                        echo "Record updated";
                    }else{
                        echo "Record not updated";
                    }
                }else if($empId !=="" && $collectedAmt !=="" && $neftNumber !=="" && $neftDate !==""){
                    $billUpdate=array();
                    if($netAmount==$pendingAmt){
                        $billUpdate=array(
                            'billType'=>'allocatedbillCurrent','isResendBill'=>'0','deliveryslipOwnerApproval'=>'1','receivedAmt'=>$finalReceived,'pendingAmt'=>$finalPending
                        );
                    }else{
                        $billUpdate=array(
                            'receivedAmt'=>$finalReceived,'isResendBill'=>'0','pendingAmt'=>$finalPending
                        );
                    }

                    $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                    if($this->db->affected_rows()>0){
                        $history=array(
                            'billId'=>$currentBillId,
                            'transactionAmount' =>$collectedAmt,
                            'transactionStatus' =>'NEFT',
                            'transactionMode' =>'dr',
                            'transactionDate'=>date('Y-m-d H:i:sa'),
                            'empId'=>$empId,
                            'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                        );
                        $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                        $billPayment=array(
                            'empId'=>$empId,'billNo'=>$clubBillNo,'compName'=>$billCompName,'retailerName'=>$clubRetailer,'billId'=>$currentBillId,'chequeStatus'=>'New','chequeReceivedDate'=>$updatedAt,'date'=>$updatedAt,'paidAmount'=>$collectedAmt,'billAmount'=>$netAmount,'neftNo'=>$neftNumber,'neftDate'=>$neftDate,'balanceAmount'=>$finalPending,'paymentMode'=>'NEFT','isLostStatus'=>2,'updatedBy'=>$updatedBy
                        );
                        $this->AllocationByManagerModel->insert('billpayments',$billPayment);//insert billpayment data

                        if($neftNumber !=="" && $neftDate !==""){
                            $updChequeDetail=array(
                                'billNo'=>$clubBillNo,
                                'retailerName'=>$clubRetailer
                            );
                            $this->AllocationByManagerModel->updateNeftData('billpayments',$updChequeDetail,$neftNumber,$neftDate);//update billpayment data
                        }
                        
                        echo "Record updated";
                    }else{
                        echo "Record not updated";
                    }
                }
            }else{
                echo "Pending amount is 0.";
            }
        }else{
            echo "Bill not found.";
        }
    }

    public function cashDiscountCollection(){
        $empName=trim($this->input->post('empName'));
        $empId=trim($this->input->post('empId'));
        $collectedAmt=trim($this->input->post('cdAmount'));
        $remark=trim($this->input->post('cdRemark'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));
        $currentBillId=trim($this->input->post('currentBillId'));

        $updatedAt=date('Y-m-d H:i:sa');
        $updatedBy=$this->session->userdata['logged_in']['id'];

        $designation = ($this->session->userdata['logged_in']['designation']);
        $des=explode(',',$designation);

        $status=0;
        if ((in_array('owner', $des))) {
            $status=1;
        }

        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);//get bill detail
        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] > 0){
                $netAmount=$billInfo[0]['netAmount'];
                $pendingAmt=$billInfo[0]['pendingAmt'];
                $cd=$billInfo[0]['cd'];

                $finalPending=$pendingAmt-$collectedAmt;//final pending for current bill
                $finalCd=$cd+$collectedAmt;//final received for current bill
                
                $billUpdate=array();
                if($netAmount==$pendingAmt){
                    $billUpdate=array(
                        'billType'=>'allocatedbillCurrent','isResendBill'=>'0','deliveryslipOwnerApproval'=>'1','cd'=>$finalCd,'pendingAmt'=>$finalPending
                    );
                }else{
                    $billUpdate=array(
                        'cd'=>$finalCd,'isResendBill'=>'0','pendingAmt'=>$finalPending
                    );
                }

                $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                if($this->db->affected_rows()>0){
                    $history=array(
                        'billId'=>$currentBillId,
                        'transactionAmount' =>$collectedAmt,
                        'transactionStatus' =>'CD',
                        'transactionMode' =>'dr',
                        'transactionDate'=>date('Y-m-d H:i:sa'),
                        'empId'=>$empId,
                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                    );
                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                    $billRemark=array(
                        'billId'=>$currentBillId,'empId'=>$empId,'remark'=>$remark,'amount'=>$collectedAmt,'updatedAt'=>$updatedAt,'updatedBy'=>$updatedBy
                    );
                    $this->AllocationByManagerModel->insert('bill_remark_history',$billRemark);//insert remark data

                    $billPayment=array(
                        'empId'=>$empId,'billId'=>$currentBillId,'date'=>$updatedAt,'paidAmount'=>$collectedAmt,'billAmount'=>$netAmount,'balanceAmount'=>$finalPending,'paymentMode'=>'CD','isLostStatus'=>2,'updatedBy'=>$updatedBy,'ownerApproval'=>$status
                    );
                    $this->AllocationByManagerModel->insert('billpayments',$billPayment);
                    echo "Record updated";
                }else{
                    echo "Record not updated";
                }
            }else{
                "Pending amount is 0.";
            }
        }else{
            echo "Bill not found.";
        }
    }

    public function debitToEmployeeCollection(){
        $empName=trim($this->input->post('empName'));
        $empId=trim($this->input->post('empId'));
        $collectedAmt=trim($this->input->post('debitAmount'));
        $remark=trim($this->input->post('debitRemark'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));
        $currentBillId=trim($this->input->post('currentBillId'));

        $updatedAt=date('Y-m-d H:i:sa');
        $updatedBy=$this->session->userdata['logged_in']['id'];

        $designation = ($this->session->userdata['logged_in']['designation']);
        $des=explode(',',$designation);

        $status=0;
        if ((in_array('owner', $des))) {
            $status=1;
        }
    
        $companyDetails=$this->AllocationByManagerModel->getdata('office_details');
        $officeName=$companyDetails[0]['distributorName'];
        $distributorCode=$companyDetails[0]['distributorCode'];
        
        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);//get bill detail

        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] > 0){
                $netAmount=$billInfo[0]['netAmount'];
                $pendingAmt=$billInfo[0]['pendingAmt'];
                $debit=$billInfo[0]['debit'];
                $billCompName=$billInfo[0]['compName'];
                $billNo=$billInfo[0]['billNo'];

                $finalPending=$pendingAmt-$collectedAmt;//final pending for current bill
                $finalDebit=$debit+$collectedAmt;//final received for current bill
                
                $billUpdate=array();
                if($netAmount==$pendingAmt){
                    $billUpdate=array(
                        'billType'=>'allocatedbillCurrent','isResendBill'=>'0','deliveryslipOwnerApproval'=>'1','debit'=>$finalDebit,'pendingAmt'=>$finalPending
                    );
                }else{
                    $billUpdate=array(
                        'debit'=>$finalDebit,'isResendBill'=>'0','pendingAmt'=>$finalPending
                    );
                }

                $lastBal=$this->AllocationByManagerModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal-$collectedAmt;

                // echo $lastBal['openCloseBalance'].' '.$openCloseBal.' '.$collectedAmt;exit;

                $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                if($this->db->affected_rows()>0){
                    $history=array(
                        'billId'=>$currentBillId,
                        'transactionAmount' =>$collectedAmt,
                        'transactionStatus' =>'Debit To Employee',
                        'transactionMode' =>'dr',
                        'transactionDate'=>date('Y-m-d H:i:sa'),
                        'empId'=>$empId,
                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                    );
                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                    $billRemark=array(
                        'billId'=>$currentBillId,'empId'=>$empId,'remark'=>$remark,'amount'=>$collectedAmt,'updatedAt'=>$updatedAt,'updatedBy'=>$updatedBy
                    );
                    $this->AllocationByManagerModel->insert('bill_remark_history',$billRemark);//insert remark data

                    $empDebit=array(
                        'billId'=>$currentBillId,'empId'=>$empId,'transactionType'=>'dr','description'=>$remark,'amount'=>$collectedAmt,'createdAt'=>$updatedAt,'createdBy'=>$updatedBy
                    );
                     $this->AllocationByManagerModel->insert('emptransactions',$empDebit);//insert remark data

                    $billPayment=array(
                        'empId'=>$empId,'billId'=>$currentBillId,'date'=>$updatedAt,'paidAmount'=>$collectedAmt,'billAmount'=>$netAmount,'balanceAmount'=>$finalPending,'paymentMode'=>'Debit To Employee','isLostStatus'=>2,'updatedBy'=>$updatedBy,'ownerApproval'=>$status
                    );
                    $this->AllocationByManagerModel->insert('billpayments',$billPayment);


                    $lastBal=$this->AllocationByManagerModel->lastRecordDayBookValue();
                    $openCloseBal=$lastBal['openCloseBalance'];
                    if($openCloseBal=='' || $openCloseBal==Null){
                        $openCloseBal=0.0;
                    }
                    $openCloseBal=$openCloseBal+$collectedAmt;
                    $billNarration="Bill No.  : ".$billNo." market collection";
                    $expenseData=array('company'=>$billCompName,'employeeId'=>$empId,'narration'=>$billNarration,'amount'=>$collectedAmt,"nature"=>"Market Collection",'inoutStatus'=>'Inflow','date'=>$updatedAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$updatedBy);
                    $this->AllocationByManagerModel->insert('expences',$expenseData);

                    $lastBal=$this->AllocationByManagerModel->lastRecordDayBookValue();
                    $openCloseBal=$lastBal['openCloseBalance'];
                    if($openCloseBal=='' || $openCloseBal==Null){
                        $openCloseBal=0.0;
                    }
                    $openCloseBal=$openCloseBal-$collectedAmt;
                    $billNarration="Bill No.  : ".$billNo." debit entry for employee debit";
                    $expenseData=array('company'=>$billCompName,'employeeId'=>$empId,'narration'=>$billNarration,'amount'=>$collectedAmt,"nature"=>"Employee Advances",'inoutStatus'=>'Outflow','date'=>$updatedAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$updatedBy);
                    $this->AllocationByManagerModel->insert('expences',$expenseData);

                    $employeeDetails=$this->AllocationByManagerModel->load('employee',$empId);
                    $employeeMobile=$employeeDetails[0]['mobile'];
                    $employeeName=$employeeDetails[0]['name'];
                    $transactionDate=date('M d, Y H:i a');
                    //send sms to employee
                    // $this->sendEmployeeDebitSMS($employeeName,$employeeMobile,$collectedAmt,$transactionDate,$billCompName,$billNo,$remark);
                    
                    $ledger=$this->AllocationByManagerModel->getEmpLedgerByEmp('emptransactions',$empId);
                    $balance=0;
                    if(!empty($ledger)){
                        foreach($ledger as $leg){
                            if($leg['transactionType']=='cr'){
                                $balance=$balance+$leg['amount'];
                            }else if($leg['transactionType']=='dr'){
                                 $balance=$balance-$leg['amount'];
                            }
                        }
                    }
                    
                    if($balance > 0){
                        $balance=number_format(abs($balance));
                        $balance =($balance).' Cr';
                    }else{
                        $balance=number_format(abs($balance));
                        $balance= ($balance).' Dr';
                    }

                    $jsonData=array(
                        "flow_id"=>"618d064ba4528b1fda1ceb1d",
                        "sender"=>"SIAInc",
                        "mobiles"=>'91'.$employeeMobile,
                        "name"=>$employeeName,
                        "amount"=>number_format($collectedAmt),
                        "distributorname"=>$officeName,
                        "dateandtime"=>date('d M, Y H:i A'),
                        "company"=>$billCompName,
                        "billnumber"=>$billNo,
                        "balance"=>$balance
                    );

                    $jsonData=json_encode($jsonData);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.msg91.com/api/v5/flow/",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $jsonData,
                        CURLOPT_HTTPHEADER => array("authkey: 291106Atbm2KHoWhat5d99ec46","content-type: application/JSON"),
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);

                    echo "Record updated";
                }else{
                    echo "Record not updated";
                }
            }else{
                echo "Pending amount is 0.";
            }
        }else{
            echo "Bill not found.";
        }
    }

    public function officeAdjustmentCollection(){
        $empName=trim($this->input->post('empName'));
        $empId=trim($this->input->post('empId'));
        $collectedAmt=trim($this->input->post('officeAdjAmount'));
        $remark=trim($this->input->post('officeAdjRemark'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));
        $currentBillId=trim($this->input->post('currentBillId'));
        $updatedAt=date('Y-m-d H:i:sa');
        $updatedBy=$this->session->userdata['logged_in']['id'];

        $designation = ($this->session->userdata['logged_in']['designation']);
        $des=explode(',',$designation);

        $status=0;
        if ((in_array('owner', $des))) {
            $status=1;
        }

        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);//get bill detail

        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] > 0){
                $netAmount=$billInfo[0]['netAmount'];
                $pendingAmt=$billInfo[0]['pendingAmt'];
                $officeAdjustment=$billInfo[0]['officeAdjustmentBillAmount'];

                $finalPending=$pendingAmt-$collectedAmt;//final pending for current bill
                $finalOA=$officeAdjustment+$collectedAmt;//final received for current bill
                
                // $billUpdate=array(
                //     'billType'=>'officeAdjustmentBill','isOfficeAdjustmentBill'=>1,'officeAdjustmentBillAmount'=>$finalOA,'pendingAmt'=>$finalPending
                // );

                $billUpdate=array();
                if($netAmount==$pendingAmt){
                    $billUpdate=array(
                        'billType'=>'allocatedbillCurrent','isResendBill'=>'0','deliveryslipOwnerApproval'=>'1','isOfficeAdjustmentBill'=>1,'officeAdjustmentBillAmount'=>$finalOA,'pendingAmt'=>$finalPending
                    );
                }else{
                    $billUpdate=array(
                        'officeAdjustmentBillAmount'=>$finalOA,'isResendBill'=>'0','isOfficeAdjustmentBill'=>1,'pendingAmt'=>$finalPending
                    );
                }

                $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                if($this->db->affected_rows()>0){
                    $history=array(
                        'billId'=>$currentBillId,
                        'transactionAmount' =>$collectedAmt,
                        'transactionStatus' =>'Office Adjustment',
                        'transactionMode' =>'dr',
                        'transactionDate'=>date('Y-m-d H:i:sa'),
                        'empId'=>$empId,
                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                    );
                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                    $billRemark=array(
                        'billId'=>$currentBillId,'empId'=>$empId,'remark'=>$remark,'amount'=>$collectedAmt,'updatedAt'=>$updatedAt,'updatedBy'=>$updatedBy
                    );
                    $this->AllocationByManagerModel->insert('bill_remark_history',$billRemark);//insert remark data

                    $billPayment=array(
                        'empId'=>$empId,'billId'=>$currentBillId,'date'=>$updatedAt,'paidAmount'=>$collectedAmt,'billAmount'=>$netAmount,'balanceAmount'=>$finalPending,'paymentMode'=>'Office Adjustment','isLostStatus'=>2,'updatedBy'=>$updatedBy,'ownerApproval'=>$status
                    );
                    $this->AllocationByManagerModel->insert('billpayments',$billPayment);
                   
                    echo "Record updated";
                }else{
                    echo "Record not updated";
                }
            }else{
                echo "Pending amount is 0.";
            }
        }else{
            echo "Bill not found.";
        }
        
    }

    public function empDeliveryCollection(){
        $empName=trim($this->input->post('empName'));
        $empId=trim($this->input->post('empId'));
        $remark=trim($this->input->post('deliveryRemark'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));
        $currentBillId=trim($this->input->post('currentBillId'));

        $updatedAt=date('Y-m-d H:i:sa');
        $updatedBy=$this->session->userdata['logged_in']['id'];

        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);//get bill detail

        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] > 0){
                $netAmount=$billInfo[0]['netAmount'];
                $pendingAmt=$billInfo[0]['pendingAmt'];
                $retailerName=$billInfo[0]['retailerName'];
                $billNo=$billInfo[0]['billNo'];

                $billUpdate=array(
                    'billCurrentStatus'=>'Direct Delivery','isResendBill'=>'0','billType'=>'adHocDeliveryBill','deliveryslipOwnerApproval'=>'1','deliveryEmpName'=>$empName,'isDirectDeliveryBill'=>1
                );

                $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                if($this->db->affected_rows()>0){
                    $billRemark=array(
                        'billId'=>$currentBillId,'empId'=>$empId,'remark'=>$remark,'updatedAt'=>$updatedAt,'updatedBy'=>$updatedBy,'isDirectDeliveryBill'=>1
                    );
                    $this->AllocationByManagerModel->insert('bill_remark_history',$billRemark);//insert remark data
                    

                    $history=array(
                        'billId'=>$currentBillId,
                        'transactionStatus' =>'Direct Delivery',
                        'transactionDate'=>date('Y-m-d H:i:sa'),
                        'empId'=>$empId,
                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                    );
                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                    $companyDetails=$this->AllocationByManagerModel->getdata('office_details');
                    $officeName=$companyDetails[0]['distributorName'];
                    $distributorCode=$companyDetails[0]['distributorCode'];
            
                    $employeeDetails=$this->AllocationByManagerModel->load('employee',$empId);
                    $employeeMobile=$employeeDetails[0]['mobile'];
                    $employeeName=$employeeDetails[0]['name'];
                    $employeeDesignation=$employeeDetails[0]['designation'];
                    $transactionDate=date('M d, Y H:i a');
                    
                    $jsonData=array(
                        "flow_id"=>"618d066ddcd3551b5e036bb6",
                        "sender"=>"SIAInc",
                        "mobiles"=>'91'.$employeeMobile,
                        "name"=>$employeeName,
                        "distributorname"=>$officeName,
                        "billnumber"=>$billNo,
                        "retailername"=>substr($retailerName,0,25),
                        "amount"=>number_format($pendingAmt),
                        "date"=>date('d M, Y'),
                        "remarks"=>substr($remark,0,20)
                    );

                    $jsonData=json_encode($jsonData);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.msg91.com/api/v5/flow/",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $jsonData,
                        CURLOPT_HTTPHEADER => array("authkey: 291106Atbm2KHoWhat5d99ec46","content-type: application/JSON"),
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                    
                   
                    echo "Record updated";
                }else{
                    echo "Record not updated";
                }
            }else{
                echo "Pending amount is 0.";
            }
        }else{
            echo "Bill not found.";
        }
    }

    public function otherAdjustmentCollection(){
        $empName=trim($this->input->post('empName'));
        $empId=trim($this->input->post('empId'));
        $collectedAmt=trim($this->input->post('otherAdjAmount'));
        $remark=trim($this->input->post('otherAdjRemark'));
        $currentPendingAmt=trim($this->input->post('currentPendingAmt'));
        $currentBillId=trim($this->input->post('currentBillId'));

        $designation = ($this->session->userdata['logged_in']['designation']);
        $des=explode(',',$designation);

        $status=0;
        if ((in_array('owner', $des))) {
            $status=1;
        }

        $updatedAt=date('Y-m-d H:i:sa');
        $updatedBy=$this->session->userdata['logged_in']['id'];

        $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);//get bill detail

        if(!empty($billInfo)){
            if($billInfo[0]['pendingAmt'] > 0){
                $netAmount=$billInfo[0]['netAmount'];
                $pendingAmt=$billInfo[0]['pendingAmt'];
                $otherAdjustment=$billInfo[0]['otherAdjustment'];

                $finalPending=$pendingAmt-$collectedAmt;//final pending for current bill
                $finalOA=$otherAdjustment+$collectedAmt;//final received for current bill
                
                $billUpdate=array();
                if($netAmount==$pendingAmt){
                    $billUpdate=array(
                        'billType'=>'allocatedbillCurrent','isResendBill'=>'0','deliveryslipOwnerApproval'=>'1','isOtherAdjustmentBill'=>1,'otherAdjustment'=>$finalOA,'pendingAmt'=>$finalPending
                    );
                }else{
                    $billUpdate=array(
                        'otherAdjustment'=>$finalOA,'isResendBill'=>'0','isOtherAdjustmentBill'=>1,'pendingAmt'=>$finalPending
                    );
                }

                $this->AllocationByManagerModel->update('bills',$billUpdate,$currentBillId);//update bill amount
                if($this->db->affected_rows()>0){
                    $history=array(
                        'billId'=>$currentBillId,
                        'transactionAmount' =>$collectedAmt,
                        'transactionStatus' =>'Other Adjustment',
                        'transactionMode' =>'dr',
                        'transactionDate'=>date('Y-m-d H:i:sa'),
                        'empId'=>$empId,
                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                    );
                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);

                    $billRemark=array(
                        'billId'=>$currentBillId,'empId'=>$empId,'remark'=>$remark,'amount'=>$collectedAmt,'updatedAt'=>$updatedAt,'updatedBy'=>$updatedBy
                    );
                    $this->AllocationByManagerModel->insert('bill_remark_history',$billRemark);//insert remark data

                    $billPayment=array(
                        'empId'=>$empId,'billId'=>$currentBillId,'date'=>$updatedAt,'paidAmount'=>$collectedAmt,'billAmount'=>$netAmount,'balanceAmount'=>$finalPending,'paymentMode'=>'Other Adjustment','isLostStatus'=>2,'updatedBy'=>$updatedBy,'ownerApproval'=>$status
                    );
                    $this->AllocationByManagerModel->insert('billpayments',$billPayment);
                   
                   
                    echo "Record updated";
                }else{
                    echo "Record not updated";
                }
            }else{
                echo "Pending amount is 0.";
            }
        }else{
            echo "Bill not found.";
        }
    }

    public function getSrDetails(){
        $billId=trim($this->input->post('currentBillId'));
        $billsInfo=$this->AllocationByManagerModel->load('bills', $billId);
        $billDetails=$this->AllocationByManagerModel->getBillDetailInfo('billsdetails',$billId);

    ?>
    <!-- <form action="<?php echo site_url('BillTransactionController/updateSRCreditAdj');?>" method="post"> -->
    <div class="col-md-12">
         <table style="font-size: 12px" id="SrTable" class="table table-bordered table-striped table-hover" data-page-length='100'>
                <span id="all_id" style="display:none"></span>
                <thead>
                    <tr>
                        <th>S. No</th>
                        <th>Product Name</th>
                        <th>MRP</th>
                        <th>Billed Qty</th>
                        <th>Net Amount</th>
                        <th>Old SR</th>
                        <th>Old SR Amount</th>
                        <th>Current SR</th>
                        <th>Current SR Amount</th>
                    </tr>
                </thead>
                <tfoot>
                     <tr>
                        <th>S. No</th>
                        <th>Product Name</th>
                        <th>MRP</th>
                        <th>Qty</th>
                        <th>Net Amount</th>
                        <th>Old SR</th>
                        <th>Old SR Amount</th>
                        <th>Current SR</th>
                        <th>Current SR Amount</th>
                    </tr>
                </tfoot>
                <tbody>


                    <?php
                    if(!empty($billDetails)){
                      $no=0;
                      foreach ($billDetails as $data) 
                        {
                         $no++; 
                         $id_is=$data['id'];
                         $id_qty=$data['qty'];
                         $id_fs_qty=$data['gkReturnQty'];
                      ?>
                <tr>
                    <td><?php echo $no; ?></td>
                    <td><?php echo $data['productName']; ?></td>
                     <input type="hidden" id="prodName_id" name="productName[]" value="<?php echo $data['productName']; ?>" readonly>
                    <td>
                        <?php echo $data['mrp']; ?> 
                        <input type="hidden" id="mrp_id" name="mrp[]" value="<?php echo $data['mrp']; ?>" readonly>
                    </td>
                    <td>
                        <?php echo $data['qty']; ?>
                          <input type="hidden" id="qty_id<?php echo $no; ?>" name="qty[]" value="<?php echo $data['qty']; ?>" readonly> 
                          <input type="hidden" name="fsReturnQty[]" value="<?php echo $data['fsReturnQty']; ?>" readonly> 
                    </td> 
                    <td>
                        <?php echo $data['netAmount']; ?>
                        <input type="hidden" id="netAmount_id<?php echo $no; ?>" name="netAmount[]" value="<?php echo $data['netAmount']; ?>" readonly>
                        <input type="hidden" id="selAmount_id" name="selAmount[]" value="<?php echo $data['sellingRate']; ?>" readonly>         
                    </td>
                     <td>
                         <?php echo $data['gkReturnQty']; ?>
                         <input type="hidden" id="id_id" name="id[]" value="<?php echo $data['id']; ?>">
                         <input type="hidden" id="billId_id" name="billId" value="<?php echo $data['billId']; ?>">
                    </td>
                    <td>
                        <?php if($data['gkReturnQty'] >0){ echo $data['fsReturnAmt']; }else{ echo "0.00"; } ?>
                    </td>
                    <td>
                    <?php if($data['qty']==$data['fsReturnQty']){?>
                        <input type="text" style="height:25px;width: 50%" onkeypress="return numbersonly(this, event);" id="returnedQty<?php echo $no; ?>" onkeyup="checkQtyPerItem(this,'<?php echo $no; ?>','<?php echo $id_qty; ?>','<?php echo $id_fs_qty; ?>','<?php echo $billsInfo[0]['netAmount'];?>','<?php echo $billsInfo[0]['pendingAmt'];?>','<?php echo $billsInfo[0]['SRAmt'];?>','<?php echo $billsInfo[0]['receivedAmt'];?>','<?php echo $billsInfo[0]['fsCashAmt'];?>','<?php echo $billsInfo[0]['fsChequeAmt'];?>','<?php echo $billsInfo[0]['fsNeftAmt'];?>','<?php echo $billsInfo[0]['fsSrAmt'];?>')" onfocus="this.select();" autofocus="autofocus" class="form-control" name="returnedQty[]" value="<?php echo '0'; ?>">
                        <span style="color:red" id="data_err<?php echo $no; ?>"></span>
                        
                     <?php }else{ ?>
                         <input id="returnedQty<?php echo $no; ?>" style="height:25px;width: 50%" onkeypress="return numbersonly(this, event);" onkeyup="checkQtyPerItem(this,'<?php echo $no; ?>','<?php echo $id_qty; ?>','<?php echo $id_fs_qty; ?>','<?php echo $billsInfo[0]['netAmount'];?>','<?php echo $billsInfo[0]['pendingAmt'];?>','<?php echo $billsInfo[0]['SRAmt'];?>','<?php echo $billsInfo[0]['receivedAmt'];?>','<?php echo $billsInfo[0]['fsCashAmt'];?>','<?php echo $billsInfo[0]['fsChequeAmt'];?>','<?php echo $billsInfo[0]['fsNeftAmt'];?>','<?php echo $billsInfo[0]['fsSrAmt'];?>')" onfocus="this.select();" autofocus="autofocus" type="text" class="form-control" name="returnedQty[]" value="<?php echo '0'; ?>">
                         <span style="color:red" id="data_err<?php echo $no; ?>"></span> 
                     <?php }?>
                    </td>
                    <td>
                        <span id="return_total_Amt_id<?php echo $no; ?>"><?php echo "0.00"; ?> </span>
                         
                         <input type="hidden" id="returnAmt_id<?php echo $no; ?>" name="returnAmt[]" value="<?php echo $data['returnAmt']; ?>">             
                    </td>
               </tr>  

                 <?php
                    }
                }else{ ?>
                    <tr><td>No data available</td></tr>
              <?php  } ?>
                </tbody>
            </table>
        </div>

   
        <div class="col-md-12">
            <div class="col-md-4">
                <button id="srBtn" type="button" class="btn btn-primary m-t-15 waves-effect">
                    <i class="material-icons">save</i> 
                    <span class="icon-name">
                     Save SR
                    </span>
                </button>
                <?php if($billsInfo[0]['receivedAmt'] == 0.00){ ?>
                    <button type="button" id="fsrBtn" data-id="<?php echo $billId ?>" class="btn btn-primary m-t-15 waves-effect">
                        <i class="material-icons">save</i> 
                        <span class="icon-name">
                         Save FSR
                        </span>
                    </button>
                <?php } ?>
               
                <button type="button" data-dismiss="modal" class="btn btn-primary m-t-15 waves-effect">
                    <i class="material-icons">cancel</i> 
                    <span class="icon-name"> Cancel</span>
                </button>
            </div>
        </div>
    <!-- </form> -->

    <?php
    
    }

    public function confirmFSR(){
        $billId=trim($this->input->post('billId'));
        $billNetAmt=$this->AllocationByManagerModel->load('bills', $billId);
        $fixNetAmt=$billNetAmt[0]['netAmount'];//net amount from bills by billid
        $userId = ($this->session->userdata['logged_in']['id']);

        $creditAdj=round($billNetAmt[0]['creditAdjustment']);
        $totalFSR=$creditAdj+$fixNetAmt;
        if($creditAdj>0){
            $billDetail=$this->AllocationByManagerModel->loadBillDetails('billsdetails', $billId);
            $srTotalFs=0;
            $id=0;
            foreach($billDetail as $bill){
                $id = $bill['id'];
                // $billId = $bill['billId'];
                $name= $bill['productName'];
                $prodCode= $bill['productCode'];
                $mrp = $bill['mrp'];
                $qty = $bill['qty'];

                $netAmount = $bill['netAmount'];
                $returnedQty = $bill['gkReturnQty'];
                $returnAmt = $bill['fsReturnAmt'];

                //check return qty
                $actReturnQty=$qty-$returnedQty;
                $calAmt=0;
                    
                $calAmt=$netAmount/$qty;

                $ReturnAmount=$returnAmt+($calAmt * $actReturnQty);

                $data['billsdetails']=$this->AllocationByManagerModel->loadBillDetailsID('billsdetails', $id);
                $oldSR=$returnedQty+$actReturnQty;
                $srTotalFs=$srTotalFs+$ReturnAmount;
                
                $data = array(
                    'gkReturnQty' => $oldSR,
                    'fsReturnAmt' =>  $ReturnAmount
                );

                $this->AllocationByManagerModel->update('billsdetails',$data,$id);
                if($this->db->affected_rows()>0){
                    $srData = array(
                        'empId'=>$userId,
                        'billId' =>  $billId,
                        'billItemId'=>$id,
                        'quantity'=>$oldSR,
                        'createdAt'=>date('Y-m-d H:i:sa')
                    );
                    $this->AllocationByManagerModel->insert('allocation_sr_details',$srData);

                    //for product return with code for delivery slip products
                    $prodDet=$this->AllocationByManagerModel->getDetailByCode('products', $prodCode);
                    if(!empty($prodDet)){
                        $insertData=array(
                            'billingId'=>$billId,
                            'operationStatus'=>'add',
                            'quantityInPcs'=>$actReturnQty,
                            'productId'=>$prodDet[0]['id'],
                            'productName'=>$prodDet[0]['name'],
                            'productCode'=>$prodDet[0]['productCode'],
                            'quantity'=>$actReturnQty,
                            'quantityUnit'=>'pcs',
                            'createdBy'=>$this->session->userdata['logged_in']['id'],
                            'createdAt'=>date('Y-m-d H:i:sa')
                        );
                        $this->AllocationByManagerModel->insert('deliveryslip_pending_for_billing',$insertData);
                    }
                    
                } 
            }
            $dataBills = array('isFsrBill'=>1,'billType'=>'allocatedbillPass','deliveryslipOwnerApproval'=>'1','SRAmt' => $fixNetAmt,'creditNoteRenewal'=>$creditAdj,'pendingAmt'=>0);  
            $this->AllocationByManagerModel->update('bills',$dataBills, $billId);
            if($this->db->affected_rows()>0){
                echo "Record Updated";
            }else{
                echo "Record not Updated";
            }
        }else{
            $billDetail=$this->AllocationByManagerModel->loadBillDetails('billsdetails', $billId);
            $srTotalFs=0;
            $id=0;
            foreach($billDetail as $bill){
                $id = $bill['id'];
                $billId = $bill['billId'];
                $name= $bill['productName'];
                $prodCode= $bill['productCode'];
                $mrp = $bill['mrp'];
                $qty = $bill['qty'];

                $netAmount = $bill['netAmount'];
                $returnedQty = $bill['gkReturnQty'];
                $returnAmt = $bill['fsReturnAmt'];

                //check return qty
                $actReturnQty=$qty-$returnedQty;
                $calAmt=0;
                    
                $calAmt=$netAmount/$qty;

                $ReturnAmount=$returnAmt+($calAmt * $actReturnQty);

                $data['billsdetails']=$this->AllocationByManagerModel->loadBillDetailsID('billsdetails', $id);
                $oldSR=$returnedQty+$actReturnQty;
                $srTotalFs=$srTotalFs+$ReturnAmount;
                
                $data = array(
                    'gkReturnQty' => $oldSR,
                    'fsReturnAmt' =>  $ReturnAmount
                );

                $this->AllocationByManagerModel->update('billsdetails',$data,$id);
                if($this->db->affected_rows()>0){
                    $srData = array(
                        'empId'=>$userId,
                        'billId' =>  $billId,
                        'billItemId'=>$id,
                        'quantity'=>$oldSR,
                        'createdAt'=>date('Y-m-d H:i:sa')
                    );
                    $this->AllocationByManagerModel->insert('allocation_sr_details',$srData);

                    //for product return with code for delivery slip products
                    $prodDet=$this->AllocationByManagerModel->getDetailByCode('products', $prodCode);
                    if(!empty($prodDet)){
                        $insertData=array(
                            'billingId'=>$billId,
                            'operationStatus'=>'add',
                            'quantityInPcs'=>$actReturnQty,
                            'productId'=>$prodDet[0]['id'],
                            'productName'=>$prodDet[0]['name'],
                            'productCode'=>$prodDet[0]['productCode'],
                            'quantity'=>$actReturnQty,
                            'quantityUnit'=>'pcs',
                            'createdBy'=>$this->session->userdata['logged_in']['id'],
                            'createdAt'=>date('Y-m-d H:i:sa')
                        );
                        // print_r($insertData);exit;
                        $this->AllocationByManagerModel->insert('deliveryslip_pending_for_billing',$insertData);
                    }
                }
            }
            $dataBills = array('isFsrBill'=>1,'billType'=>'allocatedbillPass','deliveryslipOwnerApproval'=>'1','SRAmt' => $fixNetAmt,'pendingAmt'=>0);  
            $this->AllocationByManagerModel->update('bills',$dataBills, $billId);
            if($this->db->affected_rows()>0){
                echo "Record Updated";
            }else{
                echo "Record not Updated";
            }
        }
    }

    public function updateSRCreditAdj() {
        $data['msg']='';   
        
        $id = $this->input->post('id');
        $billId = $this->input->post('billId');
        $userId = ($this->session->userdata['logged_in']['id']);

        $creditBillCheck=$this->AllocationByManagerModel->load('bills',$billId);
        $creditAdjAmount=$creditBillCheck[0]['creditAdjustment'];
        $netAmountAdj=$creditBillCheck[0]['netAmount'];
        $pendingAmounFix = $creditBillCheck[0]['pendingAmt'];
        $pendingAmtNow=$creditBillCheck[0]['pendingAmt'];

        $billDetailInfo=$this->AllocationByManagerModel->loadBillDetails('billsdetails', $billId);
        $prodCode="";
        if(!empty($billDetailInfo)){
            $prodCode=$billDetailInfo[0]['productCode'];
        }
        // echo $prodCode;exit;
        $returnedQty = $this->input->post('returnedQty');
            // print_r($returnedQty);exit;

        if($creditAdjAmount > 0){
            
            $name=$this->input->post('productName');
            $mrp = $this->input->post('mrp');
            $qty = $this->input->post('qty');

            $status="";

            $netAmount = $this->input->post('netAmount');
            $sellingRate = $this->input->post('selAmount');

            $returnAmt = $this->input->post('returnAmt');
            $srTotalFs=0;
            $fsSrBillAmt=0;

            $srTotalFsFix=0;
            $fsSrBillAmtFix=0;
            $calAmtFix=0;

            $calAmt=0;

            $sumQty=$this->AllocationByManagerModel->getSum('billsdetails',$billId);
            $actualQty=$sumQty[0]['qtySum'];

            $srQty=(array_sum($returnedQty));
            $actualQty=($actualQty);

            $status='SR';
            $srQty=(int)$srQty;
            $actualQty=(int)$actualQty;
           
            if($srQty<=$actualQty){
                for ($i=0; $i < count($returnedQty); $i++) {
                    
                    if(!empty($returnedQty[$i]) || $returnedQty[$i] != 0.00){
                        if(($returnedQty[$i] <= $qty[$i]) && ($qty[$i] >0)){
                            $calAmtFix=$netAmount[$i]/$qty[$i];
                            $retAmount=0;
                            if(trim($returnAmt[$i]) !==""){
                                $retAmount=$returnAmt[$i];
                            }
                            $ReturnAmount=$retAmount+($calAmtFix * $returnedQty[$i]);
                            $data['billsdetails']=$this->AllocationByManagerModel->loadBillDetailsID('billsdetails', $id[$i]);
                            
                            $data['bills']=$this->AllocationByManagerModel->load('bills', $billId);
                            // $fsSrBillAmtFix=0;
                            $oldSR=0+$returnedQty[$i];
                            $srTotalFsFix=$srTotalFs+$ReturnAmount;
                            $fsSrBillAmtFix=$fsSrBillAmtFix+$srTotalFsFix;
                            $pendingAmounFix=$pendingAmounFix+$data['billsdetails'][0]['fsReturnAmt'];
                        }
                    }
                }
                $onlyPending=$pendingAmounFix;
                $pendingWithAdjustment=$pendingAmounFix+$creditAdjAmount;
                $creditNoteAmt=($fsSrBillAmtFix-$onlyPending);

                $fsSrBillAmtFix=($fsSrBillAmtFix);
                $pendingAmounFix=($pendingAmounFix);
                $pendingAmtNow=($pendingAmtNow);

                // echo $fsSrBillAmtFix.' '.$pendingAmounFix.' '.$pendingAmtNow.' '.$creditNoteAmt;exit;
                
                if((int)$pendingAmounFix<=(int)$fsSrBillAmtFix){
                    if((int)$creditNoteAmt>(int)($creditAdjAmount)){
                        echo "SR Amount is greater than pending amount.";
                        // $this->session->set_flashdata('item', array('message' => 'SR Amount is greater than pending amount.','class' => 'success'));
                        // redirect('AdHocController/billSearch');
                        exit;
                    }

                    if($creditNoteAmt >0){
                    // if(($fsSrBillAmtFix)>($pendingAmtNow)){
                        echo "Credit Adjustment Bill. Sale Return can not be more than pending amount.";
                        exit;
                    }
                    

                    for ($i=0; $i < count($returnedQty); $i++) {
                       
                        if(($returnedQty[$i] <= $qty[$i]) && ($qty[$i] > 0)){
                            $calAmt=$netAmount[$i]/$qty[$i];
                            $retAmount=0;
                            if(trim($returnAmt[$i]) !==""){
                                $retAmount=$returnAmt[$i];
                            }
                            $ReturnAmount=$retAmount+($calAmt * $returnedQty[$i]);
                            $data['billsdetails']=$this->AllocationByManagerModel->loadBillDetailsID('billsdetails', $id[$i]);
                            
                            $data['bills']=$this->AllocationByManagerModel->load('bills', $billId);
                            $fsSrBillAmt=$data['bills'][0]['fsSrAmt'];
                            $oldSR=0+$returnedQty[$i];
                            $srTotalFs=$srTotalFs+$ReturnAmount;

                            $fsSrBillAmt=($srTotalFs);



                            $oldSR= $oldSR;
                            $ReturnAmount= $ReturnAmount;

                            // if($qty[$i] >= $oldSR){
                            if($returnedQty[$i] > 0){
                                $data = array(
                                    'gkReturnQty' => ($oldSR+$data['billsdetails'][0]['gkReturnQty']),
                                    'fsReturnAmt' => ($ReturnAmount+$data['billsdetails'][0]['fsReturnAmt'])
                                ); 
                                
                                $this->AllocationByManagerModel->update('billsdetails',$data,$id[$i]);

                                $srData = array(
                                    'empId'=>$userId,
                                    'billId' =>  $billId,
                                    'billItemId'=>$id[$i],
                                    'quantity'=>$oldSR,
                                    'createdAt'=>date('Y-m-d H:i:sa')
                                );
                                $this->AllocationByManagerModel->insert('allocation_sr_details',$srData);

                                //for product return with code for delivery slip products
                                $prodDet=$this->AllocationByManagerModel->getDetailByCode('products', $prodCode);
                                if(!empty($prodDet)){
                                    $insertData=array(
                                        'billingId'=>$billId,
                                        'operationStatus'=>'add',
                                        'quantityInPcs'=>$returnedQty[$i],
                                        'productId'=>$prodDet[0]['id'],
                                        'productName'=>$prodDet[0]['name'],
                                        'productCode'=>$prodDet[0]['productCode'],
                                        'quantity'=>$returnedQty[$i],
                                        'quantityUnit'=>'pcs',
                                        'createdBy'=>$this->session->userdata['logged_in']['id'],
                                        'createdAt'=>date('Y-m-d H:i:sa')
                                    );
                                    // print_r($insertData);exit;
                                    $this->AllocationByManagerModel->insert('deliveryslip_pending_for_billing',$insertData);
                                }
                            }
                            // }
                        }
                    }

                    $data['bills']=$this->AllocationByManagerModel->loadBillsDetails('bills', $billId);
                    $OlderSrAmt=$data['bills'][0]['SRAmt'];
                    $OlderPendingAmt=$data['bills'][0]['pendingAmt'];

                    $latestSrAmt=0;
                    $latestPendingAmt=0;

                   
                    $fixTotal=$fsSrBillAmt;
                    $fsSrBillAmt=round($fsSrBillAmt);

                    if($fsSrBillAmt>$pendingAmtNow){
                        $fsSrBillAmt=floor($fsSrBillAmt);
                    }else{
                        $fsSrBillAmt=round($fsSrBillAmt);
                    }
                    $latestSrAmt=$OlderSrAmt+$fsSrBillAmt;
                    $latestPendingAmt=$OlderPendingAmt-$fsSrBillAmt;

                    if($fsSrBillAmt>0){
                        $data = array('SRAmt' => $latestSrAmt,'pendingAmt'=>$latestPendingAmt,'creditNoteRenewal'=>$creditNoteAmt);  
                        $this->AllocationByManagerModel->update('bills',$data, $billId);
                    }
                }else{

                    $fsSrBillAmtFix=($fsSrBillAmtFix);
                    $pendingAmtNow=($pendingAmtNow);

                    $creditNoteAmt=($creditNoteAmt);
                    $creditAdjAmount=($creditAdjAmount);

                    
                    if((int)$creditNoteAmt>(int)($creditAdjAmount)){
                        echo "SR Amount is greater than pending amount.";
                        // $this->session->set_flashdata('item', array('message' => 'SR Amount is greater than pending amount.','class' => 'success'));
                        // redirect('AdHocController/billSearch');
                        exit;
                    }

                    // if(($fsSrBillAmtFix)>($pendingAmtNow)){
                    if($creditNoteAmt>0){
                        echo "Credit Adjustment Bill. Sale Return can not be more than pending amount.";
                        exit;
                    }

                    for ($i=0; $i < count($returnedQty); $i++) {
                        
                        if(($returnedQty[$i] <= $qty[$i]) && ($qty[$i] > 0)){
                            $calAmt=$netAmount[$i]/$qty[$i];
                            $retAmount=0;
                            if(trim($returnAmt[$i]) !==""){
                                $retAmount=$returnAmt[$i];
                            }
                            $ReturnAmount=$retAmount+($calAmt * $returnedQty[$i]);
                            $data['billsdetails']=$this->AllocationByManagerModel->loadBillDetailsID('billsdetails', $id[$i]);
                            
                            $data['bills']=$this->AllocationByManagerModel->load('bills', $billId);
                            $fsSrBillAmt=$data['bills'][0]['fsSrAmt'];
                            $oldSR=0+$returnedQty[$i];
                            $srTotalFs=$srTotalFs+$ReturnAmount;

                            $fsSrBillAmt=$srTotalFs;
                            
                            $oldSR= $oldSR;
                            $ReturnAmount= $ReturnAmount;

                            // if($qty[$i] >= $oldSR){
                                if($returnedQty[$i] > 0){
                                    $data = array(
                                        'gkReturnQty' => ($oldSR+$data['billsdetails'][0]['gkReturnQty']),
                                        'fsReturnAmt' => ($ReturnAmount+$data['billsdetails'][0]['fsReturnAmt'])
                                    ); 
                                    
                                    $this->AllocationByManagerModel->update('billsdetails',$data,$id[$i]);

                                     $srData = array(
                                        'empId'=>$userId,
                                        'billId' =>  $billId,
                                        'billItemId'=>$id[$i],
                                        'quantity'=>$oldSR,
                                        'createdAt'=>date('Y-m-d H:i:sa')
                                    );
                                    $this->AllocationByManagerModel->insert('allocation_sr_details',$srData);

                                    //for product return with code for delivery slip products
                                    $prodDet=$this->AllocationByManagerModel->getDetailByCode('products', $prodCode);
                                    if(!empty($prodDet)){
                                        $insertData=array(
                                            'billingId'=>$billId,
                                            'operationStatus'=>'add',
                                            'quantityInPcs'=>$returnedQty[$i],
                                            'productId'=>$prodDet[0]['id'],
                                            'productName'=>$prodDet[0]['name'],
                                            'productCode'=>$prodDet[0]['productCode'],
                                            'quantity'=>$returnedQty[$i],
                                            'quantityUnit'=>'pcs',
                                            'createdBy'=>$this->session->userdata['logged_in']['id'],
                                            'createdAt'=>date('Y-m-d H:i:sa')
                                        );
                                        // print_r($insertData);exit;
                                        $this->AllocationByManagerModel->insert('deliveryslip_pending_for_billing',$insertData);
                                    }
                                }
                            // }
                        }
                    }

                    $data['bills']=$this->AllocationByManagerModel->loadBillsDetails('bills', $billId);
                    $OlderSrAmt=$data['bills'][0]['SRAmt'];
                    $OlderPendingAmt=$data['bills'][0]['pendingAmt'];

                    $latestSrAmt=0;
                    $latestPendingAmt=0;
                   
                    $fixTotal=$fsSrBillAmt;
                    $fsSrBillAmt=($fsSrBillAmt);

                    if($fsSrBillAmt>$pendingAmtNow){
                        $fsSrBillAmt=floor($fsSrBillAmt);
                    }else{
                        $fsSrBillAmt=round($fsSrBillAmt);
                    }

                    $latestSrAmt=$OlderSrAmt+$fsSrBillAmt;
                    $latestPendingAmt=$OlderPendingAmt-$fsSrBillAmt;

                    $creditNoteAmt=0;
                    if(($fsSrBillAmt)>($onlyPending)){
                        $creditNoteAmt=(($fsSrBillAmt)-($onlyPending));
                    }

                    if($fsSrBillAmt>0){
                        $data = array('SRAmt' => $latestSrAmt,'pendingAmt'=>$latestPendingAmt,'creditNoteRenewal'=>$creditNoteAmt);  
                        $this->AllocationByManagerModel->update('bills',$data, $billId);
                    }
                }
            }
        }else{
            $name=$this->input->post('productName');
            $mrp = $this->input->post('mrp');
            $qty = $this->input->post('qty');

            $status="";

            $netAmount = $this->input->post('netAmount');
            $sellingRate = $this->input->post('selAmount');
            $returnedQty = $this->input->post('returnedQty');
            $returnAmt = $this->input->post('returnAmt');
            // print_r($returnAmt);
            $srTotalFs=0;
            $fsSrBillAmt=0;

            $srTotalFsFix=0;
            $fsSrBillAmtFix=0;
            $calAmtFix=0;

            $calAmt=0;

            $sumQty=$this->AllocationByManagerModel->getSum('billsdetails',$billId);
            $actualQty=$sumQty[0]['qtySum'];

            $srQty=(array_sum($returnedQty));
            $actualQty=($actualQty);

            $status='SR';
            $srQty=(int)$srQty;
            $actualQty=(int)$actualQty;

            if($srQty<=$actualQty){

                for ($i=0; $i < count($returnedQty); $i++) {
                    if(!empty($returnedQty[$i]) || $returnedQty[$i] != 0.00){

                        if($returnedQty[$i] <= $qty[$i]){
                            $calAmtFix=$netAmount[$i]/$qty[$i];
                            $retAmount=0;
                            if(trim($returnAmt[$i]) !==""){
                                $retAmount=$returnAmt[$i];
                            }
                            $ReturnAmount=$retAmount+($calAmtFix * $returnedQty[$i]);
                            // echo $ReturnAmount. ' '.$calAmtFix.' '.$returnedQty[$i].' '.$returnAmt[$i];

                            $data['billsdetails']=$this->AllocationByManagerModel->loadBillDetailsID('billsdetails', $id[$i]);
                            
                            $data['bills']=$this->AllocationByManagerModel->load('bills', $billId);
                            // $fsSrBillAmtFix=$data['bills'][0]['fsSrAmt'];
                            $oldSR=0+$returnedQty[$i];
                            $srTotalFsFix=$srTotalFs+$ReturnAmount;
                            $fsSrBillAmtFix=$fsSrBillAmtFix+$srTotalFsFix;
                            $pendingAmounFix=$pendingAmounFix+$data['billsdetails'][0]['fsReturnAmt'];
                        }
                    }
                }
                
                $fsSrBillAmtFix=($fsSrBillAmtFix);
                $pendingAmtNow=($pendingAmtNow);

                if((int)$fsSrBillAmtFix>(int)$pendingAmtNow){
                     echo "SR Amount is greater than pending amount.";
                    exit;
                }

                if((int)$fsSrBillAmtFix>(int)$pendingAmtNow){
                    echo "Credit Adjustment Bill. Sale Return can not be more than pending amount.";
                    exit;
                }
                $srTotalFs=0;
                for ($i=0; $i < count($returnedQty); $i++) {
                    if($returnedQty[$i] <= $qty[$i]){

                        $calAmt=$netAmount[$i]/$qty[$i];
                        $retAmount=0;
                        if(trim($returnAmt[$i]) !==""){
                            $retAmount=$returnAmt[$i];
                        }
                        $ReturnAmount=$retAmount+($calAmt * $returnedQty[$i]);
                        $data['billsdetails']=$this->AllocationByManagerModel->loadBillDetailsID('billsdetails', $id[$i]);
                        
                        $data['bills']=$this->AllocationByManagerModel->load('bills', $billId);
                        $fsSrBillAmt=$data['bills'][0]['fsSrAmt'];
                        $oldSR=0+$returnedQty[$i];
                        $srTotalFs=$srTotalFs+$ReturnAmount;

                        $fsSrBillAmt=$srTotalFs;
                        
                        $oldSR= $oldSR;
                        $ReturnAmount= $ReturnAmount;


                        // if($qty[$i] >= $oldSR){
                            if($returnedQty[$i] > 0){
                                 // echo "Hettt";exit;
                                 $data = array(
                                    'gkReturnQty' => ($oldSR+$data['billsdetails'][0]['gkReturnQty']),
                                    'fsReturnAmt' => ($ReturnAmount+$data['billsdetails'][0]['fsReturnAmt'])
                                ); 
                                $this->AllocationByManagerModel->update('billsdetails',$data,$id[$i]);

                                $srData = array(
                                    'empId'=>$userId,
                                    'billId' =>  $billId,
                                    'billItemId'=>$id[$i],
                                    'quantity'=>$oldSR,
                                    'createdAt'=>date('Y-m-d H:i:sa')
                                );
                                $this->AllocationByManagerModel->insert('allocation_sr_details',$srData);

                                //for product return with code for delivery slip products
                                $prodDet=$this->AllocationByManagerModel->getDetailByCode('products', $prodCode);
                                if(!empty($prodDet)){
                                    $insertData=array(
                                        'billingId'=>$billId,
                                        'operationStatus'=>'add',
                                        'quantityInPcs'=>$returnedQty[$i],
                                        'productId'=>$prodDet[0]['id'],
                                        'productName'=>$prodDet[0]['name'],
                                        'productCode'=>$prodDet[0]['productCode'],
                                        'quantity'=>$returnedQty[$i],
                                        'quantityUnit'=>'pcs',
                                        'createdBy'=>$this->session->userdata['logged_in']['id'],
                                        'createdAt'=>date('Y-m-d H:i:sa')
                                    );
                                    // print_r($insertData);exit;
                                    $this->AllocationByManagerModel->insert('deliveryslip_pending_for_billing',$insertData);
                                }
                            }
                        // }
                    }
                }

                $data['bills']=$this->AllocationByManagerModel->loadBillsDetails('bills', $billId);
                $OlderSrAmt=$data['bills'][0]['SRAmt'];
                $OlderPendingAmt=$data['bills'][0]['pendingAmt'];

                $latestSrAmt=0;
                $latestPendingAmt=0;
               
                $fixTotal=$fsSrBillAmt;
                $fsSrBillAmt=($fsSrBillAmt);

                if($fsSrBillAmt>$pendingAmtNow){
                    $fsSrBillAmt=floor($fsSrBillAmt);
                }else{
                    $fsSrBillAmt=round($fsSrBillAmt);
                }

                $latestSrAmt=$OlderSrAmt+$fsSrBillAmt;
                $latestPendingAmt=$OlderPendingAmt-$fsSrBillAmt;

                if($fsSrBillAmt>0){
                    $data = array('SRAmt' => $latestSrAmt,'pendingAmt'=>$latestPendingAmt);  
                    $this->AllocationByManagerModel->update('bills',$data, $billId);
                }
            }
            
        }
    }

    public function addDeliverySlipBillToAllocation(){
        $billId=$this->input->post('billId');
        $allocationId=trim($this->input->post('allocationId'));
        // $currentPendingAmt=trim($this->input->post('currentPendingAmt'));

        $allocationTotalAmount=0;
        $allocationBillCount=0;
        $allocationSalesman="";
        $code="";

        if(!empty($billId)){
            foreach($billId as $id){
                $allocationDetail=$this->AllocationByManagerModel->load('allocations',$allocationId);
                $code=trim($allocationDetail[0]['allocationCode']);
                if(!empty($id)){
                    $billInformation=$this->AllocationByManagerModel->getUnallocatedBillById('bills',$id);
                    if(!empty($billInformation)){
                        $allocationSalesman=$allocationDetail[0]['allocationSalesman'].','.$billInformation[0]['salesman'];
                        $allocationTotalAmount=$allocationDetail[0]['allocationTotalAmount']+$billInformation[0]['pendingAmt'];
                        $allocationBillCount=1+$allocationDetail[0]['allocationBillCount'];
                    }       
                }

                $currentBillId=$id;
                $billInfo=$this->AllocationByManagerModel->load('bills',$currentBillId);

                if($billInfo[0]['netAmount']==$billInfo[0]['pendingAmt']){
                    if(!empty($allocationId)){
                        $allocationDetails=$this->AllocationByManagerModel->load('allocations',$allocationId);
                        if(!empty($allocationDetails)){
                            $insAllocationDetail=array('billId'=>$currentBillId,'allocationId'=>$allocationId,'billStatus'=>1);
                            $this->AllocationByManagerModel->insert('allocationsbills',$insAllocationDetail);
                            if($this->db->affected_rows()>0){
                                $updBills=array('billType'=>'allocatedbillCurrent','isAllocated'=>1,'deliveryslipOwnerApproval'=>'1','isLostBill'=>'0');
                                $this->AllocationByManagerModel->update('bills',$updBills,$currentBillId);
                            }

                            $updateAllocationDetails=array(
                                "allocationTotalAmount"=>$allocationTotalAmount,
                                "allocationBillCount"=>$allocationBillCount,
                                "allocationSalesman"=>$allocationSalesman
                            );
                            $this->AllocationByManagerModel->update('allocations',$updateAllocationDetails,$allocationId);

                            $history=array(
                                'billId'=>$currentBillId,
                                'allocationId'=>$allocationId,
                                'transactionStatus' =>'Added to Allocation',
                                'transactionDate'=>date('Y-m-d H:i:sa'),
                                'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                            );
                            $this->AllocationByManagerModel->insert('bill_transaction_history',$history);
                        }
                    }
                }else{
                    if(!empty($allocationId)){
                        $allocationDetails=$this->AllocationByManagerModel->load('allocations',$allocationId);
                        if(!empty($allocationDetails)){
                            $insAllocationDetail=array('billId'=>$currentBillId,'allocationId'=>$allocationId,'billStatus'=>2);
                            $this->AllocationByManagerModel->insert('allocationsbills',$insAllocationDetail);
                            if($this->db->affected_rows()>0){
                                $updBills=array('billType'=>'allocatedbillPass','isAllocated'=>1,'deliveryslipOwnerApproval'=>'1','isLostBill'=>'0');
                                $this->AllocationByManagerModel->update('bills',$updBills,$currentBillId);
                            }

                            $updateAllocationDetails=array(
                                "allocationTotalAmount"=>$allocationTotalAmount,
                                "allocationBillCount"=>$allocationBillCount,
                                "allocationSalesman"=>$allocationSalesman
                            );
                            $this->AllocationByManagerModel->update('allocations',$updateAllocationDetails,$allocationId);

                            $history=array(
                                'billId'=>$currentBillId,
                                'allocationId'=>$allocationId,
                                'transactionStatus' =>'Added to Allocation',
                                'transactionDate'=>date('Y-m-d H:i:sa'),
                                'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                            );
                            $this->AllocationByManagerModel->insert('bill_transaction_history',$history);
                        }
                    }
                }
            }
            echo "Bills added to Allocation No. : ".$code;
        }
    }

    public function sendCashSMS($name,$mobile,$amount,$date,$company,$billNo){
        $mobile="8446107727";
        $matter="Dear ".$name.", you have deposited Rs ".$amount." cash on ".$date." against ".$company." bill No. ".$billNo." ";
       
        $url="https://api.msg91.com/api/sendhttp.php?authkey=291106AG8eCyzDe5d626f5c&mobiles=".$mobile."&country=91&message=".$matter."&sender=TESTIN&route=4";

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        //curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);
        curl_close($ch);
    }

    public function sendChequeSMS($name,$mobile,$amount,$date,$company,$billNo){
        $mobile="8446107727";
        $matter="Dear ".$name.", you have deposited cheque of Rs ".$amount." on ".$date." against ".$company." bill No. ".$billNo." ";
       
        $url="https://api.msg91.com/api/sendhttp.php?authkey=291106AG8eCyzDe5d626f5c&mobiles=".$mobile."&country=91&message=".$matter."&sender=TESTIN&route=4";

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        //curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);
        curl_close($ch);
    }

    public function sendEmployeeDebitSMS($name,$mobile,$amount,$date,$company,$billNo,$remark){
        $mobile="8446107727";
        $matter="Dear ".$name.", you have been debited Rs ".$amount." on ".$date." against bill No. ".$billNo.". Remarks - ".$remark.'. Your current balance  is Rs. ';
       
        $url="https://api.msg91.com/api/sendhttp.php?authkey=291106AG8eCyzDe5d626f5c&mobiles=".$mobile."&country=91&message=".$matter."&sender=TESTIN&route=4";

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        //curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);
        curl_close($ch);
    }

    //create new allocation by deliveryman
    public function createNewAllocation(){
        $userId=$this->session->userdata['logged_in']['id'];
        $billArray=$this->input->post('billId');
        $empId=$this->input->post('empId');
        $routeId=$this->input->post('routeId');

        $allocationTotalAmount=0;
        $allocationBillCount=0;
        $allocationEmployeeName="";
        $allocationSalesman="";
        // $allocationRouteName="";

        $route=$this->AllocationByManagerModel->load('route',$routeId);
        $companyName="";
        if(!empty($billArray)){
            foreach($billArray as $bill){
                $bills=$this->AllocationByManagerModel->load('bills',$bill);
                if(!empty($bills)){
                    if(strpos($companyName, $bills[0]['compName']) == false){
                        $companyName=$companyName.','.$bills[0]['compName'];
                    }
                    // $company=$bills[0]['compName'].', '.$company;
                }

                $billInformation=$this->AllocationByManagerModel->getUnallocatedBillById('bills',$bill);
                if(!empty($billInformation)){
                    $allocationSalesman=$billInformation[0]['salesman'].','.$allocationSalesman;
                    $allocationTotalAmount=$allocationTotalAmount+$billInformation[0]['pendingAmt'];
                    $allocationBillCount++;
                }   
            }
        }

        $salesman = explode(",", $allocationSalesman);
        $salesman=array_unique($salesman,SORT_REGULAR);
        $newSalesman="";
        foreach($salesman as $name){
            $newSalesman=$name.','.$newSalesman;
        }
        $allocationSalesman=$newSalesman;

        if(!empty($empId)){
            $empData=$this->AllocationByManagerModel->load('employee',$empId);
            $allocationEmployeeName=$empData[0]['name'];
        }
        
        $companyName=trim($companyName,',');
        $allocationEmployeeName=trim($allocationEmployeeName,',');
        $allocationSalesman=trim($allocationSalesman,',');

        $allocationCode="";
        $allocationCount=$this->AllocationByManagerModel->getNextId('allocations');
        $nextId =$allocationCount[0]['id']+1;
        $allocationCode= date("dmy").'-'.$nextId;

        $checkAllocationCode=$this->AllocationByManagerModel->checkAllocationCode('allocations',$allocationCode);  
        // print_r($checkAllocationCode);exit;
        if(empty($checkAllocationCode)){
             // 
             if(!empty($billArray)){
                $allocationData=array(
                    'date' => date("Y-m-d H:i:sa"),
                    'company'=>$companyName,
                    'fieldStaffCode1' => $empId,
                    'fieldStaffCode2' => 0,
                    'fieldStaffCode3' => 0,
                    'fieldStaffCode4' => 0,
                    'routId' => $routeId,
                    'routeCode'=>$route[0]['code'],
                    'allocationCreatedBy'=>$userId,
                    'allocationCode' => $allocationCode,
                    'allocationTotalAmount'=>$allocationTotalAmount,
                    'allocationBillCount'=>$allocationBillCount,
                    'allocationEmployeeName'=>$allocationEmployeeName,
                    'allocationSalesman'=>$allocationSalesman,
                    'allocationRouteName'=>$route[0]['name']
                );

                // print_r($allocationData);exit;
                if($this->AllocationByManagerModel->insert('allocations',$allocationData)){ 
                    $lastInsertedId=$this->db->insert_id(); 
                    foreach($billArray as $items){
                        $billUpdateInfo=$this->AllocationByManagerModel->isBillAllocated('bills',$items);
                        if(!empty($billUpdateInfo)){
                            if(($billUpdateInfo[0]['billType'] === "")){
                                $allocationBillsData=array(
                                    'billId' => $items,
                                    'allocationId' => $lastInsertedId,
                                    'billStatus'=>'1'
                                );
                                if($this->AllocationByManagerModel->insert('allocationsbills',$allocationBillsData)){
                                    $billData=array(
                                        'route' => $routeId,
                                        'billType' =>'allocatedbillCurrent',
                                        'isLostBill'=>'0',
                                        'isAllocated'=>'1',
                                        'deliveryslipOwnerApproval'=>'1'
                                    );
                                    $this->AllocationByManagerModel->update('bills',$billData,$items);

                                    $history=array(
                                        'billId'=>$items,
                                        'allocationId'=>$lastInsertedId,
                                        'transactionStatus' =>'Create new allocation',
                                        'transactionDate'=>date('Y-m-d H:i:sa'),
                                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                                    );
                                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);
                                    
                                }  
                            }else{
                                $allocationBillsData=array(
                                    'billId' => $items,
                                    'allocationId' => $lastInsertedId,
                                    'billStatus'=>'2',
                                );
                                if($this->AllocationByManagerModel->insert('allocationsbills',$allocationBillsData)){
                                    $billData=array(
                                        'route' => $routeId,
                                        'billType' =>'allocatedbillPass',
                                        'isLostBill'=>'0',
                                        'isAllocated'=>'1',
                                        'deliveryslipOwnerApproval'=>'1'
                                    );
                                    $this->AllocationByManagerModel->update('bills',$billData,$items);

                                    $history=array(
                                        'billId'=>$items,
                                        'allocationId'=>$lastInsertedId,
                                        'transactionStatus' =>'Create new allocation',
                                        'transactionDate'=>date('Y-m-d H:i:sa'),
                                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                                    );
                                    $this->AllocationByManagerModel->insert('bill_transaction_history',$history);
                                    
                                }
                            }
                        }
                    }
                    echo 'Allocation : '.$allocationCode.' is created successfully.';
                }else{
                   echo 'Unable to create new allocation.';
                }
            } else {
                echo 'Bill not available.';
            }
        }else{
            echo 'Allocation already created.';
        }
    }
}

?>