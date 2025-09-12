@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.absence.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.absence.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.absence.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($code) ? $code : '') }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.absence.fields.code_helper') }}
                </p>
            </div> --}}

            <div class="form-group {{ $errors->has('user_id') ? 'has-error' : '' }}">
                <label for="user_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="user_id" name="user_id" class="form-control" value="{{ old('user_id', isset($absence) ? $absence->user_id : '') }}">
                    <option value="">--User--</option>
                    @foreach ($users as $key=>$user )
                        <option value="{{$user->id}}">{{$user->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('user_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('user_id') }}
                    </em>
                @endif
            </div>

            
            <div class="form-group {{ $errors->has('absence_category_id') ? 'has-error' : '' }}">
                <label for="absence_category_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="absence_category_id" name="absence_category_id" class="form-control" value="{{ old('absence_category_id', isset($absence) ? $absence->absence_category_id : '') }}">
                    <option value="">--kategori absen--</option>
                    <option value="in">Masuk</option>
                    <option value="break_in">Istirahat Mulai</option>
                    <option value="break_out">Istirahat Selesai</option>
                    <option value="out">Pulang</option>
                    @foreach ($absence_categories as $key=>$absence_category_id )
                        <option value="{{$absence_category_id->id}}">{{$absence_category_id->title}}</option>
                    @endforeach
            
                </select>
                @if($errors->has('absence_category_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('absence_category_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.absence.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($absence) ? $absence->register : '') }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.absence.fields.register_helper') }}
                </p>
            </div>


            {{-- <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($absence) ? $absence->description : '') }}</textArea>
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