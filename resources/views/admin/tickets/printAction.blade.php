<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Verdana&display=swap" rel="stylesheet" />
        <link href="{{ asset('css/printAction.css') }}" rel="stylesheet" />
        <title>Document</title>
    </head>
    <body onload="onload()">
        <div class="v19_2">
            <div class="v19_6">
                <span class="v19_8">Unit Umum</span>
                <span class="v19_14">{{$ticket->customer->name}}</span>
                <span class="v19_71">Admin C</span>
                <span class="v21_177">08523121861</span>
                <span class="v19_73">26/07/2021</span>
                <span class="v19_74">09.00</span>
                <span class="v19_75">09.01</span>
                <span class="v19_76">Prf 1</span>
                <span class="v19_77">Prf 2</span>
                <span class="v19_79">26/07/2021</span>
                <span class="v19_83">{{$ticket->customer->name}}</span>
                <span class="v19_84">Kediri Rock City</span>
                <span class="v19_85">123456789</span>
                <span class="v19_87">Pipa bocor akibat galian tanah ilegal</span>
            </div>
            <div class="v19_89">
                <span class="v19_91">123456789</span>
                <span class="v19_93">26/07/2021</span>
                <span class="v19_127">Tanggal</span>
                <span class="v19_131">Mantap</span>
                <span class="v19_139">Petugas 1</span>
                <span class="v19_140">Petugas 2</span>
                <span class="v19_141">Prf 2</span>
                <span class="v19_142">Prf 1</span>
                <span class="v19_143">26/07/21</span>
                <span class="v19_144">26/07/21</span>
                <span class="v19_145">09.20</span>
                <span class="v19_146">09.21</span>
                <span class="v19_147">Pipa diganti karena sudah tidak dapat digunakan kembali</span>
                <span class="v19_148">Jam</span>
                <span class="v19_149">Jumlah Biaya Rp</span>
                <span class="v19_150">10.00</span>
                <span class="v19_151">?</span>
                <span class="v19_152">Pelanggan</span>
                <span class="v19_153">26/07/21</span>
                <span class="v19_154">10.00</span>
                <span class="v19_155">20.000</span>
            </div>
        </div>

        <script>
            onload = function (){
                window.print();
            }
        </script>
    </body>
    </html>