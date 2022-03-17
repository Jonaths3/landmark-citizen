
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
                <h4 class="modal-title" id="myLargeModalLabel">FAQ</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                @csrf
            <input type="text" id="question" class="form-control" placeholder="Enter question here."><br>
            <textarea class="form-control" id="answer" placeholder="Enter response here."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="add-faq">Add FAQ</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Edit Modal -->
         <!-- Large modal -->
         <div class="modal fade" id="bs-example-modal-lg-edit" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Edit FAQ</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                @csrf
            <input type="text" id="edit-question" class="form-control" placeholder="Please wait..."><br>
            <textarea class="form-control" id="edit-answer" placeholder="Please wait..."></textarea>
            <input type="hidden" id="edit-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="update-faq">Update FAQ</button>
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
                                    <h4 class="page-title">FAQs</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        
                      
                                        <div class="table-responsive">
                                        <a href="javascript:void(0);" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#bs-example-modal-lg"><i class="mdi mdi-plus-circle me-2"></i> New FAQ</a>
   
                                 <table id="user-table" class="table table-sm table-centered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Question</th>
                                                    <th>Answer</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($faq_list as $faq)
                                                <tr>
                                                    <td>{{$faq->question}}</td>
                                                    <td>{{$faq->answer}}</td>
                                                    <td><a href="javascript:void(0);" class="action-icon edit-faq" data-bs-toggle="modal" data-bs-target="#bs-example-modal-lg-edit" data-id="{{$faq->id}}"><i class="mdi mdi-eye"></i></a><a href="javascript:void(0);" class="action-icon delete-faq" data-id="{{$faq->id}}"> <i class="mdi mdi-delete"></i></a></td>
                                                </tr>
                                            @endforeach
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

<!-- Adding faq -->
<script>
    //Adding faqs
        $(document).on('click','#add-faq',function(){
                var question = $('#question').val();
                var answer  = $('#answer').val();
                let _token = $('input[name=_token]').val();
                $('#add-faq').html('Please wait...');
                $.ajax({
                url: "{{route('faq.add')}}",
                type: "POST",
                data: {question:question, _token:_token, answer:answer},
                success: function(response)
                {
                    if (response == 'success') {
                        location.reload();
                    }
                }
            });
        });

      //Creating Edit modal for faqs
      $(document).on('click','.edit-faq',function(e){
                e.preventDefault();
                $('#edit-answer').val('');
                $('#edit-question').val('');
                var id = $(this).data('id');
                let _token = $('input[name=_token]').val();
                $.ajax({
                url: "{{route('faq.show')}}",
                type: "POST",
                data: {id:id, _token:_token},
                success: function(response)
                {
                if (response) {
                    $('#edit-answer').val(response[0].answer);
                    $('#edit-question').val(response[0].question);
                    $('#edit-id').val(id);
                }
                }
            });
        });  

        //Updating faqs
        $(document).on('click','#update-faq',function(){
                var question = $('#edit-question').val();
                var answer  = $('#edit-answer').val();
                var id  = $('#edit-id').val();
                let _token = $('input[name=_token]').val();
                $('#update-faq').html('Please wait...');
                $.ajax({
                url: "{{route('faq.update')}}",
                type: "POST",
                data: {question:question, _token:_token, answer:answer, id:id},
                success: function(response)
                {
                    if (response == 'success') {
                        location.reload();
                    }
                }
            });
        });

         //Delete faq
      $(document).on('click','.delete-faq',function(){
                var id = $(this).data('id');
                let _token = $('input[name=_token]').val();
                $.ajax({
                url: "{{route('faq.delete')}}",
                type: "POST",
                data: {id:id, _token:_token},
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
