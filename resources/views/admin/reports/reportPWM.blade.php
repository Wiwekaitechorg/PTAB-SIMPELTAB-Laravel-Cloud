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
        <h2>REKAPITULASI USULAN PERGANTIAN WATER METER</h2>
        <h3>{{ Auth::user()->name }}</h3>
        <h3>{{ $unitName }}</h3>
        {{-- <!-- <h3>BULAN : {{ count($tickets) > 0 ?  date('F Y', strtotime($tickets[0]->created_at)) : 'Tidak ada data kosong' }} </h3> --> --}}
        <h3>Bulan {{$month}} {{date('Y')}}</h3>
        {{-- @endif --}}
      
        <table class="table">
        <tr>
            <th>NO.</th>
            <th>KODE</th>
            <th>NO.SBG.</th>            
            <th>NAMA PELANGGAN</th>
            <th>ALAMAT</th>
            <th>AREA</th>
            <th>GOL.</th>
            <th>OPERATOR</th>
            <th>STATUS WM</th> 
            <th>KETERANGAN</th>
        </tr>        
        {{-- isi data --}}
        <?php $no=1 ?>
        @foreach ($proposalWm as $d1)
        <tr>
            <td class="text-center"><?php echo "".$no?></td>
            <td >{{ $d1->close_queue }}{{ $d1->code }}</td>
            <td>{{ $d1->nomorrekening }}</td>
            
            <td>{{ $d1->namapelanggan }}</td>
            <td>{{ $d1->alamat }}</td>
            <td>{{ $d1->idareal }}</td>
            <td>{{ $d1->idgol }}</td>
            <td>{{ $d1->operator }}</td>
            <td>
            @if ($d1->status_wm == "103")
                WM Mati
             @elseif ($d1->status_wm == "102")
                WM Rusak       
             @elseif ($d1->status_wm == "101")
                WM Kabur             
             @else

             @endif
            </td>            
            <td>
            @if ($d1->status == "active")
                Sedang Diproses
             @elseif ($d1->status == "pending")
                Menunggu Proses      
             @elseif ($d1->status == "work")
                Dalam Pengerjaan
            @elseif ($d1->status == "close")
                Selesai Pengerjaan
            @elseif ($d1->status == "reject")
                Usulan Ditolak             
             @else

             @endif
            </td>
    
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
            <div class="jabatan">Ka. {{ $dapertement }}</div>
            <div class="" style="text-align : center; margin-bottom : 70px;"></div>
    <div class="nama"></div>
    <div class="nip" style = "border-top-style: solid; border-top-width: 1px; "></div>
        </div>
    
        <div class="kanan">
            <div class="" style="text-align : center">Tabanan, {{ date('d') }} {{ $month }} {{ date('Y') }}</div>
            <div class="" style="text-align : center">Ka. {{ $sub_dapertement }}</div>
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