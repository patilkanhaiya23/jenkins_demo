<?php $this->load->view('/layouts/commanHeader'); ?>

<style type="text/css">
    @media screen and (min-width: 900px) {
        .modal-dialog {
          width: 900px; /* New width for default modal */
        }
        .modal-sm {
          width: 350px; /* New width for small modal */
        }
    }

    @media screen and (min-width: 900px) {
        .modal-lg {
          width: 900px; /* New width for large modal */
        }
    }

</style>
<script>
    $(document).ready(function() {
    $.fn.dataTable.ext.errMode = 'none';
    $('#retTbl').DataTable( {
        dom: 'Bfrtip',
        stateSave: true,
            buttons: [{
                    extend: 'pdf',
                    title: 'Retailers Details',
                    exportOptions: {
                        columns: "thead th:not(.noExport)"
                    }
                },{
                    extend: 'excel',
                    title: 'Retailers Details',
                    exportOptions: {
                        columns: "thead th:not(.noExport)"
                    }
                }, {
                    extend: 'csv',
                    title: 'Retailers Details',
                    exportOptions: {
                        columns: "thead th:not(.noExport)"
                    }
                }
            ]
        
    } );
} );
</script>
        <!-- <section class="content"> -->
<h1 style="display: none;">Welcome</h1><br/><br/><br/><br/><br>
    <section class="col-md-12 box" style="height: auto;overflow-y: scroll;">
        <div class="container-fluid">
            
            <!-- Basic Examples -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                               Retailers Details
                            </h2>
                             <p align="right">
                                    <button data-toggle="modal" data-target="#newRetailerModal" class="modalLink btn btn-primary m-t-15 waves-effect">
                                    <span class="icon-name"> <i class="material-icons">person_add</i>Add Retailer </span>
                                    </button>
                          
                                    <a href="<?php echo site_url('RetailerController/blockedRetailers/');?>">
                                        <button type="submit" class="btn btn-primary m-t-15 waves-effect"><i class="material-icons">visibility</i>  Show Inactive Retailers </button>
                                    </a> 
                            </p>
                            
                            
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table id="retTbl" class="table table-bordered table-striped table-hover js-basic-example dataTable" data-page-length='10'>
                                    <thead>
                                        <tr>
                                            <th>S. No</th>
                                            <th>Retailer Code</th>
                                            <th>Retailer Name</th>
                                            <th>Area</th>
                                            <th class="noExport">Action</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>S. No</th>
                                            <th>Retailer Code</th>
                                            <th>Retailer Name</th>
                                            <th>Area</th>
                                            <th class="noExport">Action</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                    <?php
                                        $no=0;
                                        foreach($retailer as $data) 
                                          {
                                           $no++; 
                                    ?>
                                        <tr>
                                            <td><?php echo $no; ?></td>
                                           <td><?php echo $data['retailerCode']; ?></td>
                                            <td><?php echo $data['name']; ?></td>
                                            <td><?php echo $data['area']; ?></td>
                                            <td>
                                                <a class="retLink" data-id="<?php echo $data['id']; ?>" data-toggle="modal" data-target="#retailerModal" href="#">
                                                    <i class="material-icons" style="color: green;">edit</i>
                                                </a>
                                                &nbsp;
                                                <a id="deleted" href="<?php echo base_url().'index.php/RetailerController/deactivateRetailer/'.$data['id'];  ?>">
                                                    <b>
                                                        <i class="material-icons" style="color: red;">remove_circle</i> 
                                                    </b>
                                                </a>                                               
                                            </td>
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

   
<div class="container">
  <div class="modal fade" id="newRetailerModal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
            <center><h4 class="modal-title">Add Retailer</h4></center>
          </div>
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
                                                <input type="text" id='retailerCode' autocomplete="off" value="<?php echo $retailerCode; ?>" name="retailerCode" list="ret" class="form-control date" placeholder="Enter retailer code" required>
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
                                                <input type="text" id='rtName' autocomplete="off" name="rtName" list="ret" class="form-control date" placeholder="Enter retailer name" required>
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
                                                <input autocomplete="off" id="area" type="text" name="area" class="form-control date" placeholder="Enter area" required>
                                            </div>
                                        </div>
                                    </div> 

                                  <div id="recStatus1"></div>
                                     <div class="col-md-12">
                                        <div class="row clearfix">
                                            <div class="col-md-4">
                                                <button id="insRet" class="btn btn-primary m-t-15 waves-effect">
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
      </div>
    </div>
  </div>
</div>

    
<div class="container">
  <div class="modal fade" id="retailerModal" role="dialog">
    <div class="modal-dialog">
      <div id="retailerdata" class="modal-content">
      
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('/layouts/footerDataTable'); ?>
<script>
 $(document).ready(function(){
    $('.retLink').click(function(){
        var id=$(this).attr('data-id');
       
        $.ajax({
            url : "<?php echo site_url('RetailerController/editRetailer');?>",
            method : "POST",
            data : {'id': id, },
            success: function(data){
              $('#retailerdata').html(data);
            }
        });
    });
});
</script>

<script type="text/javascript">
//     jQuery("#insRet").on("click",function(){
//         var retailerName = $('#rtName').val();
//         var route=$('#routeNames').val();
//         var salesman=$('#salesmanNames').val();
//         var rtId=$('#rtId').val();
//         // alert(retailerName+" "+route+" "+salesman+" "+rtId);
//         die();
//         if(retailerName==""){
//             alert("Please enter Retailer Name");
//         }else{
//             $.ajax({
//                 type: "POST",
//                 url:"<?php echo site_url('RetailerController/update');?>",
//                 data:{"retailerName" : retailerName,"route":route,"salesman":salesman},
//                 success: function (data) {
//                 //   ('#recStatus').innerHTML=data
//                     $('#recStatus1').html(data);
//                     window.location.href="<?php echo base_url();?>index.php/DeliverySlipController";
//                 }  
//             });
//         }
//     });
</script>

<script>
function deleted(id)
{ 
  // alert(id);
swal({
  title: "Are you sure to delete?",
  text: "Once deleted, you will not be able to recover this!",
  icon: "warning",
  buttons: true,
  dangerMode: true,
})
.then((willDelete) => {
  if (willDelete) {
    $.ajax({
        url: "<?php echo site_url('RetailerController/delete');?>",
        type: "post",
        data: {'id':id},
        success: function (response) {
         
          swal(response, {
            icon: "success",
          });
          var URL = "<?php echo site_url('RetailerController');?>";
          setTimeout(function(){ window.location = URL; }, 1000);
        },
        error: function(jqXHR, textStatus, errorThrown) {
           console.log(textStatus, errorThrown);
        }
    });
    
  } else {
    swal("Your record is safe!");
  }
});
}
</script>



 <script type="text/javascript">
    $(document).on("click","#insRet",function() {
        var retailerName = $('#rtName').val();
        var area=$('#area').val();
        var retailerCode=$('#retailerCode').val();


        if(retailerName ==="" || area==="" || retailerCode===""){
            alert("Please enter all details");
        }else{
            $.ajax({
                type: "POST",
                url:"<?php echo site_url('RetailerController/insert');?>",
                data:{"retailerName" : retailerName,"area":area,"retailerCode":retailerCode},
                success: function (data) {
                //   ('#recStatus').innerHTML=data
                  alert(data);
                    // $('#recStatus1').html(data);
                    window.location.href="<?php echo base_url();?>index.php/RetailerController";
                }  
            });
        }
    });
 </script>

  <script type="text/javascript">
    $(document).on("click","#updRetInfo",function() {
        var retailerId = $('#retailerInfoIdU').val();
        var retailerName = $('#rtNameU').val();
        var area=$('#areaU').val();
        var retailerCode=$('#retailerCodeU').val();
        if(retailerName ==="" || area==="" || retailerCode===""){
            alert("Please enter all details");
        }else{
            $.ajax({
                type: "POST",
                url:"<?php echo site_url('RetailerController/updateRetailerDetail');?>",
                data:{"retailerId":retailerId,"retailerName" : retailerName,"area":area,"retailerCode":retailerCode},
                success: function (data) {
                //   ('#recStatus').innerHTML=data
                  alert(data);
                    // $('#recStatus1').html(data);
                    window.location.href="<?php echo base_url();?>index.php/RetailerController";
                }  
            });
        }
    });
 </script>
