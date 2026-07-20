<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token || ! $from) {
            Log::warning('SMS non envoyé : configuration Twilio manquante dans .env');
            return false;
        }

        $to = $this->normalizeNumber($to);

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->failed()) {
                Log::warning('Échec envoi SMS Twilio: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Erreur envoi SMS: ' . $e->getMessage());
            return false;
        }
    }

    private function normalizeNumber(string $number): string
    {
        $number = preg_replace('/\s+/', '', $number);

        if (str_starts_with($number, '+')) {
            return $number;
        }

        return '+222' . ltrim($number, '0');
    }
}
