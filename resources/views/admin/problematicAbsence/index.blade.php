@extends('layouts.admin')
@section('content')
{{-- @can('problematicabsence_create') --}}
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.problematicabsence.create') }}">
                {{ trans('global.add') }} {{ trans('global.problematicabsence.title_singular') }}
            </a>
        </div>
    </div>
    
{{-- @endcan --}}
<div class="card">

    <div class="card-header">
        {{ trans('global.problematicabsence.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
             <form action="" id="filtersForm">
                <div class="input-group">
                    <select id="type" name="type" class="form-control">
                        <option value="">== Semua Tipe ==</option>
                        <option value="problematicabsence">Pelanggan</option>
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
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-problematicabsence">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        {{-- <th>
                            {{ trans('global.problematicabsence.fields.id') }}
                        </th> --}}
                        <th>
                            {{ trans('global.problematicabsence.fields.day') }}
                        </th>
                        <th>
                            {{ trans('global.problematicabsence.fields.user') }}
                        </th>
                        <th>
                            {{ trans('global.problematicabsence.fields.lat') }}
                        </th>
                        <th>
                            {{ trans('global.problematicabsence.fields.lng') }}
                        </th>
                        <th>
                            {{ trans('global.problematicabsence.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.problematicabsence.fields.problematic_absence_category') }}
                        </th>
                        <th>
                            {{ trans('global.problematicabsence.fields.value') }}
                        </th>
                        <th>
                            {{ trans('global.problematicabsence.fields.late') }}
                        </th>
                        {{-- <th>
                            {{ trans('global.problematicabsence.fields.updated_at') }}
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
    @can('problematicabsence_delete')
    dtButtons.push(deleteButton)
    @endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    serverSide: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.problematicabsence.index') }}",
      data: {
        'type': $("#type").val(),
      },
      dataType: "JSON"
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'day', name: 'day' },
        { data: 'user', name: 'user' },
        { data: 'lat', name: 'lat' },
        { data: 'lng', name: 'lng' },
        { data: 'register', name: 'register' },
        { data: 'problematic_absence_category', name: 'problematic_absence_category' },
        { data: 'value', name: 'value' },
        { data: 'late', name: 'late' },
        // { data: 'updated_at', name: 'updated_at' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
  };

  $('.datatable-problematicabsence').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection