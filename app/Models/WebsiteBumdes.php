<?php

namespace App\Models;

use Database\Factories\WebsiteBumdesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['bumdes_id', 'subdomain', 'custom_domain', 'is_active', 'activated_at'])]
class WebsiteBumdes extends Model
{
    /** @use HasFactory<WebsiteBumdesFactory> */
    use HasFactory;

    protected $table = 'website_bumdes';

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @param Builder<WebsiteBumdes> $query @return Builder<WebsiteBumdes> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereHas('bumdes');
    }

    public static function defaultSubdomain(Bumdes $bumdes): string
    {
        return Str::limit(Str::slug($bumdes->nama) ?: 'bumdes', min(50, 62 - strlen((string) $bumdes->id)), '').'-'.$bumdes->id;
    }

    public function defaultHost(): string
    {
        return $this->subdomain.'.'.config('bumdes.website.base_domain');
    }

    public function customDomainIsReady(): bool
    {
        return $this->custom_domain !== null
            && in_array($this->custom_domain, config('bumdes.website.verified_custom_domains', []), true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'activated_at' => 'datetime'];
    }
}
