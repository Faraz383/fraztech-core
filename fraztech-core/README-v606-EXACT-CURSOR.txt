Fraztech Core V6.0.6 — Exact Cursor Alignment Fix

What was actually wrong
=======================
The previous versions deliberately let the circle trail behind the real pointer.
The screenshot showed the result clearly: when the pointer moved quickly to another
navigation/button element, the dark-green arrow was already at the new mouse position
while the circle was still visually sitting over the previous element.

V6.0.6 removes positional trailing completely.

Exact behavior now
==================
- The circle center is always the real mouse coordinates.
- The dark center dot is exactly at the circle center.
- The solid dark-green arrow tip is offset mathematically so its tip lands on that exact dot.
- Therefore the arrow and circle cannot separate when hovering buttons/navigation.
- Circle animation is preserved through:
  - subtle breathing while moving
  - rotating border arc while moving
  - expansion on links/buttons
  - click pulse
- None of those effects change the ring's X/Y position.

Performance
===========
- No spring position loop.
- One requestAnimationFrame write per pointer event at most.
- No background animation, blur, backdrop-filter, magnetic layout calculations or card pointer tracking.
- Original website backgrounds stay unchanged.

Everything else is preserved:
languages, Urdu, flags, instant translations, article scrollspy, Elementor editability and content.
