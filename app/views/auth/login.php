<?php use App\Core\View; use App\Helpers\Security; ?>
<style>
  /* Scoped to login view */
  .login-shell { min-height: calc(100vh - 60px); display: grid; place-items: center; padding: 24px 12px; }
  .login-modal { width: 100%; max-width: 460px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 20px; box-shadow: 0 30px 80px rgba(0,0,0,.18); overflow: hidden; }
  .login-header { padding: 24px 22px; display: flex; align-items: center; justify-content: flex-start; gap: 12px; background: linear-gradient(135deg, #22d3ee, #a78bfa); color: #111827; position: relative; text-align: left; }
  .login-header:after { content: ""; position: absolute; inset: 0; pointer-events: none; background: radial-gradient(80% 60% at 10% 10%, rgba(255,255,255,.06), transparent 60%); }
  .login-logo { width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; background: #0f172a; color: #fff; box-shadow: 0 8px 24px rgba(99,102,241,.35), inset 0 -2px 0 rgba(0,0,0,.25); font-size: 22px; border: 1px solid rgba(255,255,255,.08); }
  .login-title { margin: 0; font-weight: 900; letter-spacing: .2px; font-size: 1.45rem; line-height: 1; filter: drop-shadow(0 2px 6px rgba(0,0,0,.25)); }
  .login-sub { margin: 2px 0 0; opacity: .85; font-size: 12px; }
  .login-body { padding: 20px 18px; }
  .login-body .form-group label { font-weight: 600; color: #374151; }
  .login-body .form-control { height: 48px; border-radius: 14px; border: 1px solid #d1d5db; transition: box-shadow .25s ease, border-color .25s ease; }
  .login-body .form-control:focus { border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(59,130,246,.15); }
  .login-body .input-group-text { background: #f3f4f6; border: 1px solid #d1d5db; border-left: 0; }
  .input-group .form-control + .input-group-append .input-group-text,
  .input-group .form-control + .input-group-append .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }
  .input-group .form-control { border-top-right-radius: 0; border-bottom-right-radius: 0; }
  .input-group .input-group-append .input-group-text,
  .input-group .input-group-append .btn { border-top-right-radius: 14px; border-bottom-right-radius: 14px; }
  .login-actions { padding: 16px 18px 22px; }
  .login-btn { height: 52px; border-radius: 18px; font-weight: 800; letter-spacing: .2px; box-shadow: 0 14px 28px rgba(56, 189, 248, .25); background: linear-gradient(90deg, #22d3ee, #60a5fa); border: 0; }
  .login-btn:hover { filter: brightness(1.02); box-shadow: 0 18px 34px rgba(56, 189, 248, .32); }
  .login-btn:active { transform: translateY(1px); }
  .login-footer { text-align: center; padding: 10px 18px 18px; color: #6b7280; font-size: 12px; }
  /* Make background neutral when login is active */
  body.login-body { background: #f3f4f6; }

  /* Brand-style gradient (blue→violet) like dashboard with safe fallback */
  .brand-text { display:inline-block; color: #111827; }
  .brand-icon { color: #ffffff; }
  .brand-glow { text-shadow: 0 0 10px rgba(99,102,241,.35), 0 0 18px rgba(59,130,246,.18); }
  /* Enable true gradient text/icon only when supported */
  .supports-text-clip .brand-text { background: linear-gradient(90deg, #2563eb, #8b5cf6); background-clip: text; -webkit-background-clip: text; color: transparent; -webkit-text-fill-color: transparent; }
  .supports-text-clip .brand-icon { background: linear-gradient(90deg, #2563eb, #8b5cf6); background-clip: text; -webkit-background-clip: text; color: transparent; -webkit-text-fill-color: transparent; }
  .login-logo { position: relative; }
  /* Remove RGB ring/glow */
  .login-logo:before,
  .login-logo:after { content: none; }

  /* Remove RGB-related keyframes and effects */

  /* Breathing animations */
  .btn-breathe { animation: breathe 3.2s ease-in-out infinite; transform-origin: center; }
  @keyframes breathe { 0%, 100% { transform: scale(1); box-shadow: 0 8px 22px rgba(56,189,248,.24); } 50% { transform: scale(1.06); box-shadow: 0 12px 28px rgba(56,189,248,.33); } }
  .title-breathe { animation: titleBreath 10s cubic-bezier(.4,0,.2,1) infinite; display: inline-block; will-change: transform; transform-origin: center; }
  .icon-breathe { animation: iconBreath 10s cubic-bezier(.4,0,.2,1) infinite; will-change: transform; transform-origin: center; }
  @keyframes titleBreath { 0%,100% { transform: scale(1); } 50% { transform: scale(1.24); } }
  @keyframes iconBreath  { 0%,100% { transform: scale(1); } 50% { transform: scale(1.26); } }

  /* RGB lights glow outline (multi-color, stronger but slower) */
  .rgb-outline { position: relative; animation: rgbOutline 5s linear infinite; }
  @keyframes rgbOutline {
    0% { text-shadow: 0 0 14px rgba(255,0,85,.70), 0 0 20px rgba(255,204,0,.55), 0 0 28px rgba(0,204,255,.50), 0 0 36px rgba(99,102,241,.40); }
    20%{ text-shadow: 0 0 14px rgba(59,130,246,.70), 0 0 20px rgba(34,197,94,.55), 0 0 28px rgba(234,179,8,.50), 0 0 36px rgba(255,0,85,.40); }
    40%{ text-shadow: 0 0 14px rgba(139,92,246,.70), 0 0 20px rgba(59,130,246,.55), 0 0 28px rgba(255,0,85,.50), 0 0 36px rgba(34,197,94,.40); }
    60%{ text-shadow: 0 0 14px rgba(0,204,255,.70), 0 0 20px rgba(255,0,85,.55), 0 0 28px rgba(255,204,0,.50), 0 0 36px rgba(139,92,246,.40); }
    80%{ text-shadow: 0 0 14px rgba(34,197,94,.70), 0 0 20px rgba(139,92,246,.55), 0 0 28px rgba(59,130,246,.50), 0 0 36px rgba(234,179,8,.40); }
    100%{ text-shadow: 0 0 14px rgba(255,0,85,.70), 0 0 20px rgba(255,204,0,.55), 0 0 28px rgba(0,204,255,.50), 0 0 36px rgba(99,102,241,.40); }
  }

  /* Toast notifications (centered like light modal) */
  .login-toast-wrap { position: fixed; inset: 0; z-index: 1060; display: flex; align-items: center; justify-content: center; padding: 16px; background: transparent; }
  .login-toast { width: min(92vw, 520px); background: #ffffff; color: #111827; border-radius: 16px; box-shadow: 0 30px 80px rgba(0,0,0,.25); overflow: hidden; border: 1px solid #e5e7eb; animation: toastIn .22s ease-out; pointer-events: auto; }
  .login-toast .bar { height: 4px; background: linear-gradient(90deg, #22d3ee, #a78bfa, #22c55e); }
  .login-toast .content { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; padding: 16px; }
  .login-toast .pill { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; background: #0ea5e9; color: #fff; box-shadow: inset 0 -2px 0 rgba(0,0,0,.25); }
  .login-toast .title { font-weight: 900; margin-bottom: 2px; color: #0f172a; }
  .login-toast .msg { color: #991b1b; font-weight: 800; background: #fee2e2; border: 1px solid #fecaca; border-radius: 10px; padding: 8px 10px; }
  .login-toast .tips { margin: 6px 0 0; padding-left: 18px; color: #374151; font-size: .95rem; text-align: left; }
  .login-toast .actions { display: flex; gap: 8px; align-items: center; justify-content: center; margin-top: 8px; }
  .login-toast .btn { border-radius: 10px; font-weight: 800; padding: 6px 10px; font-size: .9rem; }
  .login-toast .btn-success { background: #22c55e; border: 1px solid #16a34a; color: #052e16; }
  .login-toast .btn-outline-success { background: transparent; border: 1px solid #16a34a; color: #065f46; }
  .login-toast .close { background: transparent; border: none; color: #6b7280; font-size: 18px; line-height: 1; padding: 4px 6px; }
  @keyframes toastIn { from { transform: translateY(6px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  @keyframes toastOut { to { transform: translateY(-6px); opacity: 0; } }
  .form-control.invalid { border-color: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.12); }

  /* keep placeholder to avoid duplicate definitions above */

  /* No extra text glow */

  /* Reserved for future card animations (disabled for calm static modal) */
  @keyframes cardDrift { 0%, 100% { transform: none; } 50% { transform: none; } }

  /* Respect reduced motion, but allow manual override via body[data-allow-motion="true"] */
  @media (prefers-reduced-motion: reduce) {
    body:not([data-allow-motion="true"]) .rgb-text,
    body:not([data-allow-motion="true"]) .rgb-icon,
    body:not([data-allow-motion="true"]) .login-logo:before,
    body:not([data-allow-motion="true"]) .btn-breathe,
    body:not([data-allow-motion="true"]) .title-breathe,
    body:not([data-allow-motion="true"]) .icon-breathe,
    body:not([data-allow-motion="true"]) .login-modal { animation: none !important; }
  }
</style>

<div class="login-shell">
  <div class="login-modal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
    <div class="login-header">
      <div class="login-logo" aria-hidden="true"><i class="fas fa-capsules brand-icon brand-glow rgb-outline icon-breathe"></i></div>
      <div>
        <h3 id="loginTitle" class="login-title brand-glow rgb-outline title-breathe">PharmaSoft</h3>
        <div class="login-sub">Iniciar sesión</div>
      </div>
    </div>
    <?php if (!empty($errorEmail) || !empty($errorPassword)): ?>
      <?php $isEmailErr = !empty($errorEmail); ?>
      <div class="login-toast-wrap" aria-modal="true" role="dialog">
        <div class="login-toast" data-error-type="<?= $isEmailErr ? 'email' : 'password' ?>" role="status" aria-live="polite">
          <div class="bar"></div>
          <div class="content">
            <div class="pill"><i class="fas fa-exclamation-circle" aria-hidden="true"></i></div>
            <div>
              <div class="title"><?= $isEmailErr ? 'Correo incorrecto' : 'Contraseña incorrecta' ?></div>
              <div class="msg"><?= $isEmailErr ? View::e($errorEmail) : View::e($errorPassword) ?></div>
              <ul class="tips">
                <?php if ($isEmailErr): ?>
                  <li>Verifica ortografía y evita espacios extra.</li>
                  <li>Ejemplo: nombre@empresa.com</li>
                <?php else: ?>
                  <li>Respeta mayúsculas/minúsculas y revisa Bloq Mayús.</li>
                  <li>Si olvidaste tu clave, solicita un restablecimiento.</li>
                <?php endif; ?>
              </ul>
            </div>
            <div class="actions">
              <button type="button" class="btn btn-success" id="toastFixBtn">Volver a ingresar datos</button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <div class="login-body">
      <form method="post" action="<?= BASE_URL ?>/auth/login" data-loading-text="Iniciando sesión...">
        <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
        <div class="form-group">
          <label for="loginEmail">Email</label>
          <div class="input-group">
            <input id="loginEmail" type="email" name="email" class="form-control<?= !empty($errorEmail) ? ' invalid' : '' ?>" placeholder="tu@correo.com" required autofocus value="<?= isset($email) ? View::e($email) : '' ?>">
            <div class="input-group-append">
              <span class="input-group-text"><i class="fas fa-at" aria-hidden="true"></i></span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="loginPass">Contraseña</label>
          <div class="input-group">
            <input id="loginPass" type="password" name="password" class="form-control<?= !empty($errorPassword) ? ' invalid' : '' ?>" placeholder="••••••••" required>
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" id="togglePass" aria-label="Mostrar contraseña"><i class="fas fa-eye" aria-hidden="true"></i></button>
            </div>
          </div>
        </div>
        <div class="login-actions">
          <button class="btn btn-primary btn-block login-btn btn-breathe"><i class="fas fa-sign-in-alt mr-2" aria-hidden="true"></i> Entrar</button>
        </div>
      </form>
      <div class="login-footer">© <?= date('Y') ?> PharmaSoft</div>
    </div>
  </div>
</div>

<script>
  (function(){
    try {
      // Ensure animations are allowed on the login page
      if (document && document.body) {
        document.body.setAttribute('data-allow-motion', 'true');
        document.body.classList.add('login-body');
      }
      // Feature-detect for background-clip: text to safely enable gradient text/icon
      var supportsTextClip = false;
      try {
        if (window.CSS && CSS.supports) {
          supportsTextClip = CSS.supports('-webkit-background-clip', 'text') || CSS.supports('background-clip', 'text');
        }
      } catch(_){}
      if (supportsTextClip && document.body) {
        document.body.classList.add('supports-text-clip');
      }
      var btn = document.getElementById('togglePass');
      var inp = document.getElementById('loginPass');
      if (btn && inp) {
        btn.addEventListener('click', function(){
          var isPwd = inp.getAttribute('type') === 'password';
          inp.setAttribute('type', isPwd ? 'text' : 'password');
          var ic = btn.querySelector('i');
          if (ic) { ic.classList.toggle('fa-eye'); ic.classList.toggle('fa-eye-slash'); }
          btn.setAttribute('aria-label', isPwd ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
      }

      // Toast interactions
      var toast = document.querySelector('.login-toast');
      if (toast) {
        var errType = toast.getAttribute('data-error-type');
        var closeBtn = document.getElementById('toastCloseBtn');
        var fixBtn = document.getElementById('toastFixBtn');
        var wrap = document.querySelector('.login-toast-wrap');
        function closeToast(){
          if (!toast) return;
          try {
            toast.style.animation = 'toastOut .18s ease-in forwards';
            setTimeout(function(){
              if (wrap && wrap.parentNode) { wrap.parentNode.removeChild(wrap); }
            }, 160);
          } catch(_){}
        }
        if (closeBtn) closeBtn.addEventListener('click', closeToast);
        if (fixBtn) fixBtn.addEventListener('click', function(){
          closeToast();
          setTimeout(function(){
            if (errType === 'email') {
              var f = document.getElementById('loginEmail');
              if (f) { try { f.focus(); f.select && f.select(); } catch(_){} }
            } else {
              var p = document.getElementById('loginPass');
              if (p) { try { p.focus(); p.select && p.select(); } catch(_){} }
            }
          }, 80);
        });
        // Click outside (backdrop) to close
        if (wrap) {
          wrap.addEventListener('click', function(e){
            if (e.target === wrap) {
              closeToast();
              setTimeout(function(){
                if (errType === 'email') {
                  var f = document.getElementById('loginEmail');
                  if (f) { try { f.focus(); f.select && f.select(); } catch(_){} }
                } else {
                  var p = document.getElementById('loginPass');
                  if (p) { try { p.focus(); p.select && p.select(); } catch(_){} }
                }
              }, 80);
            }
          });
        }
        // Restart button removed per UX request
        // Esc to close
        document.addEventListener('keydown', function(e){
          if (e.key === 'Escape') {
            closeToast();
            setTimeout(function(){
              if (errType === 'email') {
                var f = document.getElementById('loginEmail');
                if (f) { try { f.focus(); } catch(_){} }
              } else {
                var p = document.getElementById('loginPass');
                if (p) { try { p.focus(); } catch(_){} }
              }
            }, 80);
          }
        });
      }
    } catch(_){ }
  })();
</script>
