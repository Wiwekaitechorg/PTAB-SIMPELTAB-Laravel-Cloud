@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Ganti Nomor
    </div>

    <div class="card-body">
        <form action="{{ route("admin.devicewa.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                <label for="phone">Masukan Nomor Baru*</label>
                <input type="text" id="phone" name="phone" class="form-control" value="" required>
                @if($errors->has('phone'))
                    <em class="invalid-feedback">
                        {{ $errors->first('phone') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection