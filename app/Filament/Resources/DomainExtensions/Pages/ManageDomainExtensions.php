<?php

namespace App\Filament\Resources\DomainExtensions\Pages;

use App\Filament\Resources\DomainExtensions\DomainExtensionResource;
use App\Services\DomainPricing;
use App\Services\WhmcsService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageDomainExtensions extends ManageRecords
{
    protected static string $resource = DomainExtensionResource::class;

    public function mount(): void
    {
        parent::mount();

        // New extensions added in WHMCS show up here automatically.
        app(DomainPricing::class)->sync();
    }

    public function getSubheading(): ?string
    {
        return 'Prices come live from WHMCS. Drag to change the order on the Domain Registration page, choose a badge, or hide an extension.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh from WHMCS')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    WhmcsService::flush();
                    $pricing = app(DomainPricing::class);

                    if (! $pricing->isAvailable()) {
                        Notification::make()->danger()->title('WHMCS prices are not available')->body('Check WHMCS Connection.')->persistent()->send();

                        return;
                    }

                    $added = $pricing->sync();
                    Notification::make()->success()->title('Prices refreshed')->body($added ? "{$added} new extensions added." : 'No new extensions.')->send();
                }),
            Action::make('view')
                ->label('View page')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(hostorio_url('domain-registration'))
                ->openUrlInNewTab(),
        ];
    }
}
