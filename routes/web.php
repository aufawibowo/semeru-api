<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
	return $app->version();
});


$app->get('/bts_off', 'BtsController@get_bts_off'); 
// fixingSiteToRTPO
$app->get('/fixingSiteToRTPO', 'BtsController@fixingSiteToRTPO');
// getNumberConfig
$app->post('/getNumberConfig', 'ConfigAppController@getNumberConfig');


// fixingSiteToRTPO
// convertSitetmpToQueryInsert 
$app->get('/convertSitetmpToQueryInsert', 'BtsController@convertSitetmpToQueryInsert');
// setclassSite
$app->get('/setclassSite', 'BtsController@setclassSite');

$app->post('/register', 'UserController@register'); 
$app->post('/users_update', 'UserController@users_update'); 

$app->post('/login', 'UserController@login'); 
// loginApp
$app->post('/loginApp', 'UserController@loginApp'); 
$app->get('/user/{id}', ['middleware' => 'auth', 'uses' =>  'UserController@get_user']);
$app->post('/loginUserArea', 'UserController@loginUserArea'); 
$app->post('/cekUserType', 'UserController@cekUserType');
$app->post('/cekUserPIN', 'UserController@cekUserPIN');


// fungsi saat server private memberitahukan bahwa status mbp sudah berubah dari mainfail menjadi normal atau sebaliknya
$app->post('/setSiteMainsFail', 'SiteController@setSiteMainsFail'); 
$app->post('/update_report_location', 'SiteController@update_report_location'); 
$app->post('/updateSiteMainsFail', 'SiteController@updateSiteMainsFail'); 

$app->get('/cekFileMt', 'SiteController@cekFileMt'); 
$app->post('/cekFileMaintenance', 'SiteController@cekFileMaintenance'); 
$app->post('/cekFileReplacement', 'SiteController@cekFileReplacement'); 


$app->post('/cekSamplingFormula', 'RtpoController@cekSamplingFormula');
$app->post('/insertSamplingSite', 'RtpoController@insertSamplingSite');
$app->post('/getListSamplingSite', 'RtpoController@getListSamplingSite');
$app->post('/getDetailSamplingSite', 'RtpoController@getDetailSamplingSite');
$app->post('/checkDistanceSamplingSite', 'RtpoController@checkDistanceSamplingSite');
$app->post('/createSamplingSite', 'RtpoController@createSamplingSite');
$app->post('/getFinishSamplingSite', 'RtpoController@getFinishSamplingSite');
$app->post('/updateSyncSamplingSite', 'RtpoController@updateSyncSamplingSite');
$app->post('/updateFlagSamplingSite', 'RtpoController@updateFlagSamplingSite');
$app->post('/createSamplingSiteFlag', 'RtpoController@createSamplingSiteFlag');





//-> fungsi untuk melihat lokasi dan status emua site dan semua rtpo sampai rtpo memberikan tugas..
//. memberpihatkan semua status site dan mbp ke map.
$app->post('/getMyMbpSiteToMAp', 'MapController@getMyMbpSiteToMAp');
$app->post('/getAlarmSiteCorrective', 'MapController@getAlarmSiteCorrective');

//. fungsi rekomendasi menggunakan google matrix api, nnti di pikir lagi enaknya gmn.. hehee..
// getSiteDariMbpTerdekat
$app->post('/getSiteDariMbpTerdekat', 'RecommendationController@getSiteDariMbpTerdekat'); //DONE..:D dan 
$app->post('/getSiteTerdekatDariMbp', 'RecommendationController@getSiteTerdekatDariMbp'); //DONE..:D
$app->post('/getSiteTercepatDariMbp', 'RecommendationController@getSiteTercepatDariMbp'); //DONE..:D
$app->post('/getMbpDariSiteTerdekat', 'RecommendationController@getMbpDariSiteTerdekat'); //DONE..:D
//. fungsi rekomendasi menggunakan google matrix api dan sudah dikombinasikan dengan class site tertinggi, nnti di pikir lagi enaknya gmn.. hehee..
$app->post('/getSiteClassTertinggiDariMbpTercepat', 'RecommendationController@getSiteClassTertinggiDariMbpTercepat'); //DONE..:D dan 
$app->post('/getSiteClassTertinggiDariMbpTerdekat', 'RecommendationController@getSiteClassTertinggiDariMbpTerdekat'); //DONE..:D dan
//. rtpo memberikan tugas kepada mbp menuju site tertentu
/*cek pengiriman notif*/$app->post('/requestMbpToSiteDown', 'RtpoController@requestMbpToSiteDown'); 
/*cek pengiriman notif*/$app->post('/requestMbpToSiteDownNew', 'RtpoController@requestMbpToSiteDownNew'); 
// fungsi untuk membatalkan penugasan mbp ke site dari sisi rtpo
/*cek pengiriman notif*/$app->post('/cancelRequestMbpToSiteDown', 'RtpoController@cancelRequestMbpToSiteDown'); 



//-> fungsi untuk melakukan tugas dari rtpo -> mbp melaksanakan tugas dengan benar hingga done
//. mbp melihat statusnya sendiri
$app->post('/getStatusMbp', 'MbpController@getStatusMbp');
$app->post('/getStatusMbp1', 'MbpController@getStatusMbp1');
$app->post('/getStatusMbpNew', 'MbpController@getStatusMbpNew');
//. mengupdate status mbp menjadi sesuai keinginan, entah dia minta dijadiin (on progress, chck'in atau kembali ke available)
/*cek pengiriman notif*/$app->post('/updateStatusMbp', 'MbpController@updateStatusMbp');
/*cek pengiriman notif*/$app->post('/updateStatusMbpNew', 'MbpController@updateStatusMbpNew');
$app->post('/updateStatusMbp1', 'MbpController@updateStatusMbp1');
$app->post('/misiPenyelamatanDataMbp', 'MbpController@misiPenyelamatanDataMbp');



//-> fungsi untuk mbp melakukan pembatalan penugasan -> rtpo memberikan aksi terhadap pengajuan tersebut
//. mbp mengirim pengajuan pembatalan penugasan kepada rtpo
/*cek pengiriman notif*/$app->post('/sendCancellationLetterToRtpo', 'CancelController@sendCancellationLetterToRtpo'); 
/*cek pengiriman notif*/$app->post('/sendCancellationLetterToRtpoNew', 'CancelController@sendCancellationLetterToRtpoNew'); 
//. dan bila mbp ingin membatalkan pengajuannya, maka tinggal di delete aja dan tidak muncul di halaman rtpo
/*cek pengiriman notif*/$app->post('/deleteCancellationLetterFromMbp1', 'CancelController@deleteCancellationLetterFromMbp1');
/*cek pengiriman notif*/$app->post('/deleteCancellationLetterFromMbp', 'CancelController@deleteCancellationLetterFromMbp');/*(v)*/
// rtpo bisa melihat list pengajuan pembatalan dari mbp-mbpnya (bila sudah di lakukan aksi pada pesan tersebut maka tinggal di beri flag)
$app->post('/deleteCancellationLetterFromMbpTest', 'CancelController@deleteCancellationLetterFromMbpTest');/*(v)*/


$app->post('/getCancellationLetter1', 'CancelController@getCancellationLetter1');
$app->post('/getCancellationLetter', 'CancelController@getCancellationLetter');  
$app->post('/getCancellationLetterPaginate', 'CancelController@getCancellationLetterPaginate');  
// rtpo melihat detil dari pengajuan mbp
$app->post('/getMessageDetil1', 'MessageController@getMessageDetil1'); 
$app->post('/getMessageDetil', 'MessageController@getMessageDetil'); 
// disini merupakan aksi untuk melakukan persetujuan atau tidak menyetujui dengan yang di ajukan mbp 
/*cek pengiriman notif*/$app->post('/cancellationStatementRtpo1', 'CancelController@cancellationStatementRtpo1'); /*(v)*/
/*cek pengiriman notif*/$app->post('/cancellationStatementRtpo', 'CancelController@cancellationStatementRtpo'); /*(v)*/


//-> fungsi untuk mbp melakukan pemberitahuan delay -> rtpo memberikan aksi terhadap pengajuan tersebut (di genti dan dibatalkan / di)
// mengirimkan pesan pemberitahuan tentang delay yang dilakukan oleh mbp
/*cek pengiriman notif*/$app->post('/sendDelayLetterToRtpo', 'CancelController@sendDelayLetterToRtpo'); 
/*cek pengiriman notif*/$app->post('/sendDelayLetterToRtpoNew', 'CancelController@sendDelayLetterToRtpoNew'); 
// rtpo bisa melihat list pengajuan pembatalan dari mbp-mbpnya (bila sudah di lakukan aksi pada pesan tersebut maka tinggal di beri flag)
$app->post('/getCancellationLetter', 'CancelController@getCancellationLetter');  
// fungsi ini digunakan mbp bila delay yang mereka rasakan telah usai
/*cek pengiriman notif*/$app->post('/finishDelayFromMbp', 'CancelController@finishDelayFromMbp');/*(v)*/ //belim mencantumkan nama mbp
// delayStatementRtpo
/*cek pengiriman notif*/$app->post('/delayStatementRtpo1', 'CancelController@delayStatementRtpo1');/*(v)*/
/*cek pengiriman notif*/$app->post('/delayStatementRtpo', 'CancelController@delayStatementRtpo');/*(v)*/


//-> MELIHAT STATUS AKTIF DAN  TIDAK AKTIF DARI MBP -> DIA MERUBAH STATUSNYA SENDIRI
// untuk melihat status mbp aktif g aktif
$app->post('/getStatusActiveNotActive', 'MbpController@getStatusActiveNotActive');
// untuk rubah status dari unavailable ke available atau mksutnya dari atkif k gak aktif atau sebaliknya.
$app->post('/changeStatusActiveNotActive', 'MbpController@changeStatusActiveNotActive');


// FUNGSI UNTUK MENGAMBIL LIST NOTIFICASIONG
// getListNotification
$app->post('/getListNotification', 'NotificationController@getListNotification');
// getNotificationHomeRTPO
$app->post('/getNotificationHomeRTPO', 'NotificationController@getNotificationHomeRTPO');
// getNotificationHomeMBP
$app->post('/getNotificationHomeMBP', 'NotificationController@getNotificationHomeMBP');
// getNotificationHome
$app->post('/getNotificationHome', 'NotificationController@getNotificationHome');
// checkNotisMbpSOS
$app->post('/checkNotif', 'NotificationController@checkNotif');
$app->post('/getTelegramQueue', 'NotificationController@getTelegramQueue');





// -> melihat list sos sampai memberikan bantuan mbp
// getListSOS
$app->post('/getListSOS', 'SosController@getListSOS');
$app->post('/getListSOSPaginate', 'SosController@getListSOSPaginate');
// melihat mbp pinjamandari rtpo lain
// getMyMbpLoan
$app->post('/getBorrowedMbpList', 'SosController@getBorrowedMbpList');
// sendRequestSOS
$app->post('/sendRequestSOS', 'SosController@sendRequestSOS');
// getDetilSOS
$app->post('/getDetilSOS', 'SosController@getDetilSOS');
// sendMBPtoRTPOsos
$app->post('/sendMBPtoRTPOsos', 'SosController@sendMBPtoRTPOsos');
$app->post('/sendMBPtoRTPOsosNew', 'SosController@sendMBPtoRTPOsosNew');
// getListBorrowedMbp
$app->post('/getListBorrowedMbp', 'SosController@getListBorrowedMbp');
// returnedMbp
$app->post('/returnedMbp', 'SosController@returnedMbp');
// deleteMBPtoRTPOsos
$app->post('/deleteMBPtoRTPOsos', 'SosController@deleteMBPtoRTPOsos');
// returnedMbp_fixing
$app->post('/returnedMbp_fixing', 'SosController@returnedMbp_fixing');
// closedSos
$app->post('/closedSos', 'SosController@closedSos');
// editSos
$app->post('/editSos', 'SosController@editSos');
// rejectSos
$app->post('/rejectSos', 'SosController@rejectSos');
// getListSOS
$app->post('/CPOgetListSOS', 'SosController@CPOgetListSOS');
$app->post('/CPOgetListSOSPaginate', 'SosController@CPOgetListSOSPaginate');
// getListSOS
$app->post('/CPOgetListRTPOSosAnswer', 'SosController@CPOgetListRTPOSosAnswer');
// getListSOSneighbor
$app->post('/getListSOSneighbor', 'SosController@getListSOSneighbor');
// sendRequestSOSneighbor
$app->post('/sendRequestSOSneighbor', 'SosController@sendRequestSOSneighbor');



// readXml
$app->post('/readXml', 'OfflineController@readXml');




//OTP
$app->post('/setOtpMaintenance', 'OtpController@setOtpMaintenance');
$app->post('/checkOtpMaintenance', 'OtpController@checkOtpMaintenance');
$app->post('/setOtpTicketing', 'OtpController@setOtpTicketing');
$app->post('/checkOtpTicketing', 'OtpController@checkOtpTicketing');
// setOtpApp
$app->post('/setOtpLoginApp', 'OtpController@setOtpLoginApp');




// getListAssignment
$app->post('/getListAssignment', 'MbpController@getListAssignment');
$app->post('/getListAssignmentPaginate', 'MbpController@getListAssignmentPaginate');
// getListActiveNotActive
$app->post('/getListActiveNotActive', 'MbpController@getListActiveNotActive');








// uploadImage
// $app->post('/uploadImage', 'ImageController@uploadImage');
// getListStatusImage
$app->post('/getListStatusImage', 'ImageController@getListStatusImage');








// setNotification
$app->post('/setNotification', 'NotificationController@setNotification');




// CheckActiveMbp
$app->post('/CheckActiveMbp', 'CheckingController@CheckActiveMbp');
// CheckExpiredSos
$app->post('/CheckExpiredSos', 'CheckingController@CheckExpiredSos');








// sendNotification
$app->post('/sendNotification', 'FireBaseController@sendNotification');/*(v)*/
$app->get('/sendNotificationTelegram', 'FireBaseController@sendNotificationTelegram');/*(v)*/
$app->get('/move_zip', 'FireBaseController@move_zip');/*(v)*/
// delete_zip
$app->get('/delete_zip', 'FireBaseController@delete_zip');/*(v)*/
$app->get('/delete_xml', 'FireBaseController@delete_xml');/*(v)*/
$app->get('/proccess_zip_img', 'FireBaseController@proccess_zip_img');/*(v)*/
$app->get('/proccess_zip_img_GS', 'FireBaseController@proccess_zip_img_GS');/*(v)*/

$app->get('/delete_zip_image', 'FireBaseController@delete_zip_image');/*(v)*/
// download_img


// sendNotificationTelegram
// sendNotificationTest
$app->post('/sendNotificationTest', 'FireBaseController@sendNotificationTest');/*(v)*/
// testCurl
$app->post('/testCurl', 'FireBaseController@testCurl');/*(v)*/
$app->post('/sendNotificationQueueTelegram', 'FireBaseController@sendNotificationQueueTelegram');/*(v)*/
$app->post('/sendQueueNotificationFirebase', 'FireBaseController@sendQueueNotificationFirebase');/*(v)*/










// getHistorySupplyingPower
$app->post('/getListHistorySupplyingPower', 'SupplyingPowerController@getListHistorySupplyingPower');/*(v)*/
$app->post('/getListHistorySupplyingPowerPaginate', 'SupplyingPowerController@getListHistorySupplyingPowerPaginate');

$app->post('/getListHistorySupplyingPowerNS', 'SupplyingPowerController@getListHistorySupplyingPowerNS');/*(v)*/
$app->post('/getListHistorySupplyingPowerNSPaginate', 'SupplyingPowerController@getListHistorySupplyingPowerNSPaginate');
$app->post('/getListHistorySupplyingPowerCPO', 'SupplyingPowerController@getListHistorySupplyingPowerCPO');/*(v)*/
$app->post('/getListHistorySupplyingPowerCPOPaginate', 'SupplyingPowerController@getListHistorySupplyingPowerCPOPaginate');

// getListHistorySupplyingPowerRtpo
$app->post('/getListHistorySupplyingPowerRtpo', 'SupplyingPowerController@getListHistorySupplyingPowerRtpo');/*(v)*/
// setEvidenceNumber
$app->post('/setEvidenceNumber', 'SupplyingPowerController@setEvidenceNumber');/*(v)*/
// getDetailHistorySupplyingPower
$app->post('/getDetailHistorySupplyingPower', 'SupplyingPowerController@getDetailHistorySupplyingPower');/*(v)*/
$app->post('/closeSPTicketAfter3Day', 'SupplyingPowerController@closeSPTicketAfter3Day');/*(v)*/



// getDetailMbp
$app->post('/getDetailMbp', 'MbpController@getDetailMbp');/*(v)*/


// getAllSite
$app->get('/getAllSite', 'SiteController@getAllSite'); 
$app->get('/getAllSiteDown', 'SiteController@getAllSiteDown'); 
// getMySite
$app->post('/getMySite', 'SiteController@getMySite'); 
$app->post('/getMySiteDown', 'SiteController@getMySiteDown'); 
$app->post('/getMySiteCorrective', 'SiteController@getMySiteCorrective'); 
$app->post('/getMySitePaginate', 'SiteController@getMySitePaginate'); 
$app->post('/getListSite', 'SiteController@getListSite'); 

// getMySiteAll
$app->post('/getMySiteAll', 'SiteController@getMySiteAll'); 

$app->post('/get_site_name', 'SiteController@get_site_name'); 



// getMyMbp
$app->get('/getAllMbp', 'MbpController@getAllMbp'); 
$app->get('/getAllMbpOnProggress', 'MbpController@getAllMbpOnProggress'); 
// getMySite
$app->post('/getMyMbp', 'MbpController@getMyMbp'); 
$app->post('/getMyMbpPaginate', 'MbpController@getMyMbpPaginate'); 
$app->post('/getMyMbpCPOPaginate', 'MbpController@getMyMbpCPOPaginate'); 
$app->post('/getMyMbpNSPaginate', 'MbpController@getMyMbpNSPaginate'); 
$app->post('/getMyMbpFMCPaginate', 'MbpController@getMyMbpFMCPaginate'); 

$app->post('/getMyMbpCPO', 'MbpController@getMyMbpCPO'); 
$app->post('/getMyMbpNS', 'MbpController@getMyMbpNS'); 



$app->post('/getMyMbpOnProgress', 'MbpController@getMyMbpOnProgress'); 
$app->post('/getMyMbpAvailable', 'MbpController@getMyMbpAvailable'); 
$app->post('/getMyMbpWaiting', 'MbpController@getMyMbpWaiting'); 
// updateLatLongMbp
$app->get('/getMbp', 'MbpController@getMbp'); 
// getMbp
$app->get('/updateLatLongMbp', 'MbpController@updateLatLongMbp'); 
// updateStatusMbptoOnProgress
$app->post('/updateStatusMbptoOnProgress', 'MbpController@updateStatusMbptoOnProgress');
// updateStatusMbptoCheckin
$app->post('/updateStatusMbptoCheckin', 'MbpController@updateStatusMbptoCheckin');
// updateStatusMbptoAvailable
$app->post('/updateStatusMbptoDone', 'MbpController@updateStatusMbptoDone');//saya cari gk ada boskuh

// getMyMbpavaible

// RecommendationController, hitungJarakDuaPoint
$app->post('/calculateDistance', 'RecommendationController@calculateDistance'); 
// distanceWeb
$app->post('/distanceWeb', 'RecommendationController@distanceWeb'); 


// getMySiteDownAndMyMbpAvailable
$app->post('/getMySiteDownAndMyMbpAvailable', 'RecommendationController@getMySiteDownAndMyMbpAvailable'); 

// getRecomendationClassSite fungsi ini sudah termasuk pencarian mbp terdekat dengan site yang di rekomendasikan
$app->get('/getRecomendationClassAllSiteDown', 'RecommendationController@getRecomendationClassAllSiteDown'); 
// getListRecomendationMbp
$app->get('/getListRecomendationMbp', 'RecommendationController@getListRecomendationMbp');
// getListDistanceRecomendationSite
$app->get('/getListDistanceRecomendationSite', 'RecommendationController@getListDistanceRecomendationSite');
// getSiteRecomendationClassandNode
$app->post('/getSiteRecomendationClassandNode', 'RecommendationController@getSiteRecomendationClassandNode');
$app->post('/getSiteRecomendationClassandNodePaginate', 'RecommendationController@getSiteRecomendationClassandNodePaginate');
// getSiteRecomendationDistance
$app->post('/getSiteRecomendationDistance', 'RecommendationController@getSiteRecomendationDistance');
$app->post('/getSiteRecomendationDistancePaginate', 'RecommendationController@getSiteRecomendationDistancePaginate');
// getETAandDistanceMbpToSite
$app->post('/getETAandDistanceMbpToSite', 'RecommendationController@getETAandDistanceMbpToSite');



// fungsi rekomendasi lvl 1


// fungsi rekomendasi lvl 2



// getMyMbpCategory
$app->post('/getMyMbpCategory', 'MbpController@getMyMbpCategory'); 


// sendMessage
$app->post('/sendMessage', 'MessageController@sendMessage'); 
// getMessage
$app->post('/getMessage', 'MessageController@getMessage'); 


// acceptCancellationLetterfromMbp
$app->post('/acceptCancellationLetterfromMbp', 'CancelController@acceptCancellationLetterfromMbp'); 


// acceptCancel
$app->post('/approvedTheCancellationLetter', 'CancelController@approvedTheCancellationLetter'); 

// editTableCancel
// $app->post('/editTableCancel', 'CancelController@editTableCancel'); 
// acceptCancellationLetter
$app->post('/acceptCancellationLetter', 'CancelController@acceptCancellationLetter'); 

// ListChoiceDialog
$app->post('/ListChoiceDialog', 'ShowChoiceDialogController@ListChoiceDialog'); 

// sendNewLocSite
$app->post('/sendNewLocSite', 'SiteController@sendNewLocSite'); 
// listReportNewSite
$app->post('/listReportNewSite', 'SiteController@listReportNewSite'); 
// listHistoryReportNewSite
$app->post('/listHistoryReportNewSite', 'SiteController@listHistoryReportNewSite'); 
// detailReportSite
$app->post('/detailReportSite', 'SiteController@detailReportSite');
// approveReportNewLocSite
$app->post('/approveReportNewLocSite', 'SiteController@approveReportNewLocSite');
// checkReportSite
$app->post('/checkReportSite', 'SiteController@checkReportSite');
// loginMenuMaintenanceFromReport
$app->post('/loginMenuMaintenanceFromReport', 'SiteController@loginMenuMaintenanceFromReport');
// getMaintenanceOTP
$app->post('/getMaintenanceOTP', 'SiteController@getMaintenanceOTP');
// checkReportToLogin
$app->post('/checkReportToLogin', 'SiteController@checkReportToLogin');
// getDetailSiteFromSIK
$app->post('/getDetailSiteFromSIK', 'SiteController@getDetailSiteFromSIK');
// updateSiteDown
$app->post('/updateSiteDown', 'SiteController@updateSiteDown');
// updateSiteDownJatim
$app->post('/updateSiteDownJatim', 'SiteController@updateSiteDownJatim');
$app->post('/updateSiteDownJateng', 'SiteController@updateSiteDownJateng');
$app->post('/updateSiteDownBali', 'SiteController@updateSiteDownBali');
$app->get('/getSiteDownJateng', 'SiteController@getSiteDownJateng');




// listCategoryCorrective
$app->post('/listCategoryCorrective', 'CorrectiveController@listCategoryCorrective');
$app->post('/detilCorrectiveFromWeb', 'CorrectiveController@detilCorrectiveFromWeb');
// check15mntResponCorrective
$app->post('/check15mntResponCorrective', 'CorrectiveController@check15mntResponCorrective');

// updateEndDateCorrective
$app->post('/updateEndDateCorrective', 'CorrectiveController@updateEndDateCorrective');
// detilCorrectiveFromWeb
// appSetStatusAcceptCorrective
$app->post('/appSetStatusAcceptCorrective', 'CorrectiveController@appSetStatusAcceptCorrective');
// listCorrectiveJobs
$app->post('/listCorrectiveJobs', 'CorrectiveController@listCorrectiveJobs');
// sendCorrective
$app->post('/sendCorrective', 'CorrectiveController@sendCorrective');
// listCorrectiveFromFmc
$app->post('/listCorrectiveFromFmc', 'CorrectiveController@listCorrectiveFromFmc');
// takeCorrective
$app->post('/takeCorrective', 'CorrectiveController@takeCorrective');
// finishCorrective
$app->post('/finishCorrective', 'CorrectiveController@finishCorrective');
// listHistoryCorrectiveFromRTPO
$app->post('/listHistoryCorrectiveFromRTPO', 'CorrectiveController@listHistoryCorrectiveFromRTPO');
// detilCorrectiveFromRTPO
$app->post('/detilCorrectiveFromRTPO', 'CorrectiveController@detilCorrectiveFromRTPO');
// approveCorrective
$app->post('/approveCorrective', 'CorrectiveController@approveCorrective');
// rejectCorrective
$app->post('/rejectCorrective', 'CorrectiveController@rejectCorrective');
// appSendCorrective
$app->post('/appSendCorrective', 'CorrectiveController@appSendCorrective');
// appSetStatusResolveCorrective
$app->post('/appSetStatusResolveCorrective', 'CorrectiveController@appSetStatusResolveCorrective');
// appRTPOSetStatusCorrective
$app->post('/appRTPOSetStatusCorrective', 'CorrectiveController@appRTPOSetStatusCorrective');
// listCorrectiveFrom
$app->post('/listCorrectiveFrom', 'CorrectiveController@listCorrectiveFrom');
// detilCorrectiveFrom
$app->post('/detilCorrectiveFrom', 'CorrectiveController@detilCorrectiveFrom');
// listHistoryCorrectiveFrom
$app->post('/listHistoryCorrectiveFrom', 'CorrectiveController@listHistoryCorrectiveFrom');
// listHistoryCorrectiveFromFmc
$app->post('/listHistoryCorrectiveFromFmc', 'CorrectiveController@listHistoryCorrectiveFromFmc');
// sendPendingCorrective
$app->post('/sendPendingCorrective', 'CorrectiveController@sendPendingCorrective');
// responPendingCorrective
$app->post('/responPendingCorrective', 'CorrectiveController@responPendingCorrective');
// canceledCorrective
$app->post('/canceledCorrective', 'CorrectiveController@canceledCorrective');
// responCorrectiveByRtpo
$app->post('/responCorrectiveByRtpo', 'CorrectiveController@responCorrectiveByRtpo');
//test connection
$app->post('/ping', 'ConfigAppController@test_connection');

// SetSik
$app->post('/SetSik', 'ConfigAppController@SetSik');
$app->post('/getSiknoKosong', 'ConfigAppController@getSiknoKosong');
$app->post('/SetSpk', 'ConfigAppController@SetSpk');
$app->post('/getSpknoKosong', 'ConfigAppController@getSpknoKosong');

// download_img
$app->post('/download_img', 'ConfigAppController@download_img');
$app->post('/download_img_GS', 'ConfigAppController@download_img_GS');
// delete_image
$app->get('/delete_image', 'ConfigAppController@delete_image');
// SetADNnumber
$app->post('/SetADNnumber', 'ConfigAppController@SetADNnumber');
// fmcClusterUpdate
$app->post('/fmcClusterUpdate', 'ConfigAppController@fmcClusterUpdate');
// getMaintenanceReason
$app->post('/getMaintenanceReason', 'ConfigAppController@getMaintenanceReason');


/*
	PARSING XML MAINTENANCE DAN GANTI SPAREPART
*/
$app->post('/get_xml_ready', 'ConfigAppController@get_xml_pak_eko');
//get_return_xml
$app->post('/get_return_xml', 'ConfigAppController@get_return_xml');
$app->get('/cek_om', 'ConfigAppController@cek_om');

$app->post('/get_xml_ready_GS', 'ConfigAppController@get_xml_sparepart');
$app->post('/get_return_xml_GS', 'ConfigAppController@get_return_xml_GS');






// set_topic
$app->post('/set_topic', 'ConfigAppController@set_topic');

// get_previous_data_genset_fix
$app->post('/get_previous_data_maintenance', 'ConfigAppController@get_previous_data_maintenance');
// saveLogSP
$app->post('/saveLogSP', 'SupplyingPowerController@saveLogSP');

// check15mntResponMbp
$app->post('/check15mntResponMbp', 'SupplyingPowerController@check15mntResponMbp');

// sendImageValue
$app->post('/sendImageValue', 'ImageController@sendImageValue');

// mbp_update
$app->post('/mbp_update', 'MbpController@mbp_update');
// fmc_cluster_update
$app->post('/fmc_cluster_update', 'ConfigAppController@fmc_cluster_update');
// rtpo_update
$app->post('/rtpo_update', 'ConfigAppController@rtpo_update');
$app->post('/rtpoUpdate', 'ConfigAppController@rtpoUpdate');
$app->post('/fmcUpdate', 'ConfigAppController@fmcUpdate');

// setDataSitefromDStoMaster
$app->post('/setDataSitefromDStoMaster', 'SiteController@setDataSitefromDStoMaster');
// site_update
$app->post('/site_update', 'ConfigAppController@site_update');
// test
$app->get('/test', 'BtsController@test');
// getMbpFmc
$app->post('/getMbpFmc', 'MbpController@getMbpFmc');
// getMbpData
$app->post('/getMbpData', 'MbpController@getMbpData');

// setNotification
$app->post('/setNotificationV1', 'NotificationController@setNotificationV1');

// testnotifv1
$app->post('/testnotifv1', 'NotificationController@testnotifv1');

// getReportLocationSiteCount
$app->post('/getReportLocationSiteCount', 'NotificationController@getReportLocationSiteCount');

// getDataPencurian
$app->post('/getDataPencurian', 'TheftController@getDataPencurian');

// deleteDataPencurian
$app->post('/deleteDataPencurian', 'TheftController@deleteDataPencurian');
// deleteMaintenanceReason
$app->post('/deleteMaintenanceReason', 'ConfigAppController@deleteMaintenanceReason');

// getDataSP
$app->post('/getDataSP', 'SupplyingPowerController@getDataSP');

// getDataCorrective
$app->post('/getDataCorrective', 'CorrectiveController@getDataCorrective');

// getDataCorrective
$app->post('/getDataCorrective', 'CorrectiveController@getDataCorrective');

// getDataCorrectiveIsSync0
$app->post('/getDataCorrectiveIsSync0', 'CorrectiveController@getDataCorrectiveIsSync0');

// updateIsSyncCorrective
$app->post('/updateIsSyncCorrective', 'CorrectiveController@updateIsSyncCorrective');
// getActiveCorrectiveCount
$app->post('/getActiveCorrectiveCount', 'NotificationController@getActiveCorrectiveCount');
$app->get('/CloseTicketCorrectiveAfter24h', 'CorrectiveController@CloseTicketCorrectiveAfter24h');
$app->get('/CloseTicketCorrectiveTidakDiresponLebihDari15Menit', 'CorrectiveController@CloseTicketCorrectiveTidakDiresponLebihDari15Menit');

$app->post('/openPendingCorrectiveByFmc', 'CorrectiveController@openPendingCorrectiveByFmc');

$app->post('/upgradeTicketCorrectiveByFmc', 'CorrectiveController@upgradeTicketCorrectiveByFmc');

$app->post('/responUpgradeCorrective', 'CorrectiveController@responUpgradeCorrective');



// getLastVersion
$app->post('/getLastVersion', 'ConfigAppController@getLastVersion');
// getHistoryVersion
$app->post('/getHistoryVersion', 'ConfigAppController@getHistoryVersion');
// registration_topic
$app->post('/registration_topic', 'ConfigAppController@registration_topic');

// getDataSPIsSync0
$app->post('/getDataSPIsSync0', 'SupplyingPowerController@getDataSPIsSync0');
$app->post('/getDataSPbySPID', 'SupplyingPowerController@getDataSPbySPID');

// UpdateSyncSP
$app->post('/UpdateSyncSP', 'SupplyingPowerController@UpdateSyncSP');

// updateDataSpAdn
$app->post('/updateDataSpAdn', 'SupplyingPowerController@updateDataSpAdn');



// setMbpTrouble
$app->post('/setMbpTrouble', 'CancelController@setMbpTrouble');

// getMbpDataAll
$app->post('/getMbpDataAll', 'MbpController@getMbpDataAll');
// getLogMotionMbp
$app->get('/getLogMotionMbp', 'MbpController@getLogMotionMbp');
// setKwhMeterBefore
$app->post('/setKwhMeterBefore', 'MbpController@setKwhMeterBefore');
$app->post('/setKwhMeterAfter', 'MbpController@setKwhMeterAfter');
$app->post('/setRunningHourBefore', 'MbpController@setRunningHourBefore');
$app->post('/setRunningHourAfter', 'MbpController@setRunningHourAfter');


$app->post('/getListRegional', 'SuperUserController@getListRegional');

$app->post('/getListFmccluster', 'SuperUserController@getListFmccluster');
// getListFmccluster
//getListRtpoRegional
$app->post('/getListRtpoRegional', 'SuperUserController@getListRtpoRegional');
$app->post('/getListFmcRegional', 'SuperUserController@getListFmcRegional');
$app->post('/getListRtpoRegionalCpo', 'SuperUserController@getListRtpoRegionalCpo');
$app->post('/getListFmcRegionalCpo', 'SuperUserController@getListFmcRegionalCpo');

$app->post('/getListRtpoRegionalNsa', 'SuperUserController@getListRtpoRegionalNSa');
$app->post('/getListFmcRegionalNsa', 'SuperUserController@getListFmcRegionalNsa');
$app->post('/getListNsaRegional', 'SuperUserController@getListNsaRegional');


$app->post('/signinSuperUser', 'SuperUserController@signinSuperUser');

$app->get('/cekAdn', 'SuperUserController@cekAdn');
$app->get('/gantiAdn', 'SuperUserController@gantiAdn');
$app->get('/cekGambarMtBulanIni', 'SuperUserController@cekGambarMtBulanIni');
$app->post('/cekGambarMt', 'SuperUserController@cekGambarMt');
$app->get('/cekXmlMtBulanIni', 'SuperUserController@cekXmlMtBulanIni');
$app->get('/cekSiteTerakhirDiboking', 'SuperUserController@cekSiteTerakhirDiboking');



$app->post('/setRecomendationMbpSite', 'MbpController@setRecomendationMbpSite');
$app->post('/getRecomendationMbpSite', 'MbpController@getRecomendationMbpSite');
$app->post('/getRecomendationMbpSitePaginate', 'MbpController@getRecomendationMbpSitePaginate');
$app->post('/deleteRecomendationMbpSite', 'MbpController@deleteRecomendationMbpSite');


$app->post('/getMbpSiteDownCPO', 'MapController@getMbpSiteDownCPO');
$app->post('/getMbpSiteDownNS', 'MapController@getMbpSiteDownNS');

$app->post('/misiPenyelamatanDataMbp', 'MbpController@misiPenyelamatanDataMbp');


// AGUS 2019


$app->post('/getMbpSite', 'DashboardController@getMbpSite');
$app->post('/getMbpSiteNS', 'DashboardController@getMbpSiteNS');
$app->post('/getMbpSiteRTPO', 'DashboardController@getMbpSiteRTPO');
$app->post('/getMbpSiteRegional', 'DashboardController@getMbpSiteRegional');
$app->post('/getMbpSiteRTPO1', 'DashboardController@getMbpSiteRTPO1');
$app->post('/getMbpSiteNS1', 'DashboardController@getMbpSiteNS1');
$app->post('/getMbpSiteRegional1', 'DashboardController@getMbpSiteRegional1');
$app->post('/getMbpSiteArea', 'DashboardController@getMbpSiteArea');

$app->post('/loginMaintSite', 'LoginNewController@loginMaintSite');
$app->post('/loginGS', 'LoginNewController@loginGS');
$app->post('/SetSikNew', 'ConfigAppController@SetSikNew');
$app->post('/SetSpkNew', 'ConfigAppController@SetSpkNew');
$app->post('/cekJarak', 'LoginNewController@cekJarak');

$app->post('/getListFinishedSP', 'SupplyingPowerController@getListFinishedSP');
$app->post('/getDetailFinishedSP', 'SupplyingPowerController@getDetailFinishedSP');
$app->post('/approveFinishedSP', 'SupplyingPowerController@approveFinishedSP');
$app->post('/getListNotApprovedSP', 'SupplyingPowerController@getListNotApprovedSP');
$app->post('/getDetailNotApprovedSP', 'SupplyingPowerController@getDetailNotApprovedSP');

$app->post('/getMySiteCorrectivePaginate', 'SiteController@getMySiteCorrectivePaginate');
$app->post('/listReportNewSitePaginate', 'SiteController@listReportNewSitePaginate');
$app->post('/listHistoryReportNewSitePaginate', 'SiteController@listHistoryReportNewSitePaginate');

$app->post('/listCorrectiveFromPaginate', 'CorrectiveController@listCorrectiveFromPaginate');
$app->post('/listCorrectiveFromFmcPaginate', 'CorrectiveController@listCorrectiveFromFmcPaginate');
$app->post('/listHistoryCorrectiveFromPaginate', 'CorrectiveController@listHistoryCorrectiveFromPaginate');
$app->post('/listHistoryCorrectiveFromFmcPaginate', 'CorrectiveController@listHistoryCorrectiveFromFmcPaginate');
$app->post('/detilCorrectiveFromNew', 'CorrectiveController@detilCorrectiveFromNew');

$app->get('/downloadApk', 'AppsController@downloadApk');

$app->post('/getListRescheduleSik', 'RtpoController@getListRescheduleSik');
$app->post('/approveRescheduleSik', 'RtpoController@approveRescheduleSik');
$app->post('/rejectRescheduleSik', 'RtpoController@rejectRescheduleSik');

$app->get('/cekTanggal', 'ConfigAppController@cekTanggal');

$app->post('/getListNotificationPaginate', 'NotificationController@getListNotificationPaginate');

$app->post('/appDashboard', 'DashboardController@appDashboard');
$app->post('/dashboardFilter', 'DashboardController@dashboardFilter');
$app->post('/getListFilterName', 'DashboardController@getListFilterName');

$app->post('/deleteRlsDummy', 'SiteController@deleteRlsDummy');

$app->post('/getListFaq', 'AppsController@getListFaq');
//close tiket mbp
$app->post('/submitValueSP', 'SupplyingPowerController@submitValueSP');
$app->post('/autocloseSP', 'SupplyingPowerController@autocloseSP');
$app->post('/submitTiketTidakDikerjakan', 'SupplyingPowerController@submitTiketTidakDikerjakan');
$app->post('/loggingSPAutoClose', 'SupplyingPowerController@loggingSPAutoClose');

$app->post('/getListNS', 'DashboardController@getListNS');

$app->post('/updateRescheduleSIK', 'RtpoController@updateRescheduleSIK');
$app->post('/getProposeRescheduleSIK', 'RtpoController@getProposeRescheduleSIK');

$app->post('/changePIN', 'UserController@changePIN');
$app->post('/resetPIN', 'UserController@resetPIN');

$app->post('/tesHitungSLA', 'SupplyingPowerController@tesHitungSLA');
$app->post('/storeImage', 'SupplyingPowerController@storeImage');
$app->post('/getImageSP', 'SupplyingPowerController@getImageSP');
$app->post('/UpdateSyncImageSP', 'SupplyingPowerController@UpdateSyncImageSP');
$app->post('/tesDelIm', 'SupplyingPowerController@tesDelIm');


// $app->group(['prefix' => 'api'], function(){}
// );
//
$app->get('/api/supplying_power/fix_meet_sla', 'Api\QueryController@sp_fix_meet_sla');
$app->post('/api/supplying_power/get_sp_by_id_sync', 'Api\QueryController@sp_get_by_id_sync');

$app->get('api/test', ['middleware' => 'api_token', function () {
    //
    echo "work";
}]);

$app->group(['prefix' => 'api'], function () use ($app) {
    
    $app->post('/auth/get_otp', 'Api\AuthController@get_otp');
    $app->post('/auth/login', 'Api\AuthController@login');

    $app->get('/supplying_power/fix_meet_sla', 'Api\QueryController@sp_fix_meet_sla');
    $app->post('/supplying_power/fix_meet_sla', 'Api\QueryController@sp_fix_meet_sla');

    

    $app->group(['middleware'=>'api_token'], function () use ($app) {

    	//ambil data untuk dashboard
	    $app->post('/get_home_content', 'Api\DashboardController@get_data');
	    $app->post('/get_dashboard_data', 'Api\DashboardController@get_data_filter');
	    $app->post('/get_dashboard_filter', 'Api\DashboardController@get_filter');

	    //concern
	    $app->post('/concern/submit','Api\ConcernController@submit_concern');

        //new
        $app->post('/get_mbp_area', 'MbpController@getMbpArea');
        $app->post('/get_mbp_regional', 'MbpController@getMbpRegional');
        $app->post('/get_mbp_ns', 'MbpController@getMbpNS');
        $app->post('/get_mbp_rtpo', 'MbpController@getMbpRtpo');
        $app->post('/get_detail_mbp', 'MbpController@getDetailMbp');
    });

    $app->post('/sp/create_ticket', 'Api\SupplyingPowerController@create_ticket');
    
});



