# countries

ISO 3166-1 countries for Laravel 12 and 13: a `countries` table with all 249 officially assigned codes, an Eloquent `Country` model, names in Georgian, English and Russian, and each country's currency as a [`mchekhashvili/money`](https://github.com/MCHEKHASHVILI/money) `Currency`.

Requires PHP 8.4.

## Install

Until it is on Packagist, add the repositories to your app's `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/MCHEKHASHVILI/countries" },
    { "type": "vcs", "url": "https://github.com/MCHEKHASHVILI/money" }
]
```

```
composer require mchekhashvili/money:^1.0@dev mchekhashvili/countries:dev-main
php artisan migrate
php artisan countries:sync
```

## Usage

```php
use Mchekhashvili\Countries\Models\Country;

$georgia = Country::code('GE');        // also 'GEO' or '268'

$georgia->name;                        // in the app locale: 'საქართველო' (ka), 'Georgia' (en)
$georgia->nameIn('ru');                // 'Грузия'
$georgia->official_name;               // the ISO official name, when it has one
$georgia->currency();                  // Mchekhashvili\Money\Currency GEL; null for Antarctica
$georgia->flag;                        // '🇬🇪'

Country::query()->usingCurrency('EUR')->get();
Country::query()->whereCode('GEO')->first();   // null when there is no such country; code() throws
```

Point your own models at it:

```php
$table->foreignId('country_id')->constrained('countries');

public function country(): BelongsTo
{
    return $this->belongsTo(Country::class);
}
```

Routes bind by alpha-2: `/countries/{country}` resolves `/countries/GE`.

## Data

`resources/countries.php` holds the list: ISO 3166-1 codes from the iso-codes data (via pycountry 26.2.16), short names from Unicode CLDR, and the current legal-tender currency per CLDR. `countries:sync` (or `CountrySeeder`) loads it and can be run again after an update: it updates by alpha-2 and never deletes, so foreign keys stay intact.

## Config

`php artisan vendor:publish --tag=countries-config` — table name, connection, whether to run the migration, and the fallback locale for names.

## Tests

```
composer test
```

## License

MIT
