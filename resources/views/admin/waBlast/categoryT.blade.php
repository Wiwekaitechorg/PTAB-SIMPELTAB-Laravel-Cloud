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
        Pilih Tipe Tunggakan
    </div>

    <div class="card-body">
        
        <form action="{{ route("admin.wablast.templatet") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category" value="{{ $category }}">
            <div class="row">
        {{-- @php
            $n = 1;
        @endphp --}}
        {{-- @foreach ($categorys as $template ) --}}
       
        <a id="1" class="btn bg-secondary pb-4 ml-5 mb-5 col-5 btn_removespeciality" style="border-radius: 25px;"><h1 class="mt-3">Penyampaian Surat</h1></a>
        <input type="hidden" id="status1" name ="status" value="2" disabled>
        <input type="hidden" id="type1" name ="type" value="notice" disabled>
        <input type="hidden" name="chk">
        <a id="2" class="btn bg-secondary pb-5 ml-5 mb-5 col-5 btn_removespeciality" style="border-radius: 25px;"><h1>Penyegalan</h1></a>
        <input type="hidden" id="status2" name ="status" value="3" disabled>
        <input type="hidden" id="type2" name ="type" value="lock" disabled>
        <input type="hidden" name="chk">
        <a id="3" class="btn bg-secondary pb-5 ml-5 mb-5 col-5 btn_removespeciality" style="border-radius: 25px;"><h1>Kunjungan</h1></a>
        <input type="hidden" id="status3" name ="status" value="3" disabled>
        <input type="hidden" id="type3" name ="type" value="notice2" disabled>
        <input type="hidden" name="chk">
        <a id="4" class="btn bg-secondary pb-5 ml-5 mb-5 col-5 btn_removespeciality" style="border-radius: 25px;"><h1>Pencabutan</h1></a>
        <input type="hidden" id="status4" name ="status" value="4" disabled>
        <input type="hidden" id="type4" name ="type" value="unplug" disabled>
       
        <input type="hidden" name="chk">
        {{-- @php
            $n = $n+1;
        @endphp --}}
        {{-- @endforeach --}}
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
            if("template"+button_id == "template"+i){
            $(this).removeClass('bg-secondary').addClass('bg-primary');
                    document.getElementById("status"+button_id).disabled = false;
                    document.getElementById("type"+button_id).disabled = false;
           }
           else{
            $('#'+i).removeClass('bg-primary').addClass('bg-secondary');
            document.getElementById("status"+i).disabled = true;
            document.getElementById("type"+i).disabled = true;
           }
        }
       
                // if(document.getElementById("template"+button_id).disabled === true){
                //     $(this).removeClass('bg-secondary').addClass('bg-primary');
                //     document.getElementById("template"+button_id).disabled = false;
                // }
                // else{
                //     $(this).removeClass('bg-primary').addClass('bg-secondary');
                //     document.getElementById("template"+button_id).disabled = true;
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
