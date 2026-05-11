# G.R.O.W — Subscription plans, modules, pricing, and paddock area (billing reference)

This document is the **single consolidated reference** for marketing, the public landing page comparison table, and the in-app **Subscription & Billing** experience (`resources/views/account-settings/subscription-billing.blade.php`). It also records **how the Laravel app enforces access** so product and engineering stay aligned.

**Primary code sources**

| Area | Location |
|------|----------|
| Plan slugs and display names | `app/Support/SubscriptionPlanAccess.php` |
| URL / route gating | `config/subscription_plan_restrictions.php` + `app/Http/Middleware/RestrictSubscriptionFeatures.php` |
| Billing UI copy, prices, feature lists | `resources/views/account-settings/subscription-billing.blade.php` |
| Controller data (Stripe, invoices, paddock limits) | `app/Http/Controllers/SubscriptionBillingController.php` |
| Paddock area limits & extra-ha pricing | `config/paddock.php` |
| Stripe price lookup keys (defaults) | `config/services.php` → `stripe` |
| EOSDA regular satellite / weather entitlement | `config/services.php` → `eosda` + `app/Support/EosdaRegularPipelineGate.php` |
| Organisation sidebar structure (pages under Organisation) | `resources/menu/verticalMenu.json` |

---

## 1. Terminology: internal slug vs customer-facing name

| Internal slug (`subscription_plan` / code) | Customer-facing name (UI) |
|---------------------------------------------|---------------------------|
| `free` | **Nutrition Basics** |
| `pro` | **Nutrition Essentials** |
| `growth` | **Nutrition Premium** |

Stripe subscriptions resolve the effective plan from `users.stripe_price_lookup_key` using `SubscriptionPlanAccess::derivePlanFromLookupKey()` (exact match to configured keys, then legacy substring rules such as `pro` / `growth` / `5` / `10`).

Default Stripe **lookup key** env defaults (overridable in `.env`; see `config/services.php`):

- **Nutrition Essentials:** `STRIPE_PRO_LOOKUP_KEY` — default `NTS_GROW_Pro_Plan-05c07e9`
- **Nutrition Premium:** `STRIPE_GROWTH_LOOKUP_KEY` — default `NTS_GROW_Growth_Plan`
- **Extra hectares add-on:** `STRIPE_EXTRA_HECTARE_LOOKUP_KEY` — default `NTS_GROW_EXTRA_HECTARE_02AUD`

---

## 2. Published list prices (Subscription & Billing page)

Values are exactly as shown in the billing UI today:

| Plan | Price (billing UI) | Positioning line (billing UI) |
|------|--------------------|--------------------------------|
| **Nutrition Basics** | Free | Best for getting started |
| **Nutrition Essentials** | **AUD 9.99/month** | For active farm operators |
| **Nutrition Premium** | **AUD 19.99/month** | For teams and scaling operations |

**Add-on (not a tier): extra paddock area**

| Item | Default (config / UI) | Notes |
|------|------------------------|--------|
| Included paddock area | **50 ha** total limit per account (`PADDOCK_DEFAULT_AREA_LIMIT_HA`) unless `users.paddock_area_limit_ha` sets a custom cap | Total area users may allocate across paddocks they create |
| Extra hectares | **AUD 0.20 / ha / month** (`PADDOCK_EXTRA_HECTARE_PRICE_AUD_PER_HA`) | Shown on Subscription & Billing; recurring when purchased via Stripe |
| Min purchase (checkout) | **1 ha** by default; can increase if Stripe minimum-charge enforcement is enabled | See `SubscriptionBillingController` + `config/paddock.php` |
| Max per checkout | **100,000 ha** (abuse guard) | `PADDOCK_EXTRA_HECTARES_MAX_PER_CHECKOUT` |

**Related product pricing (paddock imagery, not the monthly tier)**

| Item | Default | Config |
|------|---------|--------|
| **3 m imagery activation** | **AUD 2.50 / ha** (one-time style product in platform) | `PADDOCK_IMAGERY_3M_ACTIVATION_PRICE_AUD_PER_HA` |

**Lab and therapy checkouts** (Brookside, EAL, leaf/soil packages, etc.) use **separate Stripe prices**; they appear in **Billing history** as invoices or PaymentIntents. They are **not** the monthly Nutrition Essentials/Premium subscription.

---

## 3. High-level module access (as marketed on Subscription & Billing)

This matches the **“Module access”** line on each plan card in `subscription-billing.blade.php`.

| Module / area | Nutrition Basics | Nutrition Essentials | Nutrition Premium |
|---------------|------------------|----------------------|-------------------|
| **Wisdom** (library, learning, news, forum, FAQs, help, etc.) | Yes (all Wisdom pages listed in billing UI) | Yes | Yes |
| **G.R.O.W Smart Tools** (irrigation, product selector, cover crop builder, tank mix, rate calculator — **see §8 for route reality**) | Marketed as included | Marketed as included | Yes |
| **Guidance** (Ask G-Man, Book a Meeting, Chat with Support) | **No** (section locked for Free — see §6) | Yes | Yes |
| **Resilience** (Soil / Plant / SAP nutrition workflows) | **Partial** — lab requests & submissions lists; **not** full therapy/analytics/iframe submissions where gated (see §7) | Yes (therapy + analytics paths allowed) | Yes |
| **Organisation** (operations dashboard, activities, finance, livestock, aquaculture, satellite, reports, etc.) | **Farms only** (farm + paddock records) | **Farms + G.R.O.W Vision** (see §6); rest of Organisation locked | **Full Organisation** |
| **Farm Dashboard** (main operations dashboard) | Locked at route level for Free & Pro | **Locked** for Pro (`dashboard` route forbidden) | Yes |

---

## 4. Feature bullets (exact text from Subscription & Billing)

### Nutrition Basics

- Wisdom library for learning  
- G.R.O.W Smart Tools included  
- Explore the platform freely  
- Upgrade when you need more  

### Nutrition Essentials

- Adds Guidance and Resilience  
- Farms and paddocks for records  
- Smart Tools for agronomy  
- Link analyses to paddocks  

### Nutrition Premium

- Adds full Organisation suite  
- Operations, finance, reporting  
- Livestock, aquaculture, imagery  
- Teams and collaboration  

---

## 5. Wisdom — pages enumerated on the billing screen

These are the **10 Wisdom entries** embedded in `subscription-billing.blade.php` (landing/comparison tables can reuse the same names and one-line descriptions).

| Page | Summary (billing UI) |
|------|----------------------|
| **G.R.O.W Library** | Searchable knowledge base of guides, references, and educational resources. |
| **Online Learning** | Access Courses, Podcast, Videos, Articles, and Translated Articles. |
| **Events** | Upcoming webinars, workshops, and community sessions. |
| **G.R.O.W Arcade** | Interactive learning and engagement activities. |
| **G.R.O.W Forum** | Community discussion space for questions and shared experiences. |
| **G.R.O.W Ag News** | Latest agriculture updates and G.R.O.W news. |
| **G.R.O.W Glossary** | Definitions for key agronomy and platform terms. |
| **G.R.O.W Feedback** | Submit ideas and platform feedback to the team. |
| **G.R.O.W FAQs** | Quick answers grouped by Guidance, Resilience, Organisation, and Wisdom. |
| **Help & Support** | Support center with contact and troubleshooting guidance. |

---

## 6. Menu locking rules (`SubscriptionPlanAccess::isSectionLocked`)

Used by sidebar / dashboard cards (e.g. `globalHeader.blade.php`, `verticalMenu.blade.php`, `new_dashboard.blade.php`).

| Section key | Nutrition Basics | Nutrition Essentials | Nutrition Premium |
|-------------|------------------|------------------------|-------------------|
| **guidance** | **Locked** | Unlocked | Unlocked |
| **resilience** | **Not locked** at section level | Not locked | Not locked |
| **organisation** / **organization** | **Locked**, except slugs allow-listed for Free | **Locked**, with **extra** unlocks for Pro | **Unlocked** (full Organisation) |

**Organisation JSON slugs treated as “restricted” unless allow-listed** (`config/subscription_plan_restrictions.php` → `organisation_menu_slugs`):

`dashboard`, `farms`, `Activity`, `farm-inputs`, `equipment`, `team`, `harvest-activity`, `harvest-yield`, `harvest.`, `traceability`, `finance`, `report`, `livestock`, `aquaculture`, `satellite-imagery`, `crops-ai`, `grow-reports`

**Allow-lists**

| Plan | Slugs removed from the “restricted” set (user can open these under Organisation) |
|------|-------------------------------------------------------------------------------------|
| **Nutrition Basics** | `farms` |
| **Nutrition Essentials** | `farms`, `crops-ai` |
| **Nutrition Premium** | (none needed — Organisation not locked) |

**Implication:** On **Nutrition Essentials**, users still **cannot** open Organisation items such as **Farm Dashboard**, **Activities**, **Satellite Imagery**, **G.R.O.W Reports**, etc., from the menu — only **Farms** (and paddocks) and **G.R.O.W Vision** (`crops-ai`).

---

## 7. Resilience — what each plan can reach (path + route layer)

Path checks use **allowed URI prefixes first**, then **forbidden URI prefixes**, then **forbidden route names** (`RestrictSubscriptionFeatures`).

### Nutrition Basics (Free) — practical summary

**Allowed examples (not an exhaustive list — see config):**

- G.R.O.W Admin subset: `/farm-management/grow-admin`, irrigation calculator, product recommendator, cover crop builder/plans  
- **Farms & paddocks:** list/add/edit/view paddocks, bulk paddock, `/paddocks`, etc.  
- **Lab request / submission entry points** under paths such as:  
  `soil-analysis-submission-form`, `brookside-labs-soil-analysis-submission-form`, `soil-therapy-submissions`, `soil-lab-selection`, `leaf-lab-selection`, `leaf-analysis-submission-form`, `leaf-therapy-submissions`, `plant-therapy-instruction`, `sap-lab-selection`, `sap-analysis`, `sap-therapy-submissions`, `sap-growth-stages-guidelines`, agronomist lab requests, `lab-request-results`, Agvita flows, etc.

**Blocked examples (tests + config):**

- Therapy **analytics** and deep therapy paths, e.g. `/soil-therapy`, `/soil-therapy-analytics`, `/plant-therapy`, `/leaf-therapy-analytics`, `/sap-therapy`, `/sap-analytics`  
- **SAP submissions iframe:** `/sap-submissions`  
- **G.R.O.W Vision** under crops-ai, e.g. `/farm-management/crops-ai/plant-id`  
- Broad prefixes such as `/technical-support`, `/support-chat`, generic `/soil-`, `/plant-`, `/leaf-`, `/sap-` **except** where a longer **allowed** prefix matches first  

**Forbidden route names (Free):** includes `dashboard`, `ask-gman`, `ask-gman-agronomy`, `ask-gman-health`, `chat-with-support`, `consultation-center`, `consultation-with-the-support-team`, `sap-submissions`, and the `crops-ai.*` vision routes.

### Nutrition Essentials (Pro)

- **No blanket “growth-only” URL list** — forbidden lists mainly block `/dashboard`, `/livestock`, `/aquaculture`, `/tank-mix-compatibility`, `/rate-calculator`, and a broad `/farm-management` prefix **unless** the path is on the **Pro allowed** prefix list (irrigation, recommendator, cover crop, **crops-ai**, farms/paddocks, etc.).  
- **Forbidden route names:** `dashboard`, `grow-fms-reports`.  
- **Resilience:** therapy and analytics paths are **not** blocked the same way as Free.

### Nutrition Premium (Growth)

- **Growth:** `allowed_uri_prefixes` and `forbidden_uri_prefixes` are **empty** — no path-based subscription lockdown from this config.  
- Users get full Organisation + dashboard + livestock + aquaculture + satellite + G.R.O.W Reports.

---

## 8. G.R.O.W Smart Tools — billing copy vs current route policy

**Billing UI** lists five Smart Tools for **Nutrition Basics** and **Nutrition Essentials**:

1. Irrigation Calculators — `/farm-management/irrigation-calculation`  
2. Product Selector — `/farm-management/nts-product-recommendator`  
3. Cover Crop Blend Builder — `/farm-management/cover-crop-blend-builder` (+ plans)  
4. Tank Mix Compatibility — `/tank-mix-compatibility`  
5. Rate Calculator — `/rate-calculator`

**Route configuration today**

| Tool | Nutrition Basics | Nutrition Essentials | Nutrition Premium |
|------|------------------|----------------------|-------------------|
| Irrigation / Product selector / Cover crop | **Allowed** (on prefix allow-list) | **Allowed** | **Allowed** |
| **Tank Mix Compatibility** | **Forbidden** | **Forbidden** | **Allowed** |
| **Rate Calculator** | **Forbidden** | **Forbidden** | **Allowed** |

So the **Subscription & Billing** expandable text currently **overstates** Tank Mix and Rate Calculator for Free and Pro relative to `config/subscription_plan_restrictions.php`. For a landing page “aligned” with production behaviour, either:

- update **marketing copy** to “Tank Mix & Rate Calculator — Nutrition Premium”, or  
- change **config** to allow those paths for Basic/Essentials.

Use this section when building an honest comparison table.

---

## 9. Guidance — pages described on the billing screen (Essentials+)

| Page | Summary (billing UI) |
|------|----------------------|
| **Ask G-Man** | AI guidance for Agronomy and Health contexts. |
| **Book a Meeting** | Book time with Graeme Sait, Agronomy Team, or Support Team. |
| **Chat with Support** | Live support messaging for account and platform help. |

*(Nutrition Basics: Guidance section is menu-locked.)*

---

## 10. Resilience — three pillars on the billing screen (Essentials+)

| Pillar | Summary (billing UI) |
|--------|----------------------|
| **Soil Nutrition** | Upload, convert, simulate, analyze, and request soil lab workflows. |
| **Plant Nutrition** | Leaf analysis uploads, conversion, analytics, and lab requests. |
| **SAP Nutrition** | SAP submission, analytics, and lab request management tools. |

---

## 11. G.R.O.W Vision — billing description (Essentials+)

| Page | Summary (billing UI) |
|------|----------------------|
| **G.R.O.W Vision** | Plant ID, Insect ID, Mushroom ID, and Crop Health vision tools. |

Menu slug: `crops-ai`. **Organisation → Satellite Imagery** is separate from Vision’s “Crop Health” tool (different routes under `verticalMenu.json`).

---

## 12. Organisation — full page list on the billing screen (Nutrition Premium)

These **17 rows** are what the Premium plan card expands to show:

| # | Page | Summary (billing UI) |
|---|------|----------------------|
| 1 | **Farm Dashboard** | Central overview of farm operations and key indicators. |
| 2 | **Farms** | Manage farm and paddock records. |
| 3 | **Activities** | Track activity list, calendar, and activity setup. |
| 4 | **Farm Inputs** | Manage inventory and product formulations. |
| 5 | **Equipment** | Equipment list and asset tracking. |
| 6 | **Team** | Employees, payroll, clients, and suppliers. |
| 7 | **Harvest** | Harvest records and yield by paddock. |
| 8 | **Farm Outputs** | Product batches, specs, and product composition. |
| 9 | **Traceability** | Create and manage QR codes for traceability. |
| 10 | **Finance** | Track income and expenses. |
| 11 | **Reports** | Operational and agronomy reporting (planting, harvest, finance, etc.). |
| 12 | **Livestock** | Animal inventory, health, feeding, production, tasks, and reports. |
| 13 | **Aquaculture** | Production units, water quality, feeding, growth, health, and reports. |
| 14 | **Satellite Imagery** | Crop health, weather monitoring, weather map, and growing degree days. |
| 15 | **G.R.O.W Smart Tools** | Irrigation, Product selector, Cover crop, Tank mix, Rate calculator. |
| 16 | **G.R.O.W Vision** | Plant ID, Insect ID, Mushroom ID, and crop-health vision tools. |
| 17 | **G.R.O.W Reports** | Farm management analytics hub and reporting dashboard. |

### 12.1 Organisation sidebar breakdown (from `verticalMenu.json`)

Use this when you need **every sub-page URL** for Premium or for sitemap-style tables.

| Top-level menu | Sub-pages (name → path) |
|----------------|-------------------------|
| **Farm Dashboard** | (route `dashboard`) |
| **Farms** | Farm List → `/farm-management/list-farm`; Paddock List → `/farm-management/list-paddock` |
| **Activities** | Activity List → `/farm-management/list-activity`; Activity Calendar → `/farm-management/activity-calendar`; Activity configuration → `/farm-management/activity-configuration` |
| **Farm Inputs** | Inventory → `/farm-management/list-inventory`; Product Formulations → `/farm-management/product-ingredient-tracker` |
| **Equipment** | Equipment List → `/farm-management/list-equipment`; Maintenance → `/farm-management/equipment-maintenance` |
| **Team** | Employees → `/farm-management/list-user`; Payroll → `/farm-management/team-payroll`; Clients → `/farm-management/list-client`; Suppliers → `/farm-management/list-providers` |
| **Harvest** | Harvest records → `/farm-management/harvest-activity-records`; Yield by paddock → `/farm-management/harvest-yield-by-paddock` |
| **Farm Outputs** | Product Batches → `/farm-management/harvest-list`; Product Specifications → `/farm-management/harvest-setting`; Product Composition → `/farm-management/produce-nutrition-tracker` |
| **Traceability** | Create QR Code → `/farm-management/genrate-qr`; QR Code List → `/farm-management/index-qr` |
| **Finance** | Income → `/farm-management/list-credit`; Expenses → `/farm-management/list-expense` |
| **Report** | Planting, Harvest, Irrigation, Fertilization, Pest and Disease, Weeds, Operational, Administration, Finance → `/farm-management/report/...` |
| **Livestock** | Dashboard `/livestock/dashboard`; Inventory, Health, Reproduction, Feeding, Feed Config, Production, Movements, Pasture, Reports, Tasks, Protocols, Calendar → under `/livestock/...` |
| **Aquaculture** | Dashboard, Production Units, Water Quality, Feeding, Feed Config, Growth & Biomass, Health & Mortality, Harvest, Finance, Reports → under `/aquaculture/...` |
| **Satellite Imagery** | Crop Health → `/farm-management/crop-health`; Weather Monitoring → `/farm-management/weather-watch`; Global Weather Map → `/farm-management/ventuskyData`; Growing Degree Days → `/farm-management/growing-degree-days` |
| **G.R.O.W Vision** | Plant ID → `.../crops-ai/plant-id`; Insect ID → `.../insect-id`; Mushroom ID → `.../mushroom-id`; Crop Health → `.../crops-ai/crop-health` |
| **G.R.O.W Smart Tools** | Same five tools as §8 (`verticalMenu.json` duplicates Organisation entry) |
| **G.R.O.W Reports** | `/farm-management/grow-fms-reports` |

---

## 13. Satellite / EOSDA regular pipeline entitlement (not the monthly price card)

Independent of the sidebar, **EOSDA “regular” (non–high-res) satellite pipeline** and (by default) **daily weather jobs** check `EosdaRegularPipelineGate`.

**Defaults (`config/services.php`):**

- `EOSDA_REGULAR_PIPELINE_REQUIRE_PAID_PLAN` — default **true**  
- `EOSDA_REGULAR_PIPELINE_PLANS` — default **`growth` only** (comma-separated env list)  
- Optional **area cap** vs user’s effective paddock limit — `EOSDA_REGULAR_PIPELINE_ENFORCE_AREA_CAP` default **true**

**Meaning for positioning**

- Unless ops changes env to include `pro`, **Nutrition Essentials alone does not entitle** the automated regular EOSDA pipeline / cohort features gated this way — **Nutrition Premium** does.  
- Align landing copy with whatever `EOSDA_REGULAR_PIPELINE_PLANS` is in **production**.

---

## 14. Extra hectares add-on — behaviour for billing UX

**Customer-facing explanation** (from billing page):

- Every account includes a **base hectare limit** (default **50 ha**).  
- **Extra hectares** are billed at **AUD 0.20 / ha / month** (config default).  
- Total paddock area is capped so satellite and lab workflows stay performant; users can raise the cap by purchasing hectares.  
- Purchases can go through **Stripe Checkout** (recurring per-ha line) or a **local credit** path when Stripe is disabled for add-ons (see `PADDOCK_EXTRA_HECTARES_USE_STRIPE`).

**Implementation details (for internal alignment)**

- Effective limit: `User::effectivePaddockAreaLimitHa()` — base from `paddock.default_area_limit_ha` or `users.paddock_area_limit_ha`, plus `users.paddock_extra_ha_purchased` / Stripe-synced purchases where applicable.  
- **Manual cap:** if `users.paddock_area_limit_ha` is set, billing UI shows “custom limit — each purchase adds to this total”.  
- Stripe minimum charge: optional `PADDOCK_STRIPE_ENFORCE_MINIMUM_CHARGE` increases minimum hectares per checkout when the per-ha price would otherwise fall below Stripe’s practical minimum.  
- Users with active Stripe add-on subscriptions cancel via **Manage billing** (Customer Portal) when Checkout mode is on.

---

## 15. Subscription & Billing screen sections (parity checklist)

For landing page / support docs, the in-app billing page is structured as:

1. **Current plan** — name, price line, next billing date  
2. **Plan comparison** — four cards: three tiers + **Extra hectares**  
3. *(Section numbering in Blade skips “3.”)*  
4. **Payment method** — add card (Stripe Elements), **Manage billing** portal  
5. **Billing history** — Stripe invoices + non-invoice PaymentIntents (e.g. lab checkouts)

---

## 16. Suggested “landing page” comparison axes

Use these columns in a public table (fill checkmarks from §3–§12):

- Price / billing period  
- Wisdom  
- Guidance  
- Resilience (lab vs full therapy/analytics)  
- Smart Tools (split **Tank mix / Rate calc** per §8 if accurate)  
- Farms & paddocks  
- G.R.O.W Vision  
- Farm Dashboard & full Organisation (Activities → G.R.O.W Reports)  
- Livestock / Aquaculture  
- Satellite imagery (UI) vs **EOSDA automation** (§13)  
- Included ha / extra ha price  

---

## 17. Upgrade prompts (exact strings users see when locked)

From `SubscriptionPlanAccess::menuDisabledTitle()`:

| Current plan | Tooltip / title text |
|--------------|----------------------|
| Nutrition Basics | `Upgrade to Nutrition Essentials or Nutrition Premium to unlock this section.` |
| Nutrition Essentials | `Upgrade to Nutrition Premium to unlock this section.` |

---

*Generated from the NTS-GROW-Client codebase for internal and landing-page use. If env differs in production (Stripe keys, EOSDA plan list, paddock defaults), treat `.env` as the source of truth for live numbers while this file describes app defaults and UI copy.*
