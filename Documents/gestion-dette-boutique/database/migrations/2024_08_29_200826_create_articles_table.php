<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('articles')) {
            Schema::create('articles', function (Blueprint $table) {
                $table->id();
                $table->string('libelle');
                $table->text('description');
                $table->decimal('price', 8, 2);
                $table->integer('stock');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }


    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
