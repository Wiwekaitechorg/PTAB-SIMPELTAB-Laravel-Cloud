@extends('layouts.admin')
@section('content')


@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.complain.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.complains.update', [$complain->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.complain.fields.code') }}*</label>
                <input  type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($complain) ? $complain->code : '') }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
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
                <textarea type="text" id="description" name="description" class="form-control" value=""> {{ old('description', isset($complain) ? $complain->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('area') ? 'has-error' : '' }}">
                <label for="area">{{ trans('global.complain.fields.area') }}*</label>
                <select id="area" name="area" class="form-control" value="{{ old('area', isset($complain) ? $complain->area : '') }}">
                    <option value="">--Pilih Area--</option>
                    @foreach ($areas as $area )
                        <option value="{{$area->id}}" {{$area->id == $complain->area ? 'selected' : ''}} >{{$area->NamaWilayah}}</option>
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
                        <option value="{{$status->id}}" {{$status->id == $complain->complain_status_id ? 'selected' : ''}}>{{$status->name}}</option>
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
                <textarea type="text" id="address" name="address" class="form-control" value=""> {{ old('address', isset($complain) ? $complain->address : '') }}</textarea>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('customer') ? 'has-error' : '' }}">
                <label for="customer">{{ trans('global.complain.fields.customer_code') }}*</label>
                <input type="text" id="customer" name="customer_id" class="form-control" value="{{ old('customer', isset($complain) ? $complain->customer_id : '') }}" required>
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

                @foreach (json_decode($complain->image) as $key => $img)
                    <div class="existing-image mb-3" id="image-container-{{ $key }}">
                        <img src={{url('/')."$img"}} class="preview-img mb-2" style="max-width: 200px; display: block;">
                        
                        <input type="checkbox" 
                            name="delete_images[]" 
                            value="{{ $img }}" 
                            id="delete-{{ $key }}" 
                            style="display: none;"
                            onchange="toggleImage(this, {{ $key }})">

                        <label for="delete-{{ $key }}" class="btn btn-danger">Remove</label>
                    </div>
                @endforeach

                <div id="image-container">
                    
                </div>
                <div class="control-group input-group" style="margin-top:10px">
                    <button class="btn btn-success" type="button" id="add-image"><i class="glyphicon glyphicon-plus"></i>Add Another Image</button>
                </div>                 
                
            </div>

            <div class="form-group {{ $errors->has('video') ? 'has-error' : '' }}">
                <label for="video">{{ trans('global.complain.fields.video') }}*</label>

                <div id="video-upload-container">
                    <input type="file" name="video" accept="video/*"
                        class="form-control-file" id="video-upload">

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

            <div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
                <label for="dapertement">{{ trans('global.action.fields.dapertement') }}*</label>
                <select id="dapertement" name="dapertement_id" class="form-control">
                    <option value="">--Pilih dapertement--</option>
                    @foreach ($dapertements as $dapertement )
                        <option value="{{$dapertement->id}}" {{$dapertement->id == $complain->dapertement_id ? 'selected' : ''}} >{{$dapertement->name}}</option>
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
@section('scripts')
    @parent
    <script>
        let previewCount = 0;
        $(document).ready(function () {
            $('#add-image').on('click', function () {
                previewCount++;
                let previewId = 'preview-' + previewCount;
                let $container = $('#image-container');

                let newInput = $(`
                    <div class="image-input">
                        <div class="control-group input-group" style="margin-top:10px">
                            <input type="file" name="image[]" class="form-control image-selector" data-preview="#${previewId}">                                                        
                            <div class="input-group-btn"> 
                                <button class="btn btn-danger remove-button" type="button"><i class="glyphicon glyphicon-remove"></i> Remove</button>
                            </div>                            
                        </div>                        
                    </div>
                    <div id="${previewId}" class="preview-wrapper mt-2" style="text-align: left;">
                        <img class="preview-img" style="display: none; max-width: 200px;">
                    </div>
                `);

                $container.append(newInput);
            });

            // Event delegation for remove buttons
            $('#image-container').on('click', '.remove-button', function () {
                $(this).closest('.image-input').next('.preview-wrapper').remove();
                $(this).closest('.image-input').remove();
            });

            // Event delegation for image preview
            $('#image-container').on('change', '.image-selector', function () {
                const file = this.files[0];
                const previewSelector = $(this).data('preview');
                const $preview = $(previewSelector).find('.preview-img');

                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $preview.attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $preview.attr('img.src', '').hide();
                }
            });

            // Preview uploaded video if not already exists
            $('#video-upload').on('change', function () {
                const file = this.files[0];
                const $previewWrapper = $('#video-upload-preview');
                $previewWrapper.empty(); // remove any existing preview

                if (file && file.type.startsWith('video/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const video = $(`
                            <video controls style="max-width: 300px; display: block;" class="preview-video mb-2">
                                <source src="${e.target.result}" type="${file.type}">
                                Your browser does not support the video tag.
                            </video>
                        `);
                        $previewWrapper.append(video);
                    };
                    reader.readAsDataURL(file);
                } else {
                    $previewWrapper.html('<p class="text-danger">Unsupported file type</p>');
                }
            });

        });

            function toggleImage(checkbox, key) {
                const $container = $('#image-container-' + key);
                const $img = $container.find('.preview-img');
                const $label = $container.find('label[for="delete-' + key + '"]');

                if (checkbox.checked) {
                    $img.hide();
                    $label.text('Cancel Remove').removeClass('btn-danger').addClass('btn-warning');
                } else {
                    $img.show();
                    $label.text('Remove').removeClass('btn-warning').addClass('btn-danger');
                }
            }
    </script>
@endsection