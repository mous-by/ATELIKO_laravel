@extends('layouts.app')

@section('title', 'Affectations')
@section('page-title', 'Gestion des affectations')

@push('styles')
<style>
.aff-page{min-height:calc(100vh - 150px);margin:-4px -2px 0;color:#24313d}
.aff-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;padding:24px;border-radius:8px;background:linear-gradient(135deg,rgba(19,121,104,.94),rgba(39,68,117,.92)),url('{{ asset('assets/images/atelier.jpg') }}') center/cover;color:#fff;box-shadow:0 18px 42px rgba(25,53,83,.18)}
.aff-hero h1{margin:0;font-size:clamp(1.8rem,4vw,3rem);font-weight:800;letter-spacing:0}.aff-hero p{max-width:760px;margin:8px 0 0;color:rgba(255,255,255,.88)}
.aff-hero-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}.aff-kicker{display:block;margin-bottom:4px;color:#20c997;font-size:.76rem;font-weight:800;text-transform:uppercase;letter-spacing:0}
.aff-stats{display:grid;grid-template-columns:repeat(4,minmax(145px,1fr));gap:14px;margin:18px 0}.aff-stat{padding:18px;border-radius:8px;background:#fff;border:1px solid #e4eaf0;box-shadow:0 8px 24px rgba(30,53,78,.06)}
.aff-stat span{display:block;color:#64717d;font-size:.88rem}.aff-stat strong{display:block;margin-top:6px;font-size:2rem;line-height:1;color:#24313d}.aff-stat.alert strong{color:#c94d3f}
.aff-panel{background:#fff;border:1px solid #e4eaf0;border-radius:8px;box-shadow:0 10px 30px rgba(35,52,78,.07);padding:20px;margin-bottom:18px}
.aff-filter-row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}.aff-filter-title{font-weight:800;margin-right:4px;color:#37414a}
.aff-chip{display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:6px 12px;border-radius:999px;border:1px solid #dce5eb;background:#fff;color:#4d5964;font-weight:700;font-size:.86rem}
.aff-chip:hover{color:#14796a;border-color:#14796a}.aff-chip.active{background:#14796a;border-color:#14796a;color:#fff}.aff-chip.danger.active{background:#c94d3f;border-color:#c94d3f}
.aff-list{display:grid;gap:12px}.aff-card{display:grid;grid-template-columns:minmax(280px,1.3fr) minmax(150px,.55fr) minmax(180px,.55fr) minmax(150px,auto);gap:16px;align-items:center;padding:16px;border:1px solid #e0e7ee;border-left:5px solid #14796a;border-radius:8px;background:#fff}
.aff-card.late{border-left-color:#c94d3f;background:#fffafa}.aff-main{display:flex;gap:12px;align-items:center;min-width:0}.aff-avatar{width:46px;height:46px;flex:0 0 46px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#e6f4f0;color:#14796a;font-weight:900;text-transform:uppercase}
.aff-main h3{margin:0 0 5px;font-size:1rem;font-weight:800;overflow-wrap:anywhere}.aff-main p{display:flex;flex-wrap:wrap;gap:8px 14px;margin:0;color:#687681;font-size:.86rem}.aff-main p span{display:inline-flex;gap:5px;align-items:center}
.aff-status-area,.aff-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center}.aff-actions{justify-content:flex-end}.aff-price{font-weight:900;color:#24313d}
.aff-progress .progress{height:8px;border-radius:999px;margin-bottom:6px}.aff-progress small{color:#687681}
.aff-status{display:inline-flex;align-items:center;justify-content:center;min-height:28px;padding:4px 10px;border-radius:999px;font-size:.78rem;font-weight:800;white-space:nowrap}.aff-wait{background:#fff0bc;color:#755500}.aff-run{background:#dff7fb;color:#075f70}.aff-done{background:#ddf5e8;color:#17633d}.aff-valid{background:#e7eefc;color:#274475}.aff-cancel,.aff-late{background:#fde4df;color:#8d2d24}.aff-muted{background:#eef1f4;color:#4d5964}
.aff-empty{min-height:280px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;text-align:center;color:#6b7782;border:1px dashed #cfd9e1;border-radius:8px;background:#fbfcfd;padding:28px}.aff-empty i{font-size:2.4rem;color:#14796a}.aff-empty strong{color:#2c3742;font-size:1.05rem}
html.dark-theme .aff-stat,html.dark-theme .aff-panel,html.dark-theme .aff-card{background:#242632;border-color:#3a3d50;color:#e4e5e6}html.dark-theme .aff-stat strong,html.dark-theme .aff-price,html.dark-theme .aff-main h3,html.dark-theme .aff-empty strong{color:#e4e5e6}html.dark-theme .aff-chip{background:#242632;border-color:#3a3d50;color:#ced4da}html.dark-theme .aff-empty{background:#242632;border-color:#3a3d50}
@media(max-width:1200px){.aff-card{grid-template-columns:1fr}.aff-actions{justify-content:flex-start}}@media(max-width:768px){.aff-hero{flex-direction:column;padding:18px}.aff-hero-actions{justify-content:flex-start;width:100%}.aff-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.aff-panel{padding:16px}.aff-page{margin:0}}@media(max-width:480px){.aff-stats{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $statutLabels = [
        '' => 'Tous',
        'EN_ATTENTE' => 'En attente',
        'EN_COURS' => 'En cours',
        'TERMINE' => 'Habit prêt',
        'VALIDE' => 'Récupéré',
        'ANNULE' => 'Annulé',
        'EN_RETARD' => 'En retard',
    ];
    $statusClasses = [
        'EN_ATTENTE' => 'aff-wait',
        'EN_COURS' => 'aff-run',
        'TERMINE' => 'aff-done',
        'VALIDE' => 'aff-valid',
        'ANNULE' => 'aff-cancel',
    ];
    $progress = ['EN_ATTENTE' => 10, 'EN_COURS' => 50, 'TERMINE' => 90, 'VALIDE' => 100, 'ANNULE' => 0];
    $progressColor = ['EN_ATTENTE' => 'bg-warning', 'EN_COURS' => 'bg-info', 'TERMINE' => 'bg-success', 'VALIDE' => 'bg-primary', 'ANNULE' => 'bg-danger'];
    $curStatut = request('statut', '');
    $curTailleur = request('tailleur_id', '');
@endphp

<div class="aff-page">
    <div class="aff-hero">
        <div>
            <span class="aff-kicker">Atelier</span>
            <h1>Affectations</h1>
            <p>Suivez les commandes, les retards et les tailleurs en un seul écran, avec des actions simples pour avancer le travail.</p>
        </div>
        <div class="aff-hero-actions">
            <a href="{{ route('affectations.index') }}" class="btn btn-light"><i class="bx bx-refresh me-1"></i>Actualiser</a>
            @if(!Auth::user()->isTailleur())
            <a href="{{ route('affectations.create') }}" class="btn btn-warning text-dark fw-bold">
                <i class="bx bx-plus-circle me-1"></i>Nouvelle affectation
            </a>
            @endif
        </div>
    </div>

    <div class="aff-stats">
        <div class="aff-stat"><span>Total</span><strong>{{ $stats['total'] ?? $affectations->total() }}</strong></div>
        <div class="aff-stat"><span>En attente</span><strong>{{ $stats['enAttente'] ?? 0 }}</strong></div>
        <div class="aff-stat"><span>En couture</span><strong>{{ $stats['enCours'] ?? 0 }}</strong></div>
        <div class="aff-stat alert"><span>En retard</span><strong>{{ $stats['retard'] ?? 0 }}</strong></div>
    </div>

    <div class="aff-panel">
        <div class="aff-filter-row mb-3">
            <span class="aff-filter-title">Statut</span>
            @foreach(array_merge([''], $statuts) as $s)
                <a href="{{ route('affectations.index', array_filter(['statut' => $s, 'tailleur_id' => $curTailleur])) }}"
                   class="aff-chip {{ $curStatut === $s ? 'active' : '' }} {{ $s === 'EN_RETARD' ? 'danger' : '' }}">
                    @if($s === 'EN_RETARD')<i class="bx bx-error-circle"></i>@endif
                    {{ $statutLabels[$s] ?? str_replace('_', ' ', $s) }}
                </a>
            @endforeach
        </div>

        @if($tailleurs->isNotEmpty())
        <div class="aff-filter-row">
            <span class="aff-filter-title">Tailleur</span>
            <a href="{{ route('affectations.index', array_filter(['statut' => $curStatut])) }}" class="aff-chip {{ !$curTailleur ? 'active' : '' }}">Tous</a>
            @foreach($tailleurs as $t)
                <a href="{{ route('affectations.index', array_filter(['statut' => $curStatut, 'tailleur_id' => $t->id])) }}"
                   class="aff-chip {{ $curTailleur === $t->id ? 'active' : '' }}">
                    {{ $t->prenom }} {{ $t->nom }}
                </a>
            @endforeach
        </div>
        @endif
    </div>

    <div class="aff-panel">
        @if($affectations->isEmpty())
            <div class="aff-empty">
                <i class="bx bx-folder-open"></i>
                <strong>Aucune affectation trouvée</strong>
                <span>Changez les filtres ou créez une nouvelle affectation.</span>
                @if(!Auth::user()->isTailleur())
                <a href="{{ route('affectations.create') }}" class="btn btn-primary mt-2">Créer la première</a>
                @endif
            </div>
        @else
            <div class="aff-list">
                @foreach($affectations as $aff)
                    @php
                        $isLate = $aff->date_echeance && $aff->date_echeance->lt(now()) && !in_array($aff->statut, ['TERMINE', 'VALIDE', 'ANNULE']);
                        $pct = $progress[$aff->statut] ?? 0;
                        $initials = Str::upper(Str::substr($aff->client?->prenom ?? '?', 0, 1).Str::substr($aff->client?->nom ?? '', 0, 1));
                    @endphp
                    <article class="aff-card {{ $isLate ? 'late' : '' }}">
                        <div class="aff-main">
                            <div class="aff-avatar">{{ $initials }}</div>
                            <div>
                                <h3>{{ $aff->client?->prenom }} {{ $aff->client?->nom }}</h3>
                                <p>
                                    <span><i class="bx bx-phone"></i>{{ $aff->client?->contact ?: 'Sans contact' }}</span>
                                    <span><i class="bx bx-closet"></i>{{ $aff->mesure?->modele_nom ?: ($aff->mesure?->modeleReference?->nom ?: ($aff->mesure?->type_vetement ?? 'Modèle')) }}</span>
                                    <span><i class="bx bx-user"></i>{{ $aff->tailleur?->prenom }} {{ $aff->tailleur?->nom }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="aff-status-area">
                            <span class="aff-status {{ $statusClasses[$aff->statut] ?? 'aff-muted' }}">{{ $statutLabels[$aff->statut] ?? $aff->statut }}</span>
                            @if($isLate)<span class="aff-status aff-late">En retard</span>@endif
                            <span class="aff-price">{{ $aff->prix_tailleur ? number_format($aff->prix_tailleur, 0, ',', ' ') . ' FCFA' : 'Prix non défini' }}</span>
                        </div>

                        <div class="aff-progress">
                            <div class="progress">
                                <div class="progress-bar {{ $progressColor[$aff->statut] ?? 'bg-secondary' }}" style="width: {{ $pct }}%"></div>
                            </div>
                            <small>Date prévue : {{ $aff->date_echeance ? $aff->date_echeance->format('d/m/Y') : 'Non définie' }}</small>
                        </div>

                        <div class="aff-actions">
                            @if($aff->statut === 'EN_ATTENTE')
                                <form action="{{ route('affectations.statut', $aff->id) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="statut" value="EN_COURS"><button class="btn btn-sm btn-primary"><i class="bx bx-play me-1"></i>Commencer couture</button></form>
                            @endif
                            @if($aff->statut === 'EN_COURS')
                                <form action="{{ route('affectations.statut', $aff->id) }}" method="POST" class="js-affectation-ready">@csrf @method('PATCH')<input type="hidden" name="statut" value="TERMINE"><button class="btn btn-sm btn-success"><i class="bx bx-check me-1"></i>Habit prêt</button></form>
                            @endif
                            @if(!in_array($aff->statut, ['VALIDE', 'ANNULE']) && !Auth::user()->isTailleur())
                                <form action="{{ route('affectations.destroy', $aff->id) }}" method="POST" data-confirm="Supprimer cette affectation ?" data-confirm-text="Cette action est irréversible." data-confirm-btn="Supprimer">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash me-1"></i>Supprimer</button></form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="d-flex justify-content-center pt-3">
                {{ $affectations->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-affectation-ready').forEach(function(form) {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        var originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Terminé...';
        }

        try {
            var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            var resp = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                }
            });
            var json = await resp.json().catch(function() { return {}; });
            if (!resp.ok) throw new Error(json.message || 'Impossible de terminer cette affectation.');

            if (json.receipt && window.showReceiptPopup) {
                await window.showReceiptPopup(json.receipt);
            }
            window.location.reload();
        } catch (err) {
            if (window.swalError) {
                window.swalError(err.message || 'Une erreur est survenue.');
            } else {
                alert(err.message || 'Une erreur est survenue.');
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    });
});
</script>
@endpush
