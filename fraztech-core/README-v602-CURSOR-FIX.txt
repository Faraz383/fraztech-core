Fraztech Core V6.0.2 — Cursor Animation Restored

Issue found
===========
V6.0.1 moved the center dot and outer ring to exactly the same pointer position.
That made the cursor very fast, but visually removed the animated-follow feeling.

V6.0.2 fix
==========
- Center dot still follows the mouse instantly.
- Outer ring now follows with a short premium trailing motion.
- The trailing animation uses requestAnimationFrame only while the ring is catching up.
- There is no permanent animation loop.
- Hovering links/buttons smoothly enlarges the ring.
- Mouse click adds a very small CSS pulse.
- No magnetic calculations, no heavy glow layer and no background animation.

Preserved
=========
- Original website backgrounds
- Performance optimizations
- Languages + Urdu + flags
- Article scrollspy
- Elementor editing
- All site content and layouts

Install
=======
Replace V6.0.1 with V6.0.2 and hard-refresh the frontend.
No migration, restore or language-pack preparation is required.
