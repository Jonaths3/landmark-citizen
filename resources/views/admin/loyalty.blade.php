<?php 
$dateRange1 = date("Y/m/d", time());
$dateRange2 = date("Y/m/d", time());
$dateRange = $dateRange1.'-'.$dateRange2;
 ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Sellers | Landmark Africa</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- third party css -->
        <link href="assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->

        <!-- App css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
    <style>
        .completed-badge {
            display: inline-block;
            min-width: 1em; /* em unit */
            padding: .1em; /* em unit */
            border-radius: 10%;
            font-size: 12px;
            text-align: center;
            background: #1779ba;
            color: #fefefe;
        }
    </style>
    </head>

    <body class="loading" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>
    
   
        
         <!-- Large modal -->
<div class="modal fade" id="bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Loyalty Settings</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
            <table class="table table-bordered border-primary table-centered mb-0">
    <thead>
        <tr>
            <th>Loyalty Classes</th>
            <th>Cashback (% of sale)</th>
            <th>Point (% of sale)</th>
            <th>Base Spend (₦)</th>
        </tr>
    </thead>
    <tbody>
        @csrf
        @foreach ($query as $value)
        <tr>
            
        <input type="hidden" class="form-control" name="id[]" value="{{$value->id}}">
            <td class="table-user">
               <input type="text" class="form-control" name="loyalty_class[]" value="{{$value->loyalty_class}}">
            </td>
            <td><input type="number" class="form-control" name="cashback[]" value="{{$value->percentage_cashback}}"></td>
            <td><input type="number" class="form-control" name="point[]" value="{{$value->percentage_points}}"></td>
            <td><input type="text" class="form-control" name="base_value[]" value="{{$value->min_point}}"> </td>
        </tr>
        @endforeach
    </tbody>
</table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="update-settings">Update Settings</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
        <!-- Begin page -->
        <div class="wrapper">
            <!-- ========== Left Sidebar Start ========== -->
            @include('admin.layout.includes.sidebar')
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    @include('admin.layout.includes.header')
                    <!-- end Topbar -->
                    
                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        
                                    </div>
                                    <h4 class="page-title">Loyalty & Rewards</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-3">
                                            <div id="reportrange" class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light">
                                                <i class="mdi mdi-calendar"></i>&nbsp;
                                                <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                                            </div>
                                            </div>
                                            <div class="col-sm-9">
                                            <button type="button" class="btn btn-light" onclick="processQuery();">Query Transactions</button>
                                                <div class="text-sm-end" style="float:right;">
                                                    <button type="button" class="btn btn-light mb-2" data-bs-toggle="modal" data-bs-target="#bs-example-modal-lg"><i class="mdi mdi-eye"></i> View Loyalty Settings</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>
                                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        
                                    </div>
                                    <h4 class="page-title" id="filtered_date">Today</h4>
                                </div>
                            </div>
                        </div>  
                        <div class="row" id="reward_summary"></div>
      <div class="table-responsive">
   <table id="user-table" class="table table-sm table-centered mb-0">
    <thead>
        <tr>
            <th>Tranx Ref</th>
            <th>Store Name</th>
            <th>Customer Name</th>
            <th>Amount Paid</th>
            <th>Point Earned</th>
            <th>Cashback Earned</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
                                           
                                        </div>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->
                        
                    </div> <!-- container -->

                </div> <!-- content -->

                <!-- Footer Start -->
                @include('admin.layout.includes.footer')
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

       
     

        <div class="rightbar-overlay"></div>
        <!-- /End-bar -->


        <!-- bundle -->
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>

        <!-- third party js -->
        <script src="assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="assets/js/vendor/apexcharts.min.js"></script>
        <script src="assets/js/vendor/dataTables.checkboxes.min.js"></script>
        <!-- third party js ends -->

<script src="assets/js/vendor/dataTables.buttons.min.js"></script>
<script src="assets/js/vendor/buttons.bootstrap4.min.js"></script>
<script src="assets/js/vendor/buttons.html5.min.js"></script>
<script src="assets/js/vendor/buttons.flash.min.js"></script>
<script src="assets/js/vendor/buttons.print.min.js"></script>
        <!-- end demo js-->
<script>
$(document).ready( function () {
    $('#vendor-table').DataTable();
} );
</script>

<script type="text/javascript">
 var date_range = '<?php echo $dateRange; ?>';
load_summary();
displayUsers(date_range);
function load_summary()
{
    var date_range = '<?php echo $dateRange; ?>';
   // $('#apartment_list_spinner').css('display', 'block');
    $('#reward_summary').load('ajax_files/reward_summary?dateRange='+date_range, 
        function () {
        //$('#apartment_list_spinner').css('display', 'none');
        });
}

function processQuery(){
    var date_range = $('#selectedValue').html();
    $('#filtered_date').html(date_range);
    var dateRange = date_range.trim().split(" ").join("%20");
    $('#reward_summary').html('');
    $('#user-table').DataTable().destroy();
    $('#reward_summary').load('ajax_files/reward_summary?dateRange='+dateRange);
    displayUsers(dateRange);
}

function displayUsers(date_range) {
    var table = $('#user-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: 'users/list_reward?dateRange='+date_range,
        columns: [
            {data: 'tranx_ref'},
            {data: 'store_name'},
            {data: 'first_name'},
            {data: 'amount_payable'},
            {data: 'point_earned'},
            {data: 'cashback_earned'},
            {data: 'created_at'},
        ]
    }); 
}


//Creating delete modal for vendors
$(document).on('click','#update-settings',function(){
                var id = $("input[name='id[]']").map(function(){return $(this).val();}).get();
                var loyalty_class  = $("input[name='loyalty_class[]']").map(function(){return $(this).val();}).get();
                var cashback  = $("input[name='cashback[]']").map(function(){return $(this).val();}).get();
                var point  = $("input[name='point[]']").map(function(){return $(this).val();}).get();
                var base_value  = $("input[name='base_value[]']").map(function(){return $(this).val();}).get();
                let _token = $('input[name=_token]').val();
                $('#vendor').html('Please wait...');
                $('#delete_id').val('');
                $.ajax({
                url: "{{route('loyalty-class.update')}}",
                type: "POST",
                data: {id:id, _token:_token, loyalty_class:loyalty_class, cashback:cashback, point:point, base_value:base_value},
                success: function(response)
                {
                    if (response == 'success') {
                        location.reload();
                    }
                }
            });
        });
</script>
    </body>
</html>
