#!/usr/bin/env sh
# Empaqueta el tema y el plugin como .zip listos para subir a WordPress.
set -e
cd "$(dirname "$0")"
mkdir -p dist
rm -f dist/*.zip
(cd plugin && zip -qr ../dist/vacantespty-core.zip vacantespty-core -x '*.DS_Store')
(cd theme && zip -qr ../dist/vacantespty-tema.zip vacantespty -x '*.DS_Store')
ls -lh dist
