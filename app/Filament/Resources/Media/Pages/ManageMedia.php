<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;

class ManageMedia extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->mutateDataUsing(function (array $data): array {
                $path = $data['path'];
                $data['disk'] = 'public';
                $data['mime_type'] = Storage::disk('public')->mimeType($path);
                $data['file_size'] = Storage::disk('public')->size($path);

                return $data;
            }),
        ];
    }
}
