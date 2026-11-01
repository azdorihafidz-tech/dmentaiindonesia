<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivityWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Aktivitas admin terbaru')
            ->query(ActivityLog::query()->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('user_name')->label('Admin')->placeholder('Admin dihapus'),
                TextColumn::make('action')->label('Aksi')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'created' => 'Dibuat',
                    'updated' => 'Diubah',
                    'deleted' => 'Dihapus',
                    default => $state,
                })->color(fn (string $state): string => match ($state) {
                    'created' => 'success',
                    'updated' => 'warning',
                    'deleted' => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('subject_type')->label('Jenis'),
                TextColumn::make('subject_name')->label('Konten')->limit(60),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada aktivitas')
            ->emptyStateDescription('Perubahan konten oleh admin akan dicatat di sini.');
    }
}
