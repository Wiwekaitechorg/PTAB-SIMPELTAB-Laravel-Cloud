@extends('layouts.admin')
@section('content')

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
  

<div class="card">
    @if($errors->any())
    <!-- <h4>{{$errors->first()}}</h4> -->
        <?php 
            echo "<script> alert('{$errors->first()}')</script>";
        ?>
    @endif
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.ticket.title_singular') }}
    </div>


    <div class="card-body">
        <form action="{{ route('admin.tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.ticket.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($ticket) ? $ticket->code : $code) }}" required>
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
            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                <label for="category">{{ trans('global.ticket.fields.category') }}*</label>
                <select id="category" name="category_id" class="form-control" value="{{ old('category', isset($customer) ? $customer->category : '') }}">
                    <option value="">--Pilih Kategori--</option>
                    @foreach ($categories as $category )
                        <option value="{{$category->id}}" >{{$category->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('category'))
                    <em class="invalid-feedback">
                        {{ $errors->first('category') }}
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
            
            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">{{ trans('global.ticket.fields.image') }}*</label>
                {{-- <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($ticket) ? $ticket->image : '') }}">
                @if($errors->has('image'))
                    <em class="invalid-feedback">
                        {{ $errors->first('image') }}
                    </em>
                @endif --}}
                <div class="input-group control-group increment" >
                    <input type="file" name="image[]" class="form-control" required>
                    <div class="input-group-btn"> 
                      <button class="btn btn-success" type="button"><i class="glyphicon glyphicon-plus"></i>Add</button>
                    </div>
                  </div>
                  <div class="clone hide">
                    <div class="control-group input-group" style="margin-top:10px">
                      <input type="file" name="image[]" class="form-control">
                      <div class="input-group-btn"> 
                        <button class="btn btn-danger" type="button"><i class="glyphicon glyphicon-remove"></i> Remove</button>
                      </div>
                    </div>
                  </div>
            </div>
            <div class="form-group {{ $errors->has('video') ? 'has-error' : '' }}">
                <label for="video">{{ trans('global.ticket.fields.video') }}*</label>
                <input type="file" id="video" name="video" class="form-control" value="{{ old('video', isset($ticket) ? $ticket->video : '') }}">
                @if($errors->has('video'))
                    <em class="invalid-feedback">
                        {{ $errors->first('video') }}
                    </em>
                @endif
            </div>
            <input type="hidden" value='pending' name='status'>            
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
    @parent
    <script>
            $(".btn-success").click(function(){ 
                var html = $(".clone").html();
                $(".increment").after(html);
            });
            $("body").on("click",".btn-danger",function(){ 
                $(this).parents(".control-group").remove();
            });
    </script>
@endsection