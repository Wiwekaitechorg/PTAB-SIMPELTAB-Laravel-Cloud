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
            width: 30%;
            /* background-color: rgb(5, 0, 69); */
            align-items: left;
            /* text-align: center; */
        }
        .kanan {
            margin-right: 30px;
            width: 30%;
            /* background-color: rgb(69, 18, 0); */
            margin-left: auto;
            /* text-align: center; */
        }
        .tengah {
            width: 20%;
            /* background-color: rgb(69, 18, 0); */
            margin: auto;
            /* text-align: center; */
        }
      
        /* table { page-break-inside:auto } */
   tr    { page-break-inside:avoid; page-break-after:auto }
    </style>
    
    
        <style type="text/css" media="print">
          th {
  font-size: 11px;
}
   td {
  font-size: 11px;
}
/* table { page-break-inside:auto } */
   tr    { page-break-inside:avoid; page-break-after:auto }
        .baris1 {
            margin-top: 20px;
            display: flex;
        }
        .kiri {
            width: 20%;
            /* background-color: rgb(5, 0, 69); */
            align-items: left;
            /* text-align: center; */
       
        }
        .kanan {
            width: 20%;
            /* background-color: rgb(69, 18, 0); */
            margin-left: auto;
            /* text-align: center; */
        }
        .tengah {
            width: 20%;
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
        {{-- @if ($jum+1 === 1) --}}
        <h2>REKAPITULASI PERGANTIAN WATER METER</h2>
        <h3>{{ Auth::user()->name }}</h3>
        <h3>{{ $unitName }}</h3>
        {{-- <!-- <h3>BULAN : {{ count($tickets) > 0 ?  date('F Y', strtotime($tickets[0]->created_at)) : 'Tidak ada data kosong' }} </h3> --> --}}
        <h3>Bulan {{$month}} {{date('Y')}}</h3>
        {{-- @endif --}}
      
        <table class="table">
        <tr>
            <th rowspan="3">No</th>
            <th rowspan="3">SBG</th>
            <th rowspan="3">AREA</th>
            <th rowspan="3">NAMA</th>
            <th rowspan="3">ALAMAT</th>
            <th rowspan="3">GOL</th>

            <th colspan="1">SERVICE</th>
 <th colspan="2">TANGGAL SURAT</th>
 <th colspan="5">TINDAKAN</th>
 <th rowspan="3">JML HARI</th>
<th colspan="3">WATER METER LAMA</th>
<th colspan="3">WATER METER BARU</th>
{{-- <th rowspan="3">DIKERJAKAN OLEH</th> --}}
<th rowspan="3">KET</th>

        </tr>
        <tr>
            {{-- <th rowspan="2" style="border-width : 0;" class="text-center"></th>
            <th rowspan="2" style="border-width : 0;"  class="text-center"></th> --}}
            {{-- <th rowspan="2" class="text-center">KD</th> --}}
            <th rowspan="2" class="text-center">ALASAN</th>
            <th rowspan="2" class="text-center">DITERIMA</th>
            <th rowspan="2" class="text-center">DIKELUARKAN</th>
            {{-- <th rowspan="2" class="text-center">KD</th> --}}
            <th rowspan="2" class="text-center">JENIS</th>
            {{-- <th rowspan="2" class="text-center" style="padding-right: 40px; background-color :blue"></th> --}}
            <th rowspan="2" class="text-center"></th>
            <th rowspan="2" class="text-center" style="border-right-width: 0px"></th>
            <th rowspan="2" class="text-center"  style="border-left-width: 0px">NO.BA</th>
            <th rowspan="2" class="text-center">TANGGAL</th>
            <th rowspan="2" class="text-center">NO</th>
            <th rowspan="2" class="text-center">MERK</th>
            <th rowspan="2" class="text-center">STAND</th>

            <th rowspan="2" class="text-center">NO</th>
            <th rowspan="2" class="text-center">MERK</th>
            <th rowspan="2" class="text-center">STAND</th>
        </tr>
        <tr>
        </tr>
        {{-- isi data --}}
        <?php $no=1 ?>
        @foreach ($proposalWm as $d1)
      
            
        <tr>
            <td class="text-center"><?php echo "".$no?></td>
            <td>{{ $d1->nomorrekening }}</td>
            <td>{{ $d1->idareal }}</td>
            <td>{{ $d1->namapelanggan }}</td>
            <td>{{ $d1->alamat }}</td>
            <td>{{ $d1->idgol }}</td>
            <td>
            @if ($d1->status_wm == "103")
                Mati
             @elseif ($d1->status_wm == "102")
                Rusak       
             @elseif ($d1->status_wm == "101")
                Kabur             
             @else

             @endif
            </td>
            {{-- <td>{{ $d1->status_wm }}</td> --}}

            <td>{{date('d-m-Y', strtotime('0 month', strtotime( $d1->diterima )))}}</td>
            <td>{{ $d1->dikeluarkan ? date('d-m-Y', strtotime('0 month', strtotime( $d1->dikeluarkan ))) : date('d-m-Y', strtotime('0 month', strtotime( $d1->diterima )))}}</td>

            {{-- <td>{{ $d1->subdapertement_id === 10? '4' : '3'}}</td> --}}
            {{-- <td>{{ $d1->subdapertement_id === 10? 'Ganti WM.' : 'Perbaikan WM.'}}</td> --}}
            <td>Ganti WM.</td>
            <td>Perumda TAB</td>
            {{-- group start --}}
            <td >{{ $d1->close_queue }}</td>
             <td >{{ $d1->code }}</td>
  {{-- group end --}}
<td>{{ date('d-m-Y', strtotime('0 month', strtotime($d1->date)))  }}</td>
<td>
<?php 
  $tgl2 = new DateTime($d1->dikeluarkan);
                $tgl1 = new DateTime($d1->diterima);
                $jarak = $tgl2->diff($tgl1);
                $jarak = $jarak->days;
                if($jarak == "0"){
                    echo "1";
                }
                else{
                    echo $jarak;
                }
                
?>

</td>
<td>{{ $d1->noWM1 }}</td>
<td>{{ $d1->brandWM1 }}</td>
<td>{{ $d1->standWM1 }}</td>
<td>{{ $d1->noWM2 }}</td>
<td>{{ $d1->brandWM2 }}</td>
<td>{{ $d1->standWM2 }}</td>
{{-- <td>KOP.DARMA TIRTA</td> --}}
<td></td>
    
<?php $no++ ?>
            
            </tr>
          
                
        @endforeach
        {{-- batas isi data  --}}
    </table>
    </section>

 {{-- @if($i===29 || $i>=12 && $i< 16) --}}
 <div style=" page-break-inside: avoid;">
    <div class="baris1">
        <div class="kiri">
            <div class="" style="text-align : center">Mengetahui</div>
            <div class="jabatan" style="text-align : center">Ka. {{ $dapertement }}</div>
            <div class="" style="text-align : center; margin-bottom : 70px;"></div>
    <div class="nama"></div>
    <div class="nip" style = "border-top-style: solid; border-top-width: 1px; "></div>
        </div>
    
        <div class="kanan">
            <div class="" style="text-align : center">Tabanan, {{ date('d') }} {{ $month }} {{ date('Y') }}</div>
            <div class="" style="text-align : center">Ka.subag {{ preg_replace("/SUBAG/", "", $sub_dapertement) }}</div>
            <div class="jabatan" style="margin-bottom : 70px"></div>
    <div class="nama" style = "border-bottom-style: solid; border-top-width: 1px; "></div>
        </div>
    </div>
    <div class="tengah">
        <div class="" style="text-align : center">Menyetujui</div>
        <div class="" style="text-align : center">Perumda Tirta Amertha Buana</div>
        <div class="jabatan" style="margin-bottom : 70px; text-align : center">{{ $menyetujui }}</div>
    <div class="nama">{{ $director_name }}</div>
    <div class="nip" style = "border-top-style: solid; border-top-width: 1px; "></div>
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
 {{-- @endif --}}
    <script>
    onload = function (){
        window.print();
    }
</script>
</body>
</html>