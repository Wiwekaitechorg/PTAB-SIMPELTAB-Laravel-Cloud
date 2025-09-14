@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.shift_group.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.shift_group.store") }}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name ="shift_parent_id" value="{{ $shift_parent_id }}">
            <input type="hidden" name ="queue" value="{{ $queue }}">

            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.shift_group.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($code) ? $code : '') }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.shift_group.fields.code_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.shift_group.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($shift_group) ? $shift_group->title : '') }}" required>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.shift_group.fields.title_helper') }}
                </p>
            </div>

            {{-- <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($shift_group) ? $shift_group->description : '') }}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('dapertement_id') ? 'has-error' : '' }}">
                <label for="dapertement_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="dapertement_id" name="dapertement_id" class="form-control" value="{{ old('dapertement_id', isset($user) ? $user->dapertement_id : '') }}">
                    <option value="">--Pilih Dapertement--</option>
                    @foreach ($departementlist as $key=>$dapertement )
                        <option value="{{$dapertement->id}}">{{$dapertement->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('dapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('dapertement_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('subdapertement_id') ? 'has-error' : '' }}">
                <label for="subdapertement_id">{{ trans('global.staff.fields.subdapertement') }}*</label>
                <select id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($user) ? $user->subdapertement_id : '') }}">
                    <option value="0">--Pilih Sub Depertement--</option>                    
                </select>
                @if($errors->has('subdapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('subdapertement_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('work_unit_id') ? 'has-error' : '' }}">
                <label for="work_unit_id">{{ trans('global.staff.fields.work_unit') }}*</label>
                <select id="work_unit_id" name="work_unit_id" class="form-control" value="{{ old('work_unit_id', isset($user) ? $user->work_unit_id : '') }}">
                    <option value="">--Pilih work_unit--</option>
                    @foreach ($work_units as $key=>$work_unit )
                        <option value="{{$work_unit->id}}">{{$work_unit->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('work_unit_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('work_unit_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('job_id') ? 'has-error' : '' }}">
                <label for="job_id">{{ trans('global.staff.fields.job') }}*</label>
                <select id="job_id" name="job_id" class="form-control" value="{{ old('job_id', isset($user) ? $user->job_id : '') }}">
                    <option value="">--Pilih job--</option>
                    @foreach ($jobs as $key=>$job )
                        <option value="{{$job->id}}">{{$job->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('job_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('job_id') }}
                    </em>
                @endif
            </div> --}}

            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                <label for="type">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="type" name="type_s" class="form-control" value="{{ old('type', isset($shift_group) ? $shift_group->type_s : '') }}">
                    <option value="">--Pilih type--</option>
                    <option value="P">P</option>
                    <option value="S">S</option>
                    <option value="M">M</option>
                </select>
                @if($errors->has('type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('type') }}
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

</script>
@endsection
<script type="text/javascript">
 $(function(){
  $(".datepicker").datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true,
      todayHighlight: true,
  });
 });
</script>