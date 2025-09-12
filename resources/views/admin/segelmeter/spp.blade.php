<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SURAT PERINTAH PENYEGELAN/PENCABUTAN/WATER METER</title>
    <link rel="stylesheet" href="{{ asset('css/printSpp.css') }}"/>
</head>
<body>
    <div class="container">
        <div class="headertitle">
            <div>SURAT PERINTAH PENYEGELAN/PENCABUTAN WATER METER</div>
            <div>NOMOR : 4/SCB/JUL/2021</div>
        </div>
        <div class="section">
            <div style="height: 9.32cm ;">
                Kepada : Petugas Meter Segel
                <div>
                    Agar dilaksanakan Penyegelan/Pencabutan Water Meter :
                </div>
                    <div class="boxdata">
                        <span class="title">
                            Nama
                        </span>
                        <span class="titik">
                            :
                        </span>
                        <span class="data">
                            {{ $customer->namapelanggan }}
                        </span>
                    </div>
                    <div class="boxdata">
                        <span class="title">
                            Alamat
                        </span>
                        <span class="titik">
                            :
                        </span>
                        <span class="data">
                            {{ $customer->alamat }}
                        </span>
                    </div>
                    <div class="boxdata">
                        <span class="title">
                            No. SBG
                        </span>
                        <span class="titik">
                            :
                        </span>
                        <span class="data">
                            {{ $customer->nomorrekening }}
                        </span>
                    </div>
                    <div class="boxdata">
                        <span class="title">
                            Area
                        </span>
                        <span class="titik">
                            :
                        </span>
                        <span class="data">
                            {{ $customer->idareal }}
                        </span>
                    </div>
                <div>
                    {{-- Penyegelan dilakukan karena tidak melakukan pembayaran tagihan air periode : 
                    MEI 2021 = Rp.177.072, JUN-2021 = Rp.127.045  --}}
                    Penyegelan dilakukan karena tidak melakukan pembayaran tagihan air periode : 
                    @foreach ($dataPembayaran as $key=>$item )
                        {{Bulan( (new DateTime($item['periode']))->format('m')).'-'. (new DateTime($item['periode']))->format('Y') .' = '. Rupiah($item['wajibdibayar'] - $item['sudahbayar']) }}
                    @endforeach
                    dengan Total {{ is_int($recap['denda']) ? Rupiah($recap['denda']) : $recap['denda'] }} (belum termasuk denda)
                </div>
                <div>
                    PERHATIAN : 1. Bila dalam 2 bulan dari tanggal SPK ini tunggakan tidak dilunasi, maka sambungan air minum akan dicabut.
                </div>
                <div style="margin-left: 98px;">
                    2. Penyambungan kembali dilaksanakan sesuai ketentuan yang berlaku. Abaikan surat ini bila tunggakan sudah
                </div>
                <div style="margin-left: 105px;">
                    dilunasi
                </div>
                <div style="display: flex; position: relative; top: 5px;">
                    <div class="box">
                        Petugas
                    </div>
                    <div class="box">
                        Pelanggan/Konsumen
                    </div>
                    <div class="box1">
                       <div>Tabanan {{ date('d') .' '. Bulan(date('m')) .' '. date('Y') }}</div>
                       <div>An. Direktur Perusahaan Umum Daerah Air Minum</div>
                       <div>Tirta Amertha Buana Kabupaten Tabanan</div>
                       <div>Ka. Bag Hubungan Langganan</div>
                    </div>
                </div>
                <br>
                <br>
                <div style="display: flex; position: relative; top: 5 px;">
                    <div class="box">
                    ____________________
                    </div>
                    <div class="box">
                    ____________________
                    </div>
                    <div class="box1">
                      
                        Ida Bagus Marjaya Wirata, Se.,MM,
                    </div>
                </div>
            </div>
        </div>
    </div>    
</body>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(()=>{
        window.print()
    })
</script>
</html>
