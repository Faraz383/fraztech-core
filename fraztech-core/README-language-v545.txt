Fraztech Core V5.4.5 — Instant Language Switch Fix

Fixes
-----
- Removes the "This page has new text. Rebuild language packs..." blocker.
- A READY language now switches immediately using every cached phrase already available.
- A small runtime-only/dynamic phrase can no longer cancel the whole translation.
- While the administrator is logged in, any missing runtime-only phrases are repaired silently in the background when possible.
- Visitors never download translation models and never wait for page-by-page translation.
- Existing Elementor pages, English content, images and styling are untouched.

Recommended state before migration
----------------------------------
Keep DE / AR / ES / NL / FR marked READY in Fraztech → Languages.
Urdu remains separate until a static Urdu pack is added.
