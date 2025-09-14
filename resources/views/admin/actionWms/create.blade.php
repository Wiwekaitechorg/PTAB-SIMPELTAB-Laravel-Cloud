@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.actionWms.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.actionWms.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
<input type="hidden" name="proposal_wm_id" value="{{ $id }}">

@if (in_array('8', $roles))
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
<input type="hidden" name="subdapertement_id" value="{{ $subdapertement_id }}">
@endif

            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
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
            </div>

            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.action.fields.memo') }}*</label>
                <textArea id="memo" name="memo" class="form-control" required>{{ old('name', isset($actionWm) ? $actionWm->memo : '') }}</textArea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
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