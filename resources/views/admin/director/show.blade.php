@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.workUnit.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                <tr>
                    <th>
                        {{ trans('global.workUnit.fields.code') }}
                    </th>
                    <td>
                        {{ $work_unit->code }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workUnit.fields.name') }}
                    </th>
                    <td>
                        {{ $work_unit->name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workUnit.fields.serial_number') }}
                    </th>
                    <td>
                        {{ $work_unit->serial_number }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection