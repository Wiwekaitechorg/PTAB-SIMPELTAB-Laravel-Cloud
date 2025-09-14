<a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.show', ['nomorrekening'=> $row->nomorrekening]) }}">
        {{ trans('global.view') }}
    </a>

@can($editGate)
    <a class="btn btn-xs btn-info" href="{{ route('admin.' . $crudRoutePart . '.edit', ['nomorrekening'=> $row->nomorrekening]) }}">
        {{ trans('global.edit') }}
    </a>
@endcan