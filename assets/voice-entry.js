(() => {
  const englishNumbers = {
    zero: 0, oh: 0, one: 1, two: 2, three: 3, four: 4, five: 5,
    six: 6, seven: 7, eight: 8, nine: 9, ten: 10, eleven: 11,
    twelve: 12, thirteen: 13, fourteen: 14, fifteen: 15, sixteen: 16,
    seventeen: 17, eighteen: 18, nineteen: 19, twenty: 20, thirty: 30,
    forty: 40, fifty: 50, sixty: 60, seventy: 70, eighty: 80, ninety: 90
  };
  const hindiNumberNames = [
    'शून्य','एक','दो','तीन','चार','पाँच','छह','सात','आठ','नौ','दस','ग्यारह','बारह','तेरह','चौदह','पंद्रह','सोलह','सत्रह','अठारह','उन्नीस',
    'बीस','इक्कीस','बाईस','तेईस','चौबीस','पच्चीस','छब्बीस','सत्ताईस','अट्ठाईस','उनतीस','तीस','इकतीस','बत्तीस','तैंतीस','चौंतीस','पैंतीस','छत्तीस','सैंतीस','अड़तीस','उनतालीस',
    'चालीस','इकतालीस','बयालीस','तैंतालीस','चवालीस','पैंतालीस','छियालीस','सैंतालीस','अड़तालीस','उनचास','पचास','इक्यावन','बावन','तिरपन','चौवन','पचपन','छप्पन','सत्तावन','अट्ठावन','उनसठ',
    'साठ','इकसठ','बासठ','तिरसठ','चौंसठ','पैंसठ','छियासठ','सड़सठ','अड़सठ','उनहत्तर','सत्तर','इकहत्तर','बहत्तर','तिहत्तर','चौहत्तर','पचहत्तर','छिहत्तर','सतहत्तर','अठहत्तर','उन्नासी',
    'अस्सी','इक्यासी','बयासी','तिरासी','चौरासी','पचासी','छियासी','सतासी','अट्ठासी','नवासी','नब्बे','इक्यानवे','बानवे','तिरानवे','चौरानवे','पंचानवे','छियानवे','सत्तानवे','अट्ठानवे','निन्यानवे'
  ];
  const hindiNumbers = Object.fromEntries(hindiNumberNames.map((word, value) => [word, value]));
  Object.assign(hindiNumbers, {'पांच': 5, 'छः': 6, 'पन्द्रह': 15});
  const devanagariDigits = '०१२३४५६७८९';

  const normalizeDigits = text => text.replace(/[०-९]/g, digit => devanagariDigits.indexOf(digit));

  const spokenNumber = phrase => {
    const normalized = normalizeDigits(phrase.toLowerCase()).replace(/-/g, ' ');
    const digit = normalized.match(/(?<!\d)\d{1,3}(?!\d)/);
    if (digit) return Number(digit[0]);

    const words = normalized.split(/\s+/).filter(word =>
      word !== 'and' && word !== 'और' &&
      (word in englishNumbers || word in hindiNumbers || word === 'hundred' || word === 'सौ')
    );
    if (!words.length) return null;

    const valueOf = word => englishNumbers[word] ?? hindiNumbers[word];
    const hundredAt = words.findIndex(word => word === 'hundred' || word === 'सौ');
    if (hundredAt >= 0) {
      const hundreds = hundredAt === 0 ? 1 : valueOf(words[hundredAt - 1]);
      if (!Number.isInteger(hundreds)) return null;
      return (hundreds * 100) + words.slice(hundredAt + 1).reduce((total, word) => total + valueOf(word), 0);
    }

    const first = valueOf(words[0]);
    const second = valueOf(words[1]);
    if (words.length >= 2 && first >= 1 && first <= 9 && second >= 20) {
      return (first * 100) + words.slice(1).reduce((total, word) => total + valueOf(word), 0);
    }
    return words.reduce((total, word) => total + valueOf(word), 0);
  };

  const validReading = ([systolic, diastolic, pulse]) =>
    systolic >= 30 && systolic <= 300 &&
    diastolic >= 30 && diastolic <= 300 &&
    systolic > diastolic && pulse >= 25 && pulse <= 250;

  const parse = transcript => {
    const text = normalizeDigits(String(transcript || '')).toLowerCase()
      .replace(/[,.।]/g, ' ')
      .replace(/heart\s*rate|हार्ट\s*रेट|हृदय\s*गति|नाड़ी|पल्स/g, 'pulse')
      .replace(/blood\s*pressure|ब्लड\s*प्रेशर|बी\s*पी|बीपी/g, ' ')
      .replace(/ऊपर|ओवर|बाय|बटा/g, ' over ')
      .replace(/\s+/g, ' ')
      .trim();

    const labelled = text.match(/^(.+?)\s+(?:over|by|on|\/|upon)\s+(.+?)\s+pulse\s+(.+)$/);
    let values = labelled ? labelled.slice(1).map(spokenNumber) : null;

    if (!values || values.some(value => value === null)) {
      const digits = [...text.matchAll(/(?<!\d)\d{1,3}(?!\d)/g)].map(match => Number(match[0]));
      values = digits.length === 3 ? digits : null;
    }

    if (!values || !validReading(values)) return null;
    return {systolic: values[0], diastolic: values[1], pulse: values[2]};
  };

  const api = {parse};
  if (typeof window !== 'undefined') window.BPVoiceEntry = api;
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
})();
