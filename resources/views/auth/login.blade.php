<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ATELIKO - Connexion</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/ateliko-icon-32.png') }}?v=4">
    <link rel="shortcut icon" href="{{ asset('assets/images/ateliko-icon-32.png') }}?v=4">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/images/ateliko-icon-192.png') }}?v=4">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=4">
    <meta name="theme-color" content="#0d6efd">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/index.css') }}" rel="stylesheet">
    <style>
        /* Widget caméra */
        .cam-video-box{position:relative;background:#000;border-radius:8px;overflow:hidden;height:200px}
        .cam-video-box video,.cam-video-box img{width:100%;height:100%;object-fit:cover;display:block}
        .cam-shutter{position:absolute;inset:0;background:rgba(255,255,255,.6);pointer-events:none;opacity:0;transition:opacity .15s}
        .cam-shutter.flash{opacity:1}
    </style>
</head>
<body>
    @php
        $slides = [
            ['jupe0.jpg', 'Collection Jupe'], ['jupe1.jpg', 'Collection Jupe'],
            ['jupe2.jpg', 'Collection Jupe'], ['jupe4.jpg', 'Collection Jupe'],
            ['jupe5.jpg', 'Collection Jupe'], ['jupe6.jpg', 'Collection Jupe'],
            ['jupe7.jpg', 'Collection Jupe'], ['jupe8.jpg', 'Collection Jupe'],
            ['jupe9.jpg', 'Collection Jupe'], ['jupe10.jpg', 'Collection Jupe'],
            ['model1.png', 'Modèle Exclusive'], ['model2.png', 'Modèle Exclusive'],
            ['model3.jpg', 'Nouvelle Collection'], ['model4.jpg', 'Nouvelle Collection'],
            ['model5.jpg', 'Nouvelle Collection'],
        ];
    @endphp
    <div class="split-screen">
        <div class="left-half">
            <div class="login-container">
                <div class="mb-4 text-center">
                    <img src="{{ asset('assets/images/logo_ateliko.png') }}" style="width:40%;max-width:250px;height:auto;object-fit:contain" alt="Logo ATELIKO">
                    <h3 class="logo-text">ATELIKO</h3>
                </div>
                <div class="login-separater text-center mb-4"><span>CONNEXION AVEC NUMÉRO DE TÉLÉPHONE</span></div>

                @if(session('success'))
                    <div class="alert alert-success py-2 mb-3"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3"><i class="bx bx-error-circle me-1"></i>{{ $errors->first() }}</div>
                @endif

                <div class="form-body">
                    <form class="row g-3" action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="col-12">
                            <label for="inputPhone" class="form-label">Numéro de téléphone</label>
                            <input id="inputPhone" type="tel" name="telephone" class="form-control @error('telephone') is-invalid @enderror" placeholder="Ex : 74 74 56 69" inputmode="tel" autocomplete="tel" value="{{ old('telephone') }}" required autofocus>
                        </div>
                        <div class="col-12">
                            <label for="inputChoosePassword" class="form-label">Mot de passe</label>
                            <div class="input-group" id="show_hide_password">
                                <input id="inputChoosePassword" type="password" name="password" class="form-control border-end-0" placeholder="Mot de passe" autocomplete="current-password" required>
                                <button type="button" class="input-group-text bg-transparent" id="togglePassword" style="border:1px solid #ced4da;border-left:none" aria-label="Afficher le mot de passe"><i class="bx bx-hide"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
                            </div>
                        </div>
                        <div class="col-md-6 text-end"><span class="text-primary">Mot de passe oublié?</span></div>
                        <div class="col-12">
                            <div class="d-grid"><button type="submit" class="btn btn-primary" id="loginButton"><i class="bx bxs-lock-open me-2"></i><span>Se connecter</span><span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span></button></div>
                        </div>
                    </form>
                </div>
                <div class="mobile-carousel">
                    <div class="mobile-carousel-item" id="mobileCarouselItem"><div class="mobile-carousel-caption"><h6 class="mb-0" id="mobileSlideTitle"></h6></div></div>
                    <div class="mobile-carousel-controls">
                        <button class="mobile-carousel-btn slide-prev" type="button"><i class="bx bx-chevron-left"></i></button>
                        <span id="mobileImageCounter" class="align-self-center"></span>
                        <button class="mobile-carousel-btn slide-next" type="button"><i class="bx bx-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-half" id="backgroundImage">
            <div class="overlay"></div>
            <div class="carousel-controls">
                <button class="carousel-btn slide-prev" type="button"><i class="bx bx-chevron-left"></i></button>
                <button class="carousel-btn slide-next" type="button"><i class="bx bx-chevron-right"></i></button>
            </div>
            <div class="image-counter" id="imageCounter"></div>
        </div>
    </div>

    {{-- ===== MODAL ABONNEMENT EXPIRÉ (PROPRIÉTAIRE) ===== --}}
    @if(session('login_blocked') === 'proprietaire')
    @php
        $loginPlans     = session('login_blocked_plans', []);
        $loginPending   = session('login_blocked_pending', false);
        $loginFailed    = session('login_blocked_failed');
    @endphp
    <div class="modal fade" id="modalLoginBlocked"
         data-bs-backdrop="static" data-bs-keyboard="false"
         tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bx bx-lock-alt me-2"></i>Abonnement expiré — Accès bloqué
                    </h5>
                </div>
                <div class="modal-body">

                    @if($loginPending)
                    {{-- Paiement en attente --}}
                    <div class="text-center py-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 p-4 mb-3" style="width:80px;height:80px">
                            <i class="bx bx-time-five text-warning" style="font-size:2.5rem"></i>
                        </span>
                        <h5 class="fw-bold mb-2">Paiement en cours de validation</h5>
                        <p class="text-muted">Votre preuve de paiement a été reçue et est en cours de vérification par l'administrateur.<br>
                        Vous pourrez vous connecter dès validation de votre paiement.</p>
                    </div>

                    @else
                    {{-- Formulaire de paiement --}}
                    @if($loginFailed)
                    <div class="alert alert-danger py-2 mb-3">
                        <i class="bx bx-x-circle me-1"></i><strong>Paiement précédent rejeté.</strong>
                        Motif : {{ $loginFailed }}. Veuillez soumettre un nouveau paiement.
                    </div>
                    @else
                    <div class="alert alert-danger py-2 mb-3">
                        <i class="bx bx-error-circle me-1"></i>
                        L'abonnement de votre atelier est expiré. Soumettez une preuve de paiement pour rétablir l'accès.
                    </div>
                    @endif

                    <p class="text-muted small mb-3">
                        Effectuez votre paiement sur l'un des numéros ci-dessous, puis envoyez votre preuve (capture ou photo).
                        Votre accès sera rétabli après validation.
                    </p>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <div class="border rounded p-2 text-center bg-light">
                                <div class="fw-bold small">Orange Money / Wave</div>
                                <div class="text-primary fw-bold">74 74 56 69</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="border rounded p-2 text-center bg-light">
                                <div class="fw-bold small">MobiCash</div>
                                <div class="text-primary fw-bold">67 20 57 36</div>
                            </div>
                        </div>
                    </div>

                    <form id="paymentLoginForm" action="{{ route('abonnement.paiement.blocked') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Plan <span class="text-danger">*</span></label>
                                <select name="plan_code" class="form-select" required>
                                    <option value="">-- Choisir un plan --</option>
                                    @foreach($loginPlans as $plan)
                                    <option value="{{ $plan['code'] }}">{{ $plan['libelle'] }} — {{ number_format($plan['prix'], 0, ',', ' ') }} {{ $plan['devise'] }}/{{ $plan['duree_mois'] }}mois</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Mode de paiement <span class="text-danger">*</span></label>
                                <select name="mode_paiement" class="form-select" id="loginModePaiement" required>
                                    <option value="">-- Choisir --</option>
                                    <option value="ORANGE_MONEY">Orange Money</option>
                                    <option value="WAVE">Wave</option>
                                    <option value="MOBICASH">MobiCash</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Référence de transaction</label>
                                <input type="text" name="transaction_ref" class="form-control" placeholder="Ex: TXN-XXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Note (facultatif)</label>
                                <input type="text" name="owner_note" class="form-control" placeholder="Note sur le paiement">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Preuve de paiement <span class="text-danger">*</span></label>
                                <div class="camera-widget" id="camWidgetLogin">
                                    <div class="btn-group w-100 mb-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary active cam-btn-file">
                                            <i class="bx bx-paperclip me-1"></i>Choisir un fichier
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary cam-btn-cam">
                                            <i class="bx bx-camera me-1"></i>Prendre une photo
                                        </button>
                                    </div>
                                    <div class="cam-file-zone">
                                        <input type="file" name="preuve" class="form-control" accept="image/*,.pdf" required>
                                        <div class="form-text">JPG, PNG ou PDF — max 5 Mo</div>
                                    </div>
                                    <div class="cam-live-zone" style="display:none">
                                        <div class="cam-video-box mb-2">
                                            <video class="cam-video" autoplay playsinline muted></video>
                                            <img class="cam-snap" style="display:none" alt="capture">
                                            <div class="cam-shutter"></div>
                                        </div>
                                        <canvas class="cam-canvas" style="display:none"></canvas>
                                        <div class="d-flex gap-2 mb-1">
                                            <button type="button" class="btn btn-primary btn-sm flex-fill cam-shoot">
                                                <i class="bx bx-camera me-1"></i>Capturer
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm cam-flip" title="Changer de caméra (avant/arrière)">
                                                <i class="bx bx-revision"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill cam-redo" style="display:none">
                                                <i class="bx bx-refresh me-1"></i>Reprendre
                                            </button>
                                        </div>
                                        <div class="cam-msg small text-muted"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @endif

                </div>
                <div class="modal-footer justify-content-between border-0 pt-0">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i>Réessayer la connexion
                    </a>
                    @if(!$loginPending)
                    <button type="submit" form="paymentLoginForm" class="btn btn-danger">
                        <i class="bx bx-upload me-1"></i>Envoyer la preuve de paiement
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL ACCÈS SUSPENDU (SECRÉTAIRE / TAILLEUR) ===== --}}
    @if(session('login_blocked') === 'employee')
    @php $blockedInfo = session('ateliko_blocked_login', []); @endphp
    <div class="modal fade" id="modalLoginBlockedEmployee"
         data-bs-backdrop="static" data-bs-keyboard="false"
         tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bx bx-lock-alt me-2"></i>Accès suspendu</h5>
                </div>
                <div class="modal-body text-center py-4">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 p-4 mb-3" style="width:80px;height:80px">
                        <i class="bx bx-lock-alt text-danger" style="font-size:2.5rem"></i>
                    </span>
                    <h5 class="fw-bold mb-2">Accès temporairement suspendu</h5>
                    <p class="text-muted">
                        L'abonnement de <strong>{{ $blockedInfo['atelier_nom'] ?? 'votre atelier' }}</strong> est expiré.<br>
                        Contactez le propriétaire de votre atelier pour renouveler l'abonnement.
                    </p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Retour à la connexion
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        (() => {
            const slides = @json(array_map(fn ($slide) => ['url' => asset('assets/images/' . $slide[0]), 'title' => $slide[1]], $slides));
            let current = 0;
            const desktop = document.getElementById('backgroundImage');
            const mobile = document.getElementById('mobileCarouselItem');
            const counter = document.getElementById('imageCounter');
            const mobileCounter = document.getElementById('mobileImageCounter');
            const title = document.getElementById('mobileSlideTitle');
            const render = () => {
                const slide = slides[current];
                desktop.style.backgroundImage = `url('${slide.url}')`;
                mobile.style.backgroundImage = `url('${slide.url}')`;
                counter.textContent = `Image ${current + 1}/${slides.length}`;
                mobileCounter.textContent = `${current + 1}/${slides.length}`;
                title.textContent = slide.title;
            };
            const move = direction => { current = (current + direction + slides.length) % slides.length; render(); };
            document.querySelectorAll('.slide-prev').forEach(button => button.addEventListener('click', () => move(-1)));
            document.querySelectorAll('.slide-next').forEach(button => button.addEventListener('click', () => move(1)));
            setInterval(() => move(1), 4000);
            render();
            document.getElementById('togglePassword').addEventListener('click', event => {
                const input = document.getElementById('inputChoosePassword');
                const icon = event.currentTarget.querySelector('i');
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                icon.className = `bx ${visible ? 'bx-hide' : 'bx-show'}`;
            });
            document.querySelector('form').addEventListener('submit', () => {
                const button = document.getElementById('loginButton');
                button.disabled = true;
                button.querySelector('span:first-of-type').textContent = 'Connexion...';
                button.querySelector('.spinner-border').classList.remove('d-none');
            });
        })();

        // Ouvrir automatiquement le modal bloquant
        @if(session('login_blocked') === 'proprietaire')
        (function() {
            var el = document.getElementById('modalLoginBlocked');
            if (el) { new bootstrap.Modal(el).show(); }
        })();
        @endif
        @if(session('login_blocked') === 'employee')
        (function() {
            var el = document.getElementById('modalLoginBlockedEmployee');
            if (el) { new bootstrap.Modal(el).show(); }
        })();
        @endif

        // Widget caméra
        (function() {
            function initCameraWidget(w) {
                var fileZone  = w.querySelector('.cam-file-zone');
                var liveZone  = w.querySelector('.cam-live-zone');
                var fileInput = fileZone ? fileZone.querySelector('input[type=file]') : null;
                var video     = w.querySelector('.cam-video');
                var snap      = w.querySelector('.cam-snap');
                var canvas    = w.querySelector('.cam-canvas');
                var shootBtn  = w.querySelector('.cam-shoot');
                var redoBtn   = w.querySelector('.cam-redo');
                var msg       = w.querySelector('.cam-msg');
                var btnFile   = w.querySelector('.cam-btn-file');
                var btnCam    = w.querySelector('.cam-btn-cam');
                var shutter   = w.querySelector('.cam-shutter');
                var stream    = null;

                if (!fileInput || !video) return;

                var facingMode = 'environment';
                var flipBtn = w.querySelector('.cam-flip');

                function stopStream() {
                    if (stream) { stream.getTracks().forEach(function(t){ t.stop(); }); stream = null; }
                }
                function startStream() {
                    msg.textContent = 'Accès à la caméra…';
                    navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 } }
                    }).then(function(s) {
                        stream = s; video.srcObject = s;
                        video.style.display = 'block'; snap.style.display = 'none';
                        shootBtn.style.display = ''; redoBtn.style.display = 'none';
                        msg.textContent = '';
                    }).catch(function(err) {
                        msg.textContent = '⚠ Caméra inaccessible : ' + (err.message || err.name);
                        msg.className = 'cam-msg small text-danger';
                    });
                }
                if (flipBtn) {
                    flipBtn.addEventListener('click', function() {
                        facingMode = facingMode === 'environment' ? 'user' : 'environment';
                        stopStream();
                        startStream();
                    });
                }

                btnCam.addEventListener('click', function() {
                    fileZone.style.display = 'none'; liveZone.style.display = 'block';
                    btnCam.classList.add('active','btn-primary'); btnCam.classList.remove('btn-outline-primary');
                    btnFile.classList.remove('active');
                    fileInput.required = false;
                    startStream();
                });
                btnFile.addEventListener('click', function() {
                    liveZone.style.display = 'none'; fileZone.style.display = 'block';
                    btnFile.classList.add('active');
                    btnCam.classList.remove('active','btn-primary'); btnCam.classList.add('btn-outline-primary');
                    fileInput.required = true;
                    stopStream();
                });
                shootBtn.addEventListener('click', function() {
                    canvas.width = video.videoWidth || 640; canvas.height = video.videoHeight || 480;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    if (shutter) { shutter.classList.add('flash'); setTimeout(function(){ shutter.classList.remove('flash'); }, 200); }
                    canvas.toBlob(function(blob) {
                        var file = new File([blob], 'preuve_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                        snap.src = URL.createObjectURL(blob);
                        snap.style.display = 'block'; video.style.display = 'none';
                        shootBtn.style.display = 'none'; redoBtn.style.display = '';
                        msg.textContent = '✓ Photo capturée';
                        msg.className = 'cam-msg small text-success';
                        try { var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; } catch(e){}
                        stopStream();
                    }, 'image/jpeg', 0.92);
                });
                redoBtn.addEventListener('click', function() {
                    snap.style.display = 'none'; shootBtn.style.display = '';
                    redoBtn.style.display = 'none'; msg.textContent = '';
                    try { fileInput.files = new DataTransfer().files; } catch(e){}
                    startStream();
                });

                // Arrêter la caméra si le modal se ferme
                var modal = w.closest('.modal');
                if (modal) {
                    modal.addEventListener('hidden.bs.modal', function() { stopStream(); });
                }
            }
            document.querySelectorAll('.camera-widget').forEach(initCameraWidget);
        })();
    </script>
</body>
</html>
