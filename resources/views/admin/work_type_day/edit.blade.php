@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.work_type_day.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.work_type_day.update", [$work_type_day->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                <label for="time">{{ trans('global.work_type_day.fields.time') }}*</label>
                <input type="time" id="time" name="time" class="form-control" value="{{ old('time', isset($work_type_day) ? $work_type_day->time : '') }}" required>
                @if($errors->has('time'))
                    <em class="invalid-feedback">
                        {{ $errors->first('time') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.work_type_day.fields.time_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('duration') ? 'has-error' : '' }}">
                <label for="duration">{{ trans('global.work_type_day.fields.duration') }}*</label>
                <input type="numer" id="duration" name="duration" class="form-control" value="{{ old('duration', isset($work_type_day) ? $work_type_day->duration : '') }}" required>
                @if($errors->has('duration'))
                    <em class="invalid-feedback">
                        {{ $errors->first('duration') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.work_type_day.fields.duration_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('duration_exp') ? 'has-error' : '' }}">
                <label for="duration_exp">{{ trans('global.work_type_day.fields.duration_exp') }}*</label>
                <input type="numer" id="duration_exp" name="duration_exp" class="form-control" value="{{ old('duration_exp', isset($work_type_day) ? $work_type_day->duration_exp : '') }}" required>
                @if($errors->has('duration_exp'))
                    <em class="invalid-feedback">
                        {{ $errors->first('duration_exp') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.work_type_day.fields.duration_exp_helper') }}
                </p>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection