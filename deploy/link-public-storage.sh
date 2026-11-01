#!/usr/bin/env bash
set -eu

APP_PATH="$HOME/dmentaiindonesia-app"
PUBLIC_PATH="$HOME/public_html/dmentaiindonesia.com"
LINK_PATH="$PUBLIC_PATH/storage"
TARGET_PATH="$APP_PATH/storage/app/public"

mkdir -p "$TARGET_PATH"

if [ -L "$LINK_PATH" ]; then
    CURRENT_TARGET="$(readlink "$LINK_PATH")"
    if [ "$CURRENT_TARGET" != "$TARGET_PATH" ]; then
        echo "Refusing to replace an existing storage symlink." >&2
        exit 1
    fi
elif [ -e "$LINK_PATH" ]; then
    echo "A storage path already exists. Back it up and move it before deploying." >&2
    exit 1
else
    /bin/ln -s "$TARGET_PATH" "$LINK_PATH"
fi
