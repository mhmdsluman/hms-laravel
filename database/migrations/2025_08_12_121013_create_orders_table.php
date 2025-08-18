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
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patients');
                $table->foreignId('appointment_id')->constrained('appointments');
                $table->foreignId('ordered_by_user_id')->constrained('users');
                $table->string('status')->default('Pending'); // e.g., Pending, Completed, Billed
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('orders');
        }
    };
