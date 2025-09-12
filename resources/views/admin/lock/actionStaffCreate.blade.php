@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.action_staff.title_singular') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.action_staff.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.action_staff.fields.name') }}
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
                @foreach($staffs as $key => $staff)
                        <tr data-entry-id="{{ $staff->id }}">
                            <td>

                            </td>
                            <td>
                            {{ $staff->code ?? '' }}
                            </td>
                            <td>
                            {{ $staff->name ?? '    ' }}
                            </td>
                            <td>
                            {{ $staff->phone ?? '' }}
                            </td>
                            <td>
                                @can('lock_staff_create')
                                    <form action="{{ route('admin.lock.actionStaffStore') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        @csrf
                                        @method('POST')
                                        <input type="hidden" value="{{$lockaction_id}}" name="lockaction_id" >
                                        <input type="hidden" value="{{$staff->id}}" name="staff_id" >
                                        <button class="btn btn-xs btn-success"  
                                            @foreach ($action_staffs_list as $list )
                                                @if ($list->staff_id == $staff->id)
                                                    {{'disabled'}}
                                                @endif
                                            @endforeach

                                            @foreach ($action_staffs->staff as $action_staff )
                                               {{$staff->id == $action_staff->id ? 'disabled' : ''}}
                                            @endforeach
                                        >   Add
                                        </button>
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

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

</script>
@endsection 

@endsection