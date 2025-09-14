@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.dispense.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                {{-- <tr>
                    <th>
                        {{ trans('global.dispense.fields.code') }}
                    </th>
                    <td>
                        {{ $dispense->code }}
                    </td>
                </tr> --}}

                <tr>
                    <th>
                        {{ trans('global.dispense.fields.staff_name') }}
                    </th>
                    <td>
                        {{ $dispense->staff_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.dispense.fields.start') }}
                    </th>
                    <td>
                        {{ $dispense->start }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.dispense.fields.end') }}
                    </th>
                    <td>
                        {{ $dispense->end }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.dispense.fields.status') }}
                    </th>
                    <td>
                        {{ $dispense->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.dispense.fields.category') }}
                    </th>
                    <td>
                        {{ $dispense->category }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.dispense.fields.description') }}
                    </th>
                    <td>
                        {{ $dispense->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.dispense.fields.file') }}
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

                <tr>
                    <th>
                        {{-- {{ trans('global.dispense.fields.file') }} --}}
                        Bukti Dinas
                    </th>
                    <th>
                    {{-- <div class="row"> --}}
                        @foreach ($visit_images as $item)
                            {{-- @foreach (json_decode($image->image) as $item) --}}
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src="{{"https://simpletabadmin.ptab-vps-storage.com/images/Visit/$item->image"}}" alt="">
                                <p>{{ $item->type == "request_log_in" ? "Bukti Check In" : "Bukti Check Out" }}</p>
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/images/Visit/$item->image"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
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

