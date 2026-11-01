<?php

declare(strict_types=1);

namespace Mchekhashvili\Countries\Database\Seeders;

use Illuminate\Database\Seeder;
use Mchekhashvili\Countries\Models\Country;

/**
 * Loads or refreshes the ISO 3166-1 list. Safe to run again: it updates by alpha-2 and never
 * deletes, so rows other tables point at survive.
 */
final class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = array_map(fn (array $c) => [
            'alpha2' => $c[0],
            'alpha3' => $c[1],
            'numeric' => $c[2],
            'currency' => $c[3],
            'names' => json_encode(array_filter(['en' => $c[4], 'ka' => $c[5], 'ru' => $c[6]]), JSON_UNESCAPED_UNICODE),
            'official_name' => $c[7],
            'created_at' => $now,
            'updated_at' => $now,
        ], require __DIR__ . '/../../resources/countries.php');

        Country::query()->upsert($rows, ['alpha2'], ['alpha3', 'numeric', 'currency', 'names', 'official_name', 'updated_at']);
    }
}
