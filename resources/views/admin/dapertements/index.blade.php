@extends('layouts.admin')
@section('content')
@can('dapertement_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.dapertements.create') }}">
                {{ trans('global.add') }} {{ trans('global.dapertement.title_singular') }}
            </a>
        </div>
    </div>
@endcan

@if($errors->any())
<!-- <h4>{{$errors->first()}}</h4> -->
    <?php 
        echo "<script> alert('{$errors->first()}')</script>";
    ?>
@endif

<div class="card">
    <div class="card-header">
        {{ trans('global.dapertement.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.dapertement.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.dapertement.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.dapertement.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($dapertements as $key => $dapertement)
                        <tr data-entry-id="{{ $dapertement->id }}">
                            <td>

                            </td>
                            <td>
                            {{ $dapertement->code ?? '' }}
                            </td>
                            <td>
                            {{ $dapertement->name ?? '' }}
                            </td>
                            <td>
                            {{ $dapertement->description ?? '' }}
                            </td>
                            <td>
                                <!-- @can('dapertements_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.dapertements.show', $dapertement->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan -->
                                @can('dapertement_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.dapertements.edit', $dapertement->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('dapertement_delete')
                                    <form action="{{ route('admin.dapertements.destroy', $dapertement->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
                dtButtons.push(deleteButton)
            @endcan

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

        </script>
    @endsection 
@endsection