@extends('layouts.admin2')
@section('content')
<div class="card">
    <div class="card-header">
        Daftar Status WM (Pergantian Lebih dari 2x dalam 5 tahun)
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
                            <label>Pilih Jumlah Pergantian</label>
                            <div class="input-group">
                                <select id="jmlhpergantian" name="jmlhpergantian" class="form-control">
                                    <option value="">== Jumlah Pergantian ==</option>
                                    {{-- <option value="1">Low</option> --}}
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                     <option value="4">4</option>
                                    <option value="5">5</option>
                                     <option value="6">6</option>
                                    <option value="7">7</option>
                                     <option value="8">8</option>
                                     <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">>10</option>
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
                        
                        {{-- <label>Pilih Status Usulan</label>
                    <div class="input-group">
                        <select id="status" name="status" class="form-control">
                            <option value="">== Semua status ==</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="work">Work</option>
                            <option value="close">CLose</option>
                        </select>
                    </div> --}}

                 

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
                        <span class="input-group-btn">
                            &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                        </span>           
                </form>
            </div> 
        </div>
        {{-- <a class="btn btn btn-danger" href="{{ route('admin.historywa.deletefilter', ['statussm'=>request()->input('statussm'), 'custom'=>request()->input('custom'), 'to'=>request()->input('to'), 'from'=>request()->input('from')]) }}" onclick="return confirm('Apakah anda ingin menghapus data(sesuai filter) ?')">
            hapus(sesuai filter)
        </a> --}}

        {{-- <a class="btn btn btn-warning" href="{{ route('admin.proposalwm.approveall') }}" onclick="return confirm('Apakah anda yakin mengirim semua usulan ?')">
            Teruskan Semua
        </a> --}}
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-customer">
                <thead>
                    <tr>
                     
                        <th>
                            Code
                        </th>
                          <th>
                           Area
                        </th>
                        <th>
                            No SBG
                        </th>
                        {{-- <th>
                            Status WM
                        </th>
                        <th>
                            Priority
                        </th>
                        <th>
                           Periode
                        </th> --}}
                        <th>
                            Jumlah Pergantian
                         </th>
                         {{-- <th>
                            Diubah Tanggal
                         </th> --}}
                      
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


        let jmlhpergantian = searchParams.get('jmlhpergantian')
        if (jmlhpergantian) {
            $("#jmlhpergantian").val(jmlhpergantian);
        }else{
            $("#jmlhpergantian").val('');
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
      url: "{{ route('admin.proposalwm.index5Year') }}",
      data: {
        'statussm': $("#statussm").val(),
        'status': $("#status").val(),
        'jmlhpergantian': $("#jmlhpergantian").val(),
        'from': $("#from").val(),
        'to': $("#to").val(),
        'areas': $("#areas").val(),
      }
    },
    columns: [
        { data: 'code', name: 'code',searchable : false  },
        { data: 'idareal', name: 'idareal', searchable : false },
        { data: 'customer_id', name: 'proposal_wms.customer_id' },
        // { data: 'status_wm', name: 'status_wm', searchable : false  },
        // { data: 'priority', name: 'priority', searchable : false  },
        // { data: 'periode', name: 'periode', searchable : false  },
         { data: 'jumlahpergantian', name: 'jumlahpergantian', searchable : false },
        // { data: 'created_at', name: 'created_at' },
        // { data: 'updated_at', name: 'updated_at' },
        // { data: 'status', name: 'status' },
     
        { data: 'actions', name: '{{ trans('global.actions') }}', searchable : false  }
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

