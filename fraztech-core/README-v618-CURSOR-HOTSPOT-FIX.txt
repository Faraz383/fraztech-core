Fraztech Core V6.1.8 — Exact Front Tip + Native Cursor Fix

Reviewed the 2026-10-19 16:17 screen recording.

Fixes:
- The black/native browser cursor is forcibly hidden for all page elements while the custom cursor is active.
- The 3D image box is now positioned in JavaScript using the measured front-tip coordinates from the supplied 256x262 PNG. No child positional transform is relied on.
- The real browser click hotspot and the visible 3D front tip are the same X/Y.
- Click light begins at that exact front tip and only travels forward.
- Trail still begins from the center-back of the 3D body.
- Cursor/trail color is champagne-lime-gold: close to the Fraztech green/lime palette, with amber contrast so it stays visible on white, mint, light-green and dark-green surfaces.
- Website content, languages, Elementor data and layouts are unchanged.
