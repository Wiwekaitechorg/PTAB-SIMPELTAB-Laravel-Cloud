@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.category.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.category.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($category) ? $category->code : $code) }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.category.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($category) ? $category->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('category_group_id') ? 'has-error' : '' }}">
                <label for="category_group_id">{{ trans('global.category.fields.category_group_id') }}*</label>
                <select id="category_group_id" name="category_group_id" class="form-control" value="{{ old('category_group_id', isset($category) ? $category->category_group_id : '') }}">
                    <option value="">--Pilih Group--</option>
                    @foreach ($category_groups as $key=>$category_group )
                        <option value="{{$category_group->id}}">{{$category_group->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('category_group_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('category_group_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('category_type_id') ? 'has-error' : '' }}">
                <label for="category_type_id">{{ trans('global.category.fields.category_type_id') }}*</label>
                <select id="category_type_id" name="category_type_id" class="form-control" value="{{ old('category_type_id', isset($category) ? $category->category_type_id : '') }}">
                    <option value="">--Pilih Type--</option>
                    @foreach ($category_types as $key=>$category_type )
                        <option value="{{$category_type->id}}">{{$category_type->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('category_type_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('category_type_id') }}
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