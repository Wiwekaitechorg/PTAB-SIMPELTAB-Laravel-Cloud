@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.proposalwm.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.statuswm.approve") }}" method="POST" enctype="multipart/form-data">
            @csrf

            @for ($i = 0 ;$i < count($data) ; $i++  )
                {{-- <div class="">https://www.ptab-vps-storage.com/pdam.{{ $data->filegambar }}</div> --}}
                <img src="https://www.ptab-vps-storage.com/pdam{{ $data[$i]['filegambar'] }}" width="300" alt="">
                <div class="">Periode : {{ $data[$i]['bulanrekening']}}  - {{ $data[$i]['tahunrekening']}}</div>
                <div class="">Status : {{ $data[$i]['status_wm'] }}</div>
                <div class="">Operator : {{ $data[$i]['operator'] }}</div>
                <p class="my-2"><a href="https://www.ptab-vps-storage.com/pdam{{ $data[$i]['filegambar'] }}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
            @endfor
            <br>
            {{-- <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.proposalwm.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($code) ? $code : '') }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.code_helper') }}
                </p>
            </div> --}}
            {{-- <div class="form-group {{ $errors->has('id') ? 'has-error' : '' }}">
                <label for="id">{{ trans('global.proposalwm.fields.id') }}*</label>
              
                @if($errors->has('id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.customer_id_helper') }}
                </p>
            </div> --}}

            <input type="hidden" id="customer_id" name="customer_id" class="form-control" value="{{ old('customer_id', isset($now->customer_id) ? $now->customer_id : '') }}" readonly required>
            <input type="hidden" id="month" name="month" class="form-control" value="{{  $now->bulan }}" required>
            <input type="hidden" id="year" name="year" class="form-control" value="{{  $now->tahun }}" required>
            <input type="hidden" id="status_wm" name="status_wm" class="form-control" value="{{  $now->status_wm_id }}" required>

            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.proposalwm.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($now) ? $now->name : '') }}" readonly required>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.name_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('NamaStatus') ? 'has-error' : '' }}">
                <label for="NamaStatus">{{ trans('global.proposalwm.fields.status') }}*</label>
                <input type="text" id="NamaStatus" name="NamaStatus" class="form-control" value="{{ old('NamaStatus', isset($now) && $now->NamaStatus ? $now->NamaStatus : '') }}" readonly required>
                @if($errors->has('NamaStatus'))
                    <em class="invalid_wm-feedback">
                        {{ $errors->first('NamaStatus') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.status_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('nomorrekening') ? 'has-error' : '' }}">
                <label for="nomorrekening">{{ trans('global.proposalwm.fields.nomorrekening') }}*</label>
                <input type="text" id="nomorrekening" name="customer_id" class="form-control" value="{{ old('nomorrekening', isset($now) ? $now->nomorrekening : '') }}" readonly required>
                @if($errors->has('nomorrekening'))
                    <em class="invalid_wm-feedback">
                        {{ $errors->first('nomorrekening') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.nomorrekening_helper') }}
                </p>
            </div>


            {{-- <div class="form-group {{ $errors->has('subdapertement_id') ? 'has-error' : '' }}">
                <label for="subdapertement_id">{{ trans('global.proposalwm.fields.subdapertement_id') }}*</label>
                <select id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($now) ? $now->subdapertement_id : '') }}" required>
                    <option value="">--Pilih category--</option>
                        <option value="10">Pergantian Water Meter</option>
                        <option value="9">Pergantian Stop Keran</option>
                </select>
                @if($errors->has('subdapertement_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('subdapertement_id') }}
                    </em>
                @endif
            </div> --}}

            {{-- @if ($role === 8)
<div class="form-group {{ $errors->has('subdapertement_id') ? 'has-error' : '' }}">
    <label for="subdapertement_id">{{ trans('global.proposalwm.fields.subdapertement_id') }}*</label>
    <select id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($actionWm) ? $actionWm->subdapertement_id : '') }}" required>
        <option value="">--Pilih subdapertement id--</option>
        @foreach ($subdapertement as $data )
        <option value="{{ $data->id }}">{{ $data->dapertement->name }} || {{ $data->name }}</option>
        @endforeach
           
    </select>
    @if($errors->has('subdapertement_id'))
        <em class="invalid-feedback">
            {{ $errors->first('subdapertement_id') }}
        </em>
    @endif
</div>

@else
<input type="hidden" name="subdapertement_id" value="{{ $subdapertement }}">
@endif --}}

            {{-- <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                <label for="category">{{ trans('global.proposalwm.fields.category') }}*</label>
                <select id="category" name="category" class="form-control" value="{{ old('category', isset($actionWm) ? $actionWm->category : '') }}" required>
                    <option value="">--Pilih category--</option>
                        <option value="Pergantian Water Meter">Pergantian Water Meter</option>
                        <option value="Pergantian Stop Keran">Pergantian Stop Keran</option>
                </select>
                @if($errors->has('category'))
                    <em class="invalid-feedback">
                        {{ $errors->first('category') }}
                    </em>
                @endif
            </div> --}}

            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.proposalwm.fields.memo') }}*</label>
                <textarea type="text" id="memo" name="memo" class="form-control" value=""> {{ old('memo', isset($now) ? $now->memo : '') }}</textarea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('priority') ? 'has-error' : '' }}">
                <label for="priority">{{ trans('global.proposalwm.fields.priority') }}*</label>
                <select id="priority" name="priority" class="form-control" value="{{ old('priority', isset($now) ? $now->priority : '') }}" required>
                    <option value="">--Pilih Prioritas--</option>
                        {{-- <option value="1" >Low</option> --}}
                        <option value="2" >Medium</option>
                        <option value="3" >High</option>
                </select>
                @if($errors->has('priority'))
                    <em class="invalid-feedback">
                        {{ $errors->first('priority') }}
                    </em>
                @endif
            </div>


            <div class="row">
                <a class="btn btn-danger" href="{{ route("admin.statuswm.reject", ["customer_id"=>$now->nomorrekening ,"month"=>$now->bulan,"year"=>$now->tahun,"status_wm"=>$now->status_wm_id]) }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                    Tolak
                </a>
            <div>
                
                <input class="btn btn-primary" style="margin-left: 10px" type="submit" name = "approve" value="Teruskan">
            </div>
        </div>
        </form>
    </div>
</div>

@endsection