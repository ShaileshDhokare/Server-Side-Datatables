@extends('layouts.app')
@section('content')
<div class="container-fluid">
    @if(session()->get('success'))
    <div class="alert alert-success my-2">
        {{ session()->get('success') }}
    </div>
    @endif
    @if(session()->get('error'))
        <div class="alert alert-danger my-2">
            {{ session()->get('error') }}
        </div>
    @endif
    <div class="alert alert-success my-2" id="delete-msg" style="display:none;">
        
    </div>
    <header class="mt-2 p-3 shadow text-primary text-center">
        <h2>Report Sample Sharing Portal</h2>
    </header>
    <div class="clearfix m-3"></div>
    <table class="table table-bordered table-hover" id="report-samples">
        <thead>
        <tr>
            <th>Id</th>
            <th>Report Title</th>
            <th>Report Code</th>
            <th>Category</th>
            <th>Report Type</th>
            <th>Uploaded By</th>
            <th>Date</th>
            <th width="10%">Download</th>
        </tr>
        </thead>
    </table>
    <div class="modal fade text-dark" id="contact-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-center" id="contactModalTitle">Update Report Sample</h4>
                </div>
                <form action="{{ url('/update-sample')}} " method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="version_id" id="version_id">
                        <div class="form-group">
                            <label>Report Title:</label>
                            <input type="text" class="form-control form-control-sm" name="amr_title" id="amr_title" readonly>
                        </div>
                        <div class="form-group">
                            <label>Sample Code:</label>
                            <input type="text" class="form-control form-control-sm" name="amr_sample_code" id="amr_sample_code" readonly>
                        </div>
                        <div class="form-group">
                            <label>Sample Version:</label>
                            <input type="text" class="form-control form-control-sm" name="version" id="version" readonly>
                        </div>
                        <div class="form-group">
                            <label>Published Date:</label><span class="text-muted ml-2" id="amr_published_date"></span>
                            <input type="date" class="form-control col-md-8" name="amr_published_date">
                        </div>
                        <div class="form-group">
                            <label>Report Sample File:</label><span class="text-muted ml-2" id="sample_filename"></span>
                            <input type="file" class="form-control" name="sample_filename">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <input type="submit" value="Update" name="upload-sample" class="btn btn-primary w-25">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade text-dark" id="delete-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Do you really want to delete this Sample?</h5>
                </div>
                <div class="modal-footer justify-content-end">
                    <button onclick="deleteSample('delete-modal');" class="btn btn-sm btn-success">Yes</button>
                    <button onclick="closeModal('delete-modal')" class="btn btn-sm btn-danger ml-2">No</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade text-dark" id="activate-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h5>Do you really want to activate this Sample?</h5>
                </div>
                <div class="modal-footer justify-content-end">
                    <button onclick="ActivateSample('activate-modal');" class="btn btn-sm btn-success">Yes</button>
                    <button onclick="closeModal('activate-modal')" class="btn btn-sm btn-danger ml-2">No</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade text-dark" id="versions-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Select Version:</h5>
                    <button onclick="closeModal('versions-modal')" class="btn btn-sm btn-danger float-right">X</button>
                </div>
                <div class="modal-body" id="versions-body">
                    
                </div>
            </div>
        </div>
    </div>   
</div>

@endsection
@section('custom-script')
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        var sampleDeleteId = 0;
        var sampleActivateId = 0;
        var baseUrl = "{{ url('/')}}";

        $(document).ready(function(){
            getAllSamples();
            // setTimeout(function(){
            //     var pagination = $('#report-samples_paginate').html();
            //     console.log(pagination);
            // },2000);
             
        })

        function getAllSamples(){
            var dataTable=$('#report-samples').DataTable({
                "processing": true,
                "serverSide":true,
                "ajax":{
                    headers:
                    {
                        'X-CSRF-Token':"{{csrf_token()}}"
                    },
                    url:"{{ url('/all-samples')}}",
                    type:"post"
                },
                "columnDefs": [
                { 
                    "targets": [ 7, 6, 5, 4, 3, 2 ], //first column / numbering column
                    "orderable": false, //set not orderable
                }]
            });
            
        }

        function editSample(id){
            $.ajax({
                headers:
                {
                    'X-CSRF-Token': "{{csrf_token()}}"
                },
                type: "POST",
                url:"{{ url('/get-versions') }}",
                data: {
                    sampleId : id
                },
                success:function(response){
                    $('#versions-body').html('');
                    var versions = '<ul class="list-group">';
                    for (i = 0; i < response.length; i++) {
                        versions +=`<li class="list-group-item">V ${response[i].version} (${response[i].published_date})<span class="float-right"><a onclick="editSampleModal(${response[i].version_id});"><i class="fas fa-pencil-alt ml-2 h6 text-warning"></i></a><span></li>`
                    }
                    versions +=`</ul><br><a href="${baseUrl+'/upgrade-sample/'+response[0].report_id}" class="btn btn-sm btn-primary" role="button">Upgrade Version</a>`;

                    $('#versions-body').append(versions);
                    $('#versions-modal').modal('show');

                }
            });
        }

        function editSampleModal(id){
            closeModal('versions-modal');
            $.ajax({
                headers:
                {
                    'X-CSRF-Token': "{{csrf_token()}}"
                },
                type: "GET",
                url:"{{ url('/edit-sample') }}",
                data: {
                    sampleId : id
                },
                success:function(response){
                    console.log(response);
                    $('#version_id').val(response.version_id);
                    $('#amr_title').val(response.amr_title);
                    $('#amr_sample_code').val(response.amr_sample_code);
                    $('#version').val(response.version);
                    $('#amr_published_date').html(response.published_date);
                    $('#sample_filename').html(response.sample_filename);
                    $('body').addClass('modal-open');
                    $('#contact-modal').modal('show');
                }
            });
        } 

        function confirmDelete(id){
            sampleDeleteId = id;
            $('#delete-modal').modal('show');
            
        }

        function confirmActive(id){
            sampleActivateId = id;
            $('#activate-modal').modal('show');
        }
        function closeModal(id){
            var modalId = '#'+id;
            $(modalId).modal('hide');
        } 
        function deleteSample(modalId){
            $.ajax({
                headers:
                {
                    'X-CSRF-Token': "{{csrf_token()}}"
                },
                type: "POST",
                url:"{{ url('/delete-sample') }}",
                data: {
                    sampleId : sampleDeleteId
                },
                success:function(response){
                    // console.log(response);
                    closeModal(modalId);
                    $('#delete-msg').html(response).show();
                    $("#report-samples").dataTable().fnDestroy()
                    getAllSamples();
                    setTimeout(function() {
                        $('#delete-msg').fadeOut('slow');
                    }, 3000);    
                }
            });
        }

        function ActivateSample(modalId){
            $.ajax({
                headers:
                {
                    'X-CSRF-Token': "{{csrf_token()}}"
                },
                type: "POST",
                url:"{{ url('/activate-sample') }}",
                data: {
                    sampleId : sampleActivateId
                },
                success:function(response){
                    // console.log(response);
                    closeModal(modalId);
                    $('#delete-msg').html(response).show();
                    $("#report-samples").dataTable().fnDestroy()
                    getAllSamples();
                    setTimeout(function() {
                        $('#delete-msg').fadeOut('slow');
                    }, 3000);    
                }
            });

        }

        function downloadSample(id){
            $.ajax({
                headers:
                {
                    'X-CSRF-Token': "{{csrf_token()}}"
                },
                type: "POST",
                url:"{{ url('/get-versions') }}",
                data: {
                    sampleId : id
                },
                success:function(response){
                    // console.log(response);
                    $('#versions-body').html('');
                    var versions = '<ul class="list-group">';
                    for (i = 0; i < response.length; i++) {
                        versions +=`<li class="list-group-item"><b>V ${response[i].version}</b> (${response[i].published_date})<span class="float-right"><a href="${baseUrl+'/download-sample/'+response[i].version_id}" onclick="closeModal('versions-modal')"><i class="fas fa-download ml-2 h6 text-primary"></i></a><span></li>`
                    }
                    versions +="</ul>";
                    $('#versions-body').append(versions);
                    $('#versions-modal').modal('show');    
                }
            });
            
        }

        setTimeout(function() {
            $('div.alert').fadeOut('slow');
        }, 3000);
    </script>
   
@endsection