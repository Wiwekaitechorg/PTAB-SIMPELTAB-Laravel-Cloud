@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.work_type.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                <tr>
                    <th>
                        {{ trans('global.work_type.fields.code') }}
                    </th>
                    <td>
                        {{ $work_type->code }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.work_type.fields.title') }}
                    </th>
                    <td>
                        {{ $work_type->title }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.work_type.fields.type') }}
                    </th>
                    <td>
                        {{ $work_type->type }}
                    </td>
                </tr>

              
            </tbody>
        </table>
    </div>
</div>

@endsection