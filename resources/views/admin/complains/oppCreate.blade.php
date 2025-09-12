@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.add') }} {{ trans('global.customer.title_singular') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                       <th>
                            {{ trans('global.customer.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.customer.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.customer.fields.address') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($opps as $key => $opp)
                        <tr data-entry-id="{{ $opp->id }}">
                            <td>

                            </td>
                            <td>
                            {{ $opp->nomorrekening ?? '' }}
                            </td>
                            <td>
                            {{ $opp->pelanggan->namapelanggan ?? '    ' }}
                            </td>
                            <td>
                            {{ $opp->pelanggan->alamat ?? '' }}
                            </td>
                            <td>
                                @can('complain_create')
                                    <form action="{{ route('admin.complain.complainOppStore') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        @csrf
                                        @method('POST')
                                        <input type="hidden" value="{{$operator}}" name="operator" >
                                        <input type="hidden" value="{{$opp->nomorrekening}}" name="nomorrekening" >
                                        <button class="btn btn-xs btn-success"  
                                        @if(!empty($complainopps))    
                                        @foreach ($complainopps as $complainopp )
                                                @if ($opp->nomorrekening == $complainopp->nomorrekening)
                                                    {{'disabled'}}
                                                @endif
                                            @endforeach
                                            @endif
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
            url: "{{ route('admin.dapertements.massDestroy') }}",
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


            @can('dapertement_delete')
                //dtButtons.push(deleteButton)
            @endcan

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

</script>
@endsection 

@endsection