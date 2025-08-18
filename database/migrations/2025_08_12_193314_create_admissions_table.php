    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('admissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patients');
                $table->foreignId('appointment_id')->nullable()->constrained('appointments')->comment('The initial encounter leading to admission');
                $table->foreignId('bed_id')->constrained('beds');
                $table->foreignId('admitting_doctor_id')->constrained('users');
                $table->dateTime('admission_time');
                $table->dateTime('discharge_time')->nullable();
                $table->string('status')->default('Admitted'); // Admitted, Discharged
                $table->text('reason_for_admission');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('admissions');
        }
    };
