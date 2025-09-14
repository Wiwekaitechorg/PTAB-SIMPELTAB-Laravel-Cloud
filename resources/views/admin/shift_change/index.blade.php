@extends('layouts.admin')
@section('content')
{{-- @can('shift_change_create') --}}
    {{-- <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-2">
            <a class="btn btn-success" href="{{ route('admin.shift_change.create',['type'=>'visit']) }}">
                {{ trans('global.add') }} {{ trans('global.shift_change.title_singular') }}(Masuk)
            </a>
        </div>
        <div class="col-lg-6">
            <a class="btn btn-warning" href="{{ route('admin.shift_change.create',['type'=>'shift_change']) }}">
                {{ trans('global.add') }} {{ trans('global.shift_change.title_singular') }}(Keluar)
            </a>
        </div>
    </div>
     --}}
{{-- @endcan --}}
<div class="card">

    <div class="card-header">
        {{ trans('global.shift_change.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
             <form action="" id="filtersForm">
                <div class="input-group">
                    <select id="type" name="type" class="form-control">
                        <option value="">== Semua Tipe ==</option>
                        <option value="shift_change">Pelanggan</option>
                        <option value="public">Umum</option>
                    </select>
                    <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                </div>                
             </form>
             </div> 
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-shift_change">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        {{-- <th>
                            {{ trans('global.shift_change.fields.id') }}
                        </th> --}}
                        <th>
                            {{ trans('global.shift_change.fields.name1') }}
                        </th>
                        <th>
                            {{ trans('global.shift_change.fields.shift1') }}
                        </th>
                        <th>
                            {{ trans('global.shift_change.fields.name2') }}
                        </th>
                        <th>
                            {{ trans('global.shift_change.fields.shift2') }}
                        </th>
                        <th>
                            {{ trans('global.shift_change.fields.status') }}
                        </th>
                        <th>
                            {{ trans('global.shift_change.fields.description') }}
                        </th>
                        <th>
                            Diajukan Tanggal
                        </th>
                        {{-- <th>
                            {{ trans('global.shift_change.fields.upstartd_at') }}
                        </th> --}}
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
    @can('shift_change_delete')
    dtButtons.push(deleteButton)
    @endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    serverSide: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.shift_change.index') }}",
      data: {
        'type': $("#type").val(),
      },
      dataType: "JSON"
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no', searchable : false },
        { data: 'name1', name: 'st1.name' },
        { data: 'shift1', name: 'sh1.title' },
        { data: 'name2', name: 'st2.name' },
        { data: 'shift2', name: 'sh2.title' },
        { data: 'status', name: 'status' },
        { data: 'description', name: 'description' },
        { data: 'created_at', name: 'created_at' },
        // { data: 'upstartd_at', name: 'upstartd_at' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
  };

  $('.datatable-shift_change').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection
