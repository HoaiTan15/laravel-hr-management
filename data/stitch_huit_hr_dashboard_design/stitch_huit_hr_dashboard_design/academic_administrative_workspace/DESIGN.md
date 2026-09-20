---
name: Academic Administrative Workspace
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#424750'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#737782'
  outline-variant: '#c3c6d2'
  surface-tint: '#2d5ea2'
  primary: '#002d5d'
  on-primary: '#ffffff'
  primary-container: '#004385'
  on-primary-container: '#85b2fb'
  inverse-primary: '#a8c8ff'
  secondary: '#a04100'
  on-secondary: '#ffffff'
  secondary-container: '#fc7728'
  on-secondary-container: '#5d2300'
  tertiary: '#00304c'
  on-tertiary: '#ffffff'
  tertiary-container: '#00476e'
  on-tertiary-container: '#5bb8fe'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d6e3ff'
  primary-fixed-dim: '#a8c8ff'
  on-primary-fixed: '#001b3d'
  on-primary-fixed-variant: '#074688'
  secondary-fixed: '#ffdbcb'
  secondary-fixed-dim: '#ffb693'
  on-secondary-fixed: '#341000'
  on-secondary-fixed-variant: '#7a3000'
  tertiary-fixed: '#cce5ff'
  tertiary-fixed-dim: '#93ccff'
  on-tertiary-fixed: '#001d31'
  on-tertiary-fixed-variant: '#004b73'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  headline-xl:
    fontFamily: Inter
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-xl-mobile:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 30px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Inter
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 30px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 26px
  title-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  title-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
    letterSpacing: 0.04em
  caption:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '400'
    lineHeight: 14px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-desktop: 1.5rem
  margin: 1rem
  margin-desktop: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 0.75rem
  space-lg: 1.25rem
  space-xl: 2rem
---

## Brand & Style

This design system establishes a high-density, structured, and modern administrative interface tailored for university operations and institutional personnel management. It balances formal institutional prestige with contemporary digital workspace efficiency.

### Personality & Emotional Response
- **Authoritative & Reliable:** Grounded in collegiate institutional heritage, radiating precision, procedural clarity, and systemic dependability.
- **Efficient & Calm:** Alleviates administrative cognitive load through crisp contrast ratios, clear visual demarcation, and uncluttered data organization.
- **Approachable Modernity:** Complements traditional higher-education authority with contemporary rounded geometries and warm, actionable micro-accents.

### Design Movement
- **Corporate / Modern Data-Dense Ergonomics:** Drawing directly from refined enterprise productivity suites and modular CRM architecture.
- **Tonal Structure:** Relies on clear surface layering (cool slate canvasses contrasting with clean white cards) framed with gossamer hairline borders and soft ambient occlusion rather than heavy skeletal divisions.

## Colors

The color system delivers strict legibility for complex tables, profile management, and multi-tier institutional workflows.

### Roles & Application
- **Primary (`#004385`):** The core institutional deep blue. Used for primary interactive actions, active sidebar state highlights, master document headers, key metrics, and brand anchor points.
- **Secondary / Accent (`#F37021`):** The dynamic university flame orange. Reserved for high-urgency notifications, primary promotional status indicators, pending approval callouts, and key conversion moments (e.g., final submission buttons).
- **Tertiary (`#0284C7`):** An accessible sky blue used for secondary data visualizations, interactive links, informational tags, and informational states.
- **Neutral Core:**
  - **Canvas Base (`#F8FAFC` to `#F1F5F9`):** Cool light slate foundation that reduces eye strain during prolonged operational usage.
  - **Surface Layer (`#FFFFFF`):** High-contrast elevated cards, data panels, floating flyouts, and modals.
  - **Hairline Dividers (`#E2E8F0`):** Precise separation borders across tables, drawers, and form fields.
  - **Text Layers:** Primary text at `#0F172A` / `#1E293B` for exceptional contrast; secondary metadata and labels at `#475569` and `#64748B`.
- **Feedback Accents:** Success (`#16A34A`), Warning (`#D97706`), Danger (`#DC2626`).

## Typography

The typography utilizes **Inter** across all typographic roles to ensure diacritic balance and rendering consistency for both Vietnamese and English administrative terminology.

### Type Strategy
- **Diacritic Balance:** Vietnamese tonal marks (`ă, â, đ, ê, ô, ơ, ư`) retain proper vertical clearance with calculated line heights that prevent clipping inside dense data tables and nested navigation rows.
- **Tabular Figures:** For numerical records, faculty IDs, payroll figures, and dates, enable `font-feature-settings: 'tnum' on, 'cv05' on` to keep table alignment rigid and scannable.
- **Hierarchy Demarcation:** Dense interfaces require clear distinctions between labels, read-only data, and editable inputs. Use uppercase styling with widened tracking (`0.04em`) solely for micro table headers and metadata chips.

## Layout & Spacing

This design system utilizes an asymmetrical, operational layout based on an integrated application shell: a persistent 260px collapsible sidebar, an explicit header utility ribbon, and a multi-column working canvas.

### Grid Rhythm & Adapting Layouts
- **Desktop (1280px+):** Fluid 12-column interior grid inside the canvas area, using `1.5rem` gutters and `2rem` outer padding. Main workspace cards utilize 12, 8, 6, 4, or 3-column spans. Multi-pane panels support split-screen master-detail views (35% roster list / 65% personnel file dossier).
- **Tablet (768px - 1279px):** 8-column layout. Sidebar collapses into an icon rail (64px) or off-canvas drawer. Gutters and section margins standardize at `1rem`. Secondary analytical charts wrap below master tables.
- **Mobile (<768px):** 4-column single-stack arrangement with `1rem` margins and `0.75rem` item spacing. Complex employee tables degrade gracefully into structured summary cards with inline key-value pairs.

## Elevation & Depth

Visual hierarchy is maintained through high-clarity surface distinction and featherweight ambient shadows that prevent visual fatigue during 8-hour administrative work sessions.

### Depth Layers
1. **Base Layer (Elevation 0 - `#F8FAFC`):** Application canvas framing headers, search bars, and breadcrumb trails.
2. **Card & Panel Layer (Elevation 1 - `#FFFFFF`):** High-density workspace tiles, analytical metric cards, and data tables. Outlined by an ultra-crisp `1px solid #E2E8F0` and accompanied by a soft, diffused drop: `box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.04)`.
3. **Hover & Interactive Tiers (Elevation 2):** Hovered table rows, interactive dropdown triggers, and draggable Kanban elements lift smoothly with: `box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.05)`.
4. **Overlay & Modal Level (Elevation 3):** Modal dialogues, popovers, and slide-in employee records float above a `backdrop-filter: blur(4px)` with background `rgba(15, 23, 42, 0.4)` and a deep grounding shadow: `box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.06)`.

## Shapes

The interface balances enterprise discipline with soft ergonomics. 

### Geometric Rules
- **Base Components (`rounded` / 0.5rem - 8px):** Applied to standard input fields, form dropdowns, default buttons, chips, and badge capsules.
- **Containers & Modules (`rounded-lg` / 1rem - 16px):** Applied to core dashboard summary cards, data panels, personnel profiles, and institutional modals.
- **Micro Accents (`rounded-sm` / 0.25rem - 4px):** Used on inner table checkboxes, progress bar tracks, and nested tooltip containers.
- **Pills / Circles:** Exclusively applied to user avatars, quick notification counters, and status dot indicators.

## Components

### Buttons
- **Primary:** Solid `#004385` deep blue, text white, font-weight 600. Hover: `#0D5C9E`. Active: `#003366`. Border: none.
- **Secondary / Action Accent:** Solid `#F37021` orange with white text for critical actions (e.g., "Phê duyệt hồ sơ" / "Thêm nhân sự mới"). Hover: `#E05D0E`.
- **Outline / Neutral:** Background transparent, border `1px solid #CBD5E1`, text `#334155`. Hover: `#F1F5F9`.
- **Size Specifications:** Small (32px height, 12px horizontal padding), Default (40px height, 16px padding), Large (48px height, 20px padding).

### Chips & Status Badges
- Compact height (24px) with subtle tinted backgrounds and high-contrast text:
  - **Đang công tác (Active):** `#ECFDF5` background, `#047857` text, subtle `#A7F3D0` border.
  - **Nghỉ phép (On Leave):** `#FFFBEB` background, `#B45309` text, `#FDE68A` border.
  - **Chờ duyệt (Pending):** `#FFF7ED` background, `#C2410C` text, `#FFEDD5` border.
  - **Thôi việc (Terminated):** `#FEF2F2` background, `#B91C1C` text, `#FECACA` border.

### Input Fields & Controls
- **Text Inputs:** Height 40px, background `#FFFFFF`, border `1px solid #CBD5E1`, placeholder `#94A3B8`, text `#0F172A`. Focused state: border color `#004385` accompanied by a 3px halo `rgba(0, 67, 133, 0.15)`.
- **Checkboxes & Radios:** 18px square/circle with crisp 1.5px border (`#94A3B8`). Selected: `#004385` fill with pure white checks.

### Data Tables & Roster Lists
- **Header:** Height 44px, background `#F8FAFC`, text `#475569`, typography `label-md`, bottom border `1px solid #E2E8F0`.
- **Row Styling:** Height 52px for standard rows, 64px for profile rows with avatars. Background `#FFFFFF`. Alternating hover background `#F8FAFC` with a smooth 150ms transition.
- **Cell Padding:** 0 16px.

### Cards & Personnel Dossiers
- Background `#FFFFFF`, border `1px solid #E2E8F0`, corner radius `1rem` (16px), inner padding `1.25rem` (20px).
- **Header Segment:** Displays faculty badge, employee ID in mono-styled muted caption, and an avatar with a 2px white border ring.