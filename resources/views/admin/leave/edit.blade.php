@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.leave.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.leave.update", $requests->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('put')
            <div class="form-group {{ $errors->has('user_id') ? 'has-error' : '' }}">
                <label for="user_id">user*</label>
                <select id="user_id" name="user_id" class="form-control" value="{{ old('user_id', isset($shift_user) ? $shift_user->user_id : '') }}">
                    <option value="">--Pilih user--</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
           
                </select>
                @if($errors->has('user_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('user_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                <label for="date">{{ trans('global.leave.fields.date') }}*</label>
                <input type="date" id="date" name="date" class="form-control" value="{{ old('date', isset($leave) ? $leave->date : '') }}" required>
                @if($errors->has('date'))
                    <em class="invalid-feedback">
                        {{ $errors->first('date') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.leave.fields.date_helper') }}
                </p>
            </div>
            
            <div class="form-group {{ $errors->has('end') ? 'has-error' : '' }}">
                <label for="end">{{ trans('global.leave.fields.end') }}*</label>
                <input type="date" id="end" name="end" class="form-control" value="{{ old('end', isset($leave) ? $leave->end : '') }}" required>
                @if($errors->has('end'))
                    <em class="invalid-feedback">
                        {{ $errors->first('end') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.leave.fields.end_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($leave) ? $leave->description : '') }}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>


            <div class="form-group {{ $errors->has('file') ? 'has-error' : '' }}">
                <label for="file">{{ trans('global.leave.fields.file') }}*</label>
                <input type="file" id="file" name="imageP" class="form-control" value="{{ old('file', isset($leave) ? $leave->file : '') }}" required>
                @if($errors->has('file'))
                    <em class="invalid-feedback">
                        {{ $errors->first('file') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.leave.fields.file_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('file') ? 'has-error' : '' }}">
                <label for="file">{{ trans('global.leave.fields.file') }}*</label>
                <input type="file" id="file" name="imagePng" class="form-control" value="{{ old('file', isset($leave) ? $leave->file : '') }}" required>
                @if($errors->has('file'))
                    <em class="invalid-feedback">
                        {{ $errors->first('file') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.leave.fields.file_helper') }}
                </p>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection