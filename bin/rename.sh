#!/usr/bin/env bash
#
# Rename the Starter Plugin boilerplate for your own project.
#
# Usage:
#   bash bin/rename.sh                                   # Interactive
#   bash bin/rename.sh my-plugin MyPlugin                # Non-interactive
#   bash bin/rename.sh --dry-run my-plugin MyPlugin      # Preview only
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"

DRY_RUN=false

# --- Parse arguments -----------------------------------------------------------

if [[ "${1:-}" == "--dry-run" ]]; then
	DRY_RUN=true
	shift
fi

if [[ $# -ge 2 ]]; then
	NEW_SLUG="$1"
	NEW_NAMESPACE="$2"
elif [[ $# -eq 0 ]]; then
	read -rp "New plugin slug (e.g. my-awesome-plugin): " NEW_SLUG
	read -rp "New PHP namespace (e.g. MyAwesomePlugin): " NEW_NAMESPACE
else
	echo "Usage: bash bin/rename.sh [--dry-run] <slug> <Namespace>" >&2
	echo "       bash bin/rename.sh                  # interactive" >&2
	exit 1
fi

# --- Validate inputs -----------------------------------------------------------

if [[ ! "$NEW_SLUG" =~ ^[a-z][a-z0-9]*(-[a-z0-9]+)*$ ]]; then
	echo "Error: Slug must be lowercase-hyphenated (e.g. 'my-plugin')." >&2
	exit 1
fi

if [[ ${#NEW_SLUG} -lt 3 ]]; then
	echo "Error: Slug must be at least 3 characters." >&2
	exit 1
fi

if [[ ! "$NEW_NAMESPACE" =~ ^[A-Z][a-zA-Z0-9]*$ ]]; then
	echo "Error: Namespace must be PascalCase with no separators (e.g. 'MyPlugin')." >&2
	exit 1
fi

# --- Derive naming variants ----------------------------------------------------

OLD_SLUG="starter-plugin"
OLD_NAMESPACE="StarterPlugin"
OLD_PREFIX="starter_plugin_"
OLD_GLOBAL="starter_plugin"
OLD_CONSTANT="STARTER_PLUGIN_"
OLD_CONSTANT_BASE="STARTER_PLUGIN"

NEW_PREFIX="${NEW_SLUG//-/_}_"
NEW_GLOBAL="${NEW_SLUG//-/_}"
NEW_CONSTANT="$(echo "${NEW_GLOBAL}" | tr '[:lower:]' '[:upper:]')_"
NEW_CONSTANT_BASE="${NEW_CONSTANT%_}"

echo ""
echo "Rename plan:"
echo "  Slug:            ${OLD_SLUG} → ${NEW_SLUG}"
echo "  Namespace:       ${OLD_NAMESPACE} → ${NEW_NAMESPACE}"
echo "  Prefix:          ${OLD_PREFIX} → ${NEW_PREFIX}"
echo "  Constant prefix: ${OLD_CONSTANT} → ${NEW_CONSTANT}"
echo "  Global var:      ${OLD_GLOBAL} → ${NEW_GLOBAL}"
echo ""

if $DRY_RUN; then
	echo "[DRY RUN] No files will be modified."
	echo ""
fi

# --- Collect files to process --------------------------------------------------

collect_files() {
	find "$PLUGIN_DIR" \
		-not -path "$PLUGIN_DIR/.git/*" \
		-not -path "$PLUGIN_DIR/node_modules/*" \
		-not -path "$PLUGIN_DIR/vendor/*" \
		-not -path "$PLUGIN_DIR/assets/build/*" \
		-not -name "rename.sh" \
		-type f \
		\( -name "*.php" -o -name "*.json" -o -name "*.xml.dist" \
		   -o -name "*.neon" -o -name "*.txt" -o -name "*.md" \
		   -o -name "*.yml" -o -name "*.yaml" -o -name "*.js" \
		   -o -name "*.css" -o -name "*.sh" -o -name ".distignore" \
		   -o -name ".claudeignore" -o -name ".editorconfig" \
		   -o -name ".wp-env.json" \)
}

# --- Perform replacements in correct order -------------------------------------
#
# Order matters! Most-specific first to avoid partial-match corruption.
# E.g. replacing slug before prefix would turn "starter_plugin_" into
# "my-plugin_" instead of "my_plugin_".

REPLACEMENTS=(
	"${OLD_CONSTANT}|${NEW_CONSTANT}"
	"${OLD_CONSTANT_BASE}|${NEW_CONSTANT_BASE}"
	"${OLD_NAMESPACE}|${NEW_NAMESPACE}"
	"${OLD_PREFIX}|${NEW_PREFIX}"
	"${OLD_GLOBAL}|${NEW_GLOBAL}"
	"${OLD_SLUG}|${NEW_SLUG}"
)

REPLACED_COUNT=0

while IFS= read -r file; do
	changed=false

	for pair in "${REPLACEMENTS[@]}"; do
		old="${pair%%|*}"
		new="${pair##*|}"

		if grep -qF "$old" "$file"; then
			changed=true
			if ! $DRY_RUN; then
				if [[ "$(uname)" == "Darwin" ]]; then
					sed -i '' "s|${old}|${new}|g" "$file"
				else
					sed -i "s|${old}|${new}|g" "$file"
				fi
			fi
		fi
	done

	if $changed; then
		REPLACED_COUNT=$((REPLACED_COUNT + 1))
		rel="${file#"$PLUGIN_DIR"/}"
		if $DRY_RUN; then
			echo "  [would change] $rel"
		else
			echo "  [changed] $rel"
		fi
	fi
done < <(collect_files)

echo ""
echo "String replacements: ${REPLACED_COUNT} file(s)."

# --- Rename main plugin file ---------------------------------------------------

OLD_MAIN="${PLUGIN_DIR}/${OLD_SLUG}.php"
NEW_MAIN="${PLUGIN_DIR}/${NEW_SLUG}.php"

if [[ -f "$OLD_MAIN" ]] && [[ "$OLD_MAIN" != "$NEW_MAIN" ]]; then
	if $DRY_RUN; then
		echo "  [would rename] ${OLD_SLUG}.php → ${NEW_SLUG}.php"
	else
		mv "$OLD_MAIN" "$NEW_MAIN"
		echo "  [renamed] ${OLD_SLUG}.php → ${NEW_SLUG}.php"
	fi
fi

# --- Rename plugin directory (if it still uses the old slug) -------------------

if [[ "$(basename "$PLUGIN_DIR")" == "$OLD_SLUG" ]]; then
	NEW_PLUGIN_DIR="$(dirname "$PLUGIN_DIR")/${NEW_SLUG}"
	if $DRY_RUN; then
		echo "  [would rename dir] ${OLD_SLUG}/ → ${NEW_SLUG}/"
	else
		mv "$PLUGIN_DIR" "$NEW_PLUGIN_DIR"
		echo "  [renamed dir] ${OLD_SLUG}/ → ${NEW_SLUG}/"
	fi
fi

# --- Summary -------------------------------------------------------------------

echo ""
if $DRY_RUN; then
	echo "Dry run complete. Re-run without --dry-run to apply changes."
else
	echo "Rename complete!"
	echo ""
	echo "Next steps:"
	echo "  1. Review the changes: git diff"
	echo "  2. Install deps:      composer install && npm ci"
	echo "  3. Verify:            composer check && npm run build"
	echo "  4. Commit:            git add -A && git commit -m 'Rename to ${NEW_SLUG}'"
fi
