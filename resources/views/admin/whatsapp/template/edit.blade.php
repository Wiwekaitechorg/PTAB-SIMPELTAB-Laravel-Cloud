@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.WaTemplate.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.WaTemplate.update", [$WaTemplate->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.WaTemplate.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($WaTemplate) ? $WaTemplate->name : '') }}" required>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.WaTemplate.fields.name_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('category_id') ? 'has-error' : '' }}">
                <label for="category_id">{{ trans('global.WaTemplate.fields.category') }}*</label>
                <select id="category_id" name="category_wa_id" class="form-control" value="{{ old('category_id', isset($WaTemplate) ? $WaTemplate->category_id : '') }}" required>
                    <option value="">--Pilih category--</option>
                    @foreach ($categorys as $key=>$category )
                        <option value="{{$category->id}}" @if ($WaTemplate->category_wa_id === $category->id) selected @endif >{{$category->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('category_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('category_id') }}
                    </em>
                @endif


                <div class="form-group">
                  <button type="button" class="btn btn-primary image" name="add" id="image"><i class="fas fa-plus"> Tambah Foto Laporan</i></button>
                  <label>File</label>
                  
                  <div id="loan_image" class="p-3 mb-2">
                     </div>
                </div>

            <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                <label for="message">{{ trans('global.WaTemplate.fields.message') }}*</label>
                <textArea id="message" name="message" class="form-control" required>{{ old('name', isset($WaTemplate) ? $WaTemplate->message : '') }}</textArea>
                @if($errors->has('message'))
                    <em class="invalid-feedback">
                        {{ $errors->first('message') }}
                    </em>
                @endif
            </div>
{{-- table bantu --}}
<table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Variabel</th>
        <th scope="col">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th scope="row">1</th>
        <td>@nama</td>
        <td>Nama Pelanggan</td>
      </tr>
      <tr>
        <th scope="row">2</th>
        <td>@alamat</td>
        <td>Alamat</td>
      </tr>
      <tr>
        <th scope="row">3</th>
        <td>@sbg</td>
        <td>Nomor SBG</td>
      </tr>
      <tr>
        <th scope="row">4</th>
        <td>@waktu</td>
        <td>Waktu</td>
      </tr>
    </tbody>
  </table>
{{-- table bantu --}}
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')

<script type="text/javascript">

var i=1; 
              $('#image').click(function(){   
              
                // alert(i)
                if(i <= 4){
                    i++;  
                $('#loan_image').append(' <div id="loan_image'+i+'" ><div class="form-group {{ $errors->has('img') ? 'has-error' : '' }}"><label for="image_file">Foto Pengerjaan</label><div class="custom-file"><input id="image_file" name="image[]" type="file" class="custom-file-input" id="customFile" required><label class="custom-file-label" for="customFile">Choose file</label></div></div><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_removeimage"><i class="fas fa-minus"> hapus</i></button></div>');                
                   $(".custom-file-input").on("change", function() {
var fileName = $(this).val().split("\\").pop();
$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
                }

                });

                $(document).on('click','.btn_removeimage', function(){ 
                var button_id = $(this).attr("id"); 
                i--;
                   $('#loan_image'+button_id+'').remove();  
              });  


    $(document).ready(function() {
      $("#message").emojioneArea();
    });
  </script>
  @endsection