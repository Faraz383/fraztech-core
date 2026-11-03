Fraztech Core V6.0.3 — Cursor Icon + Circle Animation

Added
=====
- A real premium pointer/arrow icon is now shown with the custom circle.
- The arrow stays exactly on the mouse pointer for a fast feel.
- The outer circle trails the pointer very slightly for visible animation.
- On links/buttons:
  - circle expands
  - pointer icon tilts/scales
  - small top node brightens
- On click:
  - pointer icon taps inward
  - circle sends out a quick pulse

Performance
===========
- No permanent requestAnimationFrame loop.
- The ring animation only runs while catching up with the pointer.
- No heavy glow layer, blur, backdrop-filter or magnetic layout calculations.
- Original website backgrounds remain unchanged.
- Mobile/touch does not use the custom cursor.
- prefers-reduced-motion is respected.

Preserved
=========
- Website speed optimizations
- Languages, Urdu and flags
- Instant cached translations
- Article sidebar scrollspy
- Elementor editability
- Existing content and layouts

Install
=======
Replace the current Fraztech Core plugin with V6.0.3 and hard-refresh the frontend.
No migration, restore or language-pack preparation is required.
