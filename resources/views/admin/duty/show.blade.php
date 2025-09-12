@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.duty.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                {{-- <tr>
                    <th>
                        {{ trans('global.duty.fields.code') }}
                    </th>
                    <td>
                        {{ $duty->code }}
                    </td>
                </tr> --}}

                <tr>
                    <th>
                        {{ trans('global.duty.fields.staff_name') }}
                    </th>
                    <td>
                        {{ $duty->staff_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.duty.fields.start') }}
                    </th>
                    <td>
                        {{ $duty->start }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.duty.fields.end') }}
                    </th>
                    <td>
                        {{ $duty->end }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.duty.fields.status') }}
                    </th>
                    <td>
                        {{ $duty->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.duty.fields.category') }}
                    </th>
                    <td>
                        {{ $duty->category }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.duty.fields.description') }}
                    </th>
                    <td>
                        {{ $duty->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Lihat Peta
                    </th>
                    <td>
                        @foreach ($visit_images as $item)
                        <p><a href="https://maps.google.com/?q={{ $item->lat }},{{$item->lng}}" target="_blank"><i class="fa fa-map-marker" aria-hidden="true" style="font-size:30px;color:red;"></i> Buka Map</a></p>
                        @endforeach
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.duty.fields.file') }}
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
                        {{-- {{ trans('global.duty.fields.file') }} --}}
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

