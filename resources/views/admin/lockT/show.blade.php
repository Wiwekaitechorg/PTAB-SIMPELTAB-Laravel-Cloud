@extends('layouts.admin')
@section('content')
@if($errors->any())
<!-- <h4>{{$errors->first()}}</h4> -->
    <?php 
        echo "<script> alert('{$errors->first()}')</script>";
    ?>
@endif
<div class="card">

    <div class="card-header">
        {{ trans('global.segelmeter.title') }}  {{ trans('global.list') }} Detail
    </div>
    <div class="card-body">
        <table class="w-75">
            <tr>
                <td>Tahun Pembayaran </td>
                <td>:</td>
                <td>{{ $customer->year }}</td>
            </tr>
            <tr>
                <td>No Sambungan </td>
                <td>:</td>
                <td>{{ $customer->nomorrekening }}</td>
            </tr>
            <tr>
                <td>Nama Pelanggan </td>
                <td>:</td>
                <td>{{ $customer->namapelanggan }}</td>
            </tr>
            <tr>
                <td>Alamat </td>
                <td>:</td>
                <td>{{ $customer->alamat }}</td>
            </tr>
            <tr>
                <td>Gol. Tarif </td>
                <td>:</td>
                <td>{{ $customer->idgol }}</td>
            </tr>
            <tr>
                <td>Areal </td>
                <td>:</td>
                <td>{{ $customer->idareal }}</td>
            </tr>
            <tr>
                <td>Status </td>
                <td>:</td>
                <td>{{ $customer->status == 1 ? 'Aktif' : 'Pasif' }}</td>
            </tr>
        </table>

        <table class="table text-center">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">No Rekening</th>
                    <th scope="col">Periode</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">M3</th>
                    <th scope="col">Wajib Dibayar(Rp)</th>
                    <th scope="col">Terbayar(Rp)</th>
                    <th scope="col">Denda(Rp)</th>
                    <th scope="col">Sisa(Rp)</th>
                  </tr>
            </thead>
            <tbody>
                @foreach ($dataPembayaran as $key =>$item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>{{ $item['norekening'] }}</td>
                        <td>{{ $item['periode'] }}</td>
                        <td>{{ $item['tanggal'] }}</td>
                        <td>{{ $item['m3'] }}</td>
                        <td>{{ Rupiah($item['wajibdibayar']) }}</td>
                        <td>{{ Rupiah($item['sudahbayar']) }}</td>
                        <td>{{ is_int($item['denda']) ? Rupiah($item['denda']) : $item['denda'] }}</td>
                        <td>{{ Rupiah($item['sisa']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="mt-3">
            <h4>Jumlah Tunggakan</h4>
            <table class="table w-75 font-weight-bold">
                <tr>
                    <td>1. </td>
                    <td>Tagihan Air</td>
                    <td>{{Rupiah( $recap['tagihan'] )}}</td>
                </tr>
                <tr>
                    <td>2. </td>
                    <td>Denda</td>
                    <td>{{ is_int( $recap['denda']) ?Rupiah($recap['denda']) : $recap['denda']  }}</td>
                </tr>
                <tr>
                    <td> </td>
                    <td>Total</td>
                    <td>{{ Rupiah($recap['total']) }}</td>
                </tr>
            </table>
        </div>

        @if ( is_int($recap['denda']) && $recap['denda'] >0 || $item['denda'] =='SSB (Sanksi Denda Setara Sambungan Baru)')
            <div class="mt-2">
                <a href="{{ route('admin.lockT.sppprint', $lock->id) }}" class="btn btn-primary">Print SPP</a >
            </div>
            
        @endif
    </div>
</div>
@endsection
