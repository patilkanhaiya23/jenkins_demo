<?php $this->load->view('/layouts/commanHeader'); ?>

<style type="text/css">
    @media screen and (min-width: 1100px) {
        .modal-dialog {
          width: 1100px; /* New width for default modal */
        }
        .modal-sm {
          width: 350px; /* New width for small modal */
        }
    }

    @media screen and (min-width: 1100px) {
        .modal-lg {
          width: 1100px; /* New width for large modal */
        }
    }

    .logo_prov {
        border-radius: 30px;
         border: 1px solid black;
        background: red;
        color: black;
        padding: 6px;
        width: 50px;
        height: 50px;
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
                              Collection Report
                            </h2>
                        </div>
                      
                        <div class="body">
                            <form method="post" role="form" action="<?php echo site_url('reports/ReportController/collectionwiseReportData');?>"> 
                                <div class="row clearfix">

                                    <div class="col-md-12"> 
                                        <div class="col-md-2">
                                            <b>From Date</b>
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="material-icons">account_box</i>
                                                </span>
                                                <div class="form-line">
                                                    <input type="date" autocomplete="off" autofocus id="fromDate" name="fromDate" class="form-control date" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <b>To Date</b>
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="material-icons">account_box</i>
                                                </span>
                                                <div class="form-line">
                                                    <input type="date" autocomplete="off" autofocus id="toDate" name="toDate" class="form-control date"  required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <b>Company</b>
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="material-icons">account_box</i>
                                                </span>
                                                <div class="form-line">
                                                    <select name="company" id="company" class="form-control">
                                                        <?php 
                                                            if(!empty($company)){
                                                                foreach($company as $data){
                                                        ?>
                                                                    <option value="<?php echo $data['name']; ?>"><?php echo $data['name']; ?></option>
                                                        <?php
                                                                }
                                                            }
                                                        ?>
                                                        <option value="deliverySlip">Delivery Slip</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="input-group">
                                                <input type="radio" id="radio_1" name="category" value="summary" class="with-gap radio-col-red" checked/>
                                                <label for="radio_1">Summary</label>
                                                <br>
                                                <input type="radio" id="radio_2" name="category" value="detail" class="with-gap radio-col-red"/>
                                                <label for="radio_2">Detail</label>
                                            </div>
                                        </div>
                                    
                                        <div class="col-md-4">
                                                <button id="searchInfo" class="btn btn-primary m-t-15 waves-effect">
                                                    <i class="material-icons">search</i> 
                                                    <span class="icon-name">
                                                    Search
                                                    </span>
                                                </button>
                                            <a href="<?php echo site_url('AdHocController/billSearch');?>">
                                                    <button type="button" class="btn btn-primary m-t-15 waves-effect">
                                                        <i class="material-icons">cancel</i> 
                                                        <span class="icon-name"> Cancel</span>
                                                    </button>
                                                </a> 
                                            </div>
                                        </div> 
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php $this->load->view('/layouts/footerDataTable'); ?>

<script type="text/javascript">
    $(document).on('click','#searchInfo',function(){
        var billNo=$('#billNo').val();
        var charCount=$('#billNo').val().length;

        if(charCount<4){
            alert('Please enter first 4 characters');die();
        }else{
            if(billNo===''){
                $('#hideInfo').html('');
                $('#billNo').focus();
            }else{
                $.ajax({
                    type: "POST",
                    url:"<?php echo site_url('AdHocController/findBillsData');?>",
                    data:{billNo:billNo},
                    success: function (data) {
                        // alert(data);
                        $('#hideInfo').html(data);
                        $('#billNo').val('');
                        $('#billNo').focus();
                    }  
                });
            }
        }
       
        
    });
</script>