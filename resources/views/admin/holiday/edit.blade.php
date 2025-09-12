@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.holiday.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.holiday.update", $holiday->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.holiday.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($holiday) ? $holiday->title : '') }}" required>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.holiday.fields.title_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                <label for="date">{{ trans('global.holiday.fields.date') }}*</label>
                <input type="date" id="date" name="date" class="form-control" value="{{ old('date', isset($holiday) ? $holiday->date : '') }}" required>
                @if($errors->has('date'))
                    <em class="invalid-feedback">
                        {{ $errors->first('date') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.holiday.fields.date_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($holiday) ? $holiday->description : '') }}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
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