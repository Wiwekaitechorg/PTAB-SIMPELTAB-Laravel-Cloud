@extends('layouts.admin')
@section('content')
@can('subdapertement_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.subdapertements.create') }}">
                {{ trans('global.add') }} {{ trans('global.subdapertement.title_singular') }}
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
        {{ trans('global.subdapertement.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.subdapertement.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.dapertement.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.subdapertement.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.subdapertement.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($subdapertements as $key => $subdapertement)
                        <tr data-entry-id="{{ $subdapertement->id }}">
                            <td>

                            </td>
                            <td>
                            {{ $subdapertement->code ?? '' }}
                            </td>
                            <td>
                            {{ $subdapertement->dapertement->name ?? '' }}
                            </td>
                            <td>
                            {{ $subdapertement->name ?? '' }}
                            </td>
                            <td>
                            {{ $subdapertement->description ?? '' }}
                            </td>
                            <td>
                                <!-- @can('dapertements_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.subdapertements.show', $subdapertement->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan -->
                                @can('subdapertement_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.subdapertements.edit', $subdapertement->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('subdapertement_delete')
                                    <form action="{{ route('admin.subdapertements.destroy', $subdapertement->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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


            @can('subdapertement_delete')
                dtButtons.push(deleteButton)
            @endcan

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

        </script>
    @endsection 
@endsection