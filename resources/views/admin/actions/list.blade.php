<?php
    // $status = 'close';

    foreach ($actions as $action) {
        foreach ($action->staff as $key => $staff) {
            if($staff->status == 'pending'){
               $status ='pending';
            }
        }
    }
?>

@extends('layouts.admin')
@section('content')
@can('action_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.actions.create', $ticket_id) }}">
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
                            {{ trans('global.action.fields.status') }}
                        </th>
                        <th>
                            {{ trans('global.action.fields.description') }}
                        </th>
                        <th>
                            {{ trans('global.action.fields.staff') }}
                        </th>
                        <th>
                            {{ trans('global.action.fields.dapertement') }}
                        </th>
                        <th>
                            {{ trans('global.action.fields.subdapertement') }}
                        </th>
                        <th>
                            {{ trans('global.action.fields.ticket') }}
                        </th>
                        <th>
                            {{ trans('global.action.fields.start') }}
                        </th>
                        <th>
                            {{ trans('global.action.fields.end') }}
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
                            @if  ($action->status=='pending')
                            <button type="button" class="btn btn-warning btn-sm" disabled>{{$action->status}}</button>
                            @endif
                            @if  ($action->status=='active')
                            <button type="button" class="btn btn-primary btn-sm" disabled>{{$action->status}}</button>
                            @endif
                            @if  ($action->status=='close')
                            <button type="button" class="btn btn-success btn-sm" disabled>{{$action->status}}</button>
                            @endif
                            </td>
                            <td>
                                {{ $action->description ?? '' }}
                            </td>
                            <td>
                                @foreach ($action->staff as $staff )
                                    {{'* ' . $staff->name}}
                                    <br>
                                @endforeach
                            </td>
                            <td>
                               {{$action->dapertement->name}}
                            </td>
                            <td>
                               {{$action->subdapertement?$action->subdapertement->name:''}}
                            </td>
                            <td>
                               {{$action->ticket->title}}
                            </td>
                            <td>
                               {{$action->start}}
                            </td>
                            <td>
                               {{isset($action->end) ? $action->end : '00-00-00 00:00:00'}}
                            </td>
                            <td>
                                <!-- @can('actions_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.actions.show', $action->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan -->

                                @can('action_staff_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.actions.actionStaffEdit', [$action->id]) }}">
                                        Update Status Tindakan
                                    </a>
                                @endcan
                                
                           
                                @can('action_staff_create')
                                    <a class="btn btn-xs btn-success"  href="{{ route('admin.actions.actionStaff', $action->id) }}">
                                        Tambah {{ trans('global.staff.title') }}
                                    </a>
                                @endcan

                                @if ($action->status == "close")
                                @can('action_staff_edit')
                                <a class="btn btn-xs btn-warning"  href="{{ route('admin.actions.additionalDone', $action->id) }}">
                                    Tambah Foto Selesai
                                </a>
                            @endcan
                            @endif
                              
                                <!-- start surya buat -->
                                
                        

                                <!-- @if ($action->status == "pending")
                                    @can('action_print_service')
                                        <a class="btn btn-xs btn-primary"  href="{{ route('admin.actions.printservice') }}">
                                            {{ trans('global.action.print_service') }}
                                        </a>
                                    @endcan
                                @endif

                                @if ($action->status == "pending")
                                @can('action_print_spk')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.actions.printspk') }}">
                                        {{ trans('global.action.print_SPK') }}
                                    </a>
                                @endcan
                                @endif

                                @if ($action->status == "close")
                                @can('action_print_report')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.actions.printreport') }}">
                                        {{ trans('global.action.print_Report') }}
                                    </a>
                                @endcan
                                @endif -->

                                <!-- end surya buat -->
                                @if ($action->status == "pending")
                                @can('action_delete')
                                    <form action="{{ route('admin.actions.destroy', $action->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                                @endif
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