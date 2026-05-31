<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;

trait PasswordCompatibility
{
    protected function passwordMatches(string $plainPassword, ?string $storedPassword, $user = null): bool
    {
        if (!is_string($storedPassword) || $storedPassword === '') {
            return false;
        }

        if ($this->isBcryptHash($storedPassword)) {
            return Hash::check($plainPassword, $storedPassword);
        }

        if (!hash_equals($storedPassword, $plainPassword)) {
            return false;
        }

        if ($user) {
            $user->password = Hash::make($plainPassword);
            $user->save();
        }

        return true;
    }

    protected function isBcryptHash(string $value): bool
    {
        return str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$') || str_starts_with($value, '$2b$');
    }
}
