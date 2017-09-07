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
// login
$app->post('/login', 'UserController@login'); 

$app->get('/user/{id}', ['middleware' => 'auth', 'uses' =>  'UserController@get_user']);
// getMyMbpSiteToMAp
$app->post('/getMyMbpSiteToMAp', 'MapController@getMyMbpSiteToMAp');

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
// getStatusMbp
$app->post('/getStatusMbp', 'MbpController@getStatusMbp');
// updateStatusMbptoOnProgress
$app->post('/updateStatusMbptoOnProgress', 'MbpController@updateStatusMbptoOnProgress');
// updateStatusMbptoCheckin
$app->post('/updateStatusMbptoCheckin', 'MbpController@updateStatusMbptoCheckin');
// updateStatusMbptoAvailable
$app->post('/updateStatusMbptoDone', 'MbpController@updateStatusMbptoDone');
// updateStatusMbp
$app->post('/updateStatusMbp', 'MbpController@updateStatusMbp');

// getMyMbpavaible

// RecommendationController, hitungJarakDuaPoint
$app->post('/calculateDistance', 'RecommendationController@calculateDistance'); 

// requestMbpToSiteDown, RtpoController
$app->post('/requestMbpToSiteDown', 'RtpoController@requestMbpToSiteDown'); 

// getMySiteDownAndMyMbpAvailable
$app->post('/getMySiteDownAndMyMbpAvailable', 'RecommendationController@getMySiteDownAndMyMbpAvailable'); 

// getRecomendationClassSite fungsi ini sudah termasuk pencarian mbp terdekat dengan site yang di rekomendasikan
$app->get('/getRecomendationClassAllSiteDown', 'RecommendationController@getRecomendationClassAllSiteDown'); 
// getListRecomendationMbp
$app->get('/getListRecomendationMbp', 'RecommendationController@getListRecomendationMbp');
// getListDistanceRecomendationSite
$app->get('/getListDistanceRecomendationSite', 'RecommendationController@getListDistanceRecomendationSite');



// fungsi rekomendasi lvl 1

// getSiteTerdekatDariMbp
$app->post('/getSiteTerdekatDariMbp', 'RecommendationController@getSiteTerdekatDariMbp'); //DONE..:D
$app->post('/getSiteTercepatDariMbp', 'RecommendationController@getSiteTercepatDariMbp'); //DONE..:D

// fungsi rekomendasi lvl 2

//getSiteClassTertinggiDariMbp
$app->post('/getSiteClassTertinggiDariMbpTercepat', 'RecommendationController@getSiteClassTertinggiDariMbpTercepat'); //DONE..:D dan sudah di urutkan dari class tertinggi dan waktu tempuh tercepat
$app->post('/getSiteClassTertinggiDariMbpTerdekat', 'RecommendationController@getSiteClassTertinggiDariMbpTerdekat'); //DONE..:D dan sudah di urutkan dari class tertinggi dan jarak tempuh terdekat

// getSiteDariMbpTerdekat
$app->post('/getSiteDariMbpTerdekat', 'RecommendationController@getSiteDariMbpTerdekat'); //DONE..:D dan 

// getMyMbpCategory
$app->post('/getMyMbpCategory', 'MbpController@getMyMbpCategory'); 


// sendMessage
$app->post('/sendMessage', 'MessageController@sendMessage'); 
// getMessage
$app->post('/getMessage', 'MessageController@getMessage'); 
// getMessageDetil
$app->post('/getMessageDetil', 'MessageController@getMessageDetil'); 


// sendCancellationLetterToRtpo
$app->post('/sendCancellationLetterToRtpo', 'CancelController@sendCancellationLetterToRtpo'); 
// acceptCancellationLetterfromMbp
$app->post('/acceptCancellationLetterfromMbp', 'CancelController@acceptCancellationLetterfromMbp'); 
// getCancellationLetter
$app->post('/getCancellationLetter', 'CancelController@getCancellationLetter'); 
// deleteCancellationLetterFromMbp
$app->post('/deleteCancellationLetterFromMbp', 'CancelController@deleteCancellationLetterFromMbp'); 

// sendDelayLetterToRtpo
$app->post('/sendDelayLetterToRtpo', 'CancelController@sendDelayLetterToRtpo'); 