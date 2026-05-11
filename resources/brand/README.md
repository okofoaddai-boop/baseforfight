# Brand Assets

Diese Ordnerstruktur ist die zentrale Ablage fuer Logos und Icons.

## Source of truth

- `resources/brand/logos/` fuer Logo-Dateien (SVG bevorzugt)
- `resources/brand/icons/` fuer UI-Icons und Favicons

## Runtime assets

- `public/assets/brand/` fuer Dateien, die direkt vom Browser geladen werden

## Empfehlung

1. Neue oder angepasste Dateien zuerst in `resources/brand/` pflegen.
2. Danach die finalen Auslieferungsdateien in `public/assets/brand/` ablegen.
3. Standardpfade zentral in `config/brand.php` pflegen.
