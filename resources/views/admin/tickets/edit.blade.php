@extends('layouts.admin')
@section('content')


@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.ticket.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.tickets.update', [$ticket->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.ticket.fields.code') }}*</label>
                <input  type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($ticket) ? $ticket->code : '') }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.ticket.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($ticket) ? $ticket->title : '') }}" required>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.ticket.fields.description') }}*</label>
                <textarea type="text" id="description" name="description" class="form-control" value=""> {{ old('description', isset($ticket) ? $ticket->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('customer') ? 'has-error' : '' }}">
                <label for="customer">{{ trans('global.ticket.fields.customer_code') }}*</label>
                <input type="text" id="customer" name="customer_id" class="form-control" value="{{ old('customer', isset($ticket) ? $ticket->customer_id : '') }}" required>
                @if($errors->has('customer'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer') }}
                    </em>
                @endif
            </div>            
            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                <label for="category">{{ trans('global.ticket.fields.category') }}*</label>
                <select id="category" name="category_id" class="form-control" value="{{ old('category', isset($ticket) ? $ticket->category : '') }}">
                    <option value="">--Pilih category--</option>
                    @foreach ($categories as $key=>$category )
                        <option value="{{$category->id}}" {{$category->id == $ticket->category_id ? 'selected' : ''}} >{{$category->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('category'))
                    <em class="invalid-feedback">
                        {{ $errors->first('category') }}
                    </em>
                @endif
            </div>


            @can('ticket_delete')
            <label for="" style="color :red">note : pastikan status yang diinputkan benar, 
                karena bisa menyebabkan error/bug pada sistem(lebih baik diinput setelah semua tindakan selesai).
                 dapat dikosongkan jika tidak merubah status</label>
            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                <label for="category">Status*</label>
                <select id="status" name="statusupdate"  class="form-control" value="{{ old('category', isset($ticket) ? $ticket->category : '') }}">
                    <option value="">== Semua Status ==</option>
                    <option value="pending">Pending</option>
                <option value="active">Active</option>
                <option value="close">Close</option>
                </select>
                @if($errors->has('category'))
                    <em class="invalid-feedback">
                        {{ $errors->first('category') }}
                    </em>
                @endif
            </div>
                            
            @endcan

            <div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
                <label for="dapertement">{{ trans('global.action.fields.dapertement') }}*</label>
                <select id="dapertement" name="dapertement_id" class="form-control">
                    <option value="">--Pilih dapertement--</option>
                    @foreach ($dapertements as $dapertement )
                        <option value="{{$dapertement->id}}" {{$dapertement->id == $ticket->dapertement_id ? 'selected' : ''}} >{{$dapertement->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('dapertement'))
                    <em class="invalid-feedback">
                        {{ $errors->first('dapertement') }}
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