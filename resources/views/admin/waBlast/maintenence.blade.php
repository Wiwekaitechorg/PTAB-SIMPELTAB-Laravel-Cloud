@extends('layouts.admin')
@section('content')
@can('user_create')
    <div style="margin-bottom: 10px;" class="row">
        {{-- <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.users.create") }}">
                {{ trans('global.add') }} {{ trans('global.user.title_singular') }}
            </a>
        </div> --}}
    </div>
@endcan
<div class="card">


    <div class="card-header">
        Pilih Area
    </div>
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card-body">
        <form action="{{ route("admin.wablast.templateper") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category" value="{{ $category }}">
            {{-- <div class="form-group">
                <label for="message">Pesan</label>
                <textarea class="form-control" name="message" id="message" rows="3">{{ $message }}</textarea>
              </div> --}}
            <div class="row">
        @php
            $n = 0;
        @endphp
        @foreach ($areas as $area )
        <a id="{{ $n }}" class="btn bg-secondary pb-4 ml-5 mb-5 col-3 btn_removespeciality" style="border-radius: 25px;"><h1 class="mt-3">{{ $area->code }}</h1> <br> <h6>{{ $area->NamaWilayah }}</h6></a>
        <input type="hidden" id="area{{ $n }}" name="area[]" value="{{ $area->code }}" id="name" disabled>
        @php
            $n = $n+1;
        @endphp
        @endforeach
        
        {{-- <div class="bg-secondary pb-5 ml-5 mb-5 col-5"><h1>Keluhan</h1></div>
        <div class="bg-secondary pb-5 ml-5 mb-5 col-5"><h1>Kat 3</h1></div>
        <div class="bg-secondary pb-5 ml-5 mb-5 col-5"><h1>Kat 4</h1></div> --}}
    </div>
    <input class="btn btn-danger btn-lg btn-block" type="submit" value="Lanjut">
   
</form>
    </div>
</div>
@endsection


@section('scripts')
<script>

$(document).ready(function() {
      $("#message").emojioneArea();
    });

 $(document).on('click','.btn_removespeciality', function(){ 
                var button_id = $(this).attr("id"); 
                // alert("kkkk");
               
                if(document.getElementById("area"+button_id).disabled === true){
                    $(this).removeClass('bg-secondary').addClass('bg-primary');
                    document.getElementById("area"+button_id).disabled = false;
                }
                else{
                    $(this).removeClass('bg-primary').addClass('bg-secondary');
                    document.getElementById("area"+button_id).disabled = true;
                }

 
 
});
  $(document).ready(function(){
    let row_number = {{ count(old('customers', [''])) }};
    $("#add_row").click(function(e){
      e.preventDefault();
      let new_row_number = row_number - 1;
      $('#customer' + row_number).html($('#customer' + new_row_number).html()).find('td:first-child');
      $('#customers_table').append('<tr id="customer' + (row_number + 1) + '"></tr>');
      row_number++;

      function myFunction() {
  var x = document.getElementById("amountLabel");
  var amount = $("#amount"+ new_row_numbe).val();
//   var angka = document.getElementById("maximum");

    x.innerHTML = convertToRupiah(amount);
}
    });

    $("#delete_row").click(function(e){
      e.preventDefault();
      if(row_number > 1){
        $("#customer" + (row_number - 1)).html('');
        row_number--;
      }
    });
  });

//   function myFunction() {
//   var x = document.getElementById("amountLabel");
//   var amount = $("#amount"+0).val();
// //   var angka = document.getElementById("maximum");

//     x.innerHTML = convertToRupiah(amount);
// }
// //js untuk ubah ke rupiah
// function convertToRupiah(angka)
// {
// 	var rupiah = '';		
// 	var angkarev = angka.toString().split('').reverse().join('');
// 	for(var i = 0; i < angkarev.length; i++) if(i%3 == 0) rupiah += angkarev.substr(i,3)+'.';
// 	return 'Rp. '+rupiah.split('',rupiah.length-1).reverse().join('');
// }

</script>
@endsection
