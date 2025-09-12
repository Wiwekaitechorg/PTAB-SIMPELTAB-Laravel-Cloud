@extends('layouts.admin')
@section('content')
@can('lock_staff_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.lock.actionStaffCreate', $action->id) }}">
                {{ trans('global.add') }} {{ trans('global.action_staff.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.list') }}  {{ trans('global.action_staff.title_singular') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.action_staff.fields.status') }}
                        </th>
                        <th>
                            {{ trans('global.action_staff.fields.description') }}
                        </th>
                        <th>
                            {{ trans('global.action_staff.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.action_staff.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.action_staff.fields.dapertement') }}
                        </th>
                        <th>
                            {{ trans('global.action_staff.fields.phone') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($action->staff as $key => $staff)
                        <tr data-entry-id="{{ $staff->id }}">
                            <td>

                            </td>
                            <td>
                                {{$staff->pivot->status}}
                            </td>
                            <td>
                                {{$action->description}}    
                            </td>
                            <td>
                                {{ $staff->code ?? '' }}
                            </td>
                            <td>
                                {{ $staff->name ?? '    ' }}
                            </td>
                            <td>
                                {{ $staff->dapertement->name ?? '' }}
                            </td>
                            <td>
                                {{ $staff->phone ?? '' }}
                            </td>
                            <td>
                                @can('lock_staff_delete')
                                    <form action="{{ route('admin.lock.actionStaffDestroy', [$action->id, $staff->id]) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
            url: "{{ route('admin.staffs.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
            var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                return $(entry).data('entry-id')
            });

            if (ids.length === 0) {
                alert('{{ trans('global.datatables.zero_selected') }}')

                return null;
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
        })

        </script>
    @endsection 
@endsection