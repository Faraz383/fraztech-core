Fraztech Core V6.0.8 — Smooth Trailing Contrast Cursor

Requested motion
================
- The V6.0.7 single-root architecture is preserved, so icon and cursor root never split apart.
- While the mouse is MOVING, only the circle artwork trails a small amount behind the movement.
- Maximum internal trail is limited to ~7.5px.
- When the mouse STOPS, the circle target becomes 0/0 and it smoothly returns to the exact pointer center.
- This works the same over buttons, navigation and normal page areas.
- The arrow itself always remains at the true mouse hotspot.

Color / visibility
==================
- Cursor icon changed from green to graphite/near-black (#111614).
- Ring is graphite/near-black.
- A subtle light contrast edge/shadow helps the cursor remain visible on:
  - white backgrounds
  - mint backgrounds
  - lime buttons
  - dark-green buttons/cards
- No bright off-brand accent color was introduced.

Performance
===========
- Root position uses one requestAnimationFrame write per pointer frame at most.
- Ring spring loop runs only while it is moving/returning to center, then stops.
- Internal trail is bounded and does not perform layout calculations.
- No background animation, blur layer, backdrop-filter or magnetic button calculations.
- Original website background remains unchanged.

Cache protection
================
Brand-new files are used:
- final-motion-v608.js
- final-motion-v608.css

Preserved
=========
Languages, Urdu, flags, instant translation packs, article scrollspy,
Elementor editability, website content/layout and existing performance work.
