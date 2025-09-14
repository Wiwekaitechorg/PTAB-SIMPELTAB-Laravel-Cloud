@extends('layouts.admin')
@section('content')

<div class="card">        
    @if($errors->any())
    <!-- <h4>{{$errors->first()}}</h4> -->
        <?php 
            echo "<script> alert('{$errors->first()}')</script>";
        ?>
    @endif
    <div class="card-header">
       Create Lock 
    </div>

    <div class="card-body">
        <form action="{{ route('admin.lockT.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.lock.code') }}*</label>
                <input required type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($lock) ? $lock->code : $scb) }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('customer') ? 'has-error' : '' }}">
                <label for="customer">{{ trans('global.lock.customer') }}*</label>
                <input readonly type="text" id="customer" name="customer_id" class="form-control" value="{{ old('customer_id', isset($lock) ? $lock->customer_id : $customer_id) }}">
                @if($errors->has('customer'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer') }}
                    </em>
                @endif
            </div>      
            <div class="form-group {{ $errors->has('dapertement_id') ? 'has-error' : '' }}">
                <label for="dapertement_id">{{ trans('global.lock.dapertement') }}*</label>
                <select required id="dapertement_id" name="dapertement_id" class="form-control" value="{{ old('dapertement_id', isset($lock) ? $lock->dapertement_id : '') }}">
                    <option value="">--Pilih Dapertement--</option>
                    @foreach ($dapertements as $key=>$dapertement )
                        <option value="{{$dapertement->id}}" {{$dapertement->id == $dapertement_id ? 'selected' : ''}}>{{$dapertement->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('dapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('dapertement_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('subdapertement_id') ? 'has-error' : '' }}">
                <label for="subdapertement_id">{{ trans('global.lock.subdapertement') }}*</label>
                <select required id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($lock) ? $lock->subdapertement_id : '') }}">
                    <option value="0">--Pilih Sub Depertement--</option> 
                    @foreach ($subdapertements as $key=>$subdapertement )
                        <option value="{{$subdapertement->id}}" {{$subdapertement->id == $subdapertement_id ? 'selected' : ''}} >{{$subdapertement->name}}</option>
                    @endforeach                   
                </select>
                @if($errors->has('subdapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('subdapertement_id') }}
                    </em>
                @endif
            </div>  
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.lock.description') }}*</label>
                <textarea type="text" id="description" name="description" required class="form-control"  > {{ old('description', isset($lock) ? $lock->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>
            <div>
                <input  class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
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