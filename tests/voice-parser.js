const assert = require('node:assert/strict');
const {parse} = require('../assets/voice-entry.js');

assert.deepEqual(parse('132 over 78 pulse 67'), {systolic: 132, diastolic: 78, pulse: 67});
assert.deepEqual(parse('132 by 78 heart rate 67'), {systolic: 132, diastolic: 78, pulse: 67});
assert.deepEqual(parse('one thirty two over seventy eight pulse sixty seven'), {systolic: 132, diastolic: 78, pulse: 67});
assert.deepEqual(parse('one hundred and thirty two over seventy-eight pulse sixty-seven'), {systolic: 132, diastolic: 78, pulse: 67});
assert.equal(parse('132 over 78'), null);
assert.equal(parse('78 over 132 pulse 67'), null);
assert.equal(parse('500 over 78 pulse 67'), null);

console.log('PASS: voice transcript parsing');
