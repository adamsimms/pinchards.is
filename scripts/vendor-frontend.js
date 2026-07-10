#!/usr/bin/env node
'use strict';

const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function copyFile(src, dest) {
  ensureDir(path.dirname(dest));
  fs.copyFileSync(src, dest);
  console.log(`  ${path.relative(root, dest)}`);
}

function removeIfExists(target) {
  if (fs.existsSync(target)) {
    fs.rmSync(target, { recursive: true, force: true });
  }
}

console.log('Vendoring frontend dependencies into vendor/...\n');

for (const legacy of ['jquery', 'jquery-ui', 'font-awesome', 'slick', 'exifjs', 'lightbox', 'magnific-popup', 'scrollreveal']) {
  removeIfExists(path.join(root, 'vendor', legacy));
}

removeIfExists(path.join(root, 'vendor/bootstrap'));
ensureDir(path.join(root, 'vendor/bootstrap/css'));
copyFile(
  path.join(root, 'node_modules/bootstrap/dist/css/bootstrap.min.css'),
  path.join(root, 'vendor/bootstrap/css/bootstrap.css')
);

removeIfExists(path.join(root, 'vendor/gsap'));
ensureDir(path.join(root, 'vendor/gsap'));
copyFile(
  path.join(root, 'node_modules/gsap/dist/gsap.min.js'),
  path.join(root, 'vendor/gsap/gsap.min.js')
);
copyFile(
  path.join(root, 'node_modules/gsap/dist/ScrollTrigger.min.js'),
  path.join(root, 'vendor/gsap/ScrollTrigger.min.js')
);

console.log('\nDone.');
