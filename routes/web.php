<?php


use App\Http\Controllers\AdminController;
use App\Http\Controllers\Customers_ReportController;
use App\Http\Controllers\InvoiceAttachmentsController;
use App\Http\Controllers\Invoices_ReportController;
use App\Http\Controllers\InvoicesArchiveController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\InvoicesDetailsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});


Auth::routes();

//or Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('invoices', InvoicesController::class);

Route::resource('sections', SectionsController::class);

Route::resource('products', ProductsController::class);

Route::resource('InvoiceAttachments', InvoiceAttachmentsController::class);

Route::get('/section/{id}', [InvoicesController::class, 'getProducts']);

Route::get('/InvoicesDetails/{id}/{notification_id}', [InvoicesDetailsController::class, 'edit']);

Route::get('/edit_invoice/{id}', [InvoicesController::class, 'edit']);

Route::get('/status_show/{id}', [InvoicesController::class, 'show'])->name('status_show');

Route::post('/status_update/{id}', [InvoicesController::class, 'status_update'])->name('status_update');

Route::get('download/{invoice_number}/{file_name}', [InvoicesDetailsController::class, 'get_file']);

Route::get('view_file/{invoice_number}/{file_name}', [InvoicesDetailsController::class, 'open_file']);

Route::post('delete_file', [InvoicesDetailsController::class, 'destroy'])->name('delete_file');

Route::get('paid_invoices', [InvoicesController::class, 'paid_invoices']);

Route::get('unpaid_invoices', [InvoicesController::class, 'unpaid_invoices']);

Route::get('partial_invoices', [InvoicesController::class, 'partial_invoices']);

Route::get('invoices_archive', [InvoicesArchiveController::class, 'index']);

Route::get('print_invoice/{id}', [InvoicesController::class, 'print_invoice']);

Route::get('print_invoice_archive/{id}', [InvoicesArchiveController::class, 'print_invoice_archive']);

Route::resource('archive', InvoicesArchiveController::class);

Route::get('export_invoices', [InvoicesController::class, 'export']);

Route::group(['middleware' => ['auth']], function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
});

Route::get('invoices_report', [Invoices_ReportController::class, 'index']);

Route::post('search_invoices', [Invoices_ReportController::class, 'search_invoices']);

Route::get('customers_report', [Customers_ReportController::class, 'index'])->name("customers_report");

Route::post('search_customers', [Customers_ReportController::class, 'search_customers']);


Route::get('mark_all_as_read', [InvoicesController::class, 'mark_all_as_read'])->name('mark_all_as_read');

Route::get('unreadNotifications_count', [InvoicesController::class, 'unreadNotifications_count'])->name('unreadNotifications_count');

Route::get('unreadNotifications', [InvoicesController::class, 'unreadNotifications'])->name('unreadNotifications');

Route::get('/{page}', [AdminController::class, 'index']);
