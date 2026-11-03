Fraztech Core v6.2.4 — Homepage Section 05 review fix

- Replaces the homepage Section 05 placeholder at render time, before shortcodes are expanded.
- Removes the separate /reviews/ CTA from the homepage section.
- Shows liquid-glass testimonial cards, stylized 3D initials avatars, left/right controls, pointer dragging, touch swipe and keyboard arrows.
- Cursor source is unchanged from v6.1.9 (32x33 px); this release does not enlarge it.
- Review text is sourced from the public Figmora Fiverr profile at https://www.fiverr.com/figmora. The public page currently exposes these five distinct review excerpts in the indexed view; the track duplicates them only to create a seamless looping animation. It does not claim ten distinct reviews.

After install: purge WordPress/Hostinger/LiteSpeed cache and hard refresh. Live staging has not been browser-tested from this build environment.

V6.2.4 critical integration fix: testimonial replacement now runs on final frontend HTML via output buffering, so it does not depend on the unused ft_render_content() path or on Elementor/theme render internals. The old /reviews/ route redirects to the homepage testimonial section, and local links to /reviews/ are removed from frontend output.
The standalone /reviews/ entry has also been removed from the plugin seed data so future imports do not recreate it. Existing WordPress page records are safely retired by redirecting /reviews/ to /#ft-review-heading rather than destructively deleting database content.
