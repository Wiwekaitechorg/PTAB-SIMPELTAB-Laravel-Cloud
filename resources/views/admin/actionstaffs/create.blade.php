@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.action_staff.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" value={{$actionId}} name="action_id">
            <div class="form-group {{ $errors->has('staff') ? 'has-error' : '' }}">
                <label for="staff">{{ trans('global.action.fields.staff') }}*
                    <span class="btn btn-info btn-xs select-all">Select all</span>
                    <span class="btn btn-info btn-xs deselect-all">Deselect all</span></label>
                <select name="staff[]" id="staff" class="form-control select2" multiple="multiple">
                    <option value="" >===Pilih Staff===</option>
                    @foreach ($staffs as $staff)
                            <option value="{{$staff->id}}"
                                @foreach ($action_staffs->staff as $action_staff )
                                    {{$staff->id == $action_staff->id ? 'selected' : ''}} 
                                @endforeach
                                >{{$staff->name}}</option> 
                    @endforeach
                </select>
                @if($errors->has('staff'))
                    <em class="invalid-feedback">
                        {{ $errors->first('staff') }}
                    </em>
                @endif
                <!-- <p class="helper-block">
                    {{ trans('global.action.fields.staff') }}
                </p> -->
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection