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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('social_reason');
            $table->string('ruc');
            $table->string('address');
            $table->string('logo_path')->nullable();

            //credentials
            $table->string('sol_user');
            $table->string('sol_pass');
            $table->string('certificate_path');

            //api credentials
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();

            $table->boolean('production')->default(false);

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
