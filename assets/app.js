(() => {
  const form = document.querySelector('#entry-form');
  const parking = document.querySelector('#entry-form-parking');
  if (!form || !parking) return;
  const field = name => form.elements.namedItem(name);
  const voiceControls = form.querySelector('.voice-controls');
  const voiceButton = form.querySelector('.voice-entry');
  const voiceLanguage = form.querySelector('.voice-language');
  const voiceStatus = form.querySelector('.voice-status');
  const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  let recognition = null;
  let activeHost = null;
  const setVoiceStatus = message => { if (voiceStatus) voiceStatus.textContent = message; };
  const stopRecognition = () => {
    if (!recognition) return;
    recognition.abort();
    recognition = null;
    voiceButton?.classList.remove('is-listening');
  };
  const closeForm = () => {
    stopRecognition();
    setVoiceStatus('');
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
  if (Recognition && voiceControls && voiceButton && voiceLanguage && voiceStatus && window.BPVoiceEntry) {
    voiceControls.hidden = false;
    if ((navigator.language || '').toLowerCase().startsWith('hi')) voiceLanguage.value = 'hi-IN';
    voiceButton.addEventListener('click', () => {
      stopRecognition();
      recognition = new Recognition();
      const currentRecognition = recognition;
      recognition.lang = voiceLanguage.value;
      recognition.continuous = false;
      recognition.interimResults = false;
      recognition.maxAlternatives = 3;
      recognition.onstart = () => {
        voiceButton.classList.add('is-listening');
        setVoiceStatus(voiceLanguage.value === 'hi-IN'
          ? 'सुन रहा है… कहें “132 बाय 78, पल्स 67”।'
          : 'Listening… Say “132 over 78 pulse 67”.');
      };
      recognition.onresult = event => {
        const alternatives = Array.from(event.results[0] || []);
        const reading = alternatives.map(item => window.BPVoiceEntry.parse(item.transcript)).find(Boolean);
        if (!reading) {
          setVoiceStatus('Could not identify three sensible values. Please try again or enter them manually.');
          return;
        }
        field('systolic').value = reading.systolic;
        field('diastolic').value = reading.diastolic;
        field('pulse').value = reading.pulse;
        setVoiceStatus('Values entered. Review them, then tap Save.');
        field('systolic').focus();
      };
      recognition.onerror = event => {
        const messages = {
          'not-allowed': 'Microphone permission was denied. Allow access or enter the values manually.',
          'service-not-allowed': 'Speech recognition is unavailable. Please enter the values manually.',
          'no-speech': 'No speech was detected. Please try again.',
          network: 'Speech recognition could not connect. Check your connection or enter values manually.'
        };
        setVoiceStatus(messages[event.error] || 'Voice entry did not work. Please try again or enter the values manually.');
      };
      recognition.onend = () => {
        if (recognition !== currentRecognition) return;
        voiceButton.classList.remove('is-listening');
        recognition = null;
      };
      try {
        recognition.start();
      } catch {
        setVoiceStatus('Voice entry could not start. Please try again or enter the values manually.');
        stopRecognition();
      }
    });
  }
  document.querySelectorAll('.reading-action').forEach(button => button.addEventListener('click', () => {
    const host = button.closest('.reading-content');
    if (!host) return;
    openForm({date: button.dataset.date, period: button.dataset.period, slot: button.dataset.slot, s: button.dataset.s, d: button.dataset.d, pulse: button.dataset.pulse, time: button.dataset.time}, button.dataset.edit === '1', host);
  }));
  form.querySelector('.cancel-entry').addEventListener('click', closeForm);
})();
