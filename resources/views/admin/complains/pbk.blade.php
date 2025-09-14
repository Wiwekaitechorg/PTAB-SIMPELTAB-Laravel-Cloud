@extends('layouts.admin')
@section('content')

@if($errors->any())
<!-- <h4>{{$errors->first()}}</h4> -->
    <?php 
        echo "<script> alert('{$errors->first()}')</script>";
    ?>
@endif

<div class="card">
    <div class="card-header">
        {{ trans('global.pbk.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.pbk.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.pbk.fields.number') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pbks as $key => $pbk)
                        <tr data-entry-id="{{ $pbk->Name }}">
                            <td>

                            </td>
                            <td>
                            {{ $pbk->Name ?? '' }}
                            </td>
                            <td>
                            {{ $pbk->Number ?? '' }}
                            </td>
                            <td>
                               @can('complain_access')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.complain.complainsOpp', ['operator' => $pbk->Name]) }}">
                                        Pilih
                                    </a>
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