<?php

namespace Database\Seeders;

use AshAllenDesign\ShortURL\Models\ShortURL;
use Illuminate\Database\Seeder;

class ShortUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var list<array{key: string, title: string, destination: string}> $links */
        $links = [
            ['key' => 'website', 'title' => 'Company Website', 'destination' => 'https://example.com'],
            ['key' => 'docs', 'title' => 'Documentation', 'destination' => 'https://example.org'],
            ['key' => 'support', 'title' => 'Support', 'destination' => 'https://example.net'],
            ['key' => 'newsletter', 'title' => 'Newsletter', 'destination' => 'https://example.com/?source=newsletter'],
            ['key' => 'campaign', 'title' => 'Campaign', 'destination' => 'https://example.org/?source=campaign'],
            ['key' => 'event', 'title' => 'Event', 'destination' => 'https://example.net/?source=event'],
        ];

        foreach ($links as $link) {
            $shortUrl = ShortURL::updateOrCreate(
                ['url_key' => $link['key']],
                [
                    'destination_url' => $link['destination'],
                    'default_short_url' => url($link['key']),
                    'single_use' => false,
                    'forward_query_params' => false,
                    'track_visits' => true,
                    'redirect_status_code' => 302,
                    'track_ip_address' => false,
                    'track_operating_system' => true,
                    'track_operating_system_version' => true,
                    'track_browser' => true,
                    'track_browser_version' => true,
                    'track_referer_url' => true,
                    'track_device_type' => true,
                    'activated_at' => now(),
                    'deactivated_at' => null,
                ],
            );

            $shortUrl->forceFill([
                'title' => $link['title'],
            ])->save();
        }
    }
}
