<?php

namespace App\Filament\Resources\NotFoundLogs\Pages;

use App\Filament\Resources\NotFoundLogs\NotFoundLogResource;
use App\Models\NotFoundLog;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageNotFoundLogs extends ManageRecords
{
    protected static string $resource = NotFoundLogResource::class;

    public function getSubheading(): ?string
    {
        return 'Addresses that do not exist on the site. Redirect the ones that matter (old pages, old images, mistyped links) to a page that does. Bots probing for weaknesses also show up here; those can be deleted.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear')
                ->label('Clear log')
                ->icon(Heroicon::OutlinedTrash)
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Delete every entry in the 404 log? Redirects are not affected.')
                ->action(function (): void {
                    NotFoundLog::query()->delete();
                    Notification::make()->success()->title('404 log cleared')->send();
                }),
        ];
    }
}
