@extends('layouts.admin')
@section('content')

<div class="card">

  

    <div class="card-header">
    
        
        {{ trans('global.show') }} Data Device
    </div>


    <div>
        
    </div>

    <div class="card-body">
        <form action="" id="filtersForm">
            <div class="col-md-12 row">
           
                <div class="col-md-6">
                    <label>Channel</label>
                    <select id="channel" name="channel" class="form-control">
                        <option value="">== Pilih Channel ==</option>
                        @foreach ($channelList as $item )
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
            </div>  
        </div>     
        <div class="col-md-12 row">
                <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                </span>   
            </div>     
        </form>
<br>
        <table class="table table-bordered table-striped">
            <tbody>

                <tr>
                    <th>
                        {{ trans('global.WaTemplate.fields.code') }}
                    </th>
                    <td>
                        {{ $deviceWa->serial }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.WaTemplate.fields.name') }}
                    </th>
                    <td>
                        {{ $deviceWa->name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Nomor.Telp
                    </th>
                    <td>
                        +{{ $deviceWa->sender }} <br>
                        <a class="btn btn-xs btn-warning" href="{{ route('admin.devicewa.create', ['token' => $deviceWa->token]) }}">ganti nomor</a>
                    </td>
                </tr>

                <tr>
                    <th>
                        Kuota
                    </th>
                    <td>
                        {{ $deviceWa->quota }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Expired Date
                    </th>
                    <td>
                        {{ $deviceWa->expired_date }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Active
                    </th>
                    <td>
                        {{ $deviceWa->active }}
                    </td>
                </tr>
                <tr>
                    <th>
                        Status
                    </th>
                    <td>
                        {{ $deviceWa->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Scan Untuk Mengaktifkan
                    </th>
                    <td>
                        <a class="btn btn-primary" href="{{ $scan }}" target="_blank">Scan Disini</a>
                    </td>
                </tr>
                {{-- <tr>
                    <th>
                        Disconect device
                    </th>
                    <td>
                        <a class="btn btn-danger" href="{{ route('admin.devicewa.disconect') }}" target="_blank">Disconect Disini</a>
                    </td>
                </tr> --}}


            </tbody>
        </table>
    </div>
</div>

@endsection