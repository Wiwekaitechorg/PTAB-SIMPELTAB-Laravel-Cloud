@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.action.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.actions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ticket_id" value="{{$ticket_id}}">
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" ></textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('dapertement_id') ? 'has-error' : '' }}">
                <label for="dapertement_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="dapertement_id" name="dapertement_id" class="form-control" value="{{ old('dapertement_id', isset($user) ? $user->dapertement : '') }}">
                    <option value="">--Pilih Dapertement--</option>
                    @foreach ($dapertements as $key=>$dapertement )
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
                <select id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($user) ? $user->subdapertement : '') }}">
                    <option value="0">--Pilih Sub Depertement--</option>                    
                </select>
                @if($errors->has('subdapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('subdapertement_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('todo') ? 'has-error' : '' }}">
                <label for="todo">Dikerjakan Oleh: *</label>
                <select id="todo" name="todo" class="form-control" value="{{ old('todo', isset($user) ? $user->subdapertement : '') }}" required>
                    <option value="">--Pilih Dikerjakan Oleh--</option>   
                    <option value="Internal">Internal</option>  
                    <option value="Pihak ke-3">Pihak ke-3</option>               
                </select>
                @if($errors->has('todo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('todo') }}
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
</script>
@endsection
@endsection