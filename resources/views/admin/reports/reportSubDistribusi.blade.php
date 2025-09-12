<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SIMPELTAB</title>
    <link href="{{ asset('css/printsubdistribusi.css') }}" rel="stylesheet" />
    <style type="text/css">
    .baris1 {
        display: flex;
        margin-top: 20px;
    }
    .kiri {
        margin-left: 30px;
        width: 16%;
        /* background-color: rgb(5, 0, 69); */
        align-items: left;
        /* text-align: center; */
    }
    .kanan {
        margin-right: 30px;
        width: 16%;
        /* background-color: rgb(69, 18, 0); */
        margin-left: auto;
        /* text-align: center; */
    }
    .tengah {
        width: 16%;
        /* background-color: rgb(69, 18, 0); */
        margin: auto;
        /* text-align: center; */
    }
    PRE {
        white-space: normal;
    }

</style>


    <style type="text/css" media="print">
    .baris1 {
        margin-top: 20px;
        display: flex;
    }
    .kiri {
        margin-top: 25px;
        width: 35%;
        /* background-color: rgb(5, 0, 69); */
        align-items: left;
        /* text-align: center; */
   
    }
    .kanan {
        width: 35%;
        /* background-color: rgb(69, 18, 0); */
        margin-left: auto;
        /* text-align: center; */
    }
    .tengah {
        margin-top: 20px;
        width: 30%;
        /* background-color: rgb(69, 18, 0); */
        margin: auto;
        /* text-align: center; */
    }
    PRE {
        white-space: normal;
    }
    @media print {
    @page {
        /* margin-top: 0; */
        margin-bottom: 0;
    }
    body {
        /* padding-top: 72px; */
        padding-bottom: 72px ;
    }
}
        </style>
</head>
<body class="A4" onload="onload()">
    <section class="sheet padding-10mm">
        <h3>PERUSAHAAN UMUM DAERAH AIR MINUM TIRTA AMERTHA BUANA</h3>
        <!-- <h3>BULAN : {{ count($tickets) > 0 ?  date('F Y', strtotime($tickets[0]->created_at)) : 'Tidak ada data kosong' }} </h3> -->
        <h3>PERIODE : Dari {{$request->from}} Sampai {{$request->to}}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>PELAPOR</th>
                    <th>ALAMAT</th>
                    <th>AREA</th>
                    <th>TGL MASUK</th>
                    <th>KELUHAN</th>
                    <th>NO SPK</th>
                    <th>TGL DIKERJAKAN</th>
                    {{-- <th>PEKERJA</th> --}}
                    <th>KET / TINDAKAN</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1 ?>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td class="text-center">{{$no++}}</td>
                        <td>{{ $ticket->customer ? $ticket->customer->name:'' }}</td>
                        <td> @if ($ticket->address != null){{$ticket->address}}@endif  @if ($ticket->address == null){{$ticket->customer ? $ticket->customer->address : ''}}@endif</td>
                        <td>{{$ticket->area}}</td>
                        <td>@if ($ticket->created_at != null) {{$ticket->created_at->format('d/m/Y')}} @endif</td>
                        <td>{{$ticket->description}}</td>
                        <td>{{$ticket->spk}}</td>
                        <td>@if ($ticket->created_at != null) {{$ticket->created_at->format('d/m/Y')}} @endif</td>
                        {{-- <td>{{count($ticket->action) > 0 ? $ticket->action[0]->todo : '' }}</td> --}}
                      
                        <td>  @if($ticket->action != null) 
                            @foreach ($ticket->action as $ticketaction)
                                @if($ticketaction->memo != null) 
                                <pre>{{$ticketaction->memo}}</pre>
                                    
                                    <p></p>
                                @endif
                            @endforeach
                        @endif
                    </td>
                        {{-- <td> @foreach ($ticket->action as $ticketaction)*{{$ticketaction->description}}"<br>@endforeach
                        </td> --}}
                    </tr>
                @endforeach
               
            </tbody>
        </table>
    </section>
    <div class="baris1">
        <div class="kiri">
            <div class="" style="text-align : center">Mengetahui</div>
            <div class="jabatan" style="margin-bottom : 80px; ; text-align : center">Ka.{{ $mengetahui }}</div>
    <div class="nama"></div>
    <div class="nip" style = "border-top-style: solid; "></div>
        </div>
    
        <div class="kanan">
            <div class="" style="text-align : center">Tabanan, {{ date('d') }} {{ $month }} {{ date('Y') }}</div>
            <div class="" style="text-align : center">Di buat oleh</div>
            <div class="jabatan" style="margin-bottom : 80px ; text-align : center">Ka.{{ $dibuat }}</div>
            <div class="nama" style = ""></div>
    <div class="nama" style = "border-bottom-style: solid; "></div>
        </div>
    </div>
    <div class="tengah">
        <div class="" style="text-align : center">Menyetujui</div>
        <div class="jabatan" style="margin-bottom : 80px; text-align : center">{{ $menyetujui }}</div>
    <div class="nama" style="text-align : center">{{ $director_name }}</div>
    <div class="nip" style = "border-top-style: solid; "></div>
    </div>
<script>
onload = function (){
    window.print();
}
</script>
</body>
</html>