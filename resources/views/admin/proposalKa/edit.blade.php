@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} Ka
    </div>

    <div class="card-body">
        <form action="{{ route("admin.proposalKa.update", [$proposalKa->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('user_id') ? 'has-error' : '' }}">
                <label for="user_id">user*</label>
                <select id="user_id" name="user_id" class="form-control" value="{{ old('user_id', isset($absence) ? $absence->user_id : '') }}">
                    <option value="">--user--</option>
                    @foreach ($users as $key=>$user )
                        <option value="{{$user->id}}"  {{ $user->id === $proposalKa->user_id ? "selected" : "" }} >{{$user->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('user_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('user_id') }}
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
<script type="text/javascript">
    $(document).ready(function() {
        $('#user_id').select2({
         placeholder: 'Pilih user',
         allowClear: true
        });
    });
   </script>
   @endsection