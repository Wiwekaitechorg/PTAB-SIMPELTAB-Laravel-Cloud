@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.proposalwm.title_singular') }}
    </div>

    <div class="card-body">
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.proposalwm.fields.code') }}</h5>
            <p>{{ $proposalWm->close_queue }}{{$proposalWm->code}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.proposalwm.fields.action') }}</h5>
            <p>{{$proposalWm->subdapertement_id === 10 ? "Pergantian Water Meter" : "Perbaikan Water Meter"}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.proposalwm.fields.status') }}</h5>
            <p>{{$proposalWm->status}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.proposalwm.fields.created_at') }}</h5>
            <p>{{$proposalWm->created_at}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.proposalwm.fields.updated_at') }}</h5>
            <p>{{$proposalWm->updated_at}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.proposalwm.fields.customer') }}</h5>
            <p>{{$proposalWm->namapelanggan}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">No. Telfon</h5>
            <p>{{$proposalWm->telp}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Lihat Peta</h5>
            <p><a href="https://maps.google.com/?q={{ $proposalWm->lat }},{{$proposalWm->lng}}" target="_blank"><i class="fa fa-map-marker" aria-hidden="true" style="font-size:30px;color:red;"></i> Buka Map</a></p>

        </div>

        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Water Meter Lama</h5>
            <div>No WM : {{ $proposalWm->noWM1 }}</div>
            <div>Merk WM : {{ $proposalWm->brandWM1 }}</div>
            <div>Stand WM : {{ $proposalWm->standWM1 }}</div>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Water Meter Baru</h5>
            <div>No WM : {{ $proposalWm->noWM2 }}</div>
            <div>Merk WM : {{ $proposalWm->brandWM2 }}</div>
            <div>Stand WM : {{ $proposalWm->standWM2 }}</div>
        </div>

        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.proposalwm.fields.memo') }}</h5>
            <p> 
                {{ $proposalWm->memo }}

            </p>
        </div>

        <br>
        <div style="border-bottom: 1px solid" class="mt-3" >

            <h5 style="font-weight:bold">Staff</h5>
            <p>1. KA SUBAG @if (!empty($subdapertement)) {{$subdapertement->name}} @endif</p>
            @foreach ($staffs as $index => $staff)<p>{{$index+2}}. {{$staff->staff_name}}</p>@endforeach

        </div>
        <div class="container-fluid">
            <div class="container">
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h3>Bukti Laporan</h3>
                    </div>
                </div>
                <h5 style="font-weight:bold">Foto Laporan</h5>
                @if ($proposalWm->old_image != "")
                <div class="row">
                
                        @foreach (json_decode($proposalWm->old_image) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$item"}} alt="">
                            <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                     
                    @endforeach
                
                </div>
       
                @else
                
                <div>(Foto Belum Ada)</div>
                @endif
            </div>
        </div>

        <div class="container-fluid">
            <div class="container">
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h3>Bukti Alat</h3>
                    </div>
                </div>
                <h5 style="font-weight:bold">Foto Alat</h5>
                @if ($proposalWm->new_image != "")
                <div class="row">
                 
                        @foreach (json_decode($proposalWm->new_image) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$item"}} alt="">
                            <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                       
                   
                </div>
              
                @else
                <div>(Foto Belum Ada)</div>
                @endif
            </div>
        </div>

        <div class="container-fluid">
            <div class="container">
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h3>Bukti Selesai</h3>
                    </div>
                </div>
                <h5 style="font-weight:bold">Foto Selesai</h5>
                @if ($proposalWm->image_done != "")
                <div class="row">
             
                        @foreach (json_decode($proposalWm->image_done) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$item"}} alt="">
                            <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                      
                </div>
                @else
                <p>(Foto Belum Ada)</p>
            @endif
            </div>
        </div>

        <br>
        {{-- @can('action_print_service')
        
            <a class="btn btn-lg btn-primary fa fa-print" target="_blank" href="{{ route('admin.tickets.printservice',$proposalWm->id) }}">
                {{ trans('global.action.print_service') }}
            </a>
        
        @endcan --}}
        @if($cek1 === 2)
        @can('proposalWm_spk')
        
            <a class="btn btn-lg btn-info fa fa-print " target="_blank" href="{{ route('admin.proposalwm.printspk',$proposalWm->id) }}">
                {{ trans('global.action.print_SPK') }}
            </a>
        
        @endcan

        @can('proposalWm_spk')
        @if ($proposalWm->status == "close")
            
        <a class="btn btn-lg btn-info fa fa-print " target="_blank" href="{{ route('admin.proposalwm.report',$proposalWm->proposal_wm_id) }}">
            Berita Acara
        </a>
        
        @endif
    
    @endcan
@endif
        {{-- @can('action_print_report')
        @if ($proposalWm->status == "close")
            <a class="btn btn-lg btn-success fa fa-print" target="_blank" href="{{ route('admin.tickets.printreport',$proposalWm->id) }}">
                {{ trans('global.action.print_Report') }}
            </a>
        @endif
        @endcan --}}

@endsection