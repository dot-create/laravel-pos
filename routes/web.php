<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

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

include_once('install_r.php');

// Clear Cache Routes
Route::get('clear', function() {
    Artisan::call("view:clear");
    Artisan::call("cache:clear");
    Artisan::call("optimize:clear");
    return "clear";
});

// Guest Routes with setData Middleware
Route::middleware(['setData'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Auth::routes();

    // Business Registration
    Route::prefix('/business/register')->group(function () {
        Route::get('', 'BusinessController@getRegister')->name('business.getRegister');
        Route::post('', 'BusinessController@postRegister')->name('business.postRegister');
        Route::post('check-username', 'BusinessController@postCheckUsername')->name('business.postCheckUsername');
        Route::post('check-email', 'BusinessController@postCheckEmail')->name('business.postCheckEmail');
    });

    // Invoice and Quote Routes
    Route::get('/invoice/{token}', 'SellPosController@showInvoice')->name('show_invoice');
    Route::get('/quote/{token}', 'SellPosController@showInvoice')->name('show_quote');
    
    // Payment Routes
    Route::get('/pay/{token}', 'SellPosController@invoicePayment')->name('invoice_payment');
    Route::post('/confirm-payment/{id}', 'SellPosController@confirmPayment')->name('confirm_payment');
});

// Public Expense Route
Route::get('/expenses/get_purchases', 'ExpenseController@getPurchases');

// Authenticated User Routes
Route::middleware([
    'setData', 
    'auth', 
    'SetSessionData', 
    'language', 
    'timezone', 
    'AdminSidebarMenu', 
    'CheckUserLogin'
])->group(function () {
    
    // Service Staff Routes
    Route::get('service-staff-availability', 'SellPosController@showServiceStaffAvailibility');
    Route::get('pause-resume-service-staff-timer/{user_id}', 'SellPosController@pauseResumeServiceStaffTimer');
    Route::get('mark-as-available/{user_id}', 'SellPosController@markAsAvailable');

    // Purchase Requisition Routes
    Route::resource('purchase-requisition', 'PurchaseRequisitionController')->except(['edit', 'update']);
    Route::post('/get-requisition-products', 'PurchaseRequisitionController@getRequisitionProducts')->name('get-requisition-products');
    Route::get('get-purchase-requisitions/{location_id}', 'PurchaseRequisitionController@getPurchaseRequisitions');
    Route::get('locations/{location_id}/get_currency_details', 'PurchaseRequisitionController@getCurrencyDetails');
    Route::get('get-purchase-requisition-lines/{purchase_requisition_id}', 'PurchaseRequisitionController@getPurchaseRequisitionLines');

    // User Management
    Route::get('/sign-in-as-user/{id}', 'ManageUserController@signInAsUser')->name('sign-in-as-user');
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/home/get-totals', 'HomeController@getTotals');
    Route::get('/home/product-stock-alert', 'HomeController@getProductStockAlert');
    Route::get('/home/purchase-payment-dues', 'HomeController@getPurchasePaymentDues');
    Route::get('/home/sales-payment-dues', 'HomeController@getSalesPaymentDues');
    Route::post('/attach-medias-to-model', 'HomeController@attachMediasToGivenModel')->name('attach.medias.to.model');
    Route::get('/calendar', 'HomeController@getCalendar')->name('calendar');

    // Business Configuration
    Route::post('/test-email', 'BusinessController@testEmailConfiguration');
    Route::post('/test-sms', 'BusinessController@testSmsConfiguration');
    Route::get('/business/settings', 'BusinessController@getBusinessSettings')->name('business.getBusinessSettings');
    Route::post('/business/update', 'BusinessController@postBusinessSettings')->name('business.postBusinessSettings');
    Route::post('reset_database_delete', 'BusinessController@reset_database_delete')->name('reset_database_delete');
    Route::get('reset-database', 'BusinessController@reset_database')->name('reset_database');

    // User Profile
    Route::get('/user/profile', 'UserController@getProfile')->name('user.getProfile');
    Route::post('/user/update', 'UserController@updateProfile')->name('user.updateProfile');
    Route::post('/user/update-password', 'UserController@updatePassword')->name('user.updatePassword');

    // Resource Routes
    Route::resource('brands', 'BrandController');
    Route::resource('payment-account', 'PaymentAccountController');
    Route::resource('tax-rates', 'TaxRateController');
    Route::resource('units', 'UnitController');
    Route::resource('ledger-discount', 'LedgerDiscountController')->only(['edit', 'destroy', 'store', 'update']);

    // Contact Management
    Route::post('check-mobile', 'ContactController@checkMobile');
    Route::get('/get-contact-due/{contact_id}', 'ContactController@getContactDue');
    Route::get('/contacts/payments/{contact_id}', 'ContactController@getContactPayments');
    Route::get('/contacts/map', 'ContactController@contactMap');
    Route::get('/contacts/update-status/{id}', 'ContactController@updateStatus');
    Route::get('/contacts/stock-report/{supplier_id}', 'ContactController@getSupplierStockReport');
    Route::get('/contacts/ledger', 'ContactController@getLedger');
    Route::post('/contacts/send-ledger', 'ContactController@sendLedger');
    Route::get('/contacts/import', 'ContactController@getImportContacts')->name('contacts.import');
    Route::post('/contacts/import', 'ContactController@postImportContacts');
    Route::post('/contacts/check-contacts-id', 'ContactController@checkContactId');
    Route::get('/contacts/customers', 'ContactController@getCustomers');
    Route::resource('contacts', 'ContactController');
    Route::post('add-contact', 'ContactController@addContact')->name('add.contact');
    Route::post('delete-contact', 'ContactController@deleteContact')->name('contact.delete.person');

    // Taxonomy and Variations
    Route::get('taxonomies-ajax-index-page', 'TaxonomyController@getTaxonomyIndexPage');
    Route::resource('taxonomies', 'TaxonomyController');
    Route::resource('variation-templates', 'VariationTemplateController');

    // Product Management
    Route::prefix('/products')->group(function () {
        Route::get('download-excel', 'ProductController@downloadExcel');
        Route::get('stock-history/{id}', 'ProductController@productStockHistory');
        Route::get('delete-media/{media_id}', 'ProductController@deleteMedia');
        Route::post('mass-deactivate', 'ProductController@massDeactivate');
        Route::get('activate/{id}', 'ProductController@activate');
        Route::get('view-product-group-price/{id}', 'ProductController@viewGroupPrice');
        Route::get('add-selling-prices/{id}', 'ProductController@addSellingPrices');
        Route::post('save-selling-prices', 'ProductController@saveSellingPrices');
        Route::post('mass-delete', 'ProductController@massDestroy');
        Route::get('view/{id}', 'ProductController@view');
        Route::get('list', 'ProductController@getProducts');
        Route::get('list-no-variation', 'ProductController@getProductsWithoutVariations');
        Route::post('bulk-edit', 'ProductController@bulkEdit');
        Route::post('bulk-update', 'ProductController@bulkUpdate');
        Route::post('bulk-update-location', 'ProductController@updateProductLocation');
        Route::get('get-product-to-edit/{product_id}', 'ProductController@getProductToEdit');
        Route::post('get_sub_categories', 'ProductController@getSubCategories');
        Route::get('get_sub_units', 'ProductController@getSubUnits');
        Route::post('product_form_part', 'ProductController@getProductVariationFormPart');
        Route::post('get_product_variation_row', 'ProductController@getProductVariationRow');
        Route::post('get_variation_template', 'ProductController@getVariationTemplate');
        Route::get('get_variation_value_row', 'ProductController@getVariationValueRow');
        Route::post('check_product_sku', 'ProductController@checkProductSku');
        Route::post('validate_variation_skus', 'ProductController@validateVaritionSkus');
        Route::get('quick_add', 'ProductController@quickAdd');
        Route::post('save_quick_product', 'ProductController@saveQuickProduct');
        Route::get('get-combo-product-entry-row', 'ProductController@getComboProductEntryRow');
        Route::post('toggle-woocommerce-sync', 'ProductController@toggleWooCommerceSync');
    });
    
    Route::resource('weight-units', 'WeightUnitController')->except(['show']);
    Route::get('get-weight-units', 'WeightUnitController@getUnits')->name('weight-units.get');

    Route::get('shipping-ways/get', 'ShippingWayController@get')->name('shipping-ways.get');
    Route::resource('shipping-ways', 'ShippingWayController')->except(['show']);

    // Rack Positions
    Route::get('product-racks', 'ProductRackController@index')->name('product-racks.index');
    Route::get('product-racks/get', 'ProductRackController@get')->name('product-racks.get');
    Route::get('product-racks/create', 'ProductRackController@create');
    Route::post('product-racks', 'ProductRackController@store');
    Route::get('product-racks/{id}/edit', 'ProductRackController@edit');
    Route::put('product-racks/{id}', 'ProductRackController@update');
    Route::delete('product-racks/{id}', 'ProductRackController@destroy');
    Route::get('product-racks/bulk-upload', 'ProductRackController@bulkUploadForm')->name('product-racks.bulk-upload.form');
    Route::post('product-racks/bulk-upload', 'ProductRackController@bulkUpload')->name('product-racks.bulk-upload');
    Route::get('/product-racks/storage-locations/{location_id}', 'ProductRackController@getStorageLocations')->name('product-racks.storage-locations');
    
    Route::get('/storage-locations/by-location', 'ProductRackController@getStorageLocations')->name('storage-locations.by-location');

    Route::resource('products', 'ProductController');

    Route::get('/contact-persons-by-location/{location_id}', [App\Http\Controllers\PurchaseController::class, 'getContactPersonsByLocation']);

    // Request assignment routes
    Route::get('/request/get-company-users', 'RequestController@getCompanyUsers')->name('request.get-company-users');
    Route::post('/request/assign-user', 'RequestController@assignUser')->name('request.assign-user');

    // New routes for filtering and summary
    Route::get('/request/pending-qty-by-users', 'RequestController@getPendingQtyByUsers')->name('request.pending-qty-by-users');
    Route::get('/request/{id}/filtered-items', 'RequestController@getFilteredItems')->name('request.get-filtered-items');


    // Purchase Management
    Route::prefix('/purchases')->group(function () {
        Route::post('import-purchase-products', 'PurchaseController@importPurchaseProducts');
        Route::post('update-status', 'PurchaseController@updateStatus');
        Route::get('get_products', 'PurchaseController@getProducts');
        Route::get('get_suppliers', 'PurchaseController@getSuppliers');
        Route::get('get_customers', 'PurchaseController@getCustomers');
        Route::post('get_purchase_entry_row', 'PurchaseController@getPurchaseEntryRow');
        Route::post('get_request_entry_row', 'PurchaseController@getRequestEntryRow');
        Route::post('check_ref_number', 'PurchaseController@checkRefNumber');
        Route::get('add_requests', 'PurchaseController@addRequest');
        Route::post('request/storeRequest', 'PurchaseController@storeRequest')->name('request.store');
    });
    Route::resource('purchases', 'PurchaseController')->except(['show']);

    // Request Management
    Route::prefix('requests')->group(function () {
        Route::get('', 'PurchaseController@listRequests')->name('requests');
        Route::get('{id}', 'PurchaseController@viewRequests')->name('viewRequests');
        Route::get('{id}/edit', 'RequestController@edit')->name('editRequest');
        Route::get('{id}/draft/quote/edit', 'RequestController@draftQuoteEdit')->name('draftQuoteEdit');
        Route::post('{id}/update', 'RequestController@update')->name('updateRequest');
        Route::get('items', 'PurchaseController@requestitems')->name('request.items');
        Route::get('ready-to-draft', 'RequestController@readyToDraftList')->name('request.readyToDraftList');
        Route::get('item/{id}/edit', 'RequestController@editItem')->name('request.item.edit');
        Route::get('item/{id}/reject', 'RequestController@rejectItem')->name('request.item.reject');
        Route::post('item/{id}/update', 'RequestController@itemUpdate')->name('request.item.update');
        Route::get('{id}/edit-readyToDraft', 'RequestController@draftEdit')->name('request.item.draft.edit');
        Route::post('{id}/update-readyToDraft', 'RequestController@draftUpdate')->name('request.item.draft.update');
        Route::get('draft', 'RequestController@draftList')->name('request.item.draft.list');
        Route::post('{id}/draft-quote/update', 'RequestController@updateDraftquote')->name('request.item.updateDraftquote');
        Route::get('list/quote', 'RequestController@listQuote')->name('request.quote');
        Route::get('{id}/quote/accept', 'RequestController@acceptQuote')->name('request.quote.accept');
        Route::post('{id}/quote/accept/save', 'RequestController@accepteQuoteForm')->name('request.item.accepteQuoteForm');
        Route::get('quote/accepted', 'RequestController@acceptedQuote')->name('request.list.quote.accept');
        Route::get('{id}/quote/reject', 'RequestController@rejectQuote')->name('request.quote.reject');
        Route::get('{id}/quote/print', 'RequestController@printQuote')->name('request.quote.print');
        Route::get('quote/rejected', 'RequestController@listRejected')->name('request.quote.listRejected');
        Route::get('quote/disputed', 'RequestController@listDisputed')->name('request.quote.listDisputed');
        Route::get('{id}/quote/dispute', 'RequestController@disputedQuote')->name('request.disputedQuote');
    });

    // Sales Management
    Route::prefix('/sells')->group(function () {
        Route::get('toggle-subscription/{id}', 'SellPosController@toggleRecurringInvoices');
        Route::post('pos/get-types-of-service-details', 'SellPosController@getTypesOfServiceDetails');
        Route::get('subscriptions', 'SellPosController@listSubscriptions');
        Route::get('duplicate/{id}', 'SellController@duplicateSell');
        Route::get('drafts', 'SellController@getDrafts');
        Route::get('convert-to-draft/{id}', 'SellPosController@convertToInvoice');
        Route::get('convert-to-proforma/{id}', 'SellPosController@convertToProforma');
        Route::get('quotations', 'SellController@getQuotations');
        Route::get('draft-dt', 'SellController@getDraftDatables');
        Route::get('edit-shipping/{id}', 'SellController@editShipping');
        Route::put('update-shipping/{id}', 'SellController@updateShipping');
        Route::get('shipments', 'SellController@shipments');
    });
    Route::resource('sells', 'SellController')->except(['show']);

    // Import Sales
    Route::prefix('/import-sales')->group(function () {
        Route::get('', 'ImportSalesController@index');
        Route::post('preview', 'ImportSalesController@preview');
        Route::post('', 'ImportSalesController@import');
        Route::get('revert-sale-import/{batch}', 'ImportSalesController@revertSaleImport');
    });

    // Point of Sale Routes
    Route::prefix('/sells/pos')->group(function () {
        Route::get('get_product_row/{variation_id}/{location_id}', 'SellPosController@getProductRow');
        Route::post('get_payment_row', 'SellPosController@getPaymentRow');
        Route::post('get-reward-details', 'SellPosController@getRewardDetails');
        Route::get('get-recent-transactions', 'SellPosController@getRecentTransactions');
        Route::get('get-product-suggestion', 'SellPosController@getProductSuggestion');
        Route::get('get-featured-products/{location_id}', 'SellPosController@getFeaturedProducts');
    });
    Route::get('reset-mapping', 'SellController@resetMapping');
    Route::resource('pos', 'SellPosController');
    Route::put('pos/{id}/update', 'SellPosController@update')->name('custom.pos.update');

    // System Management
    Route::resource('roles', 'RoleController');
    Route::resource('users', 'ManageUserController');
    Route::resource('currencies', 'CurrencyController');
    Route::resource('group-taxes', 'GroupTaxController');
    Route::resource('barcodes', 'BarcodeController');
    Route::get('barcodes/set_default/{id}', 'BarcodeController@setDefault');

    // Invoice Management
    Route::resource('invoice-schemes', 'InvoiceSchemeController');
    Route::get('invoice-schemes/set_default/{id}', 'InvoiceSchemeController@setDefault');

    // Label Printing
    Route::prefix('/labels')->group(function () {
        Route::get('show', 'LabelsController@show');
        Route::get('add-product-row', 'LabelsController@addProductRow');
        Route::get('preview', 'LabelsController@preview');
    });

    // Reports
    Route::prefix('/reports')->group(function () {
        Route::get('gst-purchase-report', 'ReportController@gstPurchaseReport');
        Route::get('gst-sales-report', 'ReportController@gstSalesReport');
        Route::get('get-stock-by-sell-price', 'ReportController@getStockBySellingPrice');
        Route::get('purchase-report', 'ReportController@purchaseReport');
        Route::get('sale-report', 'ReportController@saleReport');
        Route::get('service-staff-report', 'ReportController@getServiceStaffReport');
        Route::get('service-staff-line-orders', 'ReportController@serviceStaffLineOrders');
        Route::get('table-report', 'ReportController@getTableReport');
        Route::get('profit-loss', 'ReportController@getProfitLoss');
        Route::get('get-opening-stock', 'ReportController@getOpeningStock');
        Route::get('purchase-sell', 'ReportController@getPurchaseSell');
        Route::get('customer-supplier', 'ReportController@getCustomerSuppliers');
        Route::get('stock-report', 'ReportController@getStockReport');
        Route::get('stock-details', 'ReportController@getStockDetails');
        Route::get('tax-report', 'ReportController@getTaxReport');
        Route::get('tax-details', 'ReportController@getTaxDetails');
        Route::get('trending-products', 'ReportController@getTrendingProducts');
        Route::get('expense-report', 'ReportController@getExpenseReport');
        Route::get('stock-adjustment-report', 'ReportController@getStockAdjustmentReport');
        Route::get('register-report', 'ReportController@getRegisterReport');
        Route::get('sales-representative-report', 'ReportController@getSalesRepresentativeReport');
        Route::get('sales-representative-total-expense', 'ReportController@getSalesRepresentativeTotalExpense');
        Route::get('sales-representative-total-sell', 'ReportController@getSalesRepresentativeTotalSell');
        Route::get('sales-representative-total-commission', 'ReportController@getSalesRepresentativeTotalCommission');
        Route::get('stock-expiry', 'ReportController@getStockExpiryReport');
        Route::get('stock-expiry-edit-modal/{purchase_line_id}', 'ReportController@getStockExpiryReportEditModal');
        Route::post('stock-expiry-update', 'ReportController@updateStockExpiryReport')->name('updateStockExpiryReport');
        Route::get('customer-group', 'ReportController@getCustomerGroup');
        Route::get('product-purchase-report', 'ReportController@getproductPurchaseReport');
        Route::get('product-sell-grouped-by', 'ReportController@productSellReportBy');
        Route::get('product-sell-report', 'ReportController@getproductSellReport');
        Route::get('product-sell-report-with-purchase', 'ReportController@getproductSellReportWithPurchase');
        Route::get('product-sell-grouped-report', 'ReportController@getproductSellGroupedReport');
        Route::get('lot-report', 'ReportController@getLotReport');
        Route::get('purchase-payment-report', 'ReportController@purchasePaymentReport');
        Route::get('sell-payment-report', 'ReportController@sellPaymentReport');
        Route::get('product-stock-details', 'ReportController@productStockDetails');
        Route::get('adjust-product-stock', 'ReportController@adjustProductStock');
        Route::get('get-profit/{by?}', 'ReportController@getProfit');
        Route::get('items-report', 'ReportController@itemsReport');
        Route::get('get-stock-value', 'ReportController@getStockValue');
        Route::get('activity-log', 'ReportController@activityLog');
    });

    // Business Locations
    Route::get('business-location/activate-deactivate/{location_id}', 'BusinessLocationController@activateDeactivateLocation');
    Route::post('business-location/check-location-id', 'BusinessLocationController@checkLocationId');
    Route::resource('business-location', 'BusinessLocationController');
    
    // Location Settings
    Route::prefix('business-location/{location_id}')->name('location.')->group(function () {
        Route::get('settings', 'LocationSettingsController@index')->name('settings');
        Route::post('settings', 'LocationSettingsController@updateSettings')->name('settings_update');
    });

    // Invoice Layouts
    Route::resource('invoice-layouts', 'InvoiceLayoutController');

    // Expense Management
    Route::post('get-expense-sub-categories', 'ExpenseCategoryController@getSubCategories');
    Route::resource('expense-categories', 'ExpenseCategoryController');
    Route::resource('expenses', 'ExpenseController');

    // Payments
    Route::get('/payments/show-child-payments/{payment_id}', 'TransactionPaymentController@showChildPayments');
    Route::get('/payments/view-payment/{payment_id}', 'TransactionPaymentController@viewPayment');
    Route::get('/payments/add_payment/{transaction_id}', 'TransactionPaymentController@addPayment');
    Route::get('/payments/pay-contact-due/{contact_id}', 'TransactionPaymentController@getPayContactDue');
    Route::post('/payments/pay-contact-due', 'TransactionPaymentController@postPayContactDue');
    Route::resource('payments', 'TransactionPaymentController');

    // Printers
    Route::resource('printers', 'PrinterController');

    // Stock Adjustments
    Route::get('/stock-adjustments/remove-expired-stock/{purchase_line_id}', 'StockAdjustmentController@removeExpiredStock');
    Route::post('/stock-adjustments/get_product_row', 'StockAdjustmentController@getProductRow');
    Route::resource('stock-adjustments', 'StockAdjustmentController');

    // Cash Register
    Route::get('/cash-register/register-details', 'CashRegisterController@getRegisterDetails');
    Route::get('/cash-register/close-register/{id?}', 'CashRegisterController@getCloseRegister');
    Route::post('/cash-register/close-register', 'CashRegisterController@postCloseRegister');
    Route::resource('cash-register', 'CashRegisterController');

    // Import Products
    Route::get('/import-products', 'ImportProductsController@index');
    Route::post('/import-products/store', 'ImportProductsController@store');

    // Sales Agents
    Route::resource('sales-commission-agents', 'SalesCommissionAgentController');

    // Stock Transfers
    Route::get('stock-transfers/print/{id}', 'StockTransferController@printInvoice');
    Route::post('stock-transfers/update-status/{id}', 'StockTransferController@updateStatus');
    Route::resource('stock-transfers', 'StockTransferController');
    
    // Opening Stock
    Route::get('/opening-stock/add/{product_id}', 'OpeningStockController@add');
    Route::post('/opening-stock/save', 'OpeningStockController@save');

    // Customer Groups
    Route::resource('customer-group', 'CustomerGroupController');

    // Import Opening Stock
    Route::get('/import-opening-stock', 'ImportOpeningStockController@index');
    Route::post('/import-opening-stock/store', 'ImportOpeningStockController@store');

    // Sell Returns
    Route::get('validate-invoice-to-return/{invoice_no}', 'SellReturnController@validateInvoiceToReturn');
    Route::resource('sell-return', 'SellReturnController');
    Route::get('sell-return/get-product-row', 'SellReturnController@getProductRow');
    Route::get('/sell-return/print/{id}', 'SellReturnController@printInvoice');
    Route::get('/sell-return/add/{id}', 'SellReturnController@add');
    
    // Backup
    Route::get('backup/download/{file_name}', 'BackUpController@download');
    Route::get('backup/delete/{file_name}', 'BackUpController@delete');
    Route::resource('backup', 'BackUpController')->only(['index', 'create', 'store']);

    // Selling Price Groups
    Route::get('selling-price-group/activate-deactivate/{id}', 'SellingPriceGroupController@activateDeactivate');
    Route::get('export-selling-price-group', 'SellingPriceGroupController@export');
    Route::post('import-selling-price-group', 'SellingPriceGroupController@import');
    Route::resource('selling-price-group', 'SellingPriceGroupController');

    // Notifications
    Route::resource('notification-templates', 'NotificationTemplateController')->only(['index', 'store']);
    Route::get('notification/get-template/{transaction_id}/{template_for}', 'NotificationController@getTemplate');
    Route::post('notification/send', 'NotificationController@send');

    // Purchase Returns
    Route::post('/purchase-return/update', 'CombinedPurchaseReturnController@update');
    Route::get('/purchase-return/edit/{id}', 'CombinedPurchaseReturnController@edit');
    Route::post('/purchase-return/save', 'CombinedPurchaseReturnController@save');
    Route::post('/purchase-return/get_product_row', 'CombinedPurchaseReturnController@getProductRow');
    Route::get('/purchase-return/create', 'CombinedPurchaseReturnController@create');
    Route::get('/purchase-return/add/{id}', 'PurchaseReturnController@add');
    Route::resource('/purchase-return', 'PurchaseReturnController')->except(['create']);

    // Discounts
    Route::get('/discount/activate/{id}', 'DiscountController@activate');
    Route::post('/discount/mass-deactivate', 'DiscountController@massDeactivate');
    Route::resource('discount', 'DiscountController');

    // Account Management
    Route::prefix('account')->group(function () {
        Route::resource('', 'AccountController')->parameters(['' => 'account']);
        Route::get('fund-transfer/{id}', 'AccountController@getFundTransfer');
        Route::post('fund-transfer', 'AccountController@postFundTransfer');
        Route::get('deposit/{id}', 'AccountController@getDeposit');
        Route::post('deposit', 'AccountController@postDeposit');
        Route::get('close/{id}', 'AccountController@close');
        Route::get('activate/{id}', 'AccountController@activate');
        Route::get('delete-account-transaction/{id}', 'AccountController@destroyAccountTransaction');
        Route::get('edit-account-transaction/{id}', 'AccountController@editAccountTransaction');
        Route::post('update-account-transaction/{id}', 'AccountController@updateAccountTransaction');
        Route::get('get-account-balance/{id}', 'AccountController@getAccountBalance');
        Route::get('balance-sheet', 'AccountReportsController@balanceSheet');
        Route::get('trial-balance', 'AccountReportsController@trialBalance');
        Route::get('payment-account-report', 'AccountReportsController@paymentAccountReport');
        Route::get('link-account/{id}', 'AccountReportsController@getLinkAccount');
        Route::post('link-account', 'AccountReportsController@postLinkAccount');
        Route::get('cash-flow', 'AccountController@cashFlow');
    });
    
    // Account Types
    Route::resource('account-types', 'AccountTypeController');

    // Restaurant Module
    Route::prefix('modules')->group(function () {
        Route::resource('tables', 'Restaurant\TableController');
        Route::resource('modifiers', 'Restaurant\ModifierSetsController');

        // Product Modifiers
        Route::get('product-modifiers/{id}/edit', 'Restaurant\ProductModifierSetController@edit');
        Route::post('product-modifiers/{id}/update', 'Restaurant\ProductModifierSetController@update');
        Route::get('product-modifiers/product-row/{product_id}', 'Restaurant\ProductModifierSetController@product_row');
        Route::get('add-selected-modifiers', 'Restaurant\ProductModifierSetController@add_selected_modifiers');

        // Kitchen Management
        Route::get('kitchen', 'Restaurant\KitchenController@index');
        Route::get('kitchen/mark-as-cooked/{id}', 'Restaurant\KitchenController@markAsCooked');
        Route::post('refresh-orders-list', 'Restaurant\KitchenController@refreshOrdersList');
        Route::post('refresh-line-orders-list', 'Restaurant\KitchenController@refreshLineOrdersList');

        // Order Management
        Route::get('orders', 'Restaurant\OrderController@index');
        Route::get('orders/mark-as-served/{id}', 'Restaurant\OrderController@markAsServed');
        Route::get('data/get-pos-details', 'Restaurant\DataController@getPosDetails');
        Route::get('orders/mark-line-order-as-served/{id}', 'Restaurant\OrderController@markLineOrderAsServed');
        Route::get('print-line-order', 'Restaurant\OrderController@printLineOrder');
    });

    // Bookings
    Route::get('bookings/get-todays-bookings', 'Restaurant\BookingController@getTodaysBookings');
    Route::resource('bookings', 'Restaurant\BookingController');
    
    // Service Types
    Route::resource('types-of-service', 'TypesOfServiceController');

    // Module Management
    Route::post('upload-module', 'Install\ModulesController@uploadModule');
    Route::resource('manage-modules', 'Install\ModulesController')->only(['index', 'destroy', 'update']);

    // Warranties
    Route::resource('warranties', 'WarrantyController');

    // Dashboard Configuration
    Route::resource('dashboard-configurator', 'DashboardConfiguratorController')->only(['edit', 'update']);

    // Media View
    Route::get('view-media/{model_id}', 'SellController@viewMedia');

    // Documents & Notes
    Route::get('get-document-note-page', 'DocumentAndNoteController@getDocAndNoteIndexPage');
    Route::post('post-document-upload', 'DocumentAndNoteController@postMedia');
    Route::resource('note-documents', 'DocumentAndNoteController');

    // Orders
    Route::resource('purchase-order', 'PurchaseOrderController');
    Route::get('get-purchase-orders/{contact_id}', 'PurchaseOrderController@getPurchaseOrders');
    Route::get('get-purchase-order-lines/{purchase_order_id}', 'PurchaseController@getPurchaseOrderLines');
    Route::get('edit-purchase-orders/{id}/status', 'PurchaseOrderController@getEditPurchaseOrderStatus');
    Route::put('update-purchase-orders/{id}/status', 'PurchaseOrderController@postEditPurchaseOrderStatus');
    Route::resource('sales-order', 'SalesOrderController')->only(['index']);
    Route::get('get-sales-orders/{customer_id}', 'SalesOrderController@getSalesOrders');
    Route::get('get-sales-order-lines', 'SellPosController@getSalesOrderLines');
    Route::get('edit-sales-orders/{id}/status', 'SalesOrderController@getEditSalesOrderStatus');
    Route::put('update-sales-orders/{id}/status', 'SalesOrderController@postEditSalesOrderStatus');

    // User Location
    Route::get('user-location/{latlng}', 'HomeController@getUserLocation');
});

// Ecommerce API Routes
Route::middleware(['EcomApi'])->prefix('api/ecom')->group(function () {
    Route::get('products/{id?}', 'ProductController@getProductsApi');
    Route::get('categories', 'CategoryController@getCategoriesApi');
    Route::get('brands', 'BrandController@getBrandsApi');
    Route::post('customers', 'ContactController@postCustomersApi');
    Route::get('settings', 'BusinessController@getEcomSettings');
    Route::get('variations', 'ProductController@getVariationsApi');
    Route::post('orders', 'SellPosController@placeOrdersApi');
});

// Common Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', 'Auth\LoginController@logout')->name('custom.logout');
    Route::get('get-currency/{id}', 'ExpenseController@getCurrency')->name("getCurrency");
});

// Authenticated Routes with Additional Middleware
Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone'])->group(function () {
    Route::get('/load-more-notifications', 'HomeController@loadMoreNotifications');
    Route::get('/get-total-unread', 'HomeController@getTotalUnreadNotifications');
    
    // Printable Documents
    Route::get('/purchases/print/{id}', 'PurchaseController@printInvoice');
    Route::get('/purchases/{id}', 'PurchaseController@show');
    Route::get('/download-purchase-order/{id}/pdf', 'PurchaseOrderController@downloadPdf')->name('purchaseOrder.downloadPdf');
    Route::get('/sells/{id}', 'SellController@show');
    Route::get('/sells/{transaction_id}/print', 'SellPosController@printInvoice')->name('sell.printInvoice');
    Route::get('/download-sells/{transaction_id}/pdf', 'SellPosController@downloadPdf')->name('sell.downloadPdf');
    Route::get('/download-quotation/{id}/pdf', 'SellPosController@downloadQuotationPdf')->name('quotation.downloadPdf');
    Route::get('/download-packing-list/{id}/pdf', 'SellPosController@downloadPackingListPdf')->name('packing.downloadPdf');
    Route::get('/sells/invoice-url/{id}', 'SellPosController@showInvoiceUrl');
    Route::get('/show-notification/{id}', 'HomeController@showNotification');
});

// Temporary Column Addition
Route::get('/add-columns', function () {
    DB::statement("ALTER TABLE customer_requests ADD request_note  LONGTEXT NULL");
    return 'Columns added successfully';
});