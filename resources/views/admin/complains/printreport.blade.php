<!DOCTYPE html>
<html lang="en">
<head>
<link href="{{ asset('css/printreport1.css') }}" rel="stylesheet" />
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body onload="onload()" >
<div style="width: 1123px; height: 805px;">
    <div class="A1">
        <div class="v103_240">
            <div class="A1Text">
                <span class="v103_243"> {{ date('d/m/Y', strtotime($complain->action[0]->end)) }} </span>
                {{-- <span class="v103_244">@foreach ($complain->action as $index => $complainaction) <textarea name="" id="" cols="30" rows="10">{{$index+1}}.{{$complainaction->memo}}</textarea> <br>@endforeach</span> --}}
            <span class="v103_244">@foreach ($complain->action as $index => $complainaction) <pre> {{$complainaction->memo}} </pre><br>@endforeach</span>
              
               <span class="v103_245"> @if ($complain->delegated_at != null) {{ date('H:i:s', strtotime($complain->delegated_at)) }} @else {{ date('H:i:s', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_246"> {{ date('H:i:s', strtotime($complain->action[0]->end)) }}  </span>
                <span class="v103_247"></span>
                <span class="v103_248"></span>
                <?php 

$awal  = strtotime($complain->action[0]->start);
$akhir = strtotime($complain->action[0]->end);
$diff  = $akhir - $awal;
$jam = floor($diff  / (60 * 60));
$menit = $diff - ( $jam * (60 * 60) );
$detik = $diff % 60;

//                 $mulai  = date_create();
//                 $selesai = $complain->action[0]->start != null ? date_create() : date('Y-m-d H:i:s');
//                 $hasil  = date_diff( $mulai, $selesai );
                $hasil = sprintf("%02d",$jam).':'.sprintf("%02d", floor( $menit / 60 )).':'.sprintf("%02d",$detik);
            ?>
                <span class="v103_249">{{$hasil}}</span>
                <span class="v103_250"></span>
                <span class="v103_251"></span>
                <span class="v103_252">  {{-- @if ($complain->delegated_at != null){{$complain->delegated_at->format('H:i:s')}}@endif --}}  </span>
                <span class="v103_253">{{$complain->spk}}</span>
                <span class="v103_254">@if ($complain->delegated_at != null) {{ date('d/m/y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif</span>
                <span class="v103_255">{{ $complain->action[0]->todo }}</span>
                <span class="v103_256">{{$complain->dapertement->name}}</span>
                <span class="v103_257">@if ($complain->delegated_at != null) {{ date('d/m/Y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_258"> @if ($complain->delegated_at != null) {{ date('d/m/y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_259">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_260"> @if ($complain->address != null){{$complain->address}}@endif  @if ($complain->address == null){{$complain->customer->address}}@endif</span>
                <span class="v103_261">{{$complain->customer ? $complain->customer->code:''}}</span>
                <span class="v103_262">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_263">{{isset($complain->dapertementReceive->name) ? $complain->dapertementReceive->name : $complain->dapertement->name}}</span>
                <span class="v103_264">@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif</span>
                <span class="v103_265">@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif</span>
                <span class="v103_266">@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}} @endif</span>
                <span class="v103_267">@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}} @endif</span>
                <span class="v103_268">{{$complain->code}}</span>
                <span class="v103_269">{{$complain->description}}</span>
                <span class="v103_270">{{$complain->area}}</span>
                <span class="v1">{{$complain->category->categorygroup->name}}</span>
            </div>
        </div>
    </div>
</div>
<div style="width: 1123px; height: 811px;">
    <div class="A2">
        <div class="v103_240">
            <div class="A2Text">
                 <span class="v103_243"> {{ date('d/m/Y', strtotime($complain->action[0]->end)) }} </span>
                <span class="v103_244">@foreach ($complain->action as $index => $complainaction)<pre>{{$index+1}}.{{$complainaction->memo}}</pre><br>@endforeach</span>
                <span class="v103_245"> @if ($complain->delegated_at != null) {{ date('H:i:s', strtotime($complain->delegated_at)) }} @else {{ date('H:i:s', strtotime($complain->action[0]->start)) }} @endif </span>
                 <span class="v103_246"> {{ date('H:i:s', strtotime($complain->action[0]->end)) }}  </span>
                <span class="v103_247"></span>
                <span class="v103_248"></span>
                <span class="v103_249">{{$hasil}}</span>
                <span class="v103_250"></span>
                <span class="v103_251"></span>
              <span class="v103_252">  {{-- @if ($complain->delegated_at != null){{$complain->delegated_at->format('H:i:s')}}@endif --}}  </span>
                <span class="v103_253">{{$complain->spk}}</span>
                <span class="v103_254">@if ($complain->delegated_at != null) {{ date('d/m/y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif</span>
                <span class="v103_255">{{ $complain->action[0]->todo }}</span>
                <span class="v103_256">{{$complain->dapertement->name}}</span>
               <span class="v103_257">@if ($complain->delegated_at != null) {{ date('d/m/Y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_258"> @if ($complain->delegated_at != null) {{ date('d/m/y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_259">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_260"> @if ($complain->address != null){{$complain->address}}@endif  @if ($complain->address == null){{$complain->customer->address}}@endif</span>
                <span class="v103_261">{{$complain->customer ? $complain->customer->code:''}}</span>
                <span class="v103_262">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_263">{{isset($complain->dapertementReceive->name) ? $complain->dapertementReceive->name : $complain->dapertement->name}}</span>
                <span class="v103_264">@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif</span>
                <span class="v103_265">@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif</span>
                <span class="v103_266">@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}} @endif</span>
                <span class="v103_267">@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}} @endif</span>
                <span class="v103_268">{{$complain->code}}</span>
                <span class="v103_269">{{$complain->description}}</span>
                <span class="v103_270">{{$complain->area}}</span>
                <span class="v1">{{$complain->category->categorygroup->name}}</span>
            </div>
        </div>
    </div>
</div>
<div style="width: 1123px; height: 812px;">
    <div class="A3">
        <div class="v103_240">
            <div class="A3Text">
                 <span class="v103_243"> {{ date('d/m/Y', strtotime($complain->action[0]->end)) }} </span>
                <span class="v103_244">@foreach ($complain->action as $index => $complainaction)<pre>{{$index+1}}.{{$complainaction->memo}}</pre><br>@endforeach</span>
               <span class="v103_245">@if ($complain->delegated_at != null) {{ date('H:i:s', strtotime($complain->delegated_at)) }} @else {{ date('H:i:s', strtotime($complain->action[0]->start)) }} @endif </span>
                 <span class="v103_246"> {{ date('H:i:s', strtotime($complain->action[0]->end)) }}  </span>
                <span class="v103_247"></span>
                <span class="v103_248"></span>
                <span class="v103_249">{{$hasil}}</span>
                <span class="v103_250"></span>
                <span class="v103_251"></span>
              <span class="v103_252">  {{-- @if ($complain->delegated_at != null){{$complain->delegated_at->format('H:i:s')}}@endif --}}  </span>
                <span class="v103_253">{{$complain->spk}}</span>
                <span class="v103_254">@if ($complain->delegated_at != null) {{ date('d/m/y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif</span>
                <span class="v103_255">{{ $complain->action[0]->todo }}</span>
                <span class="v103_256">{{$complain->dapertement->name}}</span>
               <span class="v103_257"> @if ($complain->delegated_at != null) {{ date('d/m/Y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_258"> @if ($complain->delegated_at != null) {{ date('d/m/y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_259">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_260"> @if ($complain->address != null){{$complain->address}}@endif  @if ($complain->address == null){{$complain->customer->address}}@endif</span>
                <span class="v103_261">{{$complain->customer ? $complain->customer->code:''}}</span>
                <span class="v103_262">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_263">{{isset($complain->dapertementReceive->name) ? $complain->dapertementReceive->name : $complain->dapertement->name}}</span>
                <span class="v103_264">@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif</span>
                <span class="v103_265">@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif</span>
                <span class="v103_266">@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}} @endif</span>
                <span class="v103_267">@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}} @endif</span>
                <span class="v103_268">{{$complain->code}}</span>
                <span class="v103_269">{{$complain->description}}</span>
                <span class="v103_270">{{$complain->area}}</span>
                <span class="v1">{{$complain->category->categorygroup->name}}</span>
            </div>
        </div>
    </div>
</div>
<div style="width: 1123px; height: 805px;">
    <div class="A4">
        <div class="v103_240">
            <div class="A4Text">
                 <span class="v103_243"> {{ date('d/m/Y', strtotime($complain->action[0]->end)) }} </span>
                <span class="v103_244">@foreach ($complain->action as $index => $complainaction)<pre>{{$index+1}}.{{$complainaction->memo}}</pre><br>@endforeach</span>
               <span class="v103_245">@if ($complain->delegated_at != null) {{ date('H:i:s', strtotime($complain->delegated_at)) }} @else {{ date('H:i:s', strtotime($complain->action[0]->start)) }} @endif  </span>
                 <span class="v103_246"> {{ date('H:i:s', strtotime($complain->action[0]->end)) }}  </span>
                <span class="v103_247"></span>
                <span class="v103_248"></span>
                <span class="v103_249">{{$hasil}}</span>
                <span class="v103_250"></span>
                <span class="v103_251"></span>
              <span class="v103_252">  {{-- @if ($complain->delegated_at != null){{$complain->delegated_at->format('H:i:s')}}@endif --}}  </span>
                <span class="v103_253">{{$complain->spk}}</span>
                <span class="v103_254">@if ($complain->delegated_at != null) {{ date('d/m/y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif</span>
                <span class="v103_255">{{ $complain->action[0]->todo }}</span>
                <span class="v103_256">{{$complain->dapertement->name}}</span>
               <span class="v103_257">@if ($complain->delegated_at != null) {{ date('d/m/Y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_258"> @if ($complain->delegated_at != null) {{ date('d/m/y', strtotime($complain->delegated_at)) }} @else {{ date('d/m/Y', strtotime($complain->action[0]->start)) }} @endif </span>
                <span class="v103_259">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_260"> @if ($complain->address != null){{$complain->address}}@endif  @if ($complain->address == null){{$complain->customer->address}}@endif</span>
                <span class="v103_261">{{$complain->customer ? $complain->customer->code:''}}</span>
                <span class="v103_262">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_263">{{isset($complain->dapertementReceive->name) ? $complain->dapertementReceive->name : $complain->dapertement->name}}</span>
                <span class="v103_264">@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif</span>
                <span class="v103_265">@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif</span>
                <span class="v103_266">@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}} @endif</span>
                <span class="v103_267">@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}} @endif</span>
                <span class="v103_268">{{$complain->code}}</span>
                <span class="v103_269">{{$complain->description}}</span>
                <span class="v103_270">{{$complain->area}}</span>
                <span class="v1">{{$complain->category->categorygroup->name}}</span>
            </div>
        </div>
    </div>
</div>
<script>
    onload = function (){
        window.print();
    }
</script>
</body>
</html>