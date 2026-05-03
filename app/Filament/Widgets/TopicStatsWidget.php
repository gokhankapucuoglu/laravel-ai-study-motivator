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

        foreach ($logs as $log) {
            // Dakikayı Saate Çevirme Formülü
            $minutes = $log->total_duration;
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;

            $hoursLabel = __('study.hours');
            $minsLabel = __('study.mins');

            $timeText = ($hours > 0 ? "{$hours} {$hoursLabel} " : "") . "{$mins} {$minsLabel}";

            // 2. DEĞİŞİKLİK: Tarihleri kontrol edip metin oluşturuyoruz
            $firstDate = \Carbon\Carbon::parse($log->first_study_date);
            $lastDate = \Carbon\Carbon::parse($log->last_study_date);

            // Eğer aynı gün çalışılmışsa tek tarih (Örn: 01.05.2026)
            // Farklı günlerse aralık göster (Örn: 26.04.2026 - 01.05.2026)
            if ($firstDate->isSameDay($lastDate)) {
                $dateRangeText = $firstDate->format('d.m.Y');
            } else {
                $dateRangeText = $firstDate->format('d.m.Y') . ' - ' . $lastDate->format('d.m.Y');
            }

            // Stat Kartını Oluşturma
            $stats[] = Stat::make($log->topic, trim($timeText))
                ->icon('heroicon-m-book-open') // Konu ikonunu kitap ile değiştirdik
                ->description($dateRangeText) // Statik metin yerine dinamik tarih değişkenimizi koyduk
                ->descriptionIcon('heroicon-m-calendar-days') // İkonu takvim ile değiştirdik
                ->color('gray'); // Tarihlerin gözü yormaması için rengi gri yaptık
        }

        return $stats;
    }
}
