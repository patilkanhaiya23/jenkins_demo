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
  
    <h1 style="display: none;">Welcome</h1><br/><br/><br/><br/>
    <section class="col-md-12 box" style="height: auto;overflow-y: scroll;">
        <div class="container-fluid">
            <div class="block-header">
                
            </div>
            <!-- Basic Examples -->
            <div class="row clearfix">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                           <center> <h2>
                              Signed Bills
                            </h2></center>
                            <br>
                             <h2>
                                <span><b>Allocation No : </b><?php echo $BillInfo[0]['allocationCode']; ?></span>&nbsp;&nbsp;
                                <span><b>Company : </b><?php echo $BillInfo[0]['company']; ?></span>&nbsp;&nbsp;
                                <span><b>Route : </b><?php echo $BillInfo[0]['rname']; ?></span>&nbsp;&nbsp;
                                <span><b>Employee : </b><?php echo $BillInfo[0]['ename']; ?></span>&nbsp;&nbsp;
                                <div align="right">

                                <form method="post" role="form" enctype="multipart/form-data" action="<?php echo site_url('manager/SrCheckController/finalSrBillStatus/'.$idAllocated); ?>"> 
                                           
                                        <button class="btn btn-primary m-t-15 waves-effect btn-sm">
                                                <span class="icon-name">Close</span>
                                        </button> 
                                </form>
                                   
                                </div>
                            </h2>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table style="font-size: 12px"  id="crTbl" class="table table-bordered table-striped table-hover js-basic-example dataTable" data-page-length='25'>
                                    <thead>
                                        <tr>
                                            <th>S. No.</th>
                                            <th>Bill No</th>
                                            <th>Retailer Name</th>
                                            <th>Bill Amount</th>
                                            <th>Past SR</th>
                                            <th>Past Coll.</th>
                                            <th> Todays SR</th>
                                            <th>Todays Coll.</th>
                                            <th>Pending Amount</th>
                                            <th>Payment Modes</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>S. No.</th>
                                            <th>Bill No</th>
                                            <th>Retailer Name</th>
                                            <th>Bill Amount</th>
                                            <th>Past SR</th>
                                            <th>Past Coll.</th>
                                            <th> Todays SR</th>
                                            <th>Todays Coll.</th>
                                            <th>Pending Amount</th>
                                            <th>Payment Modes</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php 
                                        $no=0;
                                            if(!empty($signed)){
                                            foreach($signed as $data){
                                                $retailerCode=$this->SrModel->loadRetailer($data['retailerCode']);
                                                $dt=date_create($data['date']);
                                                  $dt= date_format($dt,'d-M-Y');
                                                if($data['pendingAmt']>0){
                                                
                                                $no++;
                                                $diff=strtotime(date('Y-m-d'))-strtotime($data['date']);
                                        ?>
                                        <tr>
                                            <td><?php echo $no;?></td>
                                            <td><?php echo $data['billNo'];?></td>
                                            <td><?php echo $data['retailerName'];?></td>
                                            <td align="right"><?php echo $data['netAmount']; ?></td>
                                            <?php if($allocations[0]['gkStatus']==1){ ?>
                                            <td align="right">
                                                <?php 
                                                    $sr= (($data['SRAmt']-$data['fsSrAmt'])); 
                                                    if($sr <= 1){
                                                        echo 0;
                                                    }else{
                                                        echo $sr;
                                                    }
                                                ?>
                                            </td>
                                            <?php }else{ ?>
                                                <td align="right"><?php echo (($data['SRAmt'])); ?></td>
                                            <?php } ?>
                                            
                                            <td align="right"><?php echo $data['receivedAmt']; ?></td>
                                            <td align="right"><?php echo round($data['fsSrAmt']); ?></td>
                                            <td align="right"><?php echo round(($data['fsCashAmt']+$data['fsChequeAmt']+$data['fsNeftAmt'])); ?></td>
                                            <td align="right"><?php echo round(($data['pendingAmt']));?></td>
                                        <?php if($data['fsbillStatus']==='Billed'){ ?>
                                            <td align="left"><?php echo 'Bill'; ?></td>
                                        <?php }else{ 
                                                if(strpos($data['fsbillStatus'], 'Billed') !== false){
                                        ?>
                                                 <td align="left"><?php echo str_replace("Billed","Bill",$data['fsbillStatus']); ?></td>   
                                        <?php        }else{
                                            
                                        ?>
                                            <td align="left"><?php
                                            $statusData=$data['fsbillStatus'];
                                            if($data['fsbillStatus']=="F"){
                                                $statusData="SR";
                                            }
                                            echo $statusData.',Bill'; 
                                            
                                            ?></td>          
                                        <?php        }    
                                        
                                        ?>
                                            
                                        <?php } ?>
                                            
                                            <td>
                                                <?php if($data['isLostBill'] == 0){
                                                    // echo $data["id"].' '.$idAllocated;
                                                 ?>
                                                <button onclick="signedOkStatus(this,'<?php echo $data["id"]?>','<?php echo $idAllocated; ?>');" style="font-size : 11px;" id="signedOk" class="btn btn-xs btn-primary waves-effect"><i class="material-icons">check</i>
                                                 </button> 
                                                 <button data-toggle="modal" data-target="#billRemarkModal" data-type="basic" data-id="<?php echo $data["id"]; ?>" data-name="<?php echo $idAllocated; ?>" data-id="<?php echo $data['id']; ?>" data-salesman="<?php echo $data['salesman']; ?>" data-billDate="<?php echo $dt; ?>" data-credAdj="<?php echo $data['creditAdjustment']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-route="<?php echo $data['routeName']; ?>" style="font-size : 11px;" id="signedCancel" class="identifyingClass btn btn-xs btn-primary waves-effect"><i class="material-icons">cancel</i> 
                                                 </button>

                                             <?php }else if($data['isLostBill'] == 1){ ?>
                                               <i class="material-icons">check</i> 
                                            <?php } else if($data['isLostBill'] == 2){ ?>
                                                <i class="material-icons">cancel</i> 
                                                
                                            <?php } ?>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                            }
                                           } 
                                        ?>
                                    
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                 
            </div>

        </div>
    </section>


<div class="container">
  <div class="modal fade" id="billRemarkModal" role="dialog" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <!-- <center><h4 class="modal-title">Lost Bill Remark</h4></center> -->
            <center><h4 id="title_name" style="color:#050A30">Bill Transactions </h4></center></div>
          <div class="modal-body">
              <div class="body">
                  <div class="demo-masked-input">
                      <div class="row clearfix">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <h5 style="color:#000000">Bill No :  <span style="color:#050A30" id='bill_no'></span></h5>
                                    <input type="hidden" id="currentBillNo" autocomplete="off" name="currentBillNo" class="form-control"> 
                                    <input type="hidden" id="currentBillId" autocomplete="off" name="currentBillId" class="form-control"> 
                                     <input type="hidden" id="currentBillRetailer" autocomplete="off" name="currentBillRetailer" class="form-control">    
                                </div> 
                                
                                 <div class="col-md-3">
                                    <h5 style="color:#000000">Bill Date :  <span style="color:#050A30" id='bill-date'></span></h5>
                                </div> 
                                <span id='bill_retailer'></span>
                                <!--<div class="col-md-6">-->
                                <!--    <b>Retailer : </b>-->
                                <!--        <span id='bill_retailer'></span>-->
                                <!--</div> -->
                                <div class="col-md-3">
                                    <h5 style="color:#000000">Pending Amount : <span style="color:#050A30" id='bill_pendingAmt'></span></h5>
                                    <input type="hidden" id="currentPendingAmt" autocomplete="off" name="currentPendingAmt" class="form-control">   
                                </div>
                            </div>

                            <div class="col-md-12">
                               <!--<div class="col-md-3">-->
                               <!--     <b>Bill Date : </b> <span id='bill-date'></span>-->
                               <!-- </div>-->
                                <div class="col-md-3">
                                    <h5 style="color:#000000">Route:  <span style="color:#050A30" id='bill-route'></span></h5>
                                </div>
                                <div class="col-md-3">
                                    <h5 style="color:#000000">Salesman:  <span style="color:#050A30" id='bill-salesman'></span></h5>
                                </div>
                                <div class="col-md-3">
                                    <h5 style="color:#000000">GST No. : 
                                        <span style="color:#050A30" id='gst'></span></h5>
                                </div>
                                <div class="col-md-3"><span style="display:none" class="logo_prov">CN</span></div>
                            </div>
                        </div>
                        <div class="row"><br><br>
                        <form id="frms" method="post" role="form" action="<?php echo site_url('manager/SrCheckController/ChangeManagerStatusForSigned');?>"> 
                            <div class="col-md-12">
                                <input type="hidden" name="billIdForRemark" id="billIdForRemark" class="form-control date">
                                <input type="hidden" name="allocationIdForRemark" id="allocationIdForRemark" class="form-control date">
                                <div class="col-md-12">
                                    <b style="color:#000000">Remark </b>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                             <i class="material-icons">edit</i>
                                        </span>
                                        <div class="form-line">
                                            <input type="text" required name="billRemark" id="billRemark" class="form-control date" placeholder="Enter Remarks for Lost Bill" value="">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                  <div class="row clearfix">
                                      <div class="col-md-12">
                                          <button id="updateBillRemark" class="btn btn-primary m-t-15 waves-effect">
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
                        </form> 
                        </div>                          
                    </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
  </div>
</div>


<?php $this->load->view('/layouts/footerDataTable'); ?>
<script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js');?>"></script>

<script type="text/javascript">
    jQuery("#svId").on("click",function(){
        var rowCount = $('#crTbl tr').length;
        var textData=$('#crTbl tr');
       
        if(rowCount<=3){
            var allocatedID=$('#allocatedHid').val();
            $.ajax({
                type: "POST",
                url:"<?php echo site_url('manager/SrCheckController/saveSrCheck');?>",
                data:{"allocationId":allocatedID},
                success: function (data) {
                    parent.history.back();
                }  
            });
        }else{
            alert('Please complete process');
        }
       
       
    });

    function removeMe(t) {
        $(t).closest('tr').remove();
    }
</script>
<script type="text/javascript">
    function signedStatus(e,id,allocatedId){
        
        // if(id){
        //      $.ajax({
        //         type: "POST",
        //         url:"<?php echo site_url('manager/SrCheckController/ChangeManagerStatusForSigned');?>",
        //         data:{"id" : id,"allocatedId" : allocatedId},
        //         success: function (data) {
        //             location.reload(); 
        //         }  
        //     });
        // }

        // $(e).closest('tr').find('#okedit').text('');
    }

    function signedOkStatus(e,id,allocatedId){
        $("#signedOk").attr("disabled", true);
        if(id){
             $.ajax({
                type: "POST",
                url:"<?php echo site_url('manager/SrCheckController/ChangeManagerStatusForSignedOk');?>",
                data:{"id" : id,"allocatedId" : allocatedId},
                success: function (data) {
                    // document.getElementById('err').innerHTML=data;
                    location.reload(); 
                    // parent.history.back();
                    // window.location.href="<?php echo base_url();?>index.php/manager/SrCheckController/LoadSrCheckDetails/"+allocatedId;
                }  
            });
        }

        $(e).closest('tr').find('#okedit').text('');
    }

    // function checkTableDetails(){
    //     if ( $(this).closest("tbody").find("tr").length === 0 ) {
    //         alert("no rows left");

    //     }
    // }
</script>
<script type="text/javascript">
    // $(document).on('click','#signedCancel',function(){
    //   var billId = $(this).data('modalbillId');
    //   var allocationId = $(this).data('modalallocationId');
    //   alert(billId+' '+allocationId);

    //   $('#billIdForRemark').val(billId);
    //   $('#allocationIdForRemark').val(allocationId);
    // });
</script>

<script type="text/javascript">
    $(document).on('click','.identifyingClass',function(){
            $("#billIdForRemark").val('');
            $("#allocationIdForRemark").val('');
            $("#billRemark").val('');

            var billId = $(this).data('id');
            // alert(billId);
            var allocationId = $(this).data('name');
            $("#billIdForRemark").val(billId);
            $("#allocationIdForRemark").val(allocationId);

        // })
    });
</script>

<script type="text/javascript">

// $(document).on('click','#updateBillRemark',function(){
//      $("#updateBillRemark").attr("disabled", true);
// });   

$(document).on('click','#signedCancel',function(){
    var id=$(this).attr('data-id');
    var billNo=$(this).attr('data-billNo');
    var retailerName=$(this).attr('data-retailerName');
    var pendingAmt=$(this).attr('data-pendingAmt');
    pendingAmt=parseInt(pendingAmt); 
    
    // var nf = new Intl.NumberFormat();
    // pendingAmt=nf.format(pendingAmt);
    // pendingAmt=pendingAmt.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    var gst=$(this).attr('data-gst');
    var route=$(this).attr('data-route');
    
    var credAdj=$(this).attr('data-credAdj');
    credAdj=parseInt(credAdj);
    // alert(credAdj);
    if(credAdj>0){
        // credAdj=nf.format(credAdj);
        $('.logo_prov').text('CN : '+credAdj);
         $(".logo_prov").show();
    }else{
         $(".logo_prov").hide();
    }

    var billDate=$(this).attr('data-billDate');
    var salesman=$(this).attr('data-salesman');
    
    $('#currentPendingAmt').val(pendingAmt);
    $('#currentBillId').val(id);
    $('#currentBillNo').val(billNo);
    $('#currentBillRetailer').val(retailerName);
    $('#bill_no').text(billNo);
    $('#gst').text(gst);
    $('#routeDetail').text(route);
    $('#title_name').text(retailerName);
    // $('#bill_retailer').text(retailerName);
    $('#bill_pendingAmt').text(pendingAmt);
    $('#bill-date').text(billDate);
     $('#bill-salesman').text(salesman);
     $('#bill-route').text(route);
});
</script>