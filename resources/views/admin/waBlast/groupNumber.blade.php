@extends('layouts.admin')
@section('content')
@can('user_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.users.create") }}">
                {{ trans('global.add') }} {{ trans('global.user.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.user.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
   
                    </tr>
                </thead>
                <tbody>
                  @for ($i=0; $i<count($test); $i++)
                      
                
                        <tr data-entry-id="">
                            <td>
<form action="{{ route("admin.wablast.store") }}" method="POST" enctype="multipart/form-data">
    @csrf
    @for ($a=0; $a<count($test[$a]); $a++)
<input type="hidden" name="area[]" value="{{ $item[$i][$a]['phone'] }}">
@endforeach
   
    {{-- <input type="hidden" name ="data" value="{{ $test[$i] }}" > --}}
    <input class="btn btn-danger" type="submit" value="Send">
</form>
                            </td>
                          

                        </tr>
                        @endfor
             
                </tbody>
            </table>
        </div>
    </div>
</div>
@section('scripts')
@parent
{{-- <script>
    $(function () {
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.users.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('user_delete')
  dtButtons.push(deleteButton)
@endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
})

</script> --}}
@endsection
@endsection