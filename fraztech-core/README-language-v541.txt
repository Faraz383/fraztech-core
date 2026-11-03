Fraztech Core V5.4.2 — API-Free Language Switcher Fix

Changes:
- Fixes the language getting stuck after a page reload.
- Language choice is saved only after a complete successful translation.
- If a saved page does not have a complete cached translation, the page safely returns to English.
- Language dropdown remains usable while another translation is preparing.
- Failed/unsupported translation no longer traps the visitor in that language.
- Existing Elementor documents, content, images and styling are not rebuilt or rewritten.
- No Google Cloud API key or billing is required.

Important:
Chrome's built-in Translator API works on supported desktop Chrome browsers and requires a user action the first time a language translator is created.
Urdu is not currently listed among Chrome Translator API language pairs, so it can only work when a complete cached Urdu pack already exists.
