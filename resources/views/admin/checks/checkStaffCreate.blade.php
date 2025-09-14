@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.check_staff.title_singular') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            Work Unit
                        </th>
                        <th>
                            {{ trans('global.check_staff.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.check_staff.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.check_staff.fields.phone') }}
                        </th>
                        <th>
                            Jumlah Pekerjaan
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
                                {{ $staff->work_unit_name ?? '    ' }}
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
                                {{ $staff->jumlahtindakan ?? '' }}
                                </td>
                            <td>
                                @can('check_staff_create')
                                    <form check="{{ route('admin.checks.checkStaffStore') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        @csrf
                                        @method('POST')
                                        <input type="hidden" value="{{$check_id}}" name="check_id" >
                                        <input type="hidden" value="{{$staff->id}}" name="staff_id" >
                                        <button class="btn btn-xs btn-success"  
                                            @foreach ($check_staffs_list as $list )
                                                @if ($list->staff_id == $staff->id)
                                                    {{'disabled'}}
                                                @endif
                                            @endforeach

                                            @foreach ($check_staffs->staff as $check_staff )
                                               {{$staff->id == $check_staff->id ? 'disabled' : ''}}
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
            check: function (e, dt, node, config) {
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