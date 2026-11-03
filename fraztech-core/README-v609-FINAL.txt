Fraztech Core V6.0.9 — Light Trail Cursor + Mobile Language Fix + Performance

Cursor
======
- Visible circle removed completely.
- Graphite mouse icon remains exact at the pointer hotspot.
- One soft lime/mint light follows slightly behind the mouse while moving.
- When movement stops, the light returns to the pointer.
- Click creates a quick radial light burst.
- No permanent animation loop.

Mobile languages
================
The mobile message "This language is not prepared yet" was caused by the frontend blocking a language when its admin READY revision had become stale, even though the WordPress translation cache already contained that language.

V6.0.9 fixes this:
- Mobile and desktop do not block cached languages because of a stale READY flag.
- Every enabled language reads from the shared WordPress translation cache.
- Existing German, Arabic, Spanish, Dutch, French and Urdu packs work on mobile without a browser Translator model.
- Missing/new phrases remain English instead of blocking the whole selected language.
- Saved language is restored on mobile and desktop.
- Language maps warm after the critical page load.

Performance
===========
- No cursor circle rendering.
- Only one lightweight trailing light is animated.
- Reveal animations capped at 48 elements.
- IntersectionObserver + transform/opacity only.
- Language prefetch waits until browser idle.
- New filenames avoid stale cached code:
  final-motion-v609.js
  final-motion-v609.css
  language-v609.js

Preserved
=========
Original backgrounds, Elementor editability, article scrollspy, flags, Urdu, all content and layouts.
