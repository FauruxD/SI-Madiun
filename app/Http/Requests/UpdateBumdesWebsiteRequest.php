<?php

namespace App\Http\Requests;

use App\Models\Bumdes;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBumdesWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        $profile = Bumdes::query()->visibleTo($user)->findOrFail((int) $this->route('bumdes'));

        return $user->can('manageWebsite', $profile);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $id = (int) $this->route('bumdes');

        return [
            'subdomain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/D', Rule::notIn(['www', 'admin', 'api', 'mail', 'localhost']), Rule::unique('website_bumdes')->ignore($id, 'bumdes_id')],
            'custom_domain' => ['nullable', 'string', 'max:253', 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/D', Rule::unique('website_bumdes')->ignore($id, 'bumdes_id'), function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value)) {
                    return;
                }
                $base = strtolower((string) config('bumdes.website.base_domain'));
                $appHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
                if ($value === $appHost || $value === $base || str_ends_with($value, '.'.$base) || in_array($value, ['localhost', 'example.com', 'example.org', 'example.net'], true)) {
                    $fail('Domain tersebut tidak dapat digunakan sebagai custom domain BUMDes.');
                }
            }],
            'is_active' => ['required', 'boolean'],
            'verified_custom_domains' => ['prohibited'],
            'company_profile_published' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'subdomain' => is_string($this->input('subdomain')) ? strtolower(trim($this->input('subdomain'))) : $this->input('subdomain'),
            'custom_domain' => is_string($this->input('custom_domain')) ? (strtolower(trim($this->input('custom_domain'))) ?: null) : $this->input('custom_domain'),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'subdomain.required' => 'Subdomain wajib diisi.',
            'subdomain.regex' => 'Gunakan huruf kecil, angka, dan tanda hubung tanpa titik atau spasi.',
            'subdomain.unique' => 'Subdomain sudah digunakan BUMDes lain.',
            'subdomain.not_in' => 'Subdomain tersebut dicadangkan untuk sistem.',
            'custom_domain.regex' => 'Isi hostname domain saja, tanpa https://, port, atau path.',
            'custom_domain.unique' => 'Custom domain sudah digunakan BUMDes lain.',
            'is_active.required' => 'Pilih status tampilan website.',
            'is_active.boolean' => 'Status website tidak valid.',
        ];
    }
}
