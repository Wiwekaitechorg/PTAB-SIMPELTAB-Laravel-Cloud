@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.job.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                <tr>
                    <th>
                        {{ trans('global.job.fields.code') }}
                    </th>
                    <td>
                        {{ $job->code }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.job.fields.name') }}
                    </th>
                    <td>
                        {{ $job->name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.job.fields.dapertement_name') }}
                    </th>
                    <td>
                        {{ $job->dapertement_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.job.fields.subdapertement_name') }}
                    </th>
                    <td>
                        {{ $job->subdapertement_name }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

@endsection