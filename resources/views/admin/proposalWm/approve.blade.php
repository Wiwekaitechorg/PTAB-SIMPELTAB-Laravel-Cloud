@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.proposalwm.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.proposalwm.approveProses") }}" method="POST" enctype="multipart/form-data">
            @csrf
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

            <input type="hidden" id="id" name="id" class="form-control" value="{{ old('id', isset($id) ? $id : '') }}" readonly required>

            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.proposalwm.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($proposalWm) ? $proposalWm->name : '') }}" readonly required>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.name_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('status_wm') ? 'has-error' : '' }}">
                <label for="status_wm">{{ trans('global.proposalwm.fields.status_wm') }}*</label>
                <input type="text" id="status_wm" name="status_wm" class="form-control" value="{{ old('status_wm', isset($proposalWm) && $proposalWm->NamaStatus ? $proposalWm->NamaStatus : '') }}" readonly required>
                @if($errors->has('status_wm'))
                    <em class="invalid_wm-feedback">
                        {{ $errors->first('status_wm') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.status_wm_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('nomorrekening') ? 'has-error' : '' }}">
                <label for="nomorrekening">{{ trans('global.proposalwm.fields.nomorrekening') }}*</label>
                <input type="text" id="nomorrekening" name="nomorrekening" class="form-control" value="{{ old('nomorrekening', isset($proposalWm) ? $proposalWm->nomorrekening : '') }}" readonly required>
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
                <select id="subdapertement_id" name="subdapertement_id" class="form-control" value="{{ old('subdapertement_id', isset($proposalWm) ? $proposalWm->subdapertement_id : '') }}" required>
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

@else --}}
<input type="hidden" name="subdapertement_id" value="{{ $subdapertement }}">


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
                <textarea type="text" id="memo" name="memo" class="form-control" value=""> {{ old('memo', isset($proposalWm) ? $proposalWm->memo : '') }}</textarea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('priority') ? 'has-error' : '' }}">
                <label for="priority">{{ trans('global.proposalwm.fields.priority') }}*</label>
                <select id="priority" name="priority" class="form-control" value="{{ old('priority', isset($proposalWm) ? $proposalWm->priority : '') }}" required>
                    <option value="">--Pilih Priority--</option>
                        {{-- <option value="1" @if ($proposalWm && $proposalWm->priority === "1") selected @endif >Low</option> --}}
                        <option value="2" @if ($proposalWm && $proposalWm->priority === "2") selected @endif >Medium</option>
                        <option value="3" @if ($proposalWm && $proposalWm->priority === "3") selected @endif >high</option>
                </select>
                @if($errors->has('priority'))
                    <em class="invalid-feedback">
                        {{ $errors->first('priority') }}
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