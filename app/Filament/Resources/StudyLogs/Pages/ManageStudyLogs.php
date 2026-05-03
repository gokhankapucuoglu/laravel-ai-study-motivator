<?php

namespace App\Filament\Resources\StudyLogs\Pages;

use App\Ai\Agents\MotivationAgent;
use App\Filament\Resources\StudyLogs\StudyLogResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManageStudyLogs extends ManageRecords
{
    protected static string $resource = StudyLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->after(function ($record) {
                    $user = Auth::user();

                    $currentLocale = app()->getLocale();

                    // Laravel AI SDK 13.x Ajanımızı başlatıyoruz
                    $agent = new MotivationAgent();

                    $prompt = "Kullanıcı yeni bir seans kaydetti. Konu/Proje: {$record->topic}, Kategori: {$record->category}, Süre: {$record->study_duration_minutes} dakika. Ruh hali: {$record->mood}/5, Dikkat dağınıklığı: {$record->distraction_level}/5.";

                    // İşlemi arka plana atıyoruz (queue)
                    $agent->forUser($user)
                        ->queue($prompt)
                        ->catch(function (\Throwable $e) use ($user) {
                            // Gemini 503 Yoğunluk Hatası veya Bağlantı Kopması Durumunda
                            Log::warning('Yapay zeka yoğun, tekrar denenecek...');

                            throw $e;
                        })
                        ->then(function ($response) use ($user, $record, $currentLocale) {
                            app()->setLocale($currentLocale);

                            $aiText = $response->text; // DİKKAT: Metot olduğu için () eklendi

                            // 1. Veritabanındaki 'ai_feedback' sütununu güncelle (Tabloda görünmesi için)
                            $record->update([
                                'ai_feedback' => $aiText
                            ]);

                            // 2. Arka planda analiz bitince veritabanı bildirimi atıyoruz
                            Notification::make()
                                ->title("{$record->topic} (#{$record->id}) " . __('study.analysis_ready'))
                                ->body(Str::limit($aiText, 100, '...'))
                                ->success()
                                ->actions([
                                    Action::make('view_logs')
                                        ->label(__('study.go_to_logs'))
                                        ->button() // Görünümü link değil, buton yapar
                                        ->url(url('/admin/study-logs')),
                                    Action::make('markAsRead')->label(__('study.mark_as_read'))->color('secondary')->markAsRead(),
                                ])

                                ->sendToDatabase($user);
                        });
                }),
        ];
    }
}
