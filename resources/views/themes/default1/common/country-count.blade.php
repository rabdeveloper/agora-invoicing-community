@extends('themes.default1.layouts.master')
@section('title')
Settings
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Country List</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Country List</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

<div class="card card-primary card-outline">


        <div class="alert alert-success alert-dismissable" style="display: none;">
    <i class="fa  fa-check-circle"></i>
    <span class="success-msg"></span>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

      </div>
        <!-- fail message -->

        <div id="response"></div>
       


      <div class="card-body table-responsive">
        <div class="row">
        <div class="col-md-12 ">
      

                <table id="country-count" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                            <thead><tr>
                            <th>Country</th>
                            <th>User Count</th>
                            
                        </tr></thead>


                </table>
                </div>  
            </div>
            </div>
                </div> 
     <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#country-count').DataTable({
            destroy:true,
            processing: true,
            stateSave: true,
            serverSide: true,
            order: [[ 0, "desc" ]],
            ajax: '{!! route('country-count') !!}',
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
            },
    
            columns: [
                {data: 'country', name: 'country'},
                {data: 'count', name: 'count'},
            ],
            "fnDrawCallback": function( oSettings ) {
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });
    </script>


@stop











