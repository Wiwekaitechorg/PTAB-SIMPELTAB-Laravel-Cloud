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
                        {{-- <div class="col-md-6">
                            
                            <label>Pilih Status Wm</label>
                        <div class="input-group">
                            <select id="statussm" name="statussm" class="form-control">
                                <option value="">== Semua status ==</option>
                                <option value="101">WM kabur</option>
                                <option value="102">WM Rusak</option>
                                <option value="103">WM Mati</option>
                            </select>
                        </div>
                        
                        <br>
                            </div> --}}
                            <div class="col-md-6">
                            <label>Pilih Prioritas</label>
                            <div class="input-group">
                                <select id="priority" name="priority" class="form-control">
                                    <option value="">== Semua Prioritas ==</option>
                                    {{-- <option value="1">Low</option> --}}
                                    <option value="2">Medium</option>
                                    <option value="3">High</option>
                                </select>
                            </div>  
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


                    <div class="col-md-12 row">
                    <div class="col-md-6">
                        
                        <label>Pilih Status Usulan</label>
                    <div class="input-group">
                        <select id="status" name="status" class="form-control">
                            <option value="">== Semua status ==</option>
                            <option value="pending">Menunggu</option>
                            <option value="active">Aktif</option>
                            <option value="work">Dikerjakan</option>
                            <option value="close">Selesai</option>
                            <option value="reject">Ditolak</option>
                        </select>
                    </div>

                 

                    <br>
                    
                        </div>
                        <br>
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
                    </div>   --}}
                </div>     



                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                            <input id="from" placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "{{request()->input('from')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                            <input id="to" placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{request()->input('to')}}">
                        </div>
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
        @if (in_array('8',$roles)|| in_array('14', $roles) || in_array('17', $roles))
        <a class="btn btn btn-success" href="{{ route('admin.proposalwm.create') }}">
            Tambah Usulan WM
        </a>            
        @endif

        @if (in_array('8',$roles)|| in_array('15', $roles) || in_array('16', $roles) || in_array('18', $roles))
        <a class="btn btn btn-success" href="{{ route('admin.proposalwm.approveAll', ['statussm'=>request()->input('statussm'), 'priority'=>request()->input('priority'), 'areas'=>request()->input('areas')]) }}">
            Setujui Semua
        </a>            
        @endif

        {{-- <a class="btn btn btn-warning" href="{{ route('admin.proposalwm.approveall') }}" onclick="return confirm('Apakah anda yakin mengirim semua usulan ?')">
            Teruskan Semua
        </a> --}}
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-customer">
                <thead>
                    <tr>
                     
                        {{-- <th>
                            id
                        </th> --}}
                        <th>
                            Code
                        </th>
                          <th>
                           Area
                        </th>
                        <th>
                            No SBG
                        </th>
                        <th>
                            Alamat
                        </th>
                        <th>
                            Status WM
                        </th>
                        <th>
                            Priority
                        </th>
                        <th>
                           Periode
                        </th>
                        <th>
                            Dibuat Tanggal
                         </th>
                         <th>
                            Diubah Tanggal
                         </th>
                        <th>
                            Status
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

        let status = searchParams.get('status')
        if (status) {
            $("#status").val(status);
        }else{
            $("#status").val('');
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

        let areas = searchParams.get('areas')
        if (areas) {
            $("#areas").val(areas);
        }else{
            $("#areas").val('');
        }


        let priority = searchParams.get('priority')
        if (priority) {
            $("#priority").val(priority);
        }else{
            $("#priority").val('');
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
      url: "{{ route('admin.proposalwm.index') }}",
      data: {
        'statussm': $("#statussm").val(),
        'status': $("#status").val(),
        'priority': $("#priority").val(),
        'from': $("#from").val(),
        'to': $("#to").val(),
        'areas': $("#areas").val(),
      }
    },
    columns: [
        // { data: 'id', name: 'proposal_wms.id' },
        { data: 'code', name: 'proposal_wms.close_queue', searchable : false, sortable : false   },
        { data: 'idareal', name: 'tblpelanggan.idareal' },
        { data: 'customer_id', name: 'proposal_wms.customer_id' },
        { data: 'alamat', name: 'tblpelanggan.alamat' },
        { data: 'status_wm', name: 'proposal_wms.status_wm'  },
        { data: 'priority', name: 'proposal_wms.priority'  },
        { data: 'periode', name: 'proposal_wms.periode', searchable : false, sortable : false  },
        { data: 'created_at', name: 'proposal_wms.created_at' },
        { data: 'updated_at', name: 'proposal_wms.updated_at' },
        // { data: 'status', name: 'status' },
        { data: 'status', render: function (dataField) { return dataField === 'pending' ?'<button type="button" class="btn btn-warning btn-sm" disabled>' +dataField+ '</button>' : dataField === 'close2' ?'<button type="button" class="btn btn-secondary btn-sm" disabled>' +'close'+ '</button>' : dataField === 'active' ?
        '<button type="button" class="btn btn-success btn-sm" disabled> active </button>': dataField === 'close' ?'<button type="button" class="btn btn-primary btn-sm" disabled> close </button>': '<button type="button" class="btn btn-secondary btn-sm" disabled>' +dataField+ '</button>'; }, searchable : false  },
        { data: 'actions', name: '{{ trans('global.actions') }}', searchable : false  }
    ],
    pageLength: 100,
    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    "order": [[ 8, "desc" ]]
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

