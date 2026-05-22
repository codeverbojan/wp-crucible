#!/usr/bin/env bash
#
# Build a distribution zip for WordPress.org submission.
#
# Usage: bash bin/build-zip.sh
#
set -euo pipefail

PLUGIN_SLUG="starter-plugin"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"
BUILD_DIR="$(mktemp -d)"
trap 'rm -rf "$BUILD_DIR"' EXIT
DISTIGNORE="$PLUGIN_DIR/.distignore"

# Read version from the main plugin file header.
VERSION="$(grep -m1 'Version:' "$PLUGIN_DIR/$PLUGIN_SLUG.php" | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]')"

if [ -z "$VERSION" ]; then
	echo "Error: Could not read version from $PLUGIN_SLUG.php" >&2
	exit 1
fi

ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"

echo "Building $ZIP_NAME ..."

# 1. Compile assets.
echo "→ Running npm build ..."
cd "$PLUGIN_DIR"
npm run build --silent

# 2. Sync files to a clean temp directory, respecting .distignore.
echo "→ Syncing files (excluding dev artifacts) ..."
rsync -a --exclude-from="$DISTIGNORE" "$PLUGIN_DIR/" "$BUILD_DIR/$PLUGIN_SLUG/"

# 3. Create the zip.
echo "→ Creating zip ..."
cd "$BUILD_DIR"
zip -rq "$PLUGIN_DIR/$ZIP_NAME" "$PLUGIN_SLUG/"

# 4. Validate the zip contents.
echo "→ Validating zip ..."
ERRORS=0
ZIP_CONTENTS="$(unzip -l "$PLUGIN_DIR/$ZIP_NAME")"

if ! echo "$ZIP_CONTENTS" | grep -q "$PLUGIN_SLUG/$PLUGIN_SLUG.php"; then
	echo "  ✗ Missing main plugin file" >&2
	ERRORS=1
fi

if ! echo "$ZIP_CONTENTS" | grep -q "$PLUGIN_SLUG/assets/build/"; then
	echo "  ✗ Missing assets/build/ directory" >&2
	ERRORS=1
fi

if ! echo "$ZIP_CONTENTS" | grep -q "$PLUGIN_SLUG/assets/src/"; then
	echo "  ✗ Missing assets/src/ directory (required by wp.org)" >&2
	ERRORS=1
fi

if echo "$ZIP_CONTENTS" | grep -q "node_modules/"; then
	echo "  ✗ node_modules/ leaked into zip" >&2
	ERRORS=1
fi

if echo "$ZIP_CONTENTS" | grep -q "vendor/"; then
	echo "  ✗ vendor/ leaked into zip" >&2
	ERRORS=1
fi

if [ "$ERRORS" -ne 0 ]; then
	echo "Build failed — zip validation errors." >&2
	rm -f "$PLUGIN_DIR/$ZIP_NAME"
	exit 1
fi

echo "✓ $ZIP_NAME created successfully ($(du -h "$PLUGIN_DIR/$ZIP_NAME" | cut -f1) compressed)."
