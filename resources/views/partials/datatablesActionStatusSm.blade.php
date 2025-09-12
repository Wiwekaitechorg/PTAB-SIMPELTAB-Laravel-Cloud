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


{{-- @can('proposalWm_approve') --}}
@if(in_array('14', $roles) || in_array('17', $roles))
@if ($row->customer_id == "")
<a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.create', ['customer_id'=>$row->nomorrekening, 'month'=>$row->bulan, 'year'=>$row->tahun]) }}">
    Lihat
</a> 
{{-- <form action="{{ route('admin.' . $crudRoutePart . '.updatestatus') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="id" value="{{ $row->id }}">
    <input type="hidden" name="status" value="reject">
    <input type="submit" class="btn btn-xs btn-danger" value="Tolak">
</form> --}}
@else
@if($row->proposalwm_status == "reject")
Ditolak
@else
Sudah Diteruskan
@endif
@endif
@endif


{{-- @endcan --}}


