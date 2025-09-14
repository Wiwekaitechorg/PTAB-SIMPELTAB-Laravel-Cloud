@extends('layouts.admin')
@section('content')
@can('absence_all_access')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.staffSpecials.create") }}">
                {{ trans('global.add') }} {{ trans('global.staffSpecial.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.staffSpecial.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.staffSpecial.fields.name') }}
                        </th>
                      
                        <th>
                            {{ trans('global.staffSpecial.fields.phone') }}
                        </th>
                        <th>
                            {{ trans('global.staffSpecial.fields.expired_date') }}
                        </th>
                        <th>
                            {{ trans('global.staffSpecial.fields.fingerprint') }}
                        </th>
                        <th>
                            {{ trans('global.staffSpecial.fields.camera') }}
                        </th>
                        <th>
                            {{ trans('global.staffSpecial.fields.gps') }}
                        </th>
                      
                       
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffSpecials as $key => $staffSpecial)
                        <tr data-entry-id="{{ $staffSpecial->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $staffSpecial->name ?? '' }}
                            </td>
                          
                          
                            <td>
                                {{ $staffSpecial->phone ?? '' }}
                            </td>
                            <td>
                                {{ $staffSpecial->expired_date ?? '' }}
                            </td>
                            <td>
                                {{ $staffSpecial->fingerprint ?? '' }}
                            </td>

                            <td>
                                {{ $staffSpecial->camera ?? '' }}
                            </td>

                            <td>
                                {{ $staffSpecial->gps ?? '' }}
                            </td>
                            
                            <td>
                                {{-- @can('staff_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.staffSpecials.show', $staffSpecial->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan --}}
                                @can('staff_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.staffSpecials.edit', $staffSpecial->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('staff_delete')
                                    <form action="{{ route('admin.staffSpecials.destroy', $staffSpecial->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    url: "{{ route('admin.staffSpecials.massDestroy') }}",
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
@can('staffSpecial_delete')
  dtButtons.push(deleteButton)
@endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
})

</script>
@endsection
@endsection