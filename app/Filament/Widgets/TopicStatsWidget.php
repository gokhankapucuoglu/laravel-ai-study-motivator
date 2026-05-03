<?php

namespace App\Filament\Widgets;

use App\Models\StudyLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;

class TopicStatsWidget extends BaseWidget implements HasActions
{

    use InteractsWithActions;

    // YAN YANA 2 DİKDÖRTGEN GÖSTERMEK İÇİN SİHİRLİ AYAR
    protected function getColumns(): int
    {
        return 3;
    }


    protected function getStats(): array
    {
        $userId = auth()->id();

        // 1. DEĞİŞİKLİK: select() içine MIN ve MAX fonksiyonlarıyla tarihleri ekliyoruz
        $logs = StudyLog::select(
            'topic',
            DB::raw('SUM(study_duration_minutes) as total_duration'),
            DB::raw('MIN(created_at) as first_study_date'),
            DB::raw('MAX(created_at) as last_study_date')
        )
            ->where('user_id', $userId)
            ->whereNotNull('topic')
            ->groupBy('topic')
            ->orderByDesc('total_duration')
            ->get();

        if ($logs->isEmpty()) {
            return [
                Stat::make('Merhaba!', '0 dk')
                    ->description('Hadi ilk çalışma kaydını oluştur ve serüvene başla!')
                    ->descriptionIcon('heroicon-m-rocket-launch')
                    ->color('primary')
                    ->columnspan(3) // Tek bir kartın tüm sütunları kaplamasını sağlıyoruz
                    ->extraAttributes([
                        'class' => '!bg-primary-50 dark:!bg-primary-900/20 ring-1 ring-primary-500/50',
                    ]),
            ];
        }

        $stats = [];

        // 1. DİKKAT: foreach içine $index ekliyoruz!
        foreach ($logs as $index => $log) {

            // Saat ve dakika hesaplamaları
            $minutes = $log->total_duration;
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            $timeText = ($hours > 0 ? "{$hours} " . __('study.hours') . " " : "") . "{$mins} " . __('study.mins');

            // Tarih hesaplamaları
            $firstDate = \Carbon\Carbon::parse($log->first_study_date);
            $lastDate = \Carbon\Carbon::parse($log->last_study_date);
            $dateRangeText = $firstDate->isSameDay($lastDate)
                ? $firstDate->format('d.m.Y')
                : $firstDate->format('d.m.Y') . ' - ' . $lastDate->format('d.m.Y');

            // 2. KUSURSUZ SIRALI RENK ATAMASI (crc32 SİLİNDİ)
            $availableColors = ['info', 'warning', 'success', 'danger', 'primary'];

            // 3. Rengi doğrudan döngünün sırasına ($index) göre alıyoruz
            $color = $availableColors[$index % count($availableColors)];

            $bgStyle = match ($color) {
                'info'    => 'background-color: rgba(59, 130, 246, 0.08);', // Mavi
                'warning' => 'background-color: rgba(234, 179, 8, 0.08);',  // Sarı
                'success' => 'background-color: rgba(34, 197, 94, 0.08);',  // Yeşil
                'danger'  => 'background-color: rgba(239, 68, 68, 0.08);',  // Kırmızı
                'primary' => 'background-color: rgba(245, 158, 11, 0.08);', // Amber
                default   => '',
            };

            $stats[] = Stat::make($log->topic, trim($timeText))
                ->icon('heroicon-m-book-open')
                ->description($dateRangeText)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($color)
                ->extraAttributes([
                    'style' => $bgStyle,
                ]);
        }

        return $stats;
    }
}
