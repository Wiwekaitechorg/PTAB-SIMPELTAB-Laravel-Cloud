@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Update Nomor Pelanggan dengan file Excel
    </div>

    <div class="card-body">
        <form action="{{ route("admin.customers.updateImport") }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.categoryWA.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($code) ? $code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.categoryWA.fields.code_helper') }}
                </p>
            </div> --}}
  
<label for="example">Download Contoh :</label>
              <a href="{{asset('files/exampleCustomer.xlsx')}}" download class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Download Berkas </a>

                {{-- upload image --}}
                <div class="form-group {{ $errors->has('file') ? 'has-error' : '' }}">
                    <label for="file">File</label>
                    <div class="custom-file">
                        <input id="file" name="file" type="file" class="custom-file-input" id="customFile">
                        <label class="custom-file-label" for="customFile">Masukan File (EXCEL)</label>
                      </div>
                                {{-- @if($errors->has('file'))
                        <em class="invalid-feedback">
                            {{ $errors->first('file') }}
                        </em>
                    @endif
                    <p class="helper-block">
                        {{ trans('global.patient.fields.file_helper') }}
                    </p> --}}
                </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script type="text/javascript">
  $(".custom-file-input").on("change", function() {
var fileName = $(this).val().split("\\").pop();
$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});


</script>
@endsection