@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
       Create Laporan Keuangan Audited
    </div>
    <div class="card-body">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <a href="{{Session::get('pdf')}}">
                    <div class="nav-icon fas fa-file-pdf" style="font-size:30px"></div>
                    <div>Lihat File</div>
                </a>
                <strong>{{ $message }}</strong>
            </div>
           
            @endif
            @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> Terdapat kesalahan dalam mengupload File.
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.file.upload.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.audited.fields.name') }}*</label>
                <input required type="text" id="name" name="name" class="form-control" >
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>

            <?php $years = range(2000, strftime("%Y", time())); ?>
            <div class="form-group {{ $errors->has('periode') ? 'has-error' : '' }}">
                <label for="periode">{{ trans('global.audited.fields.periode') }}*</label>
                <select id="periode" name="periode" class="form-control">
                    <option>-- Pilih Periode --</option>
                    @foreach ($years as $year)
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                    @endforeach
                </select>
                @if($errors->has('periode'))
                    <em class="invalid-feedback">
                        {{ $errors->first('periode') }}
                    </em>
                @endif
            </div>
            <div class="form-group">
                <label for="PDF">Laporan PDF</label>
                <input type="file" name="file" class="form-control">
            </div>
            <div>
                <button type="submit" class="btn btn-success">Upload</button>
            </div>
        </form>
    </div>
</div>

@endsection
