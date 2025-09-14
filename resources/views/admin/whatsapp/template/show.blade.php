@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.WaTemplate.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                <tr>
                    <th>
                        {{ trans('global.WaTemplate.fields.code') }}
                    </th>
                    <td>
                        {{ $WaTemplate->code }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.WaTemplate.fields.name') }}
                    </th>
                    <td>
                        {{ $WaTemplate->name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.WaTemplate.fields.message') }}
                    </th>
                    <td>
                        {{ $WaTemplate->message }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.WaTemplate.fields.category') }}
                    </th>
                    <td>
                        {{ $WaTemplate->category }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection