@extends('layouts.admin2')
@section('content')
{{-- @can('actionWm_create')
@if (count($cek) <= 0)

    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.actionWms.create", [$id]) }}">
                {{ trans('global.add') }} {{ trans('global.actionWms.title_singular') }}
            </a>
        </div>
    </div>
        
@endif
@endcan --}}
<div class="card">
    <div class="card-header">
        {{ trans('global.actionWms.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                           status
                        </th>
                        <th>
                            memo
                        </th>

                        <th>
                            Tindakan
                        </th>

                        <th>
                            sub dapertement
                        </th>

                        <th>
                            No WM(lama)
                        </th>
                        <th>
                            Brand WM(lama)
                        </th>
                        <th>
                            stand WM(lama)
                        </th>
                        
                        
                        <th>
                            No WM(baru)
                        </th>
                        <th>
                            Brand WM(baru)
                        </th>
                        <th>
                            stand WM(baru)
                        </th>     
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actionWms as $key => $actionWm)
                        <tr data-entry-id="{{ $actionWm->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $actionWm->status ?? '' }}
                            </td>
                            <td>
                            {{ $actionWm->memo ?? '' }}
                            </td>
                            <td>
                                {{ $actionWm->category }}
                              
                                </td>
                            <td>
                                {{ $actionWm->subdapertement_id ?? '' }}
                                </td>
                                <td>
                                    {{ $actionWm->noWM1 ?? '' }}
                                    </td>
                                    <td>
                                        {{ $actionWm->brandWM1 ?? '' }}
                                        </td>
                                        <td>
                                            {{ $actionWm->standWM1 ?? '' }}
                                            </td>

                                            <td>
                                                {{ $actionWm->noWM2 ?? '' }}
                                                </td>
                                                <td>
                                                    {{ $actionWm->brandWM2 ?? '' }}
                                                    </td>
                                                    <td>
                                                        {{ $actionWm->standWM2 ?? '' }}
                                                        </td>
                            <td>

                                
                              @can('actionWmStaff_create')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.actionWmStaff.index', $actionWm->id) }}">
                                        Tambah Petugas
                                    </a>
                                @endcan

                                @can('actionWm_edit')
                                <a class="btn btn-xs btn-primary" href="{{ route('admin.actionWms.edit', $actionWm->id) }}">
                                   Edit
                                </a>
                                @endcan
                           
                                 {{--  @if ($actionWm->id != 1 && $actionWm->id != 2)

                                @can('user_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.actionWm.edit', $actionWm->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan--}}
                                {{-- @can('actionWm_delete')
                                    <form action="{{ route('admin.actionWms.destroy', $actionWm->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan  --}}
                                                                    
                                {{-- @endif --}}
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
//
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('user_delete')
  dtButtons.push(deleteButton)
@endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
})

</script>
@endsection
@endsection