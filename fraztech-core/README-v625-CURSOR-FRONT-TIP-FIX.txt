Fraztech Core v6.2.6 — Cursor Front-Tip Hotspot Fix

Fix: The 32x33 custom cursor image visually points to the bottom tip (approximately x=12, y=32), but the previous CSS hotspot was incorrectly set to x=31, y=15 at the right-hand side of the artwork. This made clicks register offset from the visible pointer tip.

Updated both cursor CSS rules to use hotspot 5 5 and updated the click-effect comments. Asset version bumped to 6.2.6 so browsers/CDNs are less likely to reuse cached CSS/JS. Cursor artwork dimensions and visual size remain unchanged.

Checks performed: PHP syntax, CSS hotspot declarations, ZIP integrity. Live browser/WordPress click testing was not available in this build environment.
After installing: purge WordPress/Hostinger/CDN cache and hard-refresh the page once to load the new asset version.
