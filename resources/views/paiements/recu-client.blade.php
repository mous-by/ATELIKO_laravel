@extends('layouts.app')
@section('title', 'Reçu — ' . $client->prenom . ' ' . $client->nom)
@section('page-title', 'Reçu client')

@push('styles')
<style>
@media print {
    .no-print { display:none !important; }
    .topbar, .sidebar, .main-content > .d-flex { display:none !important; }
    .main-content { margin:0 !important; padding:0 !important; }
    body { background:#fff !important; }
}
#ticketWrapper {
    width: 320px;
    background: #fff;
    font-family: Helvetica, Arial, sans-serif;
    padding: 18px 16px;
    font-size: 12px;
    line-height: 1.6;
    color: #111;
    box-shadow: 0 4px 24px rgba(0,0,0,.13);
    border-radius: 10px;
    border: 1px solid #d1d5db;
    margin: 0 auto;
}
.rt-brand     { text-align:center; font-size:16px; font-weight:900; text-transform:uppercase; color:#141414; letter-spacing:.5px }
.rt-sub       { text-align:center; font-size:11px; color:#666; margin-top:1px }
.rt-badge-wrap{ display:flex; justify-content:center; margin:10px 0 }
.rt-badge     { display:inline-block; background:#1a1a1a; color:#fff; padding:5px 18px; font-size:8px; font-weight:900; letter-spacing:1.5px; text-transform:uppercase; border-radius:3px }
.rt-div       { text-align:center; color:#bbb; margin:8px 0; font-size:10px; letter-spacing:2px }
.rt-section   { font-size:10px; color:#888; font-weight:900; margin-bottom:4px; margin-top:6px; text-transform:uppercase; letter-spacing:.8px }
.rt-row       { display:flex; justify-content:space-between; gap:8px; margin-bottom:4px; font-size:11px }
.rt-label     { font-size:11px; color:#555; flex:1.2 }
.rt-value     { font-size:11px; color:#111; flex:1; text-align:right; font-weight:600 }
.rt-box       { border:1.5px solid #1a1a1a; padding:10px 8px; text-align:center; margin:10px 0; background:#f7f7f7; border-radius:4px }
.rt-box-label { font-size:9px; text-transform:uppercase; font-weight:900; color:#666; letter-spacing:.8px; margin-bottom:3px }
.rt-box-val   { font-size:22px; font-weight:900; color:#111; letter-spacing:.5px }
.rt-qr-block  { border:1px solid #ccc; background:#fafafa; padding:8px; text-align:center; margin:6px 0 10px; border-radius:4px }
.rt-thanks    { text-align:center; font-weight:900; color:#111; font-size:12px; margin-top:4px }
.rt-footer    { text-align:center; font-size:10px; color:#888; margin-top:2px }

/* Tableau commandes */
.rt-table     { width:100%; border-collapse:collapse; font-size:10.5px; margin:4px 0 }
.rt-table th  { background:#f0f0f0; font-weight:900; padding:4px 5px; text-align:left; font-size:10px; color:#444 }
.rt-table td  { padding:4px 5px; border-bottom:1px solid #f0f0f0; vertical-align:top }
.rt-table tr:last-child td { border-bottom:none }
.rt-table .num{ text-align:right }

/* Historique paiements */
.rt-pay-row   { display:flex; justify-content:space-between; align-items:center; font-size:10.5px; padding:3px 0; border-bottom:1px dotted #eee }
.rt-pay-row:last-child { border-bottom:none }
.rt-pay-date  { color:#666; font-size:10px }
.rt-pay-moyen { font-size:9px; color:#0d6efd; background:#e8f1ff; border-radius:3px; padding:1px 5px; margin-left:4px }
.rt-pay-amt   { font-weight:900; color:#198754 }
</style>
@endpush

@section('content')

@php
    $atelierNom     = $client->atelier?->nom ?? 'ATELIKO';
    $atelierAdresse = $client->atelier?->adresse ?? '';
    $atelierTel     = $client->atelier?->telephone ?? '';
    $resteAPayer    = max(0, $montantTotal - $montantPaye);
    $solde          = $montantTotal > 0 && $resteAPayer <= 0;
    $reference      = 'CLI-' . strtoupper(substr($client->id, 0, 8));
    $dateImp        = now()->format('d/m/Y H:i');
    $beneficiaire   = trim(($client->prenom ?? '') . ' ' . ($client->nom ?? ''));
    $statut         = $solde ? 'COMPTE SOLDÉ' : 'REÇU CLIENT';
    $mesures        = $client->mesures ?? collect();
    $paiementsHist  = $client->paiements->where('type_paiement', 'CLIENT')->sortByDesc('date_paiement') ?? collect();
@endphp

{{-- Actions --}}
<div class="d-flex gap-2 mb-4 no-print justify-content-center flex-wrap">
    <button id="btnWhatsApp" class="btn btn-success px-4 fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="me-2" viewBox="0 0 16 16">
            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
        </svg>
        Envoyer sur WhatsApp
    </button>
    <a id="btnDownload" href="#" download="recu-{{ $reference }}.png"
       class="btn btn-outline-primary px-4 fw-bold d-none">
        📥 Télécharger le reçu
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary px-4">🖨️ Imprimer</button>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4">← Retour</a>
</div>

{{-- Ticket --}}
<div id="ticketWrapper">

    <div class="rt-brand">{{ $atelierNom }}</div>
    @if($atelierAdresse)
        <div class="rt-sub">{{ $atelierAdresse }}</div>
    @endif
    @if($atelierTel)
        <div class="rt-sub">📞 {{ $atelierTel }}</div>
    @endif

    <div class="rt-badge-wrap">
        <span class="rt-badge">{{ $statut }}</span>
    </div>

    <div class="rt-div">· · · · · · · · · · · · · · · · · · · · ·</div>

    <div class="rt-section">Détails du ticket</div>
    <div class="rt-row"><span class="rt-label">Référence</span><span class="rt-value">{{ $reference }}</span></div>
    <div class="rt-row"><span class="rt-label">Date</span><span class="rt-value">{{ $dateImp }}</span></div>
    <div class="rt-row"><span class="rt-label">Client</span><span class="rt-value">{{ $beneficiaire }}</span></div>
    <div class="rt-row"><span class="rt-label">Contact</span><span class="rt-value">{{ $client->contact ?: '—' }}</span></div>
    <div class="rt-row"><span class="rt-label">Nb commandes</span><span class="rt-value">{{ $mesures->count() }}</span></div>

    @if($mesures->isNotEmpty())
    <div class="rt-div">· · · · · · · · · · · · · · · · · · · · ·</div>
    <div class="rt-section">Commandes</div>
    <table class="rt-table">
        <thead>
            <tr>
                <th style="width:52%">Modèle / Type</th>
                <th style="width:20%;text-align:center">Qté</th>
                <th style="width:28%;text-align:right">Prix</th>
            </tr>
        </thead>
        <tbody>
        @foreach($mesures as $m)
            <tr>
                <td>
                    @if($m->modele_nom)
                        <strong>{{ $m->modele_nom }}</strong><br>
                        <span style="color:#666">{{ $m->type_vetement ?? '' }}</span>
                    @else
                        {{ $m->type_vetement ?? 'Commande' }}
                    @endif
                    @if($m->sexe)
                        <span style="color:#888;font-size:9px"> ({{ $m->sexe }})</span>
                    @endif
                </td>
                <td class="num">1</td>
                <td class="num">{{ number_format($m->prix ?? 0, 0, ',', ' ') }} F</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

    <div class="rt-div">· · · · · · · · · · · · · · · · · · · · ·</div>

    <div class="rt-row"><span class="rt-label">Total des commandes</span><span class="rt-value">{{ number_format($montantTotal,0,',',' ') }} FCFA</span></div>
    <div class="rt-row"><span class="rt-label" style="color:#198754">Montant payé</span><span class="rt-value" style="color:#198754">{{ number_format($montantPaye,0,',',' ') }} FCFA</span></div>
    <div class="rt-row" style="font-weight:700">
        <span class="rt-label" style="color:{{ $resteAPayer > 0 ? '#dc3545' : '#198754' }}">Reste à payer</span>
        <span class="rt-value" style="color:{{ $resteAPayer > 0 ? '#dc3545' : '#198754' }}">
            {{ number_format($resteAPayer,0,',',' ') }} FCFA
        </span>
    </div>

    <div class="rt-box">
        <div class="rt-box-label">{{ $solde ? '✓ Compte soldé' : 'Montant encaissé' }}</div>
        <div class="rt-box-val">{{ number_format($montantPaye,0,',',' ') }} FCFA</div>
    </div>

    @if($paiementsHist->isNotEmpty())
    <div class="rt-div">· · · · · · · · · · · · · · · · · · · · ·</div>
    <div class="rt-section">Historique des paiements</div>
    @foreach($paiementsHist as $p)
        <div class="rt-pay-row">
            <span class="rt-pay-date">{{ $p->date_paiement?->format('d/m/Y') ?? '—' }}</span>
            <span>
                <span class="rt-pay-moyen">{{ $p->moyen ?? 'ESPECES' }}</span>
                @if($p->note)
                    <span style="color:#888;font-size:9px"> — {{ Str::limit($p->note, 20) }}</span>
                @endif
            </span>
            <span class="rt-pay-amt">{{ number_format($p->montant,0,',',' ') }} F</span>
        </div>
    @endforeach
    @endif

    <div class="rt-div">· · · · · · · · · · · · · · · · · · · · ·</div>
    <div class="rt-section">Vérification</div>
    <div class="rt-qr-block">
        <div id="qrCode" style="display:flex;justify-content:center;margin-bottom:4px"></div>
        <div class="rt-sub" style="margin-top:2px;font-size:9px">Scannez pour vérifier ce reçu</div>
    </div>

    <div class="rt-div">· · · · · · · · · · · · · · · · · · · · ·</div>
    <div class="rt-thanks">Merci pour votre confiance.</div>
    <div class="rt-footer">{{ $atelierNom }}</div>
    <div class="rt-footer" style="margin-top:4px;font-size:9px">Ce reçu a été généré électroniquement</div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    var atelierNom   = @json($atelierNom);
    var reference    = @json($reference);
    var beneficiaire = @json($beneficiaire);
    var contact      = @json($client->contact ?? '');
    var montantPaye  = {{ (int) $montantPaye }};
    var montantTotal = {{ (int) $montantTotal }};
    var resteAPayer  = {{ (int) $resteAPayer }};
    var dateImp      = @json($dateImp);
    var mesures      = @json($mesures->map(fn($m) => ['modeleNom' => $m->modele_nom, 'typeVetement' => $m->type_vetement, 'prix' => (int)($m->prix ?? 0)]));
    var paiementsHist = @json($paiementsHist->map(fn($p) => ['date' => $p->date_paiement?->format('d/m/Y'), 'montant' => (int)$p->montant, 'moyen' => $p->moyen])->values());

    function fmt(v) { return Number(v||0).toLocaleString('fr-FR') + ' FCFA'; }

    // Génération QR
    (function() {
        var qrEl = document.getElementById('qrCode');
        if (!qrEl || !window.qrcode) return;
        try {
            var lines = [
                'ATELIKO — Reçu Client',
                'Atelier: ' + atelierNom,
                'Réf: ' + reference,
                'Client: ' + beneficiaire,
                'Payé: ' + montantPaye + ' FCFA',
                'Reste: ' + resteAPayer + ' FCFA',
                'Date: ' + dateImp,
            ];
            var qr = qrcode(0, 'M');
            qr.addData(lines.join('\n'));
            qr.make();
            var dataUrl = qr.createDataURL(3, 4);
            qrEl.innerHTML = '<img src="' + dataUrl + '" width="90" height="90" style="image-rendering:pixelated;display:block">';
        } catch(e) { console.warn('[QR]', e); }
    })();

    var imgDataUrl = null;
    var imgFile    = null;

    async function captureTicket() {
        if (!window.html2canvas) return;
        try {
            var canvas = await html2canvas(document.getElementById('ticketWrapper'), {
                scale: 2.5, backgroundColor: '#ffffff',
                useCORS: true, allowTaint: true, logging: false
            });
            imgDataUrl = canvas.toDataURL('image/png');
            var blob   = await (await fetch(imgDataUrl)).blob();
            imgFile    = new File([blob], 'recu-' + reference + '.png', { type: 'image/png' });
            var dl = document.getElementById('btnDownload');
            if (dl) { dl.href = imgDataUrl; dl.classList.remove('d-none'); }
        } catch(e) { console.warn('[ATELIKO] html2canvas:', e); }
    }

    setTimeout(captureTicket, 400);

    document.getElementById('btnWhatsApp').addEventListener('click', async function() {
        if (!imgFile) await captureTicket();

        // Construire le texte WhatsApp avec détail des commandes
        var lines = [
            '*🏭 ' + atelierNom + '*',
            '─────────────────',
            '🧾 *Reçu Client*',
            'Réf : ' + reference,
            'Date : ' + dateImp,
            'Client : ' + beneficiaire,
        ];
        if (contact) lines.push('📞 ' + contact);
        lines.push('─────────────────');
        if (mesures.length > 0) {
            lines.push('*Commandes :*');
            mesures.forEach(function(m, i) {
                var label = m.modeleNom || m.typeVetement || 'Commande ' + (i+1);
                lines.push('  ' + (i+1) + '. ' + label + ' — ' + Number(m.prix||0).toLocaleString('fr-FR') + ' F');
            });
            lines.push('─────────────────');
        }
        lines.push('💰 Total : ' + fmt(montantTotal));
        lines.push('✅ Payé : ' + fmt(montantPaye));
        if (resteAPayer > 0) {
            lines.push('⚠️ Reste à payer : *' + fmt(resteAPayer) + '*');
        } else {
            lines.push('🎉 *Compte soldé !*');
        }
        lines.push('─────────────────');
        lines.push('Merci de votre confiance chez *' + atelierNom + '* !');

        var waText = lines.join('\n');

        if (typeof window.receiptSendWhatsApp === 'function') {
            await window.receiptSendWhatsApp({
                imgFile    : imgFile,
                imgDataUrl : imgDataUrl,
                waText     : waText,
                contact    : contact,
                reference  : reference,
                atelierNom : atelierNom,
            });
        }
    });
})();
</script>
@endpush
