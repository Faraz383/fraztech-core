Fraztech Core V6.1.9 — Browser Hotspot Cursor Fix

Root cause fixed:
Previous releases drew the 3D cursor as a separate DOM element and manually aligned it over the real browser pointer. The visible art could therefore feel offset from the browser click hotspot.

V6.1.9 uses the supplied 3D artwork as the actual CSS browser cursor at 32x33px with explicit hotspot 31,15 (the visible front/right tip). The browser now owns both cursor drawing and click targeting, so the visible front tip and real click point are the same native hotspot.

- No second black/native cursor.
- Click burst uses e.clientX/e.clientY, the exact same front-tip hotspot.
- Trail remains decorative and begins from the center-back of the icon.
- Website content, languages, Elementor data and layouts are untouched.
