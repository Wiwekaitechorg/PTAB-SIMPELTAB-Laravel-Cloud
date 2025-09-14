@extends('layouts.admin')
@section('content')


@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.lock.title') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.locks.update', [$lock->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.lock.code') }}*</label>
                <input  type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($lock) ? $lock->code : '') }}" readonly>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>            

            <div class="form-group {{ $errors->has('customer_id') ? 'has-error' : '' }}">
                <label for="customer_id">{{ trans('global.lock.fields.noSbg') }}*</label>
                <input type="text" id="customer_id" name="customer_id" class="form-control" value="{{ old('customer_id', isset($lock) ? $lock->customer_id : '') }}"  readonly>
                @if($errors->has('customer_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('customer') ? 'has-error' : '' }}">
                <label for="customer">{{ trans('global.lock.fields.customer') }}*</label>
                <input type="text" id="customer" name="customer" class="form-control" value="{{ old('customer', isset($lock->customer) ? $lock->customer->namapelanggan : '') }}"  readonly>
                @if($errors->has('customer'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                <label for="address">{{ trans('global.customer.fields.address') }}*</label>
                <textarea type="text" id="address" name="address" class="form-control" value="" readonly> {{ old('address', isset($lock->customer) ? $lock->customer->alamat : '') }}</textarea>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
                    </em>
                @endif
            </div>  
            <div class="form-group {{ $errors->has('customer_name') ? 'has-error' : '' }}">
                <label for="customer_name">{{ trans('global.lock.fields.customer') }}*</label>
                <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name', isset($lock->customer) ? $lock->customer->namapelanggan : '') }}" readonly>
                @if($errors->has('customer_name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer_name') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                <label for="type">{{ trans('global.lock.fields.type') }}*</label>
                <select id="type" name="type" class="form-control" value="{{ old('type', isset($lock) ? $lock->type : '') }}" required>
                    <option value="">--Pilih type--</option>
                    @foreach ($types as $type )
                        <option value="{{$type->id}}" {{$type->id == $lock->type ? 'selected' : ''}} >{{$type->title}}</option>
                    @endforeach
                </select>
                @if($errors->has('type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.lock.fields.memo') }}*</label>
                <input type="text" id="memo" name="memo" class="form-control" value="{{ old('memo', isset($lock) ? $lock->memo : '') }}" required>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif
            </div>             

            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">Foto Bukti Laporan*</label>
                @if (!empty($lock->image) && json_decode($lock->image))
                @foreach (json_decode($lock->image) as $key => $img)
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
                @endif
                <div id="image-container">
                </div>
                <div class="control-group input-group" style="margin-top:10px">
                    <button class="btn btn-success" type="button" id="add-image"><i class="glyphicon glyphicon-plus"></i>Tambah Foto Bukti Laporan</button>
                </div>
            </div>

            <div class="form-group {{ $errors->has('image_tool') ? 'has-error' : '' }}">
                <label for="image_tool">Foto Alat*</label>
                @if (!empty($lock->image_tools) && json_decode($lock->image_tools))
                @foreach (json_decode($lock->image_tools) as $key => $img)
                    <div class="existing-image_tool mb-3" id="image_tool-container-{{ $key }}">
                        <img src={{url('/')."$img"}} class="preview-img mb-2" style="max-width: 200px; display: block;">
                        
                        <input type="checkbox" 
                            name="delete_image_tools[]" 
                            value="{{ $img }}" 
                            id="delete_tool-{{ $key }}" 
                            style="display: none;"
                            onchange="toggleImageTool(this, {{ $key }})">

                        <label for="delete_tool-{{ $key }}" class="btn btn-danger">Remove</label>
                    </div>
                @endforeach
                @endif
                <div id="image_tool-container">
                </div>
                <div class="control-group input-group" style="margin-top:10px">
                    <button class="btn btn-success" type="button" id="add-image_tool"><i class="glyphicon glyphicon-plus"></i>Tambah Foto Alat</button>
                </div>
            </div>

            <div class="form-group {{ $errors->has('image_prework') ? 'has-error' : '' }}">
                <label for="image_prework">Foto Pra Pengerjaan*</label>
                @if (!empty($lock->image_prework) && json_decode($lock->image_prework))
                @foreach (json_decode($lock->image_prework) as $key => $img)
                    <div class="existing-image_prework mb-3" id="image_prework-container-{{ $key }}">
                        <img src={{url('/')."$img"}} class="preview-img mb-2" style="max-width: 200px; display: block;">
                        
                        <input type="checkbox" 
                            name="delete_image_preworks[]" 
                            value="{{ $img }}" 
                            id="delete_prework-{{ $key }}" 
                            style="display: none;"
                            onchange="toggleImagePrework(this, {{ $key }})">

                        <label for="delete_prework-{{ $key }}" class="btn btn-danger">Remove</label>
                    </div>
                @endforeach
                @endif
                <div id="image_prework-container">
                </div>
                <div class="control-group input-group" style="margin-top:10px">
                    <button class="btn btn-success" type="button" id="add-image_prework"><i class="glyphicon glyphicon-plus"></i>Tambah Foto Pra Pengerjaan</button>
                </div>
            </div>

            <div class="form-group {{ $errors->has('image_work') ? 'has-error' : '' }}">
                <label for="image_work">Foto Pengerjaan*</label>
                @if (!empty($lock->image_work) && json_decode($lock->image_work))
                @foreach (json_decode($lock->image_work) as $key => $img)
                    <div class="existing-image_work mb-3" id="image_work-container-{{ $key }}">
                        <img src={{url('/')."$img"}} class="preview-img mb-2" style="max-width: 200px; display: block;">
                        
                        <input type="checkbox" 
                            name="delete_image_works[]" 
                            value="{{ $img }}" 
                            id="delete_work-{{ $key }}" 
                            style="display: none;"
                            onchange="toggleImageWork(this, {{ $key }})">

                        <label for="delete_work-{{ $key }}" class="btn btn-danger">Remove</label>
                    </div>
                @endforeach
                @endif
                <div id="image_work-container">
                </div>
                <div class="control-group input-group" style="margin-top:10px">
                    <button class="btn btn-success" type="button" id="add-image_work"><i class="glyphicon glyphicon-plus"></i>Tambah Foto Pengerjaan</button>
                </div>
            </div>

            <div class="form-group {{ $errors->has('image_done') ? 'has-error' : '' }}">
                <label for="image_done">Foto Selesai*</label>
                @if (!empty($lock->image_done) && json_decode($lock->image_done))
                @foreach (json_decode($lock->image_done) as $key => $img)
                    <div class="existing-image_done mb-3" id="image_done-container-{{ $key }}">
                        <img src={{url('/')."$img"}} class="preview-img mb-2" style="max-width: 200px; display: block;">
                        
                        <input type="checkbox" 
                            name="delete_image_dones[]" 
                            value="{{ $img }}" 
                            id="delete_done-{{ $key }}" 
                            style="display: none;"
                            onchange="toggleImageDone(this, {{ $key }})">

                        <label for="delete_done-{{ $key }}" class="btn btn-danger">Remove</label>
                    </div>
                @endforeach
                @endif
                <div id="image_done-container">
                </div>
                <div class="control-group input-group" style="margin-top:10px">
                    <button class="btn btn-success" type="button" id="add-image_done"><i class="glyphicon glyphicon-plus"></i>Tambah Foto Selesai</button>
                </div>
            </div>

            <div>
                <input type="hidden" id="id_hidden" name="id_hidden" value="{{ $lock->id }}">
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
        let previewCountWork = 0;
        let previewCountPrework = 0;
        let previewCountTool = 0;
        let previewCountDone = 0;
        $(document).ready(function () {
            //image
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

            //image_work
            $('#add-image_work').on('click', function () {
                previewCountWork++;
                let previewId = 'preview_work-' + previewCountWork;
                let $container = $('#image_work-container');

                let newInput = $(`
                    <div class="image_work-input">
                        <div class="control-group input-group" style="margin-top:10px">
                            <input type="file" name="image_work[]" class="form-control image_work-selector" data-preview="#${previewId}">                                                        
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
            $('#image_work-container').on('click', '.remove-button', function () {
                $(this).closest('.image_work-input').next('.preview-wrapper').remove();
                $(this).closest('.image_work-input').remove();
            });
            // Event delegation for image_work preview
            $('#image_work-container').on('change', '.image_work-selector', function () {
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

            //image_prework
            $('#add-image_prework').on('click', function () {
                previewCountPrework++;
                let previewId = 'preview_prework-' + previewCountPrework;
                let $container = $('#image_prework-container');

                let newInput = $(`
                    <div class="image_prework-input">
                        <div class="control-group input-group" style="margin-top:10px">
                            <input type="file" name="image_prework[]" class="form-control image_prework-selector" data-preview="#${previewId}">                                                        
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
            $('#image_prework-container').on('click', '.remove-button', function () {
                $(this).closest('.image_prework-input').next('.preview-wrapper').remove();
                $(this).closest('.image_prework-input').remove();
            });
            // Event delegation for image_prework preview
            $('#image_prework-container').on('change', '.image_prework-selector', function () {
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

            //image_tool
            $('#add-image_tool').on('click', function () {
                previewCountTool++;
                let previewId = 'preview_tool-' + previewCountTool;
                let $container = $('#image_tool-container');

                let newInput = $(`
                    <div class="image_tool-input">
                        <div class="control-group input-group" style="margin-top:10px">
                            <input type="file" name="image_tool[]" class="form-control image_tool-selector" data-preview="#${previewId}">                                                        
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
            $('#image_tool-container').on('click', '.remove-button', function () {
                $(this).closest('.image_tool-input').next('.preview-wrapper').remove();
                $(this).closest('.image_tool-input').remove();
            });
            // Event delegation for image_tool preview
            $('#image_tool-container').on('change', '.image_tool-selector', function () {
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

            //image_done
            $('#add-image_done').on('click', function () {
                previewCountDone++;
                let previewId = 'preview_done-' + previewCountDone;
                let $container = $('#image_done-container');

                let newInput = $(`
                    <div class="image_done-input">
                        <div class="control-group input-group" style="margin-top:10px">
                            <input type="file" name="image_done[]" class="form-control image_done-selector" data-preview="#${previewId}">                                                        
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
            $('#image_done-container').on('click', '.remove-button', function () {
                $(this).closest('.image_done-input').next('.preview-wrapper').remove();
                $(this).closest('.image_done-input').remove();
            });
            // Event delegation for image_done preview
            $('#image_done-container').on('change', '.image_done-selector', function () {
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

            function toggleImageWork(checkbox, key) {
                const $container = $('#image_work-container-' + key);
                console.log('$container',$container)
                const $img = $container.find('.preview-img');
                console.log('$img',$img)
                const $label = $container.find('label[for="delete_work-' + key + '"]');
                console.log('$label',$label)

                if (checkbox.checked) {
                    $img.hide();
                    $label.text('Cancel Remove').removeClass('btn-danger').addClass('btn-warning');
                } else {
                    $img.show();
                    $label.text('Remove').removeClass('btn-warning').addClass('btn-danger');
                }
            }
            function toggleImagePrework(checkbox, key) {
                const $container = $('#image_prework-container-' + key);
                const $img = $container.find('.preview-img');
                const $label = $container.find('label[for="delete_prework-' + key + '"]');

                if (checkbox.checked) {
                    $img.hide();
                    $label.text('Cancel Remove').removeClass('btn-danger').addClass('btn-warning');
                } else {
                    $img.show();
                    $label.text('Remove').removeClass('btn-warning').addClass('btn-danger');
                }
            }
            function toggleImageTool(checkbox, key) {
                const $container = $('#image_tool-container-' + key);
                const $img = $container.find('.preview-img');
                const $label = $container.find('label[for="delete_tool-' + key + '"]');

                if (checkbox.checked) {
                    $img.hide();
                    $label.text('Cancel Remove').removeClass('btn-danger').addClass('btn-warning');
                } else {
                    $img.show();
                    $label.text('Remove').removeClass('btn-warning').addClass('btn-danger');
                }
            }
            function toggleImageDone(checkbox, key) {
                const $container = $('#image_done-container-' + key);
                const $img = $container.find('.preview-img');
                const $label = $container.find('label[for="delete_done-' + key + '"]');

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