@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Export to Excel
    </div>

    <div class="card-body">
        <form action="{{ route('admin.customers.reportExcel') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('month') ? 'has-error' : '' }}">
                <label for="month">Bulan*</label>
                <select id="month" name="month" class="form-control" required>
                    <option value="">--Pilih Bulan--</option>
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
                @if($errors->has('month'))
                    <em class="invalid-feedback">
                        {{ $errors->first('month') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('idareal') ? 'has-error' : '' }}">
                <label for="idareal">Area*</label>
                <select id="idareal" name="idareal" class="form-control" required>
                    <option value="" >--Pilih Bulan--</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->code }}">{{ $area->code }} | {{ $area->NamaWilayah }}</option>
                    @endforeach
                </select>
                @if($errors->has('idareal'))
                    <em class="invalid-feedback">
                        {{ $errors->first('idareal') }}
                    </em>
                @endif
            </div>
           
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection