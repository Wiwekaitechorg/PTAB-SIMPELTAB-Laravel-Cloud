@extends('layouts.admin')
@section('content')
<!-- @can('customerrequests_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.customerrequests.create') }}">
                {{ trans('global.add') }} {{ trans('global.customerrequest.title_singular') }}
            </a>
        </div>
    </div>
@endcan -->
<div class="card">
    <div class="card-header">
        {{ trans('global.customerrequest.fields.name') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.customerrequest.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.customerrequest.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.customerrequest.fields.phone') }}
                        </th>
                        <th>
                            {{ trans('global.customerrequest.fields.address') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($customerrequests as $key => $customerrequest)
                        <tr data-entry-id="{{ $customerrequest->id }}">
                            <td>

                            </td>
                            <td>
                            {{ $customerrequest->code ?? '' }}
                            </td>
                            <td>
                            {{ $customerrequest->customer->name ?? '' }}
                            </td>
                            <td>
                            {{ $customerrequest->customer->telp ?? '' }}
                            </td>
                            <td>
                            {{ $customerrequest->address ?? '' }}
                            </td>
                            <td>
                                @if($customerrequest->status =='pending')
                                @can('customerrequests_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.customerrequests.edit', $customerrequest->id) }}">
                                        Setujui
                                    </a>
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
        <!-- <script>
            $(function () {
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.subdapertements.massDestroy') }}",
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


            @can('customerrequests_delete')
                dtButtons.push(deleteButton)
            @endcan

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

        </script> -->
    @endsection 
@endsection