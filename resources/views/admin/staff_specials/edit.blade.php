@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.staffSpecial.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.staffSpecials.update', [$staffSpecial->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
           
            <div class="form-group {{ $errors->has('expired_date') ? 'has-error' : '' }}">
                <label for="expired_date">{{ trans('global.staffSpecial.fields.expired_date') }}*</label>
                <input type="date" id="expired_date" name="expired_date" class="form-control" value="{{ old('expired_date', isset($staffSpecial) ? $staffSpecial->expired_date : '') }}">
                @if($errors->has('expired_date'))
                    <em class="invalid-feedback">
                        {{ $errors->first('expired_date') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('fingerprint') ? 'has-error' : '' }}">
                <label for="fingerprint">Fingerprint*</label>
                <select id="fingerprint" name="fingerprint" class="form-control" value="{{ old('fingerprint', isset($staffSpecial) ? $staffSpecial->fingerprint : '') }}">
                    <option value="">--Pilih Status--</option>
                    {{-- @foreach ($jobs as $key=>$job ) --}}
                    <option value="ON" {{$staffSpecial->fingerprint == "ON" ? 'selected' : ''}}>ON</option>
                    <option value="OFF" {{$staffSpecial->fingerprint == "OFF" ? 'selected' : ''}}>OFF</option>
                        {{-- @endforeach --}}
                </select>
                @if($errors->has('fingerprint'))
                    <em class="invalid-feedback">
                        {{ $errors->first('fingerprint') }}
                    </em>
                @endif
            </div>


            <div class="form-group {{ $errors->has('camera') ? 'has-error' : '' }}">
                <label for="camera">camera*</label>
                <select id="camera" name="camera" class="form-control" value="{{ old('camera', isset($staffSpecial) ? $staffSpecial->camera : '') }}">
                    <option value="">--Pilih Status--</option>
                    {{-- @foreach ($jobs as $key=>$job ) --}}
                    <option value="ON" {{$staffSpecial->camera == "ON" ? 'selected' : ''}}>ON</option>
                    <option value="OFF" {{$staffSpecial->camera == "OFF" ? 'selected' : ''}}>OFF</option>
                        {{-- @endforeach --}}
                </select>
                @if($errors->has('camera'))
                    <em class="invalid-feedback">
                        {{ $errors->first('camera') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('gps') ? 'has-error' : '' }}">
                <label for="gps">gps*</label>
                <select id="gps" name="gps" class="form-control" value="{{ old('gps', isset($staffSpecial) ? $staffSpecial->gps : '') }}">
                    <option value="">--Pilih Status--</option>
                    {{-- @foreach ($jobs as $key=>$job ) --}}
                    <option value="ON" {{$staffSpecial->gps == "ON" ? 'selected' : ''}}>ON</option>
                    <option value="OFF" {{$staffSpecial->gps == "OFF" ? 'selected' : ''}}>OFF</option>
                        {{-- @endforeach --}}
                </select>
                @if($errors->has('gps'))
                    <em class="invalid-feedback">
                        {{ $errors->first('gps') }}
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