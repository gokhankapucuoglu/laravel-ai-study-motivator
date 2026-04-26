<?php

namespace App\Filament\Resources\Notifications;

use App\Filament\Resources\Notifications\Pages\ManageNotifications;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends Resource
{
    protected static ?string $model = DatabaseNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // JSON formatındaki 'data' sütununun içindeki 'title' anahtarını çekiyoruz
                TextColumn::make('data.title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),

                // JSON içindeki 'body' (AI tavsiyesi vs.) verisini çekiyoruz
                TextColumn::make('data.body')
                    ->label('İçerik')
                    ->limit(50)
                    ->tooltip(fn($state): ?string => $state),

                TextColumn::make('created_at')
                    ->label('Gönderilme Tarihi')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('read_at')
                    ->label('Durum')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->read_at !== null ? 'Okundu' : 'Okunmadı')
                    // Renk ayarını da yeni belirlediğimiz bu metinlere göre güncelliyoruz:
                    ->color(fn($state) => $state === 'Okundu' ? 'success' : 'warning'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->hiddenLabel()
                    ->modalHeading('Bildirim Detayı')
                    ->schema([
                        TextInput::make('data.title')->label('Başlık'),
                        Textarea::make('data.body')->label('İçerik')->autosize(),
                    ]),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageNotifications::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('notifiable_type', auth()->user()->getMorphClass())
            ->where('notifiable_id', auth()->id());
    }
}
