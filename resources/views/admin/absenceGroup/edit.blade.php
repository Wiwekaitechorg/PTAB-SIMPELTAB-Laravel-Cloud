@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.absence.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.absencegroup.update", $absence->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
          
            <div class="form-group {{ $errors->has('status_active') ? 'has-error' : '' }}">
                <label for="status_active">Status*</label>
                <select id="status_active" name="status_active" class="form-control">
                    <option value="" @if ($absence->status_active =='')
                        selected
                    @endif >Masuk</option>
                        <option value="2"  @if ($absence->status_active =='2')
                            selected
                        @endif>Lembur Mendesak</option>
                        <option value="3"  @if ($absence->status_active =='3')
                            selected
                        @endif>Permisi Tidak Kembail</option>
                        <option value="4"  @if ($absence->status_active =='4')
                            selected
                        @endif>Dianggap Tidak Hadir</option>
                </select>
                @if($errors->has('status_active'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status_active') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <textArea id="description" name="description" class="form-control" >{{$absence->description}}</textArea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{-- {{ $errors->first('description') }} --}}
                    </em>
                @endif
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection