@extends('layouts.admin')
@section('content')
{{-- @can('user_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.attendance.create") }}">
                {{ trans('global.add') }} {{ trans('global.attendance.title_singular') }}
            </a>
        </div>
    </div>
@endcan --}}
<div class="card">
    <div class="card-header">
        Data Absen
    </div>

    <div class="card-body">

        <div class="col-md-12 row">
            <div class="col-md-6">
                 <form action="" id="filtersForm">
                    <div class="input-group">
                        <select id="staff" name="staff" class="form-control">
                            <option value="">== Semua Keterangan ==</option>
                            <option value="1">Masuk</option>
                        </select>
                    </div> 
                    <br>
                            {{-- <div class="input-group">
                              
                                <select id="staff" name="staff" class="form-control">
                                    <option value="">== Semua Dapertement ==</option>
                                    <option value="1">Distribusi</option>
                                </select>
                            </div>  --}}
                            <br>
                        </div> 
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select id="staff" name="staff" class="form-control">
                                        <option value="">== Semua Dapertement ==</option>
                                        <option value="1">Kadek</option>
                                    </select>
                            </div>
                           </div>
                        <span class="input-group-btn">
                        &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                        </span>
                                   
                 </form>
                </div>



        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                           Nama
                        </th>
                        <th>
                            keterangan
                        </th>
                        <th>
                            hari
                        </th>
                        <th>
                           status
                        </th>
                        <th>
                            lambat
                        </th>
                     
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                   @for ($i = 0 ; $i < count($attendances); $i++)
                       
                 
                        <tr data-entry-id="{{ $attendances[$i]['id'] }}">
                            <td>

                            </td>
                            <td>
                                {{ $attendances[$i]['name'] ?? '' }}
                            </td>
                            <td>
                                @if ($attendances[$i]['activity'] == "reguler" && $attendances[$i]['part'] == "1")
                                    Masuk
                                @elseif ($attendances[$i]['activity'] == "reguler" && $attendances[$i]['part'] == "2")
                                Pulang
                                @else
                                {{ $attendances[$i]['activity'] ?? '' }}
                                @endif
                           
                            </td>
                            <td>
                                {{ $attendances[$i]['day'] ?? '' }}, {{ $attendances[$i]['date'] ?? '' }}
                                </td>
                                <td>
                                    {{ $attendances[$i]['status'] ?? '' }}
                                    </td>
                                    <td>
                                        @if ($attendances[$i]['late'] == 0)
                                        Tidak
                                        @else
                                            Ya
                                        @endif
                                        </td>
                            <td>
                                @if ($attendances[$i]['status'] == "pending")
                            
                                <a class="btn btn-xs btn-primary" href="">
                                    {{ trans('global.approve') }}
                                </a>
                                        
                                @endif
                                @can('user_show')
                                    <a class="btn btn-xs btn-primary" href="">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan
                              

                                {{-- @can('user_edit')
                                    <a class="btn btn-xs btn-info" href="">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('user_delete')
                                    <form action="" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan --}}
                                                                    
                           
                            </td>

                        </tr>
                        @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script>
    $(function () {
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
@can('user_delete')
  dtButtons.push(deleteButton)
@endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
})

</script>
@endsection
@endsection