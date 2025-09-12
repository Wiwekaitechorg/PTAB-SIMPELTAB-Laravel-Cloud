@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.wa.title_singular') }} {{ trans('global.list') }}
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
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No. SBG
                        </th>
                        <th>
                            {{ trans('global.wa.fields.id_wa') }}
                        </th>
                        <th>
                            {{ trans('global.wa.fields.phone') }}
                        </th>
                        <th>
                            {{ trans('global.wa.fields.created_at') }}
                        </th>
                        <th>
                            {{ trans('global.wa.fields.updated_at') }}
                        </th>
                        <th>
                            {{ trans('global.wa.fields.status') }}
                        </th>
                        {{-- <th>
                            {{ trans('global.wa.fields.customer_id') }}
                        </th> --}}
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wa_historys as $key => $wa_history)
                        <tr data-entry-id="{{ $wa_history->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $wa_history->id ?? '' }}
                            </td>
                            <td>
                                {{ $wa_history->id_wa ?? '' }}
                            </td>
                            <td>
                            {{ $wa_history->phone ?? '' }}
                            </td>
                            <td>
                            {{ $wa_history->created_at ?? '' }}
                            </td>
                            <td>
                                {{ $wa_history->updated_at ?? '' }}
                            </td>
                            <td>
                                @if ($wa_history->status == "pending")
                                <a class="btn btn-xs btn-warning"> {{ $wa_history->status ?? '' }}</a>
                                @elseif ($wa_history->status == "sent")
                                <a class="btn btn-xs btn-primary"> {{ $wa_history->status ?? '' }}</a>
                                @elseif ($wa_history->status == "read")
                                <a class="btn btn-xs btn-success"> {{ $wa_history->status ?? '' }}</a>
                                @elseif ($wa_history->status == "cancel")
                                <a class="btn btn-xs btn-secondary"> {{ $wa_history->status ?? '' }}</a>
                                @else
                                <a class="btn btn-xs btn-secondary"> {{ $wa_history->status ?? '' }}</a>
                                @endif
                               
                               
                            </td>
                            {{-- <td>
                                {{ $wa_history->customer_id ?? '' }}
                            </td> --}}
                            <td>
                                {{-- @can('user_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.users.show', $user->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan --}}
                                {{-- @can('user_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.users.edit', $user->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan --}}
                                @can('user_delete')
                                    <form action="{{ route('admin.wablast.destroy', $wa_history->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>

                        </tr>
                    @endforeach
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
    url: "{{ route('admin.wablast.massDestroy') }}",
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