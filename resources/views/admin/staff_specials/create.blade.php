@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.staff.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.staffSpecials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.staff.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($staff) ? $staff->code : $code) }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div> --}}

            <div class="form-group {{ $errors->has('expired_date') ? 'has-error' : '' }}">
                <label for="expired_date">{{ trans('global.staffSpecial.fields.expired_date') }}*</label>
                <input type="date" id="expired_date" name="expired_date" class="form-control" value="{{ old('expired_date', isset($staffSpecial) ? $staffSpecial->expired_date : '') }}">
                @if($errors->has('expired_date'))
                    <em class="invalid-feedback">
                        {{ $errors->first('expired_date') }}
                    </em>
                @endif
            </div>


            
            {{-- <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                <label for="staff_id">{{ trans('global.staffSpecial.fields.staff') }}*</label>
                <select id="staff_id" name="staff_id" class="form-control" value="{{ old('staff_id', isset($customer) ? $customer->staff : '') }}">
                    <option value="">--Pilih staff--</option>
                    @foreach ($staffs as $key=>$staff )
                        <option value="{{$staff->id}}">{{$staff->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('staff_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('staff_id') }}
                    </em>
                @endif
            </div> --}}


            <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                <label for="staff_id">Staff*</label>
                <select id="staff_id" name="staff_id" class="form-control" value="{{ old('staff_id', isset($absence) ? $absence->staff_id : '') }}">
                    <option value="">--staff--</option>
                    @foreach ($staffs as $key=>$staff )
                        <option value="{{$staff->id}}">{{$staff->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('staff_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('staff_id') }}
                    </em>
                @endif
            </div>




         

           

            <div class="form-group {{ $errors->has('fingerprint') ? 'has-error' : '' }}">
                <label for="fingerprint">Fingerprint*</label>
                <select id="fingerprint" name="fingerprint" class="form-control" value="{{ old('fingerprint', isset($customer) ? $customer->work_unit : '') }}">
                    <option value="">--Pilih Status--</option>
                    {{-- @foreach ($jobs as $key=>$job ) --}}
                        <option value="ON">ON</option>
                        <option value="OFF">OFF</option>
                        {{-- @endforeach --}}
                </select>
                @if($errors->has('fingerprint'))
                    <em class="invalid-feedback">
                        {{ $errors->first('fingerprint') }}
                    </em>
                @endif
            </div>


            <div class="form-group {{ $errors->has('camera') ? 'has-error' : '' }}">
                <label for="camera">camera*</label>
                <select id="camera" name="camera" class="form-control" value="{{ old('camera', isset($customer) ? $customer->work_unit : '') }}">
                    <option value="">--Pilih Status--</option>
                    {{-- @foreach ($jobs as $key=>$job ) --}}
                        <option value="ON">ON</option>
                        <option value="OFF">OFF</option>
                        {{-- @endforeach --}}
                </select>
                @if($errors->has('camera'))
                    <em class="invalid-feedback">
                        {{ $errors->first('camera') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('gps') ? 'has-error' : '' }}">
                <label for="gps">gps*</label>
                <select id="gps" name="gps" class="form-control" value="{{ old('gps', isset($customer) ? $customer->work_unit : '') }}">
                    <option value="">--Pilih Status--</option>
                    {{-- @foreach ($jobs as $key=>$job ) --}}
                        <option value="ON">ON</option>
                        <option value="OFF">OFF</option>
                        {{-- @endforeach --}}
                </select>
                @if($errors->has('gps'))
                    <em class="invalid-feedback">
                        {{ $errors->first('gps') }}
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

<script type="text/javascript">
    $(document).ready(function() {
        $('#staff_id').select2({
         placeholder: 'Pilih Staff',
         allowClear: true
        });
        $('#work_unit_id').select2({
         placeholder: 'Pilih Work Unit',
         allowClear: true
        });
    });
   </script>
<script>

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