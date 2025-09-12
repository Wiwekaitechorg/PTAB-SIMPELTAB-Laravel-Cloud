@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.absence.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.absence.storeLeave") }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.absence.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($code) ? $code : '') }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.absence.fields.code_helper') }}
                </p>
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


            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                <label for="date">Tanggal*</label>
                <input type="date" id="date" name="date" class="form-control" value="{{ old('date', isset($work_type_day) ? $work_type_day->date : '') }}" required>
                @if($errors->has('date'))
                    <em class="invalid-feedback">
                        {{ $errors->first('date') }}
                    </em>
                @endif
                <p class="helper-block">

                </p>
            </div>

            <div class="form-group {{ $errors->has('date2') ? 'has-error' : '' }}">
                <label for="date2">Tanggal Selesai*</label>
                <input type="date" id="date2" name="date2" class="form-control" value="{{ old('date2', isset($work_type_day) ? $work_type_day->date2 : '') }}" required>
                @if($errors->has('date2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('date2') }}
                    </em>
                @endif
                <p class="helper-block">

                </p>
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
<script type="text/javascript">
    $(document).ready(function() {
        $('#staff_id').select2({
         placeholder: 'Pilih Staff',
         allowClear: true
        });
    });
   </script>
   <script type="text/javascript">
     $("#btn2").click(function(){
    $("#listDuty")
  });
    var i = 0;
    $("#dynamic-ar").click(function () {
        var elem = "#duty" + i;
        i++;
        $("#listDuty").append(
            '<div id="group1'+i+'">'
            +'<div class="form-group {{ $errors->has("duty") ? "has-error" : "" }}">'
                +'<label for="duty">Jam Dinas '+(i+1)+'</label>'
                +'<input type="time" id="duty" name="duty[]" class="form-control" value="{{ old("duty", isset($work_type_day) ? $work_type_day->duty : "") }}" required>'
             +'@if($errors->has("duty"))'
                  +'<em class="invalid-feedback">'
                        +'{{ $errors->first("duty") }}'
                    +'</em>'
                +'@endif'
                +'<p class="helper-block">'
                +'</p>'
            +'</div>'

            +'<div class="form-group {{ $errors->has("duty_end") ? "has-error" : "" }}">'
                +'<label for="duty_end">Selesai Dinas '+(i+1)+'</label>'
                +'<input type="time" id="duty_end" name="duty_end[]" class="form-control" value="{{ old("duty_end", isset($work_type_day) ? $work_type_day->duty_end : "") }}" required>'
                +'@if($errors->has("duty_end"))'
                    +'<em class="invalid-feedback">'
                        +'{{ $errors->first("duty_end") }}'
                    +'</em>'
               +' @endif'
                +'<p class="helper-block">'
                +'</p>'
                +'</div>'
            +'</div>');
        
    });
    $(document).on("click", '.remove-input-field', function () {
        alert('dkdk'+i)
        $('#group1'+i).remove();
        i--;
    });


    $('#shift_parent_id').change(function(){
    var shift_parent_id = $(this).val();    
    console.log('ini data3')
    if(shift_parent_id){
        console.log('ini data4')
        $.ajax({
           type:"GET",
           url:"{{ route('admin.absence.getShiftPlanner') }}?shift_parent_id="+shift_parent_id,
           dataType: 'JSON',
           success:function(res){    
            console.log('ini data', res)           
            if(res){
                $("#shift_group_id").empty();
                $("#shift_group_id").append('<option>---Pilih Shift ---</option>');
                $.each(res,function(id,title){
                    $("#shift_group_id").append('<option value="'+id+'">'+title+'</option>');
                });
            }else{
               $("#shift_group_id").empty();
               console.log('ini data2')
            }
           }
        });
    }else{
        $("#shift_group_id").empty();
      
    }      
   });
</script>
   @endsection
