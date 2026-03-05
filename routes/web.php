<?php

use App\Http\Controllers\Admin\PageDesignController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SubpageController;
use App\Http\Controllers\Admin\LayoutSetupController;
use App\Http\Controllers\Admin\MainDesignController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Front\FrontPageController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\SiteSeoController;

use App\Models\MainDesign;
use App\Models\Page;
use App\Models\Subpage;
use App\Models\PageDesign;

Route::get('/theme.css', [MainDesignController::class, 'themeCss'])->name('theme.css');

Route::get('/', function () {
    return Inertia::render('Index', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,

        'pages' => Page::where('active', 1)->get(),
        'subpages' => Subpage::where('active', 1)->get(),
        'mainDesign' => MainDesign::first(),
        'designs' => PageDesign::with(['banner', 'banners'])->get(),
    ]);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('pages', PageController::class);
    Route::resource('subpage', SubpageController::class);
    Route::resource('layout', LayoutSetupController::class);
    Route::resource('page-design', PageDesignController::class);
    Route::resource('content', ContentController::class);

    Route::get('/content', [ContentController::class, 'index']);
    Route::post('/content/save', [ContentController::class, 'save']);
    Route::post('/content/upload-file', [ContentController::class, 'uploadFile']);
    Route::post('/content/delete-file', [ContentController::class, 'deleteFile']);

    // ✅ Banner (target čteme v controlleru z query: ?target=page|subpage)
    Route::post('page-design/{id}/banner', [PageDesignController::class, 'uploadBanner'])
        ->name('page-design.banner.upload');

    Route::delete('page-design/{id}/banner', [PageDesignController::class, 'deleteBanner'])
        ->name('page-design.banner.delete');

    Route::post('page-design/{id}/banner-settings', [PageDesignController::class, 'saveBannerSettings'])
        ->name('page-design.banner.settings');

    Route::post('page-design/{id}/banner-items', [PageDesignController::class, 'uploadBannerItem'])
        ->name('page-design.banner-items.upload');

    Route::delete('page-design/{id}/banner-items/{banner}', [PageDesignController::class, 'deleteBannerItem'])
        ->name('page-design.banner-items.delete');

    Route::post('page-design/{id}/banner-items/reorder', [PageDesignController::class, 'reorderBannerItems'])
        ->name('page-design.banner-items.reorder');

    Route::post('page-design/{id}/banner-items/{banner}/thumbnail', [PageDesignController::class, 'uploadBannerThumbnail'])
        ->name('page-design.banner-items.thumbnail.upload');

    Route::post('page-design/{id}/banner-items/{banner}/text', [PageDesignController::class, 'saveBannerText'])
        ->name('page-design.banner-items.text.save');

    Route::post('page-design/{id}/banner-items/empty', [PageDesignController::class, 'createEmptyBannerItem'])
        ->name('page-design.banner-items.empty');

    Route::post('page-design/{id}/banner-items/{banner}/builder', [PageDesignController::class, 'saveBannerBuilder'])
        ->name('page-design.banner-items.builder.save');

    Route::post('page-design/{id}/banner-items/{banner}/text-style', [PageDesignController::class, 'saveBannerTextStyle'])
        ->name('page-design.banner-items.text-style.save');

    Route::post('page-design/{id}/banner-items/{banner}/image', [PageDesignController::class, 'uploadBannerItemImage'])
        ->name('page-design.banner-items.image.upload');

    Route::post('page-design/{id}/banner-items/{banner}/thumbnail-size', [PageDesignController::class, 'saveThumbnailSize'])
        ->name('page-design.banner-items.thumbnail-size.save');

    Route::post('page-design/{id}/banner-items/{banner}/thumbnail-delete', [PageDesignController::class, 'deleteBannerThumbnail'])
        ->name('page-design.banner-items.thumbnail.delete');

    Route::post('page-design/{id}/banner-items/static-empty', [PageDesignController::class, 'createEmptyStaticBannerItem'])
        ->name('page-design.banner-items.static-empty');

    Route::post('main-design/logo', [MainDesignController::class, 'uploadLogo'])->name('main-design.logo');
    Route::delete('main-design/logo', [MainDesignController::class, 'deleteLogo'])->name('main-design.logo.delete');
    Route::resource('main-design', MainDesignController::class);
    Route::post('main-design/footer-image', [MainDesignController::class, 'uploadFooterImage'])
        ->name('main-design.footer-image');

    Route::get('settings', [SiteSettingsController::class, 'index'])->name('settings');
    Route::post('settings/save', [SiteSettingsController::class, 'save'])->name('settings.save');

    Route::post('settings/upload-icons', [SiteSettingsController::class, 'uploadIcons'])->name('settings.uploadIcons');

    Route::post('settings/upload-asset', [SiteSettingsController::class, 'uploadAsset'])->name('settings.upload');
    Route::post('settings/delete-asset', [SiteSettingsController::class, 'deleteAsset'])->name('settings.delete');
    Route::get('settings/seo', [SiteSeoController::class, 'index']);
    Route::post('settings/seo/save', [SiteSeoController::class, 'save']);
});


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })
    ->name('dashboard');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('logout');

Route::get('{any}', [FrontPageController::class, 'show'])
    ->where('any', '.*');
