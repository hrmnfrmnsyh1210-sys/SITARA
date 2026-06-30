<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('plan_name')->default('Bulanan');
            $table->unsignedInteger('months')->default(1)->comment('Lama langganan dalam bulan');
            $table->decimal('price', 12, 2)->default(0);

            // pending  -> diajukan admin sekolah, menunggu konfirmasi super admin
            // active   -> disetujui & berlaku
            // rejected -> ditolak super admin
            // expired  -> masa berlaku habis
            $table->string('status')->default('pending');

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            $table->string('payment_method')->nullable()->comment('Transfer / Tunai / dll');
            $table->string('payment_proof')->nullable()->comment('Path bukti transfer');
            $table->text('note')->nullable();

            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
