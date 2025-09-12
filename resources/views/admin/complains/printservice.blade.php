<!DOCTYPE html>
<html lang="en">
<head>
    <link href="{{asset('css/printservice.css')}}" rel="stylesheet" />
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body onload="onload()" >
<div style="width: 1123px; height:794px;">
    <div class="A1">
        <div class="v103_240">
            <div class="A1Text">
               <span class="v103_243"></span>
                <span class=v103_244>#Tindakan</span>
                <span class="v103_245"><!--@if ($complain->delegated_at != null) {{$complain->delegated_at->format('H:i:s')}} @else {{$complain->created_at->format('H:i:s')}} @endif--></span>
                <span class="v103_246"></span>
                <span class="v103_247"></span>
                <span class="v103_248"></span>
                <span class="v103_249"></span>
                <span class="v103_250"></span>
                <span class="v103_251"></span>
                <span class="v103_252"><!--@if ($complain->delegated_at != null) {{$complain->delegated_at->format('H:i:s')}} @else {{$complain->created_at->format('H:i:s')}} @endif--></span>
                <span class="v103_253">{{$complain->spk}}</span>
                <span class="v103_254"><!--@if ($complain->delegated_at != null) {{$complain->delegated_at->format('d/m/Y')}} @else {{$complain->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_255">Internal</span>
                <span class="v103_256">{{$complain->dapertement->name}}</span>
                <span class="v103_257"><!--@if ($complain->delegated_at != null) {{$complain->delegated_at->format('d/m/Y')}} @else {{$complain->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_258"><!--@if ($complain->delegated_at != null) {{$complain->delegated_at->format('d/m/Y')}} @else {{$complain->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_259">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_260"> @if ($complain->address != null){{$complain->address}}@endif  @if ($complain->address == null){{$complain->customers ? $complain->customer->address:''}}@endif</span>
                <span class="v103_261">{{$complain->customer ? $complain->customer->code:''}}</span>
                <span class="v103_262">{{$complain->customer ? $complain->customer->name:''}}</span>
                <span class="v103_263">{{isset($complain->dapertementReceive->name) ? $complain->dapertementReceive->name : $complain->dapertement->name}}</span>
                <span class="v103_264"><!--@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_265"><!--@if ($complain->created_at != null) {{$complain->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_266"><!--@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}}--> @endif</span>
                <span class="v103_267"><!--@if ($complain->created_at != null) {{$complain->created_at->format('H:i:s')}}--> @endif</span>
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