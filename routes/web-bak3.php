<?php

//Route::get('member/register', 'MembersController@register');
// Route::resource('member', 'MembersController');

Route::redirect('/', '/login');

Route::redirect('/home', '/admin');

Auth::routes(['register' => false]);



Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {

    Route::get('watermark', 'AddImageController@index');

    Route::post('add-watermark', 'AddImageController@imageFileUpload')->name('image.watermark');


    Route::get('/', 'HomeController@index')->name('home');

    Route::post('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');

    Route::resource('permissions', 'PermissionsController');

    Route::post('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');

    Route::resource('roles', 'RolesController');

    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');

    Route::resource('users', 'UsersController');




    // keluhan pelanggan
    Route::get('customers/viewReport', 'CustomersController@viewReport')->name('customers.viewReport');
    Route::post('customers/reportExcel', 'CustomersController@reportExcel')->name('customers.reportExcel');
    Route::get('customers/editImport', 'CustomersController@editImport')->name('customers.editImport');
    Route::post('customers/updateImport', 'CustomersController@updateImport')->name('customers.updateImport');

    Route::resource('customers', 'CustomersController');

    Route::delete('customers/destroy', 'CustomersController@massDestroy')->name('customers.massDestroy');

    Route::resource('categories', 'CategoriesController');

    Route::delete('categories/destroy', 'CategoriesController@massDestroy')->name('categories.massDestroy');

    Route::resource('dapertements', 'DapertementsController');

    Route::delete('dapertements/destroy', 'DapertementsController@massDestroy')->name('dapertements.massDestroy');

    Route::resource('staffs', 'StaffsController');

    Route::delete('staffs/destroy', 'StaffsController@massDestroy')->name('staffs.massDestroy');

    // tickets


    Route::resource('tickets', 'TicketsController');

    Route::get('tickets/print/{ticket_id}', 'TicketsController@print')->name('tickets.print');

    Route::get('tickets/print-action/{ticket_id}', 'TicketsController@printAction')->name('tickets.printAction');

    Route::delete('tickets/destroy', 'TicketsController@massDestroy')->name('tickets.massDestroy');

    Route::get('ticket/printservice/{ticket_id}', 'TicketsController@printservice')->name('tickets.printservice');

    Route::get('ticket/printspk/{ticket_id}', 'TicketsController@printspk')->name('tickets.printspk');

    Route::get('ticket/printreport/{ticket_id}', 'TicketsController@printreport')->name('tickets.printreport');

    // action & action staff
    // start surya buat
    Route::get('actions/printservice', 'ActionsController@printservice')->name('actions.printservice');

    Route::get('actions/printspk', 'ActionsController@printspk')->name('actions.printspk');

    Route::get('actions/printreport', 'ActionsController@printreport')->name('actions.printreport');
    // end surya buat


    Route::resource('actions', 'ActionsController', ['only' => ['index', 'store', 'edit', 'update', 'destroy']]);

    Route::get('actions/create/{ticket_id}', 'ActionsController@create')->name('actions.create');

    Route::post('actions/staff', 'ActionsController@staff')->name('actions.staff');

    Route::get('actions/list/{action}', 'ActionsController@list')->name('actions.list');

    Route::delete('actions/destroy', 'ActionsController@massDestroy')->name('actions.massDestroy');

    Route::get('actions/staff/{action}', 'ActionsController@actionStaff')->name('actions.actionStaff');

    Route::get('actions/staff/create/{action}', 'ActionsController@actionStaffCreate')->name('actions.actionStaffCreate');

    Route::post('actions/staff/store/', 'ActionsController@actionStaffStore')->name('actions.actionStaffStore');

    Route::get('actions/staff/{action}/edit', 'ActionsController@actionStaffEdit')->name('actions.actionStaffEdit');

    Route::put('actions/staff/update', 'ActionsController@actionStaffUpdate')->name('actions.actionStaffUpdate');

    //   30-01-2024 start
    Route::get('actions/addiyionaldone/{action}/edit', 'ActionsController@additionalDone')->name('actions.additionalDone');

    Route::put('actions/addiyionaldone/update', 'ActionsController@storeAdditionalDone')->name('actions.storeAdditionalDone');

    // 30-01-2024 end


    Route::put('actions/staff/update', 'ActionsController@actionStaffUpdate')->name('actions.actionStaffUpdate');

    Route::delete('users/staff/delete/{action}/{staff}', 'ActionsController@actionStaffDestroy')->name('actions.actionStaffDestroy');

    //customer request
    Route::resource('customerrequests', 'CustomerRequestController');

    //ctm request
    Route::post('ctmrequests/{id}/reject', 'CtmRequestController@reject')->name('ctmrequests.reject');
    Route::resource('ctmrequests', 'CtmRequestController');

    //pbk
    Route::resource('pbks', 'CtmPbkController');
    Route::get('pbk/status/{id}', 'CtmPbkController@editStatus')->name('pbks.status');
    Route::put('pbk/update', 'CtmPbkController@updateStatus')->name('pbks.statusUpdate');

    //test
    Route::resource('test-customers', 'TestController');
    Route::get('test-get', 'TestController@getTest');
    Route::get('test-action-staff-store', 'ActionsController@actionStaffStoreTest');
    Route::get('test-trsf-img', 'TestController@transferImg');

    Route::resource('subdapertements', 'SubdapertementsController');

    Route::delete('subdapertements/destroy', 'SubdapertementsController@massDestroy')->name('subdapertements.massDestroy');

    Route::get('get-subdapertement', 'StaffsController@getSubdapertement')->name('staffs.subdepartment');

    Route::get('reports/reportssubhumas', 'ReportsController@reportSubHumas')->name('report.subhumas');

    Route::post('reports/reportssubhumas/proses', 'ReportsController@reportSubHumasProses')->name('report.subhumasproses');

    Route::get('reports/reportssubdistribusi', 'ReportsController@reportSubDistribusi')->name('report.subdistribusi');

    Route::post('reports/reportssubdistribusi/proses', 'ReportsController@reportSubDistribusiProses')->name('report.subdistribusiproses');


    // baru
    Route::get('reports/reportLockAction', 'ReportsController@reportLockAction')->name('report.reportLockAction');

    Route::post('reports/reportsLockAction/proses', 'ReportsController@reportLockActionProses')->name('report.reportLockActionproses');

    Route::get('reports/reportProposalWm', 'ReportsController@reportProposalWm')->name('report.reportProposalWm');

    Route::post('reports/reportProposalWm/proses', 'ReportsController@reportProposalWmProses')->name('report.reportProposalWmproses');

    Route::get('reports/report-pwm', 'ReportsController@reportPWM')->name('report.reportPWM');

    Route::post('reports/report-pwm/process', 'ReportsController@reportPWMProcess')->name('report.reportPWMProcess');
    // baru

    Route::get('get-staff', 'StaffsController@getStaff')->name('staffs.staff');

    Route::get('segel-meter', 'SegelMeterController@index')->name('segelmeter.index');

    Route::get('segel-meter/show/{id}', 'SegelMeterController@show')->name('segelmeter.show');

    Route::get('segel-meter/sppprint/{id}', 'SegelMeterController@sppPrint')->name('segelmeter.sppprint');

    Route::get('segel-deligate', 'SegelMeterController@deligate')->name('segelmeter.deligate');

    Route::get('file-upload', 'PdfUploadController@fileUpload')->name('file.upload');

    Route::get('file-uploadCreate', 'PdfUploadController@fileUploadCreate')->name('file.create');

    Route::post('file-upload', 'PdfUploadController@fileUploadPost')->name('file.upload.post');

    Route::delete('file-upload/delete/{audited}', 'PdfUploadController@fileUploadDestroy')->name('file.upload.destroy');

    Route::resource('locks', 'LockController');

    Route::get('lock/staff/{action}', 'LockController@lockactionStaff')->name('lock.actionStaff');

    Route::get('lock/staff/create/{action}', 'LockController@lockactionStaffCreate')->name('lock.actionStaffCreate');

    Route::post('lock/staff/store/', 'LockController@lockactionStaffStore')->name('lock.actionStaffStore');

    Route::delete('lock/staff/delete/{action}/{staff}', 'LockController@lockactionStaffDestroy')->name('lock.actionStaffDestroy');

    Route::get('lock/list/{action}', 'LockController@list')->name('lock.list');

    Route::get('lock/create/{lock_id}', 'LockController@actioncreate')->name('lock.lockcreate');

    Route::post('lock/action/store/', 'LockController@lockstore')->name('lock.lockstore');

    Route::delete('lock/action/delete/{action}', 'LockController@lockactionDestroy')->name('lock.actiondestroy');

    Route::get('lock/action/{action}/view', 'LockController@lockView')->name('lock.LockView');

    Route::get('lock/sppprint/{id}', 'LockController@sppPrint')->name('lock.sppprint');




    //dibuat pada 2022-09-07
    Route::resource('lockT', 'LockTController');

    Route::get('lockT/staff/{action}', 'LockTController@lockactionStaff')->name('lockT.actionStaff');

    Route::get('lockT/staff/create/{action}', 'LockTController@lockactionStaffCreate')->name('lockT.actionStaffCreate');

    Route::post('lockT/staff/store/', 'LockTController@lockactionStaffStore')->name('lockT.actionStaffStore');

    Route::delete('lockT/staff/delete/{action}/{staff}', 'LockTController@lockactionStaffDestroy')->name('lockT.actionStaffDestroy');

    Route::get('lockT/list/{action}', 'LockTController@list')->name('lockT.list');

    Route::get('lockT/create/{lock_id}', 'LockTController@actioncreate')->name('lockT.lockcreate');

    Route::post('lockT/action/store/', 'LockTController@lockstore')->name('lockT.lockstore');

    Route::delete('lockT/action/delete/{action}', 'LockTController@lockactionDestroy')->name('lockT.actiondestroy');

    Route::get('lockT/action/{action}/view', 'LockTController@lockView')->name('lockT.LockView');

    Route::get('lockT/sppprint/{id}', 'LockTController@sppPrint')->name('lockT.sppprint');
    //dibuat pada 2022-09-07

    Route::get('spp/Sppall', 'SppController@index')->name('spp.index');

    Route::get('spp/sppprintall', 'SppController@sppPrintAll')->name('spp.sppprintall');


    Route::get('customwa', 'CustomWaController@index')->name('customwa.index');
    Route::post('customwa', 'CustomWaController@import')->name('customwa.import');
    // history Wa
    Route::get('test', 'TestWaController@index')->name('test.index');
    Route::get('historywa', 'HistoryWaController@index')->name('historywa.index');
    Route::delete('historywa/destroy/{id}', 'HistoryWaController@destroy')->name('historywa.destroy');
    Route::delete('wablast/destroy', 'WhatsappBlastController@massDestroy')->name('wablast.massDestroy');
    Route::delete('wablast/destroy/{id}', 'WhatsappBlastController@destroy')->name('wablast.destroy');

    Route::get('historywa/deleteall', 'HistoryWaController@deleteAll')->name('historywa.deleteall');
    Route::get('historywa/deletefilter', 'HistoryWaController@deleteFilter')->name('historywa.deletefilter');

    Route::get('checkphone', 'CheckPhoneController@index')->name('checkphone.index');

    // device Wa
    Route::get('devicewa', 'DeviceWaController@index')->name('devicewa.index');
    Route::get('devicewa/disconect', 'DeviceWaController@disconect')->name('devicewa.disconect');
    route::get('devicewa/create', 'DeviceWaController@create')->name('devicewa.create');
    route::post('devicewa/store', 'DeviceWaController@store')->name('devicewa.store');

    //whatsapp
    Route::get('wablast', 'WhatsappBlastController@index')->name('wablast.index');
    Route::post('wablast', 'WhatsappBlastController@templateP')->name('wablast.templateP');

    //perbaikan
    Route::get('wablast/createmessageperview', 'WhatsappBlastController@createMessagePerView')->name('wablast.createmessageperview');
    Route::post('wablast/storemessageper', 'WhatsappBlastController@storeMessagePer')->name('wablast.storemessageper');
    Route::get('wablast/area', 'WhatsappBlastController@area')->name('wablast.area');
    Route::post('wablast/templateper', 'WhatsappBlastController@templatePer')->name('wablast.templateper');
    Route::post('wablast/createmessageperp', 'WhatsappBlastController@createmessagePerP')->name('wablast.createmessageperp');
    Route::post('wablast/createmessageper', 'WhatsappBlastController@createmessagePer')->name('wablast.createmessageper');

    //tunggakan
    Route::get('wablast/categoryt', 'WhatsappBlastController@categoryT')->name('wablast.categoryt');
    Route::post('wablast/templatet', 'WhatsappBlastController@templateT')->name('wablast.templatet');
    Route::post('wablast/createmessagetp', 'WhatsappBlastController@createmessageTP')->name('wablast.createmessagetp');
    Route::get('wablast/createmessaget', 'WhatsappBlastController@createmessageT')->name('wablast.createmessaget');
    Route::post('wablast/storemessaget', 'WhatsappBlastController@storeMessageT')->name('wablast.storemessaget');


    Route::post('wablast/create', 'WhatsappBlastController@create')->name('wablast.create');
    Route::get('wablast/creater', 'WhatsappBlastController@creater')->name('wablast.creater');
    Route::post('wablast/store', 'WhatsappBlastController@store')->name('wablast.store');

    Route::post('wablast/template1', 'WhatsappBlastController@template1')->name('wablast.template1');
    Route::get('wablast/template2', 'WhatsappBlastController@template2')->name('wablast.template2');
    Route::get('wablast/template3', 'WhatsappBlastController@template3')->name('wablast.template3');
    Route::post('wablast/template4p', 'WhatsappBlastController@template4P')->name('wablast.template4P');
    Route::get('wablast/template4', 'WhatsappBlastController@template4')->name('wablast.template4');
    Route::post('wablast/template5', 'WhatsappBlastController@template5')->name('wablast.template5');

    Route::post('wablastperbaikan', 'WhatsappBlastController@templateperbaikan')->name('wablast.templateperbaikan');
    Route::post('wablast/createmessage1p', 'WhatsappBlastController@createMessage1P')->name('wablast.createmessage1P');
    Route::get('wablast/createmessage1', 'WhatsappBlastController@createMessage1')->name('wablast.createmessage1');
    Route::post('wablast/storetemplate', 'WhatsappBlastController@storeTemplate')->name('wablast.storetemplate');
    Route::get('wablast/history', 'WhatsappBlastController@history')->name('wablast.history');

    Route::get('suratsegel', 'SuratSegelController@index')->name('suratsegel.index');
    Route::get('suratsegel/create', 'SuratSegelController@create')->name('suratsegel.create');
    Route::post('suratsegel/suratPdf', 'SuratSegelController@suratPdf')->name('suratsegel.suratPdf');

    // 11-2022
    Route::get('statuswm', 'StatusWmController@index')->name('statuswm.index');
    Route::get('statuswm/approveall', 'StatusWmController@approveAll')->name('statuswm.approveall');
    Route::get('statuswm/create', 'StatusWmController@create')->name('statuswm.create');
    Route::post('statuswm/approve', 'StatusWmController@approve')->name('statuswm.approve');
    Route::get('statuswm/reject', 'StatusWmController@reject')->name('statuswm.reject');

    Route::get('proposalwm/index5Year', 'ProposalWmController@index5Year')->name('proposalwm.index5Year');
    Route::get('proposalwm/index5YearDetail/{customer_id}', 'ProposalWmController@index5YearDetail')->name('proposalwm.index5YearDetail');
    Route::get('proposalwm', 'ProposalWmController@index')->name('proposalwm.index');

    Route::get('proposalwm/create', 'ProposalWmController@create')->name('proposalwm.create');
    Route::get('proposalwm/{id}/report', 'ProposalWmController@report')->name('proposalwm.report');
    Route::get('proposalwm/{id}/approve', 'ProposalWmController@approve')->name('proposalwm.approve');
    Route::get('proposalwm/{id}/actionStaff', 'ProposalWmController@actionStaff')->name('proposalwm.actionStaff');
    Route::post('proposalwm/approveProses', 'ProposalWmController@approveProses')->name('proposalwm.approveProses');
    Route::post('proposalwm/store', 'ProposalWmController@store')->name('proposalwm.store');

    Route::get('proposalwm/{id}/edit', 'ProposalWmController@edit')->name('proposalwm.edit');
    Route::put('proposalwm/{id}/update', 'ProposalWmController@update')->name('proposalwm.update');
    Route::post('proposalwm/updatestatus', 'ProposalWmController@updatestatus')->name('proposalwm.updatestatus');
    Route::get('proposalwm/show/{id}', 'ProposalWmController@show')->name('proposalwm.show');
    Route::get('proposalwm/{id}/printspk', 'ProposalWmController@printspk')->name('proposalwm.printspk');
    Route::delete('proposalwm/{id}/destroy', 'ProposalWmController@destroy')->name('proposalwm.destroy');


    Route::get('proposalwm/approveAll', 'ProposalWmController@approveAll')->name('proposalwm.approveAll');

    Route::get('actionWmStaff/{id}', 'ActionWmStaffController@index')->name('actionWmStaff.index');
    Route::get('actionWmStaff/{id}/create', 'ActionWmStaffController@create')->name('actionWmStaff.create');
    Route::post('actionWmStaff/{id}/store', 'ActionWmStaffController@store')->name('actionWmStaff.store');
    Route::post('actionWmStaff/destroy', 'ActionWmStaffController@destroy')->name('actionWmStaff.destroy');

    Route::get('actionWms/{id}/index', 'ActionWmsController@index')->name('actionWms.index');
    Route::get('actionWms/{id}/create', 'ActionWmsController@create')->name('actionWms.create');
    Route::post('actionWms/store', 'ActionWmsController@store')->name('actionWms.store');
    Route::get('actionWms/{id}/edit', 'ActionWmsController@edit')->name('actionWms.edit');
    Route::put('actionWms/{id}/update', 'ActionWmsController@update')->name('actionWms.update');
    Route::delete('actionWms/{id}/destroy', 'ActionWmsController@destroy')->name('actionWms.destroy');

    Route::delete('workUnit/destroy', 'workUnitController@massDestroy')->name('workUnit.massDestroy');
    Route::get('workUnit/test', 'workUnitController@test')->name('workUnit.test');
    Route::resource('workUnit', 'WorkUnitController');

    Route::delete('director/destroy', 'DirectorController@massDestroy')->name('director.massDestroy');
    Route::get('director/test', 'DirectorController@test')->name('director.test');
    Route::resource('director', 'DirectorController');

    // 26-04-2024
    Route::get('proposal_ka', 'ProposalKaController@index')->name('proposalKa.index');
    Route::get('proposal_ka/{id}/edit', 'ProposalKaController@edit')->name('proposalKa.edit');
    Route::put('proposal_ka/{id}/update', 'ProposalKaController@update')->name('proposalKa.update');

    // sementara
    Route::get('actions/ubahData', 'ActionsController@ubahData')->name('actions.ubahData');
    Route::delete('staffSpecials/destroy', 'StaffSpecialController@massDestroy')->name('staffSpecials.massDestroy');
    Route::resource('staffSpecials', 'StaffSpecialController');

    // 22-01-2024 start
    Route::post('absence/reportAllExcel', 'AbsenceController@reportAllExcel')->name('absence.reportAllExcel');
    Route::get('absence/reportAbsenceAllView', 'AbsenceController@reportAbsenceAllView')->name('absence.reportAbsenceAllView');
    // 22-01-2024 end

    // 07-03-2024 start
    Route::post('absence/absenceDelete', 'AbsenceController@absenceDelete')->name('absence.absenceDelete');
    Route::get('absence/viewDelete', 'AbsenceController@viewDelete')->name('absence.viewDelete');
    // 07-03-2024 end

    Route::get('absence/reportAbsence', 'AbsenceController@reportAbsence')->name('absence.reportAbsence');
    Route::post('absence/reportAbsenceExcel', 'AbsenceController@reportAbsenceExcel')->name('absence.reportAbsenceExcel');

    Route::get('absence/getShiftPlanner', 'AbsenceController@getShiftPlanner')->name('absence.getShiftPlanner');
    Route::get('absence/reportAbsenceExcelView', 'AbsenceController@reportAbsenceExcelView')->name('absence.reportAbsenceExcelView');
    Route::get('absence/reportAbsenceView', 'AbsenceController@reportAbsenceView')->name('absence.reportAbsenceView');

    Route::get('absence/createImport', 'AbsenceController@createImport')->name('absence.createImport');
    Route::post('absence/storeImport', 'AbsenceController@storeImport')->name('absence.storeImport');

    Route::get('absence/createExtra', 'AbsenceController@createExtra')->name('absence.createExtra');
    Route::post('absence/storeExtra', 'AbsenceController@storeExtra')->name('absence.storeExtra');
    Route::get('absence/createDuty', 'AbsenceController@createDuty')->name('absence.createDuty');
    Route::post('absence/storeDuty', 'AbsenceController@storeDuty')->name('absence.storeDuty');
    Route::get('absence/createLeave', 'AbsenceController@createLeave')->name('absence.createLeave');
    Route::post('absence/storeLeave', 'AbsenceController@storeLeave')->name('absence.storeLeave');
    Route::get('absence/createPermit', 'AbsenceController@createPermit')->name('absence.createPermit');
    Route::post('absence/storePermit', 'AbsenceController@storePermit')->name('absence.storePermit');
    Route::get('absence/createShift', 'AbsenceController@createShift')->name('absence.createShift');
    Route::post('absence/storeShift', 'AbsenceController@storeShift')->name('absence.storeShift');
    Route::get('absence/createImportShift', 'AbsenceController@createImportShift')->name('absence.createImportShift');
    Route::post('absence/storeImportShift', 'AbsenceController@storeImportShift')->name('absence.storeImportShift');

    Route::get('absence/absenceMenu', 'AbsenceController@absenceMenu')->name('absence.absenceMenu');
    Route::resource('absence', 'AbsenceController');
    Route::get('attendance', 'AttendanceController@index')->name('attendance.index');
    Route::get('attendance/create', 'AttendanceController@create')->name('attendance.create');
    Route::get('attendance/test', 'AttendanceController@test')->name('attendance.test');
    Route::get('attendance/attendanceMenu', 'AttendanceController@attendanceMenu')->name('attendance.attendanceMenu');
    Route::get('attendance/cekradius', 'AttendanceController@cekradius')->name('attendance.getCoordinatesWithinRadius');
    Route::get('schedule', 'ScheduleController@index')->name('schedule.index');
    Route::get('schedule/{id}/edit', 'ScheduleController@edit')->name('schedule.edit');
    Route::put('schedule/{id}/update', 'ScheduleController@update')->name('schedule.update');
    Route::get('schedule/test', 'ScheduleController@test')->name('schedule.test');

    Route::resource('problematicabsence', 'ProblematicAbsenceController');
    Route::get('leave', 'LeaveController@index')->name('leave.index');


    Route::get('holiday/edit', 'HolidayController@edit')->name('holiday.edit');
    Route::get('holiday/check', 'HolidayController@check')->name('holiday.check');
    Route::get('holiday', 'HolidayController@index')->name('holiday.index');
    Route::get('holiday/create', 'HolidayController@create')->name('holiday.create');
    Route::post('holiday/{id}/approve', 'HolidayController@approve')->name('holiday.approve');
    Route::post('holiday/{id}/reject', 'HolidayController@reject')->name('holiday.reject');
    Route::post('holiday/action', 'HolidayController@action')->name('holiday.action');


    Route::get('shift_planner_staff/edit', 'ShiftPlannerStaffController@edit')->name('shift_planner_staff.edit');
    Route::get('shift_planner_staff/check', 'ShiftPlannerStaffController@check')->name('shift_planner_staff.check');
    Route::get('shift_planner_staff', 'ShiftPlannerStaffController@index')->name('shift_planner_staff.index');
    Route::get('shift_planner_staff/create', 'ShiftPlannerStaffController@create')->name('shift_planner_staff.create');
    Route::post('shift_planner_staff/{id}/approve', 'ShiftPlannerStaffController@approve')->name('shift_planner_staff.approve');
    Route::post('shift_planner_staff/{id}/reject', 'ShiftPlannerStaffController@reject')->name('shift_planner_staff.reject');
    Route::post('shift_planner_staff/action', 'ShiftPlannerStaffController@action')->name('shift_planner_staff.action');
    Route::get('shift_planner_staff/index', 'ShiftPlannerStaffController@index')->name('shift_planner_staff.index');
    // Route::resource('holiday', 'HolidayController');
    // Route::resource('shift_planner_staff', 'ShiftPlannerStaffController');

    // Route::get('duty/edit1', 'DutyController@edit1')->name('duty.edit1');

    // Route::get('duty/check', 'DutyController@check')->name('duty.check');
    // Route::post('duty/{id}/approve', 'DutyController@approve')->name('duty.approve');
    // Route::post('duty/{id}/reject', 'DutyController@reject')->name('duty.reject');
    // Route::post('duty/action', 'DutyController@action')->name('duty.action');
    // Route::resource('duty', 'DutyController');

    Route::post('duty/{id}/approve', 'DutyController@approve')->name('duty.approve');
    Route::post('duty/{id}/reject', 'DutyController@reject')->name('duty.reject');
    Route::resource('duty', 'DutyController');

    // Route::get('job', 'JobController@index')->name('job.index');
    Route::delete('job/destroy', 'JobController@massDestroy')->name('job.massDestroy');
    Route::resource('job', 'JobController');

    Route::delete('shift_parent/destroy', 'ShiftParentController@massDestroy')->name('shift_parent.massDestroy');
    Route::resource('shift_parent', 'ShiftParentController');


    Route::delete('work_type/destroy', 'WorkTypeController@massDestroy')->name('work_type.massDestroy');
    Route::resource('work_type', 'WorkTypeController');

    Route::delete('work_type_day/destroy', 'WorkTypeController@massDestroy')->name('work_type_day.massDestroy');
    Route::resource('work_type_day', 'WorkTypeDayController');

    Route::post('shift_change/{id}/approve', 'ShiftChangeController@approve')->name('shift_change.approve');
    Route::post('shift_change/{id}/reject', 'ShiftChangeController@reject')->name('shift_change.reject');
    Route::delete('shift_change/destroy', 'ShiftChangeController@massDestroy')->name('shift_change.massDestroy');
    Route::resource('shift_change', 'ShiftChangeController');


    Route::put('shift_group/{id}/scheduleUpdate', 'ShiftGroupController@scheduleUpdate')->name('shift_group.scheduleUpdate');
    Route::get('shift_group/{id}/scheduleEdit', 'ShiftGroupController@scheduleEdit')->name('shift_group.scheduleEdit');
    Route::get('shift_group/{id}/schedule', 'ShiftGroupController@schedule')->name('shift_group.schedule');
    Route::delete('shift_group/destroy', 'ShiftGroupController@massDestroy')->name('shift_group.massDestroy');
    Route::resource('shift_group', 'ShiftGroupController');



    Route::post('leave/{id}/approve', 'LeaveController@approve')->name('leave.approve');
    Route::post('leave/{id}/reject', 'LeaveController@reject')->name('leave.reject');
    Route::resource('leave', 'LeaveController');

    Route::post('workPermit/{id}/approve', 'WorkPermitController@approve')->name('workPermit.approve');
    Route::post('workPermit/{id}/reject', 'WorkPermitController@reject')->name('workPermit.reject');
    Route::post('workPermit/{id}/sickProof', 'WorkPermitController@sickProof')->name('workPermit.sickProof');

    Route::resource('workPermit', 'WorkPermitController');

    Route::post('extra/{id}/approve', 'ExtraController@approve')->name('extra.approve');
    Route::post('extra/{id}/reject', 'ExtraController@reject')->name('extra.reject');
    Route::resource('extra', 'ExtraController');

    Route::post('excuse/{id}/approve', 'ExcuseController@approve')->name('excuse.approve');
    Route::post('excuse/{id}/reject', 'ExcuseController@reject')->name('excuse.reject');
    Route::resource('excuse', 'ExcuseController');

    Route::post('geolocation_off/{id}/approve', 'GeolocationOffController@approve')->name('geolocation_off.approve');
    Route::post('geolocation_off/{id}/reject', 'GeolocationOffController@reject')->name('geolocation_off.reject');
    Route::resource('geolocation_off', 'GeolocationOffController');


    Route::post('forget/{id}/approve', 'ForgetController@approve')->name('forget.approve');
    Route::post('forget/{id}/reject', 'ForgetController@reject')->name('forget.reject');
    Route::resource('forget', 'ForgetController');

    Route::post('additionalTime/{id}/approve', 'AdditionalTimeController@approve')->name('additionalTime.approve');
    Route::post('additionalTime/{id}/reject', 'AdditionalTimeController@reject')->name('additionalTime.reject');
    Route::resource('additionalTime', 'AdditionalTimeController');




    Route::post('location/{id}/approve', 'LocationController@approve')->name('location.approve');
    Route::post('location/{id}/reject', 'LocationController@reject')->name('location.reject');
    Route::resource('location', 'LocationController');

    // Route::post('absencegroup/approve', 'AbsenceGroupController@approve')->name('absencegroup.approve');
    // Route::get('absencegroup', 'AbsenceGroupController@index')->name('absencegroup.index');
    Route::resource('absencegroup', 'AbsenceGroupController');

    Route::get('cronjob', 'CronJobController@index');
    Route::get('cronjob/problem', 'CronJobController@problemRemainer');
    Route::post('gawatdarurat/import', 'GawatDaruratController@import')->name('gawatdarurat.import');
    Route::get('gawatdarurat', 'GawatDaruratController@index');

    // visit
    Route::post('visit/report', 'VisitController@report')->name('visit.report');
    Route::get('visit/reportForm', 'VisitController@reportForm')->name('visit.reportForm');
    Route::resource('visit', 'VisitController');


    // 22-01-2024 start
    Route::get('waReceive', 'WaReceivesController@index')->name('receives.index');
    Route::post('waReceive/store', 'WaReceivesController@store')->name('waReceives.store');
    Route::delete('waReceive/{id}/destroy', 'WaReceivesController@destroy')->name('waReceives.destroy');
    // 22-01-2024 end



    //virmach
    Route::get('virmach-image', 'VirmachController@index')->name('virmach.index');
    Route::post('virmach-image-store', 'VirmachController@store')->name('virmach.store');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin'], function () {
    Route::post('wablast/callback', 'WhatsappBlastController@callback')->name('wablast.callback');
});
Route::get('full-calender', [FullCalenderController::class, 'index']);

Route::post('full-calender/action', [FullCalenderController::class, 'action']);
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin\whatsapp', 'middleware' => ['auth']], function () {
    Route::delete('categoryWA/destroy', 'CategoryController@massDestroy')->name('categoryWA.massDestroy');
    Route::resource('categoryWA', 'CategoryController');
    Route::delete('WaTemplate/destroy', 'WaTemplateController@massDestroy')->name('WaTemplate.massDestroy');
    Route::resource('WaTemplate', 'WaTemplateController');
    //tunggakan
    // Route::get('wa/tunggakan', 'TunggakanController@index')->name('tunggakan.index');
    // Route::post('wa/tunggakan/template', 'TunggakanController@template')->name('tunggakan.template');
    // Route::post('wa/tunggakan/create', 'TunggakanController@create')->name('tunggakan.create');
    // Route::post('wa/tunggakan/store', 'TunggakanController@store')->name('tunggakan.store');
});
