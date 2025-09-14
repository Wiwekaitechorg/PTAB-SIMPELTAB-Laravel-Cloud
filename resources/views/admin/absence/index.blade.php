@extends('layouts.admin2')
@section('content')
@can('absenceOffline_access')
    <div style="margin-bottom: 10px;" class="row">
        {{-- <div class="col-lg-3">
            <div>
            <a class="btn btn-success" href="{{ route('admin.absence.create') }}">
                {{ trans('global.add') }} {{ trans('global.absence.title_singular') }}(Reguler)
            </a>
        </div>
            <br>
            <div>
            <a class="btn btn-success" href="{{ route('admin.absence.createShift') }}">
                {{ trans('global.add') }} {{ trans('global.absence.title_singular') }}(Shift)
            </a>
        </div>
        </div>
        <div class="col-lg-3">
            <div>
            <a class="btn btn-success" href="{{ route('admin.absence.createImport') }}">
                Import {{ trans('global.absence.title_singular') }}(Reguler)
            </a>
        </div>
            <br>

            <div>
            <a class="btn btn-success" href="{{ route('admin.absence.createImportShift') }}">
                Import {{ trans('global.absence.title_singular') }}(Shift)
            </a>
        </div>
        </div>
        <div class="col-lg-3">
            <div>
            <a class="btn btn-success" href="{{ route('admin.absence.createPermit') }}">
                {{ trans('global.add') }} {{ trans('global.absence.title_singular') }}(Izin)
            </a>
        </div>
            <br>

            <div>
            <a class="btn btn-success" href="{{ route('admin.absence.createExtra') }}">
                {{ trans('global.add') }} {{ trans('global.absence.title_singular') }}(Lembur)
            </a>
        </div>
        </div>
        <div class="col-lg-3">
            <div>
            <a class="btn btn-success" href="{{ route('admin.absence.createLeave') }}">
                {{ trans('global.add') }} {{ trans('global.absence.title_singular') }}(Cuti)
            </a>
        </div>
            <br>

            <div>
            <a class="btn btn-success" href="{{ route('admin.absence.createDuty') }}">
                {{ trans('global.add') }} {{ trans('global.absence.title_singular') }}(Dinas Luar)
            </a>
        </div>
        </div> --}}
    </div>
    
@endcan
<div class="card">

    <div class="card-header">
        {{ trans('global.absence.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-12">
             <form action="" id="filtersForm">
                <div class="input-group">
               

                    <div class="col-md-6">
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
             </div> 
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-absence">
                <thead>
                    <tr>
                        {{-- <th width="10">

                        </th> --}}
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
                            {{ trans('global.absence.fields.day') }}
                        </th>
                       
                        <th>
                            {{ trans('global.absence.fields.staff') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.lat') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.lng') }}
                        </th>
                        <th>
                            Map
                        </th>
                        <th>
                            {{ trans('global.absence.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.absence_category') }}
                        </th>
                        {{-- <th>
                            {{ trans('global.absence.fields.value') }}
                        </th> --}}
                        <th>
                            {{ trans('global.absence.fields.late') }}
                        </th>
                        <th>
                            Work Type
                        </th>
                        <th>
                            {{ trans('global.absence.fields.image') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.staff_image') }}
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
    buttons: dtButtons,
    serverSide: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.absence.index') }}",
      data: {
        'staff_id': $("#staff_id").val(),
        'from': $("#from").val(),
        'to': $("#to").val(),
        'dapertement': $("#dapertement").val(),
        'absence_category_id' : $("#absence_category_id").val(),
      },
      dataType: "JSON"
    },
    columns: [
        // { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no', searchable : false },
        { data: 'NIK', name: 'NIK', searchable : false  },
        { data: 'day', name: 'days.name' },
        { data: 'staff', name: 'staffs.name' },
        { data: 'lat', name: 'lat' },
        { data: 'lng', name: 'lng' },
        { data: 'map', name: 'map', searchable : false  },
        { data: 'register', name: 'register' },
        { data: 'absence_category', name: 'absence_category_id' },
        // { data: 'value', name: 'value' },
        { data: 'late', name: 'late' },
        { data: 'work_type', name: 'work_types.type' },
        
        { data: 'image', name: 'image' ,  render: function( data, type, full, meta ) {
                        return "<img src=\"{{ asset('') }}"+ data + "\" width=\"150\"/>";
                    }, searchable : false},
        { data: 'staff_image', name: 'staff_image' ,  render: function( data, type, full, meta ) {
                        return "<img src=\"{{ asset('') }}"+ data + "\" width=\"150\"/>";
                    }, searchable : false},
        // { data: 'updated_at', name: 'updated_at' },
        { data: 'actions', name: '{{ trans('global.actions') }}', searchable : false  }
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