Fraztech Core V5.4.6 — Urdu + Flags + Article Scrollspy

Adds
----
1. Urdu site-wide pack
   - No Google/API key.
   - Admin preparation uses Chrome's supported English→Hindi local translator,
     then a local Fraztech Hindi→Urdu vocabulary/transliteration layer.
   - Urdu is stored in the same WordPress translation cache and becomes READY.
   - Frontend visitors still get instant cached translation and do not download models.

2. Flag icons
   - English 🇬🇧
   - German 🇩🇪
   - Arabic 🇸🇦
   - Spanish 🇪🇸
   - Dutch 🇳🇱
   - French 🇫🇷
   - Urdu 🇵🇰
   - Current-language flag also appears in the header trigger.

3. Article sidebar scrollspy
   - On article detail pages, the "IN THIS ARTICLE" sidebar follows reading position.
   - The current section point/link highlights automatically as the user scrolls.
   - Includes a subtle progress line and keeps the active item visible in long sidebars.
   - RTL styling is included for Arabic/Urdu.

What to do after installing
---------------------------
- Replace the current Fraztech Core plugin with V5.4.6.
- Go to Fraztech → Languages.
- Existing READY packs remain cached.
- Click "Prepare all supported languages" once; because the other packs are already READY,
  the builder focuses on Urdu (unless the site revision has changed).
- Wait until Urdu shows READY, then hard-refresh the frontend and test UR.

Quality note
------------
The Urdu fallback is designed for website/product copy and includes a Fraztech vocabulary layer.
Because Chrome does not natively expose Urdu in its Translator API, this is a practical API-free
Hindi→Urdu conversion rather than a native Urdu neural translation model. Review key marketing
headlines before launch if exact literary Urdu wording is important.

Safety
------
No Elementor documents, images, English source content or layouts are rebuilt by this update.
