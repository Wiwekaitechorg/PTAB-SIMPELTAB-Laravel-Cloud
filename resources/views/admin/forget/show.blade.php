@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.geolocation_off.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                {{-- <tr>
                    <th>
                        {{ trans('global.geolocation_off.fields.code') }}
                    </th>
                    <td>
                        {{ $geolocation_off->code }}
                    </td>
                </tr> --}}

                <tr>
                    <th>
                        {{ trans('global.geolocation_off.fields.staff_name') }}
                    </th>
                    <td>
                        {{ $geolocation_off->staff_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.geolocation_off.fields.start') }}
                    </th>
                    <td>
                        {{ $geolocation_off->start }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.geolocation_off.fields.end') }}
                    </th>
                    <td>
                        {{ $geolocation_off->end }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.geolocation_off.fields.status') }}
                    </th>
                    <td>
                        {{ $geolocation_off->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.geolocation_off.fields.category') }}
                    </th>
                    <td>
                        {{ $geolocation_off->category }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.geolocation_off.fields.description') }}
                    </th>
                    <td>
                        {{ $geolocation_off->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.geolocation_off.fields.file') }}
                    </th>
                    <th>
                    {{-- <div class="row"> --}}
                        @foreach ($file as $item)
                            {{-- @foreach (json_decode($image->image) as $item) --}}
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src="{{"https://simpletabadmin.ptab-vps-storage.com/$item->image"}}" alt="">
                                <p>{{ $item->type == "request_log_in" ? "Bukti Check In" : "Bukti Check Out" }}</p>
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$item->image"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                            {{-- @endforeach --}}
                        @endforeach
                    {{-- </div> --}}
                </th>
                </tr>

            </tbody>
        </table>
    </div>
</div>

@endsection