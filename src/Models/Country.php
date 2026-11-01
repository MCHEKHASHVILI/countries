<?php

declare(strict_types=1);

namespace Mchekhashvili\Countries\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mchekhashvili\Money\Currency;

/**
 * A country by ISO 3166-1.
 *
 *     Country::code('GE')           by alpha-2, alpha-3 ('GEO') or numeric ('268')
 *     $georgia->name                in the app's locale: 'საქართველო' under ka, 'Georgia' under en
 *     $georgia->nameIn('ru')        'Грузия'
 *     $georgia->currency()          Mchekhashvili\Money\Currency GEL
 *     $georgia->flag                '🇬🇪'
 *
 * Other models point at it with a `country_id` foreign key and belongsTo(Country::class).
 *
 * @property int $id
 * @property string $alpha2
 * @property string $alpha3
 * @property string $numeric
 * @property ?string $currency
 * @property array<string, string> $names
 * @property ?string $official_name
 * @property-read string $name
 * @property-read string $flag
 */
final class Country extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('countries.table', 'countries');
    }

    public function getConnectionName(): ?string
    {
        return config('countries.connection') ?? parent::getConnectionName();
    }

    protected function casts(): array
    {
        return ['names' => 'array'];
    }

    /** By any ISO 3166-1 code; throws if there is none. */
    public static function code(string $code): self
    {
        return self::query()->whereCode($code)->first()
            ?? throw (new ModelNotFoundException())->setModel(self::class, [$code]);
    }

    /** @param Builder<self> $query */
    public function scopeWhereCode(Builder $query, string $code): void
    {
        $code = strtoupper(trim($code));

        match (true) {
            ctype_digit($code) => $query->where('numeric', str_pad($code, 3, '0', STR_PAD_LEFT)),
            strlen($code) === 3 => $query->where('alpha3', $code),
            default => $query->where('alpha2', $code),
        };
    }

    /** @param Builder<self> $query */
    public function scopeUsingCurrency(Builder $query, Currency|string $currency): void
    {
        $query->where('currency', Currency::of($currency)->code);
    }

    /** The name in a locale, falling back to config('countries.fallback_locale'). */
    public function nameIn(string $locale): string
    {
        $names = $this->names ?? [];

        return $names[$locale] ?? $names[strtok($locale, '_-')] ?? $names[config('countries.fallback_locale', 'en')] ?? $this->alpha2;
    }

    /** @return Attribute<string, never> */
    protected function name(): Attribute
    {
        return Attribute::get(fn () => $this->nameIn(app()->getLocale()));
    }

    /** @return Attribute<string, never> */
    protected function flag(): Attribute
    {
        return Attribute::get(fn () => implode('', array_map(
            fn (string $letter) => mb_chr(0x1F1E6 + ord($letter) - ord('A')),
            str_split($this->alpha2),
        )));
    }

    public function currency(): ?Currency
    {
        return $this->currency === null || !Currency::isKnown($this->currency) ? null : Currency::of($this->currency);
    }

    /** Route model binding by alpha-2: /countries/GE */
    public function getRouteKeyName(): string
    {
        return 'alpha2';
    }
}
