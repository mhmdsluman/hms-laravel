<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnesthesiaRecordController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BedController;
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
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\LabInventoryController;
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
use App\Http\Controllers\RadiologyScheduleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShiftHandoverController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TestCatalogueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VitalController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Patients
    Route::resource('patients', PatientController::class);
    Route::post('/patients/check-duplicate', [PatientController::class, 'checkDuplicate'])->name('patients.checkDuplicate');

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');

    // Vitals & Clinical Notes
    Route::get('/appointments/{appointment}/vitals/create', [VitalController::class, 'create'])->name('vitals.create');
    Route::post('/appointments/{appointment}/vitals', [VitalController::class, 'store'])->name('vitals.store');
    Route::get('/appointments/{appointment}/consultation', [ClinicalNoteController::class, 'create'])->name('clinical-notes.create');
    Route::post('/appointments/{appointment}/consultation', [ClinicalNoteController::class, 'store'])->name('clinical-notes.store');

    // Orders
    Route::post('/appointments/{appointment}/orders', [OrderController::class, 'store'])->name('orders.store');

    // Services
    Route::resource('services', ServiceController::class);

    // Laboratory
    Route::get('/laboratory', [LabController::class, 'index'])->name('lab.index');
    Route::patch('/laboratory/orders/{orderItem}/status', [LabController::class, 'updateStatus'])->name('lab.updateStatus');
    Route::get('/lab/orders/{orderItem}/result/create', [LabResultController::class, 'create'])->name('lab-results.create');
    Route::post('/lab/orders/{orderItem}/result', [LabResultController::class, 'store'])->name('lab-results.store');
    Route::patch('/lab/results/{labResult}/verify', [LabResultController::class, 'verify'])->name('lab-results.verify');

      // Lab Inventory Route (for Lab/Admins)
    Route::resource('lab-inventory', LabInventoryController::class)->except(['show']);

    // Pharmacy
    Route::get('/pharmacy', [PharmacyController::class, 'index'])->name('pharmacy.index');
    Route::get('/pharmacy/orders/{orderItem}/dispense', [PharmacyDispensationController::class, 'create'])->name('dispensations.create');
    Route::post('/pharmacy/orders/{orderItem}/dispense', [PharmacyDispensationController::class, 'store'])->name('dispensations.store');
    Route::resource('inventory', InventoryItemController::class);

    // Radiology
    Route::get('/radiology', [RadiologyController::class, 'index'])->name('radiology.index');
    Route::get('/radiology/orders/{orderItem}/report/create', [RadiologyReportController::class, 'create'])->name('radiology-reports.create');
    Route::post('/radiology/orders/{orderItem}/report', [RadiologyReportController::class, 'store'])->name('radiology-reports.store');
    Route::get('/radiology/orders/{orderItem}/schedule', [RadiologyScheduleController::class, 'create'])->name('radiology-schedule.create');
    Route::post('/radiology/orders/{orderItem}/schedule', [RadiologyScheduleController::class, 'store'])->name('radiology-schedule.store');

    // Billing Routes (merged)
    Route::get('/billing', [BillController::class, 'index'])->name('billing.index');
    Route::get('/billing/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::get('/billing/{bill}', [BillController::class, 'show'])
    ->name('billing.show')
    ->middleware('auth');
    Route::post('/billing/{bill}/discount', [BillController::class, 'applyDiscount'])
    ->name('billing.discount')
    ->middleware('auth');
    Route::post('/appointments/{appointment}/bills', [BillController::class, 'store'])->name('bills.store');
    Route::patch('/billing/{bill}/payment', [BillController::class, 'recordPayment'])->name('bills.recordPayment');
    Route::patch('/billing/{bill}/discount', [BillController::class, 'applyDiscount'])->name('bills.applyDiscount');
    Route::delete('/billing/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');

    // routes/web.php
Route::post('/billing/{bill}/pay', [\App\Http\Controllers\BillController::class, 'recordPayment'])->name('billing.pay');
Route::delete('/billing/{bill}', [\App\Http\Controllers\BillController::class, 'destroy'])->name('billing.destroy');

    // IPD & Admissions
    Route::get('/ipd', [BedController::class, 'index'])->name('ipd.index');
    Route::get('/admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
    Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');
    Route::patch('/admissions/{admission}/discharge', [AdmissionController::class, 'discharge'])->name('admissions.discharge');

    Route::get('/admissions/{admission}/mar', [MedicationAdministrationController::class, 'show'])->name('mar.show');
    Route::patch('/mar/{medicationAdministration}', [MedicationAdministrationController::class, 'update'])->name('mar.update');

    // Nursing & Care Plans
    Route::get('/nursing-station', [NursingStationController::class, 'index'])->name('nursing.index');
    Route::get('/admissions/{admission}/nursing-notes/create', [NursingNoteController::class, 'create'])->name('nursing-notes.create');
    Route::post('/admissions/{admission}/nursing-notes', [NursingNoteController::class, 'store'])->name('nursing-notes.store');
    Route::get('/admissions/{admission}/care-plan', [CarePlanController::class, 'show'])->name('care-plans.show');
    Route::post('/admissions/{admission}/care-plan', [CarePlanController::class, 'store'])->name('care-plans.store');
    Route::get('/admissions/{admission}/shift-handover/create', [ShiftHandoverController::class, 'create'])->name('shift-handovers.create');
    Route::post('/admissions/{admission}/shift-handover', [ShiftHandoverController::class, 'store'])->name('shift-handovers.store');

    // Users & Admin
    Route::resource('users', UserController::class);
    Route::get('/admin/audit-trail', [AuditLogController::class, 'index'])->name('audit.index');
    Route::resource('templates', TemplateController::class);
    Route::get('/admin/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Test Catalogue & Order Sets
    Route::resource('test-catalogue', TestCatalogueController::class)->parameters(['test-catalogue' => 'service']);
    Route::resource('order-sets', OrderSetController::class);
    Route::get('/diagnosis-codes', [DiagnosisCodeController::class, 'index'])->name('diagnosis-codes.index');

    // Formulary & Insurance
    Route::resource('formulary', FormularyController::class)->only(['index', 'edit', 'update'])->parameters(['formulary' => 'service']);
    Route::resource('insurance-providers', InsuranceProviderController::class);
    Route::resource('insurance-policies', InsurancePolicyController::class)->except(['index', 'show']);
    Route::get('/insurance-contracts/{provider}/edit', [InsuranceContractController::class, 'edit'])->name('insurance-contracts.edit');
    Route::put('/insurance-contracts/{provider}', [InsuranceContractController::class, 'update'])->name('insurance-contracts.update');

    // Emergency
    Route::get('/emergency', [EmergencyController::class, 'index'])->name('emergency.index');
    Route::get('/emergency/register', [EmergencyController::class, 'create'])->name('emergency.create');
    Route::post('/emergency/register', [EmergencyController::class, 'store'])->name('emergency.store');

    // Operating Theater & OT Notes
    Route::get('/ot', [OperatingTheaterController::class, 'index'])->name('ot.index');
    Route::get('/ot/schedule/{orderItem}', [OperatingTheaterController::class, 'create'])->name('ot.create');
    Route::post('/ot/schedule/{orderItem}', [OperatingTheaterController::class, 'store'])->name('ot.store');
    Route::get('/ot/notes/{orderItem}/create', [OperativeNoteController::class, 'create'])->name('operative-notes.create');
    Route::post('/ot/notes/{orderItem}', [OperativeNoteController::class, 'store'])->name('operative-notes.store');
    Route::get('/ot/operative-notes/{operativeNote}/anesthesia/create', [AnesthesiaRecordController::class, 'create'])->name('anesthesia-records.create');
    Route::post('/ot/operative-notes/{operativeNote}/anesthesia', [AnesthesiaRecordController::class, 'store'])->name('anesthesia-records.store');

    // Print Routes
    Route::get('/print/lab-result/{labResult}', [PrintController::class, 'labResult'])->name('print.lab-result');
    Route::get('/print/bill/{bill}', [PrintController::class, 'billInvoice'])->name('print.bill-invoice'); // <-- Add this

    // Patient Portal
    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/dashboard', [PatientPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/appointments', [PatientPortalController::class, 'appointments'])->name('appointments');
        Route::get('/bills', [PatientPortalController::class, 'bills'])->name('bills');
        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');
        Route::get('/appointments/request', [PatientPortalController::class, 'createAppointment'])->name('appointments.request.create');
        Route::post('/appointments/request', [AppointmentController::class, 'storeRequest'])->name('appointments.request.store');
    });
});

require __DIR__.'/auth.php';
