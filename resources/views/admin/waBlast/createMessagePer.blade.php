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
        <form action="{{ route("admin.wablast.storemessageper") }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <input type="hidden" name="takeData" value="{{ $takeData }}">
            <div class="form-group {{ $errors->has('takeFrom') ? 'has-error' : '' }}">
              <label for="takeFrom">Data Group Ke*</label>
              <select id="takeFrom" name="takeFrom" class="form-control" required>
                  @for ($i = 0; $i< $takeFrom ;$i++ )
                  <option value="{{$i*$takeData}}">{{$i+1}}</option>
                  @endfor
              </select>
              @if($errors->has('takeFrom'))
                  <em class="invalid-feedback">
                      {{ $errors->first('takeFrom') }}
                  </em>
              @endif
          </div>   
           --}}
            {{-- @foreach ($area as $v )
            <input type="hidden" name="area[]" value="{{ $v }}">
            @endforeach --}}
            {{-- <input type="hidden" name="status" value="{{ $data['status'] }}"> --}}
            {{-- <input type="hidden" name="type" value="{{ $data['type'] }}"> --}}
            <input type="hidden" value="{{ $data['template_id'] }}" name="template_id">

            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
              <label for="category">{{ trans('global.wablast.fields.category') }}*</label>
              <input type="text" id="category" name="category" class="form-control" value="{{ $data['category'] }}" readonly>
              @if($errors->has('category'))
                  <em class="invalid-feedback">
                      {{ $errors->first('category') }}
                  </em>
              @endif
              <p class="helper-block">
                  {{ trans('global.wablast.fields.category_helper') }}
              </p>
          </div>

          {{-- <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
              <label for="type">{{ trans('global.wablast.fields.type') }}*</label>
              <input type="text" id="type" name="type" class="form-control" value="{{ $data['type'] }}" readonly>
              @if($errors->has('type'))
                  <em class="invalid-feedback">
                      {{ $errors->first('type') }}
                  </em>
              @endif
              <p class="helper-block">
                  {{ trans('global.wablast.fields.type_helper') }}
              </p>
          </div> --}}

          <div class="form-group {{ $errors->has('template') ? 'has-error' : '' }}">
            <label for="template">{{ trans('global.wablast.fields.template') }}*</label>
            <input type="text" name="template" class="form-control" value="{{ $data['template'] }}" readonly>
            @if($errors->has('template'))
                <em class="invalid-feedback">
                    {{ $errors->first('template') }}
                </em>
            @endif
            <p class="helper-block">
                {{ trans('global.wablast.fields.template_helper') }}
            </p>
        </div>

            <div class="form-group">
                <label for="message">Pesan</label>
                <textarea class="form-control" name="message" id="message" rows="3" required>{{ $data['message'] }}</textarea>
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

<th><input type="checkbox" onchange="checkAll(this)" ></th> Pilih Semua

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
        <td name="chk" >{{ $item->name}}</td>
        <td>{{ $item->telp }}</td>
        <td>{{ $item->adress }}</td>
        <input type="hidden" id="customer_id{{ $n+1 }}" name="customer_id[]" value="{{ $item->nomorrekening }}" disabled>
        <input type="hidden" id="name{{ $n+1 }}" name="name[]" value="{{ $item->name }}" disabled>
        <input type="hidden" id="adress{{ $n+1 }}" name="adress[]" value="{{ $item->adress }}" disabled>
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
      {{ $customers->appends(['data'=>['message' => $data['message'], 'template' => $data['template'], 'category' => $data['category'],'template_id' => $data['template_id']],'takeData'=>request()->input('takeData'), 'area'=>request()->input('area')])->links() }}
</div>
                <input class="btn btn-danger" type="submit" value="Send">
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
