@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.duty.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.duty.update", $requests->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                <label for="staff_id">staff*</label>
                <select id="staff_id" name="staff_id" class="form-control" value="{{ old('staff_id', isset($shift_staff) ? $shift_staff->staff_id : '') }}">
                    <option value="">--Pilih staff--</option>
                    @foreach ($staffs as $staff)
                    <option value="{{ $staff->id }}" @if ($requests && $requests->staff_id==$staff->id) selected @endif>{{ $staff->name }}</option>
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
                <input type="date" id="start" name="start" class="form-control" value="{{ old('start', isset($requests) ? $requests->start : '') }}" required>
                @if($errors->has('start'))
                    <em class="invalid-feedback">
                        {{ $errors->first('start') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.duty.fields.start_helper') }}
                </p>
            </div>

            @if ($type == "duty")

            <div class="form-group {{ $errors->has('end') ? 'has-error' : '' }}">
                <label for="end">{{ trans('global.duty.fields.end') }}*</label>
                <input type="date" id="end" name="end" class="form-control" value="{{ old('end', isset($requests) ? $requests->end : '') }}" required>
                @if($errors->has('end'))
                    <em class="invalid-feedback">
                        {{ $errors->first('end') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.duty.fields.end_helper') }}
                </p>
            </div>
                            
            @endif

            @if ($type == "visit")
            <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                <label for="time">{{ trans('global.duty.fields.time') }}*</label>
                <input type="time" id="time" name="time" class="form-control" value="{{ old('time', isset($requests) ? $requests->time : '') }}" required>
                @if($errors->has('time'))
                    <em class="invalid-feedback">
                        {{ $errors->first('time') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.duty.fields.time_helper') }}
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