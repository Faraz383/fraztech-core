Fraztech Core V6.0.7 — Single-Anchor Cursor Fix

Why previous fixes still showed a gap
=====================================
The video was reviewed. The important issue is architectural, not just spring speed:
the arrow icon and the circle were separate fixed elements with separate positional
transforms. Any timing, CSS animation, cached script, or event-frame difference could
make one appear at a different point from the other.

V6.0.7 removes that possibility.

New cursor architecture
=======================
- ONE fixed cursor root receives the mouse X/Y coordinates.
- Circle, center dot and dark-green arrow are children of that same root.
- Circle center = root hotspot.
- Center dot = exact root hotspot.
- Arrow tip is anchored to that same hotspot.
- There is no positional animation on the arrow or ring.
- Hovering a button can resize the ring but cannot move its center.
- Visual animation is limited to the rotating ring arc and click scale only.

Cache protection
================
This build uses brand-new asset filenames:
- final-motion-v607.js
- final-motion-v607.css

The old cursor motion assets are removed from the plugin, which avoids an older cached
cursor script continuing to run after replacement.

Preserved
=========
- Original website background
- Performance optimizations
- Languages + Urdu + flags
- Instant translation packs
- Article scrollspy
- Elementor editability
- Existing content and layouts

Install
=======
Replace V6.0.6 with V6.0.7.
Then hard-refresh the frontend once.
No migration, restore, seed import, or language-pack rebuild is required.
