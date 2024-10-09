<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('vaccine_center_id')->constrained('vaccine_centers')->onDelete('cascade');
            $table->string('full_name');
            $table->string('nid', 20)->unique();
            $table->string('email')->unique();
            $table->string('phone_number', 20);
            $table->enum('status', ['Not registered', 'Not scheduled', 'Scheduled', 'Vaccinated'])->default('Not registered');
            $table->date('scheduled_vaccination_date')->nullable();
            $table->date('vaccinated_at')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
