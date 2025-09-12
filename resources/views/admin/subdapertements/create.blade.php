@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.subdapertement.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.subdapertements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.subdapertement.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($subdapertement) ? $subdapertement->code : $code) }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
                <label for="dapertement">{{ trans('global.ticket.fields.dapertement') }}*</label>
                <select id="dapertement" name="dapertement_id" class="form-control" value="{{ old('dapertement', isset($subdapertement) ? $subdapertement->dapertement_id : '') }}">
                    <option value="">--Pilih Kategori--</option>
                    @foreach ($dapertements as $dapertement )
                        <option value="{{$dapertement->id}}" >{{$dapertement->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('dapertement'))
                    <em class="invalid-feedback">
                        {{ $errors->first('dapertement') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.subdapertement.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($subdapertement) ? $subdapertement->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.subdapertement.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" value="{{ old('description', isset($subdapertement) ? $subdapertement->description : '') }}" ></textArea>
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