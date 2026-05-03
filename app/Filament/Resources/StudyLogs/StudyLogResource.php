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
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudyLogResource extends Resource
{
    protected static ?string $model = StudyLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;
    protected static ?string $slug = 'study-logs';
    public static function getPluralModelLabel(): string
    {
        return __('study.logs_menu');
    }

    public static function getModelLabel(): string
    {
        return __('study.log_singular');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),

                TextInput::make('topic')
                    ->label(__('study.topic'))
                    ->placeholder('Örn: Laravel AI Entegrasyonu')
                    ->required()
                    ->datalist(function () {
                        // Veritabanından bu kullanıcının daha önce girdiği "benzersiz (distinct)" konuları getirir
                        return StudyLog::where('user_id', auth()->id())
                            ->whereNotNull('topic')
                            ->distinct()
                            ->pluck('topic')
                            ->toArray();
                    })
                    ->autocomplete(false), // Tarayıcının kendi geçmişini kapatır ki bizim datalist temiz görünsün

                Select::make('category')
                    ->label(__('study.category'))
                    ->options([
                        'Proje Geliştirme' => 'Proje Geliştirme',
                        'Ders/Konu Çalışması' => 'Ders/Konu Çalışması',
                        'Okuma/Araştırma' => 'Okuma/Araştırma',
                        'Not Çıkarma/Planlama' => 'Not Çıkarma/Planlama',
                        'Diğer' => 'Diğer',
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
            ->poll('3s')
            ->groups([
                Group::make('topic')
                    ->label(fn() => __('study.topic'))
                    ->orderQueryUsing(function (Builder $query) {
                        // $direction değişkenini sildik ve her zaman en yeni olanı EN ÜSTE almak için zorla DESC yazdık
                        $query->orderByRaw("(SELECT MAX(created_at) FROM study_logs AS sl WHERE sl.topic = study_logs.topic) DESC");
                    }),

                Group::make('category')
                    ->label(fn() => __('study.category'))
                    ->orderQueryUsing(function (Builder $query) {
                        // Kategori için de zorunlu DESC
                        $query->orderByRaw("(SELECT MAX(created_at) FROM study_logs AS sl WHERE sl.category = study_logs.category) DESC");
                    }),
            ])
            ->defaultGroup('topic')
            ->defaultSort('created_at', 'desc')

            ->columns([
                TextColumn::make('topic')->label(fn() => __('study.topic'))->sortable()->searchable(),
                TextColumn::make('category')->label(fn() => __('study.category'))->sortable(),
                TextColumn::make('study_duration_minutes')
                    ->label(fn() => __('study.duration'))
                    ->badge()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label(fn() => __('study.total'))
                            ->formatStateUsing(function ($state) {
                                if (!$state) return '0 ' . __('study.mins');

                                // Dakikayı saate çevirme işlemleri
                                $hours = floor($state / 60);
                                $minutes = $state % 60;

                                $timeText = '';
                                if ($hours > 0) {
                                    $timeText .= "{$hours} " . __('study.hours') . " ";
                                }
                                if ($minutes > 0 || $hours === 0) {
                                    $timeText .= "{$minutes} " . __('study.mins');
                                }

                                // HTML olmadan sadece düz metni döndürüyoruz
                                return trim($timeText);
                            })
                    ),
                TextColumn::make('mood')
                    ->label(fn() => __('study.mood'))
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '1' => '1 - Çok Kötü',
                        '2' => '2 - Kötü',
                        '3' => '3 - Normal',
                        '4' => '4 - İyi',
                        '5' => '5 - Çok İyi',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '1', '2' => 'danger',   // Kırmızı
                        '3' => 'warning',       // Sarı
                        '4', '5' => 'success',  // Yeşil
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label(fn() => __('study.date'))->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('ai_feedback')
                    ->label(fn() => __('study.ai_analysis'))
                    ->badge()
                    ->getStateUsing(fn(StudyLog $record) => $record->ai_feedback ?? 'bekleniyor')
                    ->color(function (string $state, StudyLog $record) {
                        if ($state === 'bekleniyor') {
                            return 'warning'; // Sarı renk
                        }

                        $hasUnread = auth()->user()->unreadNotifications()
                            ->where('data->title', 'like', "%(#{$record->id})%")
                            ->exists();

                        return $hasUnread ? 'success' : 'gray';
                    })
                    ->formatStateUsing(function (string $state, StudyLog $record) {
                        // Durum 'bekleniyor' ise çevrilmiş metni yazdırıyoruz!
                        if ($state === 'bekleniyor') {
                            return __('study.waiting');
                        }

                        $hasUnread = auth()->user()->unreadNotifications()
                            ->where('data->title', 'like', "%(#{$record->id})%") // ARTIK NOKTA ATIŞI!
                            ->exists();

                        return $hasUnread ? __('study.new_analysis') : __('study.analysis_read');
                    })
                    ->action(
                        Action::make('view_ai_analysis')
                            ->disabled(fn($record) => $record->ai_feedback === null)
                            ->modalHeading(fn() => __('study.ai_feedback_modal_title'))
                            ->modalSubmitActionLabel(fn() => __('study.read_close'))
                            ->modalCancelAction(false) // Sadece tek bir buton yeterli
                            ->action(function (StudyLog $record) {
                                // SİHİRLİ KISIM: Modal açıldığında, o konuya ait okunmamış bildirimi bul ve anında okundu yap!
                                auth()->user()->unreadNotifications()
                                    ->where('data->title', 'like', "%(#{$record->id})%")
                                    ->update(['read_at' => now()]);
                            })
                            ->schema([
                                Textarea::make('ai_feedback_display')
                                    ->hiddenLabel()
                                    ->disabled()
                                    ->autosize()
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
