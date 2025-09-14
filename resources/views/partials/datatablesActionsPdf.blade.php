{{-- @can($viewGate) --}}
    <a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.create', ['id' => $row->nomorrekening, 'tunggak' => $row->jumlahtunggakan]) }}">
        Buat Surat
    </a>
{{-- @endcan --}}
