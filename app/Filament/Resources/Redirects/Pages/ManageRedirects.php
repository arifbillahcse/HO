<?php

namespace App\Filament\Resources\Redirects\Pages;

use App\Filament\Resources\Redirects\RedirectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRedirects extends ManageRecords
{
    protected static string $resource = RedirectResource::class;

    public function getSubheading(): ?string
    {
        return 'Send visitors and search engines from an old address to a new one.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New redirect')];
    }
}
