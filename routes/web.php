<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CampaignController;



// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

require __DIR__.'/auth.php';

Route::get('/', function () {
    return auth()->check() ? redirect()->route('admin.dashboard') : redirect()->route('login');
});

// Laravel's built-in RedirectIfAuthenticated middleware (used by every
// "guest" route - login, register, forgot-password...) falls back to
// redirecting an already-authenticated visitor to whichever of "dashboard"
// or "home" exists as a route name; this app only has "admin.dashboard".
// Without this, an authenticated user hitting any guest route bounced
// between "/" -> "/login" -> "/" forever (ERR_TOO_MANY_REDIRECTS).
Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public trial-campaign signup pages (no auth) - see App\Http\Controllers\CampaignController.
// A visitor fills the form and gets a License auto-issued on the spot.
Route::get('campanha/{campaign:slug}', [CampaignController::class, 'show'])->name('campaign.show');
Route::post('campanha/{campaign:slug}', [CampaignController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('campaign.store');
Route::get('campanha/{campaign:slug}/licenca/{license:license_key}', [CampaignController::class, 'success'])->name('campaign.success');

Route::group(['middleware' => ['auth'], 'as' => 'admin.', 'prefix' => 'admin'], function () {
    // Dashboard
    Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::put('user/status/{id}', [App\Http\Controllers\Admin\UserController::class, 'status'])->name('user.status');
    Route::resource('user', App\Http\Controllers\Admin\UserController::class);
    Route::get('user/remove-image/{id}', [App\Http\Controllers\Admin\UserController::class, 'removeImage'])->name('user.removeImage');

    // Roles
    Route::resource('role', App\Http\Controllers\Admin\RoleController::class);

    // Customers
    Route::put('customer/status/{id}', [App\Http\Controllers\Admin\CustomerController::class, 'status'])->name('customer.status');
    Route::get('customer/cnpj-lookup/{cnpj}', [App\Http\Controllers\Admin\CustomerController::class, 'cnpjLookup'])->name('customer.cnpj-lookup');
    Route::resource('customer', App\Http\Controllers\Admin\CustomerController::class);

    // Contact leads (submitted via the public site's contact form, see
    // routes/api.php - created here only for viewAny/view/delete since the
    // API endpoint is the sole writer).
    Route::put('contact_lead/{contact_lead}/status', [App\Http\Controllers\Admin\ContactLeadController::class, 'updateStatus'])->name('contact_lead.status');
    Route::resource('contact_lead', App\Http\Controllers\Admin\ContactLeadController::class)->only(['index', 'show', 'destroy']);

    // Services
    Route::put('service/status/{id}', [App\Http\Controllers\Admin\ServiceController::class, 'status'])->name('service.status');
    Route::resource('service', App\Http\Controllers\Admin\ServiceController::class);

    // Licensing
    Route::delete('license/{license}/activations/{activation}', [App\Http\Controllers\Admin\LicenseController::class, 'revokeActivation'])->name('license.activations.revoke');
    Route::resource('license', App\Http\Controllers\Admin\LicenseController::class);

    // Trial campaigns (public signup pages that auto-issue a License -
    // see routes/web.php's "campanha/{campaign:slug}" group and
    // App\Http\Controllers\CampaignController).
    Route::put('campaign/status/{id}', [App\Http\Controllers\Admin\CampaignController::class, 'status'])->name('campaign.status');
    Route::resource('campaign', App\Http\Controllers\Admin\CampaignController::class);

    // GLPI instances (cadastro de servidores GLPI acessados via API REST v2 -
    // ver App\Services\Glpi\GlpiApiClient) + import de ativos por CSV pra
    // uma dessas instâncias (ver App\Services\Glpi\GlpiAssetImportService).
    Route::put('glpi_instance/status/{id}', [App\Http\Controllers\Admin\GlpiInstanceController::class, 'status'])->name('glpi_instance.status');
    Route::post('glpi_instance/{glpi_instance}/test-connection', [App\Http\Controllers\Admin\GlpiInstanceController::class, 'testConnection'])->name('glpi_instance.test-connection');
    Route::resource('glpi_instance', App\Http\Controllers\Admin\GlpiInstanceController::class);

    Route::get('glpi-asset-import-history', [App\Http\Controllers\Admin\GlpiAssetImportController::class, 'index'])->name('glpi_asset_import.index');
    Route::get('glpi-asset-import', [App\Http\Controllers\Admin\GlpiAssetImportController::class, 'create'])->name('glpi_asset_import.create');
    Route::post('glpi-asset-import/analyze', [App\Http\Controllers\Admin\GlpiAssetImportController::class, 'analyze'])->name('glpi_asset_import.analyze');
    Route::post('glpi-asset-import', [App\Http\Controllers\Admin\GlpiAssetImportController::class, 'store'])->name('glpi_asset_import.store');
    Route::get('glpi-asset-import/{glpi_asset_import}', [App\Http\Controllers\Admin\GlpiAssetImportController::class, 'show'])->name('glpi_asset_import.show');
    Route::delete('glpi-asset-import/{glpi_asset_import}', [App\Http\Controllers\Admin\GlpiAssetImportController::class, 'destroy'])->name('glpi_asset_import.destroy');

    // Billing policies (drive automatic invoice generation - see
    // App\Services\InvoiceGenerator and the invoices:generate command).
    Route::post('billing_policy/{billing_policy}/reconcile', [App\Http\Controllers\Admin\BillingPolicyController::class, 'reconcile'])->name('billing_policy.reconcile');
    Route::resource('billing_policy', App\Http\Controllers\Admin\BillingPolicyController::class);

    // Invoices - generated from billing policies (or manually via a "once"
    // policy); only editable post-generation. The one exception is
    // importing an already-issued NFS-e (see nfse-import below), which can
    // create a Fatura from scratch to backfill notes issued before this
    // system existed.
    Route::put('invoice/{invoice}/mark-paid', [App\Http\Controllers\Admin\InvoiceController::class, 'markPaid'])->name('invoice.mark-paid');
    Route::post('invoice/{invoice}/regenerate-link', [App\Http\Controllers\Admin\InvoiceController::class, 'regenerateLink'])->name('invoice.regenerate-link');
    Route::post('invoice/{invoice}/issue-nfse', [App\Http\Controllers\Admin\InvoiceController::class, 'issueNfse'])->name('invoice.issue-nfse');
    Route::get('invoice/{invoice}/nfse-xml', [App\Http\Controllers\Admin\InvoiceController::class, 'downloadNfseXml'])->name('invoice.nfse-xml');
    Route::get('invoice/{invoice}/nfse-pdf', [App\Http\Controllers\Admin\InvoiceController::class, 'downloadNfsePdf'])->name('invoice.nfse-pdf');
    Route::post('invoice/{invoice}/nfse-pdf/refresh', [App\Http\Controllers\Admin\InvoiceController::class, 'refreshNfsePdf'])->name('invoice.nfse-pdf.refresh');
    Route::post('invoice/{invoice}/nfse-pdf/upload', [App\Http\Controllers\Admin\InvoiceController::class, 'uploadNfsePdf'])->name('invoice.nfse-pdf.upload');
    Route::resource('invoice', App\Http\Controllers\Admin\InvoiceController::class)->except(['create', 'store']);

    // Import an already-issued NFS-e XML (historical notes, or ones issued
    // by hand outside this system) - matches/creates the Fatura + Cliente.
    Route::get('nfse-import', [App\Http\Controllers\Admin\NFSeImportController::class, 'create'])->name('nfse_import.create');
    Route::post('nfse-import/analyze', [App\Http\Controllers\Admin\NFSeImportController::class, 'analyze'])->name('nfse_import.analyze');
    Route::post('nfse-import', [App\Http\Controllers\Admin\NFSeImportController::class, 'store'])->name('nfse_import.store');

    // Commercial proposals - document editor styled after the CCITI Word
    // template, rendered to PDF via dompdf (see admin/proposal/document.blade.php).
    // Every save (create or edit) generates a new version, whose PDF is
    // stored in the azure blob (see ProposalRepository::createVersion()).
    Route::get('proposal/{proposal}/preview', [App\Http\Controllers\Admin\ProposalController::class, 'preview'])->name('proposal.preview');
    Route::get('proposal/{proposal}/versions/{version}/download', [App\Http\Controllers\Admin\ProposalController::class, 'downloadVersion'])->name('proposal.versions.download');
    Route::delete('proposal/{proposal}/versions/{version}', [App\Http\Controllers\Admin\ProposalController::class, 'destroyVersion'])->name('proposal.versions.destroy');
    Route::post('proposal/{proposal}/generate-invoices', [App\Http\Controllers\Admin\ProposalController::class, 'generateInvoices'])->name('proposal.generate-invoices');
    Route::resource('proposal', App\Http\Controllers\Admin\ProposalController::class);

    // User_profile
    Route::get('edit-profile', [App\Http\Controllers\Admin\UserController::class, 'editProfile'])->name('user.edit-profile');
    Route::get('get-states', [App\Http\Controllers\Admin\UserController::class, 'getStates'])->name('user.get-states');
    Route::post('update-profile', [App\Http\Controllers\Admin\UserController::class, 'updateProfile'])->name('user.update-profile');

    // Settings (SSO + Azure Blob Storage). Named/prefixed "setting" (singular) -
    // this whole route group is itself nested under the "admin." name/prefix,
    // which also covers the pre-existing demo route `Route::view('settings', ...)`
    // further down this file - using the plural here would collide with it
    // (both would resolve to GET admin/settings, silently shadowing one another).
    Route::get('setting', [App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('setting.edit');
    Route::put('setting/sso', [App\Http\Controllers\Admin\SettingController::class, 'updateSso'])->name('setting.sso.update');
    Route::put('setting/storage', [App\Http\Controllers\Admin\SettingController::class, 'updateStorage'])->name('setting.storage.update');
    Route::post('setting/storage/test', [App\Http\Controllers\Admin\SettingController::class, 'testStorage'])->name('setting.storage.test');
    Route::put('setting/mercadopago', [App\Http\Controllers\Admin\SettingController::class, 'updateMercadoPago'])->name('setting.mercadopago.update');
    Route::put('setting/mail', [App\Http\Controllers\Admin\SettingController::class, 'updateMail'])->name('setting.mail.update');
    Route::post('setting/nfse', [App\Http\Controllers\Admin\SettingController::class, 'updateNfse'])->name('setting.nfse.update');
    Route::post('setting/nfse/test', [App\Http\Controllers\Admin\SettingController::class, 'testNfseCertificate'])->name('setting.nfse.test');
    Route::post('setting/mail/test', [App\Http\Controllers\Admin\SettingController::class, 'testMail'])->name('setting.mail.test');


// Dashboard
Route::get('default-dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('default_dashboard');
Route::view('ecommerce-dashboard', 'dashboards.ecommerce_dashboard')->name('ecommerce_dashboard');
Route::view('online-course-dashboard', 'dashboards.online_course')->name('online_course_dashboard');
Route::view('crypto-dashboard', 'dashboards.crypto_dashboard')->name('crypto_dashboard');
Route::view('social-dashboard', 'dashboards.social_dashboard')->name('social_dashboard');
Route::view('nft-dashboard', 'dashboards.nft_dashboard')->name('nft_dashboard');
Route::view('school-management-dashboard', 'dashboards.school_management')->name('school_management_dashboard');
Route::view('pos-dashboard', 'dashboards.pos_dashboard')->name('pos_dashboard');
Route::view('crm-dashboard', 'dashboards.crm_dashboard')->name('crm_dashboard');
Route::view('analytics-dashboard', 'dashboards.analytics_dashboard')->name('analytics_dashboard');
Route::view('hr-dashboard', 'dashboards.hr_dashboard')->name('hr_dashboard');
Route::view('projects-dashboard', 'dashboards.projects_dashboard')->name('projects_dashboard');
Route::view('logistics-dashboard', 'dashboards.logistics_dashboard')->name('logistics_dashboard');

//widgets
Route::view('general-widget', 'widgets.general_widget')->name('general_widget');
Route::view('chart-widget', 'widgets.chart_widget')->name('chart_widget');

//page_layout
Route::view('box-layout', 'page_layouts.box_layout')->name('box_layout');
Route::view('rtl-layout', 'page_layouts.rtl_layout')->name('rtl_layout');
Route::view('dark-layout', 'page_layouts.dark_layout')->name('dark_layout');
Route::view('hide-on-scroll', 'page_layouts.hide_on_scroll')->name('hide_on_scroll');
Route::view('footer-light', 'page_layouts.footer_light')->name('footer_light');
Route::view('footer-dark', 'page_layouts.footer_dark')->name('footer_dark');
Route::view('footer-fixed', 'page_layouts.footer_fixed')->name('footer_fixed');

//projects
Route::view('project-details', 'projects.details_project')->name('details_project');
Route::view('project-list', 'projects.list_project')->name('list_project');
Route::view('project-create', 'projects.create_project')->name('create_project');

 //file manager
 Route::view('file-manager', 'file_manager')->name('file_manager');

 //kanban board
 Route::view('kanban', 'kanban')->name('kanban');

 //ecommerce
 Route::view('add-products', 'ecommerce.products.add_products')->name('add_products');
 Route::view('products-grid', 'ecommerce.products.products_grid')->name('products_grid');
 Route::view('list-products', 'ecommerce.products.list_products')->name('list_products');
 Route::view('products-details', 'ecommerce.products.products_details')->name('products_details');
 Route::view('categories-pages', 'ecommerce.category_page')->name('category_page');
 Route::view('seller-list', 'ecommerce.seller.seller_list')->name('seller_list');
 Route::view('seller-details', 'ecommerce.seller.seller_details')->name('seller_details');
 Route::view('order-history', 'ecommerce.order.order_history')->name('order_history');
 Route::view('order-details', 'ecommerce.order.order_details')->name('order_details');
 Route::view('invoice-1', 'ecommerce.invoices.invoice_1')->name('invoice_1');
 Route::view('invoice-2', 'ecommerce.invoices.invoice_2')->name('invoice_2');
 Route::view('invoice-3', 'ecommerce.invoices.invoice_3')->name('invoice_3');
 Route::view('invoice-4', 'ecommerce.invoices.invoice_4')->name('invoice_4');
 Route::view('invoice-5', 'ecommerce.invoices.invoice_5')->name('invoice_5');
 Route::view('invoice-6', 'ecommerce.invoices.invoice_6')->name('invoice_6');
 Route::view('cart', 'ecommerce.cart')->name('cart');
 Route::view('wish-list', 'ecommerce.wish_list')->name('wish_list');
 Route::view('checkout', 'ecommerce.checkout')->name('checkout');
 Route::view('manage-review', 'ecommerce.manage_review')->name('manage_review');
 Route::view('settings', 'ecommerce.settings')->name('settings');

//mail box
Route::view('mail-box', 'mail_box')->name('mail_box');

//chat
Route::view('chat-private', 'chats.chat_private')->name('chat_private');
Route::view('chat-group', 'chats.chat_group')->name('chat_group');

//users
Route::view('profile', 'users.user_profile')->name('user_profile');
Route::view('add_user', 'users.add_user')->name('add_user');
Route::view('list-users', 'users.user_list')->name('user_list');
Route::view('cards', 'users.user_cards')->name('user_cards');
Route::view('permissions-roles', 'users.role_permission')->name('role_permission');

// reports
Route::view('products-reports', 'reports.products')->name('products_reports');
Route::view('sales-reports', 'reports.sales')->name('sales_reports');
Route::view('sales-return', 'reports.sales_return')->name('sales_return');
Route::view('customer-order', 'reports.customer_order')->name('customer_order');

 //bookmark
Route::view('bookmark', 'bookmark')->name('bookmark');

//contacts
Route::view('contacts', 'contacts')->name('contacts');

//tasks
Route::view('task', 'task')->name('task');

//calendar
Route::view('calendar-basic', 'calendar_basic')->name('calendar_basic');

//social_app
Route::view('social-app', 'social_app')->name('social_app');

//to_do
Route::view('to-do', 'to_do')->name('to_do');

//search_result
Route::view('search', 'search')->name('search');

//forms -> form-control
Route::view('form-validation', 'forms.form_controls.form_validation')->name('form_validation');
Route::view('base-input', 'forms.form_controls.base_input')->name('base_input');
Route::view('radio-checkbox-control', 'forms.form_controls.radio_checkbox_control')->name('radio_checkbox_control');
Route::view('input-group', 'forms.form_controls.input_group')->name('input_group');
Route::view('input-mask', 'forms.form_controls.input_mask')->name('input_mask');
Route::view('megaoptions', 'forms.form_controls.megaoptions')->name('megaoptions');

//forms -> form-widgets
Route::view('datepicker', 'forms.form_widgets.datepicker')->name('datepicker');
Route::view('touchspin', 'forms.form_widgets.touchspin')->name('touchspin');
Route::view('select2', 'forms.form_widgets.select2')->name('select2');
Route::view('switch', 'forms.form_widgets.switch')->name('switch');
Route::view('typeahead', 'forms.form_widgets.typeahead')->name('typeahead');
Route::view('clipboard', 'forms.form_widgets.clipboard')->name('clipboard');

//Forms -> form_layout
Route::view('form-wizard', 'forms.form_layout.form_wizard')->name('form_wizard');
Route::view('two-form-wizard', 'forms.form_layout.form_wizard_two')->name('form_wizard_two');
Route::view('two-factor', 'forms.form_layout.two_factor')->name('two_factor');

//Tables -> bootstrap_tables
Route::view('bootstrap-basic-table', 'tables.bootstrap_tables.bootstrap_basic_table')->name('bootstrap_basic_table');
Route::view('table-components', 'tables.bootstrap_tables.table_components')->name('table_components');

//Tables -> data_tables
Route::view('datatable-basic-init', 'tables.data_tables.datatable_basic_init')->name('datatable_basic_init');
Route::view('datatable-advance-init', 'tables.data_tables.datatable_advance_init')->name('datatable_advance_init');
Route::view('datatable-api', 'tables.data_tables.datatable_api')->name('datatable_api');
Route::view('datatable-data-source', 'tables.data_tables.datatable_data_source')->name('datatable_data_source');
Route::view('datatable-ext-autofill', 'tables.data_tables.datatable_ext_autofill')->name('datatable_ext_autofill');

//Tables ->jsgrid-table
Route::view('jsgrid-table', 'tables.jsgrid_table')->name('jsgrid_table');

//ui_kits
Route::view('typography', 'ui_kits.typography')->name('typography');
Route::view('avatars', 'ui_kits.avatars')->name('avatars');
Route::view('divider', 'ui_kits.divider')->name('divider');
Route::view('helper-classes', 'ui_kits.helper_classes')->name('helper_classes');
Route::view('grid', 'ui_kits.grid')->name('grid');
Route::view('pills-tag', 'ui_kits.tag_pills')->name('tag_pills');
Route::view('progress-bar', 'ui_kits.progress_bar')->name('progress_bar');
Route::view('modal', 'ui_kits.modal')->name('modal');
Route::view('alert', 'ui_kits.alert')->name('alert');
Route::view('popover', 'ui_kits.popover')->name('popover');
Route::view('placeholders', 'ui_kits.placeholders')->name('placeholders');
Route::view('tooltip', 'ui_kits.tooltip')->name('tooltip');
Route::view('dropdown', 'ui_kits.dropdown')->name('dropdown');
Route::view('according', 'ui_kits.according')->name('according');
Route::view('tabs', 'ui_kits.tabs')->name('tabs');
Route::view('offcanvas', 'ui_kits.offcanvas')->name('offcanvas');
Route::view('navigate-links', 'ui_kits.navigate_links')->name('navigate_links');
Route::view('list', 'ui_kits.list')->name('list');

//bonus_ui
Route::view('scrollable', 'bonus_ui.scrollable')->name('scrollable');
Route::view('tree', 'bonus_ui.tree')->name('tree');
Route::view('toasts', 'bonus_ui.toasts')->name('toasts');
Route::view('block-ui', 'bonus_ui.block_ui')->name('block_ui');
Route::view('rating', 'bonus_ui.rating')->name('rating');
Route::view('dropzone', 'bonus_ui.dropzone')->name('dropzone');
Route::view('tour', 'bonus_ui.tour')->name('tour');
Route::view('sweet-alert2', 'bonus_ui.sweet_alert2')->name('sweet_alert2');
Route::view('animation-modal', 'bonus_ui.modal_animated')->name('modal_animated');
Route::view('owl-carousel', 'bonus_ui.owl_carousel')->name('owl_carousel');
Route::view('ribbons', 'bonus_ui.ribbons')->name('ribbons');
Route::view('pagination', 'bonus_ui.pagination')->name('pagination');
Route::view('scroll-spy', 'bonus_ui.scroll_spy')->name('scroll_spy');
Route::view('breadcrumb', 'bonus_ui.breadcrumb')->name('breadcrumb');
Route::view('range-slider', 'bonus_ui.range_slider')->name('range_slider');
Route::view('ratios', 'bonus_ui.ratios')->name('ratios');
Route::view('image-cropper', 'bonus_ui.image_cropper')->name('image_cropper');
Route::view('basic-card', 'bonus_ui.basic_card')->name('basic_card');
Route::view('creative-card', 'bonus_ui.creative_card')->name('creative_card');
Route::view('draggable-card', 'bonus_ui.draggable_card')->name('draggable_card');
Route::view('timeline', 'bonus_ui.timeline')->name('timeline');

//animation
Route::view('animate', 'animations.animate')->name('animate');
Route::view('scroll-reveal', 'animations.scroll_reveal')->name('scroll_reveal');
Route::view('aos', 'animations.aos')->name('aos');
Route::view('tilt', 'animations.tilt')->name('tilt');
Route::view('wow', 'animations.wow')->name('wow');
Route::view('flash-icon', 'animations.flash_icon')->name('flash_icon');

//icons
Route::view('flag-icon', 'icons.flag_icon')->name('flag_icon');
Route::view('font-awesome', 'icons.font_awesome')->name('font_awesome');
Route::view('ico-icon', 'icons.ico_icon')->name('ico_icon');
Route::view('themify-icon', 'icons.themify_icon')->name('themify_icon');
Route::view('feather-icon', 'icons.feather_icon')->name('feather_icon');
Route::view('whether-icon', 'icons.whether_icon')->name('whether_icon');

//buttons
Route::view('buttons', 'buttons')->name('buttons');

//charts
Route::view('chart-apex', 'charts.chart_apex')->name('chart_apex');
Route::view('chart-google', 'charts.chart_google')->name('chart_google');
Route::view('chart-sparkline', 'charts.chart_sparkline')->name('chart_sparkline');
Route::view('chart-flot', 'charts.chart_flot')->name('chart_flot');
Route::view('chart-knob', 'charts.chart_knob')->name('chart_knob');
Route::view('chart-morris', 'charts.chart_morris')->name('chart_morris');
Route::view('chartjs', 'charts.chartjs')->name('chartjs');
Route::view('chartist', 'charts.chartist')->name('chartist');
Route::view('chart-peity', 'charts.chart_peity')->name('chart_peity');

// landing page

//sample-page
Route::view('sample-page', 'sample_page')->name('sample_page');

// internationalization
Route::view('internationalization', 'internationalization')->name('internationalization');

// Starter kit

//error_page
Route::view('error-403', 'error_pages.error_403')->name('error_403');
Route::view('error-404', 'error_pages.error_404')->name('error_404');
Route::view('error-500', 'error_pages.error_500')->name('error_500');

//authentication
// Route::view('login', 'authentication.login')->name('login');
Route::view('login-one', 'authentication.login_one')->name('login_one');
Route::view('login-two', 'authentication.login_two')->name('login_two');
Route::view('login-three', 'authentication.login_three')->name('login_three');
Route::view('login-with-tooltip', 'authentication.login_with_tooltip')->name('login_with_tooltip');
Route::view('login-with-sweetalert', 'authentication.login_with_sweetalert')->name('login_with_sweetalert');
Route::view('sign-up', 'authentication.sign_up')->name('sign_up');
Route::view('sign-up-with-bg-image', 'authentication.sign_up_with_bg_image')->name('sign_up_with_bg_image');
Route::view('sign-up-with-image-two', 'authentication.sign_up_with_image_two')->name('sign_up_with_image_two');
Route::view('sign-up-wizard', 'authentication.sign_up_wizard')->name('sign_up_wizard');
Route::view('account-restricted', 'authentication.account_restricted')->name('account_restricted');
Route::view('unlock', 'authentication.unlock')->name('unlock');
Route::view('forget-password', 'authentication.forget_password')->name('forget_password');
Route::view('reset-password', 'authentication.reset_password')->name('reset_password');
Route::view('maintenance', 'authentication.maintenance')->name('maintenance');

//coming_soon
Route::view('comingsoon', 'coming_soon.comingsoon')->name('comingsoon');
Route::view('comingsoon-bg-video', 'coming_soon.comingsoon_bg_video')->name('comingsoon_bg_video');
Route::view('comingsoon-bg-img', 'coming_soon.comingsoon_bg_img')->name('comingsoon_bg_img');

//email_templates
Route::view('basic-template', 'email_templates.basic_template')->name('basic_template');
Route::view('email-header', 'email_templates.email_header')->name('email_header');
Route::view('template-email', 'email_templates.template_email')->name('template_email');
Route::view('template-email-2', 'email_templates.template_email_2')->name('template_email_2');
Route::view('ecommerce-templates', 'email_templates.ecommerce_templates')->name('ecommerce_templates');
Route::view('email-order-success', 'email_templates.email_order_success')->name('email_order_success');

// manage API
Route::view('manage-api', 'manage_api')->name('manage_api');

// site map
Route::view('site-map', 'site_map')->name('site_map');

// pricing
Route::view('pricing', 'pricing')->name('pricing');

// FAQ
Route::view('faq', 'faq')->name('faq');

// subscribed user
Route::view('subscribed-user', 'subscribed_user')->name('subscribed_user');

//gallery
Route::view('gallery', 'gallery.gallery')->name('gallery');
Route::view('with-description', 'gallery.gallery_with_description')->name('gallery_with_description');
Route::view('masonry', 'gallery.gallery_masonry')->name('gallery_masonry');
Route::view('gallery-with-disc', 'gallery.masonry_gallery_with_disc')->name('masonry_gallery_with_disc');
Route::view('hover', 'gallery.gallery_hover')->name('gallery_hover');
Route::view('gallery-placeholder', 'gallery.gallery_placeholder')->name('gallery_placeholder');

//blog
Route::view('all-blogs', 'blog.blog')->name('blogs');
Route::view('details-blog', 'blog.blog_details')->name('blog_details');
Route::view('add-blog', 'blog.add_blog')->name('add_blog');

//job
Route::view('job-cards-view', 'jobs.job_cards_view')->name('job_cards_view');
Route::view('job-list-view', 'jobs.job_list_view')->name('job_list_view');
Route::view('job-details', 'jobs.job_details')->name('job_details');
Route::view('job-candidates', 'jobs.job_candidates')->name('job_candidates');
Route::view('job-companies', 'jobs.job_companies')->name('job_companies');
Route::view('job-apply', 'jobs.job_apply')->name('job_apply');

//courses
Route::view('course-list', 'courses.course_list')->name('course_list');
Route::view('course-details', 'courses.course_details')->name('course_details');

//maps
Route::view('map-js', 'maps.map_js')->name('map_js');
Route::view('vector-map', 'maps.vector_map')->name('vector_map');

//editors
Route::view('quill-editor', 'editors.quill_editor')->name('quill_editor');
Route::view('ckeditor', 'editors.ckeditor')->name('ckeditor');
Route::view('ace-code-editor', 'editors.ace_code_editor')->name('ace_code_editor');

//knowledgebase
Route::view('knowledgebase', 'knowledgebase')->name('knowledgebase');

//support_ticket
Route::view('support-ticket', 'support_ticket')->name('support_ticket');
});
