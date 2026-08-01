# VaidTrack.com â€” Cancer Medical Tourism Landing Page

Static marketing site for **[VaidTrack.com](https://www.vaidtrack.com)** â€” medical tourism facilitation for international patients seeking cancer care in India (second opinion, specialist matching, visa & travel coordination).

**Live site:** https://www.vaidtrack.com  
**Repo:** https://github.com/mnadwi3/kenyalandingpage

---

## Stack

| Layer | Choice |
|--------|--------|
| Markup | Static HTML (no build step) |
| Styles | Tailwind CDN + `css/styles.css` + `css/treatment-page.css` |
| Scripts | Vanilla JS â€” `js/main.js`, `js/treatment-page.js` |
| Hosting | Apache (`.htaccess`) and/or Netlify-style (`_redirects`) |
| Analytics | GTM `GTM-KZ86XPT5`, GA4 `G-5TBH8QQ2EQ` |
| Lead CTA | WhatsApp `wa.me/918979983149` + on-page enquiry forms |

---

## Project structure

```
â”œâ”€â”€ index.html              # Homepage
â”œâ”€â”€ privacy-policy.html
â”œâ”€â”€ disclaimer.html
â”œâ”€â”€ css/
â”‚   â”œâ”€â”€ styles.css          # Home + shared styles
â”‚   â””â”€â”€ treatment-page.css  # Treatment detail pages
â”œâ”€â”€ js/
â”‚   â”œâ”€â”€ main.js             # Home: slider, FAQ, forms, clean URLs, section order
â”‚   â””â”€â”€ treatment-page.js   # Treatment pages: sticky bar, FAQ, lead scroll
â”œâ”€â”€ treatments/             # One HTML page per cancer type
â”œâ”€â”€ images/                 # Logos, hero slides, hospital image
â”œâ”€â”€ .htaccess               # Clean URLs + HTML extension redirects
â”œâ”€â”€ _redirects              # Same for Netlify-like hosts
â”œâ”€â”€ llms.txt                # Short AI/docs summary for GitMCP
â””â”€â”€ .cursor/mcp.json        # Optional GitMCP docs server config
```

---

## Homepage sections (`index.html`)

Order is enforced in `js/main.js` (after `#why-india`):

1. **Hero** (`#hero`) â€” slider + appointment forms
2. **About** (`#about-us`)
3. **Why choose** (`#why-india`)
4. **Treatments** (`#treatment`) â€” `.tx-card` grid
5. **Doctors** (`#doctors`) â€” `.doc-card` grid (8 specialists; verify credentials before launch)
6. **Journey** (`#how-it-works`, also `#visa-travel`) â€” combined 6-step process + travel CTA
7. **Testimonials** (`#testimonials`) â€” video slots
8. **FAQ** (`#faq`)
9. **Contact** (`#contact`)
10. **Location** (`#location`) â€” partner hospital Delhi

Card styling notes:

- **Treatment cards** (`.tx-card`): white background, cyan border `#C5E0E8`
- **Doctor cards** (`.doc-card`): soft teal gradient, deep teal top accent, gold specialty badge â€” no initials circles

---

## Clean URLs

Avoid hash links for main sections. Prefer paths:

| Path | Scrolls to |
|------|------------|
| `/` | Home |
| `/treatment` | Treatments grid |
| `/doctors` | Oncologists |
| `/how-it-works` or `/visa-travel` | Combined journey section |
| `/testimonials` | Testimonials |
| `/about-us` | About |
| `/faq` | FAQ |
| `/contact` | Contact |
| `/book-appointment` | Mobile book form |
| `/treatments/breast-cancer` | Treatment detail (etc.) |
| `/privacy-policy`, `/disclaimer` | Legal pages |

Rewrites live in `.htaccess` and `_redirects`. Home uses `<base href="/">` so assets resolve under section paths. Client scroll/history is handled in `js/main.js`.

**Google Ads final URL:** use `https://www.vaidtrack.com/` (not `index.html#top`).

---

## Treatment pages

Files under `treatments/*.html`, for example:

- breast-cancer, kidney-cancer, liver-cancer, cervical-cancer
- colorectal-cancer, bladder-cancer, blood-cancer, prostate-cancer
- lung-cancer, head-and-neck-cancer, brain-cancer

Each page: hero, lead form (`#lead`), care content, doctors, FAQ, sticky WhatsApp / Send Reports bar (`treatment-page.js`).

---

## Brand tokens (site)

| Token | Value | Use |
|--------|--------|-----|
| Primary | `#097895` | Buttons, accents |
| Secondary | `#023B44` | Headings, doctor card accent |
| Accent / light | `#0BA5C4` | Highlights |
| Gold | `#D4A017` | Rules, specialty badges |
| Soft bg | `#F7FAFB` / `#EAF4F7` | Section backgrounds |
| Font | Inter (Google Fonts) | UI |

---

## Local preview

Open `index.html` in a browser, or serve the folder root so clean paths work:

```bash
# Example (any static server from repo root)
npx --yes serve .
```

Bump cache query strings when shipping CSS/JS (e.g. `styles.css?v=â€¦`, `main.js?v=â€¦`).

---

## MCP / AI docs

Cursor MCP (GitMCP) for this repo:

```json
{
  "mcpServers": {
    "kenyalandingpage Docs": {
      "url": "https://gitmcp.io/mnadwi3/kenyalandingpage"
    }
  }
}
```

Server: https://gitmcp.io/mnadwi3/kenyalandingpage

Config may live in `.cursor/mcp.json` (project) and/or `~/.cursor/mcp.json` (global). Reload Cursor after changing MCP config.

---

## Placeholders / launch checklist

- [ ] Verify all doctor names, experience, and education (marked in `index.html`)
- [ ] Replace placeholder testimonials / confirm patient quotes before publishing attributed stories
- [ ] Confirm WhatsApp number and form endpoints
- [ ] Confirm partner hospital naming / legal wording in footer & disclaimer

---

## License / ownership

Private marketing site for VaidTrack.com. Hospital names on the site are informational unless a partnership is explicitly stated.