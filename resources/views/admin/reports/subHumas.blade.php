@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.report.subhumasproses') }}" method="POST" target="_blank" enctype="multipart/form-data" >
        @csrf
        <div class="row">
            <div class="col-lg-5">
                <div class="form-group">
                <label>Dari Tanggal</label>
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                        <input placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "{{date('Y-m-d')}}">
                    </div>
                </div>
                <div class="form-group">
                <label>Sampai Tanggal</label>
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                        <input placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{date('Y-m-d')}}">
                    </div>
                </div>
        

                <div class="form-group {{ $errors->has('dapertement_id') ? 'has-error' : '' }}">
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

                <div class="form-group">
                    <label>Pilih Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">== Semua Status ==</option>
                        <option value="close">Close</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
        </div>

        <Button  type="submit"  class="btn btn-primary" value="Proses" >Proses</Button>

      </form>
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
