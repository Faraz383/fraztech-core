Fraztech Core V5.4.4 — Instant Frontend Language Packs

Main change
-----------
Visitors no longer download Chrome language models and no longer translate the website page-by-page.

Preparation
-----------
WordPress Admin → Fraztech → Languages → Prepare all supported languages.
Run this ONCE on staging before migration.

The admin preparation process:
- scans all published pages, posts, services, projects and other public post types;
- starts the supported Chrome local translators from one administrator click;
- translates only unique phrases missing from the WordPress translation cache;
- stores the translations in the WordPress custom translation table;
- marks each completed language pack READY.

Frontend behavior after preparation
-----------------------------------
- Ready translations are silently prefetched from WordPress while the page loads.
- Clicking DE / FR / ES / NL / AR applies the already-stored page translations immediately.
- No model download, no "Translating 8%" progress and no page-by-page machine translation is run for visitors.
- Language preference is retained across pages.
- If content changes later, pack status becomes stale and the admin can prepare packs again; existing cached phrases are reused.

Safety
------
- Existing Elementor documents, English content, images and styles are not rewritten.
- No Google Cloud key, billing or external translation API is required.
- Urdu is not auto-prepared by Chrome's current local Translator API. A static Urdu pack can be added separately.
