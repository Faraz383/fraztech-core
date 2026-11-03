Fraztech Core V6.0.1 — PUBLISH READY PERFORMANCE

Final requested changes
=======================
1. Background restored
   - All custom straight/curvy/roadmap background lines are completely removed.
   - No decorative SVG background is injected.
   - Theme/Elementor page backgrounds display exactly as they did before the line experiments.

2. Faster custom cursor
   - Removed spring/trailing behavior.
   - Removed magnetic calculations.
   - Removed glow layers and background-related animation work.
   - Pointer position is applied directly for a tighter, quicker feel.
   - Hover expansion remains subtle and premium.

3. Website motion optimized
   - No custom background animation runtime.
   - Only one deferred custom JavaScript file.
   - Only one custom motion stylesheet.
   - Reveal targets reduced to 72 maximum elements.
   - Reveal uses transform + opacity only.
   - Card hover is CSS-only.
   - No pointer tracking on cards.
   - No canvas, WebGL, blur animation, backdrop-filter, or scroll-linked SVG calculations.
   - Mobile/touch skips the custom cursor.
   - prefers-reduced-motion is respected.

Preserved
=========
- Multilingual system
- Urdu translation
- Language flags
- Instant cached translations
- Article sidebar scrollspy
- Elementor editability
- Existing pages, articles, projects, images and content

Install
=======
Replace the current Fraztech Core plugin with V6.0.1.
Do not run migrations, restore, seed import or language-pack preparation.
Hard-refresh staging and review desktop + mobile before publishing.
