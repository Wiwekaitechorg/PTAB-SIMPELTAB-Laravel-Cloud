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
        <form action="{{ route('admin.complains.ticketStore') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.ticket.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($ticket) ? $ticket->code : $code) }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div> --}}
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.ticket.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($complain) ? $complain->title : '') }}" required>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.ticket.fields.description') }}*</label>
                <textarea type="text" id="description" name="description" class="form-control" value=""> {{ old('description', isset($complain) ? $complain->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                <label for="category">{{ trans('global.ticket.fields.category') }}*</label>
                <select required id="category" name="category_id" class="form-control" value="{{ old('category', isset($customer) ? $customer->category : '') }}">
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
            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                <label for="address">{{ trans('global.ticket.fields.address') }}*</label>
                <textarea type="text" id="address" name="address" class="form-control" value=""> {{ old('address', isset($complain) ? $complain->address : '') }}</textarea>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
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

                @foreach (json_decode($complain->image) as $key => $img)
                    <div class="existing-image mb-3" id="image-container-{{ $key }}">
                        <img src={{url('/')."$img"}} class="preview-img mb-2" style="max-width: 200px; display: block;">
                        
                        <input type="checkbox" 
                            name="delete_images[]" 
                            value="{{ $img }}" 
                            id="delete-{{ $key }}" 
                            style="display: none;">

                        <!-- <label for="delete-{{ $key }}" class="btn btn-danger">Remove</label> -->
                    </div>
                @endforeach

                <div id="image-container">
                    
                </div>
                <!-- <div class="control-group input-group" style="margin-top:10px">
                    <button class="btn btn-success" type="button" id="add-image"><i class="glyphicon glyphicon-plus"></i>Add Another Image</button>
                </div>                  -->
                
            </div>

            <div class="form-group {{ $errors->has('video') ? 'has-error' : '' }}">
                <label for="video">{{ trans('global.complain.fields.video') }}*</label>

                <div id="video-upload-container">
                    

                    <div id="video-upload-preview" class="mt-2">
                        @if ($complain->video)
                            <video controls style="max-width: 300px; display: block;" class="preview-video mb-2">
                                <source src="{{ url('/').'' . $complain->video }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @endif
                    </div>
                </div>
                
            </div>

            <input type="hidden" value='pending' name='status'> 
            <input type="hidden" value='{{$complain->id}}' name='complain_id'>
            <input type="hidden" value='{{$customer_id}}' name='customer_id'>            
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