@extends('layouts.admin')
@section('content')
@can('staff_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.staffs.create') }}">
                {{ trans('global.add') }} {{ trans('global.staff.title_singular') }}
            </a>
        </div>
    </div>
    
@endcan
@if($errors->any())
<!-- <h4>{{$errors->first()}}</h4> -->
    <?php 
        echo "<script> alert('{$errors->first()}')</script>";
    ?>
@endif
<div class="card">

    <div class="card-header">
        {{ trans('global.staff.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
             <form action="" id="filtersForm">
                <div class="input-group">
                    <select id="dapertement_id" name="dapertement_id" class="form-control">
                        <option value="">== Semua Departemen ==</option>
                        @foreach ($dapertements as $dapertement )
                            <option value="{{$dapertement->id}}">{{$dapertement->name}}</option>
                        @endforeach
                    </select>
                    <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                </div>                
             </form>
             </div> 
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-staff">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.staff.fields.NIK') }}
                        </th>
                        <th>
                            {{ trans('global.staff.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.staff.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.staff.fields.dapertement') }}
                        </th>
                        <th>
                            {{ trans('global.staff.fields.subdapertement') }}
                        </th>
                        <th>
                            {{ trans('global.staff.fields.phone') }}
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
        let dapertement_id = searchParams.get('dapertement_id')
        if (dapertement_id) {
            $("#dapertement_id").val(dapertement_id);
        }else{
            $("#dapertement_id").val('');
        }

        // console.log('type : ', type);

  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.staffs.massDestroy') }}",
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
    @can('staff_delete')
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
      url: "{{ route('admin.staffs.index') }}",
      data: {
        'dapertement_id': $("#dapertement_id").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'NIK', name: 'NIK' },
        { data: 'code', name: 'code' },
        { data: 'name', name: 'name' },
        { data: 'dapertement', name: 'dapertement' },
        { data: 'subdapertement', name: 'subdapertement' },
        { data: 'phone', name: 'phone' },
      
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    order: [[ 2, 'asc' ]],
    pageLength: 100,
  };

  $('.datatable-staff').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection