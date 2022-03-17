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
                                    <h4 class="page-title">Transactions</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                            <select class="form-control select2" data-toggle="select2" id="vendor">
                                                <option value="all">All Vendors</option>
                                                @foreach($vendors as $vendor)
                                                <option value="{{$vendor->vendor_id}}">{{$vendor->store_name}}</option>
                                                @endforeach
                                            </select>
                                            </div>
                                            <div class="col-sm-4">
                                            <div id="reportrange" class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light">
                                                <i class="mdi mdi-calendar"></i>&nbsp;
                                                <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                                            </div>
                                            </div>
                                            <div class="col-sm-4">
                                            <button type="button" class="btn btn-light" id="query_transaction" onclick="processQuery();">Query Transactions</button>
                                                <!-- <div class="text-sm-end">
                                                    <button type="button" class="btn btn-success mb-2 me-1"><i class="mdi mdi-cog"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div> -->
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
                        <div class="row" id="transaction_summary"></div>
                        <hr>
                        <div class="table-responsive" id="transaction_table">
                        <table id="user-table" class="table table-sm table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Tranx Ref</th>
                                    <th>Store Name</th>
                                    <th>Customer Name</th>
                                    <th>Customer Email</th>
                                    <th>Customer Phone</th>
                                    <th>Amount</th>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

       


        <!-- end demo js-->
<script>
var date_range = '<?php echo $dateRange; ?>';
load_summary();
displayUsers('all',date_range);
function load_summary()
{
    var date_range = '<?php echo $dateRange; ?>';
   // $('#apartment_list_spinner').css('display', 'block');
    $('#transaction_summary').load('ajax_files/transaction_summary?vendorId=all&dateRange='+date_range, 
        function () {
        //$('#apartment_list_spinner').css('display', 'none');
        });
}

function load_table()
{
    var date_range = '<?php echo $dateRange; ?>';
    $('#transaction_table').load('ajax_files/transaction_table?vendorId=all&dateRange='+date_range, 
        function () {
        //$('#apartment_list_spinner').css('display', 'none');
        });
}
function processQuery(){
    var vendor = $('#vendor').val();
    var date_range = $('#selectedValue').html();
    $('#filtered_date').html(date_range);
    var dateRange = date_range.trim().split(" ").join("%20");
    $('#transaction_summary').html('');
    $('#user-table').DataTable().destroy();
    $('#transaction_summary').load('ajax_files/transaction_summary?vendorId='+vendor+'&dateRange='+dateRange);
    displayUsers(vendor, dateRange);
}


function displayUsers(vendorId, date_range) {
    var table = $('#user-table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        processing: true,
        serverSide: true,
        "pageLength": 5,
        ajax: 'users/list?vendorId='+vendorId+'&dateRange='+date_range,
        columns: [
            {data: 'tranx_ref'},
            {data: 'store_name'},
            {data: 'first_name'},
            {data: 'email'},
            {data: 'phone'},
            {data: 'amount_payable'},
            {data: 'created_at'},
        ]
    }); 
}
    
    

</script>
</body>
</html>
