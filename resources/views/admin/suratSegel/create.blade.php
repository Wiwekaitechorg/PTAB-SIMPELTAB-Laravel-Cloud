@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Buat Surat
    </div>

    <div class="card-body">
        <form action="{{ route("admin.suratsegel.suratPdf") }}" target="_blank" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">Kode*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($customer) ? $customer->nomorrekening : '') }}" required readonly>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.user.fields.name_helper') }}
                </p>
            </div>
            
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.user.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($customer) ? $customer->namapelanggan : '') }}" required readonly>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.user.fields.name_helper') }}
                </p>
            </div>
            {{-- <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">{{ trans('global.user.fields.email') }}*</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', isset($user) ? $user->email : '') }}">
                @if($errors->has('email'))
                    <em class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.user.fields.email_helper') }}
                </p>
            </div> --}}
            {{-- <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password">{{ trans('global.user.fields.password') }}</label>
                <input type="password" id="password" name="password" class="form-control">
                @if($errors->has('password'))
                    <em class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.user.fields.password_helper') }}
                </p>
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('dapertement_id') ? 'has-error' : '' }}">
                <label for="dapertement_id">{{ trans('global.staff.fields.dapertement') }}*</label>
                <select id="dapertement_id" name="dapertement_id" class="form-control" value="{{ old('dapertement_id', isset($user) ? $user->dapertement_id : '') }}">
                    <option value="">--Pilih Dapertement--</option>
                    @foreach ($dapertements as $key=>$dapertement )
                        <option value="{{$dapertement->id}}">{{$dapertement->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('dapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('dapertement_id') }}
                    </em>
                @endif
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('subdapertement_id') ? 'has-error' : '' }}">
                <label for="subdapertement_id">{{ trans('global.staff.fields.subdapertement') }}*</label>
                <select id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($user) ? $user->subdapertement_id : '') }}">
                    <option value="0">--Pilih Sub Depertement--</option>                    
                </select>
                @if($errors->has('subdapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('subdapertement_id') }}
                    </em>
                @endif
            </div> --}}
<input type="hidden" value="{{ $id }}" name='id'>
            <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                <label for="staff_id">{{ trans('global.staff.fields.name') }}*</label>
                <select id="staff_id" name="staff_id" class="form-control" value="{{ old('staff_id', isset($user) ? $user->staff_id : '') }}" required>
                    <option value="">--Pilih Staff--</option>
                    @foreach ($staff as $value)
                    <option value="{{ $value->id }}">{{ $value->name }}</option>  
                    @endforeach
                                  
                </select>
                @if($errors->has('staff_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('staff_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('dapertement') ? 'has-error' : '' }}">
                <label for="dapertement">{{ trans('global.staff.fields.name') }}*</label>
                <select id="dapertement" name="dapertement" class="form-control" value="{{ old('dapertement', isset($user) ? $user->dapertement : '') }}" required>
                    <option value="">--Dapertement--</option>
                    @foreach ($dapertement as $value)
                    <option value="{{ $value->name }}">{{ $value->name }}</option>  
                    @endforeach
                                  
                </select>
                @if($errors->has('dapertement'))
                    <em class="invalid-feedback">
                        {{ $errors->first('dapertement') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('jenisSurat') ? 'has-error' : '' }}">
                <label for="jenisSurat">Jenis Surat*</label>
                <select id="jenisSurat" name="jenis" class="form-control" value="{{ old('jenisSurat', isset($user) ? $user->jenisSurat : '') }}" required>
                    <option value="">--Jenis Surat--</option>
                    <option value="penyegelan">Penyegelan</option>

                    @if ($tunggak > 3)
                    <option value="pencabutan">Pencabutan</option>    
                    @endif
                    
                    <option value="perintahPenyegelan">Perintah Penyegelan</option>
                    
                    @if ($tunggak > 3)
                    <option value="perintahPencabutan">Perintah Pencabutan</option>
                        
                    @endif
                    
                    @if ($tunggak > 3)
                    <option value="hambatanPencabutan">Hambatan Pencabutan</option>    
                    @endif
                    
                    <option value="hambatanPenyegelan">Hambatan Penyegelan</option>
                                  
                </select>
                @if($errors->has('jenisSurat'))
                    <em class="invalid-feedback">
                        {{ $errors->first('jenisSurat') }}
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