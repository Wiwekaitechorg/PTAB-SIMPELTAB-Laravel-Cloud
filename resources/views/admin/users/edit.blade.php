@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.user.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.users.update", [$user->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.user.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($user) ? $user->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.user.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">{{ trans('global.user.fields.email') }}*</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', isset($user) ? $user->email : '') }}">
                @if($errors->has('email'))
                    <em class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.user.fields.email_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                <label for="phone">{{ trans('global.user.fields.phone') }}*</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', isset($user) ? $user->phone : '') }}">
                @if($errors->has('phone'))
                    <em class="invalid-feedback">
                        {{ $errors->first('phone') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password">{{ trans('global.user.fields.password') }}</label>
                <input type="password" id="password" name="password" class="form-control">
                @if($errors->has('password'))
                    <em class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.user.fields.password_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
                <label for="dapertement_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="dapertement_id" name="dapertement_id" class="form-control" value="{{ old('dapertement', isset($user) ? $user->dapertement_id : '') }}">
                    <option value="">--Pilih Dapertement--</option>
                    @foreach ($dapertements as $key=>$dapertement )
                        <option value="{{$dapertement->id}}" {{$dapertement->id == $user->dapertement_id ? 'selected' : ''}} >{{$dapertement->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('dapertement'))
                    <em class="invalid-feedback">
                        {{ $errors->first('dapertement') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('subdapertement_id') ? 'has-error' : '' }}">
                <label for="subdapertement_id">{{ trans('global.staff.fields.subdapertement') }}*</label>
                <select id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($user) ? $user->subdapertement_id : '') }}">
                    <option value="0">--Pilih Sub Depertement--</option>  
                    @foreach ($subdapertements as $key=>$subdapertement )
                        <option value="{{$subdapertement->id}}" {{$subdapertement->id == $user->subdapertement_id ? 'selected' : ''}} >{{$subdapertement->name}}</option>
                    @endforeach                  
                </select>
                @if($errors->has('subdapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('subdapertement_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                <label for="staff_id">{{ trans('global.staff.fields.name') }}*</label>
                <select id="staff_id" name="staff_id" class="form-control" value="{{ old('staff_id', isset($user) ? $user->staff_id : '') }}">
                    <option value="0">--Pilih Staff--</option> 
                    @foreach ($staffs as $key=>$staff )
                        <option value="{{$staff->id}}" {{$staff->id == $user->staff_id ? 'selected' : ''}} >{{$staff->name}}</option>
                    @endforeach                   
                </select>
                @if($errors->has('staff_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('staff_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('roles') ? 'has-error' : '' }}">
                <label for="roles">{{ trans('global.user.fields.roles') }}*
                    <span class="btn btn-info btn-xs select-all">Select all</span>
                    <span class="btn btn-info btn-xs deselect-all">Deselect all</span></label>
                <select name="roles[]" id="roles" class="form-control select2" multiple="multiple">
                    @foreach($roles as $id => $roles)
                        <option value="{{ $id }}" {{ (in_array($id, old('roles', [])) || isset($user) && $user->roles->contains($id)) ? 'selected' : '' }}>
                            {{ $roles }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('roles'))
                    <em class="invalid-feedback">
                        {{ $errors->first('roles') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.user.fields.roles_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('login_date') ? 'has-error' : '' }}">
                <label for="login_date">Tanggal Login(Hapus untuk bisa login kembali)*</label>
                <input type="text" id="login_date" name="login_date" class="form-control" value="{{ old('login_date', isset($user) ? $user->login_date : '') }}">
                @if($errors->has('login_date'))
                    <em class="invalid-feedback">
                        {{-- {{ $errors->first('login_date') }} --}}
                    </em>
                @endif
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>
@section('scripts')
@parent
<script>
    $('#dapertement_id').change(function(){
    var dapertement_id = $(this).val();    
    if(dapertement_id){
        $.ajax({
           type:"GET",
           url:"{{ route('admin.staffs.subdepartment') }}?dapertement_id="+dapertement_id,
           dataType: 'JSON',
           success:function(res){               
            if(res){
                $("#subdapertement_id").empty();
                $("#subdapertement_id").append('<option value="0">---Pilih Sub Depertement---</option>');
                $.each(res,function(id,name){
                    $("#subdapertement_id").append('<option value="'+id+'">'+name+'</option>');
                });
            }else{
               $("#subdapertement_id").empty();
            }
           }
        });
    }else{
        $("#subdapertement_id").empty();
    }      
   });

   $('#subdapertement_id').change(function(){
    var subdapertement_id = $(this).val();    
    if(subdapertement_id){
        $.ajax({
           type:"GET",
           url:"{{ route('admin.staffs.staff') }}?subdapertement_id="+subdapertement_id,
           dataType: 'JSON',
           success:function(res){               
            if(res){
                $("#staff_id").empty();
                $("#staff_id").append('<option value="0">---Pilih Staff---</option>');
                $.each(res,function(id,name){
                    $("#staff_id").append('<option value="'+id+'">'+name+'</option>');
                });
            }else{
               $("#staff_id").empty();
            }
           }
        });
    }else{
        $("#staff_id").empty();
    }      
   });
</script>
@endsection
@endsection