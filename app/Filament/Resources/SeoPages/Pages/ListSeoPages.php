<?php

namespace App\Filament\Resources\SeoPages\Pages;

use App\Filament\Resources\SeoPages\SeoPageResource;
use Filament\Resources\Pages\ListRecords;

class ListSeoPages extends ListRecords
{
    protected static string $resource = SeoPageResource::class;

    public function getSubheading(): ?string
    {
        return 'Title, description and search settings for each page. Site-wide options are under SEO Settings.';
    }
}
