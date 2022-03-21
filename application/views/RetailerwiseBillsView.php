<?php $this->load->view('/layouts/commanHeader'); ?>

        <h1 style="display: none;">Welcome</h1><br/><br/><br/><br/><br/><br/>
     <section class="col-md-12 box" style="height: auto;overflow-y: scroll;">
        <div class="container-fluid">
            <div class="block-header">
                <h2>
                     Retailerwise Bills
                </h2>
            </div>
            <!-- Basic Examples -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Retailerwise Bills Master
                            </h2>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable" data-page-length='100'>
                                    <thead>
                                        <tr>
                                            <th>Sr.No</th>
                                            <th>Bill No</th>
                                            <th>Date</th>
                                            <th>Retailer Code</th>
                                            <th>Net Amount</th>
                                            <th>SRAmt</th>
                                            <th>ReceivedAmt</th>
                                            <th>PendingAmt</th>
                                            <th>BillType</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Sr.No</th>
                                            <th>Bill No</th>
                                            <th>Date</th>
                                            <th>Retailer Code</th>
                                            <th>Net Amount</th>
                                            <th>SRAmt</th>
                                            <th>ReceivedAmt</th>
                                            <th>PendingAmt</th>
                                            <th>BillType</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php
                                              $no=0;
                                              foreach ($bills as $data) 
                                                {
                                                  $id=$data['id'];
                                                 $no++; 
                                                 $retailerName=$this->DeliverySlipModel->getRetailerName($data['retailerId']);
                                                 $retailerName=$retailerName[0]['name'];
                                          ?>
                                    <tr>
                                        <td><?php echo $no; ?></td>
                                        <td>
                                            <a href="<?php echo base_url().'index.php/DeliverySlipController/billwiseDetail/'.$data['billNo']; ?>">
                                                <?php echo rtrim($data['billNo']); ?>
                                            </a>
                                        </td>
                                        <td><?php
                                            $dt=date_create($data['date']);
                                            $data['date'] = date_format($dt,'d-m-Y');
                                            echo $data['date']; ?>
                                        </td>
                                        <td><?php echo $retailerName; ?></td>
                                       <!--  <td><?php echo $data['distributorDiscount']; ?></td> -->
                                        <td><?php echo $data['netAmount']; ?></td>
                                        <td><?php echo $data['SRAmt']; ?></td>
                                        <td><?php echo $data['receivedAmt']; ?></td>
                                        <td><?php echo $data['pendingAmt']; ?></td>
                                        <td><?php echo $data['billType']; ?></td>
                                       
                                   </tr>  
                                     <?php
                                        }
                                      ?>   
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Basic Examples -->  
        </div>
    </section>
<?php $this->load->view('/layouts/footerDataTable'); ?>
