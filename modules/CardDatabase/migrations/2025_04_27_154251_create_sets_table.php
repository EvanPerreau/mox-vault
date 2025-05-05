<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the card table.
 */
return new class extends Migration
{
    protected $connection = 'card';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('object')->default('set');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('uri');
            $table->string('scryfall_uri');
            $table->string('search_uri');
            $table->date('released_at')->nullable();
            $table->string('set_type');
            $table->integer('card_count')->default(0);
            $table->string('parent_set_code')->nullable();
            $table->boolean('digital')->default(false);
            $table->boolean('nonfoil_only')->default(false);
            $table->boolean('foil_only')->default(false);
            $table->string('icon_svg_uri')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('sets');
    }
};
