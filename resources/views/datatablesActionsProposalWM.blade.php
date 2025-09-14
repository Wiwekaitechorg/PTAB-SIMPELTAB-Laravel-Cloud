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
@can($editGate)
<a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.edit', $row->id) }}">
    Edit
</a>
@endcan

@can($viewGate)
<a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.show', $row->id) }}">
    Show
</a>
@endcan

@if (($row->status == "active" || $row->status == "work" ) )

@can('actionWm_access')
<a class="btn btn-xs btn-success" href="{{ route('admin.' . 'actionWms' . '.index', $row->id) }}">
    Tindakan
</a>
@endcan
{{-- <form action="{{ route('admin.' . $crudRoutePart . '.updatestatus') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="id" value="{{ $row->id }}">
    <input type="hidden" name="status" value="pending">
    <input type="submit" class="btn btn-xs btn-warning" value="Restart Status">
</form> --}}
@elseif (($row->status == "close") )


@else

@can('proposalWm_approve')
<a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.approve', $row->id) }}">
    Setuju
</a>
@endcan

@can('proposalWm_approve')
<form action="{{ route('admin.' . $crudRoutePart . '.updatestatus') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="id" value="{{ $row->id }}">
    <input type="hidden" name="status" value="reject">
    <input type="submit" class="btn btn-xs btn-danger" value="Tolak">
</form>
@endcan
@endif

@can($deleteGate)
    <form action="{{ route('admin.' . $crudRoutePart . '.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
    </form>
@endcan

