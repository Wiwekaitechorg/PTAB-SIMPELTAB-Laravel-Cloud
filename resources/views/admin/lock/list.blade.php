

@extends('layouts.admin')
@section('content')
@can('lock_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.lock.lockcreate', $lockaction_id) }}">
                {{ trans('global.add') }} {{ trans('global.action.title_singular') }}
            </a>
        </div>
    </div>
@endcan

<div class="card">
    <div class="card-header">
        {{ trans('global.action.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.action.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.action.fields.memo') }}
                        </th>
                        <th>
                            {{ trans('global.lock.type') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($actions as $key => $action)
                        <tr data-entry-id="{{ $action->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $action->code ?? '' }}
                            </td>
                            <td>
                                {{ $action->memo ?? '' }}
                            </td>
                            <td>
                                <?php 
                                    if($action->type =='unplug'){
                                        $action->type ='Cabut';
                                    }elseif($action->type  =='lock'){
                                        $action->type ='Segel';
                                    }elseif($action->type =='lock_resist'){
                                        $action->type ='Hambatan Segel';
                                    }elseif($action->type =='unplug_resist'){
                                        $action->type ='Hambatan Cabut';
                                    }
                                ?>
                                {{ $action->type}}
                            </td>
                            <td>
                                @can('action_staff_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.lock.LockView', [$action->id]) }}">
                                       View
                                    </a>
                                @endcan
                                @can('action_delete')
                                    <form action="{{ route('admin.lock.actiondestroy', $action->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
            url: "{{ route('admin.actions.massDestroy') }}",
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


            @can('action_delete')
                dtButtons.push(deleteButton)
            @endcan

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

        </script>
    @endsection 
@endsection