@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.absence.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.absence.storeShift") }}" method="POST" enctype="multipart/form-data">
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

            <div class="form-group {{ $errors->has('shift_parent_id') ? 'has-error' : '' }}">
                <label for="shift_parent_id">Group Shift*</label>
                <select id="shift_parent_id" name="shift_parent_id" class="form-control" value="{{ old('shift_parent_id', isset($absence) ? $absence->shift_parent_id : '') }}">
                    <option value="">--Pilih Group --</option>
                    @foreach ($shift_parents as $key=>$shift_parent )
                        <option value="{{$shift_parent->id}}">{{$shift_parent->title}}</option>
                    @endforeach
                </select>
                @if($errors->has('shift_parent_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('shift_parent_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('shift_group_id') ? 'has-error' : '' }}">
                <label for="shift_group_id">Shift*</label>
                <select id="shift_group_id" name="shift_group_id" class="form-control" value="{{ old('shift_group_id', isset($customer) ? $customer->shift_group : '') }}">
                    <option value="">--Pilih Shift--</option>                    
                </select>
                @if($errors->has('shift_group_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('shift_group_id') }}
                    </em>
                @endif
            </div>

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

            <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                <label for="time">Jam Kerja*</label>
                <input type="time" id="time" name="time" class="form-control" value="{{ old('time', isset($work_type_day) ? $work_type_day->time : '') }}" required>
                @if($errors->has('time'))
                    <em class="invalid-feedback">
                        {{ $errors->first('time') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.work_type_day.fields.time_helper') }} --}}
                </p>
            </div>

            <div class="form-group {{ $errors->has('time_end') ? 'has-error' : '' }}">
                <label for="time_end">Selesai Kerja *</label>
                <input type="time" id="time_end" name="time_end" class="form-control" value="{{ old('time_end', isset($work_type_day) ? $work_type_day->time_end : '') }}" required>
                @if($errors->has('time_end'))
                    <em class="invalid-feedback">
                        {{ $errors->first('time_end') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.work_type_day.fields.duration_helper') }} --}}
                </p>
            </div>


            <div class="form-group {{ $errors->has('break') ? 'has-error' : '' }}">
                <label for="break">Jam Istirahat*</label>
                <input type="time" id="break" name="break" class="form-control" value="{{ old('break', isset($work_type_day) ? $work_type_day->break : '') }}" required>
                @if($errors->has('break'))
                    <em class="invalid-feedback">
                        {{ $errors->first('break') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.work_type_day.fields.break_helper') }} --}}
                </p>
            </div>

            <div class="form-group {{ $errors->has('break_end') ? 'has-error' : '' }}">
                <label for="break_end">Selesai Istirahat*</label>
                <input type="time" id="break_end" name="break_end" class="form-control" value="{{ old('break_end', isset($work_type_day) ? $work_type_day->break_end : '') }}" required>
                @if($errors->has('break_end'))
                    <em class="invalid-feedback">
                        {{ $errors->first('break_end') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.work_type_day.fields.duration_helper') }} --}}
                </p>
            </div>


            {{-- dinas --}}

            
<div id="listDuty">
    <div id="group1">
            <div class="form-group {{ $errors->has('duty') ? 'has-error' : '' }}">
                <label for="duty">Jam Dinas 1</label>
                <input type="time" id="duty" name="duty[]" class="form-control" value="{{ old('duty', isset($work_type_day) ? $work_type_day->duty : '') }}">
                @if($errors->has('duty'))
                    <em class="invalid-feedback">
                        {{ $errors->first('duty') }}
                    </em>
                @endif
                <p class="helper-block">
                </p>
            </div>

            <div class="form-group {{ $errors->has('duty_end') ? 'has-error' : '' }}">
                <label for="duty_end">Selesai Dinas 1</label>
                <input type="time" id="duty_end" name="duty_end[]" class="form-control" value="{{ old('duty_end', isset($work_type_day) ? $work_type_day->duty_end : '') }}">
                @if($errors->has('duty_end'))
                    <em class="invalid-feedback">
                        {{ $errors->first('duty_end') }}
                    </em>
                @endif
                <p class="helper-block">
                </p>
            </div>
        </div>
    </div>

            <button type="button" name="add" id="dynamic-ar" class="btn btn-outline-primary">Tambah</button></td>
            <button type="button" name="add" id="remove-input-field" class="btn btn-outline-primary remove-input-field">Hapus</button></td>
            
             {{-- permisi --}}
             <div class="form-group {{ $errors->has('excuse') ? 'has-error' : '' }}">
                <label for="excuse">Jam Permisi</label>
                <input type="time" id="excuse" name="excuse" class="form-control" value="{{ old('excuse', isset($work_type_day) ? $work_type_day->excuse : '') }}">
                @if($errors->has('excuse'))
                    <em class="invalid-feedback">
                        {{ $errors->first('excuse') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.work_type_day.fields.time_excuse_helper') }} --}}
                </p>
            </div>

            <div class="form-group {{ $errors->has('excuse_end') ? 'has-error' : '' }}">
                <label for="excuse_end">Selesai Permisi</label>
                <input type="time" id="excuse_end" name="excuse_end" class="form-control" value="{{ old('excuse_end', isset($work_type_day) ? $work_type_day->excuse_end : '') }}">
                @if($errors->has('excuse_end'))
                    <em class="invalid-feedback">
                        {{ $errors->first('excuse_end') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.work_type_day.fields.excuse_end_helper') }} --}}
                </p>
            </div>
            
            {{-- <div class="form-group {{ $errors->has('absence_category_id') ? 'has-error' : '' }}">
                <label for="absence_category_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="absence_category_id" name="absence_category_id" class="form-control" value="{{ old('absence_category_id', isset($absence) ? $absence->absence_category_id : '') }}">
                    <option value="">--kategori absen--</option>
                    <option value="in">Masuk</option>
                    <option value="break_in">Istirahat Mulai</option>
                    <option value="break_out">Istirahat Selesai</option>
                    <option value="out">Pulang</option>
                    @foreach ($absence_categories as $key=>$absence_category_id )
                        <option value="{{$absence_category_id->id}}">{{$absence_category_id->title}}</option>
                    @endforeach
            
                </select>
                @if($errors->has('absence_category_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('absence_category_id') }}
                    </em>
                @endif
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.absence.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($absence) ? $absence->register : '') }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.absence.fields.register_helper') }}
                </p>
            </div> --}}


            {{-- <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($absence) ? $absence->description : '') }}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div> --}}

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
        // alert('dkdk'+i)
        if(i>0){
        $('#group1'+i).remove();
        i--;
        }
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
