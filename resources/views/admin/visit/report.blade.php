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
        <h2>REKAPITULASI Kunjungan</h2>
        <h3>{{ Auth::user()->name }}</h3>
        {{-- <h3>{{ $unitName }}</h3> --}}
        {{-- <!-- <h3>BULAN : {{ count($tickets) > 0 ?  date('F Y', strtotime($tickets[0]->created_at)) : 'Tidak ada data kosong' }} </h3> --> --}}
        <h3>Tanggal : {{ $from }} - {{ $to }}</h3>
        {{-- @endif --}}
      
        <table class="table">
        <tr>
            <th >No</th>
            <th >Nama Staff</th>
            <th >Status WM</th>
            <th >SBG</th>
            <th >Pelanggan</th>
            <th >Area</th>
            <th >Keterangan</th>
            <th >Tanggal</th>
        </tr>
        {{-- isi data --}}
        <?php $no=1 ?>
        @foreach ($visits as $d1)
      
            
        <tr>
            <td class="text-center"><?php echo "".$no?></td>
            <td>{{ $d1->staff->name }}</td>
            <td>{{ $d1->NamaStatus }}</td>
            <td>{{ $d1->customer ? $d1->customer->nomorrekening : ''}}</td>
            <td>{{ $d1->customer ? $d1->customer->namapelanggan : '' }}</td>
            <td>{{ $d1->customer ? $d1->customer->idareal : '' }}</td>
            <td>{{ $d1->description }}</td>
            <td>{{ $d1->created_at }}</td>
        <?php $no++ ?>
            
            </tr>
          
                
        @endforeach
        {{-- batas isi data  --}}
    </table>
    </section>

 {{-- @if($i===29 || $i>=12 && $i< 16) --}}
 {{-- <div style=" page-break-inside: avoid;">
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
</div> --}}

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