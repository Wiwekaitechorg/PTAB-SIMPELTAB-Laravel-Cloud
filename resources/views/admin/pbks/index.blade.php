@extends('layouts.admin')
@section('content')
<!-- @can('pbks_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.pbks.create') }}">
                {{ trans('global.add') }} {{ trans('global.pbk.title_singular') }}
            </a>
        </div>
    </div>
@endcan -->
<div class="card">
    <div class="card-header">
        {{ trans('global.pbk.fields.name') }} {{ trans('global.list') }}
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
                            {{ trans('global.pbk.fields.status') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pbks as $key => $pbk)
                        <tr data-entry-id="{{ $pbk->number }}">
                            <td>

                            </td>
                            <td>
                            {{ $pbk->Name ?? '' }}
                            </td>                            
                            <td>
                            {{ $pbk->Number ?? '' }}
                            </td>
                            <td>
                            {{ $pbk->Status ?? '' }}
                            </td>
                            <td>
                                @can('pbk_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.pbks.status', $pbk->Number) }}">
                                        Update Status
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
@endsection