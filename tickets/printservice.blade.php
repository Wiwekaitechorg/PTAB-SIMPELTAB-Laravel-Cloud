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
                <span class="v103_245"><!--@if ($ticket->delegated_at != null) {{$ticket->delegated_at->format('H:i:s')}} @else {{$ticket->created_at->format('H:i:s')}} @endif--></span>
                <span class="v103_246"></span>
                <span class="v103_247"></span>
                <span class="v103_248"></span>
                <span class="v103_249"></span>
                <span class="v103_250"></span>
                <span class="v103_251"></span>
                <span class="v103_252"><!--@if ($ticket->delegated_at != null) {{$ticket->delegated_at->format('H:i:s')}} @else {{$ticket->created_at->format('H:i:s')}} @endif--></span>
                <span class="v103_253">{{$ticket->spk}}</span>
                <span class="v103_254"><!--@if ($ticket->delegated_at != null) {{$ticket->delegated_at->format('d/m/Y')}} @else {{$ticket->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_255">Internal</span>
                <span class="v103_256">{{$ticket->dapertement->name}}</span>
                <span class="v103_257"><!--@if ($ticket->delegated_at != null) {{$ticket->delegated_at->format('d/m/Y')}} @else {{$ticket->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_258"><!--@if ($ticket->delegated_at != null) {{$ticket->delegated_at->format('d/m/Y')}} @else {{$ticket->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_259">{{$ticket->customer->name}}</span>
                <span class="v103_260"> @if ($ticket->address != null){{$ticket->address}}@endif  @if ($ticket->address == null){{$ticket->customer->address}}@endif</span>
                <span class="v103_261">{{$ticket->customer->code}}</span>
                <span class="v103_262">{{$ticket->customer->name}}</span>
                <span class="v103_263">{{isset($ticket->dapertementReceive->name) ? $ticket->dapertementReceive->name : $ticket->dapertement->name}}</span>
                <span class="v103_264"><!--@if ($ticket->created_at != null) {{$ticket->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_265"><!--@if ($ticket->created_at != null) {{$ticket->created_at->format('d/m/Y')}} @endif--></span>
                <span class="v103_266"><!--@if ($ticket->created_at != null) {{$ticket->created_at->format('H:i:s')}}--> @endif</span>
                <span class="v103_267"><!--@if ($ticket->created_at != null) {{$ticket->created_at->format('H:i:s')}}--> @endif</span>
                <span class="v103_268">{{$ticket->code}}</span>
                <span class="v103_269">{{$ticket->description}}</span>
                <span class="v103_270">{{$ticket->area}}</span>
                <span class="v1">{{$ticket->category->categorygroup->name}}</span>
               
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