@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.absence.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.absence.update", $absence->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="">
                <img src="{{ asset('') }}/{{ $absence->image }}" width="200"/>
            </div>
           
            <label for="image">Gambar Sebelumnya</label>
          

            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">{{ trans('global.absence.fields.image') }}*</label>
                <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($absence) ? $absence->image : '') }}" required>
                @if($errors->has('image'))
                    <em class="invalid-feedback">
                        {{ $errors->first('file') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.absence.fields.image_helper') }}
                </p>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection