<?php

declare(strict_types=1);

namespace Mchekhashvili\Countries\Console;

use Illuminate\Console\Command;
use Mchekhashvili\Countries\Database\Seeders\CountrySeeder;
use Mchekhashvili\Countries\Models\Country;

final class SyncCountries extends Command
{
    protected $signature = 'countries:sync';

    protected $description = 'Load or refresh the ISO 3166-1 countries';

    public function handle(CountrySeeder $seeder): int
    {
        $seeder->run();
        $this->components->info(Country::query()->count() . ' countries in ' . config('countries.table') . '.');

        return self::SUCCESS;
    }
}
