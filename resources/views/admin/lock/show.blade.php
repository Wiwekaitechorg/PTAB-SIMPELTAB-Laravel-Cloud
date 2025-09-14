@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.lock.title') }}
    </div>

    <div class="card-body">
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.lock.code') }}</h5>
            <p>{{$customer->code}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.lock.fields.type') }}</h5>
            <p>
                @if ($customer->type=="notice")
                Penyampaian Surat
            @elseif ($customer->type=="lock")
            Penyegelan
            @elseif ($customer->type=="notice2")
            Kunjungan
            @else
            Cabutan
        @endif
        
        </p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.lock.fields.noSbg') }}</h5>
            <p>{{$customer->customer->nomorrekening}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.lock.fields.customer') }}</h5>
            <p>{{$customer->customer->namapelanggan}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Alamat</h5>
            <p>{{$customer->customer->alamat}}</p>
        </div>
        {{-- <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Code Staff</h5>
            <p>{{$customer->staff_code}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.lock.fields.staff_name') }}</h5>
            <p>{{$customer->staff_name}}</p>
        </div> --}}
        {{-- <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Lihat Peta</h5>
            <p><a href="https://maps.google.com/?q={{ $customer->lat }},{{$customer->lng}}" target="_blank"><i class="fa fa-map-marker" aria-hidden="true" style="font-size:30px;color:red;"></i> Buka Map</a></p>

        </div> --}}
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.lock.fields.memo') }}</h5>
            <p> 
                {{$customer->memo}}
            {{-- @if($customer->action != null) 
                @foreach ($customer->action as $customeraction)
                    @if($customeraction->memo != null) 
                        {{$customeraction->memo}}
                        <p></p>
                    @endif
                @endforeach
            @endif --}}
            </p>
        </div>

        <div class="container-fluid">
            <div class="container">
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h3>Bukti Tindakan</h3>
                    </div>
                </div>
                <h5 style="font-weight:bold">Foto Laporan</h5>
                <div class="row">
                @if ($customer->image != null)        
                @foreach (json_decode($customer->image) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{url('/')."$item"}} alt="">
                            <p class="my-2"><a href="{{url('/')."$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                        @endif
                </div>
                <h5 style="font-weight:bold">Foto Alat</h5>
                <div class="row">
                @if ($customer->image_tools != null)        
                @foreach (json_decode($customer->image_tools) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{url('/')."$item"}} alt="">
                            <p class="my-2"><a href="{{url('/')."$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                        @endif
                </div>
                <h5 style="font-weight:bold">Foto Pra Pengerjaan</h5>
                <div class="row">
                @if ($customer->image_prework != null)        
                @foreach (json_decode($customer->image_prework) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{url('/')."$item"}} alt="">
                            <p class="my-2"><a href="{{url('/')."$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                        @endif
                </div>
                <h5 style="font-weight:bold">Foto Pengerjaan</h5>
                <div class="row">
                @if ($customer->image_work != null)        
                @foreach (json_decode($customer->image_work) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{url('/')."$item"}} alt="">
                            <p class="my-2"><a href="{{url('/')."$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                        @endif
                </div>
                <h5 style="font-weight:bold">Foto Selesai</h5>
                <div class="row">
                @if ($customer->image_done != null)        
                @foreach (json_decode($customer->image_done) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{url('/')."$item"}} alt="">
                            <p class="my-2"><a href="{{url('/')."$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                        @endif
                </div>
            </div>
        </div>
        {{-- @if ($customer->status != 'pending') 
        <div class="container-fluid">
            <div class="container">
            <div class="row my-3">
                <div class="col-md-12">
                    <h3 >Bukti Tindakan</h3>
                </div>
            </div>
                <div class="row">
                    <div class="col-md-5">
                        <h5 style="font-weight:bold">Foto Sebelum Pengerjaan</h5>
                    </div>
                    <div class="col-md-5">
                        <h5 style="font-weight:bold">Foto Alat Pengerjaan</h5>
                    </div>
                </div>
                <div class="row">
                    @foreach ($customer->action as $acti)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{url('/')."" . $acti->image_prework}} alt="">
                            <p class="my-2"><a href="{{url('/').'' . $acti->image_prework}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                    @endforeach
                    @foreach ($customer->action as $acti)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{url('/')."" . $acti->image_tools}} alt="">
                            <p class="my-2"><a href="{{url('/').'' . $acti->image_tools}}"  target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                    @endforeach
                </div>

                <h5 style="font-weight:bold">Foto Pengerjaan</h5>
                <div class="row">
                    @foreach ($customer->action as $acti)
                        @if ($acti->image != null) 
                            @foreach (json_decode($acti->image) as $itemimage)
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src={{url('/')."$itemimage"}} alt="">
                                <p class="my-2"><a href="{{url('/')."$itemimage"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>

                <h5 style="font-weight:bold">Foto Selesai</h5>
                <div class="row">
                    @foreach ($customer->action as $acti)
                        @if ($acti->image_done != null) 
                            @foreach (json_decode($acti->image_done) as $itemdone)
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src={{url('/')."$itemdone"}} alt="">
                                <p class="my-2"><a href="{{url('/')."$itemdone"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>
        <div> 
        @endif --}}
      
      
        <!-- <div style="border-bottom: 1px solid" class="mt-3 pb-3" >
           
        </div> -->
       
        <br>
        {{-- @can('action_print_service')
        
            <a class="btn btn-lg btn-primary fa fa-print" target="_blank" href="{{ route('admin.locks.printservice',$customer->id) }}">
                {{ trans('global.action.print_service') }}
            </a>
        
        @endcan
        @can('action_print_spk')
        
            <a class="btn btn-lg btn-info fa fa-print " target="_blank" href="{{ route('admin.locks.printspk',$customer->id) }}">
                {{ trans('global.action.print_SPK') }}
            </a>
        
        @endcan
        @can('action_print_report')
        @if ($customer->status == "close")
            <a class="btn btn-lg btn-success fa fa-print" target="_blank" href="{{ route('admin.locks.printreport',$customer->id) }}">
                {{ trans('global.action.print_Report') }}
            </a>
        @endif
        @endcan --}}
    </div>
</div>

@endsection