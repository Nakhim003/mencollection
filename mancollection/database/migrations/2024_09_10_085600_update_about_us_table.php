<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_us', function (Blueprint $table) {
            // Add or modify columns here
            $table->string('title')->nullable()->change(); // Example modification
        });
    }

    public function down(): void
    {
        Schema::table('about_us', function (Blueprint $table) {
            // Revert changes here if necessary
            $table->string('title')->change(); // Example modification
        });
    }
};
