@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.problematicAbsence.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.problematicabsence.create") }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group {{ $errors->has('user_id') ? 'has-error' : '' }}">
                <label for="user_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="user_id" name="user_id" class="form-control" value="{{ old('user_id', isset($problematicAbsence) ? $problematicAbsence->user_id : '') }}">
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

            <div class="form-group {{ $errors->has('problematicAbsence_category_id') ? 'has-error' : '' }}">
                <label for="problematicAbsence_category_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="problematicAbsence_category_id" name="cc*" class="form-control" value="{{ old('problematicAbsence_category_id', isset($problematicAbsence) ? $problematicAbsence->problematicAbsence_category_id : '') }}">
                    <option value="">--kategori absen--</option>
                    @foreach ($problematicAbsenceCategories as $key=>$problematicAbsence_category_id )
                        <option value="{{$problematicAbsence_category_id->id}}">{{$problematicAbsence_category_id->title}}</option>
                    @endforeach
                </select>
                @if($errors->has('problematicAbsence_category_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('problematicAbsence_category_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.problematicAbsence.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($problematicAbsence) ? $problematicAbsence->register : '') }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.problematicAbsence.fields.code_helper') }}
                </p>
            </div>


            {{-- <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($problematicAbsence) ? $problematicAbsence->description : '') }}</textArea>
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