// Draws the app icons (a plate on the accent colour) into public/. Pure Node: no image library needed.
// Run with `node scripts/generate-icons.mjs` and commit the PNGs.
import { mkdirSync, writeFileSync } from 'node:fs';
import { deflateSync } from 'node:zlib';

const ACCENT = [0x2a, 0x8f, 0xe0];
const WHITE = [0xff, 0xff, 0xff];

function crc32(buffer) {
  let crc = ~0;
  for (const byte of buffer) {
    crc ^= byte;
    for (let bit = 0; bit < 8; bit++) {
      crc = (crc >>> 1) ^ (0xedb88320 & -(crc & 1));
    }
  }
  return ~crc >>> 0;
}

function chunk(type, data) {
  const body = Buffer.concat([Buffer.from(type), data]);
  const length = Buffer.alloc(4);
  length.writeUInt32BE(data.length);
  const checksum = Buffer.alloc(4);
  checksum.writeUInt32BE(crc32(body));
  return Buffer.concat([length, body, checksum]);
}

/** A plate (white ring with a filled centre) centred on a square background; `safeZone` shrinks it for maskable icons. */
function drawIcon(size, safeZone) {
  const raw = Buffer.alloc((size * 3 + 1) * size);
  const centre = size / 2;
  const outer = size * 0.34 * safeZone;
  const inner = size * 0.24 * safeZone;
  const dot = size * 0.1 * safeZone;

  for (let y = 0; y < size; y++) {
    const row = y * (size * 3 + 1);
    raw[row] = 0;
    for (let x = 0; x < size; x++) {
      const distance = Math.hypot(x + 0.5 - centre, y + 0.5 - centre);
      const onRing = distance <= outer && distance >= inner;
      const onDot = distance <= dot;
      const [r, g, b] = onRing || onDot ? WHITE : ACCENT;
      raw.set([r, g, b], row + 1 + x * 3);
    }
  }

  const header = Buffer.alloc(13);
  header.writeUInt32BE(size, 0);
  header.writeUInt32BE(size, 4);
  header.set([8, 2, 0, 0, 0], 8);

  return Buffer.concat([
    Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]),
    chunk('IHDR', header),
    chunk('IDAT', deflateSync(raw)),
    chunk('IEND', Buffer.alloc(0)),
  ]);
}

mkdirSync('public', { recursive: true });
writeFileSync('public/pwa-192.png', drawIcon(192, 1));
writeFileSync('public/pwa-512.png', drawIcon(512, 1));
writeFileSync('public/pwa-maskable-512.png', drawIcon(512, 0.8));
writeFileSync('public/favicon.png', drawIcon(64, 1));
console.log('Icons written to public/');
