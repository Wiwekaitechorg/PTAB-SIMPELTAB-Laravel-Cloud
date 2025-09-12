@extends('layouts.admin')
@section('content')
@if($errors->any())
    <?php 
        echo "<script> alert('{$errors->first()}')</script>";
    ?>
@endif
<div class="card">
    <div class="card-header">
        {{ trans('global.lock.title') }} {{ trans('global.list') }}
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
                            <option value="lock_resist">Hambatan Segel</option>
                            <option value="lock">Segel</option>
                            <option value="unplug_resist">Hambatan Cabut</option>
                            <option value="unplug">Cabut</option>
                        </select>
                    </div>
     <br>
     <label>Plih Staff</label>
                        <div class="input-group">
                          
                            <select id="staff" name="staff" class="form-control">
                                <option value="">== Semua Staff ==</option>
                                @foreach ($staff as $item )
                                <option value="{{ $item->id }}">{{ $item->dapertements_name }}-{{ $item->subdapertements_name }}-{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <label>Pilih Wilayah</label>
                        <div class="input-group">
                        <select id="area" name="area" class="form-control">
                            <option value="">== Semua area ==</option>
                            @foreach ($areas as $item )
                            <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}</option>
                            @endforeach
                        </select>
                    </div>
                        </div>
<br>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                    <input id="from" placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                    <input id="to" placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{date('Y-m-d')}}">
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
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-lock">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.lock.code') }}
                        </th>
                        <th>
                            Register
                        </th>
                        <th>
                            {{ trans('global.lock.customer') }}
                        </th>
                        <th>
                            ID Areal
                        </th>
                        <th>
                            ID Urut
                        </th>
                        <th>
                            {{ trans('global.lock.description') }}
                        </th>
                        <th>
                            {{ trans('global.lock.staff_id') }}
                        </th>
                        <th>
                            {{ trans('global.lock.staff_name') }}
                        </th>
                        <th>
                            {{ trans('global.lock.status') }}
                        </th>
                        <th>
                            {{ trans('global.lock.start') }}
                        </th>
                        <th>
                            {{ trans('global.lock.end') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                  
                </tbody>
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

        let staff = searchParams.get('staff')
        if (staff) {
            $("#staff").val(staff);
        }else{
            $("#staff").val('');
        }

        let area = searchParams.get('area')
        if (area) {
            $("#area").val(area);
        }else{
            $("#area").val('');
        }

  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.users.massDestroy') }}",
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
    @can('user_delete')
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
            url: "{{ route('admin.locks.index') }}",
            data: {
                'status': $("#status").val(),
                'from': $("#from").val(),
                'to': $("#to").val(),
                'staff': $("#staff").val(),
                'area': $("#area").val(),
                }
            },
            columns: [
                { data: 'placeholder', name: 'placeholder' },
                { data: 'DT_RowIndex', name: 'no', searchable: false },
                { data: 'code', name: 'customer_id' },
                { data: 'register', name: 'register', searchable: false },
                { data: 'customer', name: 'tblpelanggan.namapelanggan'},
                { data: 'idareal', name: 'tblpelanggan.idareal'},
                { data: 'idurut', name: 'tblpelanggan.idurut', searchable: false },
                { data: 'description', name: 'memo' },
                { data: 'staff_id', name: 'staff_id' },
                { data: 'staff_name', name: 'users.name' },
                { data: 'status', render: function (dataField) { return dataField === 'notice' ?'<button type="button" class="btn btn-primary btn-sm" disabled>P.Surat</button>':dataField === 'lock' ?'<button type="button" class="btn btn-warning btn-sm" disabled>Penyegelan</button>':dataField === 'notice2' ?'<button type="button" class="btn btn-secondary btn-sm" disabled>Kunjungan</button>':dataField === 'lock_resist' ?'<button type="button" class="btn btn-secondary btn-sm" disabled>Hambatan Segel</button>':dataField === 'unplug_resist' ?'<button type="button" class="btn btn-secondary btn-sm" disabled>Hambatan Cabut</button>':'<button type="button" class="btn btn-danger btn-sm" disabled>Cabutan</button>'; }, searchable: false },
                { data: 'start', name: 'created_at', searchable: false },
                { data: 'end', name: 'updated_at', searchable: false },
                { data: 'staff', name: '{{ trans('global.staff.title') }}' }
            ],
            // order: [[ 2, 'asc' ]],
            pageLength: 100,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        };

        $('.datatable-lock').DataTable(dtOverrideGlobals);
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });
    })

</script>
@endsection
@endsection