/*
 * Novaspark Games — one script for the whole site.
 * Behaviour is attached through data- attributes. Every page still works
 * without JavaScript: forms post and links navigate.
 */
(function () {
  'use strict';

  document.documentElement.classList.add('js');

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function $(selector, root) { return (root || document).querySelector(selector); }
  function $all(selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); }
  function onReady(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  /* ---------- Mobile navigation ---------- */
  function initNav() {
    var toggle = $('[data-nav-toggle]');
    var nav = $('#site-nav');
    if (!toggle || !nav) return;

    function setOpen(open) {
      toggle.setAttribute('aria-expanded', String(open));
      nav.classList.toggle('is-open', open);
    }
    toggle.addEventListener('click', function () {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setOpen(false);
        toggle.focus();
      }
    });
    window.matchMedia('(min-width: 768px)').addEventListener('change', function (mq) {
      if (mq.matches) setOpen(false);
    });
  }

  /* ---------- Account menu ---------- */
  function initAccountMenu() {
    var menu = $('[data-account-menu]');
    if (!menu) return;
    var button = $('[data-account-toggle]', menu);

    function setOpen(open) {
      button.setAttribute('aria-expanded', String(open));
      menu.classList.toggle('is-open', open);
    }
    button.addEventListener('click', function () {
      setOpen(button.getAttribute('aria-expanded') !== 'true');
    });
    document.addEventListener('click', function (e) {
      if (!menu.contains(e.target)) setOpen(false);
    });
    menu.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { setOpen(false); button.focus(); }
    });
  }

  /* ---------- Password show / hide ---------- */
  function initPasswordToggles() {
    $all('[data-password-toggle]').forEach(function (btn) {
      var input = document.getElementById(btn.getAttribute('data-password-toggle'));
      if (!input) return;
      btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', String(show));
        btn.firstChild.nodeValue = show ? 'Hide' : 'Show';
      });
    });
  }

  /* ---------- Live password rule check ---------- */
  function initPasswordRules() {
    $all('[data-password-rules]').forEach(function (list) {
      var input = document.getElementById(list.getAttribute('data-password-rules'));
      var confirm = document.getElementById(list.getAttribute('data-password-confirm'));
      function update() {
        var rules = {
          length: input.value.length >= 8,
          match: confirm ? (confirm.value !== '' && confirm.value === input.value) : true
        };
        $all('[data-rule]', list).forEach(function (li) {
          var met = rules[li.getAttribute('data-rule')];
          li.classList.toggle('is-met', !!met);
          var state = $('.visually-hidden', li);
          if (state) state.textContent = met ? ' (done)' : ' (not yet)';
        });
      }
      input.addEventListener('input', update);
      if (confirm) confirm.addEventListener('input', update);
      update();
    });
  }

  /* ---------- Client-side validation (mirrors the server rules) ---------- */
  var messages = {
    required: function (label) { return 'Enter your ' + label.toLowerCase() + '.'; },
    email: 'Enter an email like name@example.com.',
    minlength: 'At least 8 characters.',
    match: "The passwords don't match."
  };

  function fieldLabel(input) {
    if (input.getAttribute('data-label')) return input.getAttribute('data-label');
    var label = document.querySelector('label[for="' + input.id + '"]');
    if (!label) return 'details';
    return label.childNodes[0].nodeValue.trim();
  }

  function validateField(input) {
    var value = input.value.trim();
    var error = '';
    if (input.required && value === '') {
      error = input.getAttribute('data-msg-required') || messages.required(fieldLabel(input));
    } else if (value !== '' && input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      error = messages.email;
    } else if (value !== '' && input.getAttribute('data-minlength') && value.length < +input.getAttribute('data-minlength')) {
      error = messages.minlength;
    } else if (input.getAttribute('data-match')) {
      var other = document.getElementById(input.getAttribute('data-match'));
      if (other && value !== other.value) error = messages.match;
    } else if (value !== '' && input.type === 'number') {
      var n = Number(value);
      if (isNaN(n) || (input.min !== '' && n < +input.min) || (input.max !== '' && n > +input.max)) {
        error = input.getAttribute('data-msg-range') || 'Enter a number in range.';
      }
    }
    showFieldError(input, error);
    return error;
  }

  function showFieldError(input, error) {
    var field = input.closest('.field');
    if (!field) return;
    var holder = $('[data-field-error]', field);
    field.classList.toggle('field--error', !!error);
    if (error) input.setAttribute('aria-invalid', 'true');
    else input.removeAttribute('aria-invalid');
    if (holder) {
      holder.hidden = !error;
      $('span', holder).textContent = error;
    }
  }

  function initValidation() {
    $all('form[data-validate]').forEach(function (form) {
      form.setAttribute('novalidate', '');
      var inputs = $all('input:not([type=hidden]), select, textarea', form).filter(function (el) {
        return el.required || el.type === 'email' || el.getAttribute('data-minlength') || el.getAttribute('data-match') || el.type === 'number';
      });
      inputs.forEach(function (input) {
        input.addEventListener('blur', function () {
          if (input.value !== '' || input.getAttribute('aria-invalid')) validateField(input);
        });
        input.addEventListener('input', function () {
          if (input.getAttribute('aria-invalid')) validateField(input);
        });
      });
      form.addEventListener('submit', function (e) {
        var errors = [];
        inputs.forEach(function (input) {
          var msg = validateField(input);
          if (msg) errors.push({ input: input, msg: msg });
        });
        var summary = $('[data-error-summary]', form);
        if (errors.length) {
          e.preventDefault();
          if (summary) {
            var title = errors.length === 1 ? 'There is 1 problem to fix' : 'There are ' + errors.length + ' problems to fix';
            summary.innerHTML = '';
            var box = document.createElement('div');
            box.className = 'alert alert--error';
            box.setAttribute('role', 'alert');
            var list = errors.map(function (er) {
              return '<li><a href="#' + er.input.id + '">' + escapeHtml(er.msg) + '</a></li>';
            }).join('');
            box.innerHTML = '<svg class="icon alert__icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5l9.5 16.5h-19z"/><path d="M12 10v4.5"/><path d="M12 17.5v.01"/></svg><div class="alert__body"><p class="alert__title">' + title + '</p><ul class="error-summary">' + list + '</ul></div>';
            summary.appendChild(box);
          }
          errors[0].input.focus();
          return;
        }
        if (summary) summary.innerHTML = '';
      });
    });
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* ---------- Loading state on submit (blocks double submit) ---------- */
  function initLoadingButtons() {
    $all('form').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        var submitter = e.submitter || $('[type=submit][data-loading-text]', form);
        if (form.getAttribute('data-submitting')) { e.preventDefault(); return; }
        form.setAttribute('data-submitting', 'true');
        $all('[type=submit][data-loading-text]', form).forEach(function (btn) {
          btn.classList.add('is-loading');
          btn.setAttribute('aria-disabled', 'true');
          btn.innerHTML = '<span class="spinner" aria-hidden="true"></span><span>' + escapeHtml(btn.getAttribute('data-loading-text')) + '</span>';
        });
        void submitter;
      });
    });
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) $all('form[data-submitting]').forEach(function (f) { f.removeAttribute('data-submitting'); });
    });
  }

  /* ---------- Games filter bar ---------- */
  function initFilters() {
    var form = $('[data-filter-form]');
    if (!form) return;
    var results = $('[data-results]');
    var count = $('[data-result-count]');
    var search = $('#q', form);
    var clear = $('[data-clear-search]', form);
    var timer = null;
    var controller = null;

    function params() {
      var data = new FormData(form);
      var p = new URLSearchParams();
      ['q', 'genre', 'sort'].forEach(function (k) {
        var v = (data.get(k) || '').toString().trim();
        if (v !== '' && !(k === 'sort' && v === 'title')) p.set(k, v);
      });
      return p;
    }

    function update(push) {
      var p = params();
      var qs = p.toString();
      var target = form.getAttribute('action') + (qs ? '?' + qs : '');
      if (push) history.pushState(null, '', target);
      else history.replaceState(null, '', target);
      if (controller) controller.abort();
      controller = 'AbortController' in window ? new AbortController() : null;
      results.classList.add('is-loading-results');
      p.set('partial', '1');
      fetch(form.getAttribute('action') + '?' + p.toString(), {
        headers: { 'X-Requested-With': 'fetch' },
        signal: controller ? controller.signal : undefined
      }).then(function (r) {
        if (!r.ok) throw new Error('Request failed');
        return r.json();
      }).then(function (data) {
        results.innerHTML = data.html;
        count.textContent = data.count;
        results.classList.remove('is-loading-results');
      }).catch(function (err) {
        if (err.name === 'AbortError') return;
        results.classList.remove('is-loading-results');
        form.submit();
      });
    }

    function syncClear() { if (clear) clear.hidden = search.value === ''; }

    search.addEventListener('input', function () {
      syncClear();
      clearTimeout(timer);
      timer = setTimeout(function () { update(false); }, 200);
    });
    form.addEventListener('change', function (e) {
      if (e.target === search) return;
      update(true);
    });
    form.addEventListener('submit', function (e) { e.preventDefault(); update(true); });
    if (clear) {
      clear.addEventListener('click', function () {
        search.value = '';
        syncClear();
        search.focus();
        update(false);
      });
    }
    document.addEventListener('click', function (e) {
      if (!e.target.closest('[data-clear-filters]')) return;
      search.value = '';
      var all = $('input[name=genre][value=""]', form);
      if (all) all.checked = true;
      var sort = $('#sort', form);
      if (sort) sort.value = 'title';
      syncClear();
      update(true);
      search.focus();
    });
    window.addEventListener('popstate', function () {
      var p = new URLSearchParams(location.search);
      search.value = p.get('q') || '';
      var genre = p.get('genre') || '';
      $all('input[name=genre]', form).forEach(function (r) { r.checked = r.value === genre; });
      var sort = $('#sort', form);
      if (sort) sort.value = p.get('sort') || 'title';
      syncClear();
      update(false);
    });
    syncClear();
  }

  /* ---------- News: accordion on mobile, list and reader on desktop ---------- */
  function initNews() {
    $all('[data-news]').forEach(function (news) {
      var buttons = $all('.news__button', news);
      var desktop = window.matchMedia('(min-width: 1024px)');

      function open(btn, focusPanel) {
        buttons.forEach(function (b) {
          var isTarget = b === btn;
          var panel = document.getElementById(b.getAttribute('aria-controls'));
          var expanded = isTarget ? (desktop.matches ? true : b.getAttribute('aria-expanded') !== 'true') : false;
          b.setAttribute('aria-expanded', String(expanded));
          panel.hidden = !expanded;
        });
        if (focusPanel) void 0;
      }
      buttons.forEach(function (btn, i) {
        var panel = document.getElementById(btn.getAttribute('aria-controls'));
        panel.hidden = i !== 0;
        btn.setAttribute('aria-expanded', String(i === 0));
        btn.addEventListener('click', function () { open(btn); });
      });
      desktop.addEventListener('change', function () {
        if (desktop.matches && !buttons.some(function (b) { return b.getAttribute('aria-expanded') === 'true'; })) {
          open(buttons[0]);
        }
      });
    });
  }

  /* ---------- Dialogs (team bios) ---------- */
  function initDialogs() {
    $all('[data-dialog-open]').forEach(function (trigger) {
      var dialog = document.getElementById(trigger.getAttribute('data-dialog-open'));
      if (!dialog || typeof dialog.showModal !== 'function') return;
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        dialog.showModal();
        dialog._trigger = trigger;
      });
    });
    $all('dialog').forEach(function (dialog) {
      dialog.addEventListener('click', function (e) {
        if (e.target === dialog) dialog.close();
      });
      $all('[data-dialog-close]', dialog).forEach(function (btn) {
        btn.addEventListener('click', function () { dialog.close(); });
      });
      dialog.addEventListener('close', function () {
        if (dialog._trigger) { dialog._trigger.focus(); dialog._trigger = null; }
      });
    });
  }

  /* ---------- Confirm dialog for destructive actions ---------- */
  function initConfirm() {
    var dialog = $('#confirm-dialog');
    if (!dialog || typeof dialog.showModal !== 'function') return;
    var title = $('#confirm-dialog-title', dialog);
    var body = $('#confirm-dialog-body', dialog);
    var ok = $('[data-dialog-confirm]', dialog);
    var cancel = $('[data-dialog-cancel]', dialog);
    var pending = null;

    $all('form[data-confirm]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (form._confirmed) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        pending = form;
        title.textContent = form.getAttribute('data-confirm');
        body.textContent = form.getAttribute('data-confirm-body') || '';
        ok.textContent = form.getAttribute('data-confirm-action') || 'Delete';
        ok.className = 'btn ' + (form.getAttribute('data-confirm-style') === 'secondary' ? 'btn--secondary' : 'btn--danger');
        dialog._trigger = e.submitter || $('[type=submit]', form);
        dialog.showModal();
        cancel.focus();
      }, true);
    });
    ok.addEventListener('click', function () {
      if (!pending) return;
      var form = pending;
      pending = null;
      form._confirmed = true;
      dialog._trigger = null;
      dialog.close();
      if (form.requestSubmit) form.requestSubmit(); else form.submit();
    });
    cancel.addEventListener('click', function () { pending = null; dialog.close(); });
  }

  /* ---------- Quantity stepper and live total ---------- */
  function initSteppers() {
    $all('[data-stepper]').forEach(function (stepper) {
      var input = $('input', stepper);
      var minus = $('[data-step="-1"]', stepper);
      var plus = $('[data-step="1"]', stepper);
      var min = +input.min || 1;
      var max = +input.max || 10;
      var price = parseFloat(stepper.getAttribute('data-price'));
      var totals = $all('[data-total]');
      var qtyLabels = $all('[data-qty-label]');

      function clamp(n) { return Math.min(max, Math.max(min, isNaN(n) ? min : Math.round(n))); }
      function render() {
        var n = clamp(+input.value);
        minus.disabled = n <= min;
        plus.disabled = n >= max;
        var total = '£' + (price * n).toFixed(2);
        totals.forEach(function (t) { t.textContent = total; });
        qtyLabels.forEach(function (l) { l.textContent = n === 1 ? '1 licence' : n + ' licences'; });
      }
      [minus, plus].forEach(function (btn) {
        btn.addEventListener('click', function () {
          input.value = clamp(+input.value + (+btn.getAttribute('data-step')));
          render();
        });
      });
      input.addEventListener('input', function () { if (input.value !== '') render(); });
      input.addEventListener('blur', function () { input.value = clamp(+input.value); render(); });
      render();
    });
  }

  /* ---------- Checkout: change the email for this order only ---------- */
  function initEditToggles() {
    $all('[data-edit-toggle]').forEach(function (btn) {
      var target = document.getElementById(btn.getAttribute('aria-controls'));
      var readonly = document.getElementById(btn.getAttribute('data-edit-toggle'));
      var startOpen = !!$('.field--error', target);
      function set(open) {
        btn.setAttribute('aria-expanded', String(open));
        target.hidden = !open;
        if (readonly) readonly.hidden = open;
        btn.textContent = open ? 'Use my account email' : 'Edit';
        var input = $('input', target);
        if (input) input.disabled = !open;
        return input;
      }
      set(startOpen);
      btn.addEventListener('click', function () {
        var input = set(btn.getAttribute('aria-expanded') !== 'true');
        if (input && !input.disabled) input.focus();
      });
    });
  }

  /* ---------- Alerts ---------- */
  function initAlerts() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-dismiss-alert]');
      if (btn) btn.closest('.alert').remove();
    });
    var summary = $('#error-summary');
    if (summary) summary.focus();
  }

  /* ---------- Broken cover images fall back to a branded panel ---------- */
  function initImageFallbacks() {
    document.addEventListener('error', function (e) {
      var img = e.target;
      if (!img || img.tagName !== 'IMG' || !img.hasAttribute('data-fallback')) return;
      var panel = document.createElement('div');
      panel.className = img.className + ' cover-fallback';
      var span = document.createElement('span');
      span.textContent = img.getAttribute('data-fallback');
      panel.appendChild(span);
      panel.setAttribute('role', 'img');
      panel.setAttribute('aria-label', img.alt);
      img.replaceWith(panel);
    }, true);
  }

  /* ---------- Admin: live image preview ---------- */
  function initImagePreview() {
    $all('[data-image-preview]').forEach(function (input) {
      var img = document.getElementById(input.getAttribute('data-image-preview'));
      var base = img.getAttribute('data-base') || '';
      var timer;
      input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
          var v = input.value.trim();
          img.src = v === '' ? base + 'assets/img/games/fallback.svg' : (/^https?:\/\//i.test(v) ? v : base + v.replace(/^\/+/, ''));
        }, 250);
      });
      img.addEventListener('error', function () { img.src = base + 'assets/img/games/fallback.svg'; });
    });
  }

  /* ---------- Selects that apply as soon as they change ---------- */
  function initAutosubmit() {
    $all('[data-autosubmit]').forEach(function (el) {
      el.addEventListener('change', function () { el.form.submit(); });
    });
  }

  /* ---------- Entrance: fade out before leaving ---------- */
  function initFadeLinks() {
    $all('[data-fade-link]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        if (reduceMotion || e.metaKey || e.ctrlKey || e.shiftKey) return;
        e.preventDefault();
        var page = $('.entrance');
        if (page) page.classList.add('is-leaving');
        setTimeout(function () { window.location.href = link.href; }, 400);
      });
    });
    window.addEventListener('pageshow', function () {
      var page = $('.entrance');
      if (page) page.classList.remove('is-leaving');
    });
  }

  onReady(function () {
    initNav();
    initAccountMenu();
    initPasswordToggles();
    initPasswordRules();
    initValidation();
    initConfirm();
    initLoadingButtons();
    initFilters();
    initNews();
    initDialogs();
    initSteppers();
    initEditToggles();
    initAlerts();
    initImagePreview();
    initFadeLinks();
    initAutosubmit();
  });
  initImageFallbacks();
})();
