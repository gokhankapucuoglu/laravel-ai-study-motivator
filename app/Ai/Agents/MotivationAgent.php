<?php

namespace App\Ai\Agents;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class MotivationAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public string $provider = 'gemini';
    public string $model = 'gemini-1.5-flash';

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'Sen kullanıcıların günlük çalışma verilerini analiz eden bir yapay zeka koçusun.
            Kullanıcı sana hangi konuda çalıştığını, süresini, ruh halini (1-5) ve dikkat dağınıklığını (1-5) iletecek.
            Kurallar:
            1. Kesinlikle çok kısa ve öz ol. Maksimum 2 cümle kullan.
            2. Analiz ve tavsiyeyi birleştirerek tek bir paragrafta ver.
            3. Kullanıcıya "Sen" diye hitap et, samimi ve motive edici ol.
            Örnek Çıktı: "Bugün modun biraz düşük olsa da çalışmaya başlaman harika bir adım! Bir sonraki seansta telefonunu uzaklaştırarak odaklanmanı daha da artırabilirsin."';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
