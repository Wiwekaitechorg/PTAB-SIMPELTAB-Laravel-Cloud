@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Edit Shift
    </div>

    <div class="card-body">
        <form action="{{ route("admin.shift.update", $shift_staff->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                <label for="staff_id">Staff*</label>
                <select id="staff_id" name="staff_id" class="form-control" value="{{ old('staff_id', isset($shift_staff) ? $shift_staff->staff_id : '') }}">
                    <option value="">--Pilih Staff--</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}"  @if ($shift_staff && $shift_staff->staff_id==$user->id) selected @endif>{{ $user->name }}</option>
                    @endforeach
           
                </select>
                @if($errors->has('staff_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('staff_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('shift_id') ? 'has-error' : '' }}">
                <label for="shift_id">shift*</label>
                <select id="shift_id" name="shift_id" class="form-control" value="{{ old('shift_id', isset($shift_staff) ? $shift_staff->shift_id : '') }}">
                    <option value="">--Pilih shift--</option>
                    @foreach ($shifts as $shift)
                    <option value="{{ $shift->id }}"     @if ($shift_staff && $shift_staff->shift_id==$shift->id) selected @endif>{{ $shift->title }} || {{ $shift->dapertement_name }}</option>
                    @endforeach
           
                </select>
                @if($errors->has('shift_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('shift_id') }}
                    </em>
                @endif
            </div>

                 <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                <label for="date">Tanggal*</label>
                <input type="date" id="date" name="date" class="form-control" value="{{ old('date', isset($shift_staff->date) ? $shift_staff->date : '') }}" required>
                @if($errors->has('date'))
                    <em class="invalid-feedback">
                        {{ $errors->first('date') }}
                    </em>
                @endif
                <p class="helper-block">
                   {{-- Tanggal --}}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection