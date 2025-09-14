@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Export Pelanggan to Excel
    </div>

    <div class="card-body">
        <form action="{{ route('admin.opp.reportExcel') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="input-group">
                              <select id="staff" name="staff" class="form-control">
                                  <option value="">== Semua Staff ==</option>
                                  @foreach ($staff as $item )
                                  <option value="{{ $item->Name }}">{{ $item->Name }}</option>
                                  @endforeach
                              </select>
                          </div>

                          <div class="input-group">
                        <input class="form-control" id="from" type="date" name="from" value="{{ request()->input('from') }}">
                        </div>
                        <div class="input-group">
                        <input class="form-control" id="to" type="date" name="to" value="{{ request()->input('to') }}">
                        </div>
                        <div class="input-group">
                    <select id="status" name="status" class="form-control">
                        <option value="semua">== Semua Status ==</option>
                        <option value="belum">Belum</option>
                        <option value="sudah">Sudah</option>
                    </select>
                </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
