<?php use App\Core\View; use App\Helpers\Security; ?>
<style>
  :root {
    --primary: #2563eb;
    --primary-light: #3b82f6;
    --primary-dark: #1d4ed8;
    --text: #1f2937;
    --text-light: #6b7280;
    --border: #e5e7eb;
    --bg: #f9fafb;
    --white: #ffffff;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --transition: all 0.2s ease-in-out;
  }

  body.login-body {
    background: var(--bg);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    margin: 0;
    padding: 0;
    color: var(--text);
    line-height: 1.5;
  }

  .login-shell {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  }

  .login-container {
    width: 100%;
    max-width: 420px;
    background: var(--white);
    border-radius: 1rem;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    border: 1px solid var(--border);
  }

  .login-header {
    padding: 2rem 2rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    position: relative;
    overflow: hidden;
  }

  .login-header::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.2) 0%, transparent 50%);
    pointer-events: none;
  }

  .login-logo {
    width: 3.5rem;
    height: 3.5rem;
    margin: 0 auto 1rem;
    background: var(--white);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary);
    font-weight: 800;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  .login-title {
    margin: 0 0 0.25rem;
    font-size: 1.5rem;
    font-weight: 800;
    position: relative;
    z-index: 1;
  }

  .login-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 0.875rem;
    font-weight: 400;
    position: relative;
    z-index: 1;
  }

  .login-body {
    padding: 2rem;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text);
    font-size: 0.875rem;
  }

  .form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    height: calc(1.5em + 1.5rem + 2px);
    font-size: 1rem;
    line-height: 1.5;
    color: var(--text);
    background-color: var(--white);
    background-clip: padding-box;
    border: 1px solid var(--border);
    border-radius: 0.5rem;
    transition: var(--transition);
    box-sizing: border-box;
  }

  .form-control:focus {
    border-color: var(--primary);
    outline: 0;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  }

  .input-group {
    position: relative;
    display: flex;
    width: 100%;
    height: calc(1.5em + 1.5rem + 2px);
    align-items: stretch;
  }

  .input-group .form-control {
    position: relative;
    flex: 1 1 auto;
    width: 1%;
    min-width: 0;
    margin-bottom: 0;
  }

  .input-group-append {
    margin-left: -1px;
    display: flex;
  }

  .input-group-text {
    display: flex;
    align-items: center;
    padding: 0 1rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: var(--text-light);
    text-align: center;
    white-space: nowrap;
    background-color: #f9fafb;
    border: 1px solid var(--border);
    border-left: 0;
    border-radius: 0 0.5rem 0.5rem 0;
    height: 100%;
  }

  .login-actions {
    margin-top: 1.5rem;
  }

  .login-btn {
    display: block;
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.5;
    color: white;
    text-align: center;
    text-decoration: none;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
  }

  .login-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
  }

  .login-btn:active {
    transform: translateY(0);
  }

  .login-footer {
    padding: 1.25rem 2rem;
    text-align: center;
    font-size: 0.75rem;
    color: var(--text-light);
    background-color: #f9fafb;
    border-top: 1px solid var(--border);
  }

  .login-footer a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
  }

  .login-footer a:hover {
    text-decoration: underline;
  }

  /* Loading spinner */
  .login-btn .btn-content {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
    transition: var(--transition);
  }

  .login-btn .spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0);
    opacity: 0;
    width: 80px;
    height: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: var(--transition);
  }
  
  .login-btn .spinner span {
    display: block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #fff;
    animation: bounce 1.4s infinite ease-in-out;
  }
  
  .login-btn .spinner span:nth-child(1) { animation-delay: 0s; }
  .login-btn .spinner span:nth-child(2) { animation-delay: 0.16s; }
  .login-btn .spinner span:nth-child(3) { animation-delay: 0.32s; }
  
  .login-btn.loading .btn-content { 
    opacity: 0;
    transform: translateY(5px);
  }
  
  .login-btn.loading .spinner {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
  }
  
  @keyframes bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-8px); }
  }

  /* Error message */
  .alert {
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    font-size: 0.875rem;
  }

  .alert-danger {
    color: #991b1b;
    background-color: #fee2e2;
    border-color: #fecaca;
  }

  /* Responsive adjustments */
  @media (max-width: 480px) {
    .login-container {
      border-radius: 0.75rem;
    }
    
    .login-header {
      padding: 1.5rem 1.5rem 1.25rem;
    }
    
    .login-body {
      padding: 1.5rem;
    }
  }
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
  .login-toast-wrap { position: fixed; inset: 0; z-index: 1060; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(0,0,0,0.1); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
  .login-toast { width: min(94vw, 560px); background: #ffffff; color: #111827; border-radius: 20px; box-shadow: 0 30px 90px -10px rgba(0,0,0,.3); overflow: hidden; border: 1px solid rgba(0,0,0,0.05); animation: toastIn .45s cubic-bezier(.21,1.02,.73,1) forwards; pointer-events: auto; }
  .login-toast .bar { height: 5px; background: linear-gradient(90deg, #ef4444, #f87171, #fca5a5); }
  .login-toast .content { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px; padding: 24px; }
  .login-toast .pill { width: 60px; height: 60px; border-radius: 16px; display: grid; place-items: center; background: #ef4444; color: #fff; font-size: 24px; box-shadow: inset 0 -3px 0 rgba(0,0,0,.25); }
  .login-toast .title { font-weight: 900; font-size: 22px; margin-bottom: 4px; color: #0f172a; }
  .login-toast .msg { color: #991b1b; font-weight: 700; background: #fee2e2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 16px; font-size: 16px; }
  .login-toast .tips { margin: 12px 0 0; padding-left: 18px; color: #374151; font-size: 1rem; text-align: left; }
  .login-toast .actions { display: flex; gap: 12px; align-items: center; justify-content: center; margin-top: 16px; }
  .login-toast .btn { border-radius: 12px; font-weight: 800; padding: 10px 16px; font-size: 1rem; }
  .login-toast .btn-success { background: #22c55e; border: 1px solid #16a34a; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,.2); }
  .login-toast .btn-outline-success { background: transparent; border: 1px solid #16a34a; color: #065f46; }
  .login-toast .close { background: transparent; border: none; color: #6b7280; font-size: 18px; line-height: 1; padding: 4px 6px; }
  @keyframes toastIn { from { transform: translateY(-20px) scale(0.95); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
  @keyframes toastOut { to { transform: translateY(20px) scale(0.95); opacity: 0; } }
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
  <div class="login-container">
    <div class="login-header">
      <div class="login-logo">
        <i class="fas fa-capsules"></i>
      </div>
      <h1 class="login-title">PharmaSoft</h1>
      <p class="login-subtitle">Sistema de Gestión Farmacéutica</p>
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
      <form method="post" action="<?= BASE_URL ?>/auth/login" data-loading-text="Iniciando sesión..." id="loginForm">
        <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
        
        <div class="form-group">
          <label for="loginEmail">Correo electrónico</label>
          <div class="input-group">
            <input id="loginEmail" 
                   type="email" 
                   name="email" 
                   class="form-control<?= !empty($errorEmail) ? ' invalid' : '' ?>" 
                   placeholder="tu@correo.com" 
                   required 
                   autofocus 
                   value="<?= isset($email) ? View::e($email) : '' ?>">
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="fas fa-envelope" aria-hidden="true"></i>
              </span>
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
          <button type="submit" class="btn btn-primary btn-block login-btn btn-breathe" id="loginButton">
            <span class="btn-content">
              <i class="fas fa-sign-in-alt mr-2" aria-hidden="true"></i> Entrar
            </span>
            <span class="spinner">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
          </span>
          </button>
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

      // Manejar el estado de carga del botón
      var loginForm = document.querySelector('form[action$="/auth/login"]');
      var loginButton = document.getElementById('loginButton');
      
      if (loginForm && loginButton) {
        loginForm.addEventListener('submit', function(e) {
          if (loginForm.checkValidity()) {
            loginButton.disabled = true;
            loginButton.classList.add('loading');
          }
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

        // Automatically close the toast after 10 seconds
        setTimeout(closeToast, 10000);

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