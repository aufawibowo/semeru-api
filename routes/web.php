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
$app->post('/register', 'UserController@register'); 
$app->post('/login', 'UserController@login'); 
$app->get('/user/{id}', ['middleware' => 'auth', 'uses' =>  'UserController@get_user']);



//-> fungsi untuk melihat lokasi dan status emua site dan semua rtpo sampai rtpo memberikan tugas..
//. memberpihatkan semua status site dan mbp ke map.
$app->post('/getMyMbpSiteToMAp', 'MapController@getMyMbpSiteToMAp');
//. fungsi rekomendasi menggunakan google matrix api, nnti di pikir lagi enaknya gmn.. hehee..
// getSiteDariMbpTerdekat
$app->post('/getSiteDariMbpTerdekat', 'RecommendationController@getSiteDariMbpTerdekat'); //DONE..:D dan 
$app->post('/getSiteTerdekatDariMbp', 'RecommendationController@getSiteTerdekatDariMbp'); //DONE..:D
$app->post('/getSiteTercepatDariMbp', 'RecommendationController@getSiteTercepatDariMbp'); //DONE..:D
//. fungsi rekomendasi menggunakan google matrix api dan sudah dikombinasikan dengan class site tertinggi, nnti di pikir lagi enaknya gmn.. hehee..
$app->post('/getSiteClassTertinggiDariMbpTercepat', 'RecommendationController@getSiteClassTertinggiDariMbpTercepat'); //DONE..:D dan 
$app->post('/getSiteClassTertinggiDariMbpTerdekat', 'RecommendationController@getSiteClassTertinggiDariMbpTerdekat'); //DONE..:D dan
//. rtpo memberikan tugas kepada mbp menuju site tertentu
/*cek pengiriman notif*/$app->post('/requestMbpToSiteDown', 'RtpoController@requestMbpToSiteDown'); 
// fungsi untuk membatalkan penugasan mbp ke site dari sisi rtpo
/*cek pengiriman notif*/$app->post('/cancelRequestMbpToSiteDown', 'RtpoController@cancelRequestMbpToSiteDown'); 



//-> fungsi untuk melakukan tugas dari rtpo -> mbp melaksanakan tugas dengan benar hingga done
//. mbp melihat statusnya sendiri
$app->post('/getStatusMbp', 'MbpController@getStatusMbp');
//. mengupdate status mbp menjadi sesuai keinginan, entah dia minta dijadiin (on progress, chck'in atau kembali ke available)
/*cek pengiriman notif*/$app->post('/updateStatusMbp', 'MbpController@updateStatusMbp');


//-> fungsi untuk mbp melakukan pembatalan penugasan -> rtpo memberikan aksi terhadap pengajuan tersebut
//. mbp mengirim pengajuan pembatalan penugasan kepada rtpo
/*cek pengiriman notif*/$app->post('/sendCancellationLetterToRtpo', 'CancelController@sendCancellationLetterToRtpo'); 
//. dan bila mbp ingin membatalkan pengajuannya, maka tinggal di delete aja dan tidak muncul di halaman rtpo
/*cek pengiriman notif*/$app->post('/deleteCancellationLetterFromMbp', 'CancelController@deleteCancellationLetterFromMbp');/*(v)*/
// rtpo bisa melihat list pengajuan pembatalan dari mbp-mbpnya (bila sudah di lakukan aksi pada pesan tersebut maka tinggal di beri flag)
$app->post('/getCancellationLetter', 'CancelController@getCancellationLetter');  
// rtpo melihat detil dari pengajuan mbp
$app->post('/getMessageDetil', 'MessageController@getMessageDetil'); 
// disini merupakan aksi untuk melakukan persetujuan atau tidak menyetujui dengan yang di ajukan mbp 
/*cek pengiriman notif*/$app->post('/cancellationStatementRtpo', 'CancelController@cancellationStatementRtpo'); /*(v)*/


//-> fungsi untuk mbp melakukan pemberitahuan delay -> rtpo memberikan aksi terhadap pengajuan tersebut (di genti dan dibatalkan / di)
// mengirimkan pesan pemberitahuan tentang delay yang dilakukan oleh mbp
/*cek pengiriman notif*/$app->post('/sendDelayLetterToRtpo', 'CancelController@sendDelayLetterToRtpo'); 
// rtpo bisa melihat list pengajuan pembatalan dari mbp-mbpnya (bila sudah di lakukan aksi pada pesan tersebut maka tinggal di beri flag)
$app->post('/getCancellationLetter', 'CancelController@getCancellationLetter');  
// fungsi ini digunakan mbp bila delay yang mereka rasakan telah usai
/*cek pengiriman notif*/$app->post('/finishDelayFromMbp', 'CancelController@finishDelayFromMbp');/*(v)*/ //belim mencantumkan nama mbp
// delayStatementRtpo
/*cek pengiriman notif*/$app->post('/delayStatementRtpo', 'CancelController@delayStatementRtpo');/*(v)*/


//-> MELIHAT STATUS AKTIF DAN  TIDAK AKTIF DARI MBP -> DIA MERUBAH STATUSNYA SENDIRI
// untuk melihat status mbp aktif g aktif
$app->post('/getStatusActiveNotActive', 'MbpController@getStatusActiveNotActive');
// untuk rubah status dari unavailable ke available atau mksutnya dari atkif k gak aktif atau sebaliknya.
$app->post('/changeStatusActiveNotActive', 'MbpController@changeStatusActiveNotActive');





// sendNotification
$app->post('/sendNotification', 'FireBaseController@sendNotification');/*(v)*/









// getHistorySupplyingPower
$app->post('/getListHistorySupplyingPower', 'SupplyingPowerController@getListHistorySupplyingPower');/*(v)*/
// getDetailHistorySupplyingPower
$app->post('/getDetailHistorySupplyingPower', 'SupplyingPowerController@getDetailHistorySupplyingPower');/*(v)*/


// getDetailMbp
$app->post('/getDetailMbp', 'MbpController@getDetailMbp');/*(v)*/


// getAllSite
$app->get('/getAllSite', 'SiteController@getAllSite'); 
$app->get('/getAllSiteDown', 'SiteController@getAllSiteDown'); 
// getMySite
$app->post('/getMySite', 'SiteController@getMySite'); 
$app->post('/getMySiteDown', 'SiteController@getMySiteDown'); 

// getMyMbp
$app->get('/getAllMbp', 'MbpController@getAllMbp'); 
$app->get('/getAllMbpOnProggress', 'MbpController@getAllMbpOnProggress'); 
// getMySite
$app->post('/getMyMbp', 'MbpController@getMyMbp'); 
$app->post('/getMyMbpOnProgress', 'MbpController@getMyMbpOnProgress'); 
$app->post('/getMyMbpAvailable', 'MbpController@getMyMbpAvailable'); 
$app->post('/getMyMbpWaiting', 'MbpController@getMyMbpWaiting'); 
// updateLatLongMbp
$app->post('/updateLatLongMbp', 'MbpController@updateLatLongMbp'); 
// updateStatusMbptoOnProgress
$app->post('/updateStatusMbptoOnProgress', 'MbpController@updateStatusMbptoOnProgress');
// updateStatusMbptoCheckin
$app->post('/updateStatusMbptoCheckin', 'MbpController@updateStatusMbptoCheckin');
// updateStatusMbptoAvailable
$app->post('/updateStatusMbptoDone', 'MbpController@updateStatusMbptoDone');

// getMyMbpavaible

// RecommendationController, hitungJarakDuaPoint
$app->post('/calculateDistance', 'RecommendationController@calculateDistance'); 


// getMySiteDownAndMyMbpAvailable
$app->post('/getMySiteDownAndMyMbpAvailable', 'RecommendationController@getMySiteDownAndMyMbpAvailable'); 

// getRecomendationClassSite fungsi ini sudah termasuk pencarian mbp terdekat dengan site yang di rekomendasikan
$app->get('/getRecomendationClassAllSiteDown', 'RecommendationController@getRecomendationClassAllSiteDown'); 
// getListRecomendationMbp
$app->get('/getListRecomendationMbp', 'RecommendationController@getListRecomendationMbp');
// getListDistanceRecomendationSite
$app->get('/getListDistanceRecomendationSite', 'RecommendationController@getListDistanceRecomendationSite');



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


