@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.complain.title_singular') }}
    </div>

    <div class="card-body">
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.complain.fields.code') }}</h5>
            <p>{{$complain->code}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.complain.fields.title') }}</h5>
            <p>{{$complain->title}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.complain.fields.description') }}</h5>
            <p>{{$complain->description}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.complain.fields.status') }}</h5>
            <p>{{$complain->status}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.complain.fields.area') }}</h5>
            <p>{{$complain->area}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.complain.fields.customer') }}</h5>
            <p>{{$complain->customer ? $complain->customer->name:''}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">No. Telfon</h5>
            <p>{{$complain->customer ? $complain->customer->phone:''}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Lihat Peta</h5>
            <p><a href="https://maps.google.com/?q={{ $complain->lat }},{{$complain->lng}}" target="_blank"><i class="fa fa-map-marker" aria-hidden="true" style="font-size:30px;color:red;"></i> Buka Map</a></p>

        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.complain.fields.memo') }}</h5>
            <p> 
           {{$complain->description}}
            </p>
        </div>

        <br>
        <div style="border-bottom: 1px solid" class="mt-3" >
    </div>
        <div class="container-fluid">
            <div class="container">
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h3>Bukti Laporan</h3>
                    </div>
                </div>
                <h5 style="font-weight:bold">Foto Laporan</h5>
                <div class="row">
                    @foreach (json_decode($complain->image) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{url('/')."$item"}} alt="">
                            <p class="my-2"><a href="{{url('/')."$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                </div>
                    @if ($complain->video != null) 
                    <h5 style="font-weight:bold">{{ trans('global.complain.fields.video') }}</h5>
                        <div class="row">
                            <div class="col-md-5">
                                <video width="350px" height="250px" controls>
                                    <source src={{url('/')."$complain->video"}} type="video/mp4">
                                    
                                    {{-- <source src="mov_bbb.ogg" type="video/ogg"> --}}
                                
                                </video>
                            </div>
                        </div>
                    @endif
            </div>
        </div>
      
      
        <!-- <div style="border-bottom: 1px solid" class="mt-3 pb-3" >
           
        </div> -->       
        
    </div>
</div>

@endsection