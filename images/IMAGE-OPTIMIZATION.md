# Image optimization – About Us page

Optimize these images so the About Us page loads quickly on the live site.

---

## 1. Hero image (critical)

| File | Current size | Target |
|------|--------------|--------|
| `images/_SJD7329.jpg` | **~40 MB** | **&lt; 500 KB** |

- **Why:** This is the main cause of slow loading and the spinner.
- **How:** Resize to max width 1920px (or 2400px for retina), JPEG quality ~80% or WebP. Overwrite the file or update the `src` in `about-us.html`.

---

## 2. Section images

| File | Current size | Target | Used in |
|------|--------------|--------|---------|
| `images/15.png` | ~1.7 MB | &lt; 300 KB | Mission & Vision |
| `images/13.png` | ~2.5 MB | &lt; 300 KB | Core Values (hidden section) |

- **How:** Resize to max width ~1200px, export as PNG or WebP. Replace the files.

---

## 3. Staff / team photos (20 images)

| Location | Current total | Target per image | Target total |
|----------|----------------|------------------|--------------|
| `staff/*.png` | ~1.5–3.2 MB each (~35 MB total) | &lt; 150 KB | &lt; 3 MB |

- **Why:** Shown as small cards (height 240–320px). Full-size photos are unnecessary.
- **How:** Resize each to about **600×600px** (or 800×800 for retina), compress PNG or use JPEG/WebP. Replace the files in `staff/` (keep the same filenames so the page does not need changes).

---

## 4. Logo (header & footer)

| File | Suggested |
|------|------------|
| `assets/img/NTS G.R.O.W Logo .png` | Keep under ~100 KB if possible |

---

## Tools

- [Squoosh](https://squoosh.app) – browser-based, supports WebP and resize
- ImageOptim (Mac) / similar – batch compress
- Export for Web in Photoshop/Figma – set dimensions and quality

After replacing the hero and section images and optimizing staff photos, the About Us page should load much faster.

---

## Beyond compression

**Preload (done):** The page preloads the hero image in the head so the browser starts fetching it immediately.

**Optional WebP:** Export the hero as WebP and use a `<picture>` with `<source type="image/webp">` and the current `<img>` as fallback for faster load in supporting browsers.

**Server:** Use long-lived cache headers for images and consider a CDN so repeat visits and geographic distance matter less.
