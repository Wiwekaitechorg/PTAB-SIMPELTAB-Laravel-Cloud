{{-- @can($viewGate) --}}
{{-- <a class="btn btn-xs btn-danger" href="{{ route('admin.' . $crudRoutePart . '.edit', $row->id) }}">
    edit
</a>
    <a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.approve', $row->id) }}">
        Setujui
    </a>
    <a class="btn btn-xs btn-danger" href="{{ route('admin.' . $crudRoutePart . '.reject', $row->id) }}">
        Tolak
    </a> --}}
{{-- @endcan --}}


@can($viewGate)
<a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart .'.index5YearDetail', $row->customer_id) }}">
    Show
</a>
@endcan
