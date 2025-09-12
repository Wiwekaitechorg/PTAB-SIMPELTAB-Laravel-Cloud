@extends('layouts.admin')
@section('content')
@can('permission_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.permissions.create") }}">
                {{ trans('global.add') }} {{ trans('global.permission.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        Nomor Telp Penerima Wa (Absen)
    </div>

    <div class="card-body">

        <div class="">
            <form action="{{ route("admin.waReceives.store") }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group {{ $errors->has('no_telp') ? 'has-error' : '' }}">
                    <label for="no_telp">No telp*</label>
                    <input type="text" id="no_telp" placeholder="Masukan Nomor Telepon" name="no_telp" class="form-control" value="{{ old('no_telp', isset($permission) ? $permission->no_telp : '') }}">
                    @if($errors->has('no_telp'))
                        <em class="invalid-feedback">
                            {{ $errors->first('no_telp') }}
                        </em>
                    @endif
                    <p class="helper-block">
                        {{-- {{ trans('global.permission.fields.no_telp_helper') }} --}}
                    </p>
                </div>
                <div>
                    <input class="btn btn-danger" type="submit" value="Tambah">
                </div>
            </form>
        </div>

        <div class="table-responsive mt-5">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">
No
                        </th>
                        <th>
                            No Telp
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waReceives as $key => $data)
                        <tr name= "test" data-entry-id="1">
                            <td>
                                    {{ $key += 1 }}
                            </td>
                            <td>
                                {{ $data->no_telp ?? '' }}
                            </td>
                            <td>
                                <form action="{{ route('admin.waReceives.destroy', $data->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                </form>
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

@endsection
@endsection