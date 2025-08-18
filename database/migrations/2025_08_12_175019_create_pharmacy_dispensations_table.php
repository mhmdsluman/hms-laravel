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
            Schema::create('pharmacy_dispensations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_item_id')->constrained('order_items');
                $table->foreignId('inventory_item_id')->constrained('inventory_items');
                $table->integer('quantity_dispensed');
                $table->foreignId('dispensed_by_user_id')->constrained('users');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('pharmacy_dispensations');
        }
    };
