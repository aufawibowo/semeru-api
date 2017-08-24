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


