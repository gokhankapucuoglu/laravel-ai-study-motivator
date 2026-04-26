<?php

namespace App\Filament\Resources\StudyLogs;

use App\Filament\Resources\StudyLogs\Pages\ManageStudyLogs;
use App\Models\StudyLog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudyLogResource extends Resource
{
    protected static ?string $model = StudyLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;
    protected static ?string $pluralModelLabel = 'Çalışma Kayıtları';
    protected static ?string $slug = 'study-logs';
    protected static ?string $modelLabel = 'Çalışma Kaydı';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),

                TextInput::make('topic')
                    ->label(__('study.topic'))
                    ->placeholder('Örn: Laravel AI Entegrasyonu')
                    ->required(),

                Select::make('category')
                    ->label(__('study.category'))
                    ->options([
                        'project' => 'Proje Geliştirme',
                        'study' => 'Ders/Konu Çalışması',
                        'reading' => 'Okuma/Araştırma',
                        'notes' => 'Not Çıkarma/Planlama',
                        'other' => 'Diğer',
                    ])
                    ->required(),

                TextInput::make('study_duration_minutes')
                    ->label(__('study.duration'))
                    ->required()
                    ->numeric(),

                Select::make('mood')
                    ->label(__('study.mood'))
                    ->options([
                        1 => '1 - Çok Kötü',
                        2 => '2 - Kötü',
                        3 => '3 - Normal',
                        4 => '4 - İyi',
                        5 => '5 - Çok İyi',
                    ])
                    ->required(),

                Select::make('distraction_level')
                    ->label(__('study.distraction'))
                    ->options([
                        1 => '1 - Çok Düşük',
                        2 => '2 - Düşük',
                        3 => '3 - Orta',
                        4 => '4 - Yüksek',
                        5 => '5 - Çok Yüksek',
                    ])
                    ->required(),

                Textarea::make('notes')
                    ->label(__('study.notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('topic')->label('Konu')->sortable()->searchable(),
                TextColumn::make('category')->label('Kategori')->sortable(),
                TextColumn::make('study_duration_minutes')->label('Süre (dk)')->sortable(),
                TextColumn::make('mood')->label('Ruh Hali')->sortable(),
                TextColumn::make('created_at')->label('Tarih')->dateTime()->sortable(),
                TextColumn::make('ai_feedback')
                    ->label('🤖 AI Analizi')
                    ->badge() // Sütunu rozet (badge) görünümüne çevirir
                    ->placeholder('⏳ Bekleniyor...') // Analiz gelene kadar gösterilecek metin
                    ->color(fn($state) => $state === null ? 'warning' : 'success') // Beklerken sarı, gelince yeşil
                    ->formatStateUsing(fn($state) => $state === null ? '⏳ Bekleniyor...' : '✨ Analizi Oku') // Yazıyı kısaltır
                    ->action(
                        Action::make('view_ai_analysis')
                            ->disabled(fn($record) => $record->ai_feedback === null) // Eğer analiz henüz gelmediyse tıklanmayı engeller
                            ->modalHeading('🤖 Yapay Zeka Tavsiyesi')
                            ->modalSubmitAction(false) // Alt kısımdaki gereksiz "Kaydet" butonunu gizler
                            ->modalCancelActionLabel('Kapat')
                            ->schema([
                                Textarea::make('ai_feedback')
                                    ->hiddenLabel()
                                    ->disabled() // Kullanıcının metni değiştirmesini engeller
                                    ->autosize() // Metnin uzunluğuna göre kutuyu otomatik büyütür
                                    ->default(fn($record) => $record->ai_feedback)
                            ])
                    ),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel(),
                DeleteAction::make()->hiddenLabel(),
                ForceDeleteAction::make()->hiddenLabel(),
                RestoreAction::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStudyLogs::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
