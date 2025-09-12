<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::group(['prefix' => 'open', 'namespace' => 'Api\V1\Admin'], function () {
//     Route::get('customers', 'CustomersController@index');
// });

Route::group(['prefix' => 'close/customer', 'namespace' => 'Api\V1\Customer', 'middleware' => 'auth:apicustomer'], function () {

    Route::post('ticket/store', 'TicketsApiController@store');

    Route::get('categories', 'CategoriesApiController@index');

    Route::get('tickets/{id}', 'TicketsApiController@index');

    Route::post('ctm/prev', 'CtmApiController@ctmPrev');

    Route::get('ctm/list/{id}', 'CtmApiController@ctmList');

    Route::get('ctm/pay/{id}', 'CtmApiController@ctmPay');

    Route::get('ctm/customer/{id}', 'CtmApiController@ctmCustomer');

    Route::post('ctm/request', 'CtmApiController@ctmRequest');
    Route::get('ctm/requesthistory/{id}', 'CtmApiController@ctmRequestHistory');

    Route::get('ctm/use/{id}', 'CtmApiController@ctmUse');

    Route::get('ctm/pay-new/{id}', 'CtmApiController@ctmPay');
});

Route::group(['prefix' => 'open/customer', 'namespace' => 'Api\V1\Customer'], function () {
    Route::post('login', 'CustomersApiController@login');
    Route::post('register/public', 'CustomersApiController@register_public');

    Route::post('OTP', 'CustomersApiController@smsApi');

    Route::get('logout',  'CustomersApiController@logout');

    Route::post('code', 'CustomersApiController@scanBarcode');

    Route::post('customerrequests', 'CustomersApiController@requestCustomer');

    Route::post('reset', 'CustomersApiController@reset');

    Route::post('register-upd', 'CustomersApiController@register');

    //test
    // Route::get('test', 'CustomersApiController@test');
    Route::get('test', 'TicketsApiController@test');
});

Route::group(['prefix' => 'close/admin', 'namespace' => 'Api\V1\Admin', 'middleware' => 'auth:apiadmin'], function () {

    //
    Route::get('admin/profile', 'AdminApiController@profile');
    // custmomer
    Route::resource('customers', 'CustomersApiController');
    Route::post('customer/list', 'CustomersApiController@customers');

    // categori
    Route::resource('categories', 'CategoriesApiController');
    Route::get('categories/list/{page}', 'CategoriesApiController@categories');
    Route::post('category-groups/list', 'CategoriesApiController@categoryGroups');
    Route::post('category-types/list', 'CategoriesApiController@categoryTypes');

    // dapettement
    Route::resource('dapertements', 'DapertementsApiController');
    Route::get('dapertements/list/{page}', 'DapertementsApiController@dapertements');

    // staffs
    Route::resource('staffs', 'StaffsApiController');
    Route::post('staffs/list', 'StaffsApiController@staffs');
    // ticket
    Route::resource('tickets', 'TicketsApiController');
    Route::post('ticket/list', 'TicketsApiController@tickets');
    Route::post('ticket-close', 'TicketsApiController@close');
    // ticket detail
    Route::post('ticket-detail', 'TicketsApiController@detailTicket');

    // action
    Route::resource('actions', 'ActionsApiController');
    Route::post('actionlists', 'ActionsApiController@list');
    Route::get('actionStaffs/{action_id}', 'ActionsApiController@actionStaffs');
    Route::get('actionStaffLists/{action_id}', 'ActionsApiController@actionStaffLists');
    Route::post('actionStaffStore', 'ActionsApiController@actionStaffStore');
    Route::put('actionStaffUpdate', 'ActionsApiController@actionStaffUpdate');
    Route::delete('actionStaffDestroy/{action}/{staff}', 'ActionsApiController@actionStaffDestroy');
    Route::post('actionStatusUpdate', 'ActionsApiController@actionStatusUpdate');


    Route::post('actiondetail', 'ActionsApiController@detail');

    // sub dapettement
    Route::resource('subdapertements', 'SubdapertementsApiController');
    Route::post('subdapertements/list', 'SubdapertementsApiController@subdapertements');

    Route::get('defcustomer', 'CustomersApiController@defcustomer');

    //SR aktif pasif
    Route::get('customer-sr', 'ActionsApiController@getSr');
    Route::get('customer-srnew', 'ActionsApiController@getSrnew');
    Route::post('ctm-mapping', 'ActionsApiController@getCtmmapping');
    Route::get('ctm-operator', 'ActionsApiController@getCtmoperator');
    Route::get('ctm-arealgroup', 'ActionsApiController@getCtmarealgroup');
    Route::post('ctm-kubikasi', 'ActionsApiController@getCtmkubikasi');
    Route::post('ctm-statussm', 'ActionsApiController@getCtmStatussm');
    Route::post('ctm-hasilbaca', 'ActionsApiController@getCtmHasilbaca');
    Route::post('audited', 'ActionsApiController@getAudited');
    Route::post('Permintaan', 'ActionsApiController@getPermintaan');
    Route::post('Complaint', 'ActionsApiController@getComplaint');

    //Segel
    Route::post('segel/list', 'ActionsApiController@segellist');
    Route::post('segel/store', 'ActionsApiController@lockStore');

    //Lock
    Route::post('lock/list', 'ActionsApiController@locklist');
    Route::delete('lockdestroy/{lockaction_id}', 'ActionsApiController@lockDestroy');
    Route::get('lockshow/{lock_id}', 'ActionsApiController@lockShow');
    Route::get('lockcreate/{lock_id}', 'ActionsApiController@scb');
    Route::post('SubDapertementlist', 'ActionsApiController@SubDapertementlist');
    Route::get('alarm-locks', 'ActionsApiController@alarmLocks');

    //LockStaff
    Route::get('lockStaffs/{lockaction_id}', 'ActionsApiController@lockStaffs');
    Route::get('lockStaffList/{lockaction_id}', 'ActionsApiController@lockStaffList');
    Route::post('lockStaffStore', 'ActionsApiController@lockStaffStore');
    Route::delete('lockStaffDestroy/{action}/{staff}', 'ActionsApiController@lockStaffDestroy');
    //LockAction
    Route::get('typeshow/{lockaction_id}', 'ActionsApiController@typeshow');
    Route::post('actionlocklists', 'ActionsApiController@actionlocklist');
    Route::post('lockactionscreate', 'ActionsApiController@lockactionscreate');
    Route::delete('lockactionsdestroy/{lockaction_id}', 'ActionsApiController@lockactionsDestroy');

    // payment
    // Route::resource('payments', 'PaymentApiController');
    Route::put('payments/edit', 'PaymentApiController@updatePay');
});


Route::group(['prefix' => 'open/admin', 'namespace' => 'Api\V1\Admin'], function () {
    Route::post('login',  'AdminApiController@login');
    //test
    Route::get('test/{id}', 'TicketsApiController@test');
    Route::post('login-js',  'AdminApiController@loginJs');

    Route::post('login-api',  'AdminApiController@loginApi');
});

Route::group(['prefix' => 'close/dapertement', 'namespace' => 'Api\V1\Dapertement'], function () {
    Route::get('actions/list/{dapertement_id}', 'ActionsApiController@list');
    Route::get('actions/listStaff/{ticket_id}', 'ActionsApiController@liststaff');
    Route::put('actions/edit', 'ActionsApiController@edit');
    Route::get('actions/ticket/{ticket_id}', 'ActionsApiController@ticket');
    Route::post('actionStaffUpdate', 'SubDapertementsApiController@edit');
    Route::get('actions/list/subdapertement/{action_id}}', 'SubDapertementsApiController@actionListSubDapertement');
});


Route::group(['prefix' => 'open/staff', 'as' => 'staff.', 'namespace' => 'Api\V1\Staff'], function () {
    Route::post('login',  'StaffApiController@login');
    Route::post('code', 'CustomersApiController@scanBarcode');
});

Route::group(['prefix' => 'close/staff', 'as' => 'staff.', 'namespace' => 'Api\V1\WaterMeter', 'middleware' => 'auth:apiadmin'], function () {
    Route::post('watermeter/actionWmNewImageUpdate',  'actionWmApiController@actionWmNewImageUpdate');
    Route::post('watermeter/actionWmdoneImageUpdate',  'actionWmApiController@actionWmdoneImageUpdate');
    Route::get('watermeter/area/{id}',  'actionWmApiController@area');

    Route::get('watermeter/approve/{id}',  'actionWmApiController@approve');
    Route::post('watermeter/approveProses',  'actionWmApiController@approveProses');

    Route::get('watermeter/show/{id}',  'actionWmApiController@show');
    Route::get('watermeter/showHistory/{id}',  'actionWmApiController@showHistory');
    Route::get('watermeter/indexStaff/{id}',  'actionWmApiController@indexStaff');
    Route::get('watermeter/addStaff/{id}',  'actionWmApiController@addStaff');
    Route::post('watermeter/addStaffStore',  'actionWmApiController@addStaffStore');
    Route::post('watermeter/destroy',  'actionWmApiController@destroy');
    Route::get('watermeter/indexActionWm/{id}',  'actionWmApiController@indexActionWm');
    Route::get('watermeter/{id}',  'actionWmApiController@index');

    // Route::post('code', 'CustomersApiController@scanBarcode');
});



Route::group(['prefix' => 'close/staff', 'as' => 'staff.', 'namespace' => 'Api\V1\Staff', 'middleware' => 'auth:apiadmin'], function () {
    Route::get('logout',  'StaffApiController@logout');
    Route::post('seal/store',  'SealApiController@store');
    Route::get('seal/{id}',  'SealApiController@index');
    Route::get('seal/history/{id}',  'SealApiController@history');
    Route::get('seal/show/{id}',  'SealApiController@show');
    Route::get('seal/history/show/{id}',  'SealApiController@historyShow');
    Route::get('map/{id}',  'CustomerApiController@getCtmarealgroup');
    Route::delete('seal/delete/{id}', 'SealApiController@destroy');

    Route::get('ctm/pay/{id}', 'CustomerApiController@ctmPay');
    Route::get('ctm/customer/{id}', 'CustomerApiController@ctmCustomer');

    // test
    Route::get('sealtest/{id}',  'SealTestApiController@index');
});


Route::group(['prefix' => 'close/admin1', 'namespace' => 'Api\V1\Admin1', 'middleware' => 'auth:apiadmin'], function () {


    // dapettement
    Route::resource('dapertements', 'DapertementsApiController');
    Route::get('dapertements/list/{page}', 'DapertementsApiController@dapertements');

    // staffs
    Route::resource('staffs', 'StaffsApiController');
    Route::post('staffs/list', 'StaffsApiController@staffs');
    // ticket
    Route::resource('tickets', 'TicketsApiController');
    Route::post('ticket/list', 'TicketsApiController@tickets1');
    Route::post('ticket-close', 'TicketsApiController@close');
    // ticket detail
    Route::post('ticket-detail', 'TicketsApiController@detailTicket');

    // action
    Route::resource('actions', 'ActionsApiController');
    Route::post('actionlists', 'ActionsApiController@list');
    Route::get('actionStaffs/{action_id}', 'ActionsApiController@actionStaffs');
    Route::get('actionStaffLists/{action_id}', 'ActionsApiController@actionStaffLists');
    Route::post('actionStaffStore', 'ActionsApiController@actionStaffStore');
    Route::put('actionStaffUpdate', 'ActionsApiController@actionStaffUpdate');
    Route::delete('actionStaffDestroy/{action}/{staff}', 'ActionsApiController@actionStaffDestroy');
    Route::post('actionStatusUpdate', 'ActionsApiController@actionStatusUpdate');
});

// absence
// Route::group(['prefix' => 'close/absence', 'namespace' => 'Api\V1\Absence', 'middleware' => 'auth:apiadmin'], function () {

//     Route::post('absence', 'AbsenceApiController@cekradius');
// });

// sementara
Route::group(['prefix' => 'close/absence', 'namespace' => 'Api\V1\Absence'], function () {
    Route::get('absence', 'AbsenceApiController@index');
    Route::get('history', 'AbsenceApiController@history');
    Route::get('requests/listFile', 'RequestApiController@listFile');
    Route::get('requests/history', 'RequestApiController@history');

    // admin start

    Route::post('loginAdmin', 'UserApiController@loginAdmin');
    Route::get('requests/requestApprove', 'RequestApiController@requestApprove');
    Route::get('requests/show', 'RequestApiController@show');
    Route::post('requests/approve', 'RequestApiController@approve');
    Route::post('requests/reject', 'RequestApiController@reject');

    // admin end

    Route::get('historyExtra', 'AbsenceApiController@historyExtra');
    Route::delete('requests/imageDelete/{id}', 'RequestApiController@imageDelete');
    Route::get('absence/schedule', 'AbsenceApiController@schedule');


    Route::get('requests/absenceList', 'RequestApiController@absenceList');
    Route::get('checkAbsenceLocation', 'AbsenceApiController@checkAbsenceLocation');
    Route::get('menu', 'MenuApiController@index');
    Route::get('menu/graphic', 'MenuApiController@graphic');

    Route::get('shift', 'ShiftApiController@index');
    Route::get('shift/listChange', 'ShiftApiController@listChange');
    Route::get('requests/getPermissionCat', 'RequestApiController@getPermissionCat');
    Route::post('absence/store', 'AbsenceApiController@store');
    Route::post('absence/storeExtra', 'AbsenceApiController@storeExtra');


    Route::get('message', 'MessageApiController@index');
    Route::post('message/read', 'MessageApiController@read');
    Route::post('message/check', 'MessageApiController@check');


    Route::post('absence/storeLocationEnd', 'AbsenceApiController@storeLocationEnd');
    Route::post('absence/storeLocation', 'AbsenceApiController@storeLocation');
    Route::post('absence/storeLocationExtra', 'AbsenceApiController@storeLocationExtra');
    Route::post('absence/storeLocationDuty', 'AbsenceApiController@storeLocationDuty');
    Route::post('leaveEnd', 'AbsenceApiController@leaveEnd');

    Route::post('requests/update', 'RequestApiController@update');
    Route::post('requests/store', 'RequestApiController@store');
    Route::post('shift/update', 'ShiftApiController@update');
    Route::post('shift/approve', 'ShiftApiController@approve');
    Route::post('login', 'UserApiController@login');


    Route::post('user/update', 'UserApiController@update');
    Route::get('shift/myShift', 'ShiftApiController@myShift');
    Route::get('shiftChange', 'ShiftChangeApiController@index');
    Route::post('shiftChange/store', 'ShiftChangeApiController@store');

    Route::get('changeShiftProposal', 'ShiftChangeApiController@changeShiftProposal');
    Route::get('changeShift', 'ShiftChangeApiController@changeShift');
    Route::post('changeShiftApprove', 'ShiftChangeApiController@changeShiftApprove');


    Route::get('holiday', 'AbsenceApiController@holiday');
});

// cronjob absen
Route::group(['prefix' => 'close/cronjob', 'namespace' => 'Api\V1\CronJob'], function () {
    Route::get('cronjob', 'AbsenceCronJobApiController@index');
    Route::get('cronjob/problem', 'AbsenceCronJobApiController@problemRemainer');
});




// sementara
Route::group(['prefix' => 'close/visit', 'namespace' => 'Api\V1\Visit'], function () {
    Route::get('visit', 'VisitApiController@index');
    Route::get('show/{id}', 'VisitApiController@show');
    Route::post('store', 'VisitApiController@store');
    Route::get('history', 'AbsenceApiController@history');
    Route::get('requests/listFile', 'RequestApiController@listFile');
    Route::get('requests/history', 'RequestApiController@history');
    Route::delete('requests/imageDelete/{id}', 'RequestApiController@imageDelete');
    Route::get('absence/schedule', 'AbsenceApiController@schedule');


    Route::get('requests/absenceList', 'RequestApiController@absenceList');
    Route::get('checkAbsenceLocation', 'AbsenceApiController@checkAbsenceLocation');
    Route::get('menu', 'MenuApiController@index');
    Route::get('shift', 'ShiftApiController@index');
    Route::get('shift/listChange', 'ShiftApiController@listChange');
    Route::get('requests/getPermissionCat', 'RequestApiController@getPermissionCat');
    Route::post('absence/store', 'AbsenceApiController@store');


    Route::get('message', 'MessageApiController@index');
    Route::post('message/read', 'MessageApiController@read');
    Route::post('message/check', 'MessageApiController@check');


    Route::post('absence/storeLocationEnd', 'AbsenceApiController@storeLocationEnd');
    Route::post('absence/storeLocation', 'AbsenceApiController@storeLocation');
    Route::post('absence/storeLocationExtra', 'AbsenceApiController@storeLocationExtra');
    Route::post('absence/storeLocationDuty', 'AbsenceApiController@storeLocationDuty');
    Route::post('leaveEnd', 'AbsenceApiController@leaveEnd');

    Route::post('requests/update', 'RequestApiController@update');
    Route::post('requests/store', 'RequestApiController@store');
    Route::post('shift/update', 'ShiftApiController@update');
    Route::post('shift/approve', 'ShiftApiController@approve');
    Route::post('login', 'UserApiController@login');
    Route::post('user/update', 'UserApiController@update');
    Route::get('shift/myShift', 'ShiftApiController@myShift');
    Route::get('shiftChange', 'ShiftChangeApiController@index');
    Route::post('shiftChange/store', 'ShiftChangeApiController@store');

    Route::get('changeShiftProposal', 'ShiftChangeApiController@changeShiftProposal');
    Route::get('changeShift', 'ShiftChangeApiController@changeShift');
    Route::post('changeShiftApprove', 'ShiftChangeApiController@changeShiftApprove');


    Route::get('holiday', 'AbsenceApiController@holiday');
});
