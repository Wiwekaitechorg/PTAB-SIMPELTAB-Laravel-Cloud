@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.visit.report') }}" method="POST" target="_blank" enctype="multipart/form-data" >
        @csrf
            <div class="col-lg-5">
                <div class="form-group">
               
                </div>
                
                <label>Dari Tanggal</label>
                <div class="input-group date">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-th"></span>
                    </div>
                    <input placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "{{date('Y-m-d')}}">
                </div>
           
            <div class="form-group">
            <label>Sampai Tanggal</label>
                <div class="input-group date">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-th"></span>
                    </div>
                    <input placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{date('Y-m-d')}}">
                </div>
            </div>
     
       

<label for="type">Wilayah</label>
<select id="area" name="area" class="form-control">
    <option value="">== Semua area ==</option>
    @foreach ($areas as $item )
    <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}</option>
    @endforeach
</select>

</div>  
           
<br>
        <Button  type="submit"  class="btn btn-primary" value="Proses" >Proses</Button>

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