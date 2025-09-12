@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.absence.absenceDelete') }}" method="POST" target="_blank" enctype="multipart/form-data" >
        @csrf
        <div class="row">
            <div class="col-lg-5">
                <div class="form-group">
               
                </div>
                
                             {{-- register --}}
{{-- <label for="type">Pilih Bulan</label>
<div class='input-group' id='dpRM'>
    <input type='text' name="monthyear" id="monthyear" class="form-control form-control-1 form-input input-sm fromq" placeholder="Enter Month and year" required  />
    <span class="input-group-addon"> --}}
        {{-- <span class="fa fa-calendar"></span> --}}
    {{-- </span>
</div>
<br> --}}

<div class="col-md-12">
    <div class="form-group">
        <label>Dari Tanggal</label>
        <div class="input-group date">
            <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
            </div>
            <input id="from" placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "">
        </div>
    </div>
    <div class="form-group">
        <label>Sampai Tanggal</label>
        <div class="input-group date">
            <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
            </div>
            <input id="to" placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{date('Y-m-d')}}">
        </div>
    </div>



</div>  

{{-- <label for="type">Wilayah</label>
<select id="areas" name="areas" class="form-control">
    <option value="">== Semua area ==</option>
    @foreach ($areas as $item )
    <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}</option>
    @endforeach
</select> --}}

              
            </div>
        </div>
<br>
        <Button  type="submit"  class="btn btn-danger" value="Proses" >Hapus</Button>

            </form>
@endsection
@parent
@section('scripts')

<script>
    
$('#dpRM').datetimepicker({
    viewMode : 'months',
    format : 'YYYY-MM',
    toolbarPlacement: "top",
    allowInputToggle: true,
    icons: {
        time: 'fa fa-time',
        date: 'fa fa-calendar',
        up: 'fa fa-chevron-up',
        down: 'fa fa-chevron-down',
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-screenshot',
        clear: 'fa fa-trash',
        close: 'fa fa-remove',
    }
});

$("#dpRM").on("dp.show", function(e) {
   $(e.target).data("DateTimePicker").viewMode("months"); 
});
</script>
@endsection