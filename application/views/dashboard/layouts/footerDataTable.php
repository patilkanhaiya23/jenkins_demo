
      <div class="modal fade" id="empProDetails" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><center>Employee Details</center></h4>
          </div>
          <div class="modal-body">
         
          </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="updatePasswordlimitModal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
            <h4 class="modal-title">Forgot Password?</h4>
          </div>
          <div id="up_limitid" class="modal-body">
            <!-- <form method="post" action="<?php echo site_url('UserAuthentication/createPassword'); ?>" role="form"> -->
                <div class="row clearfix">
                <div class="col-md-12">
                    
                     <div class=" input-group">
                            <span class="input-group-addon">
                                <i class="material-icons">person</i> 
                            </span>
                            <div class="col-md-4 form-line">
                                <input type="text" onkeypress="return numbersonly(this, event);" class="form-control" id="mobile" name="mobile" placeholder="Enter registered mobile number" required>
                            </div>
                            
                        </div>
                         <p id='mbl' style="color:blue"></p>
                    </div>
                    <div class="row">
                            <div class="col-xs-4">
                                <button id="sbmt_id" class="btn btn-block bg-pink waves-effect" >Send password</button>
                            </div>
                        </div>
                </div>
            <!-- </form> -->
          </div>
      </div>
    </div>
  </div>
<!-- Jquery Core Js -->
    <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js');?>"></script>

    <!-- Bootstrap Core Js -->
    <script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.j');?>s"></script>

    <!-- Select Plugin Js -->
    <script src="<?php echo base_url('assets/pages/maps/jvectormap.html');?>"></script>

    <!-- Select Plugin Js -->
    <script src="<?php echo base_url('assets/plugins/bootstrap-select/js/bootstrap-select.js');?>"></script>

    <!-- Slimscroll Plugin Js -->
    <script src="<?php echo base_url('assets/plugins/jquery-slimscroll/jquery.slimscroll.js');?>"></script>

    <!-- Waves Effect Plugin Js -->
    <script src="<?php echo base_url('assets/plugins/node-waves/waves.js');?>"></script>

    <!-- Sparkline Chart Plugin Js -->
    <script src="<?php echo base_url('assets/plugins/jquery-sparkline/jquery.sparkline.js');?>"></script>

    <!-- Jquery DataTable Plugin Js -->
 
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/jquery.dataTables.js');?>"></script>
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js');?>"></script>
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js');?>"></script>
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/extensions/export/buttons.flash.min.js');?>"></script>
<!-- 
    <script src=" https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script> -->
   
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/extensions/export/jszip.min.js');?>"></script>
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/extensions/export/pdfmake.min.js');?>"></script>
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/extensions/export/vfs_fonts.js');?>"></script>
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/extensions/export/buttons.html5.min.js');?>"></script>
    <script src="<?php echo base_url('assets/plugins/jquery-datatable/extensions/export/buttons.print.min.js');?>"></script>

    <!-- Custom Js -->
    <script src="<?php echo base_url('assets/js/admin.js');?>"></script>
    <script src="<?php echo base_url('assets/js/pages/tables/jquery-datatable.js');?>"></script>

    <!-- Demo Js -->
    <script src="<?php echo base_url('assets/js/demo.js');?>"></script>
     <!-- SweetAlert Plugin Js -->
    <script src="<?php echo base_url('assets/plugins/bootstrap-notify/bootstrap-notify.js');?>"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> <script src="<?php echo base_url('assets/colorbox-master/jquery.colorbox-min.js');?>"></script>
   <script type="text/javascript">
      $(document).ready(function(){
        $(".iframe").colorbox({iframe:true, width:"80%", height:"80%"});
    });
   </script>


   <script type="text/javascript">
    $(document).on('click','#emp_pro_det_id',function(){
         var id=$(this).attr('data-id');
         $.ajax({
            url : "<?php echo site_url('admin/EmployeeController/empDetails');?>",
            method : "POST",
            data : {id: id},
            success: function(data){
                $('.modal-body').html(data);
            }
        });
    });

 </script>
