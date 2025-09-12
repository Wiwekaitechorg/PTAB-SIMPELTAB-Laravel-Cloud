@extends('layouts.admin2')
@section('content')
<div class="card">
    <div class="card-header">
        Check Phone
    </div>

    <div class="card-body">

        <div class="form-group">
            <div class="col-md-12">
                <form action="" id="filtersForm">
                    <div class="col-md-12 row">
                    <div class="col-md-6">
                        
                        <label>Pilih number</label>
                    <div class="input-group">
                        <select id="number" name="number" class="form-control">
                            <option value="">Nomor Tidak Ada</option>
                            <option value="08">08...</option>
                            <option value="+62">+62...</option>
                            <option value="X">X</option>
                            <option value="-">-</option>
                        </select>
                    </div>
                    <br>
                        </div>
                        <br>
                        <div class="col-md-6">
                            <label>Pilih Wilayah</label>
                            <select id="area" name="area" class="form-control">
                                <option value="">== Semua area ==</option>
                                @foreach ($areas as $item )
                                <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}</option>
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
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-customer">
                <thead>
                    <tr>
                        <th>
                            No.
                        </th>
                        <th>
                            No.SBG
                        </th>
                        <th>
                            Nama Pelanggan
                        </th>
                        <th>
                            Nomor Telepon
                        </th>
                        <th>
                           Alamat
                        </th>
                        <th>
                            ID Area
                        </th>
                        {{-- <th>
                            &nbsp;
                        </th> --}}
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
        let number = searchParams.get('number')
        if (number) {
            $("#number").val(number);
        }else{
            $("#number").val('');
        }

        let area = searchParams.get('area')
        if (area) {
            $("#area").val(area);
        }else{
            $("#area").val('');
        }
        // console.log('area : ', area);

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

  $('.datatable:not(.ajaxTable)').DataTable({ })

  let dtOverrideGlobals = {
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.checkphone.index') }}",
      data: {
        'number': $("#number").val(),
        'area': $("#area").val(),
      }
    },
    columns: [
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'nomorrekening', name: 'nomorrekening' },
        { data: 'namapelanggan', name: 'namapelanggan' },
        { data: 'telp', name: 'telp' },
             { data: 'adress', name: 'adress', searchable: false },
        { data: 'idareal', name: 'idareal', searchable: false },
        // { data: 'actions', name: '{{ trans('global.actions') }}' }
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

