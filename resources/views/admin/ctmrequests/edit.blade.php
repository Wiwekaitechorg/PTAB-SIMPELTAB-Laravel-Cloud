@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.ctmrequest.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.ctmrequests.update', [$ctmrequest->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('norek') ? 'has-error' : '' }}">
                <label for="norek">No. SBG:</label>
                <input readonly type="text" id="norek" name="norek" class="form-control" value="{{ old('norek', isset($ctmrequest) ? $ctmrequest->norek : '') }}">
            </div>
            <div class="form-group {{ $errors->has('monthyear') ? 'has-error' : '' }}">
                <label for="monthyear">Periode:</label>
                <input readonly type="text" id="monthyear" name="monthyear" class="form-control" value="{{ old('monthyear', isset($ctmrequest) ? $ctmrequest->monthyear : '') }}">
            </div>
            <div class="form-group {{ $errors->has('img2') ? 'has-error' : '' }}">
                <label for="img2">Foto Angka Meter (Bulan Lalu):</label>
                <img  height="200px" width="300px"  src={{$img2}} alt="">
            </div>
            <div class="form-group {{ $errors->has('img') ? 'has-error' : '' }}">
                <label for="img">Foto Angka Meter (Bulan Ini):</label>
                <img  height="200px" width="300px"  src={{$img}} alt="">
            </div>
            <div class="form-group {{ $errors->has('wmmeteran') ? 'has-error' : '' }}">
                <label for="wmmeteran">{{ trans('global.ctmrequest.fields.wmmeteran') }}*</label>
                <input type="text" id="wmmeteran" name="wmmeteran" class="form-control" value="{{ old('wmmeteran', isset($wmmeteran) ? $wmmeteran : '') }}">
                @if($errors->has('wmmeteran'))
                    <em class="invalid-feedback">
                        {{ $errors->first('wmmeteran') }}
                    </em>
                @endif
                <input type="hidden" name="norek" value="{{ $ctmrequest->norek }}">
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.approve') }}">
            </div>
        </form>
    </div>
</div>

@endsection