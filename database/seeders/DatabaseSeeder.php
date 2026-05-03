<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StudyLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ana Kullanıcının Oluşturulması
        $mainUser = User::firstOrCreate(
            ['email' => 'gokhankapucuoglu@gmail.com'],
            [
                'name' => 'Gökhan Kapucuoğlu',
                'password' => Hash::make('GokhaN2635!'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Faker ile 14 Adet Rastgele Türkçe Kullanıcı Oluşturma
        $faker = \Faker\Factory::create('tr_TR');
        $users = collect([$mainUser]);

        for ($i = 0; $i < 14; $i++) {
            $users->push(User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('GokhaN2635!'), // Şifreler hep aynı
                'email_verified_at' => now(),
            ]));
        }

        // 3. Çalışma Kayıtları İçin Mantıklı Konu ve Kategori Havuzu
        $topics = [
            ['title' => 'Kitap Okuma', 'category' => 'Okuma/Araştırma'],
            ['title' => 'Matematik Çalışma', 'category' => 'Ders/Konu Çalışması'],
            ['title' => 'Robotik Proje Geliştirme', 'category' => 'Proje Geliştirme'],
            ['title' => 'Arduino Sensör Testleri', 'category' => 'Proje Geliştirme'],
            ['title' => 'Genç-ARGE Toplantı Notları', 'category' => 'Not Çıkarma/Planlama'],
            ['title' => 'İngilizce Kelime Ezberi', 'category' => 'Ders/Konu Çalışması'],
            ['title' => 'Laravel ve Filament Eğitimi', 'category' => 'Okuma/Araştırma'],
        ];

        // Anlamlı ve Gerçekçi Türkçe Kullanıcı Notları Havuzu
        $fakeNotes = [
            'Konuyu tam olarak kavradım, yarın tekrar edip test çözeceğim.',
            'Bağlantı şemalarını çizdim, devre kurulumunu tamamladım. Yarın kodlamaya geçeceğim.',
            'Odaklanmakta çok zorlandım, bir dahaki sefere telefonu başka odaya bırakmalıyım.',
            'Çok verimli bir seanstı. Notlarımı temize çektim ve projeyi güncelledim.',
            'Projede beklediğimden daha hızlı ilerledim. Sensör okumaları gayet stabil.',
            'Kitabın ilk bölümünü bitirdim, özellikle son kısımlar çok ufuk açıcıydı.',
            'Zorlandığım yerleri işaretledim, yarın tekrar gözden geçirmem gerekecek.',
            'Biraz yorucu oldu ama hedeflediğim çalışma süresine ulaşmak motive ediciydi.'
        ];

        // Anlamlı ve Gerçekçi Türkçe Yapay Zeka Koçluk Analizleri Havuzu
        $fakeAIFeedback = [
            'Disiplinli çalışman takdire şayan. Ancak dikkat dağınıklığını azaltmak için çalışma ortamını sadeleştirmeyi ve uyarıcıları izole etmeni önerebilirim.',
            'Bugün harika bir performans sergilemişsin! Yüksek ruh halin verimini doğrudan artırmış. Bu derin çalışma (deep work) temposunu korumaya çalış.',
            'Modunun düşük olmasına rağmen masaya oturup çalışmayı bırakmaman çok profesyonelce bir irade göstergesi. Zihinsel yükünü hafifletmek için bir sonraki seansında ufak molalar verebilirsin.',
            'Süre ve odaklanma seviyen muazzam. Derin öğrenme sürecindesin, bu tutarlılık hedeflerine ulaşmanda en büyük silahın olacak.',
            'Dikkat seviyenin biraz dalgalandığını görüyorum. Masanda sadece ilgilendiğin konuyla alakalı materyalleri bulundurmak odaklanmanı çok daha kolaylaştıracaktır.'
        ];

        $moods = ['1', '2', '3', '4', '5'];
        $distractions = ['1', '2', '3', '4', '5'];

        // Her kullanıcı için geriye dönük son 2 hafta içinde rastgele kayıtlar oluşturalım
        foreach ($users as $user) {
            // Her kullanıcıya 8 ile 15 arasında rastgele kayıt atıyoruz
            $logCount = rand(8, 15);

            for ($j = 0; $j < $logCount; $j++) {
                $topic = $faker->randomElement($topics);

                // Kayıtların hepsi aynı güne yığılmasın, son 15 gün içine dağılsın
                $randomDate = Carbon::now()
                    ->subDays(rand(0, 15))
                    ->subMinutes(rand(10, 1400));

                StudyLog::create([
                    'user_id' => $user->id,
                    'topic' => $topic['title'],
                    'category' => $topic['category'],
                    'study_duration_minutes' => rand(25, 120), // 25 ile 120 dakika arası
                    'mood' => $faker->randomElement($moods),
                    'distraction_level' => $faker->randomElement($distractions),
                    'notes' => $faker->randomElement($fakeNotes), // Gerçekçi Türkçe Not
                    'ai_feedback' => $faker->randomElement($fakeAIFeedback), // Gerçekçi AI Dönüşü
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ]);
            }
        }
    }
}
