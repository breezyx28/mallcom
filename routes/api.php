<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountsControllerResource;
use App\Http\Controllers\AdditionalDescriptionControllerResource;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\AdsControllerResource;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryControllerResource;
use App\Http\Controllers\FavouritControllerResource;
use App\Http\Controllers\InvoiceControllerResource;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MaterialControllerResource;
use App\Http\Controllers\NotificationControllerResource;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderControllerResource;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductControllerResource;
use App\Http\Controllers\ProductSizesControllerResource;
use App\Http\Controllers\ProductsPhotoControllerResource;
use App\Http\Controllers\RateControllerResource;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RoleControllerResource;
use App\Http\Controllers\SizesControllerResource;
use App\Http\Controllers\StateControllerResource;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\StoreAddDescControllerResource;
use App\Http\Controllers\StoreControllerResource;
use App\Http\Controllers\StoreProdPhotControllerResource;
use App\Http\Controllers\StoreProductsControllerResource;
use App\Http\Controllers\UserAccountControllerResource;
use App\Http\Controllers\UserControllerResource;
use App\Http\Controllers\UserOrderControllerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;


const BASE = 'v1/user';
const GUEST = 'v1/public';
const ADMIN = 'v1/admin';

// Route::get(GUEST . 'facebook/redirect', function () {
//     return Socialite::driver('facebook')->redirect();
// });

// Route::get(GUEST . 'facebook/callback', function () {
//     $user = Socialite::driver('facebook')->user();

//     // $user->token
// });

Route::post(BASE . '/login', [LoginController::class, 'Login']);
Route::post(BASE . '/register', [RegisterController::class, 'register']);

Route::get(BASE . '/filter', [StatisticsController::class, 'productsFilter']);

// public routes
Route::group(['prefix' => GUEST], function () {
    // products
    Route::ApiResource('products', ProductControllerResource::class)->only('index', 'show');
    Route::get('top', [ProductController::class, 'topProduct']);
    Route::get('todayProducts', [ProductController::class, 'todayProducts']);
    Route::post('getProducts', [ProductController::class, 'getProducts']);

    // additional Description
    Route::ApiResource('additionalDescription', AdditionalDescriptionControllerResource::class)->only('index');

    // products photos
    Route::ApiResource('productsPhotos', ProductsPhotoControllerResource::class)->only('index');

    // states
    Route::apiResource('states', StateControllerResource::class)->only('index');

    // categories
    Route::apiResource('categories', CategoryControllerResource::class)->only('index');
    Route::get('categoryList', [CategoryController::class, 'categoryList']);

    // sizes
    Route::apiResource('sizes', SizesControllerResource::class)->only('index');

    // materials
    Route::apiResource('materilas', MaterialControllerResource::class)->only('index');

    // ads
    Route::apiResource('ads', AdsControllerResource::class)->only('index');
    Route::get('adsOptions', [AdsController::class, 'AdsOptions']);
    Route::get('adsGroupBy', [AdsController::class, 'AdsGroupBy']);
});

Route::group(['middleware' => 'auth.jwt'], function () {

    Route::group(['prefix' => BASE, 'middleware' => 'userWare'], function () {

        // user personal
        Route::get('profile', [LoginController::class, 'profile']);
        Route::post('logout', [LoginController::class, 'logout']);
        Route::put('resetPassword', [LoginController::class, 'resetPassword']);
        Route::put('updateProfile', [LoginController::class, 'updateProfile']);

        // user favourit
        Route::apiResource('favourit', FavouritControllerResource::class)->except('update');

        // user orders
        Route::apiResource('order', UserOrderControllerResource::class)->except('destroy', 'update');
        Route::get('myOrders', [OrderController::class, 'myOrders']);

        // user account
        Route::apiResource('myAccount', UserAccountControllerResource::class);

        // user invoices
        Route::get('myInvoices', [InvoicesController::class, 'myInvoices']);

        // rate product
        Route::apiResource('rate', RateControllerResource::class)->except('store', 'destroy');

        // rate notification
        Route::apiResource('myNotifications', NotificationControllerResource::class)->only('index');
    });

    Route::group(['prefix' => BASE, 'middleware' => 'storeWare'], function () {

        // store product
        Route::ApiResource('product', StoreProductsControllerResource::class);

        // store additional description
        Route::apiResource('storeAdditionalDescription', StoreAddDescControllerResource::class);

        // store product Photo
        Route::apiResource('storeProductPhotos', StoreProdPhotControllerResource::class);
    });

    Route::group(['prefix' => ADMIN, 'middleware' => 'adminWare'], function () {

        // statistics
        Route::get(BASE . '/statistics', [StatisticsController::class, 'statistics']);

        // users
        Route::apiResource('users', UserControllerResource::class);

        // stores
        Route::ApiResource('stores', StoreControllerResource::class);

        // products
        Route::ApiResource('products', ProductControllerResource::class)->except('store');

        // additional Description
        Route::ApiResource('additionalDescription', AdditionalDescriptionControllerResource::class);

        // products photos
        Route::ApiResource('productsPhotos', ProductsPhotoControllerResource::class);

        // products sizes
        Route::ApiResource('productSizes', ProductSizesControllerResource::class);

        // accounts
        Route::resource('accounts', AccountsControllerResource::class)->only('index');

        // invoices
        Route::ApiResource('invoices', InvoiceControllerResource::class)->only('index');

        // orders
        Route::apiResource('orders', OrderControllerResource::class)->except('destroy', 'store');

        // states
        Route::apiResource('states', StateControllerResource::class);

        // categories
        Route::apiResource('categories', CategoryControllerResource::class);

        // sizes
        Route::apiResource('sizes', SizesControllerResource::class);

        // materials
        Route::apiResource('materilas', MaterialControllerResource::class);

        // roles
        Route::apiResource('roles', RoleControllerResource::class);

        // ads
        Route::apiResource('ads', AdsControllerResource::class);
    });
});
