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

    </head>

    <body class="loading" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>
    
    <div id="delete-warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-warning">
                <h4 class="modal-title" id="warning-header-modalLabel">Warning - Sensitive Action</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <div id="vendor"></div>
            </div>
            <div class="modal-footer">
            <input type="hidden" id="delete_id">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="disable-account">Disable Account</button>
                <button type="button" class="btn btn-danger" id="delete-account">Delete Account</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

        <div class="modal fade" id="edit-vendor" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Vendor Account Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <div class="modal-body">
                    <form class="form-horizontal" id="update-vendor">
                    @csrf
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Store Name</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="edit_store_name" placeholder="Enter store name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Contact Name</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="edit_contact_name" placeholder="Enter contact person" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-3 col-form-label">Contact Email</label>
                            <div class="col-9">
                                <input type="email" class="form-control" id="edit_contact_email" placeholder="Contact person's email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Phone</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="edit_contact_phone" placeholder="Contact person's phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Sales Rent (%)</label>
                            <div class="col-9">
                                <input type="number" class="form-control" step='0.01' id="edit_sales_rent" placeholder="% value of vendors sales rent" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Loyalty Discount (%)</label>
                            <div class="col-9">
                                <input type="number" class="form-control" id="edit_loyalty_discount" placeholder="negotiated discount with this vendor in %" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Verifying Bank</label>
                            <div class="col-9">
                                <select class="form-control" id="edit_verify_bank">
                                @foreach ($bank_list as $banks)
                                    <option value="{{$banks['code']}}">{{$banks['name']}}</opiton>
                               @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Account Number</label>
                            <div class="col-9">
                                <div class="input-group">
                                    <input type="number" class="form-control" placeholder="Vendor's account number" id="edit_account_no">
                                    <div class="input-group-append">
                                        <button class="btn btn-dark edit_verify_account" id="edit_verify_account" type="button">Verify</button>
                                        <button class="btn btn-dark" id="edit_verify_loader" style="display:none;" type="button" disabled>
                                            <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                                            Verifying...
                                        </button>
                                        <button class="btn btn-danger edit_verify_account" id="edit_invalid_verify_account" style="display:none;" type="button">
                                            Invalid Account - Click to try again
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Resolved Account</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="edit_resolved_account_no" disabled required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Account Name</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="edit_account_name" disabled required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Bank Name</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="edit_bank_name" disabled required>
                            </div>
                        </div>
                        <input type="hidden" class="form-control" id="edit_bank_code">
                        <div class="justify-content-end row">
                            <div class="col-9">
                                <button type="submit" class="btn btn-info" id="update_vendor">Update Vendor</button>
                                <button  data-bs-dismiss="modal" class="btn btn-light">Close</button>
                            </div>
                        </div>
                        <input type="hidden" id="edit_id">
                </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- Large modal -->
       <div class="modal fade" id="new-vendor" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Create Vendor Account</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <div class="modal-body">
                    <form class="form-horizontal" id="register-vendor">
                    @csrf
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Store Name</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="store_name" placeholder="Enter store name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Contact Name</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="contact_name" placeholder="Enter contact person" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-3 col-form-label">Contact Email</label>
                            <div class="col-9">
                                <input type="email" class="form-control" id="contact_email" placeholder="Contact person's email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Phone</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="contact_phone" placeholder="Contact person's phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Sales Rent (%)</label>
                            <div class="col-9">
                                <input type="number" class="form-control" step='0.01' id="sales_rent" placeholder="% value of vendors sales rent" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Loyalty Discount (%)</label>
                            <div class="col-9">
                                <input type="number" class="form-control" id="loyalty_discount" placeholder="negotiated discount with this vendor in %" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Verifying Bank</label>
                            <div class="col-9">
                                <select class="form-control" id="verify_bank" required>
                                @foreach ($bank_list as $bank)
                                    <option value="{{$bank['code']}}">{{$bank['name']}}</opiton>
                               @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Account Number</label>
                            <div class="col-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Vendor's account number" id="account_no" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-dark verify_account" id="verify_account" type="button">Verify</button>
                                        <button class="btn btn-dark" id="verify_loader" style="display:none;" type="button" disabled>
                                            <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                                            Verifying...
                                        </button>
                                        <button class="btn btn-danger verify_account" id="invalid_verify_account" style="display:none;" type="button">
                                            Invalid Account - Click to try again
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Resolved Account</label>
                            <div class="col-9">
                                <input type="number" class="form-control" id="resolved_account_no" disabled required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Account Name</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="account_name" disabled required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">Bank Name</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="bank_name" disabled required>
                            </div>
                        </div>
                        <input type="hidden" class="form-control" id="bank_code">

                        <div class="justify-content-end row">
                            <div class="col-9">
                                <button type="submit" class="btn btn-info" id="register_vendor">Register Vendor</button>
                                <button  data-bs-dismiss="modal" class="btn btn-light">Close</button>
                            </div>
                        </div>
                </form>
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
                                    <h4 class="page-title">Vendors</h4>
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
                                                <a href="javascript:void(0);" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#new-vendor"><i class="mdi mdi-plus-circle me-2"></i> Add Vendor</a>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-end">
                                                    <button type="button" class="btn btn-success mb-2 me-1"><i class="mdi mdi-cog"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>
                
                                        <div class="table-responsive">
                                        <table id="vendor-table" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Contact</th>
                                                    <th>Store Name</th>
                                                    <th>No of Tranx</th>
                                                    <th>Tranx Value</th>
                                                    <th>Created Date</th>
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
$(document).ready( function () {
    var table = $('#vendor-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: 'vendor/list',
        columns: [
            {data: 'contact_name'},
            {data: 'store_name'},
            {data: 'no_of_tranx'},
            {data: 'tranx_value'},
            {data: 'created_at'},
            {data: 'action'},
        ]
    }); 
} );

</script>

<script type="text/javascript">
        $("form#register-vendor").submit(function(e) {
		e.preventDefault();  
        let store_name = $('#store_name').val();
        let contact_name = $('#contact_name').val();
        let contact_email = $('#contact_email').val();
        let contact_phone = $('#contact_phone').val();
        let sales_rent = $('#sales_rent').val();
        let loyalty_discount = $('#loyalty_discount').val();
        let account_no = $('#resolved_account_no').val();
        let account_name = $('#account_name').val();
        let bank_name = $('#bank_name').val();
        let bank_code = $('#bank_code').val();
        let _token = $('input[name=_token]').val();
        $('#register_vendor').html('Please wait...');
		    var formData = new FormData(this);
            $.ajax({
                url: "{{route('vendor.add')}}",
                type: 'POST',
                data: {loyalty_discount:loyalty_discount, sales_rent:sales_rent, account_no:account_no, account_name:account_name, bank_name:bank_name, bank_code:bank_code,_token:_token, store_name:store_name, contact_name:contact_name, contact_email:contact_email, contact_phone:contact_phone},
                success: function (data) {
                    if (data == 'success') {
                        location.reload();
                    }
                    else {
                        alert(data);
                        $('#register_vendor').html('Register Vendor');
                    } 
                }
            });
        });
    
        //Creating Edit modal for vendors
        $(document).on('click','.edit-vendor',function(e){
                e.preventDefault();
                var id = $(this).data('id');
                let _token = $('input[name=_token]').val();
                $('#edit_store_name').val('Fetching records - please wait...');
                $('#edit_contact_name').val('Fetching records - please wait...');
                $('#edit_contact_email').val('Fetching records - please wait...');
                $('#edit_contact_phone').val('Fetching records - please wait...');
                $('#edit_account_no').val('Fetching records - please wait...');
                $('#edit_sales_rent').val('Fetching records - please wait...');
                $('#edit_loyalty_discount').val('Fetching records - please wait...');
                $('#edit_resolved_account_no').val('Fetching records - please wait...');
                $('#edit_account_name').val('Fetching records - please wait...');
                $('#edit_bank_name').val('Fetching records - please wait...');
                $('#edit_bank_code').val('Fetching records - please wait...');
                $('#edit_id').val('');
                $.ajax({
                url: "{{route('view-vendor.add')}}",
                type: "POST",
                data: {id:id, _token:_token},
                success: function(response)
                {
                if (response) {
                    $('#edit_store_name').val(response[0].store_name);
                    $('#edit_contact_name').val(response[0].contact_name);
                    $('#edit_contact_email').val(response[0].contact_email);
                    $('#edit_contact_phone').val(response[0].contact_phone);
                    $('#edit_sales_rent').val(response[0].sales_rent);
                    $('#edit_loyalty_discount').val(response[0].loyalty_discount);
                    $('#edit_account_no').val(response[0].account_no);
                    $('#edit_resolved_account_no').val(response[0].account_no);
                    $('#edit_account_name').val(response[0].account_name);
                    $('#edit_bank_name').val(response[0].bank_name);
                    $('#edit_bank_code').val(response[0].bank_code);
                    $('#edit_id').val(id);
                }
                }
            });
        });

        //Creating delete modal for vendors
        $(document).on('click','.delete-vendor',function(e){
                e.preventDefault();
                var id = $(this).data('id');
                let _token = $('input[name=_token]').val();
                $('#vendor').html('Please wait...');
                $('#delete_id').val('');
                $.ajax({
                url: "{{route('view-vendor.add')}}",
                type: "POST",
                data: {id:id, _token:_token},
                success: function(response)
                {
                    if (response) {
                        $('#vendor').html('You are about to perform a very sensitive operation on the vendor account - <strong>'+response[0].store_name+'</strong>');
                        $('#delete_id').val(id);
                    }
                }
            });
        });

        //Deleting vendors
        $(document).on('click','#delete-account',function(e){
                e.preventDefault();
                var id = $('#delete_id').val();
                let _token = $('input[name=_token]').val();
                $.ajax({
                url: "{{route('vendor.remove')}}",
                type: "POST",
                data: {id:id, _token:_token},
                success: function(response)
                {
                    if (response == 'success') {
                        location.reload();
                    }
                    else {
                        alert(response)
                    }
                }
            });
        });

         //Disabling vendors
         $(document).on('click','#disable-account',function(e){
                e.preventDefault();
                var id = $('#delete_id').val();
                let _token = $('input[name=_token]').val();
                $.ajax({
                url: "{{route('vendor.disable')}}",
                type: "POST",
                data: {id:id, _token:_token},
                success: function(response)
                {
                    if (response) {
                        location.reload();
                    }
                }
            });
        });

        //Disabling vendors
        $(document).on('click','#enable-account',function(e){
                e.preventDefault();
                var id = $('#delete_id').val();
                let _token = $('input[name=_token]').val();
                $.ajax({
                url: "{{route('vendor.enable')}}",
                type: "POST",
                data: {id:id, _token:_token},
                success: function(response)
                {
                    if (response) {
                        location.reload();
                    }
                }
            });
        });

        $("form#update-vendor").submit(function(e) {
		    e.preventDefault();  
        let id = $('#edit_id').val();
        let store_name = $('#edit_store_name').val();
        let contact_name = $('#edit_contact_name').val();
        let contact_email = $('#edit_contact_email').val();
        let contact_phone = $('#edit_contact_phone').val();
        let sales_rent = $('#edit_sales_rent').val();
        let loyalty_discount = $('#edit_loyalty_discount').val();
        let account_no = $('#edit_account_no').val();
        let account_name = $('#edit_account_name').val();
        let bank_name = $('#edit_bank_name').val();
        let bank_code = $('#edit_bank_code').val();
        let _token = $('input[name=_token]').val();
        $('#update_vendor').html('Please wait...');
            $.ajax({
                url: "{{route('vendor.update')}}",
                type: 'POST',
                data: {sales_rent:sales_rent, loyalty_discount:loyalty_discount, account_no:account_no, account_name:account_name, bank_name:bank_name, bank_code:bank_code, _token:_token, id:id, store_name:store_name, contact_name:contact_name, contact_email:contact_email, contact_phone:contact_phone},
                success: function (data) {
                    if (data == 'success') {
                        location.reload();
                    }
                    else {
                        alert(data);
                        $('#update_vendor').html('Update Vendor');
                    }  
                }
            });
        });

         //Verifying vendor accounts
         $(document).on('click','.edit_verify_account',function(e){
                e.preventDefault();
                $('#edit_verify_account').css('display', 'none');
                $('#edit_invalid_verify_account').css('display', 'none');
                $('#edit_verify_loader').css('display', 'block');
                $('#edit_resolved_account_no').val('');
                $('#edit_account_name').val('');
                $('#edit_bank_name').val('');
                $('#edit_bank_code').val('');
                let account_no = $('#edit_account_no').val();
                let verify_bank = $('#edit_verify_bank').val();
                let bank_name = $( "#edit_verify_bank option:selected" ).text();
                let _token = $('input[name=_token]').val();
                $.ajax({
                url: "{{route('verify-vendor-account.add')}}",
                type: "POST",
                data: {verify_bank:verify_bank, account_no:account_no, _token:_token},
                success: function(response)
                {
                    var json = $.parseJSON(response);
                    if (response) {
                        if (json.status == false) {
                            $('#edit_verify_loader').css('display', 'none');
                            $('#edit_invalid_verify_account').css('display', 'block');
                        }
                        else {
                                $('#edit_resolved_account_no').val(json.data.account_number);
                                $('#edit_account_name').val(json.data.account_name);
                                $('#edit_bank_name').val(bank_name);
                                $('#edit_bank_code').val(verify_bank);
                                $('#edit_verify_loader').css('display', 'none');
                                $('#edit_verify_account').css('display', 'block');
                        }
                        
                    }
                }
            });
        });

        //Verifying vendor accounts
        $(document).on('click','.verify_account',function(e){
                e.preventDefault();
                $('#verify_account').css('display', 'none');
                $('#invalid_verify_account').css('display', 'none');
                $('#verify_loader').css('display', 'block');
                $('#resolved_account_no').val('');
                $('#account_name').val('');
                $('#bank_name').val('');
                $('#bank_code').val('');
                let account_no = $('#account_no').val();
                let verify_bank = $('#verify_bank').val();
                let bank_name = $( "#verify_bank option:selected" ).text();
                let _token = $('input[name=_token]').val();
                $.ajax({
                url: "{{route('verify-vendor-account.add')}}",
                type: "POST",
                data: {verify_bank:verify_bank, account_no:account_no, _token:_token},
                success: function(response)
                {
                    var json = $.parseJSON(response);
                    if (response) {
                        if (json.status == false) {
                            $('#verify_loader').css('display', 'none');
                            $('#invalid_verify_account').css('display', 'block');
                        }
                       else {
                            $('#resolved_account_no').val(json.data.account_number);
                            $('#account_name').val(json.data.account_name);
                            $('#bank_name').val(bank_name);
                            $('#bank_code').val(verify_bank);
                            $('#verify_loader').css('display', 'none');
                            $('#verify_account').css('display', 'block');
                       }
                    }
                }
            });
        });
        </script>
    </body>
</html>
