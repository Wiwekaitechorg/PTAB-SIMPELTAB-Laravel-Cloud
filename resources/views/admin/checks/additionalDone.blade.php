@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} Status Tindakan
    </div>

    <div class="card-body">
        <form check="{{route('admin.checks.storeAdditionalDone')}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name='check_id' value='{{$check->id}}'>
            <input type="hidden" name='ticket_id' value='{{$check->ticket_id}}'>
        
            {{-- test baru --}}
            <label>Catatan : foto akan ditambahkan dari foto yang sebelumnya diinput</label>
  {{-- image --}}

  <div class="form-group">
    <button type="button" class="btn btn-primary image_done" name="add" id="image_done"><i class="fas fa-plus"> Tambah Foto Selesai</i></button>
    <label>Foto Selesai (max 18/upload)</label>
    
    <div id="loan_image_done" class="p-3 mb-2">
       </div>
  </div>


  <div>
    <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
</div>
    {{-- <button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_removeimage_prework">
        <i class="fas fa-minus"> hapus</i></button> --}}

        <h5 style="font-weight:bold">Foto selesai saat ini</h5>
        <h6>Note : hanya untuk menambahkan foto dari foto sebelumnya, untuk mereset foto harus dari update status tindakan</h6>
        <div class="row">
            @foreach ($ticket->check as $acti)
                @if ($acti->image_done != null) 
                    @foreach (json_decode($acti->image_done) as $itemdone)
                    <div class="col-md-5">
                        <img  height="250px" width="350px"  src={{url('/')."$itemdone"}} alt="">
                        <p class="my-2"><a href="{{url('/')."$itemdone"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                    </div>
                    @endforeach
                @endif
            @endforeach
        </div>

    </div>


    
   
 

            {{-- test baru end --}}
          
        </form>
    </div>

@endsection
@section('scripts')
    @parent
    <script>
   $(document).ready(function(){  
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

                var a=1; 
              $('#image_tools').click(function(){   
              
                // alert(a)
                if(a <= 3){
                    a++;  
                $('#loan_image_tools').append(' <div id="loan_image_tools'+a+'" ><div class="form-group {{ $errors->has('img') ? 'has-error' : '' }}"><label for="image_tools_file">Foto Alat</label><div class="custom-file"><input id="image_tools_file" name="image_tools[]" type="file" class="custom-file-input" id="customFile" required><label class="custom-file-label" for="customFile">Choose file</label></div></div><button type="button" name="remove" id="'+a+'" class="btn btn-danger btn_removeimage_tools"><i class="fas fa-minus"> hapus</i></button></div>');                
                   $(".custom-file-input").on("change", function() {
var fileName = $(this).val().split("\\").pop();
$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
                }

                });
              
              $(document).on('click','.btn_removeimage_tools', function(){ 
                var button_id = $(this).attr("id"); 
                i--;
                   $('#loan_image_tools'+button_id+'').remove();  
              });  

              var b=1; 
              $('#image_done').click(function(){   
                
                // alert(b)
                if(b <= 18){
                    b++; 
                $('#loan_image_done').append(' <div id="loan_image_done'+b+'" ><div class="form-group {{ $errors->has('img') ? 'has-error' : '' }}"><label for="image_done_file">Foto Selesai</label><div class="custom-file"><input id="image_done_file" name="image_done[]" type="file" class="custom-file-input" id="customFile" required><label class="custom-file-label" for="customFile">Choose file</label></div></div><button type="button" name="remove" id="'+b+'" class="btn btn-danger btn_removeimage_done"><i class="fas fa-minus"> hapus</i></button></div>');                
                   $(".custom-file-input").on("change", function() {
var fileName = $(this).val().split("\\").pop();
$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
                }

                });
              
              $(document).on('click','.btn_removeimage_done', function(){ 
                var button_id = $(this).attr("id"); 
                i--;
                   $('#loan_image_done'+button_id+'').remove();  
              });  
        
              
            }); 

              $(".custom-file-input").on("change", function() {
var fileName = $(this).val().split("\\").pop();
$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
    </script>
@endsection