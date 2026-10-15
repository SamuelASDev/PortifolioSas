<?php

namespace App\Rules;

use App\Support\ProjectTags;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ProjectTagList implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }
        $tags = app(ProjectTags::class)->parse($value);
        if (count($tags) > 8) {
            $fail('Use no máximo 8 tags por projeto.');
        }
        foreach ($tags as $name) {
            if (Str::length($name) > 30 || ! preg_match('/^[\pL\pN.#][\pL\pN .+#\/_-]*$/u', $name)) {
                $fail('Cada tag deve ter até 30 caracteres e usar apenas letras, números, espaços ou . + # / _ -.');
                break;
            }
        }
    }
}
