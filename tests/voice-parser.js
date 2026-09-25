const assert = require('node:assert/strict');
const {parse} = require('../assets/voice-entry.js');

const reading = {systolic: 132, diastolic: 78, pulse: 67};
assert.deepEqual(parse('132 over 78 pulse 67'), reading);
assert.deepEqual(parse('My blood pressure is 132 over 78 and my pulse is 67'), reading);
assert.deepEqual(parse('Systolic 132, diastolic 78, pulse 67'), reading);
assert.deepEqual(parse('one thirty two over seventy eight pulse sixty seven'), reading);
assert.deepEqual(parse('one hundred and thirty two over seventy-eight pulse sixty-seven'), reading);
assert.deepEqual(parse('मेरा बीपी 132 बाय 78 है, पल्स 67'), reading);
assert.deepEqual(parse('बीपी १३२ ऊपर ७८ और नाड़ी ६७'), reading);
assert.deepEqual(parse('एक सौ बत्तीस ऊपर अठहत्तर नाड़ी सड़सठ'), reading);
assert.deepEqual(parse('BP 132 over 78, pulse 67 hai'), reading);
assert.equal(parse('132 over 78'), null);
assert.equal(parse('78 over 132 pulse 67'), null);
assert.equal(parse('500 over 78 pulse 67'), null);

console.log('PASS: English, Hindi, and Hinglish voice transcript parsing');
