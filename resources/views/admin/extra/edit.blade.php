@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.extra.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.extra.update", $requests->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('user_id') ? 'has-error' : '' }}">
                <label for="user_id">user*</label>
                <select id="user_id" name="user_id" class="form-control" value="{{ old('user_id', isset($shift_user) ? $shift_user->user_id : '') }}">
                    <option value="">--Pilih user--</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}" @if ($requests && $requests->user_id==$user->id) selected @endif>{{ $user->name }}</option>
                    @endforeach
           
                </select>
                @if($errors->has('user_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('user_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                <label for="date">{{ trans('global.extra.fields.date') }}*</label>
                <input type="date" id="date" name="date" class="form-control" value="{{ old('date', isset($requests) ? $requests->date : '') }}" required>
                @if($errors->has('date'))
                    <em class="invalid-feedback">
                        {{ $errors->first('date') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.extra.fields.date_helper') }}
                </p>
            </div>

            @if ($type == "extraOut")

            <div class="form-group {{ $errors->has('end') ? 'has-error' : '' }}">
                <label for="end">{{ trans('global.extra.fields.end') }}*</label>
                <input type="date" id="end" name="end" class="form-control" value="{{ old('end', isset($requests) ? $requests->end : '') }}" required>
                @if($errors->has('end'))
                    <em class="invalid-feedback">
                        {{ $errors->first('end') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.extra.fields.end_helper') }}
                </p>
            </div>
                            
            @endif

            @if ($type == "extraIn")
            <div class="form-group {{ $errors->has('start') ? 'has-error' : '' }}">
                <label for="start">{{ trans('global.extra.fields.start') }}*</label>
                <input type="time" id="start" name="start" class="form-control" value="{{ old('start', isset($requests) ? $requests->start : '') }}" required>
                @if($errors->has('start'))
                    <em class="invalid-feedback">
                        {{ $errors->first('start') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.extra.fields.start_helper') }}
                </p>
            </div>
                            
            @endif

            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($requests) ? $requests->description : '') }}</textArea>
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