# Product direction

**Cloudberry on pinchards.is is a closed photographic archive.** The solar camera is gone; there is no planned return to continuous capture. The live site’s job is to present that finite corpus well — gallery, viewer, about, maps, exhibition/kiosk surfaces — with durable citations and careful performance on shared hosting.

## What this means for engineering

- Prefer reliability, accessibility, and deploy hygiene over new capture or remix features.
- Do not treat `docs/practice-forward-brief.md`, `docs/practice-research-briefs.epub`, or related practice notes as a product roadmap. Those documents are **artistic research** for continuing the Newfoundland body of work (possibly as sibling projects or future exhibitions). Implementing them here would change the product.
- Sibling repos ([Adrift](https://github.com/adamsimms/adrift), [Dory](https://github.com/adamsimms/dory), [Waves](https://github.com/adamsimms/waves)) stay separate; collisions with Cloudberry are curatorial, not a monorepo merge goal.

## When the product *would* change

Only with an explicit owner decision — for example shipping archive remix tools, a CMS, or a new capture pipeline. Until then, engineering backlog stays archive-first (caching, CI, a11y, docs, hosting).
