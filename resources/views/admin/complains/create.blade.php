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
        {{ trans('global.create') }} {{ trans('global.complain.title_singular') }}
    </div>


    <div class="card-body">
        <form action="{{ route('admin.complains.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.complain.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($complain) ? $complain->code : $code) }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div> --}}
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.complain.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($complain) ? $complain->title : '') }}" required>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.complain.fields.description') }}*</label>
                <textarea type="text" id="description" name="description" class="form-control" value="" required> {{ old('description', isset($complain) ? $complain->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('area') ? 'has-error' : '' }}">
                <label for="area">{{ trans('global.complain.fields.area') }}*</label>
                <select id="area" name="area" class="form-control" value="{{ old('area', isset($complain) ? $complain->area : '') }}" required>
                    <option value="">--Pilih Area--</option>
                    @foreach ($areas as $area )
                        <option value="{{$area->id}}" >{{$area->NamaWilayah}}</option>
                    @endforeach
                </select>
                @if($errors->has('area'))
                    <em class="invalid-feedback">
                        {{ $errors->first('area') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('complain_status_id') ? 'has-error' : '' }}">
                <label for="complain_status_id">{{ trans('global.complain.fields.complain_status_id') }}*</label>
                <select id="complain_status_id" name="complain_status_id" class="form-control" value="{{ old('complain_status_id', isset($complain) ? $complain->complain_status_id : '') }}" required>
                    <option value="">--Pilih Status--</option>
                    @foreach ($complainstatus as $status )
                        <option value="{{$status->id}}" >{{$status->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('complain_status_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('complain_status_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                <label for="address">{{ trans('global.complain.fields.address') }}*</label>
                <textarea type="text" id="address" name="address" class="form-control" value="" required> {{ old('address', isset($complain) ? $complain->address : '') }}</textarea>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
                    </em>
                @endif
            </div>
           
            <div class="form-group {{ $errors->has('customer') ? 'has-error' : '' }}">
                <label for="customer">{{ trans('global.complain.fields.customer_code') }}*</label>
                <input type="text" id="customer" name="customer_id" class="form-control" value="{{ old('customer', isset($complain) ? $complain->customer_id : '') }}">
                @if($errors->has('customer'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('customer_name') ? 'has-error' : '' }}">
                <label for="customer_name">{{ trans('global.complain.fields.customer_name') }}*</label>
                <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name', isset($complain) ? $complain->customer_name : '') }}">
                @if($errors->has('customer_name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer_name') }}
                    </em>
                @endif
            </div>
            
            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">{{ trans('global.complain.fields.image') }}*</label>
                {{-- <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($complain) ? $complain->image : '') }}">
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
                <label for="video">{{ trans('global.complain.fields.video') }}*</label>
                <input type="file" id="video" accept="video/*" name="video" class="form-control" value="{{ old('video', isset($complain) ? $complain->video : '') }}">
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