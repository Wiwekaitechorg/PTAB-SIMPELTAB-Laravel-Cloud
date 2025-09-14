@extends('layouts.admin')
@section('content')
@can('wablast_access')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.WaTemplate.create") }}">
                {{ trans('global.add') }} {{ trans('global.WaTemplate.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.WaTemplate.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.WaTemplate.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.WaTemplate.fields.category') }}
                        </th>
                        <th>
                            {{ trans('global.WaTemplate.fields.message') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($WaTemplates as $key => $WaTemplate)
                        <tr data-entry-id="{{ $WaTemplate->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $WaTemplate->name ?? '' }}
                            </td>
                            <td>
                                {{ $WaTemplate->category?? '' }}
                                </td>
                            <td>
                            {{ $WaTemplate->message ?? '' }}
                            </td>
                            <td>
                                @can('wablast_access')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.WaTemplate.show', $WaTemplate->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan
                                @can('wablast_access')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.WaTemplate.edit', $WaTemplate->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('user_delete')
                                    <form action="{{ route('admin.WaTemplate.destroy', $WaTemplate->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    url: "{{ route('admin.WaTemplate.massDestroy') }}",
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