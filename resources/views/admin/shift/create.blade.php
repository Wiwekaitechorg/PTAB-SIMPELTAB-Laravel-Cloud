@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Buat Shift
    </div>

    <div class="card-body">
        <form action="{{ route("admin.shift.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                <label for="staff_id">Staff*</label>
                <select id="staff_id" name="staff_id" class="form-control" value="{{ old('staff_id', isset($shift_staff) ? $shift_staff->staff_id : '') }}">
                    <option value="">--Pilih Staff--</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
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
                    <option value="{{ $shift->id }}">{{ $shift->title }} || {{ $shift->dapertement_name }}</option>
                    @endforeach
           
                </select>
                @if($errors->has('shift_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('shift_id') }}
                    </em>
                @endif
            </div>

                 <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="register">Tanggal*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($register) ? $register : '') }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
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