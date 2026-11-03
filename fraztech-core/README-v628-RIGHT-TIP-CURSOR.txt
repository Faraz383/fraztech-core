FrazTech Core 6.2.8 — right-facing cursor tip correction

Native cursor uses 64x64 PNG generated from the existing high-resolution 3D asset (v613), with transparent padding and hotspot 56 28 at the rightmost visible tip. Both CSS selectors updated. Other plugin functions preserved.

Test in staging on Chrome/Safari, clear all caching. Browser support for 64px native cursors can vary; if it falls back to default pointer, a 32px variant may be needed. Verify exact tip alignment and rendering visually before publishing.
