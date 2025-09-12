@can($viewGate)
    <a class="btn btn-xs btn-primary" href="{{ route('admin.absents.list', [$row->id, $row->grade_id]) }}">
        Absensi
    </a>
@endcan