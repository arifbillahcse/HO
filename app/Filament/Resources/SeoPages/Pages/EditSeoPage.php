<?php

namespace App\Filament\Resources\SeoPages\Pages;

use App\Filament\Resources\SeoPages\SeoPageResource;
use App\Services\Seo;
use App\Services\SeoAnalyzer;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditSeoPage extends EditRecord
{
    protected static string $resource = SeoPageResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Read the page's text once, before rendering; the live score then uses the cache.
        app(SeoAnalyzer::class)->content($this->getRecord());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View page')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => Seo::url($this->getRecord()->slug()))
                ->openUrlInNewTab(),
        ];
    }
}
