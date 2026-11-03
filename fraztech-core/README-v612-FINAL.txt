Fraztech Core V6.1.2 — Site Repair + 3D Segmented Trail + Solution Provider Positioning

CRITICAL REPAIR
===============
V6.1.1 wrote raw Elementor JSON with update_post_meta() without wp_slash(). WordPress strips slashes from meta values, which can invalidate nested JSON strings such as outer_attrs. That is why the public page could fall apart into raw/flattened content after the update.

V6.1.2:
- removes the destructive V6.1.1 output-buffer/content writer;
- repairs the current Elementor JSON in place first, preserving current editable fields where recoverable;
- stores repaired Elementor data with wp_slash(wp_json_encode(...));
- rebuilds _ft_layout from the repaired current Elementor document rather than from old seed content;
- backs up the pre-repair Elementor/layout meta before touching it;
- clears Elementor/LiteSpeed/WordPress caches after repair.

CURSOR / VIDEO REFERENCE
========================
The uploaded animation reference shows a head moving at the pointer with a curved segmented tail made from progressively smaller light points; direction changes create a snake/dragon-like curve, and click creates a fire/spark moment. V6.1.2 follows that interaction model:
- exact user-supplied glossy orange/gold 3D cursor shape;
- no circle;
- 12 light points follow the recent mouse path and naturally bend when direction changes;
- trail collapses/fades when the pointer stops;
- click creates a short radial gold-light particle burst;
- desktop fine-pointer only; no cursor runtime on mobile/touch.

SOLUTION PROVIDER POSITIONING
=============================
Home, About, Services and Solutions are updated through decoded Elementor field values only — never by raw HTML/JSON replacement. The message positions Muhammad Faraz as a digital solution provider/product partner who uses critical thinking, product strategy, UI/UX, development and AI to decide and deliver the right solution, instead of selling coding alone. New key strings are pre-seeded in DE/AR/ES/NL/FR/UR.

MOBILE LANGUAGES
================
Frontend language selection uses the shared WordPress translation cache directly and never blocks on READY/STALE status. The stale "This language is not prepared yet" notice is suppressed. Saved language works on mobile and desktop; genuinely uncached phrases remain English instead of blocking the whole page.
