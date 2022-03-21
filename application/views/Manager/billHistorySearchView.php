<?php $this->load->view('/layouts/commanHeader'); ?>

<style type="text/css">
    @media screen and (min-width: 1100px) {
        .modal-dialog {
          width: 1100px; /* New width for default modal */
        }
        .modal-sm {
          width: 400px; /* New width for small modal */
        }
    }

    @media screen and (min-width: 1100px) {
        .modal-lg {
          width: 1100px; /* New width for large modal */
        }
    }

</style>

    <h1 style="display: none;">Welcome</h1><br/><br/><br/><br/><br/>    
    <section class="col-md-12 box" style="height: auto;overflow-y: scroll;">
        <div class="container-fluid">
           
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                              Bill History

                            </h2>
                            <h2 align="right">
                            <span><a class="btn btn-xs btn-primary" href="javascript:window.history.go(-1);"><i class="material-icons">arrow_back</i></a></span>
                            </h2>
                        </div>
                      
                        <div class="body">
                            
                            <div class="row clearfix">
                            <!-- <div class="demo-masked-input"> -->
                                    <div id="hideInfo" class="col-md-12"> 
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
                        
            ?>
                    <tr>
                        <td colspan="3">
                            <a href="<?php echo base_url('index.php/AdHocController/retailerHistoryInfoByBillSearch'.$urlSite); ?>"><?php echo $data['retailerName']; ?></a>
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
                 <th> Status</th>
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
                        <td><?php echo ($data['SRAmt']+$data['fsSrAmt']); ?></td>
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
                          }
                        ?>
                        </td>
                        
                     <!--    <td>
                        <?php
                        if($data['isAllocated']!=1 && $data['pendingAmt'] >0){
                     ?>
                        <a id="prDetails" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-code="<?php echo $data['retailerCode']; ?>" data-salesman="<?php echo $data['salesman']; ?>" data-route="<?php echo $data['routeName']; ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-toggle="modal" data-target="#processModal"><button class="btn bg-primary margin">Process</button></a>

                      <?php }else{  

                        $allocations=$this->AllocationByManagerModel->getAllocationDetailsByBill('bills',$billId);

                        $officeAllocations=$this->AllocationByManagerModel->getOfficeAllocationDetailsByBill('bills',$billId);
                          
                         if(!empty($allocations)){
                          echo "<p style='color:blue'>Allocated in : ".$allocations[0]['allocationCode']."</p>";
                            }else if(!empty($officeAllocations)){
                               echo "<p style='color:blue'>Allocated in : ".$officeAllocations[0]['allocationCode']."</p>";
                            }else{
                                if($data['pendingAmt'] >0){
                                    echo "Leave Unallocated";
                                }else{
                                    echo "Bill Cleared";
                                }
                            }
                        }
                            ?>
                        </td> -->

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
                  <span style="color:blue">  Bill Signed / Resend History  </span>
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
                        <td colspan="4"><a target="_blank" href="<?php echo site_url('AllocationByManagerController/CloseCompleteAllocation/'.$resend['allocationId'])?>"><?php echo $resend['alCode']; ?></a></td>
                        <td colspan="4"><?php echo ($resend['ename1'].' '.$resend['ename2'].$resend['ename3'].$resend['ename4']) ; ?></td>
                        <td colspan="4"><?php echo $createdDate; ?></td>
                        <td colspan="4">
                            <?php
                              if($resend['isResendBill']==1){
                                echo 'Resend';
                            } else if((($resend['netAmount']-($remAmount)) > 0) && ($resend['isResendBill'] == 0)){ 
                                echo "Signed";
                            } else if((($resend['netAmount']-($remAmount)) <= 0)){
                                echo "Cleared";
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

        <table id="payHistory" style="font-size: 12px" class="table table-bordered table-striped table-hover dataTable js-exportable" data-page-length='100'>
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
                      if($data['paidAmount'] !=0){
                    ?>
                      <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php 
                                if(empty($data['allocationCode'])){
                                    if($data['paymentMode']=="Cash"){
                                         echo 'Office Collection'; 
                                    }else{
                                         echo '<center>-</center>'; 
                                    } 
                                   
                                }else {
                                    $idForAllocation=$data['allocationId'];
                                    $codeForAllocation=$data['allocationCode'];
                                    $url= site_url("AllocationByManagerController/CloseCompleteAllocation/".$idForAllocation);
                                    
                                    echo "<a target='_blank' href='".$url."'>".$codeForAllocation."</a>";
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
                      if($data['amount']>0){
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
    <?php } ?>
                                    </div>                                
                                <!-- </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



<?php $this->load->view('/layouts/footerDataTable'); ?>

<?php $this->load->view('/layouts/processButtonView'); ?>

<script type="text/javascript">

  $.fn.dataTable.moment('DD/MM/YY');
  $('#payHistory').DataTable({ 
         "order": [[ 3, "desc" ]] 
  }); 
</script>