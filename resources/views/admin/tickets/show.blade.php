@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.ticket.title_singular') }}
    </div>

    <div class="card-body">
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.code') }}</h5>
            <p>{{$ticket->code}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.title') }}</h5>
            <p>{{$ticket->title}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.description') }}</h5>
            <p>{{$ticket->description}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.status') }}</h5>
            <p>{{$ticket->status}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.category') }}</h5>
            <p>{{$ticket->category->name}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.customer') }}</h5>
            <p>{{$ticket->customer ? $ticket->customer->name:''}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">No. Telfon</h5>
            <p>{{$ticket->customer ? $ticket->customer->phone:''}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">Lihat Peta</h5>
            <p><a href="https://maps.google.com/?q={{ $ticket->lat }},{{$ticket->lng}}" target="_blank"><i class="fa fa-map-marker" aria-hidden="true" style="font-size:30px;color:red;"></i> Buka Map</a></p>

        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.memo') }}</h5>
            <p> 
            @if($ticket->action != null) 
                @foreach ($ticket->action as $ticketaction)
                    @if($ticketaction->memo != null) 
                    <pre>{{$ticketaction->memo}}</pre>
                        
                        <p></p>
                    @endif
                @endforeach
            @endif
            </p>
        </div>

        <br>
        <div style="border-bottom: 1px solid" class="mt-3" >
   
            <h5 style="font-weight:bold">Staff</h5>
        <p>1. KA SUBAG @if (!empty($subdapertement)) {{$subdapertement->name}} @endif</p>
        @foreach ($staffs as $index => $staff)<p>{{$index+2}}. {{$staff->name}}</p>@endforeach
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
                    @foreach ($ticket->ticket_image as $image)
                        @foreach (json_decode($image->image) as $item)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$item"}} alt="">
                            <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                        @endforeach
                    @endforeach
                </div>
                    @if ($ticket->video != null) 
                    <h5 style="font-weight:bold">{{ trans('global.ticket.fields.video') }}</h5>
                        <div class="row">
                            <div class="col-md-5">
                                <video width="350px" height="250px" controls>
                                    <source src={{"https://simpletabadmin.ptab-vps-storage.com/$ticket->video"}} type="video/mp4">
                                    
                                    {{-- <source src="mov_bbb.ogg" type="video/ogg"> --}}
                                
                                </video>
                            </div>
                        </div>
                    @endif
            </div>
        </div>
        @if ($ticket->status != 'pending') 
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
                    @foreach ($ticket->action as $acti)
                        <div class="col-md-5">
                            <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/" . $acti->image_prework}} alt="">
                            <p class="my-2"><a href="{{'https://simpletabadmin.ptab-vps-storage.com/' . $acti->image_prework}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                        </div>
                    @endforeach
                    @foreach ($ticket->action as $acti)
                    @if ($acti->image_tools != null) 
                    @if (json_decode($acti->image_tools))
                    @foreach (json_decode($acti->image_tools) as $itemdone)
                    <div class="col-md-5">
                        <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$itemdone"}} alt="">
                        <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$itemdone"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                    </div>
                    @endforeach


                    @else
                    <div class="col-md-5">
                        <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/" . $acti->image_tools}} alt="">
                        <p class="my-2"><a href="{{'https://simpletabadmin.ptab-vps-storage.com/' . $acti->image_tools}}"  target="_blank" class="btn btn-primary">Tampilkan</a></p>
                    </div>
                    @endif
                        
                    @endif
                @endforeach
                </div>

                <h5 style="font-weight:bold">Foto Pengerjaan</h5>
                <div class="row">
                    @foreach ($ticket->action as $acti)
                        @if ($acti->image != null) 
                            @foreach (json_decode($acti->image) as $itemimage)
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$itemimage"}} alt="">
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$itemimage"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>

                <h5 style="font-weight:bold">Foto Selesai</h5>
                <div class="row">
                    @foreach ($ticket->action as $acti)
                        @if ($acti->image_done != null) 
                            @foreach (json_decode($acti->image_done) as $itemdone)
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$itemdone"}} alt="">
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$itemdone"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>
        <div> 
        @endif
      
      
        <!-- <div style="border-bottom: 1px solid" class="mt-3 pb-3" >
           
        </div> -->
       
        <br>
        @can('action_print_service')
        @if($ticket->print_status == "1")
            <a class="btn btn-lg btn-danger fa fa-print" target="_blank" href="{{ route('admin.tickets.printservice',$ticket->id) }}">
                {{ trans('global.action.print_service') }}
            </a>
        
        @else
        <a class="btn btn-lg btn-primary fa fa-print" target="_blank" href="{{ route('admin.tickets.printservice',$ticket->id) }}">
            {{ trans('global.action.print_service') }}
        </a>
        @endif
          
        
        @endcan
        @can('action_print_spk')
        
        @if($ticket->print_spk_status == "1")
        <a class="btn btn-lg btn-danger fa fa-print " target="_blank" href="{{ route('admin.tickets.printspk',$ticket->id) }}">
            {{ trans('global.action.print_SPK') }}
        </a>
    
    @else
    <a class="btn btn-lg btn-info fa fa-print " target="_blank" href="{{ route('admin.tickets.printspk',$ticket->id) }}">
        {{ trans('global.action.print_SPK') }}
    </a>
    @endif
      
    
    @endcan
          
        
        @can('action_print_report')
        @if ($ticket->status == "close")
        @if($ticket->print_report_status == "1")
        <a class="btn btn-lg btn-danger fa fa-print" target="_blank" href="{{ route('admin.tickets.printreport',$ticket->id) }}">
            {{ trans('global.action.print_Report') }}
        </a>
    
    @else
    <a class="btn btn-lg btn-success fa fa-print" target="_blank" href="{{ route('admin.tickets.printreport',$ticket->id) }}">
        {{ trans('global.action.print_Report') }}
    </a>
    @endif
           
        @endif
        @endcan
    </div>
</div>

@endsection