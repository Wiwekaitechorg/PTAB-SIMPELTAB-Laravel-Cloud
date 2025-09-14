@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.check.title_singular') }}
    </div>

    <div class="card-body">
        <form check="{{ route('admin.checks.update', $check->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.check.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" >{{$check->description}}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
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
                    url : "{{route('admin.checks.staff')}}",
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

