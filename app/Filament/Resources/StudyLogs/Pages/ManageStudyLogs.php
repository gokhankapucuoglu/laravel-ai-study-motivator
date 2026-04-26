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

                    // Laravel AI SDK 13.x Ajanımızı başlatıyoruz
                    $agent = new MotivationAgent();

                    $prompt = "Kullanıcı yeni bir seans kaydetti. Konu/Proje: {$record->topic}, Kategori: {$record->category}, Süre: {$record->study_duration_minutes} dakika. Ruh hali: {$record->mood}/5, Dikkat dağınıklığı: {$record->distraction_level}/5.";

                    // İşlemi arka plana atıyoruz (queue)
                    $agent->forUser($user)
                        ->queue($prompt)
                        ->catch(function (\Throwable $e) use ($user) {
                            // Gemini 503 Yoğunluk Hatası veya Bağlantı Kopması Durumunda
                            Log::warning('Gemini yoğun, tekrar denenecek...');

                            throw $e;
                        })
                        ->then(function ($response) use ($user, $record) {
                            $aiText = $response->text; // DİKKAT: Metot olduğu için () eklendi

                            // 1. Veritabanındaki 'ai_feedback' sütununu güncelle (Tabloda görünmesi için)
                            $record->update([
                                'ai_feedback' => $aiText
                            ]);

                            // 2. Arka planda analiz bitince veritabanı bildirimi atıyoruz
                            Notification::make()
                                ->title("$record->topic Yapay Zeka Analizin Hazır! 🤖")
                                ->body(Str::limit($aiText, 100, '...'))
                                ->success()
                                ->actions([
                                    Action::make('markAsRead')->label('Okundu')->color('secondary')->markAsRead(),
                                ])

                                ->sendToDatabase($user);
                        });
                }),
        ];
    }
}
