@extends('layouts.admin')
@section('content')
    @can('complain_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                {{-- <a class="btn btn-success" href="{{ route('admin.complains.create') }}">
                    {{ trans('global.add') }} {{ trans('global.complain.title_singular') }}
                </a> --}}
            </div>
        </div>
    @endcan
    @if ($errors->any())
        <!-- <h4>{{ $errors->first() }}</h4> -->
        <?php
        echo "<script> alert('{$errors->first()}')</script>";
        ?>
    @endif
    <div class="card">
        <div class="card-header">
            {{ trans('global.complain.title_singular') }} {{ trans('global.list') }}
        </div>
        <div class="card-body">
            <div class="form-group">
                <div class="col-md-6">
                    <form action="" id="filtersForm">
                        <div class="input-group">
                            <select id="status" name="status" class="form-control">
                                <option value="">== Semua Status ==</option>
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="close">Close</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <select id="areas" name="areas" class="form-control">
                                <option value="">== Semua area ==</option>
                                @foreach ($areas as $item)
                                    <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group">
                            <input class="form-control" id="from" type="date" name="from"
                                value="{{ request()->input('from') }}">
                        </div>
                        <div class="input-group">
                            <input class="form-control" id="to" type="date" name="to"
                                value="{{ request()->input('to') }}">
                        </div>
                        <span class="input-group-btn">
                            &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                        </span>
                </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-complain">
                <thead>
                    <tr>
                        <th width="10">
                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.complain.fields.status') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.complain_status_id') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.proposalwm.fields.nomorrekening') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.date') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.departement') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.title') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.description') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.address') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.area') }}
                        </th>
                        <th>
                            {{ trans('global.complain.fields.user_id') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    </div>
@section('scripts')
    @parent
    <script>
        $(function() {
            let searchParams = new URLSearchParams(window.location.search)
            let status = searchParams.get('status')
            if (status) {
                $("#status").val(status);
            } else {
                $("#status").val('');
            }
            let areas = searchParams.get('areas')
            if (areas) {
                $("#areas").val(areas);
            } else {
                $("#areas").val('');
            }
            let departement = searchParams.get('departement')
            if (departement) {
                $("#departement").val(departement);
            } else {
                $("#departement").val('');
            }
            let subdepartement = searchParams.get('subdepartement')
            if (subdepartement) {
                $("#subdepartement").val(subdepartement);
            } else {
                $("#subdepartement").val('');
            }
            // console.log('type : ', type);
            let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
            let deleteButton = {
                text: deleteButtonTrans,
                url: "{{ route('admin.complains.massDestroy') }}",
                className: 'btn-danger',
                action: function(e, dt, node, config) {
                    var ids = $.map(dt.rows({
                        selected: true
                    }).nodes(), function(entry) {
                        return $(entry).data('entry-id')
                    });
                    if (ids.length === 0) {
                        alert('{{ trans('global.datatables.zero_selected') }}')
                        return
                    }
                    if (confirm('{{ trans('global.areYouSure') }}')) {
                        $.ajax({
                                headers: {
                                    'x-csrf-token': _token
                                },
                                method: 'POST',
                                url: config.url,
                                data: {
                                    ids: ids,
                                    _method: 'DELETE'
                                }
                            })
                            .done(function() {
                                location.reload()
                            })
                    }
                }
            }
            let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
            @can('complain_delete')
                dtButtons.push(deleteButton)
            @endcan
            $('.datatable:not(.ajaxTable)').DataTable({
                buttons: dtButtons
            })
            let dtOverrideGlobals = {
                buttons: dtButtons,
                processing: true,
                serverSide: true,
                retrieve: true,
                aaSorting: [],
                ajax: {
                    url: "{{ route('admin.complains.index') }}",
                    data: {
                        'status': $("#status").val(),
                        'from': $("#from").val(),
                        'to': $("#to").val(),
                        'areas': $("#areas").val(),
                        'departement': $("#departement").val(),
                        'subdepartement': $("#subdepartement").val(),
                    }
                },
                columns: [{
                        data: 'placeholder',
                        name: 'placeholder'
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'no',
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'complainstatus',
                        render: function(dataField) {
                            return dataField === 'RED' ?
                                '<button type="button" class="btn btn-danger btn-sm" disabled>&nbsp;</button>' :
                                dataField === 'YLW' ?
                                '<button type="button" class="btn btn-warning btn-sm" disabled>&nbsp;</button>' :
                                dataField === 'BLU' ?
                                '<button type="button" class="btn btn-primary btn-sm" disabled>&nbsp;</button>' :
                                '<button type="button" class="btn btn-success btn-sm" disabled>&nbsp;</button>';
                        },
                        searchable: false
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'nomorrekening',
                        name: 'customer_id',
                        searchable: true
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'dapertement',
                        name: 'dapertement',
                        searchable: false
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'address',
                        name: 'address',
                        searchable: false
                    },
                    {
                        data: 'area',
                        name: 'area',
                        searchable: false
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
                    },
                    {
                        data: 'actions',
                        name: '{{ trans('global.actions') }}'
                    }
                ],
                // order: [[ 2, 'asc' ]],
                pageLength: 100,
            };
            $('.datatable-complain').DataTable(dtOverrideGlobals);
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                $($.fn.dataTable.tables(true)).DataTable()
                    .columns.adjust();
            });
        })
    </script>
    <script>
        $('#departement').change(function() {
            var departement = $(this).val();
            if (departement) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('admin.staffs.subdepartment') }}?dapertement_id=" + departement,
                    dataType: 'JSON',
                    success: function(res) {
                        if (res) {
                            $("#subdepartement").empty();
                            $("#subdepartement").append('<option>---Pilih Sub Depertement---</option>');
                            $.each(res, function(id, name) {
                                $("#subdepartement").append('<option value="' + id + '">' +
                                    name + '</option>');
                            });
                        } else {
                            $("#subdepartement").empty();
                        }
                    }
                });
            } else {
                $("#subdepartement").empty();
            }
        });
    </script>
@endsection
@endsection
