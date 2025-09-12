@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{-- {{ trans('global.create') }} {{ trans('global.ledger.title_singular') }} --}}
    </div>
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="card-body">
        <form action="{{ route("admin.tunggakan.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="status" value="{{ $data['status'] }}">
            <input type="hidden" name="type" value="{{ $data['type'] }}">
            <div class="form-group {{ $errors->has('template') ? 'has-error' : '' }}">
              <label for="template">{{ trans('global.user.fields.template') }}*</label>
              <input type="text" id="template" name="template" class="form-control" value="{{ $data['template'] }}">
              @if($errors->has('template'))
                  <em class="invalid-feedback">
                      {{ $errors->first('template') }}
                  </em>
              @endif
              <p class="helper-block">
                  {{ trans('global.user.fields.template_helper') }}
              </p>
          </div>

          <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
            <label for="message">{{ trans('global.user.fields.message') }}*</label>
            <input type="text" id="message" name="message" class="form-control" value="{{ $data['template'] }}">
            @if($errors->has('message'))
                <em class="invalid-feedback">
                    {{ $errors->first('message') }}
                </em>
            @endif
            <p class="helper-block">
                {{ trans('global.user.fields.message_helper') }}
            </p>
        </div>

            <div class="form-group">
                <label for="exampleFormControlTextarea1">Pesan</label>
                <textarea class="form-control" name="message" id="exampleFormControlTextarea1" rows="3">{{ $data['message'] }}</textarea>
              </div>

              <div class="container">
              {{-- <div class="row">
                <div class="form-group col-sm">
                    <label for="exampleFormControlSelect1">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">== Semua Status ==</option>
                        <option value="pending">Pending</option>
                        <option value="lock_resist">Hambatan Segel</option>
                        <option value="lock">Segel</option>
                        <option value="unplug_resist">Hambatan Cabut</option>
                        <option value="unplug">Cabut</option>
                    </select>
                  </div>

                  <div class="form-group col-sm">
                    <label for="exampleFormControlSelect1">== Area ==</label>
                    <select id="area" name="area" class="form-control">
                        <option value="">== Semua Area ==</option>
                        <option value="Tabanan">Tabanan</option>
                        <option value="Badung">Badung</option>
                        <option value="Denpasar">Denpasar</option>
                    </select>
                  </div>

                </div>
              </div> --}}
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
 </div>

@endsection


@section('scripts')
<script>

function checkAll(ele) {
       var cek = document.getElementsByName('chk');
       var checkboxes = document.getElementsByTagName('input');
       alert(cek.length);
       if (ele.checked) {
           for (var i = 1; i <= cek.length; i++) {
            document.getElementById(i).checked = true;
                   document.getElementById("name"+i).disabled = false;
                  document.getElementById("adress"+i).disabled = false;
           }
       } else {
           for (var i = 1; i <= cek.length; i++) {
            document.getElementById(i).checked = false;
                   document.getElementById("name"+i).disabled = true;
                  document.getElementById("adress"+i).disabled = true;
               
           }
       }
   }

 $(document).on('click','.btn_removespeciality', function(){ 
                var button_id = $(this).attr("id"); 
                // alert("kkkk");
               
                if(document.getElementById("name"+button_id).disabled === true){
                  // alert(document.getElementById("name"+button_id).value);
                  document.getElementById("name"+button_id).disabled = false;
                  document.getElementById("adress"+button_id).disabled = false;
                }
                else{
                  // alert('gagal');
                  document.getElementById("name"+button_id).disabled = true;
                  document.getElementById("adress"+button_id).disabled = true;
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
