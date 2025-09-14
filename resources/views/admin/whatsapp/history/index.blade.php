@extends('layouts.admin2')
@section('content')
<div class="card">
    <div class="card-header">
        History Whatsapp Blast
    </div>

    <div class="card-body">
        @if (session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        <div class="form-group">
            <div class="col-md-12">
                <form action="" id="filtersForm">
                        <div class="col-md-12 row">
                       
                            <div class="col-md-6">
                                <label>Channel</label>
                                <select id="channel" name="channel" class="form-control">
                                    <option value="">== Pilih Channel ==</option>
                                    @foreach ($channelList as $item )
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                        </div>  
                    </div>     

                    <div class="col-md-12 row">
                    <div class="col-md-6">
                        
                        <label>Pilih Status</label>
                    <div class="input-group">
                        <select id="status" name="status" class="form-control">
                            <option value="">== Semua Status ==</option>
                            <option value="sent">sent</option>
                            <option value="read">read</option>
                            <option value="cancel">cancel</option>
                            <option value="received">received</option>
                            <option value="reject">reject</option>
                            <option value="pending">pending</option>
                            <option value="gagal">gagal</option>
                        </select>
                    </div>

                    <label>Tipe</label>
                    <div class="input-group">
                        <select id="custom" name="custom" class="form-control">
                            <option value="">== Semua Status ==</option>
                            <option value="customer">Pelanggan</option>
                            <option value="nonCustomer">Bukan Pelanggan</option>
                        </select>
                    </div>
                    <br>
                        </div>
                        <br>
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
                </div>     
                        <span class="input-group-btn">
                            &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                        </span>           
                </form>
            </div> 
        </div>
        <a class="btn btn btn-danger" href="{{ route('admin.historywa.deletefilter', ['status'=>request()->input('status'), 'custom'=>request()->input('custom'), 'to'=>request()->input('to'), 'from'=>request()->input('from')]) }}" onclick="return confirm('Apakah anda ingin menghapus data(sesuai filter) ?')">
            hapus(sesuai filter)
        </a>
        <a class="btn btn btn-warning" href="{{ route('admin.historywa.deleteall') }}" onclick="return confirm('Apakah anda ingin menghapus semua data ?')">
            hapus semua
        </a>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-customer">
                <thead>
                    <tr>
                     
                        <th>
                            No.
                        </th>
                        <th>
                            ID Whatsapp
                        </th>
                        <th>
                            No.SBG
                        </th>
                        <th>
                            Nomor Telepon
                        </th>
                        <th>
                            Status
                        </th>
                        <th>
                            Template ID
                        </th>
                        <th>
                           Dibuat Tanggal
                        </th>
                        <th>
                            Diubah Tanggal
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

        let custom = searchParams.get('custom')
        if (custom) {
            $("#custom").val(custom);
        }else{
            $("#custom").val('');
        }

        let channel = searchParams.get('channel')
        if (channel) {
            $("#channel").val(channel);
        }else{
            $("#channel").val('');
        }

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

  $('.datatable:not(.ajaxTable)').DataTable({  })

  let dtOverrideGlobals = {
  
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.historywa.index') }}",
      data: {
        'status': $("#status").val(),
        'custom': $("#custom").val(),
        'from': $("#from").val(),
        'channel' : $("#channel").val(),
        'to': $("#to").val(),
      }
    },
    columns: [
        { data: 'DT_RowIndex', name: 'no', searchable : false },
        { data: 'id_wa', name: 'id_wa', searchable : false  },
        { data: 'customer_id', name: 'customer_id', searchable : false  },
        { data: 'phone', name: 'phone' },
        { data: 'status', render: function (dataField) { return dataField === 'sent' ?'<button type="button" class="btn btn-primary btn-sm" disabled> sent </button>' : dataField === 'read' ?'<button type="button" class="btn btn-success btn-sm" disabled> read </button>': dataField === 'pending' ?'<button type="button" class="btn btn-warning btn-sm" disabled> pending </button>': '<button type="button" class="btn btn-danger btn-sm" disabled>' +dataField+ '</button>'; } },
        { data: 'template_id', name: 'template_id' },
        { data: 'created_at', name: 'created_at', searchable: false },
        { data: 'updated_at', name: 'updated_at', searchable: false },
        { data: 'actions', name: '{{ trans('global.actions') }}', searchable: false  }
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

