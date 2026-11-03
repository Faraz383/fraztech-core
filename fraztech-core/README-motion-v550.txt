Fraztech Core V5.5.0 — Premium Motion System

Added
=====
1. Premium animated cursor
   - Brand-matched lime/green liquid-glass ring + dot + soft trailing glow.
   - Smooth spring-follow movement using requestAnimationFrame.
   - Enlarges subtly over links/buttons/media.
   - Small magnetic movement on key CTA buttons.
   - Desktop precise mouse pointers only; disabled for touch/coarse pointers.
   - Text inputs retain the normal text cursor.

2. Aurora scroll reveal
   - Sections, headings, cards and article content reveal with subtle opacity,
     upward motion, micro-scale and blur.
   - Uses IntersectionObserver; elements in the initial viewport stay visible.
   - Runs once per element rather than continuously on scroll.

3. Liquid-glass card aura
   - Services, projects, articles, AI tools, pricing, video and solution cards
     receive a subtle green glass spotlight that follows the pointer inside the card.
   - Includes a very small lift and premium soft shadow on hover.

Accessibility / performance
===========================
- Honors prefers-reduced-motion.
- Cursor effects run only when hover:hover + pointer:fine.
- requestAnimationFrame is used for the continuous cursor follower.
- IntersectionObserver handles scroll reveals.
- No third-party animation library, CDN or external runtime dependency.
- Does not modify Elementor content, images, translations, language packs or layouts.
- Does not run inside Elementor editor preview.

Install
=======
Replace the current Fraztech Core plugin with V5.5.0. No migration and no rebuild is required.
Hard-refresh the frontend after installation.
