@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.dapertement.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.dapertements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.dapertement.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($dapertement) ? $dapertement->code : $code) }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.dapertement.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($dapertement) ? $dapertement->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.dapertement.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" value="{{ old('description', isset($dapertement) ? $dapertement->description : '') }}" ></textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>
            <div>
                <div class="form-group {{ $errors->has('director') ? 'has-error' : '' }}">
                    <label for="director_id">{{ trans('global.staff.fields.director') }}*</label>
                    <select id="director_id" name="director_id" class="form-control" value="{{ old('director', isset($customer) ? $customer->director : '') }}">
                        <option value="">--Pilih director--</option>
                        @foreach ($directors as $key=>$director )
                            <option value="{{$director->id}}" >{{$director->name}}</option>
                        @endforeach
                    </select>
                    @if($errors->has('director'))
                        <em class="invalid-feedback">
                            {{ $errors->first('director') }}
                        </em>
                    @endif
                </div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection