@extends('layouts.admin')
@section('content')
@can('action_staff_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.complain.complainOppCreate', ['operator' => $operator]) }}">
                {{ trans('global.add') }} {{ trans('global.customer.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.list') }}  {{ trans('global.customer.title_singular') }} ({{ $operator }})
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
                            {{ trans('global.segelmeter.norekening') }}
                        </th>
                        <th>
                            {{ trans('global.segelmeter.name') }}
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
                                {{$opp->operator}}
                            </td>
                            <td>
                                {{$opp->nomorrekening}}    
                            </td>
                            <td>
                                {{ $opp->pelanggan->namapelanggan ?? '' }}
                            </td>
                            <td>
                                @can('complain_delete')
                                    <form action="{{ route('admin.complain.complainOppDestroy', [$opp->operator, $opp->id]) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
            
        </script>
    @endsection 
@endsection