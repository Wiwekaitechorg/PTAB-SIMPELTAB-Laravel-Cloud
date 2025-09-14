@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.workPermit.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>

                {{-- <tr>
                    <th>
                        {{ trans('global.workPermit.fields.code') }}
                    </th>
                    <td>
                        {{ $workPermit->code }}
                    </td>
                </tr> --}}

                <tr>
                    <th>
                        {{ trans('global.workPermit.fields.staff_name') }}
                    </th>
                    <td>
                        {{ $workPermit->staff_name }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workPermit.fields.start') }}
                    </th>
                    <td>
                        {{ $workPermit->start }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workPermit.fields.end') }}
                    </th>
                    <td>
                        {{ $workPermit->end }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workPermit.fields.status') }}
                    </th>
                    <td>
                        {{ $workPermit->status }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workPermit.fields.category') }}
                    </th>
                    <td>
                        {{ $workPermit->category }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workPermit.fields.description') }}
                    </th>
                    <td>
                        {{ $workPermit->description }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workPermit.fields.type') }}
                    </th>
                    <td>
                        @if ($workPermit->type == "sick")
                            sakit;
                        @elseif ($workPermit->type == "sick_proof") 
                            sakit dengan keterang
                        @elseif ($workPermit->type == "other") 
                            Izin
                        @else 
                            -
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('global.workPermit.fields.file') }}
                    </th>
                    <th>
                    {{-- <div class="row"> --}}
                        @foreach ($file as $item)
                            {{-- @foreach (json_decode($image->image) as $item) --}}
                            <div class="col-md-5">
                                <img  height="250px" width="350px"  src="{{"https://simpletabadmin.ptab-vps-storage.com/images/RequestFile/$item->image"}}" alt="">
                                <p>{{ $item->type == "request_log_in" ? "Bukti Check In" : "Bukti Check Out" }}</p>
                                <p class="my-2"><a href="{{"https://simpletabadmin.ptab-vps-storage.com/images/RequestFile/$item->image"}}" target="_blank" class="btn btn-primary">Tampilkan</a></p>
                            </div>
                            {{-- @endforeach --}}
                        @endforeach

                        @if ($workPermit->type == 'sick' && $workPermit->status != 'pending' && $workPermit->status != 'reject')
<form action="{{ route('admin.workPermit.sickProof', $workPermit->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="submit" class="btn btn-xs btn-primary" value="Sakit Dengan Keterangan">
</form>
    
@endif
                    {{-- </div> --}}
                </th>
                </tr>

            </tbody>
        </table>
    </div>
</div>

@endsection