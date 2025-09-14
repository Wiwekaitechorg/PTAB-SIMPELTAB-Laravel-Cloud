@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.actionWms.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.actionWms.update", [$actionWm->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                <label for="status">{{ trans('global.actionWms.fields.status') }}*</label>
                <select id="status" name="status" class="form-control" value="{{ old('status', isset($actionWm) ? $actionWm->status : '') }}" required>
                    <option value="">--Pilih Status--</option>
                        <option value="pending" @if ($actionWm && $actionWm->status === "pending") selected @endif >Pending</option>
                        <option value="active" @if ($actionWm && $actionWm->status === "active") selected @endif >Active</option>
                        <option value="close" @if ($actionWm && $actionWm->status === "close") selected @endif >Close</option>
                </select>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </em>
                @endif
            </div>


            <div class="form-group {{ $errors->has('noWM1') ? 'has-error' : '' }}">
                <label for="noWM1">{{ trans('global.actionWms.fields.noWM1') }}*</label>
                <input type="text" id="noWM1" name="noWM1" class="form-control" value="{{ old('noWM1', isset($actionWm) ? $actionWm->noWM1 : '') }}" required>
                @if($errors->has('noWM1'))
                    <em class="invalid-feedback">
                        {{ $errors->first('noWM1') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.actionWms.fields.noWM1_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('brandWM1') ? 'has-error' : '' }}">
                <label for="brandWM1">{{ trans('global.actionWms.fields.brandWM1') }}*</label>
                <input type="text" id="brandWM1" name="brandWM1" class="form-control" value="{{ old('brandWM1', isset($actionWm) ? $actionWm->brandWM1 : '') }}" required>
                @if($errors->has('brandWM1'))
                    <em class="invalid-feedback">
                        {{ $errors->first('brandWM1') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.actionWms.fields.brandWM1_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('standWM1') ? 'has-error' : '' }}">
                <label for="standWM1">{{ trans('global.actionWms.fields.standWM1') }}*</label>
                <input type="text" id="standWM1" name="standWM1" class="form-control" value="{{ old('standWM1', isset($actionWm) ? $actionWm->standWM1 : '') }}" required>
                @if($errors->has('standWM1'))
                    <em class="invalid-feedback">
                        {{ $errors->first('standWM1') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.actionWms.fields.standWM1_helper') }}
                </p>
            </div>


            <div class="form-group {{ $errors->has('noWM2') ? 'has-error' : '' }}">
                <label for="noWM2">{{ trans('global.actionWms.fields.noWM2') }}*</label>
                <input type="text" id="noWM2" name="noWM2" class="form-control" value="{{ old('noWM2', isset($actionWm) ? $actionWm->noWM2 : '') }}" required>
                @if($errors->has('noWM2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('noWM2') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.actionWms.fields.noWM2_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('brandWM2') ? 'has-error' : '' }}">
                <label for="brandWM2">{{ trans('global.actionWms.fields.brandWM2') }}*</label>
                <input type="text" id="brandWM2" name="brandWM2" class="form-control" value="{{ old('brandWM2', isset($actionWm) ? $actionWm->brandWM2 : '') }}" required>
                @if($errors->has('brandWM2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('brandWM2') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.actionWms.fields.brandWM2_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('standWM2') ? 'has-error' : '' }}">
                <label for="standWM2">{{ trans('global.actionWms.fields.standWM2') }}*</label>
                <input type="text" id="standWM2" name="standWM2" class="form-control" value="{{ old('standWM2', isset($actionWm) ? $actionWm->standWM2 : '') }}" required>
                @if($errors->has('standWM2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('standWM2') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.actionWms.fields.standWM2_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.action.fields.memo') }}*</label>
                <textArea id="memo" name="memo" class="form-control" required>{{ old('name', isset($actionWm) ? $actionWm->memo : '') }}</textArea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">Foto Pengerjaan</label>
                {{-- <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($ticket) ? $ticket->image : '') }}">
                @if($errors->has('image'))
                    <em class="invalid-feedback">
                        {{ $errors->first('image') }}
                    </em>
                @endif --}}
                <div class="input-group control-group increment" >
                    <input type="file" name="old_image[]" class="form-control">
                    <div class="input-group-btn"> 
                      {{-- <button class="btn btn-success" type="button"><i class="glyphicon glyphicon-plus"></i>Add</button> --}}
                    </div>
                  </div>
                  {{-- <div class="clone hide">
                    <div class="control-group input-group" style="margin-top:10px">
                      <input type="file" name="image[]" class="form-control">
                      <div class="input-group-btn"> 
                        <button class="btn btn-danger" type="button"><i class="glyphicon glyphicon-remove"></i> Remove</button>
                      </div>
                    </div>
                  </div> --}}
            </div>


            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">Foto Alat</label>
                {{-- <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($ticket) ? $ticket->image : '') }}">
                @if($errors->has('image'))
                    <em class="invalid-feedback">
                        {{ $errors->first('image') }}
                    </em>
                @endif --}}
                <div class="input-group control-group increment" >
                    <input type="file" name="new_image[]" class="form-control">
                    <div class="input-group-btn"> 
                      {{-- <button class="btn btn-success" type="button"><i class="glyphicon glyphicon-plus"></i>Add</button> --}}
                    </div>
                  </div>
                  {{-- <div class="clone hide">
                    <div class="control-group input-group" style="margin-top:10px">
                      <input type="file" name="image[]" class="form-control">
                      <div class="input-group-btn"> 
                        <button class="btn btn-danger" type="button"><i class="glyphicon glyphicon-remove"></i> Remove</button>
                      </div>
                    </div>
                  </div> --}}
            </div>


            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">Foto Selesai</label>
                {{-- <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($ticket) ? $ticket->image : '') }}">
                @if($errors->has('image'))
                    <em class="invalid-feedback">
                        {{ $errors->first('image') }}
                    </em>
                @endif --}}
                <div class="input-group control-group increment" >
                    <input type="file" name="image_done[]" class="form-control">
                    <div class="input-group-btn"> 
                      {{-- <button class="btn btn-success" type="button"><i class="glyphicon glyphicon-plus"></i>Add</button> --}}
                    </div>
                  </div>
                  {{-- <div class="clone hide">
                    <div class="control-group input-group" style="margin-top:10px">
                      <input type="file" name="image[]" class="form-control">
                      <div class="input-group-btn"> 
                        <button class="btn btn-danger" type="button"><i class="glyphicon glyphicon-remove"></i> Remove</button>
                      </div>
                    </div>
                  </div> --}}
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection