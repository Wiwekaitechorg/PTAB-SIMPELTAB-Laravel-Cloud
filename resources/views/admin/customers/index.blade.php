@extends('layouts.admin')
@section('content')
@can('customer_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-2">
            <a class="btn btn-success" href="{{ route('admin.customers.create') }}">
                {{ trans('global.add') }} {{ trans('global.customer.title_singular') }}
            </a>
        </div>
        @can('customer_edit')

        <div class="col-lg-2">
            <a class="btn btn-info" href="{{ route('admin.customers.editImport') }}">
                {{ trans('global.edit') }} {{ trans('global.customer.title_singular') }} (Excel)
            </a>
        </div>

        {{-- <div class="col-lg-2">
            <a class="btn btn-warning" href="{{ route('admin.customers.viewReport') }}">
                Export to Excel
            </a>
        </div> --}}

@endcan
    </div>

@endcan
<div class="card">

    <div class="card-header">
        {{ trans('global.customer.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
             <form action="" id="filtersForm">
                <div class="input-group">
                    <select id="type" name="type" class="form-control">
                        <option value="">== Semua Tipe ==</option>
                        <option value="customer">Pelanggan</option>
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
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-customer">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.customer.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.customer.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.customer.fields.email') }}
                        </th>
                        <th>
                            {{ trans('global.customer.fields.address') }}
                        </th>
                        <th>
                            {{ trans('global.customer.fields.gender') }}
                        </th>
                        <th>
                            {{ trans('global.customer.fields.type') }}
                        </th>
                        <th>
                            {{ trans('global.customer.fields.phone') }}
                        </th>
                        <th>
                            Tipe Pelanggan
                        </th>
                        <th>
                            Foto Rumah
                        </th>
                        <th>
                            Foto KTP
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

        // console.log('type : ', type);

  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.customers.massDestroy') }}",
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
    @can('customer_delete')
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
      url: "{{ route('admin.customers.index') }}",
      data: {
        'type': $("#type").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'nomorrekening' },
        { data: 'code', name: 'nomorrekening' },
        { data: 'name', name: 'namapelanggan' },
        { data: 'email', name: '_email' },
        { data: 'address', name: 'alamat' },
        { data: 'gender', name: '_gender' },
        { data: 'type', name: '_type' },
        { data: 'phone', name: 'telp', searchable: false },
        { data: '_desctype', name: '_desctype', searchable: false },
        { data: '_filegambar', name: '_filegambar' ,  render: function( data, type, full, meta ) {
                        return "<img src=\"https://www.ptab-vps-storage.com/pdam"+ data + "\" width=\"150\"/>";
                    }, searchable : false},
        { data: '_filektp', name: '_filektp' ,  render: function( data, type, full, meta ) {
                        return "<img src=\"https://www.ptab-vps-storage.com/pdam"+ data + "\" width=\"150\"/>";
                    }, searchable : false},
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
  };

  $('.datatable-customer').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection
