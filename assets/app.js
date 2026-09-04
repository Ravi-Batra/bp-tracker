(() => {
  const form = document.querySelector('#entry-form');
  const parking = document.querySelector('#entry-form-parking');
  if (!form || !parking) return;
  const field = name => form.elements.namedItem(name);
  let activeHost = null;
  const closeForm = () => {
    form.hidden = true;
    if (activeHost) activeHost.classList.remove('is-editing');
    parking.append(form);
    activeHost = null;
  };
  const openForm = (data, edit, host) => {
    closeForm();
    field('date').value = data.date;
    field('time').value = data.time || new Date().toTimeString().slice(0, 5);
    field('period').value = data.period;
    field('slot').value = data.slot;
    field('systolic').value = data.s || '';
    field('diastolic').value = data.d || '';
    field('pulse').value = data.pulse || '';
    field('edit').value = edit ? '1' : '0';
    field('original_date').value = edit ? data.date : '';
    field('original_period').value = edit ? data.period : '';
    field('original_slot').value = edit ? data.slot : '';
    form.querySelector('#save-button').textContent = 'Save';
    activeHost = host;
    activeHost.classList.add('is-editing');
    activeHost.append(form);
    form.hidden = false;
    field('systolic').focus();
  };
  document.querySelectorAll('.reading-action').forEach(button => button.addEventListener('click', () => {
    const host = button.closest('.reading-content');
    if (!host) return;
    openForm({date: button.dataset.date, period: button.dataset.period, slot: button.dataset.slot, s: button.dataset.s, d: button.dataset.d, pulse: button.dataset.pulse, time: button.dataset.time}, button.dataset.edit === '1', host);
  }));
  form.querySelector('.cancel-entry').addEventListener('click', closeForm);
})();
