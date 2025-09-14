@extends('layouts.admin')
@section('content')

    <div class="card">
        <div class="card-header">
            {{ trans('global.create') }} {{ trans('global.absenceadj.title_singular') }}
        </div>

        <div class="card-body">
        <form action="{{ route('admin.absenceadj.update', [$absenceadj->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="form-group {{ $errors->has('staff_id') ? 'has-error' : '' }}">
                    <label for="staff_id">{{ trans('global.absenceadj.fields.staff_id') }}*</label>
                    <select id="staff_id" name="staff_id" class="form-control select2"
                        value="{{ old('staff_id', isset($absenceadj) ? $absenceadj->staff_id : '') }}" required>
                        <option value="">--Pilih staff--</option>
                        @foreach ($staffs as $staff)
                            <option value="{{ $staff->id }}" {{$staff->id == $absenceadj->staff_id ? 'selected' : ''}}>{{ $staff->name }}</option>
                        @endforeach

                    </select>
                    @if ($errors->has('staff_id'))
                        <em class="invalid-feedback">
                            {{ $errors->first('staff_id') }}
                        </em>
                    @endif
                </div>

                <div class="form-group {{ $errors->has('session_start') ? 'has-error' : '' }}">
                    <label for="session_start">{{ trans('global.absenceadj.fields.session_start') }}*</label>
                    <input type="date" id="session_start" name="session_start" class="form-control"
                        value="{{ old('session_start', isset($absenceadj->session_start) ? date('Y-m-d', strtotime($absenceadj->session_start)) : '') }}" required>
                    @if ($errors->has('session_start'))
                        <em class="invalid-feedback">
                            {{ $errors->first('session_start') }}
                        </em>
                    @endif
                </div>

                <div class="form-group {{ $errors->has('session_end') ? 'has-error' : '' }}">
                    <label for="session_end">{{ trans('global.absenceadj.fields.session_end') }}*</label>
                    <input type="date" id="session_end" name="session_end" class="form-control"
                        value="{{ old('session_end', isset($absenceadj->session_end) ? date('Y-m-d', strtotime($absenceadj->session_end)) : '') }}" required>
                    @if ($errors->has('session_end'))
                        <em class="invalid-feedback">
                            {{ $errors->first('session_end') }}
                        </em>
                    @endif
                </div>

                <div class="form-group {{ $errors->has('Jumlah_Masuk') ? 'has-error' : '' }}">
                    <label for="Jumlah_Masuk" id="Jumlah_Masuk_Label">{{ trans('global.absenceadj.fields.Jumlah_Masuk') }}</label>
                    <input type="number" id="Jumlah_Masuk" name="Jumlah_Masuk" class="form-control"
                        value="{{ old('Jumlah_Masuk', isset($absenceadj) ? $absenceadj->Jumlah_Masuk : '') }}">
                    @if ($errors->has('Jumlah_Masuk'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Masuk') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Kegiatan1') ? 'has-error' : '' }}">
                    <label for="Jumlah_Kegiatan1" id="Jumlah_Kegiatan1_Label">{{ trans('global.absenceadj.fields.Jumlah_Kegiatan1') }}</label>
                    <input type="number" id="Jumlah_Kegiatan1" name="Jumlah_Kegiatan1" class="form-control"
                        value="{{ old('Jumlah_Kegiatan1', isset($absenceadj) ? $absenceadj->Jumlah_Kegiatan1 : '') }}">
                    @if ($errors->has('Jumlah_Kegiatan1'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Kegiatan1') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Kegiatan2') ? 'has-error' : '' }}">
                    <label for="Jumlah_Kegiatan2" id="Jumlah_Kegiatan2_Label">{{ trans('global.absenceadj.fields.Jumlah_Kegiatan2') }}</label>
                    <input type="number" id="Jumlah_Kegiatan2" name="Jumlah_Kegiatan2" class="form-control"
                        value="{{ old('Jumlah_Kegiatan2', isset($absenceadj) ? $absenceadj->Jumlah_Kegiatan2 : '') }}">
                    @if ($errors->has('Jumlah_Kegiatan2'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Kegiatan2') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Dinas_Dalam') ? 'has-error' : '' }}">
                    <label for="Jumlah_Dinas_Dalam" id="Jumlah_Dinas_Dalam_Label">{{ trans('global.absenceadj.fields.Jumlah_Dinas_Dalam') }}</label>
                    <input type="number" id="Jumlah_Dinas_Dalam" name="Jumlah_Dinas_Dalam" class="form-control"
                        value="{{ old('Jumlah_Dinas_Dalam', isset($absenceadj) ? $absenceadj->Jumlah_Dinas_Dalam : '') }}">
                    @if ($errors->has('Jumlah_Dinas_Dalam'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Dinas_Dalam') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Dinas_Luar') ? 'has-error' : '' }}">
                    <label for="Jumlah_Dinas_Luar" id="Jumlah_Dinas_Luar_Label">{{ trans('global.absenceadj.fields.Jumlah_Dinas_Luar') }}</label>
                    <input type="number" id="Jumlah_Dinas_Luar" name="Jumlah_Dinas_Luar" class="form-control"
                        value="{{ old('Jumlah_Dinas_Luar', isset($absenceadj) ? $absenceadj->Jumlah_Dinas_Luar : '') }}">
                    @if ($errors->has('Jumlah_Dinas_Luar'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Dinas_Luar') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Cuti') ? 'has-error' : '' }}">
                    <label for="Jumlah_Cuti" id="Jumlah_Cuti_Label">{{ trans('global.absenceadj.fields.Jumlah_Cuti') }}</label>
                    <input type="number" id="Jumlah_Cuti" name="Jumlah_Cuti" class="form-control"
                        value="{{ old('Jumlah_Cuti', isset($absenceadj) ? $absenceadj->Jumlah_Cuti : '') }}">
                    @if ($errors->has('Jumlah_Cuti'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Cuti') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Lembur') ? 'has-error' : '' }}">
                    <label for="Jumlah_Lembur" id="Jumlah_Lembur_Label">{{ trans('global.absenceadj.fields.Jumlah_Lembur') }}</label>
                    <input type="number" id="Jumlah_Lembur" name="Jumlah_Lembur" class="form-control"
                        value="{{ old('Jumlah_Lembur', isset($absenceadj) ? $absenceadj->Jumlah_Lembur : '') }}">
                    @if ($errors->has('Jumlah_Lembur'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Lembur') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Lembur4') ? 'has-error' : '' }}">
                    <label for="Jumlah_Lembur4" id="Jumlah_Lembur4_Label">{{ trans('global.absenceadj.fields.Jumlah_Lembur4') }}</label>
                    <input type="number" id="Jumlah_Lembur4" name="Jumlah_Lembur4" class="form-control"
                        value="{{ old('Jumlah_Lembur4', isset($absenceadj) ? $absenceadj->Jumlah_Lembur4 : '') }}">
                    @if ($errors->has('Jumlah_Lembur4'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Lembur4') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Permisi') ? 'has-error' : '' }}">
                    <label for="Jumlah_Permisi" id="Jumlah_Permisi_Label">{{ trans('global.absenceadj.fields.Jumlah_Permisi') }}</label>
                    <input type="number" id="Jumlah_Permisi" name="Jumlah_Permisi" class="form-control"
                        value="{{ old('Jumlah_Permisi', isset($absenceadj) ? $absenceadj->Jumlah_Permisi : '') }}">
                    @if ($errors->has('Jumlah_Permisi'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Permisi') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Izin') ? 'has-error' : '' }}">
                    <label for="Jumlah_Izin" id="Jumlah_Izin_Label">{{ trans('global.absenceadj.fields.Jumlah_Izin') }}</label>
                    <input type="number" id="Jumlah_Izin" name="Jumlah_Izin" class="form-control"
                        value="{{ old('Jumlah_Izin', isset($absenceadj) ? $absenceadj->Jumlah_Izin : '') }}">
                    @if ($errors->has('Jumlah_Izin'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Izin') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Sakit') ? 'has-error' : '' }}">
                    <label for="Jumlah_Sakit" id="Jumlah_Sakit_Label">{{ trans('global.absenceadj.fields.Jumlah_Sakit') }}</label>
                    <input type="number" id="Jumlah_Sakit" name="Jumlah_Sakit" class="form-control"
                        value="{{ old('Jumlah_Sakit', isset($absenceadj) ? $absenceadj->Jumlah_Sakit : '') }}">
                    @if ($errors->has('Jumlah_Sakit'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Sakit') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Dispen') ? 'has-error' : '' }}">
                    <label for="Jumlah_Dispen" id="Jumlah_Dispen_Label">{{ trans('global.absenceadj.fields.Jumlah_Dispen') }}</label>
                    <input type="number" id="Jumlah_Dispen" name="Jumlah_Dispen" class="form-control"
                        value="{{ old('Jumlah_Dispen', isset($absenceadj) ? $absenceadj->Jumlah_Dispen : '') }}">
                    @if ($errors->has('Jumlah_Dispen'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Dispen') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Alfa') ? 'has-error' : '' }}">
                    <label for="Jumlah_Alfa" id="Jumlah_Alfa_Label">{{ trans('global.absenceadj.fields.Jumlah_Alfa') }}</label>
                    <input type="number" id="Jumlah_Alfa" name="Jumlah_Alfa" class="form-control"
                        value="{{ old('Jumlah_Alfa', isset($absenceadj) ? $absenceadj->Jumlah_Alfa : '') }}">
                    @if ($errors->has('Jumlah_Alfa'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Alfa') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Tidak_Masuk') ? 'has-error' : '' }}">
                    <label for="Jumlah_Tidak_Masuk" id="Jumlah_Tidak_Masuk_Label">{{ trans('global.absenceadj.fields.Jumlah_Tidak_Masuk') }}</label>
                    <input type="number" id="Jumlah_Tidak_Masuk" name="Jumlah_Tidak_Masuk" class="form-control"
                        value="{{ old('Jumlah_Tidak_Masuk', isset($absenceadj) ? $absenceadj->Jumlah_Tidak_Masuk : '') }}">
                    @if ($errors->has('Jumlah_Tidak_Masuk'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Tidak_Masuk') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Hari_Kerja') ? 'has-error' : '' }}">
                    <label for="Jumlah_Hari_Kerja" id="Jumlah_Hari_Kerja_Label">{{ trans('global.absenceadj.fields.Jumlah_Hari_Kerja') }}</label>
                    <input type="number" id="Jumlah_Hari_Kerja" name="Jumlah_Hari_Kerja" class="form-control"
                        value="{{ old('Jumlah_Hari_Kerja', isset($absenceadj) ? $absenceadj->Jumlah_Hari_Kerja : '') }}">
                    @if ($errors->has('Jumlah_Hari_Kerja'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Hari_Kerja') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Jumlah_Hari_Libur') ? 'has-error' : '' }}">
                    <label for="Jumlah_Hari_Libur" id="Jumlah_Hari_Libur_Label">{{ trans('global.absenceadj.fields.Jumlah_Hari_Libur') }}</label>
                    <input type="number" id="Jumlah_Hari_Libur" name="Jumlah_Hari_Libur" class="form-control"
                        value="{{ old('Jumlah_Hari_Libur', isset($absenceadj) ? $absenceadj->Jumlah_Hari_Libur : '') }}">
                    @if ($errors->has('Jumlah_Hari_Libur'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Jumlah_Hari_Libur') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Absen_Bolong_Datang') ? 'has-error' : '' }}">
                    <label for="Absen_Bolong_Datang" id="Absen_Bolong_Datang_Label">{{ trans('global.absenceadj.fields.Absen_Bolong_Datang') }}</label>
                    <input type="number" id="Absen_Bolong_Datang" name="Absen_Bolong_Datang" class="form-control"
                        value="{{ old('Absen_Bolong_Datang', isset($absenceadj) ? $absenceadj->Absen_Bolong_Datang : '') }}">
                    @if ($errors->has('Absen_Bolong_Datang'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Absen_Bolong_Datang') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Absen_Bolong_Pulang') ? 'has-error' : '' }}">
                    <label for="Absen_Bolong_Pulang" id="Absen_Bolong_Pulang_Label">{{ trans('global.absenceadj.fields.Absen_Bolong_Pulang') }}</label>
                    <input type="number" id="Absen_Bolong_Pulang" name="Absen_Bolong_Pulang" class="form-control"
                        value="{{ old('Absen_Bolong_Pulang', isset($absenceadj) ? $absenceadj->Absen_Bolong_Pulang : '') }}">
                    @if ($errors->has('Absen_Bolong_Pulang'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Absen_Bolong_Pulang') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Absen_Lambat') ? 'has-error' : '' }}">
                    <label for="Absen_Lambat" id="Absen_Lambat_Label">{{ trans('global.absenceadj.fields.Absen_Lambat') }}</label>
                    <input type="number" id="Absen_Lambat" name="Absen_Lambat" class="form-control"
                        value="{{ old('Absen_Lambat', isset($absenceadj) ? $absenceadj->Absen_Lambat : '') }}">
                    @if ($errors->has('Absen_Lambat'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Absen_Lambat') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Permisi_Potong_Gaji') ? 'has-error' : '' }}">
                    <label for="Permisi_Potong_Gaji" id="Permisi_Potong_Gaji_Label">{{ trans('global.absenceadj.fields.Permisi_Potong_Gaji') }}</label>
                    <input type="number" id="Permisi_Potong_Gaji" name="Permisi_Potong_Gaji" class="form-control"
                        value="{{ old('Permisi_Potong_Gaji', isset($absenceadj) ? $absenceadj->Permisi_Potong_Gaji : '') }}">
                    @if ($errors->has('Permisi_Potong_Gaji'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Permisi_Potong_Gaji') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('Permisi_Tidak_Potong_Gaji') ? 'has-error' : '' }}">
                    <label
                        for="Permisi_Tidak_Potong_Gaji" id="Permisi_Tidak_Potong_Gaji_Label">{{ trans('global.absenceadj.fields.Permisi_Tidak_Potong_Gaji') }}</label>
                    <input type="number" id="Permisi_Tidak_Potong_Gaji" name="Permisi_Tidak_Potong_Gaji"
                        class="form-control"
                        value="{{ old('Permisi_Tidak_Potong_Gaji', isset($absenceadj) ? $absenceadj->Permisi_Tidak_Potong_Gaji : '') }}">
                    @if ($errors->has('Permisi_Tidak_Potong_Gaji'))
                        <em class="invalid-feedback">
                            {{ $errors->first('Permisi_Tidak_Potong_Gaji') }}
                        </em>
                    @endif
                </div>

                <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                    <label for="memo">{{ trans('global.absenceadj.fields.memo') }}*</label>
                    <textArea id="memo" name="memo" class="form-control"
                        value="{{ old('memo', isset($absenceadj) ? $absenceadj->memo : '') }}" required></textArea>
                    @if ($errors->has('memo'))
                        <em class="invalid-feedback">
                            {{ $errors->first('memo') }}
                        </em>
                    @endif
                </div>
                <div>
                    <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
                </div>
            </form>
        </div>
    </div>

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {

            $('#staff_id').select2({
                placeholder: "--Pilih staff--",
                allowClear: true
            });

            function fetchData() {
                const staff_id = $('#staff_id').val();
                const from = $('#session_start').val();
                const to = $('#session_end').val();

                if (!to || !staff_id || !from) {
                    if (to && (!staff_id || !from)) {
                    alert('Perhatian, data Pegawai & Tanggal Awal harus diisi!');
                    return;
                    }
                } else {
                    //alert(staff_id+':'+from+':'+to)

                    $.ajax({
                        url: "{{ route('admin.absenceRecap') }}", // Replace with your route
                        method: 'GET',
                        data: {
                            staff_id: staff_id,
                            from: from,
                            to: to,
                            adj_inc: true
                        },
                        success: function(response) {
                            // Set values to input fields
                            //console.log('response ajax', response.data[0].Jumlah_Cuti)
                            $('#Jumlah_Masuk').val(response.data[0].Jumlah_Masuk);
                            $('#Jumlah_Kegiatan1').val(response.data[0].Jumlah_Kegiatan1);
                            $('#Jumlah_Kegiatan2').val(response.data[0].Jumlah_Kegiatan2);
                            $('#Jumlah_Dinas_Dalam').val(response.data[0].Jumlah_Dinas_Dalam);
                            $('#Jumlah_Dinas_Luar').val(response.data[0].Jumlah_Dinas_Luar);
                            $('#Jumlah_Cuti').val(response.data[0].Jumlah_Cuti);
                            $('#Jumlah_Lembur').val(response.data[0].Jumlah_Lembur);
                            $('#Jumlah_Lembur4').val(response.data[0].Jumlah_Lembur4);
                            $('#Jumlah_Permisi').val(response.data[0].Jumlah_Permisi);
                            $('#Jumlah_Izin').val(response.data[0].Jumlah_Izin);
                            $('#Jumlah_Sakit').val(response.data[0].Jumlah_Sakit);
                            $('#Jumlah_Dispen').val(response.data[0].Jumlah_Dispen);
                            $('#Jumlah_Alfa').val(response.data[0].Jumlah_Alfa);
                            $('#Jumlah_Tidak_Masuk').val(response.data[0].Jumlah_Tidak_Masuk);
                            $('#Jumlah_Hari_Kerja').val(response.data[0].Jumlah_Hari_Kerja);
                            $('#Jumlah_Hari_Libur').val(response.data[0].Jumlah_Hari_Libur);
                            $('#Absen_Bolong_Datang').val(response.data[0].Absen_Bolong_Datang);
                            $('#Absen_Bolong_Pulang').val(response.data[0].Absen_Bolong_Pulang);
                            $('#Absen_Lambat').val(response.data[0].Absen_Lambat);
                            $('#Permisi_Potong_Gaji').val(response.data[0].Permisi_Potong_Gaji);
                            $('#Permisi_Tidak_Potong_Gaji').val(response.data[0]
                                .Permisi_Tidak_Potong_Gaji);
                            // Set text to .text_here div
                            $('#Jumlah_Masuk_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Masuk_Label + ')';
                            });
                            $('#Jumlah_Kegiatan1_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Kegiatan1_Label + ')';
                            });
                            $('#Jumlah_Kegiatan2_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Kegiatan2_Label + ')';
                            });
                            $('#Jumlah_Dinas_Dalam_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Dinas_Dalam_Label + ')';
                            });
                            $('#Jumlah_Dinas_Luar_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Dinas_Luar_Label + ')';
                            });
                            $('#Jumlah_Cuti_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Cuti_Label + ')';
                            });
                            $('#Jumlah_Lembur_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Lembur_Label + ')';
                            });
                            $('#Jumlah_Lembur4_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Lembur4_Label + ')';
                            });
                            $('#Jumlah_Permisi_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Permisi_Label + ')';
                            });
                            $('#Jumlah_Izin_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Izin_Label + ')';
                            });
                            $('#Jumlah_Sakit_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Sakit_Label + ')';
                            });
                            $('#Jumlah_Dispen_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Dispen_Label + ')';
                            });
                            $('#Jumlah_Alfa_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Alfa_Label + ')';
                            });
                            $('#Jumlah_Tidak_Masuk_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Tidak_Masuk_Label + ')';
                            });
                            $('#Jumlah_Hari_Kerja_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Hari_Kerja_Label + ')';
                            });
                            $('#Jumlah_Hari_Libur_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Jumlah_Hari_Libur_Label + ')';
                            });
                            $('#Absen_Bolong_Datang_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Absen_Bolong_Datang_Label + ')';
                            });
                            $('#Absen_Bolong_Pulang_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Absen_Bolong_Pulang_Label + ')';
                            });
                            $('#Absen_Lambat_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Absen_Lambat_Label + ')';
                            });
                            $('#Permisi_Potong_Gaji_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Permisi_Potong_Gaji_Label + ')';
                            });
                            $('#Permisi_Tidak_Potong_Gaji_Label').text(function(_, currentText) {
                                return currentText.split(' ')[0] + ' (' + response.data[0]
                                    .Permisi_Tidak_Potong_Gaji_Label + ')';
                            });
                        },
                        error: function(xhr) {
                            console.error('AJAX error:', xhr.responseText);
                            alert('Gagal mengambil data. ' + xhr.responseText);
                        }
                    });
                }
            };
            fetchData();
            $('#staff_id, #session_start, #session_end').on('change', fetchData);
        });
    </script>
@endsection
@endsection

