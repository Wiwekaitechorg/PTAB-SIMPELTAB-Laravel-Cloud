@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Buat Absen(Khusus absen izin)
    </div>

    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <div class="form-group {{ $errors->has('reason') ? 'has-error' : '' }}">
                <label for="reason">Keterangan*</label>
                <input type="text" id="reason" name="reason" class="form-control" value="{{ old('reason', isset($reason) ? $reason : '') }}" required>
                @if($errors->has('reason'))
                    <em class="invalid-feedback">
                        {{ $errors->first('reason') }}
                    </em>
                @endif
                <p class="helper-block">
                   Keterangan
                </p>
            </div> --}}

            <div class="form-group {{ $errors->has('reason') ? 'has-error' : '' }}">
                <label for="reason">Keterangan*</label>
                <select id="reason" name="reason" class="form-control" value="{{ old('reason', isset($customer) ? $customer->reason : '') }}">
                    <option value="">--Pilih Keterangan--</option>
                    @for ($i = 0 ; $i < count($reason) ;$i++ )
                        <option value="{{$reason[$i]['id']}}">{{$reason[$i]['name']}}</option>
                    @endfor
                </select>
                @if($errors->has('reason'))
                    <em class="invalid-feedback">
                        {{ $errors->first('reason') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">Deskripsi*</label>
                <textArea id="description" name="description" class="form-control" required>{{ old('name', isset($attendance) ? $attendance->description : '') }}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        Deskripsi
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">Bukti</label>
                {{-- <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($ticket) ? $ticket->image : '') }}">
                @if($errors->has('image'))
                    <em class="invalid-feedback">
                        {{ $errors->first('image') }}
                    </em>
                @endif --}}
                <div class="input-group control-group increment" >
                    <input type="file" name="old_image[]" class="form-control">
                    <div class="input-group-btn"> 
                      {{-- <button class="btn btn-success" type="button"><i class="glyphicon glyphicon-plus"></i>Add</button> --}}
                    </div>
                  </div>
                  {{-- <div class="clone hide">
                    <div class="control-group input-group" style="margin-top:10px">
                      <input type="file" name="image[]" class="form-control">
                      <div class="input-group-btn"> 
                        <button class="btn btn-danger" type="button"><i class="glyphicon glyphicon-remove"></i> Remove</button>
                      </div>
                    </div>
                  </div> --}}
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection