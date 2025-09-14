@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.lock.title') }}
    </div>

    <div class="card-body">
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.code') }}</h5>
            <p>{{$lock->code}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.lock.type') }}</h5>
            <?php 
                if($lock->type =='unplug'){
                    $lock->type ='Cabut';
                }elseif($lock->type  =='lock'){
                    $lock->type ='Segel';
                }elseif($lock->type =='lock_resist'){
                    $lock->type ='Hambatan Segel';
                }elseif($lock->type =='unplug_resist'){
                    $lock->type ='Hambatan Cabut';
                }
            ?>
            <p>{{$lock->type}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.memo') }}</h5>
            <p>{{$lock->memo}}</p>
        </div>      
        <div class="container-fluid">
            <div class="container">
            <div class="row my-3">
                <div class="col-md-12">
                </div>
            </div>
                <div class="row">
                    <div class="col-md-5">
                        <h5 style="font-weight:bold">Foto</h5>
                    </div>
                </div>
                <div class="row">
                        @if ($lock->image != null) 
                            @foreach (json_decode($lock->image) as $item)
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/pdf/$item"}} alt="">
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/pdf/$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                            @endforeach
                        @endif
                </div>
            </div>
        <div> 
    </div>
</div>

@endsection