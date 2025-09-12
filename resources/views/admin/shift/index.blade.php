@extends('layouts.admin')
@section('content')
{{-- @can('shift_create') --}}
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.shift.create') }}">
                {{ trans('global.add') }} {{ trans('global.shift.title_singular') }}
            </a>
        </div>
    </div>
    
{{-- @endcan --}}
<div class="card">

    <div class="card-header">
        {{ trans('global.shift.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
      
             <form action="" id="filtersForm">
                <div class="row">
  
                    <div class="col-md-6">
                <div class="input-group">
                    <select id="dapertement_id" name="dapertement_id" class="form-control">
                        <option value="">== Semua Dapertement ==</option>
                        @foreach ($dapertements as $dapertement)
                        <option value="{{ $dapertement->id }}">{{ $dapertement->name }}</option>
                        @endforeach
                      
                    </select>
                </div>
            </div> 

                <div class="col-md-6">
                <div class="input-group">
                    <select id="staff_id" name="staff_id" class="form-control">
                        <option value="">== Semua Staff ==</option>
                        @foreach ($staffs as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                      
                    </select>
                </div> 
            </div>
                                  
            </div>
            <br>
            <div class="row">
            <div class="col-md-6">
                <div class="input-group">
                    <select id="shift_id" name="shift_id" class="form-control">
                        <option value="">== Semua Shift ==</option>
                        @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->dapertement_name }} | {{ $shift->title }}</option>
                        @endforeach
                      
                    </select>
                </div> 
            </div> 
        </div> 
                    <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                            
             </form>
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-shift">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        {{-- <th>
                            {{ trans('global.shift.fields.id') }}
                        </th> --}}
                        <th>
                            {{ trans('global.shift.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.shift.fields.shift_title') }}
                        </th>
                        <th>
                            {{ trans('global.shift.fields.dapertement_name') }}
                        </th>
                        <th>
                            {{ trans('global.shift.fields.shift_date') }}
                        </th>
                        <th>
                            {{ trans('global.shift.fields.created_at') }}
                        </th> 
                         <th>
                            {{ trans('global.shift.fields.updated_at') }}
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
        let type = searchParams.get('type')
        if (type) {
            $("#type").val(type);
        }else{
            $("#type").val('');
        }


        let staff_id = searchParams.get('staff_id')
        if (staff_id) {
            $("#staff_id").val(staff_id);
        }else{
            $("#staff_id").val('');
        }

        let dapertement_id = searchParams.get('dapertement_id')
        if (dapertement_id) {
            $("#dapertement_id").val(dapertement_id);
        }else{
            $("#dapertement_id").val('');
        }

        let shift_id = searchParams.get('shift_id')
        if (shift_id) {
            $("#shift_id").val(shift_id);
        }else{
            $("#shift_id").val('');
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
    @can('shift_delete')
    dtButtons.push(deleteButton)
    @endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    serverSide: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.shift.index') }}",
      data: {
        'type': $("#type").val(),
        'staff_id': $("#staff_id").val(),
        'shift_id': $("#shift_id").val(),
        'dapertement_id': $("#dapertement_id").val(),
      },
      dataType: "JSON"
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'staff_name', name: 'staff_name' },
        { data: 'shift_title', name: 'shift_title' },
        { data: 'dapertement_name', name: 'dapertement_name' },
        { data: 'shift_date', name: 'shift_date' },
        { data: 'created_at', name: 'created_at' },
        { data: 'updated_at', name: 'updated_at' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
  };

  $('.datatable-shift').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection