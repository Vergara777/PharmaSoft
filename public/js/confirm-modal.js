(function(){
  // Reusable confirmation using SweetAlert2 if present, otherwise a simple DOM modal
  function confirmDialog(opts) {
    var title = opts && opts.title || 'Confirmación';
    var text = opts && opts.text || '¿Deseas continuar?';
    var ok = opts && opts.ok || 'Aceptar';
    var cancel = opts && opts.cancel || 'Cancelar';
    if (window.Swal && typeof Swal.fire === 'function') {
      return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: ok,
        cancelButtonText: cancel,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
      }).then(function(r){ return !!r.isConfirmed; });
    }
    // Fallback minimal modal
    return new Promise(function(resolve){
      var modal = document.getElementById('psConfirmFallback');
      if (!modal) {
        modal = document.createElement('div');
        modal.id = 'psConfirmFallback';
        modal.style.cssText = 'position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.45);z-index:4000;';
        modal.innerHTML = '\
          <div style="background:#fff;max-width:520px;width:92%;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,.2);overflow:hidden;">\
            <div style="padding:14px 18px;border-bottom:1px solid #eee;display:flex;align-items:center;gap:8px;">\
              <div style="width:28px;height:28px;border-radius:50%;background:#f8d7da;color:#721c24;display:grid;place-items:center;font-weight:700;">!</div>\
              <h5 id="pscfTitle" style="margin:0;font-weight:600;">'+title+'</h5>\
            </div>\
            <div style="padding:18px;color:#444;" id="pscfText"></div>\
            <div style="padding:12px 18px;border-top:1px solid #eee;display:flex;justify-content:flex-end;gap:8px;">\
              <button id="pscfCancel" type="button" class="btn btn-secondary">'+cancel+'</button>\
              <button id="pscfOk" type="button" class="btn btn-danger">'+ok+'</button>\
            </div>\
          </div>';
        document.body.appendChild(modal);
      }
      var titleEl = modal.querySelector('#pscfTitle');
      var textEl = modal.querySelector('#pscfText');
      var okBtn = modal.querySelector('#pscfOk');
      var cancelBtn = modal.querySelector('#pscfCancel');
      if (titleEl) titleEl.textContent = title;
      if (textEl) textEl.textContent = text;
      function hide(){ modal.style.display = 'none'; okBtn.onclick = cancelBtn.onclick = null; }
      okBtn.onclick = function(){ hide(); resolve(true); };
      cancelBtn.onclick = function(){ hide(); resolve(false); };
      modal.onclick = function(e){ if (e.target === modal) { hide(); resolve(false); } };
      modal.style.display = 'flex';
    });
  }

  // Expose helper
  window.psConfirm = confirmDialog;

  // Auto-bind forms with .js-confirmable
  function bindConfirmableForms(){
    function shouldBypassConfirm(form){
      try {
        if (window.psForceImmediateSubmit === true) return true;
      } catch(_){ }
      // Do not bypass by default; always confirm for .js-confirmable
      return false;
    }
    // Bind only forms explicitly marked as confirmable
    var forms = Array.prototype.slice.call(document.querySelectorAll('form.js-confirmable'));
    forms.forEach(function(f){
      // Avoid double-binding
      if (f.__psConfirmBound) return; f.__psConfirmBound = true;
      f.addEventListener('submit', function(ev){
        try { console.debug('[psConfirm] intercept submit', { action: f.getAttribute('action'), method: (f.method||'').toLowerCase() }); } catch(_){ }
        if (f.__confirmed) return; // already confirmed, allow through
        // If a confirmation is in progress, let it pass to avoid double prompts
        if (f.getAttribute('data-confirming') === '1') return;
        ev.preventDefault();
        try { if (typeof window.bannerLoading === 'function') window.bannerLoading(false); } catch(_){ }
        // Mark confirming and temporarily skip global jQuery confirm
        f.setAttribute('data-confirming','1');
        f.setAttribute('data-skip-confirm','1');
        var title = f.getAttribute('data-confirm-title') || 'Confirmación';
        var text = f.getAttribute('data-confirm-text') || '¿Deseas continuar?';
        var ok = f.getAttribute('data-confirm-ok') || 'Aceptar';
        var cancel = f.getAttribute('data-confirm-cancel') || 'Cancelar';
        var bypass = shouldBypassConfirm(f);
        var proceed = function(){
          try { console.debug('[psConfirm] hotfix proceed immediate'); } catch(_){}
          f.__confirmed = true;
          f.setAttribute('data-skip-confirm','1'); // keep skipping global confirm
          var form = f; form.removeAttribute('data-confirming');
          setTimeout(function(){
            try {
              if (typeof window.bannerLoading === 'function' && form.getAttribute('data-no-loading') !== '1') {
                var sbtn = form.querySelector('button[type="submit"][data-loading-text], input[type="submit"][data-loading-text]');
                var ltxt = (sbtn && sbtn.getAttribute('data-loading-text')) || form.getAttribute('data-loading-text') || 'Enviando datos...';
                window.bannerLoading(true, ltxt);
              }
            } catch(_){ }
            try {
              try { console.debug('[psConfirm] submitting natively as POST'); } catch(_){ }
              if (!form.method || form.method.toLowerCase() !== 'post') form.method = 'post';
              HTMLFormElement.prototype.submit.call(form);
            } catch(_) {
              try { console.debug('[psConfirm] native submit fallback'); } catch(_){}
              try { form.method = 'post'; } catch(_) {}
              form.submit();
            }
          }, 0);
        };
        if (bypass) {
          proceed();
        } else {
          confirmDialog({ title: title, text: text, ok: ok, cancel: cancel }).then(function(confirmed){
          try { console.debug('[psConfirm] user choice', { confirmed: confirmed }); } catch(_){}
          if (confirmed) {
            proceed();
          } else {
            // Clean flags so user can try again later
            f.removeAttribute('data-skip-confirm');
            f.removeAttribute('data-confirming');
            try { if (typeof window.bannerLoading === 'function') window.bannerLoading(false); } catch(_){ }
          }
        });
        }
      }, { capture: true });
    });
    // Note: If forms are injected dynamically, call bindConfirmableForms() after injection.
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindConfirmableForms);
  } else {
    bindConfirmableForms();
  }
})();
