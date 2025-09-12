@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.location.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                {{-- <tr>
                    <th>
                        {{ trans('global.location.fields.code') }}
                    </th>
                    <td>
                        {{ $location->code }}
                    </td>
                </tr> --}}

                <tr>
                    <th>
                        {{ trans('global.location.fields.staff_name') }}
                    </th>
                    <td>
                        {{ $location->staff_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.location.fields.start') }}
                    </th>
                    <td>
                        {{ $location->start }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.location.fields.end') }}
                    </th>
                    <td>
                        {{ $location->end }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.location.fields.status') }}
                    </th>
                    <td>
                        {{ $location->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.location.fields.category') }}
                    </th>
                    <td>
                        {{ $location->category }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.location.fields.description') }}
                    </th>
                    <td>
                        {{ $location->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.location.fields.file') }}
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