Fraztech Core V5.4.3 — API-Free Site-Wide Language Packs

Purpose
-------
Stops visitors from downloading/translating the same language separately on every page.

How it works
------------
1. While logged in as WordPress administrator, select a language once from the frontend header.
2. Chrome downloads that language model once (when needed).
3. Fraztech automatically gets the URL list for the complete published website.
4. It fetches those pages in the background, extracts unique visible text and translatable attributes,
   translates only phrases missing from the WordPress cache, and saves them as a shared site-wide pack.
5. The language is marked READY for the current website revision.
6. Future visitors/pages only read the cached phrases. They do not repeat per-page model translation.

Safety
------
- Does not rewrite Elementor documents.
- Does not overwrite English content.
- Does not rebuild Home, Work, Services, Projects, Articles, How We Work, images or styles.
- Any later content edit marks packs stale; selecting the language again as admin repairs only missing/new phrases.
- Anonymous visitors cannot write translation cache data.

Browser limitation
------------------
Chrome's built-in Translator API currently works on desktop Chrome.
Urdu is not listed in Chrome's current Translator API supported language list, so Urdu cannot be automatically
prepared by this API-free mechanism unless a separate static Urdu pack is supplied later.
