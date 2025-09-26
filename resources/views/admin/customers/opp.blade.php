@extends('layouts.admin')
@section('content')

<div class="card">

    <div class="card-header">
        {{ trans('global.customer.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
             <form action="" id="filtersForm">
             <div class="input-group">
                              <select id="staff" name="staff" class="form-control">
                                  <option value="">== Semua Staff ==</option>
                                  @foreach ($staff as $item )
                                  <option value="{{ $item->Name }}">{{ $item->Name }}</option>
                                  @endforeach
                              </select>
                          </div>

                          <div class="input-group">
                        <input class="form-control" id="from" type="date" name="from" value="{{ request()->input('from') }}">
                        </div>
                        <div class="input-group">
                        <input class="form-control" id="to" type="date" name="to" value="{{ request()->input('to') }}">
                        </div>
                        <div class="input-group">
                    <select id="status" name="status" class="form-control">
                        <option value="semua">== Semua Status ==</option>
                        <option value="belum">Belum</option>
                        <option value="sudah">Sudah</option>
                    </select>                    
                </div>
                          <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>
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
                            Last Update
                        </th>
                        <th>
                            Tipe Pelanggan
                        </th>
                        <th>
                            Keterangan Pelanggan
                        </th>
                        <th>
                            Foto Rumah
                        </th>
                        <th>
                            Foto WM
                        </th>
                        <th>
                            Foto KTP
                        </th>
                        <th>
                            Foto Lain
                        </th>
                        <th>
                            Latitude
                        </th>
                        <th>
                            Longitude
                        </th>
                        <th>
                            No. NIK/KTP
                        </th>
                        <th>
                            Nama Sesuai KTP
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
        let staff = searchParams.get('staff')
        if (staff) {
            $("#staff").val(staff);
        }else{
            $("#staff").val('');
        }

        let status = searchParams.get('status')
        if (status) {
            $("#status").val(status);
        }else{
            $("#status").val('semua');
        }

        // console.log('staff : ', staff);

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
    //buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.opp.index') }}",
      data: {
        'staff': $("#staff").val(),
        'from' : $("#from").val(),
        'to' : $("#to").val(),
        'status' : $("#status").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'nomorrekening' },
        { data: 'nomorrekening', name: 'nomorrekening' },
        { data: 'namapelanggan', name: 'namapelanggan' },
        { data: '_email', name: '_email' },
        { data: 'alamat', name: 'alamat' },
        { data: '_gender', name: '_gender' },
        { data: 'last_update', name: 'last_update', searchable: false },
        { data: '_desctype', name: '_desctype', searchable: false },
        { data: '_desc', name: '_desc', searchable: false },
        { data: '_filegambar', name: '_filegambar' ,  render: function( data, type, full, meta ) {
                        return "<img src=\"https://ptab-vps-storage.com/pdam"+ data + "\" width=\"150\"/>";
                    }, searchable : false},
                    
        { data: '_filewm', name: '_filewm' ,  render: function( data, type, full, meta ) {
                        return "<img src=\"https://ptab-vps-storage.com/pdam"+ data + "\" width=\"150\"/>";
                    }, searchable : false},
        { data: '_filektp', name: '_filektp' ,  render: function( data, type, full, meta ) {
                        return "<img src=\"https://ptab-vps-storage.com/pdam"+ data + "\" width=\"150\"/>";
                    }, searchable : false},
        { data: '_filelain', name: '_filelain' ,  render: function( data, type, full, meta ) {
                        return "<img src=\"https://ptab-vps-storage.com/pdam"+ data + "\" width=\"150\"/>";
                    }, searchable : false},
        { data: 'lat', name: 'lat', searchable: false },
        { data: 'lng', name: 'lng', searchable: false },
        { data: 'noktp', name: 'noktp', searchable: false },
        { data: '_namaktp', name: '_namaktp', searchable: false },
        { data: 'status', name: 'status', searchable: false },
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
