@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.report.reportLockActionproses') }}" method="POST" target="_blank" enctype="multipart/form-data" >
        @csrf
        <div class="row">
            <div class="col-lg-5">
                <div class="form-group">
               
                </div>
                
                <input type = "hidden" name="take" value="{{ $jum }}">
                <label for="exampleFormControlSelect1">Rekap Ke</label>
                <select id="jum" name="jum" class="form-control">
                    <option value="">== Pilih Rekap ==</option>
                    @for($i=0 ;$i<$jum;$i++ )
                    <option value="{{ $i }}" >{{ $i+1 }}</option>
                    @endfor
                   
                   
                </select>
<br>



             
              
            </div>
        </div>

        <Button  type="submit"  class="btn btn-primary" value="Proses" >Proses</Button>

      </form>
@endsection
@section('scripts')
@parent
<script>
</script>
