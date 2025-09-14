@extends('layouts.admin')
@section('content')
@can('shift_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.shift_group.create",['shift_parent_id'=>$shift_parent_id]) }}">
                {{ trans('global.add') }} {{ trans('global.shift_group.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.shift_group.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.shift_group.fields.title') }}
                        </th>
                        <th>
                            {{ trans('global.shift_group.fields.queue') }}
                        </th>
                    

                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shift_groups as $key => $shift_group)
                        <tr data-entry-id="{{ $shift_group->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $shift_group->title ?? '' }}
                            </td>
                            <td>
                            {{ $shift_group->queue ?? '' }}
                            </td>
                       
                            <td>
                                @can('shift_edit')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.shift_group.show', $shift_group->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                          

                                @can('shift_edit')
                                <a class="btn btn-xs btn-success" href="{{ route('admin.shift_group.schedule', $shift_group->id) }}">
                                   Jadwal
                                </a>
                            @endcan
                                {{-- @if ($shift_group->id != 1 && $shift_group->id != 2) --}}

                                @can('shift_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.shift_group.edit', $shift_group->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('shift_delete')
                                    <form action="{{ route('admin.shift_group.destroy', $shift_group->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                                                                    
                                {{-- @endif --}}
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
    url: "{{ route('admin.shift_group.massDestroy') }}",
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
@can('shift_delete')
  dtButtons.push(deleteButton)
@endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
})

</script>
@endsection
@endsection