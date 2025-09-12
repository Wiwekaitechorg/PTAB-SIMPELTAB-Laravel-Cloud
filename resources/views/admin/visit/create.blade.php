@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.duty.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.duty.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                <label for="staff_id">staff*</label>
                <select id="staff_id" name="staff_id" class="form-control" value="{{ old('staff_id', isset($shift_staff) ? $shift_staff->staff_id : '') }}">
                    <option value="">--Pilih staff--</option>
                    @foreach ($staffs as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
           
                </select>
                @if($errors->has('staff_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('staff_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('start') ? 'has-error' : '' }}">
                <label for="start">{{ trans('global.duty.fields.start') }}*</label>
                <input type="date" id="start" name="start" class="form-control" value="{{ old('start', isset($duty) ? $duty->start : '') }}" required>
                @if($errors->has('start'))
                    <em class="invalid-feedback">
                        {{ $errors->first('start') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.duty.fields.start_helper') }}
                </p>
            </div>
            
            <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                <label for="time">{{ trans('global.duty.fields.time') }}*</label>
                <input type="time" id="time" name="time" class="form-control" value="{{ old('time', isset($duty) ? $duty->time : '') }}" required>
                @if($errors->has('time'))
                    <em class="invalid-feedback">
                        {{ $errors->first('time') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.duty.fields.time_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($duty) ? $duty->description : '') }}</textArea>
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