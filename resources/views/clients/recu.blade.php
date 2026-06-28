@extends('layouts.app')
@section('title', 'Reçu — ' . $client->prenom . ' ' . $client->nom)
@section('page-title', 'Reçu client')

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .topbar, .sidebar, .main-content > .d-flex { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    body { background: #fff !important; }
}
#ticketWrapper {
    width: 300px;
    background: #fff;
    font-family: 'Courier New', Courier, monospace;
    padding: 20px 16px;
    font-size: 12.5px;
    line-height: 1.7;
    color: #111;
    box-shadow: 0 2px 16px rgba(0,0,0,.12);
    border-radius: 4px;
    margin: 0 auto;
}
.rt-title  { text-align:center; font-size:15px; font-weight:900; text-transform:uppercase; letter-spacing:1px }
.rt-sub    { text-align:center; font-size:10px; color:#555; margin-top:1px }
.rt-div    { border-top:1px dashed #888; margin:10px 0 }
.rt-label  { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px }
.rt-row    { display:flex; justify-content:space-between; margin-bottom:3px; font-size:11px }
.rt-row b  { font-weight:700 }
.rt-badge  { display:inline-block; background:#111; color:#fff; padding:4px 16px; font-size:9px; font-weight:900; letter-spacing:1px; text-transform:uppercase }
.rt-box    { border:2px solid #111; padding:10px; text-align:center; margin:10px 0; background:#f8f8f8 }
.rt-box-label { font-size:10px; text-transform:uppercase; font-weight:700; letter-spacing:.5px }
.rt-box-val   { font-size:20px; font-weight:900; margin-top:2px }
.rt-footer { text-align:center; font-size:11px; color:#666; margin-top:4px }
</style>
@endpush

@section('content')

@php
    $atelierNom  = $atelier?->nom ?? 'ATELIKO';
    $resteAPayer = max(0, $montantTotal - $montantPaye);
    $solde       = $montantTotal > 0 && $resteAPayer <= 0;
    $reference   = 'CLI-' . strtoupper(substr($client->id, 0, 8));
    $dateImp     = now()->format('d/m/Y H:i');
    $beneficiaire = trim(($client->prenom ?? '') . ' ' . ($client->nom ?? ''));
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
        📥 Télécharger l'image
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary px-4">
        🖨️ Imprimer
    </button>
    <a href="{{ route('clients.show', $client->id) }}" class="btn btn-outline-secondary px-4">
        ← Retour
    </a>
</div>

{{-- Ticket thermique --}}
<div id="ticketWrapper">

    <div class="rt-title">{{ $atelierNom }}</div>
    <div class="rt-sub">Atelier de couture</div>

    <div style="text-align:center;margin:10px 0">
        <span class="rt-badge">{{ $solde ? 'SOLDÉ' : 'REÇU CLIENT' }}</span>
    </div>

    <div class="rt-div"></div>
    <div class="rt-label">Détails du ticket</div>

    <div class="rt-row"><span>Référence</span><b>{{ $reference }}</b></div>
    <div class="rt-row"><span>Date</span><b>{{ $dateImp }}</b></div>
    <div class="rt-row"><span>Client</span><b>{{ $beneficiaire }}</b></div>
    <div class="rt-row"><span>Contact</span><b>{{ $client->contact ?: '—' }}</b></div>
    <div class="rt-row"><span>Nb commandes</span><b>{{ $client->mesures->count() }}</b></div>

    <div class="rt-div"></div>

    <div class="rt-row"><span>Total commandes</span><b>{{ number_format($montantTotal,0,',',' ') }} FCFA</b></div>
    <div class="rt-row"><span>Avance payée</span><b style="color:#198754">{{ number_format($montantPaye,0,',',' ') }} FCFA</b></div>
    <div class="rt-row" style="font-weight:700">
        <span>Reste à payer</span>
        <b style="color:{{ $resteAPayer > 0 ? '#dc3545' : '#198754' }}">
            {{ number_format($resteAPayer,0,',',' ') }} FCFA
        </b>
    </div>

    <div class="rt-box">
        <div class="rt-box-label">{{ $solde ? 'Compte soldé' : 'Total payé' }}</div>
        <div class="rt-box-val">{{ number_format($montantPaye,0,',',' ') }} FCFA</div>
    </div>

    <div class="rt-div"></div>
    <div class="rt-label">Vérification</div>
    <div class="rt-sub" style="margin-bottom:8px">Scannez pour vérifier ce reçu</div>
    <div id="qrCode" style="display:flex;justify-content:center;margin:6px 0"></div>

    <div class="rt-div"></div>
    <div class="rt-footer" style="font-weight:900;font-size:12px">Merci pour votre confiance.</div>
    <div class="rt-footer">Conservez ce reçu comme preuve.</div>
    <div class="rt-footer">{{ $atelierNom }}</div>

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

    function fmt(v) { return Number(v||0).toLocaleString('fr-FR') + ' FCFA'; }

    var qrText = [
        'TICKET PAIEMENT',
        'Atelier: '      + atelierNom,
        'Reference: '    + reference,
        'Type: Reçu client',
        'Beneficiaire: ' + beneficiaire,
        'Montant: '      + montantPaye + ' FCFA',
        'Date: '         + dateImp,
        'Contact: '      + contact,
    ].join('\n');

    var imgDataUrl = null;
    var imgFile    = null;

    // ── QR synchrone (qrcode-generator)
    (function() {
        var qrEl = document.getElementById('qrCode');
        if (qrEl && window.qrcode) {
            try {
                var qr = qrcode(0, 'M');
                qr.addData(qrText);
                qr.make();
                var dataUrl = qr.createDataURL(3, 4);
                qrEl.innerHTML = '<img src="' + dataUrl + '" width="90" height="90" style="image-rendering:pixelated;display:block">';
            } catch(e) { console.warn('[QR]', e); }
        }
    })();

    // ── Capture html2canvas
    setTimeout(async function() {
        if (!window.html2canvas) return;
        try {
            var canvas = await html2canvas(document.getElementById('ticketWrapper'), {
                scale: 2, backgroundColor: '#ffffff',
                useCORS: true, allowTaint: true, logging: false
            });
            imgDataUrl = canvas.toDataURL('image/png');
            var blob   = await (await fetch(imgDataUrl)).blob();
            imgFile    = new File([blob], 'recu-' + reference + '.png', { type: 'image/png' });
            var dl = document.getElementById('btnDownload');
            dl.href = imgDataUrl;
            dl.classList.remove('d-none');
        } catch(e) { console.warn('[ATELIKO] html2canvas:', e); }
    }, 300);

    // ── Bouton WhatsApp
    document.getElementById('btnWhatsApp').addEventListener('click', async function() {
        var waText = [
            '*' + atelierNom + '*',
            'Reçu client',
            'Référence : '     + reference,
            'Date : '          + dateImp,
            'Client : '        + beneficiaire,
            '',
            'Total dû : '      + fmt(montantTotal),
            'Payé : '          + fmt(montantPaye),
            'Reste à payer : ' + fmt(resteAPayer),
            '',
            'Merci pour votre confiance chez ' + atelierNom + ' !'
        ].join('\n');

        var phone = contact.replace(/[^\d+]/g, '');
        if (phone.charAt(0) === '+') phone = phone.slice(1);
        if (phone.length === 8) phone = '223' + phone;
        var waUrl = phone ? 'https://wa.me/' + phone + '?text=' + encodeURIComponent(waText) : null;

        if (imgFile && navigator.canShare && navigator.canShare({ files: [imgFile] })) {
            try { await navigator.share({ files: [imgFile], text: waText, title: 'Reçu ATELIKO' }); return; } catch (_) {}
        }
        if (waUrl) {
            window.open(waUrl, '_blank');
        } else {
            Swal.fire({ icon: 'warning', title: 'Pas de contact', text: 'Ce client n\'a pas de numéro enregistré.', timer: 2500, showConfirmButton: false });
        }
    });
})();
</script>
@endpush
