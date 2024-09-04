<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Login as BasePage;

class CustomLogin extends BasePage
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
            'remember' => true,
        ]);
    }
}
