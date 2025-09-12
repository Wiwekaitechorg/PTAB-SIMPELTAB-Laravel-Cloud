@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.report.complainproses') }}" target="_blank" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-5">
                <div class="form-group">
                    <label>Dari Tanggal</label>
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                        <input placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker"
                            name="from" value = "{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Sampai Tanggal</label>
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                        <input placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker"
                            name="to" value = "{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-group {{ $errors->has('dapertement_id') ? 'has-error' : '' }}">
                    <label for="type">Wilayah</label>
                    <select id="areas" name="areas" class="form-control">
                        <option value="">== Semua area ==</option>
                        @foreach ($areas as $item)
                            <option value="{{ $item->code }}">{{ $item->code }} | {{ $item->NamaWilayah }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Pilih Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">== Semua Status ==</option>
                        <option value="close">Close</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

            </div>
        </div>
        <Button type="submit" class="btn btn-primary" value="Proses">Proses</Button>
    </form>
@endsection
@section('scripts')
    @parent
    <script>
        $('#dapertement_id').change(function() {
            var dapertement_id = $(this).val();
            if (dapertement_id) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('admin.staffs.subdepartment') }}?dapertement_id=" + dapertement_id,
                    dataType: 'JSON',
                    success: function(res) {
                        if (res) {
                            $("#subdapertement_id").empty();
                            $("#subdapertement_id").append(
                                '<option value="0">---Pilih Sub Depertement---</option>');
                            $.each(res, function(id, name) {
                                $("#subdapertement_id").append('<option value="' + id + '">' +
                                    name + '</option>');
                            });
                        } else {
                            $("#subdapertement_id").empty();
                        }
                    }
                });
            } else {
                $("#subdapertement_id").empty();
            }
        });
    </script>
@endsection
<script type="text/javascript">
    $(function() {
        $(".datepicker").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
        });
    });
</script>
