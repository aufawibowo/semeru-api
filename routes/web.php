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
$app->post('/getMyMbpavailable', 'MbpController@getMyMbpavailable'); 
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
