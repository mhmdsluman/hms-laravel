<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnesthesiaRecordController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\BillableEventController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CarePlanController;
use App\Http\Controllers\ClinicalNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiagnosisCodeController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\FormularyController;
use App\Http\Controllers\InsuranceContractController;
use App\Http\Controllers\InsurancePolicyController;
use App\Http\Controllers\InsuranceProviderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabResultController;
use App\Http\Controllers\MedicationAdministrationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NursingNoteController;
use App\Http\Controllers\NursingStationController;
use App\Http\Controllers\OperatingTheaterController;
use App\Http\Controllers\OperativeNoteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderSetController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PharmacyDispensationController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RadiologyController;
use App\Http\Controllers\RadiologyReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\RadiologyScheduleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShiftHandoverController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TestCatalogueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VitalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/
// TEMP debug route - remove after debugging
Route::get('/debug/patients/search', [App\Http\Controllers\PatientController::class, 'search']);

// Language Switcher Route
Route::get('language/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return redirect()->back();
})->name('language.switch');

// Public/Guest Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Main Authenticated Application Routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Patient Registration & Management
    Route::get('/patients/search', [PatientController::class, 'search'])->name('patients.search');
    Route::post('/patients/check-duplicate', [PatientController::class, 'checkDuplicate'])->name('patients.checkDuplicate');
    Route::resource('patients', PatientController::class);

    // Appointments & Vitals
    Route::resource('appointments', AppointmentController::class)->except(['show']);
    Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::resource('appointments.vitals', VitalController::class)->shallow()->only(['create', 'store']);

    // Consultation (Clinical Notes, Orders)
    Route::get('/consultation/{appointment}', [ClinicalNoteController::class, 'create'])->name('consultation.create');
    Route::post('/consultation/{appointment}', [ClinicalNoteController::class, 'store'])->name('consultation.store');
    Route::post('/consultation/{appointment}/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/diagnosis-codes', [DiagnosisCodeController::class, 'index'])->name('diagnosis-codes.index');

    // Billing
    Route::resource('billing', BillController::class)->except(['store', 'create', 'edit', 'update']);
    Route::post('/appointments/{appointment}/generate-bill', [BillController::class, 'store'])->name('billing.store');
    Route::post('/billing/{bill}/payment', [BillController::class, 'recordPayment'])->name('billing.recordPayment');
    Route::post('/billing/{bill}/discount', [BillController::class, 'applyDiscount'])->name('billing.applyDiscount');

    // Insurance Policies (Standalone management for a specific patient)
    Route::resource('insurance-policies', InsurancePolicyController::class)->except(['index', 'show']);

    // Emergency Department
    Route::get('/emergency', [EmergencyController::class, 'index'])->name('emergency.index');
    Route::get('/emergency/register', [EmergencyController::class, 'create'])->name('emergency.create');
    Route::post('/emergency/register', [EmergencyController::class, 'store'])->name('emergency.store');

    // In-Patient Department (IPD) & Nursing
    Route::prefix('ipd')->group(function () {
        Route::get('/', [BedController::class, 'index'])->name('ipd.index');
        Route::resource('admissions', AdmissionController::class)->only(['create', 'store']);
        Route::get('/nursing', [NursingStationController::class, 'index'])->name('nursing.index');

        // MAR (Medication Administration Record)
        Route::get('/admission/{admission}/mar', [MedicationAdministrationController::class, 'show'])->name('mar.show');
        Route::put('/medication-administration/{medicationAdministration}', [MedicationAdministrationController::class, 'update'])->name('mar.update');

        // Nursing Notes
        Route::get('/admission/{admission}/nursing-notes/create', [NursingNoteController::class, 'create'])->name('nursing-notes.create');
        Route::post('/admission/{admission}/nursing-notes', [NursingNoteController::class, 'store'])->name('nursing-notes.store');

        // Care Plans
        Route::get('/admission/{admission}/care-plan', [CarePlanController::class, 'show'])->name('care-plans.show');
        Route::post('/admission/{admission}/care-plan', [CarePlanController::class, 'store'])->name('care-plans.store');

        // Shift Handovers
        Route::get('/admission/{admission}/shift-handover/create', [ShiftHandoverController::class, 'create'])->name('shift-handovers.create');
        Route::post('/admission/{admission}/shift-handover', [ShiftHandoverController::class, 'store'])->name('shift-handovers.store');
    });

    // Laboratory
    Route::prefix('lab')->name('lab.')->group(function () {
        Route::get('/', [LabController::class, 'index'])->name('index');
        Route::put('/orders/{orderItem}', [LabController::class, 'updateStatus'])->name('updateStatus');
        Route::get('/orders/{orderItem}/results', [LabResultController::class, 'create'])->name('results.create');
        Route::post('/orders/{orderItem}/results', [LabResultController::class, 'store'])->name('results.store');
        Route::post('/results/{labResult}/verify', [LabResultController::class, 'verify'])->name('results.verify');
    });

    // Pharmacy
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::get('/', [PharmacyController::class, 'index'])->name('index');
        Route::get('/orders/{orderItem}/dispense', [PharmacyDispensationController::class, 'create'])->name('dispense.create');
        Route::post('/orders/{orderItem}/dispense', [PharmacyDispensationController::class, 'store'])->name('dispense.store');
    });

    // Radiology
    Route::prefix('radiology')->name('radiology.')->group(function () {
        Route::get('/', [RadiologyController::class, 'index'])->name('index');
        Route::get('/orders/{orderItem}/schedule', [RadiologyScheduleController::class, 'create'])->name('schedule.create');
        Route::post('/orders/{orderItem}/schedule', [RadiologyScheduleController::class, 'store'])->name('schedule.store');
        Route::get('/orders/{orderItem}/report', [RadiologyReportController::class, 'create'])->name('report.create');
        Route::post('/orders/{orderItem}/report', [RadiologyReportController::class, 'store'])->name('report.store');
    });

    // Operating Theater (OT)
    Route::prefix('ot')->name('ot.')->group(function () {
        Route::get('/', [OperatingTheaterController::class, 'index'])->name('index');
        Route::get('/orders/{orderItem}/schedule', [OperatingTheaterController::class, 'create'])->name('schedule.create');
        Route::post('/orders/{orderItem}/schedule', [OperatingTheaterController::class, 'store'])->name('schedule.store');
        Route::get('/orders/{orderItem}/notes', [OperativeNoteController::class, 'create'])->name('operative-notes.create');
        Route::post('/orders/{orderItem}/notes', [OperativeNoteController::class, 'store'])->name('operative-notes.store');
        Route::get('/notes/{operativeNote}/anesthesia', [AnesthesiaRecordController::class, 'create'])->name('anesthesia-record.create');
        Route::post('/notes/{operativeNote}/anesthesia', [AnesthesiaRecordController::class, 'store'])->name('anesthesia-record.store');
    });

    // Printing
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/lab/{labResult}', [PrintController::class, 'labResult'])->name('labResult');
        Route::get('/bill/{bill}', [PrintController::class, 'billInvoice'])->name('billInvoice');
    });

    // Administration Panel
    Route::prefix('admin')->middleware('auth')->group(function () {
        Route::get('settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('admin.settings');
        Route::post('settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('admin.settings.update');
        Route::resource('users', UserController::class);
        Route::resource('services', ServiceController::class);
        Route::resource('inventory', InventoryController::class);
        Route::resource('schedule', ScheduleController::class);
        Route::resource('billable-events', BillableEventController::class)->only(['index', 'store']);
        Route::post('billable-events/generate-bill', [BillableEventController::class, 'generateBill'])->name('admin.billable-events.generate-bill');
        Route::resource('templates', TemplateController::class);
        Route::get('/audit-trail', [AuditLogController::class, 'index'])->name('audit.index');
        Route::resource('test-catalogue', TestCatalogueController::class);

        Route::get('/formulary', [FormularyController::class, 'index'])->name('formulary.index');
        Route::get('/formulary/{service}/edit', [FormularyController::class, 'edit'])->name('formulary.edit');
        Route::put('/formulary/{service}', [FormularyController::class, 'update'])->name('formulary.update');

        Route::resource('order-sets', OrderSetController::class);
        Route::resource('insurance-providers', InsuranceProviderController::class);
        Route::get('/insurance-providers/{provider}/contracts', [InsuranceContractController::class, 'edit'])->name('insurance-contracts.edit');
        Route::put('/insurance-providers/{provider}/contracts', [InsuranceContractController::class, 'update'])->name('insurance-contracts.update');

        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});

// Patient Portal Routes
Route::prefix('portal')->middleware('auth')->name('portal.')->group(function () {
    Route::get('/dashboard', [PatientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [PatientPortalController::class, 'appointments'])->name('appointments');
    Route::get('/bills', [PatientPortalController::class, 'bills'])->name('bills');

    // Requesting appointments
    Route::get('/request-appointment', [PatientPortalController::class, 'createAppointment'])->name('appointments.create');
    Route::post('/request-appointment', [AppointmentController::class, 'storeRequest'])->name('appointments.store');

    // Messaging
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');
});

// Auth routes
require __DIR__.'/auth.php';
