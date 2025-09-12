<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SIMPELTAB</title>
    <link href="{{ asset('css/printsubdistribusi.css') }}" rel="stylesheet" />
    <style type="text/css">
    .btn-danger {
        background-color: red;
    }
    .btn-warning {
        background-color: yellow;
    }
    .btn-primary {
        background-color: #008CBA;
    }
    .btn-success {
        background-color: #04AA6D;;
    }
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
        <h3>TINDAK LANJUT KONDISI PELAYANAN DISTRIBUSI KOTA DAN HUBUNGAN LANGGANAN </h3>
        <h3>PERIODE : Dari {{$request->from}} Sampai {{$request->to}}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>KODE</th>
                    <th>WILAYAH</th>
                    <th>SBG</th>
                    <th>CEK AWAL</th>
                    <th>NAMA</th>
                    <th>ALAMAT</th>
                    <th>PELAPOR</th>
                    <th>KETERANGAN</th>
                    <th>TINDAKAN TEKNIK</th>
                    <th>DURASI TINDAKAN</th>
                    <th>KODE TINDAKAN</th>
                    <th>KODE PENGECEKAN</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1 ?>
                @foreach ($complains as $complain)
                    <tr>
                        <td class="text-center">{{$no++}}</td>
                        <td>
                            @if  ($complain->complainstatus->code=='RED')
                            <button type="button" class="btn btn-danger btn-sm" disabled>&nbsp;</button>
                            @endif
                            @if  ($complain->complainstatus->code=='YLW')
                            <button type="button" class="btn btn-warning btn-sm" disabled>&nbsp;</button>
                            @endif
                            @if  ($complain->complainstatus->code=='BLU')
                            <button type="button" class="btn btn-primary btn-sm" disabled>&nbsp;</button>
                            @endif
                            @if  ($complain->complainstatus->code=='GRN')
                            <button type="button" class="btn btn-success btn-sm" disabled>&nbsp;</button>
                            @endif
                            </td>
                        <td>{{ $complain->areas ? $complain->areas->NamaWilayah:'' }}</td>
                        <td>{{ $complain->customer_id }}</td>
                        <td>{{ $complain->created_at->format('d/m/Y') }}</td>
                        <td>{{ $complain->customer_name }}</td>
                        <td> @if ($complain->address != null){{$complain->address}}@endif  @if ($complain->address == null){{$complain->customer ? $complain->customer->address : ''}}@endif</td>
                        <td>{{ $complain->users ? $complain->users->name:$complain->pbk->Name }}</td> 
                        <td>{{$complain->description}}</td>
                        <td>  @if($complain->action != null) 
                            @foreach ($complain->action as $complainaction)
                                @if($complainaction->memo != null) 
                                <pre>{{$complainaction->memo}}</pre>
                                    
                                    <p></p>
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <?php 
                    $datetime1 = strtotime($complain->created_at);
                    $datetime2 = strtotime(date('Y-m-d H:i:s'));
                    $dateclose = date_format(date_create($complain->updated_at),"d/m/Y");
                 
                    $days = (int)(($datetime2 - $datetime1)/86400);
                    if($complain->ticket){
                    foreach ($complain->ticket->action as $complainaction){
                        if($complainaction->complain_status_id == 4){
                            //$datetime1 = strtotime($complainaction->start);
                            $datetime2 = strtotime($complainaction->start);
                            $dateclose = date_format(date_create($complainaction->start),"d/m/Y");
                         
                            $days = (int)(($datetime2 - $datetime1)/86400);
                        }
                    } }                    
                ?>
                        <td>{{$days}} Hari</td>
                        <td>  @if($complain->ticket && $complain->ticket->action != null) 
                            @foreach ($complain->ticket->action as $complainaction)
                            <p>
                            @if  ($complainaction->status=='pending')
                            <button type="button" class="btn btn-warning btn-sm" disabled>&nbsp;</button><p>{{$complain->ticket->code.' '.$complainaction->created_at->format('d/m/Y')}}</p>
                            @endif
                            @if  ($complainaction->status=='active')
                            <button type="button" class="btn btn-primary btn-sm" disabled>&nbsp;</button><p>{{$complain->ticket->code.' '.$complainaction->created_at->format('d/m/Y')}}</p>
                            @endif
                            @if  ($complainaction->status=='close')
                            <button type="button" class="btn btn-success btn-sm" disabled>&nbsp;</button><p>{{$complain->ticket->code.' '.$complainaction->created_at->format('d/m/Y')}}</p>
                            @endif
                            </p>
                            @endforeach
                        @endif
                    </td>
                      
                    <td>  @if($complain->ticket && $complain->ticket->check != null) 
                            @foreach ($complain->ticket->check as $complaincheck)
                            <p>
                            @if  ($complaincheck->status=='pending')
                            <button type="button" class="btn btn-warning btn-sm" disabled>&nbsp;</button><p>{{$complain->ticket->code.' '.$complaincheck->created_at->format('d/m/Y')}}</p>
                            @endif
                            @if  ($complaincheck->status=='active')
                            <button type="button" class="btn btn-primary btn-sm" disabled>&nbsp;</button><p>{{$complain->ticket->code.' '.$complaincheck->created_at->format('d/m/Y')}}</p>
                            @endif
                            @if  ($complaincheck->status=='close')
                            <button type="button" class="btn btn-success btn-sm" disabled>&nbsp;</button><p>{{$complain->ticket->code.' '.$complaincheck->created_at->format('d/m/Y')}}</p>
                            @endif
                            </p>
                            @endforeach
                        @endif
                    </td>
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