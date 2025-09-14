<?php
public function reportAllExcelTest(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);

        $date_start   = $request->from;
        $date_end     = $request->to;
        $alpha        = 0;
        $absen_bolong = 0;

        $awal_cuti  = strtotime($date_start);
        $akhir_cuti = strtotime($date_end);

        $dapertement_id = '';
        if ($request->kode_bagian) {
            $dapertement_row = Dapertement::select('id')->where('code', $request->kode_bagian)->first();
            $dapertement_id  = $dapertement_row->id;
        }

        $staffs = Staff::FilterWorkUnit($request->work_unit_id)
            ->FilterNik($request->NIK)
            ->FilterDapertement($dapertement_id)
            ->FilterId($request->staff_id)
            ->FilterJob($request->job_id)
            ->with('dapertement', 'subdapertement')
            ->orderBy('NIK', 'ASC')
            ->where('_status', 'active')
            ->get();
        $data = [];

        foreach ($staffs as $staff) {

            $report = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 ,1,NULL)) jumlah_masuk')
                ->selectRaw('count(IF(absence_category_id = 2 AND status = 0 ,1,NULL)) jumlah_pulang')
                ->selectRaw('count(IF(absence_category_id = 3 AND status = 0 ,1,NULL)) jumlah_k1')
                ->selectRaw('count(IF(absence_category_id = 4 AND status = 0 ,1,NULL)) jumlah_k2')
                ->selectRaw('count(IF(absence_category_id = 5 AND status = 0 ,1,NULL)) jumlah_dinasDalam')
                ->selectRaw('count(IF(absence_category_id = 7 AND status = 0 ,1,NULL)) jumlah_dinasLuar')
                ->selectRaw('count(IF(absence_category_id = 8 AND status = 0 ,1,NULL)) jumlah_cuti')
                ->selectRaw('SUM(IF(absence_category_id = 10 AND duration > 0 AND status = 0 ,duration,0)) jumlah_lembur')
                ->selectRaw('count(IF(absence_category_id = 10 AND duration >= 4 AND status = 0 ,1,NULL)) jumlah_lemburlebih')
                ->selectRaw('count(IF(absence_category_id = 11 AND status = 0 ,1,NULL)) jumlah_permisi')
            // ->selectRaw('count(IF(absence_category_id = 13 AND status = 0 ,1,NULL)) jumlah_izin')
                ->selectRaw('count(IF(absence_category_id = 14 AND status = 0 ,1,NULL)) jumlah_dispen')
                ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 AND late > 0.016667 ,1,NULL)) jumlah_lambat')
                ->where('staff_id', $staff->id)
            //->whereBetween('absences.created_at', [$date_start, $date_end])
                ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
                ->first();

            $sakit = AbsenceRequest::selectRaw('count(id) as jumlah_sakit')
                ->where('category', 'permission')
                ->where('type', 'sick')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            $sakitizindokter = AbsenceRequest::selectRaw('count(id) as jumlah_sakit')
                ->where('category', 'permission')
                ->where('type', 'sick_proof')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            $izin = AbsenceRequest::selectRaw('count(id) as jumlah_izin')
                ->where('category', 'permission')
                ->where('type', 'other')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            //get dinas luar
            $dinasLuarRows = AbsenceRequest::selectRaw('start,end')
                ->where('category', 'duty')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->get();
            $jumlah_dinasLuar = 0;
            foreach ($dinasLuarRows as $dinasLuarRow) {
                $diff = date_diff(date_create($dinasLuarRow->start), date_create($dinasLuarRow->end));
                $diff = $diff->format("%R%a");
                $diff = (int) $diff + 1;
                $jumlah_dinasLuar += $diff;
            }
            //permisi
            $permisi = AbsenceRequest::selectRaw('*')
                ->where('category', 'excuse')
            //->where('type', 'out')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();

            // if permisi
            $permisi_potong_gaji       = 0;
            $permisi_tidak_potong_gaji = 0;
            if ($permisi) {
                //check reguler or shift
                if ($staff->work_type_id != 2) {
                    //if reguler get hari
                    $day_id = date('w', strtotime($permisi->start)) == "0" ? '7' : date('w', strtotime($permisi->start));
                    //get clock
                    $date_now_12      = date('Y-m-d', strtotime($permisi->start)) . " 12:00:00";
                    $date_now_11      = date('Y-m-d', strtotime($permisi->start)) . " 11:00:00";
                    $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($permisi->start)));
                    $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                    $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                    //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                    //if reguler mon - thu, check if jam mulai < 12
                    if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                        $permisi_potong_gaji++;
                    }
                    //if reguler fri, check if jam mulai < 11
                    else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                        $permisi_potong_gaji++;
                    } else {
                        $permisi_tidak_potong_gaji++;
                    }
                } else {
                    $permisi_tidak_potong_gaji++;
                }
            }

            $absen_bolong_datang    = 0;
            $absen_bolong_pulang    = 0;
            $absen_bolong_kegiatan1 = 0;
            $absen_bolong_kegiatan2 = 0;
            //get bolong masuk
            if ($report->jumlah_masuk < $report->jumlah_pulang) {
                $absen_bolong_datang = $report->jumlah_pulang - $report->jumlah_masuk;
            }
            if ($report->jumlah_masuk > $report->jumlah_pulang) {
                $absen_bolong_pulang = $report->jumlah_masuk - $report->jumlah_pulang;
            }
            //get bolong kegiatan
            if ($report->jumlah_k1 < ($report->jumlah_masuk + $absen_bolong_datang)) {
                $absen_bolong_kegiatan1 = ($report->jumlah_masuk + $absen_bolong_datang) - $report->jumlah_k1;
            }
            if ($report->jumlah_k2 < ($report->jumlah_masuk + $absen_bolong_datang)) {
                $absen_bolong_kegiatan2 = ($report->jumlah_masuk + $absen_bolong_datang) - $report->jumlah_k2;
            }
            // $report_masuk        = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            //     ->selectRaw('absence_logs.*')
            //     ->where('absence_category_id', 1)
            //     ->where('status', 0)
            //     ->where('staff_id', $staff->id)
            // //->whereBetween('absences.created_at', [$date_start, $date_end])
            //     ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            //     ->get();

            // foreach ($report_masuk as $item) {
            //     //get absen pulang
            //     $log_out = AbsenceLog::selectRaw('id')
            //         ->where('absence_id', $item->absence_id)
            //         ->where('absence_category_id', 2)
            //         ->first();
            //     if (! $log_out) {
            //         $absen_bolong_pulang += 1;
            //     }
            //     // if ($staff->work_type_id === 1) {
            //     //     $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($item->timein))));
            //     //     // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
            //     //     if ($item->register >= $cekDateNew) {
            //     //         $absen_bolong += 1;
            //     //     }
            //     // } else {
            //     //     $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($item->timein))));
            //     //     // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
            //     //     if ($item->register >= $cekDateNew) {
            //     //         $absen_bolong += 1;
            //     //     }
            //     // }
            // }

            // $report_pulang = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            //     ->selectRaw('absence_logs.*')
            //     ->where('absence_category_id', 2)
            //     ->where('status', 0)
            //     ->where('staff_id', $staff->id)
            // //->whereBetween('absences.created_at', [$date_start, $date_end])
            //     ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            //     ->get();

            // foreach ($report_pulang as $item) {
            //     //get absen masuk
            //     $log_in = AbsenceLog::selectRaw('id')
            //         ->where('absence_id', $item->absence_id)
            //         ->where('absence_category_id', 1)
            //         ->first();
            //     if (! $log_in) {
            //         $absen_bolong_masuk += 1;
            //     }
            // }

            $kegiatan = '';

            if ($staff->work_type_id === 1) {

                // tanggalnya diubah formatnya ke Y-m-d

                $hariKerja   = [];
                $sabtuminggu = [];

                for ($i = $awal_cuti; $i <= $akhir_cuti; $i += (60 * 60 * 24)) {
                    if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                        $hariKerja[] = $i;
                    } else {
                        $sabtuminggu[] = $i;
                    }
                }
                $jumlah_kerja = count($hariKerja);

                // mencari jumlah hari end

                //$holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereBetween('start', [$date_start, $date_end])->where('status', null)->first();
                $holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->first();

                //holiday in weekend
                $holiday_weekend = 0;
                //$holiday_rows = Holiday::selectRaw('start')->whereBetween('start', [$date_start, $date_end])->where('status', null)->get();
                $holiday_rows = Holiday::selectRaw('start')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->get();
                foreach ($holiday_rows as $holiday_row) {
                    $dayOfWeek = date('w', strtotime($holiday_row->start));
                    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                        $holiday_weekend++;
                    }
                }

                $jumlah_libur = $holiday->jumlah_libur - $holiday_weekend;
                $jumlah_kerja = $jumlah_kerja - $jumlah_libur;
                $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit - $sakitizindokter->jumlah_sakit;
                $kegiatan     = "Kegiatan";
                if ($alpha <= 0) {
                    $alpha = 0;
                }
            } else {
                //$jadwal = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $staff->id)->whereBetween('start', [$date_start, $date_end])->first();
                $jadwal       = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $staff->id)->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->first();
                $jumlah_kerja = $jadwal->jumlah_kerja;
                $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit - $sakitizindokter->jumlah_sakit;
                $jumlah_libur = 0;
                $kegiatan     = "Kontrol";
                if ($alpha <= 0) {
                    $alpha = 0;
                }
            }

            $jumlah_cuti = $report->jumlah_cuti;
            if ($report->jumlah_cuti > $jumlah_kerja) {
                $jumlah_cuti = $jumlah_kerja;
            }

            $absenceadjs = AbsenceAdj::whereDate('session_start', '=', $request->from)->whereDate('session_end', '=', $request->to)->where('staff_id', '=', $staff->id)->first();

            $data[] = [
                "NIK"                             => $staff->NIK,
                "Nama"                            => $staff->id . " " . $staff->name,
                "Bagian_Unit"                     => $staff->dapertement->name,
                "Tipe_Kerja"                      => $staff->work_type_id === 1 ? "Reguler" : "Shift",
                "Jumlah_Masuk"                    => ($report->jumlah_masuk + $report->jumlah_dispen + $jumlah_dinasLuar) + ($absenceadjs ? $absenceadjs->Jumlah_Masuk : 0),
                "Jumlah_" . $kegiatan . "1"       => $report->jumlah_k1 + ($absenceadjs ? $absenceadjs->Jumlah_Kegiatan1 : 0),
                "Jumlah_" . $kegiatan . "2"       => $report->jumlah_k2 + ($absenceadjs ? $absenceadjs->Jumlah_Kegiatan2 : 0),
                "Jumlah_Dinas_Dalam"              => $report->jumlah_dinasDalam + ($absenceadjs ? $absenceadjs->Jumlah_Dinas_Dalam : 0),
                "Jumlah_Dinas_Luar"               => $jumlah_dinasLuar + ($absenceadjs ? $absenceadjs->Jumlah_Dinas_Luar : 0),
                "Jumlah_Cuti"                     => $jumlah_cuti + ($absenceadjs ? $absenceadjs->Jumlah_Cuti : 0),
                "Jumlah_Lembur"                   => $report->jumlah_lembur + ($absenceadjs ? $absenceadjs->Jumlah_Lembur : 0),
                "Jumlah_Lembur4"                  => $report->jumlah_lemburlebih + ($absenceadjs ? $absenceadjs->Jumlah_Lembur4 : 0),
                "Jumlah_Permisi"                  => $report->jumlah_permisi + ($absenceadjs ? $absenceadjs->Jumlah_Permisi : 0),
                "Jumlah_Izin"                     => $izin->jumlah_izin + ($absenceadjs ? $absenceadjs->Jumlah_Izin : 0),
                "Jumlah_Sakit_Tidak_Izin_Dokter"  => $sakit->jumlah_sakit + ($absenceadjs ? $absenceadjs->Jumlah_Sakit : 0),
                "Jumlah_Sakit_Izin_Dokter"        => $sakitizindokter->jumlah_sakit + ($absenceadjs ? $absenceadjs->Jumlah_Sakit_Izin_Dokter : 0),
                "Jumlah_Dispen"                   => $report->jumlah_dispen + ($absenceadjs ? $absenceadjs->Jumlah_Dispen : 0),
                "Jumlah_Alfa"                     => ($alpha > 0 ? $alpha : 0) + ($absenceadjs ? $absenceadjs->Jumlah_Alfa : 0),
                "Jumlah_Tidak_Masuk"              => ($jumlah_cuti + $izin->jumlah_izin + $sakit->jumlah_sakit + $sakitizindokter->jumlah_sakit + $alpha) + ($absenceadjs ? $absenceadjs->Jumlah_Tidak_Masuk : 0),
                "Jumlah_Hari_Kerja"               => $jumlah_kerja + ($absenceadjs ? $absenceadjs->Jumlah_Hari_Kerja : 0),
                "Jumlah_Hari_Libur"               => $jumlah_libur + ($absenceadjs ? $absenceadjs->Jumlah_Hari_Libur : 0),
                "Absen_Bolong_Datang"             => $absen_bolong_datang + ($absenceadjs ? $absenceadjs->Absen_Bolong_Datang : 0),
                "Absen_Bolong_Pulang"             => $absen_bolong_pulang + ($absenceadjs ? $absenceadjs->Absen_Bolong_Pulang : 0), //
                "Absen_Lambat"                    => $report->jumlah_lambat + ($absenceadjs ? $absenceadjs->Absen_Lambat : 0),
                "Permisi_Potong_Gaji"             => $permisi_potong_gaji + ($absenceadjs ? $absenceadjs->Permisi_Potong_Gaji : 0),
                "Permisi_Tidak_Potong_Gaji"       => $permisi_tidak_potong_gaji + ($absenceadjs ? $absenceadjs->Permisi_Tidak_Potong_Gaji : 0),
                "Absen_Bolong_" . $kegiatan . "1" => $absen_bolong_kegiatan1 + ($absenceadjs ? $absenceadjs->Absen_Bolong_Kegiatan1 : 0),
                "Absen_Bolong_" . $kegiatan . "2" => $absen_bolong_kegiatan2 + ($absenceadjs ? $absenceadjs->Absen_Bolong_Kegiatan2 : 0),
            ];
        }

        return response()->json([
            'message' => 'success',
            'data'    => $data,
        ]);
    }
