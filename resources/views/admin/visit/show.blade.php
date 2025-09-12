







@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} Kunjungan
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
                        {{ $visit->staff->name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Status WM
                    </th>
                    <td>
                        {{ $visit->NamaStatus }}
                    </td>
                </tr>

                <tr>
                    <th>
                       Tanggal
                    </th>
                    <td>
                        {{ $visit->created_at }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Keterangan
                    </th>
                    <td>
                        {{ $visit->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                       Lokasi
                    </th>
                    <td>
                        <p><a href="https://maps.google.com/?q={{ $visit->lat }},{{$visit->lng}}" target="_blank"><i class="fa fa-map-marker" aria-hidden="true" style="font-size:30px;color:red;"></i> Buka Map</a></p>
        
                    </td>
                </tr>

                {{-- <div style="border-bottom: 1px solid" class="mt-3" > --}}
                    {{-- <h5 style="font-weight:bold">Lihat Peta</h5> --}}
                   
                {{-- </div> --}}

                <tr>
                    <th>
                        {{-- {{ trans('global.duty.fields.file') }} --}}
                        Bukti Kunjungan
                    </th>
                    <th>
                    {{-- <div class="row"> --}}
                        @foreach ($visit->visitImages as $item)
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