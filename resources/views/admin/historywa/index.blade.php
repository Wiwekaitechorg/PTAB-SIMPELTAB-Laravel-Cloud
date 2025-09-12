@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        History Whatsapp Blast  {{ trans('global.list') }}
    </div>

    <div class="card-body">

        <div class="form-group">
            <div class="col-md-12">
                <form action="" id="filtersForm">
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

        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-customer">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            ID Whatsapp
                        </th>
                        <th>
                            Nomor Telepon
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

        // console.log('status : ', status);

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
      url: "{{ route('admin.historywa.index') }}",
      data: {
        'status': $("#status").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'id_wa' },
        { data: 'id_wa', name: 'id_wa' },
        { data: 'phone', name: 'phone' },
        { data: 'created_at', name: 'created_at' },
        { data: 'updated_at', name: 'updated_at' },
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

