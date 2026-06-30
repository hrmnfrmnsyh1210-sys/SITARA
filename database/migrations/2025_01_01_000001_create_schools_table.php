<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('npsn')->nullable()->comment('Nomor Pokok Sekolah Nasional');
            $table->string('level')->nullable()->comment('SD/SMP/SMA/SMK');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('primary_color')->default('#2563EB');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Now that schools exists, attach the FK from users.school_id.
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });
        Schema::dropIfExists('schools');
    }
};
