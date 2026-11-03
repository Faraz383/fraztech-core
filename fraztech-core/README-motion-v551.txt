Fraztech Core V5.5.1 — Premium Motion Performance

Goal
----
Keep the premium cursor and website motion while reducing CPU/GPU work.

Optimizations
-------------
- Removed the permanent cursor requestAnimationFrame loop.
- Cursor animation now wakes only when movement still needs smoothing.
- Removed the large trailing glow layer.
- Removed backdrop-filter from the cursor.
- Removed filter: blur from scroll reveal animations.
- Reduced reveal targets and animation duration.
- Replaced one pointermove listener per card with one delegated document listener.
- Replaced appended card aura DOM nodes with a CSS ::after aura.
- Card aura position updates are throttled to requestAnimationFrame.
- Reduced shadow strength and hover travel.
- Touch/mobile and prefers-reduced-motion continue to disable non-essential effects.
- Elementor editor remains unaffected.

No migration required.
Replace the current plugin and hard-refresh the frontend.
