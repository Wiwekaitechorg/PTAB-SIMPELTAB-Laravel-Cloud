@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.proposalwm.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.proposalwm.update",[$proposalwm->id]) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
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

            <div class="form-group {{ $errors->has('customer_id') ? 'has-error' : '' }}">
                <label for="customer_id">{{ trans('global.proposalwm.fields.customer_id') }}*</label>
                <input type="text" id="customer_id" name="customer_id" class="form-control" value="{{ old('customer_id', isset($proposalwm) ? $proposalwm->customer_id : '') }}" readonly required>
                @if($errors->has('customer_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.customer_id_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('status_wm') ? 'has-error' : '' }}">
                <label for="status_wm">{{ trans('global.proposalwm.fields.status_wm') }}*</label>
                <input type="text" id="status_wm" name="status_wm" class="form-control" value="{{ old('status_wm', isset($proposalwm) ? $proposalwm->status_wm : '') }}" readonly required>
                @if($errors->has('status_wm'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status_wm') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.proposalwm.fields.status_wm_helper') }}
                </p>
            </div>

            {{-- <div class="form-group {{ $errors->has('status_wm') ? 'has-error' : '' }}">
                <label for="status_wm">{{ trans('global.proposalwm.fields.status_wm') }}*</label>
                <select id="status_wm" name="status_wm" class="form-control" value="{{ old('status_wm', isset($proposalwm) ? $proposalwm->status_wm : '') }}" readonly required>
                    <option value="">--Pilih category--</option>
                        <option value="WM Rusak" @if ($proposalwm && $proposalwm->status_wm == "WM Rusak") selected @endif >WM Rusak</option>
                        <option value="WM Kabur" @if ($proposalwm && $proposalwm->status_wm == "WM Kabur") selected @endif >WM Kabur</option>
                        <option value="WM Mati" @if ($proposalwm && $proposalwm->status_wm == "WM Mati") selected @endif >WM Mati</option>
                </select>
                @if($errors->has('status_wm'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status_wm') }}
                    </em>
                @endif
            </div> --}}

            <div class="form-group {{ $errors->has('priority') ? 'has-error' : '' }}">
                <label for="priority">{{ trans('global.proposalwm.fields.priority') }}*</label>
                <select id="priority" name="priority" class="form-control" value="{{ old('priority', isset($proposalwm) ? $proposalwm->priority : '') }}" required>
                    <option value="">--Pilih Priority--</option>
                        <option value="2" @if ($proposalwm && $proposalwm->priority === "2") selected @endif >Medium</option>
                        <option value="3" @if ($proposalwm && $proposalwm->priority === "3") selected @endif >high</option>
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