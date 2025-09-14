@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.action.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.actions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.action.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($action) ? $action->code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" ></textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('ticket') ? 'has-error' : '' }}">
                <label for="ticket">{{ trans('global.action.fields.ticket') }}*</label>
                <select id="ticket" name="ticket_id" class="form-control select2">
                    <option value="">--Pilih Tiket--</option>
                    @foreach ($tickets as $ticket )
                        <option value="{{$ticket->id}}" >{{$ticket->title}}</option>
                    @endforeach
                </select>
                @if($errors->has('ticket'))
                    <em class="invalid-feedback">
                        {{ $errors->first('ticket') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
                <label for="dapertement">{{ trans('global.action.fields.dapertement') }}*</label>
                <select id="dapertement" name="dapertement_id" class="form-control">
                    <option value="">--Pilih dapertement--</option>
                    @foreach ($dapertements as $dapertement )
                        <option value="{{$dapertement->id}}" >{{$dapertement->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('dapertement'))
                    <em class="invalid-feedback">
                        {{ $errors->first('dapertement') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('staff') ? 'has-error' : '' }}">
                <label for="staff">{{ trans('global.action.fields.staff') }}*
                    <span class="btn btn-info btn-xs select-all">Select all</span>
                    <span class="btn btn-info btn-xs deselect-all">Deselect all</span></label>
                <select name="staff[]" id="staff" class="form-control select2" multiple="multiple">
                </select>
                @if($errors->has('staff'))
                    <em class="invalid-feedback">
                        {{ $errors->first('staff') }}
                    </em>
                @endif
                <!-- <p class="helper-block">
                    {{ trans('global.action.fields.staff') }}
                </p> -->
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
        $(document).ready(function (){

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#dapertement').on('change', function(){
                let dapertement_id = $('#dapertement').val();
                $('#staff').empty();
                $.ajax({
                    url : "{{route('admin.actions.staff')}}",
                    method : 'post',
                    dataType:'json',
                    data :{
                        dapertement_id :  dapertement_id
                    },
                    success: function(result){
                        console.log(result);
                        $.each(result, function(key, item){
                        // perhtikan dimana kita akan menampilkan data select nya, di sini saya memberi name select kota adalah destination
                            $('#staff').append('<option value =' + item.id +'>' +item.name+'</option>');
                        });
                    }
                })
            })
        })
    </script>

@endsection

@endsection

