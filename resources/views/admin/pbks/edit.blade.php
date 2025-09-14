@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.pbk.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.pbks.statusUpdate') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('Name') ? 'has-error' : '' }}">
                <label for="Name">No. SBG:</label>
                <input readonly type="text" id="Name" name="Name" class="form-control" value="{{ old('Name', isset($pbk) ? $pbk->Name : '') }}">
            </div>     
            <div class="form-group {{ $errors->has('Number') ? 'has-error' : '' }}">
                <label for="Number">No. SBG:</label>
                <input readonly type="text" id="Number" name="Number" class="form-control" value="{{ old('Number', isset($pbk) ? $pbk->Number : '') }}">
            </div>        
            <div class="form-group {{ $errors->has('Status') ? 'has-error' : '' }}">
                <label for="Status">{{ trans('global.pbk.fields.status') }}*</label>
                <select id="Status" name="Status" class="form-control" value="{{ old('Status', isset($pbk) ? $pbk->Status : '') }}">
                    <option value="0" {{$pbk->Status =='0' ? 'selected' : ''}} >Tidak Aktif</option>
                    <option value="1" {{$pbk->Status =='1' ? 'selected' : ''}}>Aktif</option>
                </select>
                @if($errors->has('Status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('Status') }}
                    </em>
                @endif
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.approve') }}">
            </div>
        </form>
    </div>
</div>

@endsection