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
    <img src="{{ "https://simpletabadmin.ptab-vps-storage.com/images/QRCodeSimTab.png" }}" alt="" style="height: 100px; margin-left: 800px; ">
    <br>
    <img src="{{ "https://simpletabadmin.ptab-vps-storage.com/images/kopSuratPDAM.PNG" }}" alt="" style="width: 800px; height: 200px; ">
    {{-- <div class="title">
        <h4>PERUSAHAAN DAERAH AIR MINUM KABUPATEN TABANAN
            <hr style="width: 240px;">
        </h4>
    </div>
     --}}
    <div class="text-center">
        <h3>SURAT PERINTAH KERJA
            <hr style="width: 190px;">
        </h3>
        <div class="text-height">
            Nomor : {{$ticket->spk}}
        </div>
    </div>
    <br>
    Dengan ini kami tugaskan saudara
    <p>1. KA SUBAG @if (!empty($subdapertement)) {{$subdapertement->name}} @endif</p>
    @foreach ($staffs as $index => $staff)<p>{{$index+2}}. {{$staff->name}}</p>@endforeach
     untuk mengadakan kegiatan penelitian / perbaikan terhadap masalah-masalah yang terjadi pada saluran Air Minum seperti yang tercantum pada formulir Permintaan Service terlampir
    <p>Demikian untuk dilaksanakan sebagaimana mestinya.   </p>
   <div class="row">
        <div class="column">
        </div>
        <div class="column">
        </div>
        <div class="column">
            Tabanan, {{$ticket->created_at->format('d/m/Y')}}
            <br>
            Yang memberi Perintah
            <br>
            Ka.
            <br>
            <br>
            <br>
            <br>
            <br>
            _____________________
            <br>
            Nik. 10.09
        </div>
    </div>  
    <br>
    <br>
    <br>
    NB:  Kalau Pekerjaan Lambat (Lebih dari 3 hari)
    <br>
    &nbsp &nbsp &nbsp &nbsp supaya laporan keatasan
<script>
    onload = function (){
        window.print();
    }
</script>
</body>
</html>