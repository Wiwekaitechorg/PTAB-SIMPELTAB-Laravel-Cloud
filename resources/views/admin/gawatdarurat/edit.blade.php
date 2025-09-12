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
        <form action="{{ route('admin.customers.update', [$customer->id]) }}" method="POST" enctype="multipart/form-data">
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
            <div class="form-group {{ $errors->has('gender') ? 'has-error' : '' }}">
                <label for="gender">{{ trans('global.customer.fields.gender') }}*</label>
                <select id="gender" name="gender" class="form-control" value="{{ old('gender', isset($customer) ? $customer->gender : '') }}" readonly>
                    <option value="male" {{$customer->gender =='male' ? 'selected' : ''}} >Laki-Laki</option>
                    <option value="female" {{$customer->gender =='female' ? 'selected' : ''}}>Perempuan</option>
                </select>
                @if($errors->has('gender'))
                    <em class="invalid-feedback">
                        {{ $errors->first('gender') }}
                    </em>
                @endif
                <input type="hidden" name="type" value="{{$customer->type}}">
            </div>
            
            {{-- <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                <label for="address">{{ trans('global.customer.fields.address') }}*</label>
                <input type="text" id="address" name="address" class="form-control" value="{{ old('address', isset($customer) ? $customer->address : '') }}" readonly>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
                    </em>
                @endif
            </div> --}}
            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">{{ trans('global.customer.fields.email') }}*</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', isset($customer) ? $customer->email : '') }}">
                @if($errors->has('email'))
                    <em class="invalid-feedback">
                        {{ $errors->first('email') }}
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

            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password">{{ trans('global.customer.fields.password') }}</label>
                <input type="password" id="password" name="password" class="form-control" value="">
                @if($errors->has('password'))
                    <em class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('repassword') ? 'has-error' : '' }}">
                <label for="repassword">Re- {{ trans('global.customer.fields.password') }}</label>
                <input type="password" id="repassword" name="repassword" class="form-control" value="">
                @if($errors->has('repassword'))
                    <em class="invalid-feedback">
                        {{ $errors->first('repassword') }}
                    </em>
                @endif
            </div>

             <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                <label for="address">{{ trans('global.customer.fields.address') }}*</label>
                <textArea id="address" name="address" class="form-control"  readonly>{{ old('address', isset($customer) ? $customer->address : '') }}</textArea>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
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