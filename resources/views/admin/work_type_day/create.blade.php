@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.work_type.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.work_type_day.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.work_type.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($code) ? $code : '') }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.work_type.fields.code_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.work_type.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($work_type) ? $work_type->title : '') }}" required>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.work_type.fields.name_helper') }}
                </p>
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($work_type) ? $work_type->description : '') }}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div> --}}
            <input type="hidden" name="work_type_id" value="{{ $work_type_id }}">

            <div class="form-group {{ $errors->has('day_id') ? 'has-error' : '' }}">
                <label for="day_id">{{ trans('global.work_type_day.fields.day') }}*</label>
                <select id="day_id" name="day_id" class="form-control" value="{{ old('day_id', isset($work_type_day) ? $work_type_day->day_id : '') }}">
                    <option value="">Pilih Hari</option>
                    @foreach ($days as $day)
                    <option value="{{ $day->id }}">{{ $day->name }}</option>                        
                    @endforeach
     
                </select>
                @if($errors->has('day_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('day_id') }}
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
