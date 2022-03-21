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
                            <h2>
                              Resend Bills
                            </h2>
                             <h2>
                               
                            </h2>
                        </div>
                        <div class="body">
                          <div class="row m-t-20">
                                <div class="col-md-12">
                                    <form method="post" role="form" action="">
                                        
                                        <div class="col-md-3">
                                            <b>Company Name </b>
                                        
                                            <select class="form-control" required id="comp" name="cmp">
                                                <option value="<?php echo $cmpName; ?>"><?php echo $cmpName; ?></option>
                                                <?php foreach ($company as $req_item){ ?>
                                                <option value="<?php echo $req_item['name'] ?>"><?php echo $req_item['name'] ?></option>
                                                <?php } ?> 
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                          <button type="submit" class="btn m-t-20 btn-primary">Search</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                            <br>
                            <div class="table-responsive">
                                <table style="font-size: 12px; " class="table table-bordered table-striped table-hover js-exportable dataTable" data-page-length='100'>
                                    <thead>
                                        <tr>
                                            <th>S. No.</th>
                                            <th>Bill No</th>
                                            <th>Bill Date</th>
                                            <th>Retailer</th>
                                            <th>Bill Amount</th>

                                            <th>Pending Amount</th>
                                            <th>Salesman</th>
                                            <th>Employee</th>
                                             <th>Route</th>
                                              <th>Entry Date</th>
                                            <th>No. of Days</th>
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
                                             <th>Employee</th>
                                              <th>Route</th>
                                              <th>Entry Date</th>
                                            <th>No. of Days</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php 
                                        $no=0;
                                        // print_r($resend);
                                            if(!empty($resend)){
                                            foreach($resend as $data){
                                                if($data['netAmount']==$data['pendingAmt']){
                                                  $retailerCode=$this->SrModel->loadRetailer($data['retailerCode']);

                                                $no++;
                                                $diff=strtotime(date('Y-m-d'))-strtotime($data['entryDate']);
                                        ?>
                                         <?php if($data['isAllocated']==1){ ?>
                                                 <tr style="background-color: #dcd6d5">
                                            <?php }else{ ?>
                                                 <tr>
                                            <?php } ?>
                                            <td><?php echo $no;?></td>
                                            <td><?php echo $data['billNo'];?></td>
                                            <td><?php 
                                             $dt=date_create($data['date']);
                                             echo date_format($dt,'d-M-Y');
                                             $dt=date_format($dt,'d-M-Y');
                                             ?></td>
                                            <td><?php echo $data['retailerName'];?></td>
                                            <td align="right"><?php echo number_format($data['netAmount']);?></td>
                                            <td align="right"><?php echo number_format($data['pendingAmt']);?></td>
                                            <td><?php echo $data['salesman'];?></td>
                                            <td><?php echo $data['ename'];?></td>
                                            <td><?php echo $data['rname'];?></td>
                                             <td><?php
                                             $date=date_create($data['entryDate']);
                                             echo date_format($date,"d-M-Y");
                                              ?></td>
                                            <td><?php echo abs(round($diff/86400));?></td>
                                            <td>
                                                 <?php if($data['isAllocated']!=1){ ?>
                                                <a id="prDetailsForAll" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-salesman="<?php echo $data['salesman']; ?>" data-billDate="<?php echo $dt; ?>" data-credAdj="<?php echo $data['creditAdjustment']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-route="<?php echo $data['routeName']; ?>" data-toggle="modal" data-target="#processModalForAll" ><button class="btn btn-xs btn-primary waves-effect waves-float" data-toggle="tooltip" data-placement="bottom" title="Process"><i class="material-icons">touch_app</i></button></a>

                                                   <!-- <a id="prDetails" href="javascript:void()" data-id="<?php echo $data['id']; ?>" data-billNo="<?php echo $data['billNo']; ?>" data-retailerName="<?php echo $data['retailerName']; ?>" data-gst="<?php if(!empty($retailerCode)){ echo $retailerCode[0]['gstIn']; } ?>" data-pendingAmt="<?php echo $data['pendingAmt']; ?>" data-toggle="modal" data-target="#processModal"><button class="btn btn-xs btn-primary waves-effect"><i class="material-icons">touch_app</i></button></a> -->
                                                    &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billHistoryInfo/'.$data['id']); ?>" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="bottom" title="View History"><i class="material-icons">info</i></a>
                                                  &nbsp;&nbsp;<a href="<?php echo site_url('AdHocController/billDetailsInfo/'.$data['id']); ?>" class="btn btn-xs  btn-primary" data-toggle="tooltip" data-placement="bottom" title="View Bill"><i class="material-icons">article</i></a>
                                                <?php  }else{

                                            $allocations=$this->SrModel->getAllocationDetailsByBill('bills',$data['id']);

                                                      $officeAllocations=$this->SrModel->getOfficeAllocationDetailsByBill('bills',$data['id']);
                                                      
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

<?php $this->load->view('/layouts/footerDataTable'); ?>

<?php $this->load->view('/layouts/processButtonView'); ?>

<script type="text/javascript">
    $( "#cmp").click(function() {
      $( "#cmp" ).select();
    });
</script>