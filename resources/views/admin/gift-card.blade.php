<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Sellers | Landmark Africa</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
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
                                    <h4 class="page-title">Gift Cards</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        <div id="new-card-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    @csrf
                                    <div class="modal-header modal-colored-header bg-dark">
                                        <h4 class="modal-title" id="warning-header-modalLabel">Generate Gift Cards</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Enter the gift card value and quantity you wish to generate</p>
                                    <div class="row g-2">
                                        <div class="mb-3 col-md-6">
                                            <input type="number" class="form-control" id="amount" placeholder="Amount">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <input type="number" class="form-control" id="quantity" placeholder="Quantity">
                                        </div>
                                    </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="generate-card">Generate Gift Cards</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <a href="javascript:void(0);" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#new-card-modal"><i class="mdi mdi-plus-circle me-2"></i> Create Gift Cards</a>
                                            </div>
                                            <div class="col-sm-8">
                                               
                                            </div><!-- end col-->
                                        </div>
                
                                        <div class="table-responsive">
                                        <table id="gift-card-table" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Card Number</th>
                                                    <th>Amount</th>
                                                    <th>Date Created</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
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

// Generate Gift Cards
$(document).on("click", "#generate-card", function(){
            var amount = $('#amount').val();
            var quantity = $('#quantity').val();
            let _token = $('input[name=_token]').val();
            $.ajax({
                url: "{{route('generate.cards')}}",
                type: "POST",
                data: {amount:amount, quantity:quantity, _token:_token},
                success: function(data){
                    if(data == 'success') {
                        location.reload();
                    }
                }
            })
        
});

$(document).ready( function () {
    var table = $('#gift-card-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: 'giftcard/list',
        columns: [
            {data: 'card_no'},
            {data: 'funded_amount'},
            {data: 'created_at'},
            {data: 'status'},
            {data: 'action'},
        ]
    }); 
});

</script>

    </body>
</html>
