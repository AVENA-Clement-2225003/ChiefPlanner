import { describe, expect, it } from 'vitest';
import { initialsOf } from './userService';

describe('initialsOf', () => {
  it('keeps the first letter of the first two words, in capitals', () => {
    expect(initialsOf('alice martin dupont')).toBe('AM');
  });

  it('works with a single word', () => {
    expect(initialsOf('Alice')).toBe('A');
  });

  it('falls back to a placeholder for an empty name', () => {
    expect(initialsOf('   ')).toBe('--');
  });
});
