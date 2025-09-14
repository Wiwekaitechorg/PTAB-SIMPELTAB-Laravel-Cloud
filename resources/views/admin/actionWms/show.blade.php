@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.categoryWA.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                <tr>
                    <th>
                        {{ trans('global.categoryWA.fields.code') }}
                    </th>
                    <td>
                        {{ $categoryWa->code }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.categoryWA.fields.name') }}
                    </th>
                    <td>
                        {{ $categoryWa->name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.categoryWA.fields.description') }}
                    </th>
                    <td>
                        {{ $categoryWa->description }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection