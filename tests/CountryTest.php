<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Mchekhashvili\Countries\Database\Seeders\CountrySeeder;
use Mchekhashvili\Countries\Models\Country;
use Mchekhashvili\Money\Currency;

beforeEach(fn () => $this->seed(CountrySeeder::class));

it('seeds every ISO 3166-1 country', function () {
    expect(Country::query()->count())->toBe(249)
        ->and(Country::query()->distinct()->count('alpha3'))->toBe(249);
});

it('finds a country by any of its codes', function (string $code) {
    expect(Country::code($code)->alpha2)->toBe('GE');
})->with(['GE', 'ge', 'GEO', '268']);

it('throws for an unknown code', function () {
    Country::code('ZZ');
})->throws(ModelNotFoundException::class);

it('names a country in the app locale, with a fallback', function () {
    $georgia = Country::code('GE');

    app()->setLocale('ka');
    expect($georgia->name)->toBe('საქართველო');

    app()->setLocale('en');
    expect($georgia->name)->toBe('Georgia')
        ->and($georgia->nameIn('ru'))->toBe('Грузия')
        ->and($georgia->nameIn('ka_GE'))->toBe('საქართველო')
        ->and($georgia->nameIn('fr'))->toBe('Georgia')
        ->and(Country::code('US')->official_name)->toBe('United States of America');
});

it('gives the currency as a money Currency', function () {
    expect(Country::code('GE')->currency())->toEqual(Currency::GEL())
        ->and(Country::code('DE')->currency()->code)->toBe('EUR')
        ->and(Country::code('AQ')->currency())->toBeNull()
        ->and(Country::query()->usingCurrency('EUR')->pluck('alpha2'))->toContain('DE', 'FR', 'AX');
});

it('knows every currency it names', function () {
    $unknown = Country::query()->whereNotNull('currency')->pluck('currency')->unique()
        ->reject(fn (string $code) => Currency::isKnown($code));

    expect($unknown->all())->toBe([]);
});

it('draws the flag', function () {
    expect(Country::code('GE')->flag)->toBe('🇬🇪');
});

it('binds routes by alpha-2', function () {
    expect(Country::code('GE')->getRouteKey())->toBe('GE');
});

it('re-seeds without duplicating or deleting', function () {
    $id = Country::code('GE')->id;
    $this->seed(CountrySeeder::class);

    expect(Country::query()->count())->toBe(249)->and(Country::code('GE')->id)->toBe($id);
});

it('syncs from artisan', function () {
    Country::query()->delete();

    $this->artisan('countries:sync')->expectsOutputToContain('249 countries')->assertSuccessful();
});

it('uses the configured table', function () {
    expect(Schema::hasTable('countries'))->toBeTrue()->and(new Country()->getTable())->toBe('countries');
});

it('is what other models point at', function () {
    $law = new class extends Model {
        protected $guarded = [];

        public function country(): BelongsTo
        {
            return $this->belongsTo(Country::class);
        }
    };

    expect($law->forceFill(['country_id' => Country::code('GE')->id])->country->alpha3)->toBe('GEO');
});
