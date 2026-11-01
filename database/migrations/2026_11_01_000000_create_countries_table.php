<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection(): ?string
    {
        return config('countries.connection');
    }

    public function up(): void
    {
        Schema::create(config('countries.table', 'countries'), function (Blueprint $table) {
            $table->id();
            $table->char('alpha2', 2)->unique();     // GE
            $table->char('alpha3', 3)->unique();     // GEO
            $table->char('numeric', 3)->unique();    // 268
            $table->char('currency', 3)->nullable(); // GEL — ISO 4217
            $table->json('names');                   // {"en": "Georgia", "ka": "საქართველო", "ru": "Грузия"}
            $table->string('official_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('countries.table', 'countries'));
    }
};
