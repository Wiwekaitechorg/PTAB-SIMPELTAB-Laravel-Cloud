@extends('layouts.admin')
@section('content')
@can('proposalKa_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.proposalKas.create") }}">
                {{ trans('global.add') }} {{ trans('global.proposalKa.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        Ka {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            Ka
                        </th>
                        <th>
                            Departemen
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proposalKas as $key => $proposalKa)
                        <tr name= "test" data-entry-id="1">
                            <td>

                            </td>
                            <td>
                                {{ $proposalKa->users->name ?? '(belum ditentukan)' }}
                            </td>
                            <td>
                                {{ $proposalKa->dapertements->name ?? '' }}
                            </td>
                            <td>
                                @can('proposal_ka_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.proposalKa.edit', $proposalKa->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script>
    $(function () {

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
})

</script>
@endsection
@endsection