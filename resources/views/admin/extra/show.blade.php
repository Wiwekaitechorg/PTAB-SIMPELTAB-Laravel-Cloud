@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.extra.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                {{-- <tr>
                    <th>
                        {{ trans('global.extra.fields.code') }}
                    </th>
                    <td>
                        {{ $extra->code }}
                    </td>
                </tr> --}}

                <tr>
                    <th>
                        {{ trans('global.extra.fields.staff_name') }}
                    </th>
                    <td>
                        {{ $extra->staff_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.extra.fields.start') }}
                    </th>
                    <td>
                        {{ $extra->start }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.extra.fields.end') }}
                    </th>
                    <td>
                        {{ $extra->end }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.extra.fields.status') }}
                    </th>
                    <td>
                        {{ $extra->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.extra.fields.category') }}
                    </th>
                    <td>
                        {{ $extra->category }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.extra.fields.description') }}
                    </th>
                    <td>
                        {{ $extra->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.extra.fields.file') }}
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