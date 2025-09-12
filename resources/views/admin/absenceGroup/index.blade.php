@extends('layouts.admin2')
@section('content')
{{-- @can('absence_create') --}}
    {{-- <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.absence.create') }}">
                {{ trans('global.add') }} {{ trans('global.absence.title_singular') }}
            </a>
        </div>
    </div> --}}
    
{{-- @endcan --}}
<div class="card">

    <div class="card-header">
        {{ trans('global.absence.title_singular') }} {{ trans('global.list') }}
    </div>
    <input type="hidden" id="id" value="{{ !empty($request->id) ? $request->id : '' }}">
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-12">
             <form action="" id="filtersForm">
                <div class="input-group">
               

                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-th"></span>
                                </div>
                                <input id="from" placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "{{request()->input('from') ? request()->input('from') : date('Y-m-d')}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-th"></span>
                                </div>
                                <input id="to" placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{request()->input('to') ? request()->input('to') :"" }}">
                            </div>
                        </div>
                     
                </div> 

                <div class="col-md-6">
                <label>Staff</label>
                <select id="staff_id" name="staff_id" class="form-control">
                    <option value="">== Semua Staff ==</option>
                    @foreach ($staffs as $staff )
                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                   
                </select> 

                <label>Dapertement</label>
                <select id="dapertement" name="dapertement" class="form-control">
                    <option value="">== Semua dapertement ==</option>
                    @foreach ($dapertements as $dapertement )
                    <option value="{{ $dapertement->id }}">{{ $dapertement->name }}</option>
                    @endforeach
                   
                </select> 

                <label>Categori</label>
                <select id="absence_category_id" name="absence_category_id" class="form-control">
                    <option value="">== Semua categori ==</option>
                    @foreach ($absence_categories as $absence_category )
                    <option value="{{ $absence_category->id }}">{{ $absence_category->title }}</option>
                    @endforeach
                   
                </select> 
            </div> 
                 
                </div>  
                <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>              
             </form>
             </div>  --}}
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-absence">
                <thead>
                    <tr>
                   
                        <th>
                            No.
                        </th>
                        {{-- <th>
                            {{ trans('global.absence.fields.id') }}
                        </th> --}}
                        <th>
                            NIK
                        </th>
                        <th>
                            Nama
                        </th>
                       
                        <th>
                            Tanggal
                        </th>
                        <th>
                            Status
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
        let staff_id = searchParams.get('staff_id')
        if (staff_id) {
            $("#staff_id").val(staff_id);
        }else{
            $("#staff_id").val('');
        }

        let absence_category_id = searchParams.get('absence_category_id')
        if (absence_category_id) {
            $("#absence_category_id").val(absence_category_id);
        }else{
            $("#absence_category_id").val('');
        }

        let dapertement = searchParams.get('dapertement')
        if (dapertement) {
            $("#dapertement").val(dapertement);
        }else{
            $("#dapertement").val('');
        }

        let from = searchParams.get('from')
        if (from) {
            $("#from").val(from);
        }else{
            $("#from").val('');
        }

        let to = searchParams.get('to')
        if (to) {
            $("#to").val(to);
        }else{
            $("#to").val('');
        }

        // console.log('type : ', type);

  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "",
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
    @can('absence_delete')
    dtButtons.push(deleteButton)
    @endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    // buttons: dtButtons,
    serverSide: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.absencegroup.index') }}",
      data: {
        'staff_id': $("#staff_id").val(),
        'from': $("#from").val(),
        'to': $("#to").val(),
        'dapertement': $("#dapertement").val(),
        'absence_category_id' : $("#absence_category_id").val(),
        'id' : $("#id").val()
      },
      dataType: "JSON"
    },
    columns: [
        // { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no', searchable : false },
        { data: 'NIK', name: 'NIK', searchable : false  },
        { data: 'name', name: 'staffs.name' },
        { data: 'created_at', name: 'absences.created_at' },
        { data: 'status_active', name: 'absences.status_active' },
        { data: 'actions', name: '{{ trans('global.actions') }}', searchable : false }
    ],
    pageLength: 100,
  };

  $('.datatable-absence').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection