<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $phoneNumberId;
    private string $baseUrl;
    private string $countryCode;

    public function __construct()
    {
        $this->token         = config('services.whatsapp.token', '');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->countryCode   = config('services.whatsapp.default_country_code', '223');
        $version             = config('services.whatsapp.api_version', 'v20.0');
        $this->baseUrl       = "https://graph.facebook.com/{$version}/{$this->phoneNumberId}";
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->phoneNumberId);
    }

    /**
     * Normalise un numéro vers le format international sans le +
     * Ex: "70123456" → "22370123456", "+22370123456" → "22370123456"
     */
    public function formatPhone(string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', $phone);

        if (empty($digits)) {
            return '';
        }

        // Déjà avec indicatif (> 9 chiffres)
        if (strlen($digits) > 9) {
            return $digits;
        }

        // Numéro local → ajouter l'indicatif pays par défaut
        return $this->countryCode . $digits;
    }

    /**
     * Upload une image (base64 data URL) vers les serveurs Meta et retourne le media_id.
     * Retourne null en cas d'échec.
     */
    public function uploadImage(string $base64DataUrl): ?string
    {
        // Retirer le préfixe "data:image/png;base64,"
        $binary = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64DataUrl));

        if (empty($binary)) {
            Log::warning('[WhatsApp] Image base64 invalide.');
            return null;
        }

        try {
            $response = Http::withToken($this->token)
                ->timeout(30)
                ->attach('file', $binary, 'receipt.png', ['Content-Type' => 'image/png'])
                ->post("{$this->baseUrl}/media", [
                    'messaging_product' => 'whatsapp',
                    'type'              => 'image/png',
                ]);

            if (!$response->successful()) {
                Log::warning('[WhatsApp] Upload image échoué', [
                    'status'   => $response->status(),
                    'response' => $response->json(),
                ]);
                return null;
            }

            return $response->json('id');
        } catch (\Throwable $e) {
            Log::error('[WhatsApp] Erreur upload image : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Envoie un message image avec légende.
     */
    public function sendImage(string $phone, string $mediaId, string $caption = ''): bool
    {
        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'image',
            'image'             => [
                'id'      => $mediaId,
                'caption' => mb_substr($caption, 0, 1024), // limite WhatsApp
            ],
        ];

        return $this->post('/messages', $body);
    }

    /**
     * Envoie un message texte simple.
     */
    public function sendText(string $phone, string $text): bool
    {
        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'text',
            'text'              => [
                'body'        => mb_substr($text, 0, 4096),
                'preview_url' => false,
            ],
        ];

        return $this->post('/messages', $body);
    }

    /**
     * Envoie un document (PDF) avec nom de fichier.
     */
    public function sendDocument(string $phone, string $mediaId, string $filename, string $caption = ''): bool
    {
        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'document',
            'document'          => [
                'id'       => $mediaId,
                'filename' => $filename,
                'caption'  => $caption,
            ],
        ];

        return $this->post('/messages', $body);
    }

    // ─── Méthode principale : essai image → fallback texte ───────────────────

    /**
     * Envoie le reçu complet : image si possible, texte sinon.
     * Retourne un tableau ['success', 'method', 'error']
     */
    public function sendReceipt(string $rawPhone, string $base64Image, string $textMessage): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'method' => null, 'error' => 'api_not_configured'];
        }

        $phone = $this->formatPhone($rawPhone);

        if (empty($phone)) {
            return ['success' => false, 'method' => null, 'error' => 'invalid_phone'];
        }

        // ── Tentative 1 : image + légende courte
        if (!empty($base64Image)) {
            $mediaId = $this->uploadImage($base64Image);

            if ($mediaId) {
                // Légende = première ligne du texte (nom atelier + statut)
                $caption = collect(explode("\n", $textMessage))->take(2)->join(' — ');
                $sent = $this->sendImage($phone, $mediaId, $caption);

                if ($sent) {
                    return ['success' => true, 'method' => 'image'];
                }
            }
        }

        // ── Tentative 2 : texte seul
        $sent = $this->sendText($phone, $textMessage);

        if ($sent) {
            return ['success' => true, 'method' => 'text'];
        }

        return ['success' => false, 'method' => null, 'error' => 'send_failed'];
    }

    // ─── Requête HTTP interne ─────────────────────────────────────────────────

    private function post(string $path, array $body): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(20)
                ->post($this->baseUrl . $path, $body);

            if (!$response->successful()) {
                Log::warning('[WhatsApp] POST ' . $path . ' échoué', [
                    'status'   => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[WhatsApp] Erreur réseau : ' . $e->getMessage());
            return false;
        }
    }
}
