<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateJobsTable
 *
 * This migration creates the jobs table for handling queued jobs.
 */
class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id'); // Unique identifier for each job
            $table->string('queue'); // The name of the queue
            $table->longText('payload'); // The serialized job data
            $table->tinyInteger('attempts')->unsigned(); // Number of attempts to process the job
            $table->unsignedInteger('reserved_at')->nullable(); // Timestamp when the job was reserved
            $table->unsignedInteger('available_at'); // Timestamp when the job is available to be processed
            $table->unsignedInteger('created_at'); // Timestamp when the job was created
            $table->index(['queue', 'reserved_at']); // Index for quicker lookups
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs'); // Drop the jobs table if it exists
    }
}
