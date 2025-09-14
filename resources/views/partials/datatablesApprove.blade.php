
@can($approveGate)
    <form action="{{ route('admin.' . $crudRoutePart . '.approve') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="id" value="{{ $row->id }}">
        <input type="submit" class="btn btn-xs btn-info" value="Setujui">
    </form>
@endcan

