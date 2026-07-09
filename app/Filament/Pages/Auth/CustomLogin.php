<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <style>
                .fi-logo { display: none !important; }
            </style>
            <div class="flex flex-col items-center gap-2 mt-4">
                <img src="' . asset('logo.png') . '" alt="Logo" style="height: 100px;" class="w-auto">
                <span class="text-xl font-bold">Madrasah Diniyyah Al-Amiriyyah</span>
            </div>
        ');
    }
}
