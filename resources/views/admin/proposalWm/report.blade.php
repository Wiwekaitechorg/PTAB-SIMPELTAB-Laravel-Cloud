<!DOCTYPE html>
<html lang="en">
<head>
    <!-- biar mau masuk -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Perintah Kerja</title>
    <!-- <link rel="stylesheet" href="./style.css"/> -->
    <style type="text/css">
      .baris1 {
            display: flex;
            margin-top: 20px;
            margin-bottom: 20px;
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
            width: 25%;
            /* background-color: rgb(69, 18, 0); */
            margin-left: auto;
            /* text-align: center; */
        }
        .tengah {
            width: 25%;
            /* background-color: rgb(69, 18, 0); */
            margin: auto;
            /* text-align: center; */
        }
.grid-container {
  display: grid;
  margin-left: 40px;
  grid-template-columns: 150px auto;
 
}
.grid-item {
    margin-top: 5px;
    margin-bottom: 5px;
  /* background-color: rgba(255, 255, 255, 0.8); */
  /* border: 1px solid rgba(0, 0, 0, 0.8); */
}
            *{
            font-size: 14px;
        }
        .text-center {
                text-align: center;
        }
        .title{
            width: 300px;
            text-align: center;
        }
        .subtitle{
            text-align: center;
            width: 100%;
        }

        h3{
            letter-spacing: 1px;
        }
        .text-height{
            line-height: 0.10px;
        }
        .column {
            float: left;
            width: 33.33%;
            height:120px;
            text-align: center;
        }
        .row:after {
            content: "";
            display: table;
            clear: both;
        }
        @page { 
            size: A4 ;
            size:portrait;
            margin-right: 1.5cm;
            margin-left: 1.5cm;
            margin-top: 0cm;
            margin-bottom: 0cm;
        }
    </style>
</head>
<body onload="onload()" >
    {{-- <img src="{{ "https://simpletabadmin.ptab-vps-storage.com/images/QRCodeSimTab.png" }}" alt="" style="height: 100px; margin-left: 800px; ">
    <br> --}}
    {{-- @for ($i = 0; $i < 5 ; $i++) --}}
    <div class="" style="page-break-inside: avoid; ">
    <img src="{{ "https://simpletabadmin.ptab-vps-storage.com/images/kopSuratPDAM.PNG" }}" alt="" style="width: 800px; height: 200px; ">
    {{-- <div class="title" style="border-bottom : 1px">
        <h4>PERUSAHAAB UMUM DAERAH AIR MINUM TIRTA AMERTHA BUANA KABUPATEN TABANAN
            <hr style="width: 240px;">
        </h4>
   
    </div> --}}
    
    <div class="text-center">
        <h3>BERITA ACARA</h3>
        <h3>PERGANTIAN WATER METER
            <hr style="width: 190px;">
        </h3>
        <div class="text-height">
            Nomor : Perumda.TAB {{ $proposalWm->close_queue }}{{ $proposalWm->code }}
        </div>
    </div>
    <br>
    {{-- Dengan ini kami tugaskan saudara
    <p>1. KA SUBAG @if (!empty($proposalWm)) {{$proposalWm->name}} @endif</p>
    @foreach ($staffs as $index => $staff)<p>{{$index+2}}. {{$staff->staff_name}}</p>@endforeach
     untuk mengadakan kegiatan 
     @if ($proposalWm->subdapertement_id === 10)
     pergantian
     @else --}}
{{--     
     perbaikan
     @endif  --}}
     {{-- Water Meter --}}
    <p>Pada hari ini {{ $dayName }} tanggal {{ $date }}, bulan {{ $monthName }}, tahun {{ $year }} telah dilaksanakan pergantian Water Meter pada pelanggan tersebut dibawah ini :   </p>

    <div class="grid-container">
<div class="grid-item">
    Nama
</div>
<div class="grid-item">
    : {{ $proposalWm->namapelanggan }}
</div>
<div class="grid-item">
    Alamat
</div>
<div class="grid-item">
    : {{ $proposalWm->alamat }}
</div>
<div class="grid-item">
    Nomor Sambungan
</div>
<div class="grid-item">
    : {{ $proposalWm->nomorrekening }}
</div>

<div class="grid-item">
    Area
</div>


<div class="grid-item">
    : {{ $proposalWm->idareal }}
</div>


<div class="grid-item">
    No. Water Meter Lama
</div>
<div class="grid-item">
    : {{ $proposalWm->noWM1 }}
</div>
<div class="grid-item">
Merk Water Meter Lama    
</div>  
<div class="grid-item">
    : {{ $proposalWm->brandWM1 }}
</div>
<div class="grid-item">
    Stand Meter Akhir
</div>
<div class="grid-item">
    : {{ $proposalWm->standWM1 }}
</div>
</div>
<p>Diganti Dengan Water Meter</p>
<div class="grid-container">
  
 
    <div class="grid-item">
        Nomor
    </div>
    <div class="grid-item">
        : {{ $proposalWm->noWM2 }}
    </div>
    <div class="grid-item">
    Merk  
    </div>  
    <div class="grid-item">
        : {{ $proposalWm->brandWM2 }}
    </div>
    <div class="grid-item">
        Stand Meter
    </div>
    <div class="grid-item">
        : {{ $proposalWm->standWM2 }}
    </div>

  
    </div>

    <p>Pergantian Water Meter dilaksanakan karena : @if ($proposalWm->status_wm == "101")
        Water Meter kabur
        @elseif ($proposalWm->status_wm == "102")
Water Meter rusak
@elseif ($proposalWm->status_wm == "103")
Water Meter mati
        @else

    @endif</p>

    <p>Demikian Berita Acara Pergantian Water Meter ini dibuat untuk dapat digunakan seperlunya.</p>
    {{-- <div style=" page-break-inside: avoid;"> --}}
    <div class="">
        <div class="baris1">
            <div class="kiri">
                <div class="" style="text-align : center">Pengawas</div>
                <div class="jabatan"></div>
               
                @if($proposalWm->dapertement_id == "2")
                <div class="" style="text-align : center; margin-bottom : 10px;"></div>
                <img src="{{ "https://simpletabadmin.ptab-vps-storage.com/ttd/pengawas.png" }}" alt="" style="height: 80px; margin-left : 80px">
               @else
               <div class="" style="text-align : center; margin-bottom : 80px;"></div>
                @endif
                <div class="nama">{{ count($staffs) > 0 ? $staffs[count($staffs)-1]->staff_name : "" }}</div>
        <div class="jabatan" style="margin-bottom : 10px"></div>

        <div class="nip" style = "border-top-style: solid; "></div>
            </div>
        
            <div class="kanan">
                {{-- <div class="" style="text-align : center">Tabanan,</div> --}}
                <div class="" style="text-align : center">Dikerjakan Oleh</div>
               
                
                @if($proposalWm->dapertement_id == "2")
                <div class="jabatan" style="margin-bottom : 10px"></div>
                <img src="{{ "https://simpletabadmin.ptab-vps-storage.com/ttd/pihak%20ketiga.png" }}" alt="" style="height: 80px; margin-left : 80px">
                @else
                <div class="jabatan" style="margin-bottom : 80px"></div>
                @endif
                <div class="nama" style = "border-bottom-style: solid; ">Pihak Ketiga</div>
        
            </div>
        </div>
        <div class="tengah">
            <div class="" style="text-align : center">Mengetahui</div>
            {{-- <div class="jabatan" style="margin-bottom : 80px; text-align : center"></div> --}}
            <div class="jabatan" style="margin-bottom : 10px; text-align : center"></div>
        <div class="nama"></div>
        @if($proposalWm->dapertement_id == "2")
        <div class="jabatan" style="margin-bottom : 10px; text-align : center"></div>
            <img src="{{ "https://simpletabadmin.ptab-vps-storage.com/ttd/distribusi.png" }}" alt="" style="height: 80px; margin-left : 80px">
      @else
      <div class="jabatan" style="margin-bottom : 80px; text-align : center"></div>
            @endif
      
        <div class="nama" style = "border-bottom-style: solid; ">{{ $proposalWm->dapertement_name }}</div>
        {{-- <div class="nip" style = "border-top-style: solid; ">NIK.</div> --}}
        </div>
    </div> 
    {{-- <br>
    <br>
    <br>
    Lembaran :
    <br>
    <p>&nbsp &nbsp &nbsp &nbsp 1. Hub. Langganan</p>
    <p>&nbsp &nbsp &nbsp &nbsp 2. Pelanggan</p>
    <p>&nbsp &nbsp &nbsp &nbsp 3. Bag. Meter Segel</p>
    <p>&nbsp &nbsp &nbsp &nbsp 4. Bag. SPI</p> --}}
    <img src="{{ "https://simpletabadmin.ptab-vps-storage.com/images/QRCodeSimTab.png" }}" alt="" style="height: 80px; margin-left: 700px;">
</div>
    {{-- @endfor --}}
<script>
    onload = function (){
        window.print();
    }
</script>
</body>
</html>