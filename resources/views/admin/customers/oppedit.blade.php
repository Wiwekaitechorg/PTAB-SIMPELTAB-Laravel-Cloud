@extends('layouts.admin')
@section('content')

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.customer.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.opp.update', [$customer->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.customer.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($customer) ? $customer->code : '') }}" readonly>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.customer.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($customer) ? $customer->name : '') }}" readonly>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                <label for="phone">{{ trans('global.customer.fields.phone') }}*</label>
                <input type="number" id="phone" name="phone" class="form-control" value="{{ old('phone', isset($customer) ? $customer->phone : '') }}">
                @if($errors->has('phone'))
                    <em class="invalid-feedback">
                        {{ $errors->first('phone') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('noktp') ? 'has-error' : '' }}">
                <label for="noktp">No. KTP*</label>
                <input type="number" id="noktp" name="noktp" class="form-control" value="{{ old('noktp', isset($customer) ? $customer->noktp : '') }}">
                @if($errors->has('noktp'))
                    <em class="invalid-feedback">
                        {{ $errors->first('noktp') }}
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