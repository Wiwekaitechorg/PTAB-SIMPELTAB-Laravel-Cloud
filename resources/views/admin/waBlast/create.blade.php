@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{-- {{ trans('global.create') }} {{ trans('global.ledger.title_singular') }} --}}
    </div>
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    <div class="card-body">
        <form action="{{ route("admin.wablast.store") }}" method="POST" enctype="multipart/form-data">
            @csrf 
<input type="hidden" value="{{ $takeData }}" name="takeData">
            <div class="form-group">
                <label for="message">Pesan</label>
                {{-- sementara --}}
                <input type="hidden" value="{{ $data['template_id'] }}" name="template_id">
                <input type="hidden" value="{{ $file }}" name="file">
                <input type="hidden" value="{{ $image }}" name="image">
                <textarea class="form-control" name="message" id="message" rows="3">{{ $data['message'] }} </textarea>
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

              <div class="container">
              <div class="row">
                {{-- <div class="form-group col-sm">
                    <label for="exampleFormControlSelect1">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">== Semua Status ==</option>
                        <option value="pending">Pending</option>
                        <option value="lock_resist">Hambatan Segel</option>
                        <option value="lock">Segel</option>
                        <option value="unplug_resist">Hambatan Cabut</option>
                        <option value="unplug">Cabut</option>
                    </select>
                  </div> --}}

                  <div class="form-group col-sm">
                    <label for="exampleFormControlSelect1">Area</label>
                    <select id="area" name="area" class="form-control">
                        <option value="">== Semua Area ==</option>
                        @foreach ($areas as $area )
                        <option value="{{ $area->code }}" @if($area->code == request()->input('area'))
                          selected
                          
                        @endif >{{ $area->code }} || {{ $area->NamaWilayah }}</option>
                        @endforeach
                       
                       
                    </select>
                    <div class="form-group">
                      <label for="sbg" class="form-label">SBG</label>
                      <div class="">
                        <input type="SBG" class="form-control" name="nomorrekening" value="{{ request()->input('nomorrekening') }}" id="sbg" placeholder="No.SBG">
                      </div>
                    </div>
                  </div>

                </div>
              </div>
             
<button type="submit" name="filter" value="filter" class="btn btn-warning">Filter</button>
<br>
<th><input type="checkbox" onchange="checkAll(this)" name="chk[]" ></th> Pilih Semua

            <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">Pilih</th>
                    <th scope="col">Nomor Rekening</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Telepon</th>
                    <th scope="col">Alamat</th>
                
                  </tr>
                </thead>
                <tbody>
                 @php
                    $n =0
                 @endphp
                  @foreach ($customers as $item)
                  
                  <tr>
                    <td><input type="checkbox" name = "phone[]" value="{{ $item->telp }}" id="{{ $n+1 }}" class ="btn_removespeciality"></td>
                    <th scope="row">{{ $item->nomorrekening }}</th>
                    <td name="chk">{{ $item->name}}</td>
                    <td>{{ $item->telp }}</td>
                    <td>{{ $item->address }}</td>
                    <input type="hidden" id="customer_id{{ $n+1 }}" name="customer_id[]" value="{{ $item->nomorrekening }}" disabled>
                    <input type="hidden" id="name{{ $n+1 }}" name="name[]" value="{{ $item->name }}" disabled>
                    <input type="hidden" id="adress{{ $n+1 }}" name="adress[]" value="{{ $item->address }}" disabled>
                   @php
                      $n = $n+1
                   @endphp
                   
                  </tr>
                  @endforeach
                 
                  {{-- <tr>
                    <th scope="row">2</th>
                    <td>Jacob</td>
                    <td>Thornton</td>
                    <td>@fat</td>
                    <td><input type="checkbox" name = nomor[] value="081236815960"></td>
                  </tr> --}}
                  {{-- <tr>
                    <th scope="row">3</th>
                    <td>Larry</td>
                    <td>the Bird</td>
                    <td>@twitter</td>
                    <td><input type="checkbox" name = nomor[] value="081236815960"></td>
                  </tr> --}}
                </tbody>
              </table>
            <div>
                {{-- <nav aria-label="Page navigation example">
                    <ul class="pagination">
                      @foreach ($customers->links() as $data )
                      <li class="page-item"><a class="page-link" href="#">1</a></li>
                      @endforeach
                     
                    </ul>
                 
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                  </nav> --}}
                  {{ $customers->appends(['message' => $data['message'],'takeData'=>$takeData, 'area'=>request()->input('area'), 'file' => request()->input('file'), 'image' =>request()->input('image')])->links() }}
                <input class="btn btn-danger" name="send" type="submit">
            </div>
        </form>
    </div>
 </div>

@endsection


@section('scripts')
<script>

    $(document).ready(function() {
      $("#message").emojioneArea();
    });


function checkAll(ele) {
       var cek = document.getElementsByName('chk');
       var checkboxes = document.getElementsByTagName('input');
      //  alert(cek.length);
       if (ele.checked) {
           for (var i = 1; i <= cek.length; i++) {
            document.getElementById(i).checked = true;
                   document.getElementById("name"+i).disabled = false;
                  document.getElementById("adress"+i).disabled = false;
                  document.getElementById("customer_id"+i).disabled = false;
           }
       } else {
           for (var i = 1; i <= cek.length; i++) {
            document.getElementById(i).checked = false;
                   document.getElementById("name"+i).disabled = true;
                  document.getElementById("adress"+i).disabled = true;
                  document.getElementById("customer_id"+i).disabled = true;
               
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
                  document.getElementById("customer_id"+button_id).disabled = false;
                }
                else{
                  // alert('gagal');
                  document.getElementById("name"+button_id).disabled = true;
                  document.getElementById("adress"+button_id).disabled = true;
                  document.getElementById("customer_id"+button_id).disabled = true;
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
