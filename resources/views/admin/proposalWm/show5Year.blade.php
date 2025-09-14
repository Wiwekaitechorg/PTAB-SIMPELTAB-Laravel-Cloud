@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.proposalwm.title_singular') }}
    </div>

    <div class="card-body">
        <h1>{{ $customer->namapelanggan }}-{{ $customer->nomorrekening }}</h1>
        <table class="table table-bordered table-striped table-borderless">
            <tbody>
                @foreach ($proposalWm as $data )
                
                <tr>
                  
                    <th>
                       <h3>{{ $data->created_at }}</h3>
                       {{-- <h3>{{ $data->id }}</h3> --}}
                    </th>
                    <td>
                        
                    </td>
                </tr>


                <tr>
                  
                    <th style = "font-weight: normal;">
                        <h5 style="font-weight:bold">Water Meter Lama</h5>
                        <div>No WM : {{ $data->noWM1 }}</div>
                        <div>Merk WM : {{ $data->brandWM1 }}</div>
                        <div>Stand WM : {{ $data->standWM1 }}</div>
                        <div>
                            @if ($data->old_image != "")
                            @foreach (json_decode($data->old_image) as $item)
                            <div>
                                <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$item"}} alt="">
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                        </div>
                        @endforeach
                        @endif
                     
                    </th>
                    <td>
                        <div>
                            <h5 style="font-weight:bold">Water Meter Baru</h5>
                            <div>No WM : {{ $data->noWM2 }}</div>
                            <div>Merk WM : {{ $data->brandWM2 }}</div>
                            <div>Stand WM : {{ $data->standWM2 }}</div>
                            @if ($data->old_image != "")
                            @foreach (json_decode($data->new_image) as $item)
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src={{"https://simpletabadmin.ptab-vps-storage.com/$item"}} alt="">
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/$item"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                            @endforeach
                            @endif
                           
                       
                    </div>
                      
                    </td>
                </tr>

                @endforeach
            </tbody>
        </table>


  
    
      
      
    </div>
</div>
    
    

@endsection