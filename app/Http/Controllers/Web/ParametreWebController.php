<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atelier;
use App\Models\AbonnementAtelier;
use App\Models\AbonnementPlan;
use App\Models\Permission;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ParametreWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isSuperAdmin() || $user->isProprietaire(), 403);

        $section = $request->string('section')->toString() ?: 'ateliers';
        $allowed = ['ateliers', 'abonnement-tarifs', 'utilisateurs', 'assigner', 'liste'];
        if (!in_array($section, $allowed, true) || ($section === 'abonnement-tarifs' && !$user->isSuperAdmin())) {
            $section = 'ateliers';
        }

        $ateliers = $user->isSuperAdmin()
            ? Atelier::with(['abonnement.plan'])->withCount(['utilisateurs', 'clients'])->orderByDesc('date_creation')->get()
            : Atelier::with(['abonnement.plan'])->withCount(['utilisateurs', 'clients'])->whereKey($user->atelier_id)->get();

        $utilisateurs = Utilisateur::with(['atelier', 'permissions'])
            ->when(!$user->isSuperAdmin(), fn ($query) => $query
                ->where('atelier_id', $user->atelier_id)
                ->where('role', '!=', 'SUPERADMIN'))
            ->orderByDesc('created_at')->get();

        $permissions = Permission::orderBy('code')->get();
        $roles = $user->isSuperAdmin()
            ? ['PROPRIETAIRE', 'SECRETAIRE', 'TAILLEUR']
            : ['SECRETAIRE', 'TAILLEUR'];
        $subscriptionPlans = $user->isSuperAdmin() ? AbonnementPlan::orderBy('id')->get() : collect();

        return view('parametres.index', compact(
            'section', 'ateliers', 'utilisateurs', 'permissions', 'roles', 'user', 'subscriptionPlans'
        ));
    }

    public function storeAtelier(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $data = $request->validate([
            'nom'       => 'required|string|max:150',
            'adresse'   => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:150',
            'telephone' => 'nullable|string|max:30',
            'plan_code' => 'nullable|string|exists:abonnement_plan,code',
        ]);

        $planCode = $data['plan_code'] ?? null;
        unset($data['plan_code']);
        $data['id'] = Str::uuid();
        $data['date_creation'] = now();
        $atelier = Atelier::create($data);

        if ($planCode) {
            $plan = AbonnementPlan::where('code', $planCode)->first();
            if ($plan) {
                AbonnementAtelier::create([
                    'atelier_id'  => $atelier->id,
                    'plan_id'     => $plan->id,
                    'statut'      => 'ACTIVE',
                    'date_debut'  => now(),
                    'date_fin'    => now()->addMonths($plan->duree_mois),
                ]);
            }
        }

        return back()->with('success', 'Atelier ajouté avec succès' . ($planCode ? " avec le plan $planCode activé" : ''));
    }

    public function updateAtelier(Request $request, Atelier $atelier)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $data = $request->validate([
            'nom'       => 'required|string|max:150',
            'adresse'   => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:150',
            'telephone' => 'nullable|string|max:30',
            'plan_code' => 'nullable|string|exists:abonnement_plan,code',
        ]);

        $planCode = $data['plan_code'] ?? null;
        unset($data['plan_code']);
        $atelier->update($data);

        if ($planCode) {
            $plan = AbonnementPlan::where('code', $planCode)->first();
            if ($plan) {
                $abonnement = AbonnementAtelier::where('atelier_id', $atelier->id)->first();
                if (!$abonnement) {
                    AbonnementAtelier::create([
                        'atelier_id' => $atelier->id,
                        'plan_id'    => $plan->id,
                        'statut'     => 'ACTIVE',
                        'date_debut' => now(),
                        'date_fin'   => now()->addMonths($plan->duree_mois),
                    ]);
                } elseif ($abonnement->plan?->code !== $planCode) {
                    $abonnement->plan_id    = $plan->id;
                    $abonnement->statut     = 'ACTIVE';
                    $abonnement->date_debut = now();
                    $abonnement->date_fin   = now()->addMonths($plan->duree_mois);
                    $abonnement->save();
                }
            }
        }

        return back()->with('success', 'Atelier modifié avec succès' . ($planCode ? " — plan $planCode appliqué" : ''));
    }

    public function destroyAtelier(Atelier $atelier)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        abort_if($atelier->utilisateurs()->exists() || $atelier->clients()->exists(), 422, 'Atelier utilisé');
        $atelier->delete();
        return back()->with('success', 'Atelier supprimé');
    }

    public function storePlan(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $data = $request->validate(['code'=>'required|string|max:50|unique:abonnement_plan,code','libelle'=>'required|string|max:150','duree_mois'=>'required|integer|min:1','prix'=>'required|numeric|min:1','devise'=>'required|string|max:10']);
        $data['code'] = strtoupper($data['code']);
        $data['devise'] = strtoupper($data['devise']);
        $data['actif'] = $request->boolean('actif', true);
        AbonnementPlan::create($data);
        return back()->with('success', 'Plan créé avec succès');
    }

    public function updatePlan(Request $request, AbonnementPlan $plan)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $data = $request->validate(['libelle'=>'required|string|max:150','duree_mois'=>'required|integer|min:1','prix'=>'required|numeric|min:1','devise'=>'required|string|max:10']);
        $data['devise'] = strtoupper($data['devise']);
        $data['actif'] = $request->boolean('actif');
        $plan->update($data);
        return back()->with('success', 'Tarif '.$plan->code.' mis à jour');
    }

    public function destroyPlan(AbonnementPlan $plan)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        if (\DB::table('abonnement_atelier')->where('plan_id', $plan->id)->exists()) {
            $plan->update(['actif'=>false]);
            return back()->with('success', 'Plan désactivé, historique conservé');
        }
        $plan->delete();
        return back()->with('success', 'Plan supprimé');
    }
}
