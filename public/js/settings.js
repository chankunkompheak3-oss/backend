(async function () {
  const forms = { profile: document.getElementById('profileForm'), password: document.getElementById('passwordForm'), system: document.getElementById('systemForm') };
  const fill = (form, obj) => { for (const el of form.elements) if (el.name && el.name in obj) el.value = obj[el.name] ?? ''; };

  try {
    const { data } = await api('settings.php');
    fill(forms.profile, data.profile);
    fill(forms.system, data.system);
    if (!data.is_admin) for (const el of forms.system.elements) el.disabled = true;
  } catch (e) { toast(e.message, 'error'); }

  function wire(name, action, after) {
    forms[name].addEventListener('submit', async e => {
      e.preventDefault(); clearErrors(forms[name]);
      const body = {};
      for (const el of forms[name].elements) if (el.name && !el.disabled) body[el.name] = el.value;
      const btn = forms[name].querySelector('[type=submit]'); setBusy(btn, true);
      try { const j = await api('settings.php?action=' + action, { method: 'PUT', body }); toast(j.message); after?.(); }
      catch (err) { showErrors(forms[name], err.errors); toast(err.message, 'error'); }
      finally { setBusy(btn, false); }
    });
  }
  wire('profile', 'profile');
  wire('password', 'password', () => { forms.password.reset(); if (new URLSearchParams(location.search).has('force')) location.href = document.querySelector('meta[name="base-url"]').content + '/dashboard.php'; });
  wire('system', 'system', () => setTimeout(() => location.reload(), 800));   // reload so currency/tax refresh everywhere
})();