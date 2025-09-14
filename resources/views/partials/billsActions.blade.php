@can($editGate)
    <a class="btn btn-xs btn-info" href="{{ route('admin.' . $crudRoutePart . '.paid', [$row->id,$period_def]) }}">
        {{ trans('global.edit') }}
    </a>
@endcan