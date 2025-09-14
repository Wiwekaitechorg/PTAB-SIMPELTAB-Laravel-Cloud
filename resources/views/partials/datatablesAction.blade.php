@can($viewGate)
    <a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.show', $row->nomorrekening) }}">
        {{ trans('global.view') }}
    </a>
@endcan
@can($editGate)
    <a class="btn btn-xs btn-info" href="{{ route('admin.' . $crudRoutePart . '.edit', $row->nomorrekening) }}">
        {{ trans('global.edit') }}
    </a>
@endcan
@if (isset($actionGate))
    @can($actionGate)
        <a class="btn btn-xs btn-info" href="{{ route('admin.actions.list', $row->nomorrekening) }}">
            {{ trans('global.action.title') }}
        </a>
    @endcan
@endif

@if (isset($staffGate))
    @can($staffGate)
        <a class="btn btn-xs btn-success" href="{{ route('admin.lock.actionStaff', $row->nomorrekening) }}">
            Tambah {{ trans('global.staff.title') }}
        </a>
    @endcan
@endif

@if (isset($actionLockGate))
    @can($actionLockGate)
        <a class="btn btn-xs btn-info" href="{{ route('admin.lock.list', $row->nomorrekening) }}">
            {{ trans('global.action.title') }}
        </a>
    @endcan
@endif

@if (isset($viewSegelGate))
    @can($viewSegelGate)
        <a class="btn btn-xs btn-primary" href="{{ route('admin.segelmeter.show', $row->customer_id) }}">
            {{ trans('global.view') }}
        </a>
    @endcan
@endif

<!-- @if (isset($print) && $print)
        <a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.print', $row->nomorrekening) }}">
            Print
        </a>
@endif -->
@can($deleteGate)
    <form action="{{ route('admin.' . $crudRoutePart . '.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
    </form>
@endcan


@if (isset($lockGate) && $lockGate == 1)
    <a class="btn btn-xs btn-info" href="{{ route('admin.lock.create',['id'=>$row->nomorrekening]) }}">
      Teruskan
    </a>
@endif