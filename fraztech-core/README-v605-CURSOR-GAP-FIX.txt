Fraztech Core V6.0.5 — Cursor Gap Smooth Fix

Issue fixed
===========
In V6.0.4 the circle used an intentionally soft spring. During a fast mouse move,
the solid dark-green pointer icon reached the new element immediately while the
circle could remain around the previous element for too long. This created the
large gap visible in the screenshot.

V6.0.5 behavior
===============
- Dark-green pointer icon still follows the real mouse instantly.
- Circle remains smoothly animated.
- Circle is now limited to a maximum ~11px center gap from the real pointer.
- Fast jumps automatically pull the circle close immediately.
- A short spring then gives it the subtle forward/back floating movement.
- Excess momentum is damped when the pointer moves quickly to another element.
- Link/button expansion and click pulse are preserved.

Performance
===========
- No permanent animation loop.
- requestAnimationFrame runs only while the circle is settling.
- No background animation, blur, backdrop-filter, magnetic calculations or card pointer tracking.

Everything else is unchanged:
languages, Urdu, flags, article scrollspy, Elementor editability, content and original backgrounds.
