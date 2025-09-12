@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.schedule.title_singular') }}
    </div>

    <div class="card-body">
        {{-- {{ route("admin.schedule.update", [$schedule->id]) }} --}}
        <form action="{{ route("admin.schedule.update", [$schedule->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.schedule.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($schedule) ? $schedule->title : '') }}" required readonly>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
                <p class="helper-block">
                  
                </p>
            </div>

            <div class="form-group {{ $errors->has('value') ? 'has-error' : '' }}">
                <label for="value">{{ trans('global.schedule.fields.value') }}*</label>
                <input type="number" min = '0' max = '1' id="value" name="value" class="form-control" value="{{ old('value', isset($schedule) ? $schedule->value : '') }}" required>
                @if($errors->has('value'))
                    <em class="invalid-feedback">
                        {{ $errors->first('value') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.schedule.fields.value_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                <label for="time">{{ trans('global.schedule.fields.time') }}*</label>
                <input type="time" id="time" name="time" class="form-control" value="{{ old('time', isset($schedule) ? $schedule->time : '') }}" required>
                @if($errors->has('time'))
                    <em class="invalid-feedback">
                        {{ $errors->first('time') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.schedule.fields.time_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('start') ? 'has-error' : '' }}">
                <label for="start">{{ trans('global.schedule.fields.start') }}*</label>
                <input type="time" id="start" name="start" class="form-control" value="{{ old('start', isset($schedule) ? $schedule->start : '') }}" required>
                @if($errors->has('start'))
                    <em class="invalid-feedback">
                        {{ $errors->first('start') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.schedule.fields.start_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('end') ? 'has-error' : '' }}">
                <label for="end">{{ trans('global.schedule.fields.end') }}*</label>
                <input type="time" id="end" name="end" class="form-control" value="{{ old('end', isset($schedule) ? $schedule->end : '') }}" required>
                @if($errors->has('end'))
                    <em class="invalid-feedback">
                        {{ $errors->first('end') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.schedule.fields.end_helper') }}
                </p>
            </div>
     

            
            {{-- <div class="form-group {{ $errors->has('value') ? 'has-error' : '' }}">
                <label for="value">{{ trans('global.schedule.fields.value') }}*</label>
                <input type="text" id="value" name="value" class="form-control" value="{{ old('value', isset($schedule) ? $schedule->value : '') }}" required>
                @if($errors->has('value'))
                    <em class="invalid-feedback">
                        {{ $errors->first('value') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.schedule.fields.value_helper') }}
                </div> --}}
            

            {{-- <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($schedule) ? $schedule->description : '') }}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div> --}}

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection