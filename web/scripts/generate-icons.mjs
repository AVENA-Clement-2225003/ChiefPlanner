// Draws the app icons into public/: the toque of `components/layout/Logo.tsx` in white on the accent colour.
// Pure Node, no image library. Run `node scripts/generate-icons.mjs` and commit the files.
import { mkdirSync, writeFileSync } from 'node:fs';
import { deflateSync } from 'node:zlib';

const ACCENT = [0x2a, 0x8f, 0xe0];
const WHITE = [0xff, 0xff, 0xff];

// Same geometry as Logo.tsx (a 64-unit square, the toque occupies about 41 × 44 of it).
const BUMPS = [
  { cx: 22, cy: 27, r: 10.5 },
  { cx: 32, cy: 21, r: 12.5 },
  { cx: 42, cy: 27, r: 10.5 },
];
const BAND = { x: 18, y: 33, width: 28, height: 19, radius: 4 };
const DAY_DOTS = Array.from({ length: 7 }, (_, day) => ({ cx: 21 + (day * 22) / 6, cy: 43, r: 1.5 }));
const MARK_CENTRE = { x: 32, y: 30.25 };
const MARK_HEIGHT = 43.5;
const SUPERSAMPLING = 4;

const inCircle = (x, y, { cx, cy, r }) => (x - cx) ** 2 + (y - cy) ** 2 <= r ** 2;

function inBand(x, y) {
  const { x: left, y: top, width, height, radius } = BAND;
  if (x < left || x > left + width || y < top || y > top + height) {
    return false;
  }
  const cornerX = x < left + radius ? left + radius : x > left + width - radius ? left + width - radius : x;
  const cornerY = y < top + radius ? top + radius : y > top + height - radius ? top + height - radius : y;
  return (x - cornerX) ** 2 + (y - cornerY) ** 2 <= radius ** 2;
}

function inToque(x, y) {
  const solid = BUMPS.some((bump) => inCircle(x, y, bump)) || inBand(x, y);
  return solid && !DAY_DOTS.some((dot) => inCircle(x, y, dot));
}

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

/** `markShare` is the part of the icon height the toque takes: smaller for maskable icons, which are cropped. */
function drawIcon(size, markShare) {
  const scale = (size * markShare) / MARK_HEIGHT;
  const raw = Buffer.alloc((size * 3 + 1) * size);

  for (let y = 0; y < size; y++) {
    const row = y * (size * 3 + 1);
    raw[row] = 0;
    for (let x = 0; x < size; x++) {
      let covered = 0;
      for (let sy = 0; sy < SUPERSAMPLING; sy++) {
        for (let sx = 0; sx < SUPERSAMPLING; sx++) {
          const markX = MARK_CENTRE.x + (x + (sx + 0.5) / SUPERSAMPLING - size / 2) / scale;
          const markY = MARK_CENTRE.y + (y + (sy + 0.5) / SUPERSAMPLING - size / 2) / scale;
          covered += inToque(markX, markY) ? 1 : 0;
        }
      }
      const share = covered / SUPERSAMPLING ** 2;
      const pixel = ACCENT.map((channel, index) => Math.round(channel + (WHITE[index] - channel) * share));
      raw.set(pixel, row + 1 + x * 3);
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
writeFileSync('public/pwa-192.png', drawIcon(192, 0.62));
writeFileSync('public/pwa-512.png', drawIcon(512, 0.62));
writeFileSync('public/pwa-maskable-512.png', drawIcon(512, 0.5));
writeFileSync('public/favicon.png', drawIcon(64, 0.74));
console.log('Icons written to public/');
