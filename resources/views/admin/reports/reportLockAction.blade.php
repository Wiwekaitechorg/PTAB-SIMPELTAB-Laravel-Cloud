<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SIMPELTAB</title>
    <link href="{{ asset('css/printsubhumas.css') }}" rel="stylesheet" />

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
      
        /* table { page-break-inside:auto } */
   tr    { page-break-inside:avoid; page-break-after:auto }
    </style>
    
    
        <style type="text/css" media="print">

/* table { page-break-inside:auto } */
   tr    { page-break-inside:avoid; page-break-after:auto }
        .baris1 {
            margin-top: 20px;
            display: flex;
        }
        .kiri {
            width: 16%;
            /* background-color: rgb(5, 0, 69); */
            align-items: left;
            /* text-align: center; */
       
        }
        .kanan {
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
<body class="A4"  onload="onload()" >
    {{-- {{ dd($tickets) }} --}}
    <section class="sheet padding-10mm">
        @if ($jum+1 === 1)
        <h2>REKAPITULASI PENCABUTAN WATER METER</h2>
        {{-- <!-- <h3>BULAN : {{ count($tickets) > 0 ?  date('F Y', strtotime($tickets[0]->created_at)) : 'Tidak ada data kosong' }} </h3> --> --}}
        <h3>bulan {{$month}} {{date('Y')}}</h3>
        @endif
      
        <table class="table">
        <tr>
            <th rowspan="3">No</th>
            <th rowspan="3">SBG</th>
            <th rowspan="3">AREA</th>
            <th rowspan="3">NAMA</th>
            <th rowspan="3">ALAMAT</th>
            <th rowspan="3">GOL</th>

            <th colspan="4">Service</th>
 <th colspan="4">Tanggal Surat</th>
 <th colspan="4">Tindakan</th>
 <th rowspan="3">JML HARI</th>
<th colspan="4">WATER METER LAMA</th>
<th colspan="4">WATER METER BARU</th>
<th rowspan="3">DIKERJAKAN OLEH</th>
<th rowspan="3">KET</th>

        </tr>
        <tr>
            <th rowspan="2" style="border-width : 0;" class="text-center"></th>
            <th rowspan="2" style="border-width : 0;"  class="text-center"></th>
            <th rowspan="2" class="text-center">Dikeluarkan</th>
            <th rowspan="2" class="text-center">Diterima</th>
            <th rowspan="2" class="text-center">Kd</th>
            <th rowspan="2" class="text-center">Jenis</th>
            <th rowspan="2" class="text-center">No.SPK</th>
            <th rowspan="2" class="text-center">Tanggal</th>
            <th rowspan="2" class="text-center">Hri/Wkt</th>
            <th rowspan="2" class="text-center">Kd</th>
            <th rowspan="2" class="text-center">Alasan</th>
            <th rowspan="2" class="text-center">oleh</th>
            <th rowspan="2" class="text-center">dari</th>
        </tr>
        <tr>
        </tr>
        {{-- isi data --}}
        <?php $no=1 ?>
        @for ($i=0 ; $i<count($d1); $i++)
            
        <tr>
            <td class="text-center"><?php echo "".$no+(24*$jum)?></td>
            <td>{{ $d1[$i]['nomorrekening'] }}</td>
            <td>{{ $d1[$i]['wilayah_id'] }}</td>
            <td>{{ $d1[$i]['namapelanggan'] }}</td>
            <td>{{ $d1[$i]['alamat'] }}</td>
            <td>{{ $d1[$i]['idgol'] }}</td>
            <td ><?php echo "".substr("0000", 0, -strlen(strval(($no+(24*$jum))))).($no+(24*$jum))?></td>
            <td>/SCB/{{ $monthR }}/{{ date('y') }}</td>
            <td>27/{{ date('m/Y', strtotime('-1 month', strtotime(date('Y-m-d')))) }}</td>
            <td>25/{{ date('m/Y', strtotime('-1 month', strtotime(date('Y-m-d')))) }}</td>
            <td>{{$d1[$i]['status_paid_this_month'] < 1?"S" : "L"}}</td>
            <td>{{$d1[$i]['status_paid_this_month'] < 1?$d1[$i]['lockActionType'] : "Lunas"}}</td>
            <td></td>
            <td>{{$d1[$i]['action_date'] ? date('d/m/Y', strtotime('0 month', strtotime($d1[$i]['action_date']))) : "" }}</td>
            <td>{{ $d1[$i]['jarak'] }}</td>
            <td>{{$d1[$i]['status_paid_this_month'] < 1?"" : "L"}}</td>
            <td>{{$d1[$i]['status_paid_this_month'] < 1?"#NA" : "Lunas"}}</td>
            <td>{{ $d1[$i]['staff_name'] }}</td>
            <td>{{ $d1[$i]['staff_name'] !="" ? "PDAM" : "" }}</td>
            <td>{{str_replace('-','/',$d1[$i]['tglbayarterakhir'])}}</td>
        <?php $no++ ?>
            
            </tr>
          
            @endfor
        {{-- batas isi data  --}}
    </table>
    </section>
 @if ($take-0 === $jum-0)

 {{-- @if($i===29 || $i>=12 && $i< 16) --}}
 <div style=" page-break-inside: avoid;">
    <div class="baris1">
        <div class="kiri">
            <div class="" style="text-align : center">Mengetahui</div>
            <div class="jabatan">Ka.Bag</div>
            <div class="" style="text-align : center; margin-bottom : 70px;">Distribusi</div>
    <div class="nama"></div>
    <div class="nip" style = "border-top-style: solid; ">NIK.</div>
        </div>
    
        <div class="kanan">
            <div class="" style="text-align : center">Tabanan, {{ date('d') }} {{ $month }} {{ date('Y') }}</div>
            <div class="" style="text-align : center">Di buat oleh</div>
            <div class="jabatan" style="margin-bottom : 70px">Ka.Subag Meter Segel</div>
    <div class="nama" style = "border-bottom-style: solid; "></div>
        </div>
    </div>
    <div class="tengah">
        <div class="" style="text-align : center">Mengetahui</div>
        <div class="" style="text-align : center">Perumda Tirta Amertha Buana</div>
        <div class="jabatan" style="margin-bottom : 70px; text-align : center">Direksi</div>
    <div class="nama"></div>
    <div class="nip" style = "border-top-style: solid; ">NIK.</div>
    </div>
</div>

    {{-- @else
        <div class="baris1">
            <div class="kiri">
                <div class="" style="text-align : center">Mengetahui</div>
                <div class="jabatan">Ka.Bag</div>
                <div class="" style="text-align : center; margin-bottom : 70px;">Distribusi</div>
        <div class="nama"></div>
        <div class="nip" style = "border-top-style: solid; ">NIK.</div>
            </div>
        
            <div class="kanan">
                <div class="" style="text-align : center">Tabanan, {{ date('d') }} {{ $month }} {{ date('Y') }}</div>
                <div class="" style="text-align : center">Di buat oleh</div>
                <div class="jabatan" style="margin-bottom : 70px">Ka.Subag Meter Segel</div>
        <div class="nama" style = "border-bottom-style: solid; "></div>
            </div>
        </div>
        <div class="tengah">
            <div class="" style="text-align : center">Mengetahui</div>
            <div class="" style="text-align : center">Perumda Tirta Amertha Buana</div>
            <div class="jabatan" style="margin-bottom : 70px; text-align : center">Direksi</div>
        <div class="nama"></div>
        <div class="nip" style = "border-top-style: solid; ">NIK.</div>
        </div>
    @endif --}}
 @endif
    <script>
    onload = function (){
        window.print();
    }
</script>
</body>
</html>