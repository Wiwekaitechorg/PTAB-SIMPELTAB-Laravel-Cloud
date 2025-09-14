@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.excuse.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                {{-- <tr>
                    <th>
                        {{ trans('global.excuse.fields.code') }}
                    </th>
                    <td>
                        {{ $excuse->code }}
                    </td>
                </tr> --}}

                <tr>
                    <th>
                        {{ trans('global.excuse.fields.staff_name') }}
                    </th>
                    <td>
                        {{ $excuse->staff_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.excuse.fields.start') }}
                    </th>
                    <td>
                        {{ $excuse->start }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.excuse.fields.end') }}
                    </th>
                    <td>
                        {{ $excuse->end }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.excuse.fields.status') }}
                    </th>
                    <td>
                        {{ $excuse->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.excuse.fields.category') }}
                    </th>
                    <td>
                        {{ $excuse->category }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.excuse.fields.description') }}
                    </th>
                    <td>
                        {{ $excuse->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.excuse.fields.file') }}
                    </th>
                    <th>
                    {{-- <div class="row"> --}}
                        @foreach ($file as $item)
                            {{-- @foreach (json_decode($image->image) as $item) --}}
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src="{{"https://simpletabadmin.ptab-vps-storage.com/images/RequestFile/$item->image"}}" alt="">
                                <p>{{ $item->type == "request_log_in" ? "Bukti Check In" : "Bukti Check Out" }}</p>
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/images/RequestFile/$item->image"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
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