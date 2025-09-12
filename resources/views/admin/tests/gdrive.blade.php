@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        GDrive Test
    </div>

    <div class="card-body">
        <form action="{{route('admin.test.gdriveStore')}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')            

  <div id="1" >
    <div class="form-group">
        <label for="image_file">Foto Sebelum Pengerjaan</label>
        <div class="custom-file"><input id="image_file" name="image" type="file" class="custom-file-input" id="customFile">
            <label class="custom-file-label" for="customFile">Choose file</label>
        </div>
    </div>
    
    <div class="row">
            <div class="col-md-5">
                <img src="{{ route('admin.test.gdriveImage','test/1728213424_1005') }}" alt="" title="">
                        <p class="my-2"><a href="https://lh3.google.com/u/0/d/{{$fileid}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                    </div>
        </div>
    
    <div class="row">
            <div class="col-md-5">
                        <img  height="250px" width="350px"  src="https://lh3.google.com/u/0/d/{{$fileid}}" alt="">
                        <p class="my-2"><a href="https://lh3.google.com/u/0/d/{{$fileid}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                    </div>
        </div>

    </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
@endsection
