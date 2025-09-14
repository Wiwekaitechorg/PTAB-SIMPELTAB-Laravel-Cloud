<?php
    // $status = 'close';

    foreach ($checks as $check) {
        foreach ($check->staff as $key => $staff) {
            if($staff->status == 'pending'){
               $status ='pending';
            }
        }
    }
?>

@extends('layouts.admin')
@section('content')
@can('check_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.checks.create', $ticket_id) }}">
                {{ trans('global.add') }} {{ trans('global.check.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.check.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.check.fields.status') }}
                        </th>
                        <th>
                            {{ trans('global.check.fields.description') }}
                        </th>
                        <th>
                            {{ trans('global.check.fields.staff') }}
                        </th>
                        <th>
                            {{ trans('global.check.fields.dapertement') }}
                        </th>
                        <th>
                            {{ trans('global.check.fields.subdapertement') }}
                        </th>
                        <th>
                            {{ trans('global.check.fields.ticket') }}
                        </th>
                        <th>
                            {{ trans('global.check.fields.start') }}
                        </th>
                        <th>
                            {{ trans('global.check.fields.end') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($checks as $key => $check)
                        <tr data-entry-id="{{ $check->id }}">
                            <td>

                            </td>
                            <td>
                            @if  ($check->status=='pending')
                            <button type="button" class="btn btn-warning btn-sm" disabled>{{$check->status}}</button>
                            @endif
                            @if  ($check->status=='active')
                            <button type="button" class="btn btn-primary btn-sm" disabled>{{$check->status}}</button>
                            @endif
                            @if  ($check->status=='close')
                            <button type="button" class="btn btn-success btn-sm" disabled>{{$check->status}}</button>
                            @endif
                            </td>
                            <td>
                                {{ $check->description ?? '' }}
                            </td>
                            <td>
                                @foreach ($check->staff as $staff )
                                    {{'* ' . $staff->name}}
                                    <br>
                                @endforeach
                            </td>
                            <td>
                               {{$check->dapertement->name}}
                            </td>
                            <td>
                               {{$check->subdapertement->name}}
                            </td>
                            <td>
                               {{$check->ticket->title}}
                            </td>
                            <td>
                               {{$check->start}}
                            </td>
                            <td>
                               {{isset($check->end) ? $check->end : '00-00-00 00:00:00'}}
                            </td>
                            <td>
                                <!-- @can('checks_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.checks.show', $check->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan -->

                                @can('check_staff_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.checks.checkStaffEdit', [$check->id]) }}">
                                        Update Status Tindakan
                                    </a>
                                @endcan
                                
                           
                                @can('check_staff_create')
                                    <a class="btn btn-xs btn-success"  href="{{ route('admin.checks.checkStaff', $check->id) }}">
                                        Tambah {{ trans('global.staff.title') }}
                                    </a>
                                @endcan

                                @if ($check->status == "close")
                                @can('check_staff_edit')
                                <a class="btn btn-xs btn-warning"  href="{{ route('admin.checks.additionalDone', $check->id) }}">
                                    Tambah Foto Selesai
                                </a>
                            @endcan
                            @endif
                              
                                <!-- start surya buat -->
                                
                        

                                <!-- @if ($check->status == "pending")
                                    @can('check_print_service')
                                        <a class="btn btn-xs btn-primary"  href="{{ route('admin.checks.printservice') }}">
                                            {{ trans('global.check.print_service') }}
                                        </a>
                                    @endcan
                                @endif

                                @if ($check->status == "pending")
                                @can('check_print_spk')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.checks.printspk') }}">
                                        {{ trans('global.check.print_SPK') }}
                                    </a>
                                @endcan
                                @endif

                                @if ($check->status == "close")
                                @can('check_print_report')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.checks.printreport') }}">
                                        {{ trans('global.check.print_Report') }}
                                    </a>
                                @endcan
                                @endif -->

                                <!-- end surya buat -->
                                @if ($check->status == "pending")
                                @can('check_delete')
                                    <form check="{{ route('admin.checks.destroy', $check->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
            url: "{{ route('admin.checks.massDestroy') }}",
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


            @can('check_delete')
                dtButtons.push(deleteButton)
            @endcan

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

        </script>
    @endsection 
@endsection