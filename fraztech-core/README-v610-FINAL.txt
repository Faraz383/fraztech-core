Fraztech Core V6.1.0 — Final 3D Cursor + Mobile/Desktop Language Reliability

Cursor
------
- Uses the owner-supplied glossy 3D cursor artwork (optimized to a tiny PNG).
- No circle.
- The cursor tip stays on the true pointer hotspot.
- Soft lime/mint light trails behind movement and returns to the pointer when movement stops.
- Click creates a quick light burst.
- Runtime is desktop/fine-pointer only and uses requestAnimationFrame only while needed.

Languages
---------
- Frontend no longer gates any enabled language on the admin READY revision flag.
- New public read-only AJAX cache endpoints do not depend on page-cached WordPress nonces.
- This is especially important for mobile/public visitors behind page caching/CDN.
- Cached DE/AR/ES/NL/FR/UR translations are reused directly.
- Missing/new phrases remain English instead of blocking the entire language.
- The legacy “This language is not prepared yet” frontend notice is suppressed.
- Saved language restores on mobile and desktop.

Cache / speed
-------------
- On first request after update, the plugin fires LiteSpeed’s official purge-all hook and flushes WP object cache once.
- New asset filenames eliminate old cursor/language JS cache collisions.
- Scroll reveal is capped at 36 elements and uses transform/opacity only.
- No Canvas, WebGL, backdrop-filter, or permanent cursor animation loop.
