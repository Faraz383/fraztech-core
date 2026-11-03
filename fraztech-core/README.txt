Fraztech Core 5.1.0

Adds the Elementor-editable “How We Work” page at /how-we-work/ as a safe replacement for the old Stories & Meetings destination.

What 5.1 does:
- Creates the new page only if it does not already exist.
- Preserves every existing Elementor page and current user edits.
- Adds an interactive service selector for Mobile Apps, Web Apps, WordPress, Shopify, SaaS/CMS, AI/Automation, and UI/UX Design.
- Exposes text, links, technology icons, cards/containers and styling through the existing Fraztech Elementor system.
- Replaces only the matching Stories & Meetings header/footer links with How We Work.
- Drafts the old /videos/ page and permanently redirects /videos/ to /how-we-work/.
- Updates the V5 integrity audit so How We Work replaces /videos/ in the canonical 76-route set.
- Provides Fraztech → How We Work status/repair screen.

No full-site migration is required.


V5.2 How We Work full roadmap
- Rebuilds only /how-we-work/ once from the complete interactive roadmap.
- Preserves all other Elementor pages and global styling.
- Adds 7 service process selectors, technology stacks, QA, handover and CTA sections.
- Stores a safety backup of the prior How We Work Elementor data before rebuild.

V5.4.0 — Header Language Switcher
- Adds English, German, Arabic, Spanish, Dutch, French and Urdu selector to the global header.
- Uses Google Cloud Translation Basic (v2) server-side; API key is never exposed to browser JavaScript.
- Caches translated phrases in a dedicated WordPress table.
- Does not rewrite Elementor pages, layouts, images, styles, URLs or existing edited content.
- Settings: Fraztech → Languages.
