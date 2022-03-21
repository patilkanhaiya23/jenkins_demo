<?php $this->load->view('/layouts/commanHeader'); ?>
        <h1 style="display: none;">Welcome</h1><br/><br/><br/><br/>
     <section class="col-md-12 box" style="height: auto;overflow-y: scroll;">
        <div class="container-fluid">
          <!--   <div class="block-header">
                <h2>
                    Closed Allocations Master
                </h2>
            </div> -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                               Closed Allocations Master
                            </h2>
                        </div>
                        <div class="body">
                               <br>
                            <div class="top-panel">
                              <div class="btn-group pull-right">
                                <button type="button" class="btn btn-primary btn-lg dropdown-toggle" data-toggle="dropdown">Export <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                  <!-- <li><a class="dataExport" data-type="csv">CSV</a></li> -->
                                  <li><a class="dataExport" data-type="excel">XLS</a></li>          
                                </ul>
                              </div>
                            </div>
                            <div class="table-responsive">
                              <div class="row">
                                <div class="col-sm-3">
                                  <b>Search Anything</b>
                                  <div class="form-group">
                                    <div class="form-line">
                                       <input type="text" name="searchFor" placeholder="Search..." class="form-control" id="searchKey" onchange="sendRequest();">
                                    </div>
                                  </div>
                                </div>

                                <div class="col-sm-3">
                                  <b>Range</b>
                                  <div class="form-group">
                                    <select class="form-control" id="limitRows" onchange="sendRequest();">
                                      <option value="100">100</option>
                                      <option value="500">500</option>
                                      <option value="1000">1000</option>
                                      <option value="2000">2000</option>
                                      <option value="5000">5000</option>
                                      <option value="10000">10000</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="col-sm-3">
                                  <a href="<?php echo site_url('AllocationByManagerController/closedAllocations'); ?>" class="btn btn-sm m-t-15 btn-primary waves-effect">
                                        <i class="material-icons">cancel</i> 
                                        <span class="icon-name"> Cancel</span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- <div class="table-responsive"> -->
                                <?php echo $pagination; ?>
                                <table id="outstanding_table" style="font-size: 11px" class="table table-bordered table-striped " data-page-length='100'>
                                    <thead>
                                        <tr>
                                            <th>Sr.No</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Allocation</th>
                                            <th data-action="sort" data-title="date" data-direction="ASC">Date</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Route</th>
                                            <th class="MayBeLongColumn" data-action="sort" data-title="allocationCode" data-direction="ASC">Salesman</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Deliveryman</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Reference</th>
                                            <th data-action="sort" data-title="totalCashAmt" data-direction="ASC">Total Cash Amt</th>
                                            <th data-action="sort" data-title="totalChequeNeftAmt" data-direction="ASC">Total Cheque/NEFT Amt</th>
                                            <th data-action="sort" data-title="totalSRAmt" data-direction="ASC"> Total SR Amt</th>
                                           
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Sr.No</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Allocation</th>
                                            <th data-action="sort" data-title="date" data-direction="ASC">Date</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Route</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Salesman</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Deliveryman</th>
                                            <th data-action="sort" data-title="allocationCode" data-direction="ASC">Reference</th>
                                            <th data-action="sort" data-title="totalCashAmt" data-direction="ASC">Total Cash Amt</th>
                                            <th data-action="sort" data-title="totalChequeNeftAmt" data-direction="ASC">Total Cheque/NEFT Amt</th>
                                            <th data-action="sort" data-title="totalSRAmt" data-direction="ASC"> Total SR Amt</th>
                                           
                                        </tr>
                                    </tfoot>
                                    <tbody>
                               
                                        <?php
                                        $no=0;
                                        foreach ($allocations as $data) 
                                          {
                                               $no++; 
                                               $routeName='';
                                               $rtName=explode(",",rtrim($data['routeCode'],','));
                                               for($i=0;$i<count($rtName);$i++){
                                                    $routes=$this->AllocationByManagerModel->getRouteName($rtName[$i]);
                                                    if(!empty($routes)){
                                                        $routeName=$routeName.' '.$routes[0]['name'].', ';
                                                    }
                                               }

                                                $employee="";
                                                if(($data['fieldStaffCode1'] !='0')){
                                                    $emp=$this->AllocationByManagerModel->getEmployeeNamesByID($data['fieldStaffCode1']);
                                                    $employee= $employee.$emp.', ';
                                                }
                                                if(($data['fieldStaffCode2'] !='0')){
                                                    $emp=$this->AllocationByManagerModel->getEmployeeNamesByID($data['fieldStaffCode2']);
                                                    $employee=$employee.$emp.', ';
                                                }
                                             
                                           ?>
                                        <tr>
                                            <td><?php echo $no; ?></td>
                                            
                                            <td>
                                                <a href="<?php echo base_url().'index.php/AllocationByManagerController/CloseCompleteAllocation/'.$data['id']; ?>"><?php echo $data['allocationCode']; ?></a>
                                            </td>
                                            <?php
                                                $dt=date_create($data['date']);
                                                $date = date_format($dt,'d-M-Y');
                                            ?>
                                             <td><?php echo $date; ?></td>
                                             <td><?php echo rtrim($data['allocationRouteName'],', '); ?></td> 
                                            <td><?php echo rtrim($data['allocationEmployeeName'],', '); ?></td>
                                            <td><?php echo rtrim($data['allocationSalesman'],', '); ?></td>
                                            
                                           <!--  <td><?php echo rtrim($routeName,', '); ?></td>
                                            <td></td> 
                                            <td><?php echo rtrim($employee,', '); ?></td> -->
                                            <td><?php echo $data['reference']; ?></td> 
                                            <td align="right"><?php echo number_format($data['totalCashAmt']); ?></td>
                                            <td align="right"><?php echo number_format($data['totalChequeNeftAmt']); ?></td>
                                            <td align="right"><?php echo number_format($data['totalSRAmt']); ?></td>
                                        </tr>
                                    <?php
                                        }
                                      ?> 
                                    </tbody>
                                </table>
                                <?php echo $pagination; ?>
                            <!-- </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $this->load->view('/layouts/footerDataTable'); ?>


<script type="text/javascript">
    var sendRequest = function(){
      // var curOrderField = "BillNo";
      // var curOrderDirection = "ASC";
      var searchKey = $('#searchKey').val();
      var limitRows = $('#limitRows').val();
      window.location.href = '<?=base_url('index.php/AllocationByManagerController/closedAllocations')?>?query='+searchKey+'&limitRows='+limitRows+'&orderField='+curOrderField+'&orderDirection='+curOrderDirection;
    }

    var getNamedParameter = function (key) {
            if (key == undefined) return false;
            var url = window.location.href;
            var path_arr = url.split('?');
            if (path_arr.length === 1) {
                return null;
            }
            path_arr = path_arr[1].split('&');
            path_arr = remove_value(path_arr, "");
            var value = undefined;
            for (var i = 0; i < path_arr.length; i++) {
                var keyValue = path_arr[i].split('=');
                if (keyValue[0] == key) {
                    value = keyValue[1];
                    break;
                }
            }
            return value;
        };

        var remove_value = function (value, remove) {
            if (value.indexOf(remove) > -1) {
                value.splice(value.indexOf(remove), 1);
                remove_value(value, remove);
            }
            return value;
        };

        var curOrderField, curOrderDirection;
        $('[data-action="sort"]').on('click', function(e){
          curOrderField = $(this).data('title');
          curOrderDirection = $(this).data('direction');
          // curOrderField = "BillNo";
          // curOrderDirection = "ASC";
          sendRequest();
        });

        $('#searchKey').val(decodeURIComponent(getNamedParameter('query')||""));
        $('#limitRows option[value="'+getNamedParameter('limitRows')+'"]').attr('selected', true);

        var curOrderField = getNamedParameter('orderField')||"";
        var curOrderDirection = getNamedParameter('orderDirection')||"";
        var currentSort = $('[data-action="sort"][data-title="'+getNamedParameter('orderField')+'"]');
        if(curOrderDirection=="ASC"){
          currentSort.attr('data-direction', "DESC").find('i.glyphicon').removeClass('glyphicon-triangle-bottom').addClass('glyphicon-triangle-top'); 
        }else{
          currentSort.attr('data-direction', "ASC").find('i.glyphicon').removeClass('glyphicon-triangle-top').addClass('glyphicon-triangle-bottom');  
        }

  </script>

  <script type="text/javascript">
    $( document ).ready(function() {
      $(".dataExport").click(function() {
        var exportType = $(this).data('type');  
        $('#outstanding_table').tableExport({
          type : exportType, 
          select: true,  
          escape : 'false',
          ignoreColumn: [7]
        });   
      });
    });

  </script>
