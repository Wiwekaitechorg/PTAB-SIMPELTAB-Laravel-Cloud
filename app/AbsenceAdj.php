<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class AbsenceAdj extends Model
{
    protected $table    = 'absence_adj';

    protected $dates = [
        'updated_at',
        'created_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'user_id',
        'staff_id',
        'session_start',
        'session_end',
        'memo',
        'Jumlah_Masuk',
        'Jumlah_Kegiatan1',
        'Jumlah_Kegiatan2',
        'Jumlah_Dinas_Dalam',
        'Jumlah_Dinas_Luar',
        'Jumlah_Cuti',
        'Jumlah_Lembur',
        'Jumlah_Lembur4',
        'Jumlah_Permisi',
        'Jumlah_Izin',
        'Jumlah_Sakit',
        'Jumlah_Dispen',
        'Jumlah_Alfa',
        'Jumlah_Tidak_Masuk',
        'Jumlah_Hari_Kerja',
        'Jumlah_Hari_Libur',
        'Absen_Bolong_Datang',
        'Absen_Bolong_Pulang',
        'Absen_Lambat',
        'Permisi_Potong_Gaji',
        'Permisi_Tidak_Potong_Gaji',
        'Jumlah_Sakit_Izin_Dokter',
        'Absen_Bolong_Kegiatan1',
        'Absen_Bolong_Kegiatan2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
}
