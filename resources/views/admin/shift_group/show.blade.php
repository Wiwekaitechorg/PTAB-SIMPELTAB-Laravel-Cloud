@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.shift_group.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                <tr>
                    <th>
                        {{ trans('global.shift_group.fields.code') }}
                    </th>
                    <td>
                        {{ $shift_group->code }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.shift_group.fields.title') }}
                    </th>
                    <td>
                        {{ $shift_group->title }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.shift_group.fields.type') }}
                    </th>
                    <td>
                        {{ $shift_group->type }}
                    </td>
                </tr>

              
            </tbody>
        </table>
    </div>
</div>

@endsection