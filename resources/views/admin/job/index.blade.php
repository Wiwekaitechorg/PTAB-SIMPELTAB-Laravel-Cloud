@extends('layouts.admin')
@section('content')
@can('user_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.job.create") }}">
                {{ trans('global.add') }} {{ trans('global.job.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.job.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.job.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.job.fields.dapertement') }}
                        </th>
                        <th>
                            {{ trans('global.job.fields.dapertement_id') }}
                        </th>

                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $key => $job)
                        <tr data-entry-id="{{ $job->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $job->name ?? '' }}
                            </td>
                            <td>
                            {{ $job->dapertement_name ?? '' }}
                            </td>
                            <td>
                                {{ $job->subdapertement_name ?? '' }}
                                </td>
                            <td>
                                @can('user_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.job.show', $job->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan
                                @can('user_show')
                                <a class="btn btn-xs btn-primary" href="{{ route('admin.shift_planner_staff.index', ['id'=>$job->id]) }}">
                                    Jadwal Shift
                                </a>
                            @endcan
                                {{-- @if ($job->id != 1 && $job->id != 2) --}}

                                @can('user_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.job.edit', $job->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('user_delete')
                                    <form action="{{ route('admin.job.destroy', $job->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    url: "{{ route('admin.job.massDestroy') }}",
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