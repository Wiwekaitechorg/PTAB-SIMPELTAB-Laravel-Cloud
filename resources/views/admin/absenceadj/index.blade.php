@extends('layouts.admin')
@section('content')
@can('absenceadj_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.absenceadj.create') }}">
                {{ trans('global.add') }} {{ trans('global.absenceadj.title_singular') }}
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
        {{ trans('global.absenceadj.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.absenceadj.fields.session_start') }}-{{ trans('global.absenceadj.fields.session_end') }}
                        </th>
                        <th>
                            {{ trans('global.absenceadj.fields.staff_id') }}
                        </th>
                        <th>
                            {{ trans('global.absenceadj.fields.user_id') }}
                        </th>                        
                        <th>
                            {{ trans('global.absenceadj.fields.memo') }}
                        </th>                        
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($absenceadjs as $key => $absenceadj)
                        <tr data-entry-id="{{ $absenceadj->id }}">
                            <td>

                            </td>
                            <td>
                            {{ $absenceadj->session_start ?? '' }}-{{ $absenceadj->session_end ?? '' }}
                            </td>
                            <td>
                            {{ $absenceadj->staff->name ?? '' }}
                            </td>
                            <td>
                            {{ $absenceadj->user->name ?? '' }}
                            </td>                            
                            <td>
                            {{ $absenceadj->memo ?? '' }}
                            </td>
                            <td>
                                <!-- @can('absenceadj_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.absenceadj.show', $absenceadj->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan -->
                                @can('absenceadj_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.absenceadj.edit', $absenceadj->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('absenceadj_delete')
                                    <form action="{{ route('admin.absenceadj.destroy', $absenceadj->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
            url: "{{ route('admin.absenceadj.massDestroy') }}",
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


            @can('absenceadj_delete')
                dtButtons.push(deleteButton)
            @endcan

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

        </script>
    @endsection 
@endsection