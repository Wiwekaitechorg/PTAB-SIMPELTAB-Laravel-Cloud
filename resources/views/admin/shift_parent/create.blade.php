@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.shift_parent.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.shift_parent.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.shift_parent.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($code) ? $code : '') }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.shift_parent.fields.code_helper') }}
                </p>
            </div> --}}

            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.shift_parent.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($shift_parent) ? $shift_parent->title : '') }}" required>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.shift_parent.fields.title_helper') }}
                </p>
            </div>

            {{-- <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($shift_parent) ? $shift_parent->description : '') }}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                <label for="type">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="type" name="type" class="form-control" value="{{ old('type', isset($user) ? $user->type : '') }}">
                    <option value="">--Pilih Tipe--</option>
                    <option value="reguler">reguler</option>
                    <option value="shift">shift</option>     
                </select>
                @if($errors->has('type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </em>
                @endif
            </div> --}}


            <div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
                <label for="dapertement">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="dapertement_id" name="dapertement_id" class="form-control" value="{{ old('dapertement', isset($user) ? $user->dapertement : '') }}">
                    <option value="">--Pilih Tipe--</option>
                    @foreach ($dapertements as $dapertement )
                    <option value="{{ $dapertement->id }}">{{ $dapertement->name }}</option>
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
                </select>
                @if($errors->has('subdapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('subdapertement_id') }}
                    </em>
                @endif
            </div>


            <div class="form-group {{ $errors->has('work_unit') ? 'has-error' : '' }}">
                <label for="work_unit">{{ trans('global.staff.fields.work_unit') }}*</label>
                <select id="work_unit" name="work_unit_id" class="form-control" value="{{ old('work_unit', isset($user) ? $user->work_unit : '') }}">
                    <option value="">--Pilih Tipe--</option>
                    @foreach ($work_units as $work_unit )
                    <option value="{{ $work_unit->id }}">{{ $work_unit->name }}</option>
                    @endforeach
 
                </select>
                @if($errors->has('work_unit'))
                    <em class="invalid-feedback">
                        {{ $errors->first('work_unit') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('job') ? 'has-error' : '' }}">
                <label for="job">{{ trans('global.staff.fields.job') }}*</label>
                <select id="job" name="job_id" class="form-control" value="{{ old('job', isset($user) ? $user->job : '') }}">
                    <option value="">--Pilih Tipe--</option>
                    @foreach ($jobs as $job )
                    <option value="{{ $job->id }}">{{ $job->name }}</option>
                    @endforeach
 
                </select>
                @if($errors->has('job_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('job') }}
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
{{-- <script type="text/javascript">
 $(function(){
  $(".datepicker").datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true,
      todayHighlight: true,
  });
 });
</script> --}}