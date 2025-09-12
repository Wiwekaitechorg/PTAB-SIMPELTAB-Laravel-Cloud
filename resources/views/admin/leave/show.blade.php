@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.leave.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                {{-- <tr>
                    <th>
                        {{ trans('global.leave.fields.code') }}
                    </th>
                    <td>
                        {{ $leave->code }}
                    </td>
                </tr> --}}

                <tr>
                    <th>
                        {{ trans('global.leave.fields.staff_name') }}
                    </th>
                    <td>
                        {{ $leave->staff_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.leave.fields.start') }}
                    </th>
                    <td>
                        {{ $leave->start }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.leave.fields.end') }}
                    </th>
                    <td>
                        {{ $leave->end }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.leave.fields.status') }}
                    </th>
                    <td>
                        {{ $leave->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.leave.fields.category') }}
                    </th>
                    <td>
                        {{ $leave->category }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.leave.fields.description') }}
                    </th>
                    <td>
                        {{ $leave->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.leave.fields.file') }}
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