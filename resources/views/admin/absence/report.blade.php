@extends('layouts.admin')
@section('content')
{{-- @can('absence_create') --}}
    {{-- <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.absence.create') }}">
                {{ trans('global.add') }} {{ trans('global.absence.title_singular') }}
            </a>
        </div>
    </div> --}}
    
{{-- @endcan --}}
<div class="card">

    <div class="card-header">
        {{ trans('global.absence.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-12">
             <form action="" id="filtersForm">
                <div class="input-group">
                    {{-- <select id="type" name="type" class="form-control">
                        <option value="">== Semua Tipe ==</option>
                        <option value="absence">Pelanggan</option>
                        <option value="public">Umum</option>
                    </select> --}}
                    {{-- <label for="type">Pilih Bulan</label>
<div class='input-group' id='dpRM'>
    <input type='text' name="monthyear" id="monthyear" class="form-control form-control-1 form-input input-sm fromq" placeholder="Enter Month and year" required  />
    <span class="input-group-addon"> --}}
        {{-- <span class="fa fa-calendar"></span> --}}
    {{-- </span>
</div> --}}

<div class="col-md-6">
    <div class="form-group">
        <label>Dari Tanggal</label>
        <div class="input-group date">
            <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
            </div>
            <input id="from" placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "{{ (date('d') > 20) ? date('Y-m-d', strtotime(date('Y-m') . '-21')) : date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m') .'-21')))}}">
        </div>
    </div>
    <div class="form-group">
        <label>Sampai Tanggal</label>
        <div class="input-group date">
            <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
            </div>
            <input id="to" placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{ (date('d') > 20) ? date("Y-m-d", strtotime('+1 month', strtotime(date('Y-m') . "-20"))): date('Y-m-d', strtotime('0 month', strtotime(date('Y-m') .'-20'))) }}">
        </div>
    </div>
</div>  

                  
                </div>  
                <div>
                    <input type="submit" class="btn btn-primary" value="Filter">
                      </div>              
             </form>
             </div> 
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-absence">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{-- {{ trans('global.absence.fields.NIK') }} --}}
                            NIK
                        </th>
                        <th>
                            {{ trans('global.absence.fields.staff_name') }}
                        </th>
                        <th>
                            Work Type
                            </th>
                        <th>
                            {{ trans('global.absence.fields.subdapertement') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.job_name') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.abtotal') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.jumlah_libur') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.total_efektif_kerja') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.hadir') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.alfa') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.izin') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.dinas_luar') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.cuti') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.jam_hadir') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.jam_istirahat') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.dinas_dalam') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.jam_dinas_dalam') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.jam_terlambat') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.terlambat') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.jam_permisi') }}
                        </th>
                        <th>
                            {{ trans('global.absence.fields.permisi') }}
                        </th>
                        <th>
                            Lembur
                        </th>
                        <th>
                        Jam Lembur
                        </th>
                   
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tfoot align="left">
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>

                        {{-- <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th> --}}
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script>
    $(function () {
        let searchParams = new URLSearchParams(window.location.search)
        let monthyear = searchParams.get('monthyear')
        if (monthyear) {
            $("#monthyear").val(monthyear);
        }else{
            $("#monthyear").val('');
        }

        let from = searchParams.get('from')
        if (from) {
            $("#from").val(from);
        }else{
            // $("#from").val('');
        }
        let to = searchParams.get('to')
        if (to) {
            $("#to").val(to);
        }else{
            // $("#to").val('');
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
    @can('absence_delete')
    dtButtons.push(deleteButton)
    @endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    serverSide: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.absence.reportAbsence') }}",
      data: {
        'monthyear': $("#monthyear").val(),
        'from': $("#from").val(),
        'to': $("#to").val(),
      },
      dataType: "JSON"
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no', searchable : false,orderable: false },
        { data: 'NIK', name: 'staffs.NIK' },
        { data: 'staff_name', name: 'staffs.name' },
        { data: 'work_type', name: 'work_types.type' },
        { data: 'subdapertement_name', name: 'subdapertements.name' },
        { data: 'job_name', name: 'job_name', searchable : false,orderable: false },
        { data: 'abtotal', name: 'abtotal', searchable : false,orderable: false },
        { data: 'jumlah_libur', name: 'jumlah_libur', searchable : false,orderable: false },
        { data: 'efective_kerja', name: 'efective_kerja', searchable : false,orderable: false },
        { data: 'hadir', name: 'hadir', searchable : false,orderable: false },
        { data: 'alfa', name: 'alfa', searchable : false,orderable: false },
        { data: 'izin', name: 'izin', searchable : false,orderable: false },
        { data: 'dinas_luar', name: 'dinas_luar', searchable : false,orderable: false },
        { data: 'cuti', name: 'cuti', searchable : false,orderable: false },
        { data: 'jam_hadir', name: 'jam_hadir', searchable : false,orderable: false },
        { data: 'jam_istirahat', name: 'jam_istirahat', searchable : false,orderable: false },
        
        { data: 'dinas_dalam', name: 'dinas_dalam', searchable : false,orderable: false },
        { data: 'jam_dinas_dalam', name: 'jam_dinas_dalam', searchable : false,orderable: false },
        { data: 'jam_lambat', name: 'jam_lambat', searchable : false,orderable: false },
        { data: 'lambat', name: 'lambat', searchable : false,orderable: false },
        { data: 'jam_permisi', name: 'jam_permisi', searchable : false,orderable: false },
        { data: 'permisi', name: 'permisi', searchable : false,orderable: false },
        { data: 'lembur', name: 'lembur', searchable : false,orderable: false },
        { data: 'jam_lembur', name: 'jam_lembur', searchable : false,orderable: false },
        // { data: 'late', name: 'late' },
        // { data: 'image', name: 'image' ,  render: function( data, type, full, meta ) {
        //                 return "<img src=\"{{ asset('') }}"+ data + "\" width=\"150\"/>";
        //             }},
        // { data: 'staff_image', name: 'staff_image' ,  render: function( data, type, full, meta ) {
        //                 return "<img src=\"{{ asset('') }}"+ data + "\" width=\"150\"/>";
        //             }},
        // { data: 'updated_at', name: 'updated_at' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // converting to interger to find total
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            // computing column Total of the complete result 
            var Hadir = api
                .column( 10 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var Alpha = api
                .column( 11 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var Izin = api
                .column( 12 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
              
                var DinasL = api
                .column( 13 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var Cuti = api
                .column( 14 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var DinasD = api
                .column( 17 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var Lambat = api
                .column( 20 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var Permisi = api
                .column( 22 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var Libur = api
                .column( 8 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var Lembur = api
                .column( 23 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var Permisi = api
                .column( 22 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
	    // Update footer by showing the total with the reference of the column index 
	    $( api.column( 1 ).footer() ).html('Total');
        $( api.column( 10 ).footer() ).html(Hadir.toLocaleString("en-GB"));
        $( api.column( 11 ).footer() ).html(Alpha.toLocaleString("en-GB"));
        $( api.column( 12 ).footer() ).html(Izin.toLocaleString("en-GB"));
        $( api.column( 13 ).footer() ).html(DinasL.toLocaleString("en-GB"));
        $( api.column( 14 ).footer() ).html(Cuti.toLocaleString("en-GB"));
        $( api.column( 17 ).footer() ).html(DinasD.toLocaleString("en-GB"));
        $( api.column( 20 ).footer() ).html(Lambat.toLocaleString("en-GB"));
        $( api.column( 23 ).footer() ).html(Lembur.toLocaleString("en-GB"));
        $( api.column( 22 ).footer() ).html(Permisi.toLocaleString("en-GB"));
        $( api.column( 8 ).footer() ).html(Libur.toLocaleString("en-GB"));
        },

  };

  $('.datatable-absence').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
<script>
    
    $('#dpRM').datetimepicker({
        viewMode : 'months',
        format : 'YYYY-MM',
        toolbarPlacement: "top",
        allowInputToggle: true,
        icons: {
            time: 'fa fa-time',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove',
        }
    });
    
    $("#dpRM").on("dp.show", function(e) {
       $(e.target).data("DateTimePicker").viewMode("months"); 
    });
    </script>
@endsection
@endsection