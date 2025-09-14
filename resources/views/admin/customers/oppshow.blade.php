@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.customer.title_singular') }}
    </div>

    <div class="card-body">
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.customer.fields.code') }}</h5>
            <p>{{$customer->nomorrekening}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.customer.fields.name') }}</h5>
            <p>{{$customer->namapelanggan}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.customer.fields.email') }}</h5>
            <p>{{$customer->_email}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.customer.fields.address') }}</h5>
            <p>{{$customer->alamat}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">NO. NIK/KTP</h5>
            <p>{{$customer->noktp}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.customer.fields.phone') }}</h5>
            <p>{{$customer->telp}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Last Update</h5>
            <p>{{$customer->last_update}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Tipe Pelanggan</h5>
            <p>{{$customer->_desctype}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Keterangan Pelanggan</h5>
            <p>{{$customer->_desc}}</p>
        </div>
        <h5 style="font-weight:bold">Foto Rumah</h5>
                <div class="row">
                    <div class="col-md-5">
                    <img  height="250px" width="350px"  src={{"https://ptab-vps-storage.com/pdam$customer->_filegambar"}} alt="">
                    <p class="my-2"><a href={{"https://ptab-vps-storage.com/pdam$customer->_filegambar"}} target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                </div>
        <h5 style="font-weight:bold">Foto WM</h5>
                <div class="row">
                    <div class="col-md-5">
                    <img  height="250px" width="350px"  src={{"https://ptab-vps-storage.com/pdam$customer->_filewm"}} alt="">
                    <p class="my-2"><a href={{"https://ptab-vps-storage.com/pdam$customer->_filewm"}} target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                </div>
        <h5 style="font-weight:bold">Foto KTP</h5>
                <div class="row">
                    <div class="col-md-5">
                    <img  height="250px" width="350px"  src={{"https://ptab-vps-storage.com/pdam$customer->_filektp"}} alt="">
                    <p class="my-2"><a href={{"https://ptab-vps-storage.com/pdam$customer->_filektp"}} target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                </div>
        <h5 style="font-weight:bold">Foto Lainnya</h5>
                <div class="row">
                    <div class="col-md-5">
                    <img  height="250px" width="350px"  src={{"https://ptab-vps-storage.com/pdam$customer->_filelain"}} alt="">
                    <p class="my-2"><a href={{"https://ptab-vps-storage.com/pdam$customer->_filelain"}} target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                </div>

        <br>

        <!-- <div style="border-bottom: 1px solid" class="mt-3 pb-3" >

        </div> -->

        <br>
        <a class="btn btn-lg btn-danger fa fa-print" target="_blank" href="#" onclick="window.print();">
                Print
            </a>
    </div>
</div>

@endsection
