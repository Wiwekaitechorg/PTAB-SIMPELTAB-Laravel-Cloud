@extends('layouts.admin')
@section('content')
@can('actionWmStaff_create')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        <a class="btn btn-success" href="{{ route('admin.actionWmStaff.create', [$id]) }}">
            {{ trans('global.add') }} Staff
        </a>
    </div>
</div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.staff.title') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            Unit Kerja
                        </th>
                        <th>
                            Kode
                        </th>
                        <th>
                            Nama
                        </th>
                        <th>
                            Phone
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffs as $key => $data)
                    <tr data-entry-id="{{ $data->id }}">
                        <td>

                        </td>
                        <td>
                            {{ $data->work_unit_name ?? '' }}
                        </td>
                        <td>
                            {{ $data->staff_code ?? '' }}
                        </td>
                        <td>
                            {{ $data->staff_name ?? '' }}
                        </td>
                        <td>
                            {{ $data->staff_phone ?? '' }}
                        </td>
                        <td>
                            {{-- <!-- @can('categories_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.categories.show', $data->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan -->
                            @can('categories_edit')
                            <a class="btn btn-xs btn-info" href="{{ route('admin.categories.edit', $data->id) }}">
                                {{ trans('global.edit') }}
                            </a>
                            @endcan
                            @can('categories_delete')
                            <form action="{{ route('admin.categories.destroy', $data->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                            </form>
                            @endcan --}}
                            @can('actionWmStaff_delete')
                            <form action="{{ route('admin.actionWmStaff.destroy') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                <input type="hidden" name="_method" value="POST">
                                <input type="hidden" name="staff_id" value="{{$data->staff_id}}">
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
    $(function() {
        let deleteButtonTrans = '{{ trans('
        global.datatables.delete ') }}'
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.categories.massDestroy') }}",
            className: 'btn-danger',
            action: function(e, dt, node, config) {
                var ids = $.map(dt.rows({
                    selected: true
                }).nodes(), function(entry) {
                    return $(entry).data('entry-id')
                });

                if (ids.length === 0) {
                    alert('{{ trans('
                        global.datatables.zero_selected ') }}')

                    return null;
                }

                if (confirm('{{ trans('
                        global.areYouSure ') }}')) {
                    $.ajax({
                            headers: {
                                'x-csrf-token': _token
                            },
                            method: 'POST',
                            url: config.url,
                            data: {
                                ids: ids,
                                _method: 'DELETE'
                            }
                        })
                        .done(function() {
                            location.reload()
                        })
                }
            }
        }
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)


        @can('categories_delete')
        dtButtons.push(deleteButton)
        @endcan

        $('.datatable:not(.ajaxTable)').DataTable({
            buttons: dtButtons
        })
    })
</script>
@endsection
@endsection