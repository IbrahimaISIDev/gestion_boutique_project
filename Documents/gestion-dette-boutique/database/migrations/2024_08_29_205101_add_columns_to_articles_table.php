<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToArticlesTable extends Migration
{
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'libelle')) {
                $table->string('libelle');
            }
            if (!Schema::hasColumn('articles', 'description')) {
                $table->text('description');
            }
            if (!Schema::hasColumn('articles', 'price')) {
                $table->decimal('price', 8, 2);
            }
            if (!Schema::hasColumn('articles', 'stock')) {
                $table->integer('stock');
            }
            if (!Schema::hasColumn('articles', 'created_at') && !Schema::hasColumn('articles', 'updated_at')) {
                $table->timestamps();
            }
            if (!Schema::hasColumn('articles', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'libelle')) {
                $table->dropColumn('libelle');
            }
            if (Schema::hasColumn('articles', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('articles', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('articles', 'stock')) {
                $table->dropColumn('stock');
            }
            if (Schema::hasColumn('articles', 'created_at') && Schema::hasColumn('articles', 'updated_at')) {
                $table->dropTimestamps();
            }
            if (Schema::hasColumn('articles', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
}

