@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.absence.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                <tr>
                    <th>
                        {{ trans('global.absence.fields.user') }}
                    </th>
                    <td>
                        {{ $absence->user }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.absence.fields.register') }}
                    </th>
                    <td>
                        {{ $absence->register }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.absence.fields.day') }}
                    </th>
                    <td>
                        {{ $absence->day }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.absence.fields.absence_category') }}
                    </th>
                    <td>
                        {{ $absence->absence_category }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.absence.fields.late') }}
                    </th>
                    <td>
                        {{ $absence->late }}
                    </td>
                </tr>
                <tr>
                    <th>
                        Lihat Peta
                    </th>
                    <td>
                        <p><a href="https://maps.google.com/?q={{ $absence->lat }},{{$absence->lng}}" target="_blank"><i class="fa fa-map-marker" aria-hidden="true" style="font-size:30px;color:red;"></i> Buka Map</a></p>
    
                    </td>
                </tr>
              
                <tr>
                    <th>
                        {{ trans('global.absence.fields.image') }}
                    </th>
                    <td>
                        <img src="{{ asset('') }}/{{ $absence->image }}" width="200"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.absence.fields.user_image') }}
                    </th>
                    <td>
                        <img src="{{ asset('') }}/{{ $absence->user_image }}" width="200"/>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection