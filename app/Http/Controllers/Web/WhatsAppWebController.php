<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppWebController extends Controller
{
    public function __construct(private WhatsAppService $whatsapp) {}

    /**
     * Reçoit l'image du reçu (base64) depuis le navigateur,
     * l'upload sur Meta et l'envoie via WhatsApp Business API.
     *
     * POST /whatsapp/send-receipt
     * Body JSON: { phone, image (base64 data URL), text }
     */
    public function sendReceipt(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'image' => 'nullable|string',   // base64 data URL (peut être absent = texte seul)
            'text'  => 'required|string|max:5000',
        ]);

        $result = $this->whatsapp->sendReceipt(
            $request->phone,
            $request->image ?? '',
            $request->text,
        );

        // On retourne toujours 200 pour que le JS puisse lire result.success
        return response()->json($result);
    }

    /**
     * Indique au front si l'API WhatsApp est configurée sur ce serveur.
     * GET /whatsapp/status
     */
    public function status()
    {
        return response()->json([
            'configured' => $this->whatsapp->isConfigured(),
        ]);
    }
}
