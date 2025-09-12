@extends('layouts.admin')
@section('content')

<div class="card">
    @if($errors->any())
        <?php 
            echo "<script> alert('{$errors->first()}')</script>";
        ?>
    @endif
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.lock.title') }}
    </div>

    <div class="card-body">
    <form action="{{ route('admin.lock.lockstore') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="lock_id" value="{{$lock_id}}">
        <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.ticket.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
        </div>
        <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
            <label for="type">{{ trans('global.lock.type') }}*</label>
            <select id="type" name="type" class="form-control" >
                <option value="">--Pilih Type--</option>
                <option value="lock">Segel</option>
                <option value="unplug">Cabut</option>
                <option value="lock_resist">Hambatan Segel</option>
                <option value="unplug_resist">Hambatan Cabut</option>
            </select>
            @if($errors->has('category'))
                <em class="invalid-feedback">
                    {{ $errors->first('category') }}
                </em>
            @endif
        </div>
        <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
            <label for="image">{{ trans('global.ticket.fields.image') }}*</label>
            {{-- <input type="file" id="image" name="image" class="form-control">
            @if($errors->has('image'))
                <em class="invalid-feedback">
                    {{ $errors->first('image') }}
                </em>
            @endif --}}
            <div class="input-group control-group increment" >
                <input type="file" name="image[]" class="form-control">
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
        <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
            <label for="memo">{{ trans('global.action.fields.memo') }}*</label>
            <textArea id="memo" name="memo" class="form-control" ></textArea>
            @if($errors->has('memo'))
                <em class="invalid-feedback">
                    {{ $errors->first('memo') }}
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
            $(".btn-success").click(function(){ 
                var html = $(".clone").html();
                $(".increment").after(html);
            });
            $("body").on("click",".btn-danger",function(){ 
                $(this).parents(".control-group").remove();
            });
    </script>
@endsection