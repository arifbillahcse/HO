<?php

namespace App\Filament\Resources\ComparisonRows\Pages;

use App\Filament\Resources\ComparisonRows\ComparisonRowResource;
use App\Models\ComparisonRow;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageComparisonRows extends ManageRecords
{
    protected static string $resource = ComparisonRowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New row')
                ->mutateDataUsing(function (array $data): array {
                    $data['sort_order'] = (int) ComparisonRow::query()->where('page_key', $data['page_key'])->max('sort_order') + 1;

                    return $data;
                }),
        ];
    }
}
