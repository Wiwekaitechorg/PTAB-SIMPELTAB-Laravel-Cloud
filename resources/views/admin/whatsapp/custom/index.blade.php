@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Kirim Pesan Custom
    </div>

    <div class="card-body">
        <form action="{{ route("admin.customwa.import") }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.categoryWA.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($code) ? $code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.categoryWA.fields.code_helper') }}
                </p>
            </div> --}}
  
<label for="example">Download Contoh :</label>
              <a href="{{asset('files/exampleCustomWa.xlsx')}}" download class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Download Berkas </a>

                {{-- upload image --}}
                <div class="form-group {{ $errors->has('files') ? 'has-error' : '' }}">
                    <label for="files">File (lebih baik tidak lebih dari 100 data)</label>
                    <div class="custom-file">
                        <input id="files" name="files" type="file" accept=".xls,.xlsx" class="custom-file-input" id="customFile">
                        <label class="custom-file-label" for="customFile">Masukan File (EXCEL)</label>
                      </div>
                                {{-- @if($errors->has('file'))
                        <em class="invalid-feedback">
                            {{ $errors->first('file') }}
                        </em>
                    @endif
                    <p class="helper-block">
                        {{ trans('global.patient.fields.file_helper') }}
                    </p> --}}
                </div>
                <div class="form-group">
                  <label for="message">Pesan</label>
                  {{-- sementara --}}
                  <textarea class="form-control" name="message" id="message" rows="3"> </textarea>
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
        <td>Nama</td>
      </tr>
      <tr>
        <th scope="row">2</th>
        <td>@alamat</td>
        <td>Alamat</td>
      </tr>
      <tr>
        <th scope="row">4</th>
        <td>@waktu</td>
        <td>Waktu</td>
      </tr>
    </tbody>
  </table>
{{-- table bantu --}}


<div class="form-group">
  <button type="button" class="btn btn-primary file_group" name="add" id="file_group"><i class="fas fa-plus"> Tambah File</i></button>
  <label>File</label>
  
  <div id="file" class="p-3 mb-2">
     </div>
</div>

<div class="form-group">
  <button type="button" class="btn btn-primary image_group" name="add" id="image_group"><i class="fas fa-plus"> Tambah Gambar</i></button>
  <label>Gambar</label>
  
  <div id="image" class="p-3 mb-2">
     </div>
</div>

<div class="form-group">
  <button type="button" class="btn btn-primary video_group" name="add" id="video_group"><i class="fas fa-plus"> Tambah Gambar</i></button>
  <label>Video</label>
  
  <div id="video" class="p-3 mb-2">
     </div>
</div>



            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script type="text/javascript">
  $(".custom-file-input").on("change", function() {
var fileName = $(this).val().split("\\").pop();
$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});


var i=1; 
                  $('#image_group').click(function(){   
                  
                    // alert(i)
                    if(i <= 3){
                        i++;  
                    $('#image').append('<div id="image'+i+'" ><div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}"><label for="image">Gambar</label><div class="custom-file"><input id="image" name="image[]" accept="image/png, image/jpeg,, image/jpg" type="file" class="custom-file-input" id="customFile" required><label class="custom-file-label" for="customFile">Choose file</label></div></div><button type="button" name="remove" idi="'+i+'" class="btn btn-danger btn_removeimage"><i class="fas fa-minus"> hapus</i></button></div>');                
                       $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
                    }
    
                    });
    
                    $(document).on('click','.btn_removeimage', function(){ 
                    var button_id = $(this).attr("idi"); 
                    i--;
                       $('#image'+button_id+'').remove();  
                  });  
    

                  var f=1; 
                  $('#file_group').click(function(){   
                  
                    // alert(i)
                    if(f <= 3){
                        f++;  
                    $('#file').append('<div id="file'+i+'" ><div class="form-group {{ $errors->has('file') ? 'has-error' : '' }}"><label for="file">File(Seperti Pdf, Excel, ataupun Word)</label><div class="custom-file"><input id="file" name="file[]" type="file" class="custom-file-input" id="customFile" required><label class="custom-file-label" for="customFile">Choose file</label></div></div><button type="button" name="remove" idi="'+i+'" class="btn btn-danger btn_removefile"><i class="fas fa-minus"> hapus</i></button></div>');                
                       $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
                    }
    
                    });
    
                    $(document).on('click','.btn_removefile', function(){ 
                    var button_id = $(this).attr("idi"); 
                    f--;
                       $('#file'+button_id+'').remove();  
                  });  
    

              
                  var v=1; 
                  $('#video_group').click(function(){   
                  
                    // alert(i)
                    if(v <= 1){
                        v++;  
                    $('#video').append('<div id="video'+v+'" ><div class="form-group {{ $errors->has('video') ? 'has-error' : '' }}"><label for="video">Video</label><div class="custom-file"><input id="video" name="video[]" accept="video/mp4" type="file" class="custom-file-input" id="customFile" required><label class="custom-file-label" for="customFile">Choose file</label></div></div><button type="button" name="remove" idi="'+v+'" class="btn btn-danger btn_removevideo"><i class="fas fa-minus"> hapus</i></button></div>');                
                       $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
                    }
    
                    });
    
                    $(document).on('click','.btn_removevideo', function(){ 
                    var button_id = $(this).attr("idi"); 
                    v--;
                       $('#video'+button_id+'').remove();  
                  });  
    
    
        // $(document).ready(function() {
        //   $("#message").emojioneArea();
        // });


</script>

    
@endsection