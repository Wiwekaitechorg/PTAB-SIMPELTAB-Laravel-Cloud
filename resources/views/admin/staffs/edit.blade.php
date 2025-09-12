@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.staff.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.staffs.update', [$staff->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.staff.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($staff) ? $staff->code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('NIK') ? 'has-error' : '' }}">
                <label for="NIK">{{ trans('global.staff.fields.NIK') }}*</label>
                <input type="number" id="NIK" name="NIK" class="form-control" value="{{ old('NIK', isset($staff) ? $staff->NIK : '') }}">
                @if($errors->has('NIK'))
                    <em class="invalid-feedback">
                        {{ $errors->first('NIK') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.staff.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($staff) ? $staff->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                <label for="phone">{{ trans('global.staff.fields.phone') }}*</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', isset($staff) ? $staff->phone : '') }}">
                @if($errors->has('phone'))
                    <em class="invalid-feedback">
                        {{ $errors->first('phone') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
                <label for="dapertement_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="dapertement_id" name="dapertement_id" class="form-control" value="{{ old('dapertement', isset($customer) ? $customer->dapertement : '') }}">
                    <option value="">--Pilih Dapertement--</option>
                    @foreach ($dapertements as $key=>$dapertement )
                        <option value="{{$dapertement->id}}" {{$dapertement->id == $staff->dapertement_id ? 'selected' : ''}} >{{$dapertement->name}}</option>
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
                <select id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($customer) ? $customer->subdapertement : '') }}">
                    <option value="">--Pilih Sub Depertement--</option>  
                    @foreach ($subdapertements as $key=>$subdapertement )
                        <option value="{{$subdapertement->id}}" {{$subdapertement->id == $staff->subdapertement_id ? 'selected' : ''}} >{{$subdapertement->name}}</option>
                    @endforeach                  
                </select>
                @if($errors->has('subdapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('subdapertement_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('work_unit') ? 'has-error' : '' }}">
                <label for="work_unit_id">{{ trans('global.staff.fields.work_unit') }}*</label>
                <select id="work_unit_id" name="work_unit_id" class="form-control" value="{{ old('work_unit', isset($customer) ? $customer->work_unit : '') }}" required>
                    <option value="">--Pilih work_unit--</option>
                    @foreach ($work_units as $key=>$work_unit )
                        <option value="{{$work_unit->id}}" {{$work_unit->id == $staff->work_unit_id ? 'selected' : ''}} >{{$work_unit->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('work_unit'))
                    <em class="invalid-feedback">
                        {{ $errors->first('work_unit') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('pbk') ? 'has-error' : '' }}">
                <label for="pbk">{{ trans('global.staff.fields.pbk') }}*</label>
                <select id="pbk" name="pbk" class="form-control" value="{{ old('pbk', isset($customer) ? $customer->pbk : '') }}">
                    <option value="">--Pilih pbk--</option>
                    @foreach ($pbks as $key=>$pbk )
                        <option value="{{$pbk->Name}}">{{$pbk->Name}}</option>
                    @endforeach
                </select>
                @if($errors->has('pbk'))
                    <em class="invalid-feedback">
                        {{ $errors->first('pbk') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('work_type_id') ? 'has-error' : '' }}">
                <label for="work_type_id">Work Type*</label>
                <select id="work_type_id" name="work_type_id" class="form-control" value="{{ old('work_type_id', isset($customer) ? $customer->work_unit : '') }}" required>
                    <option value="">--Pilih work_type--</option>
                    @foreach ($work_types as $key=>$work_type )
                        <option value="{{$work_type->id}}" {{$work_type->id == $staff->work_type_id ? 'selected' : ''}}>{{$work_type->title}}</option>
                    @endforeach
                </select>
                @if($errors->has('work_type_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('work_type_id') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('job_id') ? 'has-error' : '' }}">
                <label for="job_id">{{ trans('global.staff.fields.job') }}*</label>
                <select id="job_id" name="job_id" class="form-control" value="{{ old('job_id', isset($customer) ? $customer->work_unit : '') }}">
                    <option value="">--Pilih job--</option>
                    @foreach ($jobs as $key=>$job )
                        <option value="{{$job->id}}" {{$job->id == $staff->job_id ? 'selected' : ''}}>{{$job->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('job_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('job_id') }}
                    </em>
                @endif
            </div>

            {{-- <div class="form-group {{ $errors->has('fingerprint') ? 'has-error' : '' }}">
                <label for="fingerprint">Fingerprint*</label>
                <select id="fingerprint" name="fingerprint" class="form-control" value="{{ old('fingerprint', isset($customer) ? $customer->work_unit : '') }}">
                    <option value="">--Pilih Status--</option>
                        <option value="ON" {{$staff->fingerprint == "ON" ? 'selected' : ''}}>ON</option>
                        <option value="OFF" {{$staff->fingerprint == "OFF" ? 'selected' : ''}}>OFF</option>
                </select>
                @if($errors->has('fingerprint'))
                    <em class="invalid-feedback">
                        {{ $errors->first('fingerprint') }}
                    </em>
                @endif
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('selfie') ? 'has-error' : '' }}">
                <label for="selfie">{{ trans('global.staff.fields.job') }}*</label>
                <select id="selfie" name="selfie" class="form-control" value="{{ old('selfie', isset($customer) ? $customer->work_unit : '') }}">
                    <option value="">--Pilih Status--</option>
                        <option value="ON" {{$staff->selfie == "ON" ? 'selected' : ''}}>ON</option>
                        <option value="OFF" {{$staff->selfie == "OFF" ? 'selected' : ''}}>OFF</option>
                     
                </select>
                @if($errors->has('selfie'))
                    <em class="invalid-feedback">
                        {{ $errors->first('selfie') }}
                    </em>
                @endif
            </div> --}}

            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                <label for="type">Tipe*</label>
                <select id="type" name="type" class="form-control" value="{{ old('type', isset($customer) ? $customer->work_unit : '') }}">
                    <option value="">--Pilih Tipe--</option>
                        <option value="employee" {{$staff->type == "employee" ? 'selected' : ''}}>employee</option>
                        <option value="contract" {{$staff->type == "contract" ? 'selected' : ''}}>contract</option>
                     
                </select>
                @if($errors->has('type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('_status') ? 'has-error' : '' }}">
                <label for="_status">Status*</label>
                <select id="_status" name="_status" class="form-control" value="{{ old('_status', isset($customer) ? $customer->work_unit : '') }}">
                    {{-- <option value="">--Pilih Tipe--</option> --}}
                        <option value="active" {{$staff->_status == "active" ? 'selected' : ''}}>active</option>
                        <option value="non_active" {{$staff->_status == "non_active" ? 'selected' : ''}}>non_active</option>
                     
                </select>
                @if($errors->has('_status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('_status') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('area') ? 'has-error' : '' }}">
                <label for="area">{{ trans('global.staff.fields.area') }}*</label>
                <select name="area[]" id="area" class="form-control select2" multiple="multiple">
                    @foreach($area as $id => $are)
                        <option value="{{ $are->code }}"   @foreach($staff->area as $i => $ares)  {{$are->code == $ares->pivot->area_id ? 'selected' : ''}}  @endforeach  >
                            {{ $are->code}}-{{ $are->NamaWilayah}}
                        </option>
                    @endforeach
                </select>
              
                @if($errors->has('area'))
                    <em class="invalid-feedback">
                        {{ $errors->first('area') }}
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
                $("#subdapertement_id").append('<option>---Pilih Sub Depertement---</option>');
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
@endsection