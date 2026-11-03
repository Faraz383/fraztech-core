Fraztech Core V6.0.4 — Smooth Dark-Green Cursor

Requested cursor changes
========================
- Mouse pointer icon is now fully solid dark green (#0d4f3c).
- No light fill and no outline-style pointer.
- Outer circle remains around the pointer.
- The pointer icon follows the mouse instantly.
- The circle uses a soft spring motion:
  it falls slightly behind while moving and catches/overshoots gently.
- Hovering buttons/links enlarges the circle smoothly.
- Clicking keeps the small tap + pulse interaction.

Performance
===========
- The spring requestAnimationFrame loop runs only while the circle is still moving.
- It stops completely as soon as the ring settles.
- No glow layer, blur, backdrop-filter, magnetic layout calculations or background animation.
- Original page backgrounds remain unchanged.
- Mobile/touch does not run the custom cursor.
- Reduced-motion is respected.

Preserved
=========
- Languages + Urdu + flags
- Instant cached translations
- Article sidebar scrollspy
- Elementor editability
- Existing content/layout
- Performance optimizations

Install
=======
Replace V6.0.3 with V6.0.4 and hard-refresh the frontend.
No migration, restore or language-pack preparation is required.
