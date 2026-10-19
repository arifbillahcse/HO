<?php

namespace App\Filament\Resources\SeoPages\Pages;

use App\Filament\Resources\SeoPages\SeoPageResource;
use App\Models\SeoPage;
use App\Services\SeoAnalyzer;
use Filament\Resources\Pages\ListRecords;

class ListSeoPages extends ListRecords
{
    protected static string $resource = SeoPageResource::class;

    public function mount(): void
    {
        parent::mount();

        // Read each page's text before the table renders (cached until the page changes).
        SeoPage::query()->get()->each(fn (SeoPage $page) => app(SeoAnalyzer::class)->content($page));
    }

    public function getSubheading(): ?string
    {
        return 'Title, description and search settings for each page. Site-wide options are under SEO Settings.';
    }
}
