@extends('layouts.admin2')
@section('content')
<div class="card">
    <div class="card-header">
        Daftar Status WM
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
                        
                        <label>Pilih Status</label>
                    <div class="input-group">
                        <select id="statussm" name="statussm" class="form-control">
                            <option value="">== Semua status ==</option>
                            <option value="101">WM kabur</option>
                            <option value="102">WM Rusak</option>
                            <option value="103">WM Mati</option>
                        </select>
                    </div>
                    <br>
                        </div>
                        <br>
                        <div class="col-md-6">
                            <label>Pilih Wilayah</label>
                            <select id="areas" name="areas" class="form-control">
                                <option value="">== Semua area ==</option>
                                @foreach ($areas as $item )
                                <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}</option>
                                @endforeach
                            </select>
                            {{-- <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                    <input id="to" placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{request()->input('to') ? request()->input('to') :"" }}">
                                </div>
                            </div> --}}
                    </div>  
                </div>     
                        <span class="input-group-btn">
                            &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                        </span>           
                </form>
            </div> 
        </div>
        {{-- <a class="btn btn btn-danger" href="{{ route('admin.historywa.deletefilter', ['statussm'=>request()->input('statussm'), 'custom'=>request()->input('custom'), 'to'=>request()->input('to'), 'from'=>request()->input('from')]) }}" onclick="return confirm('Apakah anda ingin menghapus data(sesuai filter) ?')">
            hapus(sesuai filter)
        </a> --}}

       
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-customer">
                <thead>
                    <tr>
                     
                        <th>
                            No SBG
                        </th>
                        <th>
                           Area
                        </th>
                    
                        <th>
                            Nama Pelanggan
                        </th>
                        <th>
                            Periode
                        </th>
                        <th>
                            Status WM
                        </th>
                        <th>
                           
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
        let statussm = searchParams.get('statussm')
        if (statussm) {
            $("#statussm").val(statussm);
        }else{
            $("#statussm").val('');
        }

        let areas = searchParams.get('areas')
        if (areas) {
            $("#areas").val(areas);
        }else{
            $("#areas").val('');
        }
        // console.log('custom : ', custom);

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
      url: "{{ route('admin.statuswm.index') }}",
      data: {
        'statussm': $("#statussm").val(),
        'areas': $("#areas").val(),
        'from': $("#from").val(),
        'to': $("#to").val(),
      }
    },
    columns: [
        { data: 'nomorrekening', name: 'nomorrekening'},
        { data: 'idareal', name: 'idareal', searchable: false },
        { data: 'namapelanggan', name: 'namapelanggan', searchable: false },
        { data: 'periode', name: 'periode', searchable: false  },
        { data: 'NamaStatus', name: 'NamaStatus', searchable: false  },
        // { data: 'statussm', render: function (dataField) { return dataField === 'sent' ?'<button type="button" class="btn btn-primary btn-sm" disabled> sent </button>' : dataField === 'read' ?'<button type="button" class="btn btn-success btn-sm" disabled> read </button>': dataField === 'pending' ?'<button type="button" class="btn btn-warning btn-sm" disabled> pending </button>': '<button type="button" class="btn btn-danger btn-sm" disabled>' +dataField+ '</button>'; } },
        { data: 'actions', name: '{{ trans('global.actions') }}' , searchable: false }
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

