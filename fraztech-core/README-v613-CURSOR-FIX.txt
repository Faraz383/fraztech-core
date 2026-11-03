Fraztech Core V6.1.3 — 3D Cursor Visibility Fix

Only the cursor system changed.

- Uses the exact orange/gold 3D cursor image supplied by the owner.
- The artwork is embedded directly inside final-motion-v613.css, so a missing image request cannot hide the cursor.
- A copy of the exact cropped artwork is also included as assets/cursor-3d-v613.png.
- Cursor renders at ~44px for clear 3D detail.
- The pointed tip is aligned to the real mouse hotspot.
- The segmented gold/light trail remains behind the 3D cursor.
- Click keeps the light/spark burst.
- Reduced Motion still shows the 3D cursor but disables the decorative trail/burst.
- Mobile/touch keeps the normal system cursor behavior and does not run the custom cursor.
- No content, Elementor data, language packs, positioning copy, routes, images or layouts are changed.
