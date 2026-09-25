(() => {
  const small = {
    zero: 0, oh: 0, one: 1, two: 2, three: 3, four: 4, five: 5,
    six: 6, seven: 7, eight: 8, nine: 9, ten: 10, eleven: 11,
    twelve: 12, thirteen: 13, fourteen: 14, fifteen: 15, sixteen: 16,
    seventeen: 17, eighteen: 18, nineteen: 19
  };
  const tens = {
    twenty: 20, thirty: 30, forty: 40, fifty: 50,
    sixty: 60, seventy: 70, eighty: 80, ninety: 90
  };

  const spokenNumber = phrase => {
    const digit = phrase.match(/\b\d{1,3}\b/);
    if (digit) return Number(digit[0]);

    const words = phrase.toLowerCase().replace(/-/g, ' ').split(/\s+/)
      .filter(word => word !== 'and' && (word in small || word in tens || word === 'hundred'));
    if (!words.length) return null;

    if (words.length >= 2 && small[words[0]] >= 1 && small[words[0]] <= 9 && words[1] in tens) {
      return (small[words[0]] * 100) + tens[words[1]] + (small[words[2]] || 0);
    }

    let value = 0;
    for (const word of words) {
      if (word === 'hundred') value *= 100;
      else value += small[word] ?? tens[word];
    }
    return value;
  };

  const validReading = ([systolic, diastolic, pulse]) =>
    systolic >= 30 && systolic <= 300 &&
    diastolic >= 30 && diastolic <= 300 &&
    systolic > diastolic && pulse >= 25 && pulse <= 250;

  const parse = transcript => {
    const text = String(transcript || '').toLowerCase()
      .replace(/[,.]/g, ' ')
      .replace(/heart\s*rate/g, 'pulse')
      .replace(/blood\s*pressure/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();

    const labelled = text.match(/^(.+?)\s+(?:over|by|on|\/|upon)\s+(.+?)\s+(?:pulse|pulserate)\s+(.+)$/);
    let values = labelled ? labelled.slice(1).map(spokenNumber) : null;

    if (!values || values.some(value => value === null)) {
      const digits = [...text.matchAll(/\b\d{1,3}\b/g)].map(match => Number(match[0]));
      values = digits.length === 3 ? digits : null;
    }

    if (!values || !validReading(values)) return null;
    return {systolic: values[0], diastolic: values[1], pulse: values[2]};
  };

  const api = {parse};
  if (typeof window !== 'undefined') window.BPVoiceEntry = api;
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
})();
