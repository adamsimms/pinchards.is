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

function copyDir(srcDir, destDir) {
  ensureDir(destDir);
  for (const entry of fs.readdirSync(srcDir, { withFileTypes: true })) {
    const src = path.join(srcDir, entry.name);
    const dest = path.join(destDir, entry.name);
    if (entry.isDirectory()) {
      copyDir(src, dest);
    } else {
      copyFile(src, dest);
    }
  }
}

function removeIfExists(target) {
  if (fs.existsSync(target)) {
    fs.rmSync(target, { recursive: true, force: true });
  }
}

console.log('Vendoring frontend dependencies into vendor/...\n');

for (const legacy of ['jquery-ui', 'exifjs', 'lightbox', 'magnific-popup', 'scrollreveal']) {
  removeIfExists(path.join(root, 'vendor', legacy));
}

copyFile(
  path.join(root, 'node_modules/jquery/dist/jquery.min.js'),
  path.join(root, 'vendor/jquery/jquery.js')
);

copyFile(
  path.join(root, 'node_modules/bootstrap/dist/css/bootstrap.min.css'),
  path.join(root, 'vendor/bootstrap/css/bootstrap.css')
);
copyFile(
  path.join(root, 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'),
  path.join(root, 'vendor/bootstrap/js/bootstrap.bundle.js')
);
removeIfExists(path.join(root, 'vendor/bootstrap/js/bootstrap.js'));
removeIfExists(path.join(root, 'vendor/bootstrap/fonts'));

removeIfExists(path.join(root, 'vendor/font-awesome'));
copyDir(
  path.join(root, 'node_modules/font-awesome'),
  path.join(root, 'vendor/font-awesome')
);

removeIfExists(path.join(root, 'vendor/slick'));
copyDir(
  path.join(root, 'node_modules/slick-carousel/slick'),
  path.join(root, 'vendor/slick')
);

console.log('\nDone.');
