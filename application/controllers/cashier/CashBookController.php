<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CashBookController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('CashBookModel');
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
    }
    public function index()
    {
        $data['employee']=$this->CashBookModel->getdata('employee');
        $data['bills']=$this->CashBookModel->getdata('bills');
        
        $this->load->view('CurrentDayBookView',$data);
    }
    public function PastDayBook()
    {
        $data['company']=$this->CashBookModel->getdata('company');
        $this->load->view('PastDayBookView',$data);
    }
    public function PeroidDayBook()
    {
        $data['company']=$this->CashBookModel->getdata('company');
        $this->load->view('PeroidDayBookView',$data);
    }
    public function EVauchers()
    {
        $this->load->view('cashier/EvoucherView');
    }

    public function IncomeExpense(){
        
        $data['inOut']=$this->CashBookModel->getInflowOutflow('allocations');
        $data['inflowEmp']=$this->CashBookModel->getInflowEmp('expences');
        $data['inflowCategory']=$this->CashBookModel->getInflowCategory('expences');
        
        $openingBalance=$this->CashBookModel->openingRecordValue();
        $closingBalance=$this->CashBookModel->lastRecordValue();
        $bankDep=$this->CashBookModel->sumBankDeposit();
        $income=$this->CashBookModel->sumIncome();
        $exp=$this->CashBookModel->sumExp();

        $data['open']= $openingBalance['openCloseBalance'];
        $data['close']=$closingBalance['openCloseBalance'];
        $data['bankDep']=$bankDep['BankDepSum'];
        $data['income']=$income['income'];
        $data['expense']=$exp['expense'];

        $data['emp']=$this->CashBookModel->getEmployeeNames();
        
        $this->load->view('cashier/IncomeExpenseView',$data);
    }
    
    public function AddIncome(){
        $data['emp']=$this->CashBookModel->getEmployeeNames();
        $this->load->view('cashier/AddIncomeView',$data);
    }

    public function AddExpense(){
        $data['emp']=$this->CashBookModel->getEmployeeNames();
        $this->load->view('cashier/AddExpenseView',$data);
    }

    public function AddBankDeposit(){
        $this->load->view('cashier/AddBankDepositView');
    }

    public function allocationWiseCashBook($id){
        $data['bills']=$this->CashBookModel->getAllocatedBills('bills',$id);

        $data['allocations']=$this->CashBookModel->load('allocations',$id);

        $data["current"]=array();
        $data["bounced"]=array();
        $data["pass"]=array();
        $data["slip"]=array();
        $count=0;
        $total=0;
        foreach ($data['bills'] as $items) {
            if($items['billType']=='allocatedbillCurrent'){
                $data['current']=$this->CashBookModel->getAllocatedBillsByType('bills',$id,$items['billType'],'1');
                
            }else if(($items['billType']==='allocatedbillPass') || ($items['billType']==='adHocDeliveryBill') || ($items['billType']==='officeAdjustmentBill')){
                $data['pass']=$this->CashBookModel->getAllocatedPastBillsByType('bills',$id);
                
            }else if($items['billType']=='allocatedbillDS'){
                $data['slip']=$this->CashBookModel->getAllocatedBillsByType('bills',$id,$items['billType'],'1');
            }else if($items['billType']=='allocatedbillBounce'){
                $data['bounced']=$this->CashBookModel->getAllocatedBillsByType('bills',$id,$items['billType'],'1');
            }
        }

        //Total Allocated Bills
        $count=$count+count($data['current'])+count($data['pass'])+count($data['slip'])+count($data['bounced']);
       
        $cashBillTotal=0;
        $chequeNeftTotal=0; 
        $chequeNeftCount=0;

        //Total Allocated bills Amount Total : 
        for($i=0;$i<count($data['current']);$i++){
            if($data['current'][$i]['fsCashAmt'] != '0.00'){
                $cashBillTotal=$cashBillTotal+$data['current'][$i]['fsCashAmt'];
            }

            if($data['current'][$i]['fsChequeAmt'] != '0.00'){
                $chequeNeftTotal=$chequeNeftTotal+$data['current'][$i]['fsChequeAmt'];
                $chequeNeftCount++;
            }

            if($data['current'][$i]['fsNeftAmt'] != '0.00'){
                $chequeNeftCount++;
            }
        }

        for($i=0;$i<count($data['pass']);$i++){
            if($data['pass'][$i]['fsCashAmt'] != '0.00'){
                $cashBillTotal=$cashBillTotal+$data['pass'][$i]['fsCashAmt'];
            }

            if($data['pass'][$i]['fsChequeAmt'] != '0.00'){
                $chequeNeftTotal=$chequeNeftTotal+$data['pass'][$i]['fsChequeAmt'];
                $chequeNeftCount++;
            }
             if($data['pass'][$i]['fsNeftAmt'] != '0.00'){
                $chequeNeftCount++;
            }
        }

        for($i=0;$i<count($data['slip']);$i++){
            if($data['slip'][$i]['fsCashAmt'] != '0.00'){
                $cashBillTotal=$cashBillTotal+$data['slip'][$i]['fsCashAmt'];
            }

            if($data['slip'][$i]['fsChequeAmt'] != '0.00'){
                $chequeNeftTotal=$chequeNeftTotal+$data['slip'][$i]['fsChequeAmt'];
                $chequeNeftCount++;
            }
            if($data['slip'][$i]['fsNeftAmt'] != '0.00'){
                $chequeNeftCount++;
            }
        }

        for($i=0;$i<count($data['bounced']);$i++){
            if($data['bounced'][$i]['fsCashAmt'] != '0.00'){
                $cashBillTotal=$cashBillTotal+$data['bounced'][$i]['fsCashAmt'];
            }

            if($data['bounced'][$i]['fsChequeAmt'] != '0.00'){
                $chequeNeftTotal=$chequeNeftTotal+$data['bounced'][$i]['fsChequeAmt'];
                $chequeNeftCount++;
            }
            if($data['bounced'][$i]['fsNeftAmt'] != '0.00'){
                $chequeNeftCount++;
            }
        }

        $data['cashTotal']=$cashBillTotal;
        $data['chequeTotal']=$chequeNeftTotal;
        $data['chequeCount']=$chequeNeftCount;
        $data['notes']=$this->CashBookModel->loadByAllocationId('notesdetails',$id);
        $expenses=0.0;
        if(!empty($data['notes'])){
            $n2000=$data['notes'][0]['note2000']*2000;
            // $n1000=$data['notes'][0]['note1000']*1000;        
            $n500=$data['notes'][0]['note500']*500;
            $n200=$data['notes'][0]['note200']*200;
            $n100=$data['notes'][0]['note100']*100;
            $n50=$data['notes'][0]['note50']*50;
            $n20=$data['notes'][0]['note20']*20;
            $n10=$data['notes'][0]['note10']*10;
            $coin=$data['notes'][0]['coins'];
            $total=$n2000+$n500+$n200+$n100+$n50+$n20+$n10+$coin;
            $expenses=$data['notes'][0]['cng']+$data['notes'][0]['challan']+$data['notes'][0]['parking'];
            if($data['notes'][0]['statusParking']==2){
                 $expenses=$expenses-$data['notes'][0]['parking'];
            }
            if($data['notes'][0]['statusCng']==2){
                $expenses=$expenses-$data['notes'][0]['cng'];
            }
            if($data['notes'][0]['statusChallan']==2){
                $expenses=$expenses-$data['notes'][0]['challan'];
            }
           // echo $expenses;exit;
            $data['total']=$total;
            $data['expenses']=$expenses;
        }
        $this->load->view('cashier/cashBookView',$data);
    }

    public function changeStatusRecChequeNeft(){
        $id=trim($this->input->post('id'));
        $amt=trim($this->input->post('amt'));
        $mode=trim($this->input->post('mode'));

        $allocationId=trim($this->input->post('allocationId'));

        $updateDataImp=array('isLostStatus'=>'2');
        $this->CashBookModel->statusUpdateByBillIdWithAllocation('billpayments',$updateDataImp,$id,$amt,$mode,$allocationId);
        
        if($this->db->affected_rows()>0){
            $updateData=array('statusLostChequeNeft'=>1);
            $this->CashBookModel->update('bills',$updateData,$id);
        }
        $this->getlostChequeNeftDetails($allocationId);
    }

    public function changeStatusLostChequeNeft(){
        $id=trim($this->input->post('id'));
        $amt=trim($this->input->post('amt'));
        $mode=trim($this->input->post('mode'));
        $allocationId=trim($this->input->post('allocationId'));

        $updatedAmt=0;
        $bills=$this->CashBookModel->load('bills',$id);

        $pendingAmt=$bills[0]['pendingAmt'];
        $creditAdjustmentAmount=$bills[0]['creditAdjustment'];
        $creditNoteRenewalAmount=$bills[0]['creditNoteRenewal'];

        if($creditAdjustmentAmount > 0){
            if($creditNoteRenewalAmount > $amt){
                $updatedAmt=$creditNoteRenewalAmount-$amt;//substract lost cheque/neft amount from creditNoteRenewal amount

                $updateDataImp=array('isLostStatus'=>'1');
                $this->CashBookModel->statusUpdateByBillIdWithAllocation('billpayments',$updateDataImp,$id,$amt,$mode,$allocationId);
                
                if($this->db->affected_rows()>0){
                    if($mode=='Cheque'){
                        $updateData=array('billCurrentStatus'=>'Lost Cheque','statusLostChequeNeft'=>2,'fsChequeAmt'=>0);
                        $this->CashBookModel->update('bills',$updateData,$id);

                        // $updateBilldetailsData=array('paidAmount'=>0);
                        // $this->CashBookModel->updateAllocationStatus('billpayments',$updateBilldetailsData,$allocationId,$id);

                        $updateAllocationData=array('isLostCheque'=>1);
                        $this->CashBookModel->updateAllocationStatus('allocationsbills',$updateAllocationData,$allocationId,$id);

                        $history=array(
                            'billId'=>$id,
                            'allocationId' => $allocationId,
                            'transactionAmount' =>$amt,
                            'transactionStatus' =>'Lost Cheque',
                            'transactionMode' =>'cr',
                            'transactionDate'=>date('Y-m-d H:i:sa'),
                            'empId'=>trim($this->session->userdata['logged_in']['id']),
                            'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                        );
                        $this->CashBookModel->insert('bill_transaction_history',$history);
                        
                    }else if($mode=="NEFT"){
                        $updateData=array('billCurrentStatus'=>'Pending NEFT','statusLostChequeNeft'=>2,'fsNeftAmt'=>0);
                        $this->CashBookModel->update('bills',$updateData,$id);

                        // $updateBilldetailsData=array('paidAmount'=>0);
                        // $this->CashBookModel->updateAllocationStatus('billpayments',$updateBilldetailsData,$allocationId,$id);

                        $updateAllocationData=array('isPendingNeft'=>1);
                        $this->CashBookModel->updateAllocationStatus('allocationsbills',$updateAllocationData,$allocationId,$id);
                        
                        $history=array(
                            'billId'=>$id,
                            'allocationId' => $allocationId,
                            'transactionAmount' =>$amt,
                            'transactionStatus' =>'Pending NEFT',
                            'transactionMode' =>'cr',
                            'transactionDate'=>date('Y-m-d H:i:sa'),
                            'empId'=>trim($this->session->userdata['logged_in']['id']),
                            'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                        );
                        $this->CashBookModel->insert('bill_transaction_history',$history);
                    }
                }
            }else{
                $updatedAmt=$amt+$pendingAmt;//add lost cheque/neft amount to pending amount

                $updateDataImp=array('isLostStatus'=>'1');
                $this->CashBookModel->statusUpdateByBillIdWithAllocation('billpayments',$updateDataImp,$id,$amt,$mode,$allocationId);
                
                if($this->db->affected_rows()>0){
                    
                    if($mode=='Cheque'){
                        $updateData=array('billCurrentStatus'=>'Lost Cheque','statusLostChequeNeft'=>2,'pendingAmt'=>$updatedAmt,'fsChequeAmt'=>0);
                        $this->CashBookModel->update('bills',$updateData,$id);

                        $updateAllocationData=array('isLostCheque'=>1);
                        $this->CashBookModel->updateAllocationStatus('allocationsbills',$updateAllocationData,$allocationId,$id);

                        $history=array(
                            'billId'=>$id,
                            'allocationId' => $allocationId,
                            'transactionAmount' =>$amt,
                            'transactionStatus' =>'Lost Cheque',
                            'transactionMode' =>'cr',
                            'transactionDate'=>date('Y-m-d H:i:sa'),
                            'empId'=>trim($this->session->userdata['logged_in']['id']),
                            'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                        );
                        $this->CashBookModel->insert('bill_transaction_history',$history);
                       
                    }else if($mode=="NEFT"){
                        $updateData=array('billCurrentStatus'=>'Pending NEFT','statusLostChequeNeft'=>2,'pendingAmt'=>$updatedAmt,'fsNeftAmt'=>0);
                        $this->CashBookModel->update('bills',$updateData,$id);

                        $updateAllocationData=array('isPendingNeft'=>1);
                        $this->CashBookModel->updateAllocationStatus('allocationsbills',$updateAllocationData,$allocationId,$id);
                        
                        $history=array(
                            'billId'=>$id,
                            'allocationId' => $allocationId,
                            'transactionAmount' =>$amt,
                            'transactionStatus' =>'Pending NEFT',
                            'transactionMode' =>'cr',
                            'transactionDate'=>date('Y-m-d H:i:sa'),
                            'empId'=>trim($this->session->userdata['logged_in']['id']),
                            'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                        );
                        $this->CashBookModel->insert('bill_transaction_history',$history);
                        
                    }
                }
            }
        }else{
            $updatedAmt=$amt+$pendingAmt;//add lost cheque/neft amount to pending amount

            $updateDataImp=array('isLostStatus'=>'1');
            $this->CashBookModel->statusUpdateByBillIdWithAllocation('billpayments',$updateDataImp,$id,$amt,$mode,$allocationId);
            
            if($this->db->affected_rows()>0){
                if($mode=='Cheque'){
                    $updateData=array('billCurrentStatus'=>'Lost Cheque','statusLostChequeNeft'=>2,'pendingAmt'=>$updatedAmt,'fsChequeAmt'=>0);
                    $this->CashBookModel->update('bills',$updateData,$id);

                    $updateAllocationData=array('isLostCheque'=>1);
                    $this->CashBookModel->updateAllocationStatus('allocationsbills',$updateAllocationData,$allocationId,$id);

                    $history=array(
                        'billId'=>$id,
                        'allocationId' => $allocationId,
                        'transactionAmount' =>$amt,
                        'transactionStatus' =>'Lost Cheque',
                        'transactionMode' =>'cr',
                        'transactionDate'=>date('Y-m-d H:i:sa'),
                        'empId'=>trim($this->session->userdata['logged_in']['id']),
                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                    );
                    $this->CashBookModel->insert('bill_transaction_history',$history);
                }else if($mode=="NEFT"){
                    $updateData=array('billCurrentStatus'=>'Pending NEFT','statusLostChequeNeft'=>2,'pendingAmt'=>$updatedAmt,'fsNeftAmt'=>0);
                    $this->CashBookModel->update('bills',$updateData,$id);

                    $updateAllocationData=array('isPendingNeft'=>1);
                    $this->CashBookModel->updateAllocationStatus('allocationsbills',$updateAllocationData,$allocationId,$id);

                    $history=array(
                        'billId'=>$id,
                        'allocationId' => $allocationId,
                        'transactionAmount' =>$amt,
                        'transactionStatus' =>'Pending NEFT',
                        'transactionMode' =>'cr',
                        'transactionDate'=>date('Y-m-d H:i:sa'),
                        'empId'=>trim($this->session->userdata['logged_in']['id']),
                        'transactionBy'=>trim($this->session->userdata['logged_in']['id'])
                    );
                    $this->CashBookModel->insert('bill_transaction_history',$history);
                }
            }
        }
        $this->getlostChequeNeftDetails($allocationId);
    }


    public function getlostChequeNeftDetails($id){
        $bills=$this->CashBookModel->getAllocatedBills('bills',$id);
        // $allocations=$this->CashBookModel->load('allocations',$id);

            foreach($bills as $itm){
                $billD=$this->CashBookModel->loadChkBillPaymentDetails('billpayments',$itm['id'],$id,"NEFT");

                if(!empty($billD) && ($billD[0]['paidAmount'] > 0)){
          ?>
                  <tr>  
                    <td><?php echo $itm['billNo']?></td>
                       <td><?php echo $itm['retailerName']?></td>
                      <td><?php echo $billD[0]['paidAmount']?></td>
                      <td><?php echo 'NEFT';?></td>
                      <td>
                        <?php if($billD[0]['isLostStatus'] == 0){ ?>
                      <button style="font-size: 11px" id="neftReceived" onclick="updateChequeNeftRec(this,'<?php echo $itm['id'];?>','<?php echo $itm['fsNeftAmt']?>','NEFT','<?php echo $id;?>');" class="btn btn-primary btn-sm waves-effect">
                                <span class="icon-name">Received</span>
                      </button> 
                      <button style="font-size: 11px" id="neftNotReceived" onclick="updateChequeNeft(this,'<?php echo $itm['id'];?>','<?php echo $itm['fsNeftAmt']?>','NEFT','<?php echo $id;?>')" class="btn btn-primary btn-sm waves-effect">
                          <span class="icon-name">Not Received</span>
                      </button> 
                    <?php }else if($billD[0]['isLostStatus'] == 1){ ?>
                       <i class="material-icons">cancel</i> 
                    <?php } else if($billD[0]['isLostStatus'] == 2){ ?>
                        <i class="material-icons">check</i> 
                    <?php } ?>
                    </td>
                  </tr>
          <?php       }
                  } 

              foreach($bills as $itm){
                $billD=$this->CashBookModel->loadChkBillPaymentDetails('billpayments',$itm['id'],$id,"Cheque");
                  if(!empty($billD) && ($billD[0]['paidAmount'] > 0)){
          ?>
                      <tr>
                        <td><?php echo $itm['billNo']?></td>
                      <td><?php echo $itm['retailerName']?></td>
                       <td><?php echo $billD[0]['paidAmount']; ?></td>
                      <td><?php echo 'Cheque'; ?></td>
                      <td>
                        <?php if($billD[0]['isLostStatus'] == 0){ ?>
                      <button style="font-size: 11px" id="chequeReceived" onclick="updateChequeNeftRec(this,'<?php echo $itm['id'];?>','<?php echo $itm['fsChequeAmt']?>','Cheque','<?php echo $id;?>');" class="btn btn-primary btn-sm waves-effect">
                                <span class="icon-name">Received</span>
                      </button> 
                      <button style="font-size: 11px" id="chequeNotReceived" onclick="updateChequeNeft(this,'<?php echo $itm['id'];?>','<?php echo $itm['fsChequeAmt']?>','Cheque','<?php echo $id;?>')" class="btn btn-primary btn-sm waves-effect">
                          <span class="icon-name">Not Received</span>
                      </button> 
                    <?php }else if($billD[0]['isLostStatus'] == 1){ ?>
                        <i class="material-icons">cancel</i> 
                    <?php } else if($billD[0]['isLostStatus'] == 2){ ?>
                        <i class="material-icons">check</i> 
                    <?php } ?>
                    </td>
                  </tr>
          <?php       }
                  }
    }

    public function getParkingExpenseDetails($notes){
              if(!empty($notes)){
                  if(($notes[0]['statusParking'] != 1 && $notes[0]['statusParking'] != 2)){
               ?>

                <tr>
                    <td>Parking</td>
                    <td>
                      <input style="height:25px;width: 50%" onChange="return expVal()"  onkeypress="return isNumber(event);" type="text" id="prk" name="prk" value="<?php if(!empty($notes)){echo ($notes[0]['parking']*1);} ?>">
                      </td>
                    <td>
                      <?php if(!empty($notes)){ ?>
                        <button style="font-size: 11px" id="parkingReceive" onclick="updateParkingAllow('parking','<?php echo $notes[0]['id'];?>','<?php echo $notes[0]['allocationId'];?>');" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Allow</span>
                        </button> 
                        <button style="font-size: 11px" id="parkingNotReceive" onclick="updateParkingDisAllow('parking','<?php echo $notes[0]['id'];?>','<?php echo $notes[0]['allocationId'];?>');notReceived('parking','<?php echo $notes[0]['parking'];?>');" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Disallow</span>
                        </button> 
                        <?php }else{?>
                          <button style="font-size: 11px" id="parkingReceive" onclick="removeMe(this);" disabled="" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Allow</span>
                        </button> 
                        <button style="font-size: 11px" id="parkingNotReceive" onclick="removeMe(this);" disabled="" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Disallow</span>
                        </button>
                        <?php } ?>
                    </td>
                </tr>
            <?php    
                  }else if(($notes[0]['statusParking'] == 1 || $notes[0]['statusParking'] == 2)){
            ?>
                  <tr>
                    <td>Parking</td>
                    <td>
                      <span><?php if(!empty($notes)){echo ($notes[0]['parking']*1);} ?></span>
                      
                      </td>
                    <td>
                      
                    </td>
                </tr>

            <?php
                  }
                } 

                if(!empty($notes)){
                  if(($notes[0]['statusCng'] != 1 && $notes[0]['statusCng'] != 2)){
            ?>
                <tr>
                    <td>CNG</td>
                     <td>
                      <input style="height:25px;width: 50%" onkeypress="return isNumber(event);" onChange="return expVal()" type="text" id="cngValAmt" name="cngValAmt" value="<?php if(!empty($notes)){echo ($notes[0]['cng']*1);} ?>">
                      </td>
                     <td>
                       <?php if(!empty($notes)){ ?>
                        <button style="font-size: 11px" id="cngReceived" onclick="updateCngAllow('cng','<?php echo $notes[0]['id'];?>','<?php echo $notes[0]['allocationId'];?>');" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Allow</span>
                        </button> 
                        <button style="font-size: 11px" id="cngNotReceived" onclick="updateCngDisAllow('cng','<?php echo $notes[0]['id'];?>','<?php echo $notes[0]['allocationId'];?>');notReceived('cng','<?php echo $notes[0]['cng'];?>');" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Disallow</span>
                        </button> 
                         <?php }else{?>
                             <button style="font-size: 11px" id="cngReceived" onclick="removeMe(this);" disabled="" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Allow</span>
                          </button> 
                          <button style="font-size: 11px" id="cngNotReceived" onclick="removeMe(this);" disabled="" class="btn btn-primary btn-sm waves-effect">
                              <span class="icon-name">Disallow</span>
                          </button> 
                          <?php } ?>
                    </td>
                </tr>
            <?php
                  }else if(($notes[0]['statusCng'] == 1 || $notes[0]['statusCng'] == 2)){
            ?>
                <tr>
                    <td>CNG</td>
                     <td>
                      <span><?php if(!empty($notes)){echo ($notes[0]['cng']*1);} ?></span>
                      </td>
                     <td>
                       
                    </td>
                </tr>

            <?php        
                  }
                }  
              if(!empty($notes)){
                  if(($notes[0]['statusChallan'] !=1 && $notes[0]['statusChallan'] != 2)){
            ?>    
                <tr>
                    <td>Challan</td>
                    <td>
                      <input style="height:25px;width: 50%" onChange="return expVal()" onkeypress="return isNumber(event);" type="text" id="clnValAmt" name="clnValAmt" value="<?php if(!empty($notes)){echo ($notes[0]['challan']*1);} ?>">
                      </td>
                     <td>
                      <?php if(!empty($notes)){ ?>
                        <button style="font-size: 11px" id="challanReceive" onclick="updateChallanAllow('challan','<?php echo $notes[0]['id'];?>','<?php echo $notes[0]['allocationId'];?>');" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Allow</span>
                        </button> 
                        <button style="font-size: 11px" id="challanNotReceive" onclick="updateChallanDisAllow('challan','<?php echo $notes[0]['id'];?>','<?php echo $notes[0]['allocationId'];?>');notReceived('challan','<?php echo $notes[0]['challan'];?>');" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Disallow</span>
                        </button> 
                         <?php }else{?>
                            <button style="font-size: 11px" id="challanReceive" onclick="removeMe(this);" disabled="" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Allow</span>
                        </button> 
                        <button style="font-size: 11px" id="challanNotReceive" onclick="removeMe(this);" disabled="" class="btn btn-primary btn-sm waves-effect">
                            <span class="icon-name">Disallow</span>
                        </button> 
                         <?php } ?>
                    </td>
                </tr>
          <?php
                }else if(($notes[0]['statusChallan'] ==1 || $notes[0]['statusChallan'] == 2)){
          ?>
                  <td>Challan</td>
                    <td>
                     <span><?php if(!empty($notes)){echo ($notes[0]['challan']*1);} ?></span>
                      </td>
                     <td>
                      
                    </td>
          <?php

                }
              }  
    }


    public function getRefreshDetails($id){
        $data['bills']=$this->CashBookModel->getAllocatedBills('bills',$id);

        $data['allocations']=$this->CashBookModel->load('allocations',$id);

        $data["current"]=array();
        $data["bounced"]=array();
        $data["pass"]=array();
        $data["slip"]=array();
        $count=0;
        $total=0;
        foreach ($data['bills'] as $items) {
            if($items['billType']=='allocatedbillCurrent'){
                $data['current']=$this->CashBookModel->getAllocatedBillsByType('bills',$id,$items['billType'],'1');
                
            }else if(($items['billType']==='allocatedbillPass') || ($items['billType']==='adHocDeliveryBill') || ($items['billType']==='officeAdjustmentBill')){
                $data['pass']=$this->CashBookModel->getAllocatedPastBillsByType('bills',$id);
                
            }else if($items['billType']=='allocatedbillDS'){
                $data['slip']=$this->CashBookModel->getAllocatedBillsByType('bills',$id,$items['billType'],'1');
            }else if($items['billType']=='allocatedbillBounce'){
                $data['bounced']=$this->CashBookModel->getAllocatedBillsByType('bills',$id,$items['billType'],'1');
            }
        }

        //Total Allocated Bills
        $count=$count+count($data['current'])+count($data['pass'])+count($data['slip'])+count($data['bounced']);
       
        $cashBillTotal=0;
        $chequeNeftTotal=0; 
        $chequeNeftCount=0;

        //Total Allocated bills Amount Total : 
        for($i=0;$i<count($data['current']);$i++){
            if($data['current'][$i]['fsCashAmt'] != '0.00'){
                $cashBillTotal=$cashBillTotal+$data['current'][$i]['fsCashAmt'];
            }

            if($data['current'][$i]['fsChequeAmt'] != '0.00'){
                $chequeNeftTotal=$chequeNeftTotal+$data['current'][$i]['fsChequeAmt'];
                $chequeNeftCount++;
            }

            if($data['current'][$i]['fsNeftAmt'] != '0.00'){
                $chequeNeftCount++;
            }
        }

        for($i=0;$i<count($data['pass']);$i++){
            if($data['pass'][$i]['fsCashAmt'] != '0.00'){
                $cashBillTotal=$cashBillTotal+$data['pass'][$i]['fsCashAmt'];
            }

            if($data['pass'][$i]['fsChequeAmt'] != '0.00'){
                $chequeNeftTotal=$chequeNeftTotal+$data['pass'][$i]['fsChequeAmt'];
                $chequeNeftCount++;
            }
             if($data['pass'][$i]['fsNeftAmt'] != '0.00'){
                $chequeNeftCount++;
            }
        }

        for($i=0;$i<count($data['slip']);$i++){
            if($data['slip'][$i]['fsCashAmt'] != '0.00'){
                $cashBillTotal=$cashBillTotal+$data['slip'][$i]['fsCashAmt'];
            }

            if($data['slip'][$i]['fsChequeAmt'] != '0.00'){
                $chequeNeftTotal=$chequeNeftTotal+$data['slip'][$i]['fsChequeAmt'];
                $chequeNeftCount++;
            }
            if($data['slip'][$i]['fsNeftAmt'] != '0.00'){
                $chequeNeftCount++;
            }
        }

        for($i=0;$i<count($data['bounced']);$i++){
            if($data['bounced'][$i]['fsCashAmt'] != '0.00'){
                $cashBillTotal=$cashBillTotal+$data['bounced'][$i]['fsCashAmt'];
            }

            if($data['bounced'][$i]['fsChequeAmt'] != '0.00'){
                $chequeNeftTotal=$chequeNeftTotal+$data['bounced'][$i]['fsChequeAmt'];
                $chequeNeftCount++;
            }
            if($data['bounced'][$i]['fsNeftAmt'] != '0.00'){
                $chequeNeftCount++;
            }
        }

        $data['cashTotal']=$cashBillTotal;
        $data['chequeTotal']=$chequeNeftTotal;
        $data['chequeCount']=$chequeNeftCount;
        $data['notes']=$this->CashBookModel->loadByAllocationId('notesdetails',$id);
       $expenses=0.0;
        if(!empty($data['notes'])){
            $n2000=$data['notes'][0]['note2000']*2000;
            // $n1000=$data['notes'][0]['note1000']*1000;        
            $n500=$data['notes'][0]['note500']*500;
            $n200=$data['notes'][0]['note200']*200;
            $n100=$data['notes'][0]['note100']*100;
            $n50=$data['notes'][0]['note50']*50;
            $n20=$data['notes'][0]['note20']*20;
            $n10=$data['notes'][0]['note10']*10;
            $coin=$data['notes'][0]['coins'];
            $total=$n2000+$n500+$n200+$n100+$n50+$n20+$n10+$coin;
            $expenses=$data['notes'][0]['cng']+$data['notes'][0]['challan']+$data['notes'][0]['parking'];
            if($data['notes'][0]['statusParking']==2){
                 $expenses=$expenses-$data['notes'][0]['parking'];
            }
            if($data['notes'][0]['statusCng']==2){
                $expenses=$expenses-$data['notes'][0]['cng'];
            }
            if($data['notes'][0]['statusChallan']==2){
                $expenses=$expenses-$data['notes'][0]['challan'];
            }
           // echo $expenses;exit;
            $data['total']=$total;
            $data['expenses']=$expenses;
        }

        $this->getParkingExpenseDetails($data['notes']);
    }


    public function changeStatusAllow(){
        $id=$this->input->post('id');
        $amount=$this->input->post('amount');
        $allocationId=$this->input->post('allocationId');
         $category=trim($this->input->post('category'));
         $updatedAt=date('Y-m-d H:i:sa');

        $expenseLimit=$this->CashBookModel->get_expenseLimit('expenses_limit');

        $balAmt=$this->CashBookModel->loadBalAmt('notesdetails',$allocationId);
        $balAmount=$balAmt[0]['balanceAmt'];
        $lastParking=$balAmt[0]['parking'];
        $lastChallan=$balAmt[0]['challan'];
        $lastCng=$balAmt[0]['cng'];

        $updAmt=0;
        $amt=$amount;
        if($category=="parking"){
            $prevAmt=$balAmt[0]['parking'];

            if($prevAmt==$amount){
                $updAmt=$balAmount;
            }else if($prevAmt>$amount){
                $amount=$prevAmt-$amount;
                $updAmt=$balAmount+$amount;
            }else if($prevAmt<$amount){
                $amount=$amount-$prevAmt;
                $updAmt=$balAmount-$amount;
            }

            $totalLatestExpense=$lastChallan+$lastCng+$amt;
            $ownerStatus=0;
            if($totalLatestExpense > $expenseLimit){
                $ownerStatus=1;
            }

            $updateData=array('parking'=>$amt,'balanceAmt'=>$updAmt,'statusParking'=>1,'updatedAt'=>$updatedAt,'expenseOwnerApproval'=>$ownerStatus);
            $this->CashBookModel->update('notesdetails',$updateData,$id); 
            if($this->db->affected_rows()>0){
                echo "yes";
            }else{
                echo "No";
            }
        }else if($category=="cng"){
            $totalLatestExpense=$lastParking+$lastChallan+$amount;
            $ownerStatus=0;
            if($totalLatestExpense > $expenseLimit){
                $ownerStatus=1;
            }

            $updateData=array('cng'=>$amount,'statusCng'=>1,'updatedAt'=>$updatedAt,'expenseOwnerApproval'=>$ownerStatus);
            $this->CashBookModel->update('notesdetails',$updateData,$id);
            if($this->db->affected_rows()>0){
                echo "yes";
            }else{
                echo "No";
            }
        }else if($category=="challan"){
            $totalLatestExpense=$lastParking+$lastCng+$amount;
            $ownerStatus=0;
            if($totalLatestExpense > $expenseLimit){
                $ownerStatus=1;
            }

            $updateData=array('challan'=>$amount,'statusChallan'=>1,'updatedAt'=>$updatedAt,'expenseOwnerApproval'=>$ownerStatus);
            $this->CashBookModel->update('notesdetails',$updateData,$id);
            if($this->db->affected_rows()>0){
                echo "yes";
            }else{
                echo "No";
            } 
        }
        $this->getRefreshDetails($allocationId);
    }

    public function changeStatusDisAllow(){
        $id=$this->input->post('id');
        $amount=$this->input->post('amount');
        $allocationId=$this->input->post('allocationId');
        $category=trim($this->input->post('category'));
        $updatedAt=date('Y-m-d H:i:sa');

        $expenseLimit=$this->CashBookModel->get_expenseLimit('expenses_limit');

        $balAmt=$this->CashBookModel->loadBalAmt('notesdetails',$allocationId);
        $amt=$balAmt[0]['balanceAmt']+$amount;
        $lastParking=$balAmt[0]['parking'];
        $lastChallan=$balAmt[0]['challan'];
        $lastCng=$balAmt[0]['cng'];
        // echo $amt;
        // print_r($balAmt);exit;

        if($category=="parking"){
            $totalLatestExpense=$lastChallan+$lastCng+0;
            $ownerStatus=0;
            if($totalLatestExpense > $expenseLimit){
                $ownerStatus=1;
            }

            $updateData=array('parking'=>0,'balanceAmt'=>$amt,'statusParking'=>2,'updatedAt'=>$updatedAt,'expenseOwnerApproval'=>$ownerStatus);
            $this->CashBookModel->update('notesdetails',$updateData,$id); 
            if($this->db->affected_rows()>0){
                echo "yes";
            }else{
                echo "No";
            }
        }else if($category=="cng"){
            $totalLatestExpense=$lastParking+$lastChallan+0;
            $ownerStatus=0;
            if($totalLatestExpense > $expenseLimit){
                $ownerStatus=1;
            }

            $updateData=array('cng'=>0,'balanceAmt'=>$amt,'statusCng'=>2,'updatedAt'=>$updatedAt,'expenseOwnerApproval'=>$ownerStatus);
            $this->CashBookModel->update('notesdetails',$updateData,$id);
            if($this->db->affected_rows()>0){
                echo "yes";
            }else{
                echo "No";
            }
        }else if($category=="challan"){
            $totalLatestExpense=$lastParking+$lastCng+0;
            $ownerStatus=0;
            if($totalLatestExpense > $expenseLimit){
                $ownerStatus=1;
            }

            $updateData=array('challan'=>0,'balanceAmt'=>$amt,'statusChallan'=>2,'updatedAt'=>$updatedAt,'expenseOwnerApproval'=>$ownerStatus);
            $this->CashBookModel->update('notesdetails',$updateData,$id);
            if($this->db->affected_rows()>0){
                echo "yes";
            }else{
                echo "No";
            } 
        }

        $this->getRefreshDetails($allocationId);
    }

    public function updateCashValues(){
        $bookId=date('d_m_Y')."_Daily_Cash_Book";
        $id=$this->input->post('id');
        $data['notes']=$this->CashBookModel->load('notesdetails',$id);
        if($data['notes'][0]['collectedAmt']==0.00 || $data['notes'][0]['collectedAmt']==''){
            $allocatedId=$this->input->post('allocationId');
            $phyCash=$this->input->post('phyCash');
            $phForExpence=$phyCash;
            $expenses=$this->input->post('expenses');
            $cashTotal=$this->input->post('cashTotal');
            $cashTotal=$cashTotal-$expenses;
            $balance=$cashTotal-$phyCash;
            $phyCash=$data['notes'][0]['collectedAmt']+$phyCash;
            $updateData=array('collectedAmt'=>$phyCash,'balanceAmt'=>$balance);
            $this->CashBookModel->update('notesdetails',$updateData,$id);
            if($this->db->affected_rows()>0){
                $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal+$phForExpence;
                $empId=$this->session->userdata['logged_in']['id'];
                $createdAt=date('Y-m-d H:i:sa');
                $inputData=array('notesId'=>$id,'employeeId'=>$empId,'amount'=>$phForExpence,"nature"=>"Market Collection",'inoutStatus'=>'Inflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId);
                $this->CashBookModel->insert('expences',$inputData);
                if($this->db->affected_rows()>0){
                    echo "Yes.";
                }else{
                    echo "No.";
                }
            }else{
                echo "no";
            }
        }else{
            $allocatedId=$this->input->post('allocationId');
            $phyCash=$this->input->post('phyCash');
            $phForExpence=$phyCash;
            $expenses=$this->input->post('expenses');
            $balance=$data['notes'][0]['balanceAmt']-$phyCash;
            $phyCash=$data['notes'][0]['collectedAmt']+$phyCash;
            $updateData=array('collectedAmt'=>$phyCash,'balanceAmt'=>$balance);
            $this->CashBookModel->update('notesdetails',$updateData,$id);
            if($this->db->affected_rows()>0){
                $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal+$phForExpence;
                $empId=$this->session->userdata['logged_in']['id'];
                $createdAt=date('Y-m-d H:i:sa');
                $inputData=array('notesId'=>$id,'employeeId'=>$empId,'amount'=>$phForExpence,"nature"=>"Market Collection",'inoutStatus'=>'Inflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId);
                $this->CashBookModel->insert('expences',$inputData);
                if($this->db->affected_rows()>0){
                    echo "Yes.";
                }else{
                    echo "No.";
                }
            }else{
                echo "no";
            }
        }
       
    }

    public function insertIncomeInflow(){
        $bookId=date('d_m_Y')."_Daily_Cash_Book";
        $user_id = $this->session->userdata['logged_in']['id'];
        
        $lastBal=$this->CashBookModel->lastRecordDayBookValue();
        $openCloseBal=$lastBal['openCloseBalance'];
        if($openCloseBal=='' || $openCloseBal==Null){
            $openCloseBal=0.0;
        }
      
        $nature=trim($this->input->post('nature'));
        $emp=trim($this->input->post('empName'));
        $category=trim($this->input->post('category'));
        $cashAmt=trim($this->input->post('cashAmt'));
        $narration=trim($this->input->post('narration'));
        $openCloseBal=$openCloseBal+$cashAmt;
        if($emp != "--Select Employee---"){
            $createdAt=date('Y-m-d H:i:sa');
            $insertData=array('employeeId'=>$emp,'nature'=>$nature,'amount'=>$cashAmt,'inoutStatus'=>'Inflow','narration'=>$narration,'date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId,'updatedBy'=>$user_id);
            $this->CashBookModel->insert('expences',$insertData);
            if($this->db->affected_rows()>0){
                return redirect('cashier/CashBookController/IncomeExpense');
            }else{
                return redirect('cashier/CashBookController/IncomeExpense');
            }
        }  
        if($category != "---Select Category---"){
            $createdAt=date('Y-m-d H:i:sa');
            $insertData=array('category'=>$category,'nature'=>$nature,'amount'=>$cashAmt,'inoutStatus'=>'Inflow','narration'=>$narration,'date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId,'updatedBy'=>$user_id);
            $this->CashBookModel->insert('expences',$insertData);
            if($this->db->affected_rows()>0){
                return redirect('cashier/CashBookController/IncomeExpense');
            }else{
                return redirect('cashier/CashBookController/IncomeExpense');
            }
        }
    }

     public function insertIncomeOutflow(){
        $user_id = $this->session->userdata['logged_in']['id'];
        $bookId=date('d_m_Y')."_Daily_Cash_Book";
        $nature=trim($this->input->post('nature'));
        $emp=trim($this->input->post('empName'));
        $category=trim($this->input->post('category'));
        $cashAmt=trim($this->input->post('cashAmt'));
        $narration=trim($this->input->post('narration'));
        $createdAt=date('Y-m-d H:i:sa');
        
        $lastBal=$this->CashBookModel->lastRecordDayBookValue();
        $openCloseBal=$lastBal['openCloseBalance'];
        if($openCloseBal=='' || $openCloseBal==Null){
            $openCloseBal=0.0;
        }
        $openCloseBal=$openCloseBal-$cashAmt; 
         
        if($emp != "--Select Employee---"){
            $createdAt=date('Y-m-d H:i:sa');
            $insertData=array('employeeId'=>$emp,'nature'=>$nature,'amount'=>$cashAmt,'inoutStatus'=>'Outflow','narration'=>$narration,'date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId,'updatedBy'=>$user_id);
            $this->CashBookModel->insert('expences',$insertData);
            if($this->db->affected_rows()>0){
                return redirect('cashier/CashBookController/IncomeExpense');
            }else{
                return redirect('cashier/CashBookController/IncomeExpense');
            }
        }  
        if($category != "---Select Category---"){
            $createdAt=date('Y-m-d H:i:sa');
            $insertData=array('category'=>$category,'nature'=>$nature,'amount'=>$cashAmt,'inoutStatus'=>'Outflow','narration'=>$narration,'date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId,'updatedBy'=>$user_id);
            $this->CashBookModel->insert('expences',$insertData);
            if($this->db->affected_rows()>0){
                return redirect('cashier/CashBookController/IncomeExpense');
            }else{
                return redirect('cashier/CashBookController/IncomeExpense');
            }
        }
    }
    
   public function addBankDeposits(){
        $bookId=date('d_m_Y')."_Daily_Cash_Book";
       
        $empId=$this->session->userdata['logged_in']['id'];
        $note2000=$this->input->post('add2000');
        if($note2000==''||$note2000==NULL){
            $note2000=0;
        }else{
            $note2000=2000*(float)$note2000;
        }
        
        // $note1000=$this->input->post('add1000');
        // if($note1000==''||$note1000==NULL){
        //     $note1000=0;
        // }else{
        //     $note1000=1000*(float)$note1000;
        // }
    
        
        $note500=$this->input->post('add500');
        if($note500==''||$note500==NULL){
            $note500=0;
        }else{
            $note500=500*(float)$note500;
        }

        $note200=$this->input->post('add200');
        if($note200==''||$note200==NULL){
            $note200=0;
        }else{
            $note200=200*(float)$note200;
        }
        
        $note100=$this->input->post('add100');
        if($note100==''||$note100==NULL){
            $note100=0;
        }else{
            $note100=100*(float)$note100;
        }
        
        $note50=$this->input->post('add50');
        if($note50==''||$note50==NULL){
            $note50=0;
        }else{
            $note50=50*(float)$note50;
        }

        $note20=$this->input->post('add20');
        if($note20==''||$note20==NULL){
            $note20=0;
        }else{
            $note20=20*(float)$note20;
        }
        
        $note10=$this->input->post('add10');
        if($note10==''||$note10==NULL){
            $note10=0;
        }else{
            $note10=10*(float)$note10;
        }
        
        $coin=$this->input->post('coin');
        if($coin==''||$coin==NULL){
            $coin=0;
        }else{
            $coin=(float)$coin;
        }
    
        $total=$note2000+$note500+$note200+$note100+$note50+$note20+$note10+$coin;
        
        $lastBal=$this->CashBookModel->lastRecordDayBookValue();
        $openCloseBal=$lastBal['openCloseBalance'];
        if($openCloseBal=='' || $openCloseBal==Null){
            $openCloseBal=0.0;
        }
        $openCloseBal=$openCloseBal-$total; 
        
        $createdAt=date('Y-m-d H:i:sa');
        
        $insertData=array('employeeId'=>$empId,'amount'=>$total,'nature'=>'Bank Deposit','inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId,'updatedBy'=>$empId);
        $this->CashBookModel->insert('expences',$insertData);
        if($this->db->affected_rows()>0){
            return redirect('cashier/CashBookController/IncomeExpense');
        }else{
            return redirect('cashier/CashBookController/IncomeExpense');
        }
   }
   
    public function pastDay(){
        $data['getDate']=$this->CashBookModel->getDates('expences');
        $this->load->view('cashier/pastDayCashBooksView',$data);
    }
   
   public function dayWisePastDayBook($date){
        $data['pastDayDate']=$date;
        $data['inOut']=$this->CashBookModel->getPastDayInflowOutflow('allocations',$date);
        $data['inflowEmp']=$this->CashBookModel->getPastDayInflowEmp('expences',$date);
        $data['inflowCategory']=$this->CashBookModel->getPastDayInflowCategory('expences',$date);
        $this->load->view('cashier/dayWisePastDayCashBookView',$data);
    }

    public function suspenseIncomeTransaction(){
        $userid = $this->session->userdata['logged_in']['id'];
        $allocationId=trim($this->input->post('allocationId'));

        $detailAllocations=$this->CashBookModel->load('allocations',$allocationId);
        $currentAllocationCode=trim($detailAllocations[0]['allocationCode']);

        $notesdetailId=trim($this->input->post('noteId'));

        $shortAmount=trim($this->input->post('short'));
        $cashTaken=trim($this->input->post('cashTaken'));
        $phyCash=trim($this->input->post('phyCash')); 

        $expenses=trim($this->input->post('expenses'));
        $cashTotal=trim($this->input->post('cashTotal'));

        $note2000=trim($this->input->post('a2000'));
        // $note1000=trim($this->input->post('a1000'));
        $note500=trim($this->input->post('a500'));

        $note200=trim($this->input->post('a200'));
        $note100=trim($this->input->post('a100'));
        $note50=trim($this->input->post('a50'));

        $note20=trim($this->input->post('a20'));
        $note10=trim($this->input->post('a10'));
        $coin=trim($this->input->post('coin'));

        $detailAllocations=$this->CashBookModel->loadByAllocationId('notesdetails',$allocationId);

        if(!empty($detailAllocations)){
            if($detailAllocations[0]['parking'] >0 && $detailAllocations[0]['statusParking']==0){
                echo "Please allow/disallow parking expense first";
                exit;
            }

            if($detailAllocations[0]['challan'] >0 && $detailAllocations[0]['statusChallan']==0){
                echo "Please allow/disallow challan expense first";
                exit;
            }

            if($detailAllocations[0]['cng'] >0 && $detailAllocations[0]['statusCng']==0){
                echo "Please allow/disallow CNG expense first";
                exit;
            }
        }
        exit;

        $emp=$this->CashBookModel->getEmloyee('employee');
        $suspenseIncome=0;
        if($shortAmount<0){
            $suspenseIncome=abs($shortAmount);

            if($suspenseIncome>0){
                $susEmp=$this->CashBookModel->getSuspenseDetail('employee','SUSINC');
                if(!empty($susEmp)){
                    $description=$suspenseIncome." added for allocation no : ".$currentAllocationCode;
                    $susIncomeInsert=array('empId'=>$susEmp[0]['id'],'transactionType'=>'cr','allocationId'=>$allocationId,'amount'=>$suspenseIncome,'description'=>$description,'createdAt'=>date('Y-m-d H:i:sa'),'createdBy'=>$userid);
                    $this->CashBookModel->insert('emptransactions',$susIncomeInsert);
                }
            //suspense income transaction
                
            }
        }

        $bookId="";
        $balance=0.00;

        $allocation_info=$this->CashBookModel->load('allocations',$allocationId);
        $compName=$allocation_info[0]['company'];
        $data['notes']=$this->CashBookModel->load('notesdetails',$notesdetailId);

        if($phyCash==""){
            $phyCash=0;
        }
        $phForExpence=$phyCash;
        $balance=$cashTotal+abs($shortAmount)-$phyCash-$data['notes'][0]['parking']-$data['notes'][0]['challan']-$data['notes'][0]['cng'];
        $phyCash=$data['notes'][0]['collectedAmt']+$phyCash;
        $updateData=array('collectedAmt'=>$phyCash,'balanceAmt'=>$balance,'updatedAt'=>date('Y-m-d H:i:sa'),'updatedBy'=>$userid);
        $this->CashBookModel->update('notesdetails',$updateData,$notesdetailId);
        if($this->db->affected_rows()>0){
            $lastBal=$this->CashBookModel->lastRecordDayBookValue();
            $openCloseBal=$lastBal['openCloseBalance'];
            if($openCloseBal=='' || $openCloseBal==Null){
                $openCloseBal=0.0;
            }
            $openCloseBal=$openCloseBal+$cashTotal+abs($shortAmount);
            $empId=$this->session->userdata['logged_in']['id'];
            $createdAt=date('Y-m-d H:i:sa');
            $inputData=array('notesId'=>$notesdetailId,'company'=>$compName,'allocationId'=>$allocationId,'employeeId'=>$empId,'amount'=>($cashTotal+abs($shortAmount)),"nature"=>"Market Collection",'inoutStatus'=>'Inflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId,'updatedBy'=>$userid);
            $this->CashBookModel->insert('expences',$inputData);

            $notesDetails= $this->CashBookModel->load('notesdetails',$notesdetailId);
            $parking=$notesDetails[0]['parking'];
            $challan=$notesDetails[0]['challan'];
            $cng=$notesDetails[0]['cng'];

            if($parking !=0){
                $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal-$parking;
                $narrationData="Parking expense against Allocation No : ".$currentAllocationCode;
                $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$parking,"nature"=>"Parking",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                $this->CashBookModel->insert('expences',$expenseData);
            }

            if($challan !=0){
                $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal-$challan;
                $narrationData="Challan expense against Allocation No : ".$currentAllocationCode;
                $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$challan,"nature"=>"Challan",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                $this->CashBookModel->insert('expences',$expenseData);
            }

            if($cng !=0){
                $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal-$cng;
                $narrationData="CNG expense against Allocation No : ".$currentAllocationCode;
                $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$cng,"nature"=>"CNG",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                $this->CashBookModel->insert('expences',$expenseData);
            }
        }

        $remAmount=$balance;
        $cashTaken=$phyCash;
        
        $cashTotalFinal=$this->input->post('cashTotal');

        if($cashTaken>0.00){
            $insertData=array('note2000' =>$note2000, 'note500' => $note500,'note200' => $note200,'note100' => $note100,'note50' => $note50,'note20' => $note20,'note10' => $note10,'coins' => $coin,'updatedBy'=>$userid);
            $this->CashBookModel->updateNotesDetails('notesdetails',$insertData,$allocationId);
            // if($remAmount<=0){

                $upData=array('cashChequeStatus'=>'1','isAllocationComplete'=>'1','allocationCloseAt'=>date('Y-m-d H:i:sa'),'allocationClosedBy'=>$userid);
                $this->CashBookModel->update('allocations',$upData,$allocationId);

                if($this->db->affected_rows()>0){
                    $upBillCompData=array('status'=>0);
                    $this->CashBookModel->updateAllocationBillsStatus('allocationsbills',$upBillCompData,$allocationId);

                    $allocatedBills=$this->CashBookModel->getAllocatedBills('bills',$allocationId);
                    if(!empty($allocatedBills)){
                        $totalCash=0.0;
                        $totalCheque=0.0;
                        $totalNeft=0.0;
                        $totalSr=0.0;
                        foreach($allocatedBills as $b){
                            $totalCash=$totalCash+$b['fsCashAmt'];
                            $totalCheque=$totalCheque+$b['fsChequeAmt'];
                            $totalNeft=$totalNeft+$b['fsNeftAmt'];
                            $totalSr=$totalSr+$b['fsSrAmt'];
                            $penAmount=$b['pendingAmt'];

                            $bstatus=$b['fsbillStatus'];

                            $realSR=0.0;
                            $realReceive=0.0;
                            $realSR=($b['SRAmt'])+($b['fsSrAmt']);
                            $realReceive=($b['receivedAmt'])+($b['fsCashAmt']+$b['fsNeftAmt']+$b['fsChequeAmt']);

                            if($bstatus==="Resend"){
                                $upBillData=array('billType'=>'','fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','receivedAmt'=>$realReceive,'isAllocated'=>0);
                                $this->CashBookModel->update('bills',$upBillData,$b['id']);
                            }else{
                                $upBillData=array('fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','pendingAmt'=>$penAmount,'receivedAmt'=>$realReceive,'isAllocated'=>0);
                                $this->CashBookModel->update('bills',$upBillData,$b['id']);
                            }
                           
                        }
                        $totalCheque=$totalCheque+$totalNeft;
                        //total collected total update for allocations.
                        $upAllocationData=array('totalCashAmt'=>$totalCash,'totalChequeNeftAmt'=>$totalCheque,'totalSRAmt'=>$totalSr);
                        $this->CashBookModel->update('allocations',$upAllocationData,$allocationId);
                        
                        $allocationData=$this->CashBookModel->load('allocations',$allocationId);
                        if(!empty($allocationData)){
                            if($allocationData[0]['fieldStaffCode1'] > 0){
                                $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode1']);
                                $employeeMobile=$employeeDetails[0]['mobile'];
                                $employeeName=$employeeDetails[0]['name'];
                                $transactionDate=date('M d, Y H:i a');
                                
                                $companyDetails=$this->CashBookModel->getdata('office_details');
                                $officeName=$companyDetails[0]['distributorName'];
                                $distributorCode=$companyDetails[0]['distributorCode'];
                                
                                $jsonData=array(
                                    "flow_id"=>"618d086aff89a71b142e37e2",
                                    "sender"=>"SIAInc",
                                    "mobiles"=>'91'.$employeeMobile,
                                    "amount"=>number_format($totalCash),
                                    "distributorname"=>$officeName,
                                    "allocationnumber"=>$allocationData[0]['allocationCode'],
                                    "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                    "Allocationshortcash"=>""
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
                            }
                            
                            if($allocationData[0]['fieldStaffCode2'] > 0){
                                $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode2']);
                                $employeeMobile=$employeeDetails[0]['mobile'];
                                $employeeName=$employeeDetails[0]['name'];
                                $transactionDate=date('M d, Y H:i a');
                                
                                $companyDetails=$this->CashBookModel->getdata('office_details');
                                $officeName=$companyDetails[0]['distributorName'];
                                $distributorCode=$companyDetails[0]['distributorCode'];
                                
                                $jsonData=array(
                                     "flow_id"=>"618d086aff89a71b142e37e2",
                                    "sender"=>"SIAInc",
                                    "mobiles"=>'91'.$employeeMobile,
                                    "amount"=>number_format($totalCash),
                                    "distributorname"=>$officeName,
                                    "allocationnumber"=>$allocationData[0]['allocationCode'],
                                    "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                    "Allocationshortcash"=>""
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
                            }
                            
                            if($allocationData[0]['fieldStaffCode3'] > 0){
                                $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode3']);
                                $employeeMobile=$employeeDetails[0]['mobile'];
                                $employeeName=$employeeDetails[0]['name'];
                                $transactionDate=date('M d, Y H:i a');
                                
                                $companyDetails=$this->CashBookModel->getdata('office_details');
                                $officeName=$companyDetails[0]['distributorName'];
                                $distributorCode=$companyDetails[0]['distributorCode'];
                                
                                $jsonData=array(
                                    "flow_id"=>"618d086aff89a71b142e37e2",
                                    "sender"=>"SIAInc",
                                    "mobiles"=>'91'.$employeeMobile,
                                    "amount"=>number_format($totalCash),
                                    "distributorname"=>$officeName,
                                    "allocationnumber"=>$allocationData[0]['allocationCode'],
                                    "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                    "Allocationshortcash"=>""
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
                            }
                            
                            if($allocationData[0]['fieldStaffCode4'] > 0){
                                $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode4']);
                                $employeeMobile=$employeeDetails[0]['mobile'];
                                $employeeName=$employeeDetails[0]['name'];
                                $transactionDate=date('M d, Y H:i a');
                                
                                $companyDetails=$this->CashBookModel->getdata('office_details');
                                $officeName=$companyDetails[0]['distributorName'];
                                $distributorCode=$companyDetails[0]['distributorCode'];
                                
                                $jsonData=array(
                                     "flow_id"=>"618d086aff89a71b142e37e2",
                                    "sender"=>"SIAInc",
                                    "mobiles"=>'91'.$employeeMobile,
                                    "amount"=>number_format($totalCash),
                                    "distributorname"=>$officeName,
                                    "allocationnumber"=>$allocationData[0]['allocationCode'],
                                    "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                    "Allocationshortcash"=>""
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
                            }
                        }
                        
                        
                        
                    }
                }
            // }
        }
    }

     public function debitAmoutToEmp(){
        $userid = $this->session->userdata['logged_in']['id'];
        $allocationId=trim($this->input->post('allocationId'));
        $notesdetailId=trim($this->input->post('noteId'));

        $detailAllocations=$this->CashBookModel->load('allocations',$allocationId);
        $currentAllocationCode=trim($detailAllocations[0]['allocationCode']);

        $shortAmount=trim($this->input->post('short'));
        $cashTaken=trim($this->input->post('cashTaken'));
        $phyCash=trim($this->input->post('phyCash'));
        $expenses=trim($this->input->post('expenses'));
        $cashTotal=trim($this->input->post('cashTotal'));
        $finalcashTotal=trim($this->input->post('cashTotal'));

        $finalPhysicalAmount=$phyCash;
        $finalPhysicalShortAmount=$shortAmount;

        $note2000=trim($this->input->post('a2000'));
        // $note1000=trim($this->input->post('a1000'));
        $note500=trim($this->input->post('a500'));

        $note200=trim($this->input->post('a200'));
        $note100=trim($this->input->post('a100'));
        $note50=trim($this->input->post('a50'));

        $note20=trim($this->input->post('a20'));
        $note10=trim($this->input->post('a10'));
        $coin=trim($this->input->post('coin'));
        // echo $coin;exit;

        $detailAllocations=$this->CashBookModel->loadByAllocationId('notesdetails',$allocationId);

        if(!empty($detailAllocations)){
            if($detailAllocations[0]['parking'] >0 && $detailAllocations[0]['statusParking']==0){
                echo "Please allow/disallow parking expense first";
                exit;
            }

            if($detailAllocations[0]['challan'] >0 && $detailAllocations[0]['statusChallan']==0){
                echo "Please allow/disallow challan expense first";
                exit;
            }

            if($detailAllocations[0]['cng'] >0 && $detailAllocations[0]['statusCng']==0){
                echo "Please allow/disallow CNG expense first";
                exit;
            }
        }
       

        $emp=$this->CashBookModel->getEmloyee('employee');

        $suspenseIncome=0;
        

        if($shortAmount!=0 and $suspenseIncome == 0){
            $data['alocated']=$this->CashBookModel->getfieldStaffsById('allocations',$allocationId);
            $staff1=0;
            $staff2=0;
            $staff3=0;
            $staff4=0;
            if($data['alocated'][0]['fieldStaffCode1']>0){
                $staff1=$data['alocated'][0]['fieldStaffCode1'];
            }
            if($data['alocated'][0]['fieldStaffCode2']>0){
                $staff2=$data['alocated'][0]['fieldStaffCode2'];
            }
            if($data['alocated'][0]['fieldStaffCode3']>0){
                $staff3=$data['alocated'][0]['fieldStaffCode3'];
            }
            if($data['alocated'][0]['fieldStaffCode4']>0){
                $staff4=$data['alocated'][0]['fieldStaffCode4'];
            }
            $staff=$staff1." ".$staff2." ".$staff3." ".$staff4;
            $staff=explode(' ',$staff);
            $remove = array(0);
            $staff = array_diff($staff, $remove);
            $empIdForEmp=implode(',',$staff);   
            $countEmp=count($staff);

            $divAmt=$shortAmount/$countEmp;
    ?>
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="modal-title">Debit Allocation Short Amount from Employee</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                        <p id="totAmt">Short Amount is : <?php echo $shortAmount;?></p>
                        <input type="hidden" id="totalCalAmt" name="totalCalAmt" value="<?php echo $shortAmount; ?>">
                        <input type="hidden" id="dbt_cashTotal" name="cashTotal" value="<?php echo $cashTotal; ?>">
                         <input type="hidden" id="dbt_notesdetailId" name="notesdetailId" value="<?php echo $notesdetailId; ?>">
                     </div>
                        <br>
                        <div class="col-md-6">
                            <span>Select Employee :</span> 
                        <input type="text" autocomplete="off" placeholder="select employee" list="empNameList" id="empCm" name="addEmp" class="form-control"> 
                                    <datalist id="empNameList">
                                    <?php foreach ($emp as $req_item){ ?>
                                        <option id="<?php echo $req_item['id'] ?>" value="<?php echo $req_item['name'] ?>" />
                                      <?php } ?> 
                                    </datalist> 
                                    
                        </div>

                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary m-t-20" onclick="addNewRow();">Add</button><br><br>
                        </div>
                       
                        <table id="myRowTable" class="table table-striped table-hover js-basic-example DataTable display nowrap">
                          <?php 
                                for($i=0;$i<$countEmp;$i++){
                                    $emp=$this->CashBookModel->getOnlyName('employee',$staff[$i]);
                          ?>

                          <input type="hidden" id="dbt_allocationId" name="allocationId" value="<?php echo $allocationId; ?>">
                            <tr>
                                <th>Employee Name</th>
                                <th>Amount</th>
                                <th></th>
                                <th align="right">Action</th>
                            </tr>
                          <tr>
                            <td><?php echo $emp;?></td>
                            <td><input id='empAmt' onkeypress="return numbersonly(this, event);"
 type="text" name="empAmt[]" value="<?php echo $divAmt; ?>"></td>
                             <td><input id="dbt_empId" type="hidden" onkeypress="return numbersonly(this, event);"
 name="empId[]" value="<?php echo $staff[$i]; ?>"></td>
                            <td><button style="float:right;" onclick="Delete(this);"><i class="fa fa-close"></i></button></td>

                        </tr>
                          <?php  
                            }        
                          ?>
                        </table>
                    
                       <!--  <input type="checkbox" class="filled-in" id="basic_checkbox_1" value="yes" name="needSMS" />
                        <label for="basic_checkbox_1">Need to send SMS?</label> -->
                        <div id="err" style="color:red"></div><br>

                        <input id="btn_dbt" type="button" class="btn btn-primary btn-sm waves-effect" value="Debit">
                        <input type="button" data-dismiss="modal" class="btn btn-primary btn-sm waves-effect" value="Cancel">
                    </div>
    <?php   
        }else{


            if($cashTotal==0){
                // echo "if";exit;
                $upData=array('cashChequeStatus'=>'1','isAllocationComplete'=>'1','allocationCloseAt'=>date('Y-m-d H:i:sa'),'allocationClosedBy'=>$userid);
                $this->CashBookModel->update('allocations',$upData,$allocationId);
                if($this->db->affected_rows()>0){
                    $upBillCompData=array('status'=>0);
                    $this->CashBookModel->updateAllocationBillsStatus('allocationsbills',$upBillCompData,$allocationId);

                    $allocatedBills=$this->CashBookModel->getAllocatedBills('bills',$allocationId);
                    if(!empty($allocatedBills)){
                        $totalCash=0.0;
                        $totalCheque=0.0;
                        $totalNeft=0.0;
                        $totalSr=0.0;
                        foreach($allocatedBills as $b){
                            $totalCash=$totalCash+$b['fsCashAmt'];
                            $totalCheque=$totalCheque+$b['fsChequeAmt'];
                            $totalNeft=$totalNeft+$b['fsNeftAmt'];
                            $totalSr=$totalSr+$b['fsSrAmt'];
                            $bstatus=$b['fsbillStatus'];
                            $penAmount=$b['pendingAmt'];

                            $realSR=0.0;
                            $realReceive=0.0;
                            $realSR=($b['SRAmt'])+($b['fsSrAmt']);
                            $realReceive=($b['receivedAmt'])+($b['fsCashAmt']+$b['fsNeftAmt']+$b['fsChequeAmt']);
                            if($bstatus==="Resend"){
                                $upBillData=array('billType'=>'','fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','receivedAmt'=>$realReceive,'isAllocated'=>0);
                                $this->CashBookModel->update('bills',$upBillData,$b['id']);
                            }else{
                                $upBillData=array('fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','pendingAmt'=>$penAmount,'receivedAmt'=>$realReceive,'isAllocated'=>0);
                                $this->CashBookModel->update('bills',$upBillData,$b['id']);
                            }
                            
                        }
                        $totalCheque=$totalCheque+$totalNeft;

                        //total collected total update for allocations.
                        $upAllocationData=array('totalCashAmt'=>$totalCash,'totalChequeNeftAmt'=>$totalCheque,'totalSRAmt'=>$totalSr);
                        $this->CashBookModel->update('allocations',$upAllocationData,$allocationId);
                    }
                }
            }else{
                // echo "else";exit;
                $bookId="";
               
                $balance=0.00;
              

                $allocation_info=$this->CashBookModel->load('allocations',$allocationId);
                $compName=$allocation_info[0]['company'];
                $data['notes']=$this->CashBookModel->load('notesdetails',$notesdetailId);
                // echo $cashTotal.' ';
                // print_r($data['notes']);
                if($cashTotal===0 || $cashTotal===""){
                    // echo "if";exit;
                    if($phyCash==""){
                        $phyCash=0;
                    }
                    $phForExpence=$phyCash;
                   
                    $cashTotal=$cashTotal-$expenses;
                    $balance=$cashTotal-$phyCash;
                    $phyCash=$data['notes'][0]['collectedAmt']+$phyCash;
                    $updateData=array('collectedAmt'=>$finalPhysicalAmount,'balanceAmt'=>$finalPhysicalShortAmount,'updatedAt'=>date('Y-m-d H:i:sa'),'updatedBy'=>$userid);
                    $this->CashBookModel->update('notesdetails',$updateData,$notesdetailId);
                    if($this->db->affected_rows()>0){
                        $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                        $openCloseBal=$lastBal['openCloseBalance'];
                        if($openCloseBal=='' || $openCloseBal==Null){
                            $openCloseBal=0.0;
                        }
                        $openCloseBal=$openCloseBal+$phForExpence;
                        $empId=$this->session->userdata['logged_in']['id'];
                        $createdAt=date('Y-m-d H:i:sa');
                        $inputData=array('notesId'=>$notesdetailId,'company'=>$compName,'allocationId'=>$allocationId,'employeeId'=>$empId,'amount'=>$phForExpence,"nature"=>"Market Collection",'inoutStatus'=>'Inflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId,'updatedBy'=>$userid);
                        $this->CashBookModel->insert('expences',$inputData);
                    }
                }else{

                    // echo "else";exit;
                    if($phyCash==""){
                        $phyCash=0;
                    }
                    $phForExpence=$phyCash;
                    $balance=$cashTotal-$phyCash;
                    $phyCash=$data['notes'][0]['collectedAmt']+$phyCash;
                    // $balance=$data['notes'][0]['balanceAmt']-$phyCash;
                    // $phyCash=$data['notes'][0]['collectedAmt']+$phyCash;
                    $updateData=array('collectedAmt'=>$finalPhysicalAmount,'balanceAmt'=>$finalPhysicalShortAmount,'updatedAt'=>date('Y-m-d H:i:sa'),'updatedBy'=>$userid);
                    $this->CashBookModel->update('notesdetails',$updateData,$notesdetailId);
                    if($this->db->affected_rows()>0){
                        $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                        $openCloseBal=$lastBal['openCloseBalance'];
                        if($openCloseBal=='' || $openCloseBal==Null){
                            $openCloseBal=0.0;
                        }
                        $openCloseBal=$openCloseBal+$finalcashTotal;
                        $empId=$this->session->userdata['logged_in']['id'];
                        $createdAt=date('Y-m-d H:i:sa');
                        $inputData=array('notesId'=>$notesdetailId,'company'=>$compName,'allocationId'=>$allocationId,'employeeId'=>$empId,'amount'=>$finalcashTotal,"nature"=>"Market Collection",'inoutStatus'=>'Inflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'dayBookId'=>$bookId,'updatedBy'=>$userid);
                        $this->CashBookModel->insert('expences',$inputData);

                        $notesDetails= $this->CashBookModel->load('notesdetails',$notesdetailId);
                        $parking=$notesDetails[0]['parking'];
                        $challan=$notesDetails[0]['challan'];
                        $cng=$notesDetails[0]['cng'];

                        if($parking !=0){
                            $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                            $openCloseBal=$lastBal['openCloseBalance'];
                            if($openCloseBal=='' || $openCloseBal==Null){
                                $openCloseBal=0.0;
                            }
                            $openCloseBal=$openCloseBal-$parking;
                            $narrationData="Parking expense against Allocation No : ".$currentAllocationCode;
                            $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$parking,"nature"=>"Parking",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                            $this->CashBookModel->insert('expences',$expenseData);
                        }

                        if($challan !=0){
                            $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                            $openCloseBal=$lastBal['openCloseBalance'];
                            if($openCloseBal=='' || $openCloseBal==Null){
                                $openCloseBal=0.0;
                            }
                            $openCloseBal=$openCloseBal-$challan;
                            $narrationData="Challan expense against Allocation No : ".$currentAllocationCode;
                            $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$challan,"nature"=>"Challan",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                            $this->CashBookModel->insert('expences',$expenseData);
                        }

                        if($cng !=0){
                            $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                            $openCloseBal=$lastBal['openCloseBalance'];
                            if($openCloseBal=='' || $openCloseBal==Null){
                                $openCloseBal=0.0;
                            }
                            $openCloseBal=$openCloseBal-$cng;
                            $narrationData="CNG expense against Allocation No : ".$currentAllocationCode;
                            $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$cng,"nature"=>"CNG",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                            $this->CashBookModel->insert('expences',$expenseData);
                        }
                    }
                }

                $remAmount=$balance;
                $cashTaken=$phyCash;
                
                $cashTotalFinal=$this->input->post('cashTotal');

                if($cashTaken>0.00){
                    $insertData=array('collectedAmt'=>$finalPhysicalAmount,'balanceAmt'=>$finalPhysicalShortAmount,'note2000' =>$note2000,'note500' => $note500,'note200' => $note200,'note100' => $note100,'note50' => $note50,'note20' => $note20,'note10' => $note10,'coins' => $coin,'updatedBy'=>$userid);
                    $this->CashBookModel->updateNotesDetails('notesdetails',$insertData,$allocationId);
                    if($remAmount<=0){
                        $upData=array('cashChequeStatus'=>'1','isAllocationComplete'=>'1','allocationCloseAt'=>date('Y-m-d H:i:sa'),'allocationClosedBy'=>$userid);
                        $this->CashBookModel->update('allocations',$upData,$allocationId);
                        if($this->db->affected_rows()>0){
                            $upBillCompData=array('status'=>0);
                            $this->CashBookModel->updateAllocationBillsStatus('allocationsbills',$upBillCompData,$allocationId);

                            $allocatedBills=$this->CashBookModel->getAllocatedBills('bills',$allocationId);
                            if(!empty($allocatedBills)){
                                $totalCash=0.0;
                                $totalCheque=0.0;
                                $totalNeft=0.0;
                                $totalSr=0.0;
                                foreach($allocatedBills as $b){
                                    $totalCash=$totalCash+$b['fsCashAmt'];
                                    $totalCheque=$totalCheque+$b['fsChequeAmt'];
                                    $totalNeft=$totalNeft+$b['fsNeftAmt'];
                                    $totalSr=$totalSr+$b['fsSrAmt'];
                                    $penAmount=$b['pendingAmt'];

                                    $bstatus=$b['fsbillStatus'];

                                    $realSR=0.0;
                                    $realReceive=0.0;
                                    $realSR=($b['SRAmt'])+($b['fsSrAmt']);
                                    $realReceive=($b['receivedAmt'])+($b['fsCashAmt']+$b['fsNeftAmt']+$b['fsChequeAmt']);

                                    if($bstatus==="Resend"){
                                        $upBillData=array('billType'=>'','fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','receivedAmt'=>$realReceive,'isAllocated'=>0);
                                        $this->CashBookModel->update('bills',$upBillData,$b['id']);
                                    }else{
                                        $upBillData=array('fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','pendingAmt'=>$penAmount,'receivedAmt'=>$realReceive,'isAllocated'=>0);
                                        $this->CashBookModel->update('bills',$upBillData,$b['id']);
                                    }
                                   
                                }
                                $totalCheque=$totalCheque+$totalNeft;
                                //total collected total update for allocations.
                                $upAllocationData=array('totalCashAmt'=>$totalCash,'totalChequeNeftAmt'=>$totalCheque,'totalSRAmt'=>$totalSr);
                                $this->CashBookModel->update('allocations',$upAllocationData,$allocationId);
                                
                                $allocationData=$this->CashBookModel->load('allocations',$allocationId);
                                if(!empty($allocationData)){
                                    if($allocationData[0]['fieldStaffCode1'] > 0){
                                        $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode1']);
                                        $employeeMobile=$employeeDetails[0]['mobile'];
                                        $employeeName=$employeeDetails[0]['name'];
                                        $transactionDate=date('M d, Y H:i a');
                                        
                                        $companyDetails=$this->CashBookModel->getdata('office_details');
                                        $officeName=$companyDetails[0]['distributorName'];
                                        $distributorCode=$companyDetails[0]['distributorCode'];
                                        
                                        $jsonData=array(
                                            "flow_id"=>"618d086aff89a71b142e37e2",
                                            "sender"=>"SIAInc",
                                            "mobiles"=>'91'.$employeeMobile,
                                            "amount"=>number_format($totalCash),
                                            "distributorname"=>$officeName,
                                            "allocationnumber"=>$allocationData[0]['allocationCode'],
                                            "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                            "Allocationshortcash"=>""
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
                                    }
                                    
                                    if($allocationData[0]['fieldStaffCode2'] > 0){
                                        $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode2']);
                                        $employeeMobile=$employeeDetails[0]['mobile'];
                                        $employeeName=$employeeDetails[0]['name'];
                                        $transactionDate=date('M d, Y H:i a');
                                        
                                        $companyDetails=$this->CashBookModel->getdata('office_details');
                                        $officeName=$companyDetails[0]['distributorName'];
                                        $distributorCode=$companyDetails[0]['distributorCode'];
                                        
                                        $jsonData=array(
                                             "flow_id"=>"618d086aff89a71b142e37e2",
                                            "sender"=>"SIAInc",
                                            "mobiles"=>'91'.$employeeMobile,
                                            "amount"=>number_format($totalCash),
                                            "distributorname"=>$officeName,
                                            "allocationnumber"=>$allocationData[0]['allocationCode'],
                                            "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                            "Allocationshortcash"=>""
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
                                    }
                                    
                                    if($allocationData[0]['fieldStaffCode3'] > 0){
                                        $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode3']);
                                        $employeeMobile=$employeeDetails[0]['mobile'];
                                        $employeeName=$employeeDetails[0]['name'];
                                        $transactionDate=date('M d, Y H:i a');
                                        
                                        $companyDetails=$this->CashBookModel->getdata('office_details');
                                        $officeName=$companyDetails[0]['distributorName'];
                                        $distributorCode=$companyDetails[0]['distributorCode'];
                                        
                                        $jsonData=array(
                                             "flow_id"=>"618d086aff89a71b142e37e2",
                                            "sender"=>"SIAInc",
                                            "mobiles"=>'91'.$employeeMobile,
                                            "amount"=>number_format($totalCash),
                                            "distributorname"=>$officeName,
                                            "allocationnumber"=>$allocationData[0]['allocationCode'],
                                            "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                            "Allocationshortcash"=>""
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
                                    }
                                    
                                    if($allocationData[0]['fieldStaffCode4'] > 0){
                                        $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode4']);
                                        $employeeMobile=$employeeDetails[0]['mobile'];
                                        $employeeName=$employeeDetails[0]['name'];
                                        $transactionDate=date('M d, Y H:i a');
                                        
                                        $companyDetails=$this->CashBookModel->getdata('office_details');
                                        $officeName=$companyDetails[0]['distributorName'];
                                        $distributorCode=$companyDetails[0]['distributorCode'];
                                        
                                        $jsonData=array(
                                             "flow_id"=>"618d086aff89a71b142e37e2",
                                            "sender"=>"SIAInc",
                                            "mobiles"=>'91'.$employeeMobile,
                                            "amount"=>number_format($totalCash),
                                            "distributorname"=>$officeName,
                                            "allocationnumber"=>$allocationData[0]['allocationCode'],
                                            "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                            "Allocationshortcash"=>""
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
                                    }
                                }
                            }
                        }
                    }else{
                       
                        $upData=array('cashChequeStatus'=>'1','isAllocationComplete'=>'1','allocationCloseAt'=>date('Y-m-d H:i:sa'),'allocationClosedBy'=>$userid);
                        $this->CashBookModel->update('allocations',$upData,$allocationId);
                        if($this->db->affected_rows()>0){
                            $upBillCompData=array('status'=>0);
                            $this->CashBookModel->updateAllocationBillsStatus('allocationsbills',$upBillCompData,$allocationId);

                            $allocatedBills=$this->CashBookModel->getAllocatedBills('bills',$allocationId);
                            if(!empty($allocatedBills)){
                                $totalCash=0.0;
                                $totalCheque=0.0;
                                $totalNeft=0.0;
                                $totalSr=0.0;
                                foreach($allocatedBills as $b){
                                    $totalCash=$totalCash+$b['fsCashAmt'];
                                    $totalCheque=$totalCheque+$b['fsChequeAmt'];
                                    $totalNeft=$totalNeft+$b['fsNeftAmt'];
                                    $totalSr=$totalSr+$b['fsSrAmt'];
                                    $penAmount=$b['pendingAmt'];

                                    $bstatus=$b['fsbillStatus'];

                                    $realSR=0.0;
                                    $realReceive=0.0;
                                    $realSR=($b['SRAmt'])+($b['fsSrAmt']);
                                    $realReceive=($b['receivedAmt'])+($b['fsCashAmt']+$b['fsNeftAmt']+$b['fsChequeAmt']);

                                    if($bstatus==="Resend"){
                                        $upBillData=array('billType'=>'','fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','receivedAmt'=>$realReceive,'isAllocated'=>0);
                                        $this->CashBookModel->update('bills',$upBillData,$b['id']);
                                    }else{
                                        $upBillData=array('fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','pendingAmt'=>$penAmount,'receivedAmt'=>$realReceive,'isAllocated'=>0);
                                        $this->CashBookModel->update('bills',$upBillData,$b['id']);
                                    }
                                   
                                }
                                $totalCheque=$totalCheque+$totalNeft;
                                //total collected total update for allocations.
                                $upAllocationData=array('totalCashAmt'=>$totalCash,'totalChequeNeftAmt'=>$totalCheque,'totalSRAmt'=>$totalSr);
                                $this->CashBookModel->update('allocations',$upAllocationData,$allocationId);
                                
                                $allocationData=$this->CashBookModel->load('allocations',$allocationId);
                                if(!empty($allocationData)){
                                    if($allocationData[0]['fieldStaffCode1'] > 0){
                                        $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode1']);
                                        $employeeMobile=$employeeDetails[0]['mobile'];
                                        $employeeName=$employeeDetails[0]['name'];
                                        $transactionDate=date('M d, Y H:i a');
                                        
                                        $companyDetails=$this->CashBookModel->getdata('office_details');
                                        $officeName=$companyDetails[0]['distributorName'];
                                        $distributorCode=$companyDetails[0]['distributorCode'];
                                        
                                        $jsonData=array(
                                             "flow_id"=>"618d086aff89a71b142e37e2",
                                            "sender"=>"SIAInc",
                                            "mobiles"=>'91'.$employeeMobile,
                                            "amount"=>number_format($totalCash),
                                            "distributorname"=>$officeName,
                                            "allocationnumber"=>$allocationData[0]['allocationCode'],
                                            "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                            "Allocationshortcash"=>""
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
                                    }
                                    
                                    if($allocationData[0]['fieldStaffCode2'] > 0){
                                        $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode2']);
                                        $employeeMobile=$employeeDetails[0]['mobile'];
                                        $employeeName=$employeeDetails[0]['name'];
                                        $transactionDate=date('M d, Y H:i a');
                                        
                                        $companyDetails=$this->CashBookModel->getdata('office_details');
                                        $officeName=$companyDetails[0]['distributorName'];
                                        $distributorCode=$companyDetails[0]['distributorCode'];
                                        
                                        $jsonData=array(
                                            "flow_id"=>"618d086aff89a71b142e37e2",
                                            "sender"=>"SIAInc",
                                            "mobiles"=>'91'.$employeeMobile,
                                            "amount"=>number_format($totalCash),
                                            "distributorname"=>$officeName,
                                            "allocationnumber"=>$allocationData[0]['allocationCode'],
                                            "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                            "Allocationshortcash"=>""
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
                                    }
                                    
                                    if($allocationData[0]['fieldStaffCode3'] > 0){
                                        $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode3']);
                                        $employeeMobile=$employeeDetails[0]['mobile'];
                                        $employeeName=$employeeDetails[0]['name'];
                                        $transactionDate=date('M d, Y H:i a');
                                        
                                        $companyDetails=$this->CashBookModel->getdata('office_details');
                                        $officeName=$companyDetails[0]['distributorName'];
                                        $distributorCode=$companyDetails[0]['distributorCode'];
                                        
                                        $jsonData=array(
                                             "flow_id"=>"618d086aff89a71b142e37e2",
                                            "sender"=>"SIAInc",
                                            "mobiles"=>'91'.$employeeMobile,
                                            "amount"=>number_format($totalCash),
                                            "distributorname"=>$officeName,
                                            "allocationnumber"=>$allocationData[0]['allocationCode'],
                                            "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                            "Allocationshortcash"=>""
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
                                    }
                                    
                                    if($allocationData[0]['fieldStaffCode4'] > 0){
                                        $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode4']);
                                        $employeeMobile=$employeeDetails[0]['mobile'];
                                        $employeeName=$employeeDetails[0]['name'];
                                        $transactionDate=date('M d, Y H:i a');
                                        
                                        $companyDetails=$this->CashBookModel->getdata('office_details');
                                        $officeName=$companyDetails[0]['distributorName'];
                                        $distributorCode=$companyDetails[0]['distributorCode'];
                                        
                                        $jsonData=array(
                                             "flow_id"=>"618d086aff89a71b142e37e2",
                                            "sender"=>"SIAInc",
                                            "mobiles"=>'91'.$employeeMobile,
                                            "amount"=>number_format($totalCash),
                                            "distributorname"=>$officeName,
                                            "allocationnumber"=>$allocationData[0]['allocationCode'],
                                            "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                            "Allocationshortcash"=>""
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
                                    }
                                }
                                
                            }
                        }
                    }
                }
            }
        }
    }

 
    public function closeCashChequeBook(){
        $userid = $this->session->userdata['logged_in']['id'];
        $createdAt=date('Y-m-d H:i:sa');
        //array values
        $empId=$this->input->post('empId');
        $empAmt=$this->input->post('empAmt');
        
        $allocationShort=$this->input->post('totalCalAmt');
        
        //text values
        $allocationId=$this->input->post('allocationId');
        $notesdetailId=$this->input->post('notesdetailId');
        $totalCalAmt=$this->input->post('totalCalAmt');
        $cashTotal=$this->input->post('cashTotal');
        $note_details=$this->CashBookModel->load('notesdetails',$notesdetailId);

        $detailAllocations=$this->CashBookModel->load('allocations',$allocationId);
        $currentAllocationCode=trim($detailAllocations[0]['allocationCode']);

        $expenseTotal=0;
        if(!empty($note_details)){
            $expenseTotal=$note_details[0]['parking']+$note_details[0]['challan']+$note_details[0]['cng'];
        }

        //notes values
        $note2000=$this->input->post('add2000');
        if($note2000==''||$note2000==NULL){
            $note2000=0;
        }else{
            $note2000=(float)$note2000;
        }
        
        // $note1000=$this->input->post('add1000');
        // if($note1000==''||$note1000==NULL){
        //     $note1000=0;
        // }else{
        //     $note1000=(float)$note1000;
        // }
        
        $note500=$this->input->post('add500');
        if($note500==''||$note500==NULL){
            $note500=0;
        }else{
            $note500=(float)$note500;
        }

        $note200=$this->input->post('add200');
        if($note200==''||$note200==NULL){
            $note200=0;
        }else{
            $note200=(float)$note200;
        }
        
        $note100=$this->input->post('add100');
        if($note100==''||$note100==NULL){
            $note100=0;
        }else{
            $note100=(float)$note100;
        }
        
        $note50=$this->input->post('add50');
        if($note50==''||$note50==NULL){
            $note50=0;
        }else{
            $note50=(float)$note50;
        }

        $note20=$this->input->post('add20');
        if($note20==''||$note20==NULL){
            $note20=0;
        }else{
            $note20=(float)$note20;
        }
        
        $note10=$this->input->post('add10');
        if($note10==''||$note10==NULL){
            $note10=0;
        }else{
            $note10=(float)$note10;
        }
        
        $coin=$this->input->post('coin');
        if($coin==''||$coin==NULL){
            $coin=0;
        }else{
            $coin=(float)$coin;
        }
        $collectedAmt=($note2000*2000)+($note500*500)+($note200*200)+($note100*100)+($note50*50)+($note20*20)+($note10*10)+($coin);
        // echo $coin;exit;

        $finalTotal=$expenseTotal+$totalCalAmt;
        $debitedAmt=0;


        // echo $collectedAmt.' '.$cashTotal.' '.$finalTotal.' '.$totalCalAmt;exit;

        if($cashTotal==$finalTotal){
            
            $allocationDetails=$this->CashBookModel->getAllocationDetails('allocations',$allocationId);
            $emp=$this->input->post('empId');
            $debAmt=$this->input->post('empAmt');
            
            for($i=0;$i<count($emp);$i++){
                if($debAmt[$i]>0){
                    $debitedAmt=$debitedAmt+$debAmt[$i];
                    $desc=$debAmt[$i]." Debit against cash shortage in allocation no. ".$allocationDetails[0]['allocationCode'].' - '.$allocationDetails[0]['name'];

                    //debit to employee
                    $insData=array('empId'=>$emp[$i],'transactionType'=>'dr','allocationId'=>$allocationId,'amount'=>$debAmt[$i],'description'=>$desc,'createdAt'=>$createdAt,'createdBy'=>$userid);
                    $this->CashBookModel->insert('emptransactions',$insData);

                    //bill payment transaction
                    $insBillPaymentData=array('empId'=>$emp[$i],'paymentMode'=>'Debit To Employee','allocationId'=>$allocationId,'paidAmount'=>$debAmt[$i],'date'=>$createdAt,'updatedBy'=>$userid);
                    $this->CashBookModel->insert('billpayments',$insBillPaymentData);

                    if($this->db->affected_rows()>0){
                        // $mobile="8446107727";
                       
                        // $url="https://api.msg91.com/api/sendhttp.php?authkey=291106AG8eCyzDe5d626f5c&mobiles=".$mobile."&country=91&message=".$desc."&sender=TESTIN&route=4";

                        // $ch = curl_init();
                        // curl_setopt ($ch, CURLOPT_URL, $url);
                        // curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
                        // $contents = curl_exec($ch);
                        // curl_close($ch);

                        $employeeDetails=$this->CashBookModel->load('employee',$emp[$i]);
                        $employeeMobile=$employeeDetails[0]['mobile'];
                        $employeeName=$employeeDetails[0]['name'];
                        $transactionDate=date('M d, Y H:i a');
                        
                        $companyDetails=$this->CashBookModel->getdata('office_details');
                        $officeName=$companyDetails[0]['distributorName'];
                        $distributorCode=$companyDetails[0]['distributorCode'];
    
                        $ledger=$this->CashBookModel->getEmpLedgerByEmp('emptransactions',$emp[$i]);
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
                        
                        // $tmpBalance=$balance;
                        if($balance > 0){
                            $balance=number_format(abs($balance));
                            $balance =($balance).' Cr';
                        }else{
                            $balance=number_format(abs($balance));
                            $balance= ($balance).' Dr';
                        }
    
                        $jsonData=array(
                            "flow_id"=>"618d0891d740d95e8a535e64",
                            "sender"=>"SIAInc",
                            "mobiles"=>'91'.$employeeMobile,
                            "name"=>$employeeName,
                            "amount"=>number_format($debAmt[$i]),
                            "distributorname"=>$officeName,
                            "allocationnumber"=>$allocationDetails[0]['allocationCode'],
                            "route"=>$allocationDetails[0]['name'],
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
                    }
                }
            }


            //close allocation
            $upData=array('cashChequeStatus'=>'1','isAllocationComplete'=>'1','allocationCloseAt'=>date('Y-m-d H:i:sa'),'allocationClosedBy'=>$userid);
            $this->CashBookModel->update('allocations',$upData,$allocationId);
            
            $totalCash=0;
            $totalCheque=0;
            $totalNeft=0;
            $totalSr=0;

            if($this->db->affected_rows()>0){
                //get collection details
                $allocatedBills=$this->CashBookModel->getAllocatedBills('bills',$allocationId);
                if(!empty($allocatedBills)){
                    foreach($allocatedBills as $b){
                        $totalCash=$totalCash+$b['fsCashAmt'];
                        $totalCheque=$totalCheque+$b['fsChequeAmt'];
                        $totalNeft=$totalNeft+$b['fsNeftAmt'];
                        $totalSr=$totalSr+$b['fsSrAmt'];
                        $penAmount=$b['pendingAmt'];

                        $bstatus=$b['fsbillStatus'];

                        $realSR=0.0;
                        $realReceive=0.0;
                        $realSR=($b['SRAmt'])+($b['fsSrAmt']);
                        $realReceive=($b['receivedAmt'])+($b['fsCashAmt']+$b['fsNeftAmt']+$b['fsChequeAmt']);
                        if($bstatus==="Resend"){
                             $upBillData=array('billType'=>'','fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','receivedAmt'=>$realReceive,'isAllocated'=>0);
                            $this->CashBookModel->update('bills',$upBillData,$b['id']);
                        }else{
                             $upBillData=array('fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','pendingAmt'=>$penAmount,'receivedAmt'=>$realReceive,'isAllocated'=>0);
                            $this->CashBookModel->update('bills',$upBillData,$b['id']);
                        }
                    }
                    $totalCheque=$totalCheque+$totalNeft;
                    //total collected total update for allocations.
                    $upAllocationData=array('totalCashAmt'=>$totalCash,'totalChequeNeftAmt'=>$totalCheque,'totalSRAmt'=>$totalSr);
                    $this->CashBookModel->update('allocations',$upAllocationData,$allocationId);
                    
                    $allocationData=$this->CashBookModel->load('allocations',$allocationId);
                    if(!empty($allocationData)){
                        if($allocationData[0]['fieldStaffCode1'] > 0){
                            $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode1']);
                            $employeeMobile=$employeeDetails[0]['mobile'];
                            $employeeName=$employeeDetails[0]['name'];
                            $transactionDate=date('M d, Y H:i a');
                            
                            $companyDetails=$this->CashBookModel->getdata('office_details');
                            $officeName=$companyDetails[0]['distributorName'];
                            $distributorCode=$companyDetails[0]['distributorCode'];
                            
                            $jsonData=array(
                                "flow_id"=>"618d086aff89a71b142e37e2",
                                "sender"=>"SIAInc",
                                "mobiles"=>'91'.$employeeMobile,
                                "amount"=>number_format($totalCash-$allocationShort),
                                "distributorname"=>$officeName,
                                "allocationnumber"=>$allocationData[0]['allocationCode'],
                                "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                "Allocationshortcash"=>"Short Cash Rs ".number_format($allocationShort)." "
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
                        }
                        
                        if($allocationData[0]['fieldStaffCode2'] > 0){
                            $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode2']);
                            $employeeMobile=$employeeDetails[0]['mobile'];
                            $employeeName=$employeeDetails[0]['name'];
                            $transactionDate=date('M d, Y H:i a');
                            
                            $companyDetails=$this->CashBookModel->getdata('office_details');
                            $officeName=$companyDetails[0]['distributorName'];
                            $distributorCode=$companyDetails[0]['distributorCode'];
                            
                            $jsonData=array(
                                "flow_id"=>"618d086aff89a71b142e37e2",
                                "sender"=>"SIAInc",
                                "mobiles"=>'91'.$employeeMobile,
                                "amount"=>number_format($totalCash-$allocationShort),
                                "distributorname"=>$officeName,
                                "allocationnumber"=>$allocationData[0]['allocationCode'],
                                "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                "Allocationshortcash"=>"Short Cash Rs ".number_format($allocationShort)." "
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
                        }
                        
                        if($allocationData[0]['fieldStaffCode3'] > 0){
                            $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode3']);
                            $employeeMobile=$employeeDetails[0]['mobile'];
                            $employeeName=$employeeDetails[0]['name'];
                            $transactionDate=date('M d, Y H:i a');
                            
                            $companyDetails=$this->CashBookModel->getdata('office_details');
                            $officeName=$companyDetails[0]['distributorName'];
                            $distributorCode=$companyDetails[0]['distributorCode'];
                            
                            $jsonData=array(
                                "flow_id"=>"618d086aff89a71b142e37e2",
                                "sender"=>"SIAInc",
                                "mobiles"=>'91'.$employeeMobile,
                                "amount"=>number_format($totalCash-$allocationShort),
                                "distributorname"=>$officeName,
                                "allocationnumber"=>$allocationData[0]['allocationCode'],
                                "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                "Allocationshortcash"=>"Short Cash Rs ".number_format($allocationShort)." "
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
                        }
                        
                        if($allocationData[0]['fieldStaffCode4'] > 0){
                            $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode4']);
                            $employeeMobile=$employeeDetails[0]['mobile'];
                            $employeeName=$employeeDetails[0]['name'];
                            $transactionDate=date('M d, Y H:i a');
                            
                            $companyDetails=$this->CashBookModel->getdata('office_details');
                            $officeName=$companyDetails[0]['distributorName'];
                            $distributorCode=$companyDetails[0]['distributorCode'];
                            
                            $jsonData=array(
                                "flow_id"=>"618d086aff89a71b142e37e2",
                                "sender"=>"SIAInc",
                                "mobiles"=>'91'.$employeeMobile,
                                "amount"=>number_format($totalCash-$allocationShort),
                                "distributorname"=>$officeName,
                                "allocationnumber"=>$allocationData[0]['allocationCode'],
                                "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                "Allocationshortcash"=>"Short Cash Rs ".number_format($allocationShort)." "
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
                        }
                    }
                } 
            }
            echo "Allocation closed";
        }else{
            $createdAt=date('Y-m-d H:i:sa');
            $allocationDetails=$this->CashBookModel->load('allocations',$allocationId);
            $compName= $allocationDetails[0]['company'];
            $empId= $allocationDetails[0]['fieldStaffCode1'];
            //collected amount 
            $calculatedAmount= $cashTotal-$finalTotal;

            $lastBal=$this->CashBookModel->lastRecordDayBookValue();
            $openCloseBal=$lastBal['openCloseBalance'];
            if($openCloseBal=='' || $openCloseBal==Null){
                $openCloseBal=0.0;
            }
            $openCloseBal=$openCloseBal+$cashTotal;

            $notesDetailsData=array('collectedAmt'=>$collectedAmt,'balanceAmt'=>$totalCalAmt,'transactionType'=>'income','note2000'=>$note2000,'note500'=>$note500,'note200'=>$note200,'note100'=>$note100,'note50'=>$note50,'note20'=>$note20,'note10'=>$note10,'coins'=>$coin,'updatedAt'=>$createdAt,'updatedBy'=>$userid);
            //insert notes details
            $this->CashBookModel->update('notesdetails',$notesDetailsData,$notesdetailId);

            $expenseData=array('notesId'=>$notesdetailId,'allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'amount'=>$cashTotal,"nature"=>"Market Collection",'inoutStatus'=>'Inflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
            //insert expences details
            $this->CashBookModel->insert('expences',$expenseData);

            $notesDetails= $this->CashBookModel->load('notesdetails',$notesdetailId);
            $parking=$notesDetails[0]['parking'];
            $challan=$notesDetails[0]['challan'];
            $cng=$notesDetails[0]['cng'];

            if($parking !=0){
                $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal-$parking;
                $narrationData="Parking expense against Allocation No : ".$currentAllocationCode;
                $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$parking,"nature"=>"Parking",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                $this->CashBookModel->insert('expences',$expenseData);
            }

            if($challan !=0){
                $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal-$challan;
                $narrationData="Challan expense against Allocation No : ".$currentAllocationCode;
                $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$challan,"nature"=>"Challan",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                $this->CashBookModel->insert('expences',$expenseData);
            }

            if($cng !=0){
                $lastBal=$this->CashBookModel->lastRecordDayBookValue();
                $openCloseBal=$lastBal['openCloseBalance'];
                if($openCloseBal=='' || $openCloseBal==Null){
                    $openCloseBal=0.0;
                }
                $openCloseBal=$openCloseBal-$cng;
                $narrationData="CNG expense against Allocation No : ".$currentAllocationCode;
                $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$narrationData,'amount'=>$cng,"nature"=>"CNG",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
                $this->CashBookModel->insert('expences',$expenseData);
            }

            $lastBal=$this->CashBookModel->lastRecordDayBookValue();
            $openCloseBal=$lastBal['openCloseBalance'];
            if($openCloseBal=='' || $openCloseBal==Null){
                $openCloseBal=0.0;
            }
            $openCloseBal=$openCloseBal-$totalCalAmt;
            $allocationNarration="Allocation short amount for Allocation No : ".$currentAllocationCode." debited to employee";
           
            $expenseData=array('allocationId'=>$allocationId,'company'=>$compName,'employeeId'=>$empId,'narration'=>$allocationNarration,'amount'=>$totalCalAmt,"nature"=>"Employee Advances",'inoutStatus'=>'Outflow','date'=>$createdAt,'openCloseBalance'=>$openCloseBal,'updatedBy'=>$userid);
            //insert expences details
            $this->CashBookModel->insert('expences',$expenseData);

            $allocationDetails=$this->CashBookModel->getAllocationDetails('allocations',$allocationId);
            $emp=$this->input->post('empId');
            $debAmt=$this->input->post('empAmt');

            for($i=0;$i<count($emp);$i++){
                if($debAmt[$i]>0){
                    $desc=$debAmt[$i]." Debit against cash shortage in allocation no. ".$allocationDetails[0]['allocationCode'].' - '.$allocationDetails[0]['name'];

                    //debit to employee
                    $insData=array('empId'=>$emp[$i],'transactionType'=>'dr','allocationId'=>$allocationId,'amount'=>$debAmt[$i],'description'=>$desc,'createdAt'=>$createdAt,'createdBy'=>$userid);
                    $this->CashBookModel->insert('emptransactions',$insData);

                    //bill payment transaction
                    $insBillPaymentData=array('empId'=>$emp[$i],'paymentMode'=>'Debit To Employee','allocationId'=>$allocationId,'paidAmount'=>$debAmt[$i],'date'=>$createdAt,'updatedBy'=>$userid);
                    $this->CashBookModel->insert('billpayments',$insBillPaymentData);

                    if($this->db->affected_rows()>0){
                        // $mobile="8446107727";
                       
                        // $url="https://api.msg91.com/api/sendhttp.php?authkey=291106AG8eCyzDe5d626f5c&mobiles=".$mobile."&country=91&message=".$desc."&sender=TESTIN&route=4";

                        // $ch = curl_init();
                        // curl_setopt ($ch, CURLOPT_URL, $url);
                        // curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
                        // $contents = curl_exec($ch);
                        // curl_close($ch);
                        
                        $employeeDetails=$this->CashBookModel->load('employee',$emp[$i]);
                        $employeeMobile=$employeeDetails[0]['mobile'];
                        $employeeName=$employeeDetails[0]['name'];
                        $transactionDate=date('M d, Y H:i a');
                        
                        $companyDetails=$this->CashBookModel->getdata('office_details');
                        $officeName=$companyDetails[0]['distributorName'];
                        $distributorCode=$companyDetails[0]['distributorCode'];
    
                        $ledger=$this->CashBookModel->getEmpLedgerByEmp('emptransactions',$emp[$i]);
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
                            "flow_id"=>"618d0891d740d95e8a535e64",
                            "sender"=>"SIAInc",
                            "mobiles"=>'91'.$employeeMobile,
                            "name"=>$employeeName,
                            "amount"=>number_format($debAmt[$i]),
                            "distributorname"=>$officeName,
                            "allocationnumber"=>$allocationDetails[0]['allocationCode'],
                            "route"=>$allocationDetails[0]['name'],
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
                    }
                }
            }

            //close allocation
            $upData=array('cashChequeStatus'=>'1','isAllocationComplete'=>'1','allocationCloseAt'=>date('Y-m-d H:i:sa'),'allocationClosedBy'=>$userid);
            $this->CashBookModel->update('allocations',$upData,$allocationId);
            $totalCash=0;
            $totalCheque=0;
            $totalNeft=0;
            $totalSr=0;

            if($this->db->affected_rows()>0){
                //get collection details
                $allocatedBills=$this->CashBookModel->getAllocatedBills('bills',$allocationId);
                if(!empty($allocatedBills)){
                    foreach($allocatedBills as $b){
                        $totalCash=$totalCash+$b['fsCashAmt'];
                        $totalCheque=$totalCheque+$b['fsChequeAmt'];
                        $totalNeft=$totalNeft+$b['fsNeftAmt'];
                        $totalSr=$totalSr+$b['fsSrAmt'];
                        $penAmount=$b['pendingAmt'];
                        $bstatus=$b['fsbillStatus'];

                        $realSR=0.0;
                        $realReceive=0.0;
                        $realSR=($b['SRAmt'])+($b['fsSrAmt']);
                        $realReceive=($b['receivedAmt'])+($b['fsCashAmt']+$b['fsNeftAmt']+$b['fsChequeAmt']);
                        if($bstatus==="Resend"){
                             $upBillData=array('billType'=>'','fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','receivedAmt'=>$realReceive,'isAllocated'=>0);
                            $this->CashBookModel->update('bills',$upBillData,$b['id']);
                        }else{
                             $upBillData=array('fsBillStatus'=>'','fsCashAmt'=>'0','fsSrAmt'=>'0','fsNeftAmt'=>'0','fsChequeAmt'=>'0','statusLostChequeNeft'=>'0','pendingAmt'=>$penAmount,'receivedAmt'=>$realReceive,'isAllocated'=>0);
                            $this->CashBookModel->update('bills',$upBillData,$b['id']);
                        }
                    }
                    $totalCheque=$totalCheque+$totalNeft;
                    //total collected total update for allocations.
                    $upAllocationData=array('totalCashAmt'=>$totalCash,'totalChequeNeftAmt'=>$totalCheque,'totalSRAmt'=>$totalSr);
                    $this->CashBookModel->update('allocations',$upAllocationData,$allocationId);
                    
                    $allocationData=$this->CashBookModel->load('allocations',$allocationId);
                    if(!empty($allocationData)){
                        if($allocationData[0]['fieldStaffCode1'] > 0){
                            $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode1']);
                            $employeeMobile=$employeeDetails[0]['mobile'];
                            $employeeName=$employeeDetails[0]['name'];
                            $transactionDate=date('M d, Y H:i a');
                            
                            $companyDetails=$this->CashBookModel->getdata('office_details');
                            $officeName=$companyDetails[0]['distributorName'];
                            $distributorCode=$companyDetails[0]['distributorCode'];
                            
                            $jsonData=array(
                                "flow_id"=>"618d086aff89a71b142e37e2",
                                "sender"=>"SIAInc",
                                "mobiles"=>'91'.$employeeMobile,
                                "amount"=>number_format($totalCash-$allocationShort),
                                "distributorname"=>$officeName,
                                "allocationnumber"=>$allocationData[0]['allocationCode'],
                                "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                "Allocationshortcash"=>"Short Cash Rs ".number_format($allocationShort)." "
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
                        }
                        
                        if($allocationData[0]['fieldStaffCode2'] > 0){
                            $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode2']);
                            $employeeMobile=$employeeDetails[0]['mobile'];
                            $employeeName=$employeeDetails[0]['name'];
                            $transactionDate=date('M d, Y H:i a');
                            
                            $companyDetails=$this->CashBookModel->getdata('office_details');
                            $officeName=$companyDetails[0]['distributorName'];
                            $distributorCode=$companyDetails[0]['distributorCode'];
                            
                            $jsonData=array(
                                "flow_id"=>"618d086aff89a71b142e37e2",
                                "sender"=>"SIAInc",
                                "mobiles"=>'91'.$employeeMobile,
                                "amount"=>number_format($totalCash-$allocationShort),
                                "distributorname"=>$officeName,
                                "allocationnumber"=>$allocationData[0]['allocationCode'],
                                "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                "Allocationshortcash"=>"Short Cash Rs ".number_format($allocationShort)." "
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
                        }
                        
                        if($allocationData[0]['fieldStaffCode3'] > 0){
                            $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode3']);
                            $employeeMobile=$employeeDetails[0]['mobile'];
                            $employeeName=$employeeDetails[0]['name'];
                            $transactionDate=date('M d, Y H:i a');
                            
                            $companyDetails=$this->CashBookModel->getdata('office_details');
                            $officeName=$companyDetails[0]['distributorName'];
                            $distributorCode=$companyDetails[0]['distributorCode'];
                            
                            $jsonData=array(
                                "flow_id"=>"618d086aff89a71b142e37e2",
                                "sender"=>"SIAInc",
                                "mobiles"=>'91'.$employeeMobile,
                                "amount"=>number_format($totalCash-$allocationShort),
                                "distributorname"=>$officeName,
                                "allocationnumber"=>$allocationData[0]['allocationCode'],
                                "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                "Allocationshortcash"=>"Short Cash Rs ".number_format($allocationShort)." "
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
                        }
                        
                        if($allocationData[0]['fieldStaffCode4'] > 0){
                            $employeeDetails=$this->CashBookModel->load('employee',$allocationData[0]['fieldStaffCode4']);
                            $employeeMobile=$employeeDetails[0]['mobile'];
                            $employeeName=$employeeDetails[0]['name'];
                            $transactionDate=date('M d, Y H:i a');
                            
                            $companyDetails=$this->CashBookModel->getdata('office_details');
                            $officeName=$companyDetails[0]['distributorName'];
                            $distributorCode=$companyDetails[0]['distributorCode'];
                            
                            $jsonData=array(
                                "flow_id"=>"618d086aff89a71b142e37e2",
                                "sender"=>"SIAInc",
                                "mobiles"=>'91'.$employeeMobile,
                                "amount"=>number_format($totalCash-$allocationShort),
                                "distributorname"=>$officeName,
                                "allocationnumber"=>$allocationData[0]['allocationCode'],
                                "route"=>substr($allocationData[0]['allocationRouteName'],0,20),
                                "Allocationshortcash"=>"Short Cash Rs ".number_format($allocationShort)." "
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
                        }
                    }
                } 
            }
            echo "Allocation closed";
        }
    }
}
?>