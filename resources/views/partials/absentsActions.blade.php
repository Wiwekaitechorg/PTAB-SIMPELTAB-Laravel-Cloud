@can($editGate)
    <a class="btn btn-xs btn-info" href="{{ route('admin.' . $crudRoutePart . '.presence', [$row->id,$register_def,$schedule_subject_id]) }}">
        Absen
    </a>
    <a class="btn btn-xs btn-info" href="{{ route('admin.' . $crudRoutePart . '.bill', [$row->id,$register_def,$schedule_subject_id]) }}">
        Bayar
    </a>
@endcan