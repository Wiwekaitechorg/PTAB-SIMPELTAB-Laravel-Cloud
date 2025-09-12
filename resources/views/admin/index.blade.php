@extends('layouts.admin')



@section('content')



@can('ticket_create')



    <div style="margin-bottom: 10px;" class="row">



        <div class="col-lg-12">



            <a class="btn btn-success" href="{{ route('admin.tickets.create') }}">



                {{ trans('global.add') }} {{ trans('global.ticket.title_singular') }}



            </a>



        </div>



    </div>







@endcan







@if($errors->any())



<!-- <h4>{{$errors->first()}}</h4> -->



    <?php

echo "<script> alert('{$errors->first()}')</script>";

?>



@endif



<div class="card">







    <div class="card-header">



        {{ trans('global.ticket.title_singular') }} {{ trans('global.list') }}



    </div>



    <div class="card-body">



    <div class="form-group">



        <div class="col-md-6">



             <form action="" id="filtersForm">



                <div class="input-group">



                    <select id="status" name="status" class="form-control">



                        <option value="">== Semua Status ==</option>



                        <option value="pending">Pending</option>



                        <option value="active">Active</option>



                        <option value="close">Close</option>



                    </select>



                    <select id="departement" name="departement" class="form-control">



                        <option value="">== Semua Departement ==</option>



                        @foreach ($departementlist as $depart )



                            <option value="{{$depart->id}}" >{{$depart->name}}</option>



                        @endforeach



                    </select>

                    <select id="subdepartement" name="subdepartement" class="form-control">



                        <option value="">== Semua Sub Departement ==</option>
                        @foreach ($subdepartementlist as $subdepart )



                        <option value="{{$subdepart->id}}" >{{$subdepart->name}}</option>



                        @endforeach


                    </select>



                    <span class="input-group-btn">



                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">



                    </span>



                </div>



             </form>



        </div>



    </div>







        <div class="table-responsive">



            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-ticket">



                <thead>



                    <tr>



                        <th width="10">







                        </th>



                        <th>



                            No.



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.code') }}



                        </th>



                        <th>



                            {{ trans('global.proposalwm.fields.nomorrekening') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.date') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.departement') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.title') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.description') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.address') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.status') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.category') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.customer') }}



                        </th>



                        <th>



                            {{ trans('global.ticket.fields.creator') }}



                        </th>



                        <th>



                            &nbsp;



                        </th>



                    </tr>



                </thead>







            </table>



        </div>



    </div>



</div>



@section('scripts')



@parent



<script>



    $(function () {



        let searchParams = new URLSearchParams(window.location.search)







        let status = searchParams.get('status')



        if (status) {



            $("#status").val(status);



        }else{



            $("#status").val('');



        }







        let departement = searchParams.get('departement')



        if (departement) {



            $("#departement").val(departement);



        }else{



            $("#departement").val('');



        }

        let subdepartement = searchParams.get('subdepartement')



        if (subdepartement) {



            $("#subdepartement").val(subdepartement);



        }else{



            $("#subdepartement").val('');



        }







        // console.log('type : ', type);







  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'



  let deleteButton = {



    text: deleteButtonTrans,



    url: "{{ route('admin.tickets.massDestroy') }}",



    className: 'btn-danger',



    action: function (e, dt, node, config) {



      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {



          return $(entry).data('entry-id')



      });







      if (ids.length === 0) {



        alert('{{ trans('global.datatables.zero_selected') }}')







        return



      }







      if (confirm('{{ trans('global.areYouSure') }}')) {



        $.ajax({



          headers: {'x-csrf-token': _token},



          method: 'POST',



          url: config.url,



          data: { ids: ids, _method: 'DELETE' }})



          .done(function () { location.reload() })



      }



    }



  }



  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)



    @can('ticket_delete')



    dtButtons.push(deleteButton)



    @endcan







  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })







  let dtOverrideGlobals = {



    buttons: dtButtons,



    processing: true,



    serverSide: true,



    retrieve: true,



    aaSorting: [],



    ajax: {



      url: "{{ route('admin.tickets.index') }}",



      data: {



        'status': $("#status").val(),



        'departement': $("#departement").val(),
        'subdepartement': $("#subdepartement").val(),



      }



    },



    columns: [



        { data: 'placeholder', name: 'placeholder' },



        { data: 'DT_RowIndex', name: 'no', searchable : false },



        { data: 'code', name: 'code' },



        { data: 'nomorrekening', name: 'nomorrekening', searchable : false  },



        { data: 'created_at', name: 'created_at' },



        { data: 'dapertement', name: 'dapertement', searchable : false  },



        { data: 'title', name: 'title' },



        { data: 'description', name: 'description' },



        { data: 'address', name: 'address', searchable : false },



        { data: 'status', render: function (dataField) { return dataField === 'pending' ?'<button type="button" class="btn btn-warning btn-sm" disabled>'+dataField+'</button>': dataField === 'close2' ?'<button type="button" class="btn bg-secondary btn-sm" disabled>'+'close'+'</button>': dataField === 'pending2' ?'<button type="button" class="btn bg-secondary btn-sm" disabled>'+'pending'+'</button>': dataField === 'active' ?'<button type="button" class="btn btn-primary btn-sm" disabled>'+dataField+'</button>':'<button type="button" class="btn btn-success btn-sm" disabled>'+dataField+'</button>'; } },



        { data: 'category', name: 'category', searchable : false  },



        { data: 'customer', name: 'customer_id' },



        { data: 'creator', name: 'creator' },



        { data: 'actions', name: '{{ trans('global.actions') }}' }







    ],



    // order: [[ 2, 'asc' ]],



    pageLength: 100,



  };







  $('.datatable-ticket').DataTable(dtOverrideGlobals);



    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){



        $($.fn.dataTable.tables(true)).DataTable()



            .columns.adjust();



    });



})







</script>
<script>
    $('#departement').change(function(){
    var departement = $(this).val();    
    if(departement){
        $.ajax({
           type:"GET",
           url:"{{ route('admin.staffs.subdepartment') }}?dapertement_id="+departement,
           dataType: 'JSON',
           success:function(res){               
            if(res){
                $("#subdepartement").empty();
                $("#subdepartement").append('<option>---Pilih Sub Depertement---</option>');
                $.each(res,function(id,name){
                    $("#subdepartement").append('<option value="'+id+'">'+name+'</option>');
                });
            }else{
               $("#subdepartement").empty();
            }
           }
        });
    }else{
        $("#subdepartement").empty();
    }      
   });
</script>



@endsection



@endsection