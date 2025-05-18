<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained();

            $table->unsignedBigInteger('admin_id')->nullable(); // nullable agar bisa kosong jika dibuat member
            $table->unsignedBigInteger('member_id')->nullable(); // nullable agar bisa kosong jika dibuat admin

            $table->enum('created_by', ['admin', 'member'])->default('member'); // Penanda asal transaksi

            $table->timestamp('finish_date')->nullable();
            $table->integer('discount')->default(0);
            $table->integer('total')->default(0);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
