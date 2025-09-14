@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.absence.reportAbsenceExcel') }}" method="POST" target="_blank" enctype="multipart/form-data" >
        @csrf
        <div class="row">
            <div class="col-lg-5">
                <div class="form-group">
               
                </div>
                
                             {{-- register --}}
{{-- <label for="type">Pilih Bulan</label>
<div class='input-group' id='dpRM'>
    <input type='text' name="monthyear" id="monthyear" class="form-control form-control-1 form-input input-sm fromq" placeholder="Enter Month and year" required  />
    <span class="input-group-addon"> --}}
        {{-- <span class="fa fa-calendar"></span> --}}
    {{-- </span>
</div>
<br> --}}

<div class="col-md-12">
    <div class="form-group">
        <label>Dari Tanggal</label>
        <div class="input-group date">
            <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
            </div>
            <input id="from" placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "">
        </div>
    </div>
    <div class="form-group">
        <label>Sampai Tanggal</label>
        <div class="input-group date">
            <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
            </div>
            <input id="to" placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{date('Y-m-d')}}">
        </div>
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

<div class="form-group {{ $errors->has('work_unit_id') ? 'has-error' : '' }}">
    <label for="work_unit_id">work_unit*</label>
    <select id="work_unit_id" name="work_unit_id" class="form-control" value="{{ old('work_unit_id', isset($absence) ? $absence->work_unit_id : '') }}">
        <option value="">--work_unit--</option>
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


{{-- <div class="form-group {{ $errors->has('sub_dapertement_id') ? 'has-error' : '' }}">
    <label for="sub_dapertement_id">Sub Dapetement*</label>
    <select id="sub_dapertement_id" name="sub_dapertement_id" class="form-control" value="{{ old('sub_dapertement_id', isset($absence) ? $absence->sub_dapertement_id : '') }}">
        <option value="">--sub_dapetement--</option>
        @foreach ($sub_dapetements as $key=>$sub_dapetement )
            <option value="{{$sub_dapetement->id}}">{{$sub_dapetement->name}}</option>
        @endforeach
    </select>
    @if($errors->has('sub_dapertement_id'))
        <em class="invalid-feedback">
            {{ $errors->first('sub_dapertement_id') }}
        </em>
    @endif
</div> --}}

<div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
    <label for="dapertement_id">{{ trans('global.staff.fields.dapertement') }}*</label>
    <select id="dapertement_id" name="dapertement_id" class="form-control" value="{{ old('dapertement', isset($customer) ? $customer->dapertement : '') }}">
        <option value="">--Pilih Dapertement--</option>
        @foreach ($dapertements as $key=>$dapertement )
            <option value="{{$dapertement->id}}" >{{$dapertement->name}}</option>
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
    </select>
    @if($errors->has('subdapertement_id'))
        <em class="invalid-feedback">
            {{ $errors->first('subdapertement_id') }}
        </em>
    @endif
</div>

<div class="form-group {{ $errors->has('job_id') ? 'has-error' : '' }}">
    <label for="job_id">Job*</label>
    <select id="job_id" name="job_id" class="form-control" value="{{ old('job_id', isset($absence) ? $absence->job_id : '') }}">
        <option value="">--job--</option>
        @foreach ($jobs as $key=>$job )
            <option value="{{$job->id}}">{{$job->name}}</option>
        @endforeach
    </select>
    @if($errors->has('job_id'))
        <em class="invalid-feedback">
            {{ $errors->first('job_id') }}
        </em>
    @endif
</div>


</div>  

{{-- <label for="type">Wilayah</label>
<select id="areas" name="areas" class="form-control">
    <option value="">== Semua area ==</option>
    @foreach ($areas as $item )
    <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}</option>
    @endforeach
</select> --}}

              
            </div>
        </div>
<br>
        <Button  type="submit"  name="absence_log" class="btn btn-primary" value="Proses" >Proses Absen</Button>

        <Button  type="submit" name="absence_log"  class="btn btn-warning" value="yes" >Proses Absen Log</Button>

      </form>
@endsection
@parent
@section('scripts')
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
                $("#subdapertement_id").append('<option value="">---Pilih Sub Depertement---</option>');
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


<script>
    
$('#dpRM').datetimepicker({
    viewMode : 'months',
    format : 'YYYY-MM',
    toolbarPlacement: "top",
    allowInputToggle: true,
    icons: {
        time: 'fa fa-time',
        date: 'fa fa-calendar',
        up: 'fa fa-chevron-up',
        down: 'fa fa-chevron-down',
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-screenshot',
        clear: 'fa fa-trash',
        close: 'fa fa-remove',
    }
});

$("#dpRM").on("dp.show", function(e) {
   $(e.target).data("DateTimePicker").viewMode("months"); 
});
</script>
@endsection