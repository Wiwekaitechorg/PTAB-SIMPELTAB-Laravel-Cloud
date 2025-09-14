@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.report.reportProposalWmproses') }}" method="POST" target="_blank" enctype="multipart/form-data" >
        @csrf
        <div class="row">
            <div class="col-lg-5">
                <div class="form-group">
               
                </div>
                
                             {{-- register --}}
<label for="type">Pilih Bulan</label>
<div class='input-group' id='dpRM'>
    <input type='text' name="monthyear" id="monthyear" class="form-control form-control-1 form-input input-sm fromq" placeholder="Enter Month and year" required  />
    <span class="input-group-addon">
        {{-- <span class="fa fa-calendar"></span> --}}
    </span>
</div>
<br>


<label for="type">Wilayah</label>
<select id="areas" name="areas" class="form-control">
    <option value="">== Semua area ==</option>
    @foreach ($areas as $item )
    <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}</option>
    @endforeach
</select>

              
            </div>
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