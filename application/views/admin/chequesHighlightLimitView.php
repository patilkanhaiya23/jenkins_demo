<?php $this->load->view('/layouts/header'); ?>

        <section class="content">
        <div class="container-fluid">
           
            <!-- Basic Examples -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                              Cheques Limit
                            </h2>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                              <p id="res"></p>
                                <table style="font-size: 14px" class="table" data-page-length='100'>
                                    <thead>
                                        <tr>
                                            <th>Days Limit For</th>
                                            <th>Days or Bills</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="sr_rep">
                                            <td><?php echo $days[5]['name']; ?></td>
                                            <td>
                                              <input type="text" id="cheque_one" class="form-control" value="<?php echo $days[5]['days']; ?>" style="width:100px">
                                            </td>
                                             <td>
                                                 <a id="chequeOne" data-id="" href="javascript:void();">
                                                    <button class="btn btn-primary waves-effect">
                                                        <i class="material-icons">save</i> 
                                                        <span class="icon-name"> Save</span>
                                                    </button>
                                                </a>                                   
                                            </td>
                                        </tr>
                                        <tr id="sr_rep">
                                            <td><?php echo $days[6]['name']; ?></td>
                                            <td>
                                              <input type="text" id="cheque_two" class="form-control" value="<?php echo $days[6]['days']; ?>" style="width:100px">
                                            </td>
                                             <td>
                                                 <a id="chequeTwo" href="javascript:void();">
                                                    <button class="btn btn-primary waves-effect">
                                                        <i class="material-icons">save</i> 
                                                        <span class="icon-name"> Save</span>
                                                    </button>
                                                </a>                                   
                                            </td>
                                        </tr>

                                         <tr id="sr_rep">
                                            <td><?php echo $days[7]['name']; ?></td>
                                            <td>
                                              <input type="text" id="cheque_three" class="form-control" value="<?php echo $days[7]['days']; ?>" style="width:100px">
                                            </td>
                                             <td>
                                                 <a id="chequeThree" href="javascript:void();">
                                                    <button class="btn btn-primary waves-effect">
                                                        <i class="material-icons">save</i> 
                                                        <span class="icon-name"> Save</span>
                                                    </button>
                                                </a>                                   
                                            </td>
                                        </tr>
                                        
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
        url: "<?php echo site_url('admin/PenaltyController/delete');?>",
        type: "post",
        data: {'id':id},
        success: function (response) {
         
          swal(response, {
            icon: "success",
          });
          var URL = "<?php echo site_url('admin/PenaltyController');?>";
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

<script>
     function numbersonly(myfield, e){
            var key;
            var keychar;
            if (window.event)
                key = window.event.keyCode;
            else if (e)
                key = e.which;
            else
                return true;

            keychar = String.fromCharCode(key);
            // control keys
            if ((key==null) || (key==0) || (key==8) || (key==9) || (key==13) || (key==27) )
                return true;

            // numbers
            else if ((("0123456789").indexOf(keychar) > -1))
                return true;

            // only one decimal point
            else if ((keychar == "."))
            {
                if (myfield.value.indexOf(keychar) > -1)
                    return false;
            }
            else
                return false;
    }
</script>


<script type="text/javascript">
    $(document).on('click','#chequeOne',function(){
        var days=$('#cheque_one').val();
         $.ajax({
            url : "<?php echo site_url('admin/SettingsController/updatedDaysLimit');?>",
            method : "POST",
            data : {id: '6',days:days},
            success: function(data){
              alert('Record Updated');
                location.reload(); 
            }
        });
    });

    $(document).on('click','#chequeTwo',function(){
        var days=$('#cheque_two').val();
         $.ajax({
            url : "<?php echo site_url('admin/SettingsController/updatedDaysLimit');?>",
            method : "POST",
            data : {id: '7',days:days},
            success: function(data){
              alert('Record Updated');
                location.reload(); 
            }
        });
    });

    $(document).on('click','#chequeThree',function(){
        var days=$('#cheque_three').val();
         $.ajax({
            url : "<?php echo site_url('admin/SettingsController/updatedDaysLimit');?>",
            method : "POST",
            data : {id: '8',days:days},
            success: function(data){
              alert('Record Updated');
                location.reload(); 
            }
        });
    });

    $("input[type='text']").on("click", function () {
       $(this).select();
    });
 </script>