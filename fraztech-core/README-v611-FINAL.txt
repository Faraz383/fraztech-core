Fraztech Core V6.1.1 — 3D Glow Cursor + Solution Provider Positioning + Language Reliability

Cursor
------
- Uses the user's supplied glossy orange/gold 3D cursor reference as the actual cursor artwork.
- Asset upgraded to a higher-resolution transparent PNG so the 3D highlights remain visible.
- No circle.
- A warm luminous comet trail stretches behind movement and smoothly collapses back to the cursor when movement stops.
- Click creates a fast warm light burst.
- Cursor art is anchored by its actual pointed tip.

Performance
-----------
- Cursor runs only on fine-pointer desktop devices.
- No permanent animation loop; trail spring sleeps when settled.
- No canvas/WebGL/backdrop-filter.
- Decorative scroll reveal is disabled on touch/mobile and capped at 24 nodes on desktop.
- Existing LiteSpeed/runtime purge runs once for 6.1.1.

Languages
---------
- New language-v611.js filename bypasses older browser/page cache.
- Public read-only translation lookup does not depend on a page nonce, so cached mobile pages cannot expire translation access.
- No READY/STALE gating on visitors.
- Compatibility copies are included for older language script filenames.
- The new solution-provider copy is pre-seeded in DE, AR, ES, NL, FR and UR, so users do not need to prepare language packs again for these texts.

Positioning copy
----------------
- Home hero: solution-provider + critical-thinking positioning.
- Home service intro: problem-to-solution positioning.
- About: independent digital solution provider, critical thinking, solution design and connected development/AI team.
- Solutions: AI-era value framed around choosing the right approach, not just code.
- Services: strategy + design + development + AI, focused on outcomes.
- Exact text replacements are applied persistently to front/about/solutions/services content and Elementor data where the old copy exists.
