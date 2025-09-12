@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Pilih Kategori
    </div>

    <div class="card-body">
      @if (session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
        <form action="{{ route("admin.wablast.templateP") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
        @php
            $n = 1;
        @endphp
        @foreach ($categorys as $category )
        <a id="{{ $n }}" class="btn bg-secondary pb-4 ml-5 mb-5 col-5 btn_removespeciality" style="border-radius: 25px;"><h1 class="mt-3">{{ $category->name }}</h1></a>
        <input type="hidden" id="category{{ $n }}" name ="category" id="name" value="{{ $category->id }}" disabled>
        <input type="hidden" id="name{{ $n }}" name ="name" id="name" value="{{ $category->name }}" disabled>
        <input type="hidden" name="chk">
        @php
            $n = $n+1;
        @endphp
        @endforeach
    </div>
        <input class="btn btn-danger btn-lg btn-block" type="submit" value="Lanjut">
   
</form>
        {{-- <div class="bg-secondary pb-5 ml-5 mb-5 col-5"><h1>Keluhan</h1></div>
        <div class="bg-secondary pb-5 ml-5 mb-5 col-5"><h1>Kat 3</h1></div>
        <div class="bg-secondary pb-5 ml-5 mb-5 col-5"><h1>Kat 4</h1></div> --}}
       
    </div>
</div>
@endsection


@section('scripts')
<script>

 $(document).on('click','.btn_removespeciality', function(){ 
                var button_id = $(this).attr("id"); 
                // alert("kkkk");
                var cek = document.getElementsByName('chk');
    //    alert(cek.length);
      
           for (var i = 1; i <= cek.length; i++) {
            if("category"+button_id == "category"+i){
            $(this).removeClass('bg-secondary').addClass('bg-primary');
                    document.getElementById("category"+button_id).disabled = false;
                    document.getElementById("name"+button_id).disabled = false;
           }
           else{
            $('#'+i).removeClass('bg-primary').addClass('bg-secondary');
            document.getElementById("category"+i).disabled = true;
            document.getElementById("name"+i).disabled = true;
           }
        }
       
                // if(document.getElementById("category"+button_id).disabled === true){
                //     $(this).removeClass('bg-secondary').addClass('bg-primary');
                //     document.getElementById("category"+button_id).disabled = false;
                // }
                // else{
                //     $(this).removeClass('bg-primary').addClass('bg-secondary');
                //     document.getElementById("category"+button_id).disabled = true;
                // }

 
 
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
