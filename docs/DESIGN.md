---
name: ShortHop
description: A precise, quiet, tactile design system for client-facing short-link management.
colors:
  slate-950: "Tailwind slate-950"
  slate-700: "Tailwind slate-700"
  slate-500: "Tailwind slate-500"
  slate-100: "Tailwind slate-100"
  white: "#ffffff"
typography:
  display:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(3rem, 8vw, 6rem)"
    fontWeight: 900
    lineHeight: 0.93
    letterSpacing: "-0.04em"
  headline:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2.25rem"
    fontWeight: 900
    lineHeight: 1
    letterSpacing: "-0.04em"
  title:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 700
    lineHeight: 1.25
  body:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: 1.75
  label:
    fontFamily: "ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace"
    fontSize: "0.75rem"
    fontWeight: 700
    lineHeight: 1
    letterSpacing: "0.24em"
rounded:
  sm: "8px"
  md: "16px"
  lg: "24px"
  pill: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "40px"
components:
  button-primary:
    backgroundColor: "framework default"
    textColor: "framework default"
    rounded: "{rounded.pill}"
    padding: "12px 20px"
  button-secondary:
    backgroundColor: "transparent"
    textColor: "{colors.slate-950}"
    rounded: "{rounded.pill}"
    padding: "8px 16px"
  link-card:
    backgroundColor: "{colors.white}"
    textColor: "{colors.slate-950}"
    rounded: "{rounded.lg}"
    padding: "20px"
---

# Design System: ShortHop

## 1. Overview

**Creative North Star: "The Client Link Ledger"**

ShortHop should feel like a precise ledger for client-facing links: quietly professional, fast to scan, and tactile enough that every URL feels deliberately kept. The system serves a personal operator who shares branded short links with clients, so the visual language must balance private-tool efficiency with public-facing confidence.

The current surface uses Tailwind's built-in slate and white palette. It should keep a quiet, task-first quality while resisting generic SaaS polish, terminal cosplay, and busy analytics-suite styling. The product can be memorable through clarity and restraint, but it should never make routine link management feel theatrical. The public root page is informational, not promotional.

**Key Characteristics:**
- Client-facing confidence without commercial-shortener gloss.
- Crisp labels and plain structure instead of branded decoration.
- Restrained data display: enough visit signal to act, never a BI dashboard.
- Configurable public identity through `APP_URL`, not hardcoded domain branding.

## 2. Colors

The palette is neutral and slate-first. Prefer Tailwind's built-in `slate-*`, `neutral-*`, `white`, and framework defaults before adding project-specific color tokens. ShortHop does not need a brand accent across dashboard buttons.

### Neutral
- **Slate 950**: Primary text and high-confidence strokes.
- **Slate 700**: Supporting prose where full ink would be too loud.
- **Slate 500**: Metadata, secondary labels, and small utility text.
- **Slate 100**: Quiet page background.
- **White**: Primary panel and inset surfaces.

### Named Rules
**The Built-In First Rule.** Use Tailwind and Filament defaults before creating custom palette names. Custom colors need a concrete product reason, not branding by habit.

## 3. Typography

**Display Font:** Instrument Sans with system sans fallbacks.
**Body Font:** Instrument Sans with system sans fallbacks.
**Label/Mono Font:** System monospace for short keys, URL labels, and compact metadata.

**Character:** The system uses one sans family for trust and continuity, with strong weight contrast for the public entry page. Monospace is allowed only where text is genuinely code-like or label-like: short keys, route fragments, and metadata.

### Hierarchy
- **Display** (900, clamp max 6rem, 0.93 line-height): Avoid on the root page unless the root page role changes. Letter spacing must never be tighter than `-0.04em`.
- **Headline** (900, 2.25rem, 1 line-height): Major product panels and empty-state titles.
- **Title** (700, 1.125rem, 1.25 line-height): Link names, table headings, and card titles.
- **Body** (400, 1.125rem, 1.75 line-height): Explanatory copy. Cap prose at 65-75ch.
- **Label** (700, 0.75rem, tracked uppercase): Short, functional labels only. Do not use tracked labels as repeated section decoration.

### Named Rules
**The Short-Key Rule.** Monospace appears when the text behaves like a key, route, or compact machine label. It is not a costume for the whole product.

**The No-Cramp Rule.** Display letter spacing floors at `-0.04em`. Tighter tracking makes the page feel generated and cramped.

## 4. Elevation

ShortHop uses tactile but restrained depth. Surfaces are primarily separated by color, border, and proportion. Shadows should feel structural and deliberate, not soft ghost-card decoration.

### Shadow Vocabulary
- **Product Surfaces** (`none` or framework defaults): Primary elevation vocabulary.
- **Subtle Borders** (`border-slate-950/10` to `border-slate-950/20`): Preferred public-page separation.

### Named Rules
**The No-Decorative-Lift Rule.** Prefer flat surfaces and subtle borders. Do not pair a thin border with a large soft blur for decoration.

## 5. Components

### Buttons
- **Shape:** Full pill for CTAs and nav actions.
- **Primary:** Use framework defaults inside Filament/Flux unless a workflow need justifies customization.
- **Hover / Focus:** Keep feedback visible but quiet. Focus must use a visible offset outline.
- **Secondary / Ghost:** Outlined buttons are appropriate for low-pressure public navigation.

### Chips
- **Style:** Status chips should use framework/default semantic treatments where available.
- **State:** Chips should communicate state, never decorate a card just to add color.

### Cards / Containers
- **Corner Style:** Moderate rounded corners for cards, maxing at 24px for signature objects and 16px for product UI surfaces.
- **Background:** White for primary panels and `slate-100` for quiet page backgrounds.
- **Shadow Strategy:** Product UI should stay flat unless the framework supplies elevation.
- **Border:** Subtle slate borders can define edges. Avoid colored side stripes.
- **Internal Padding:** 20-24px on cards; larger section padding can use 40px on desktop.

### Inputs / Fields
- **Style:** Follow Flux/Filament defaults for authenticated UI, using the existing accent/focus system.
- **Focus:** Visible ring is mandatory; never rely only on border color.
- **Error / Disabled:** Use framework semantics first, with clear copy and non-color indicators where possible.

### Navigation
- **Style:** Compact, direct, and stable. The public page uses a small brand mark plus one route action. Authenticated product surfaces should preserve Filament/Flux conventions unless there is a clear workflow improvement.

### Root Explainer
The public root page exists for people who visit the domain directly and wonder what it is. It should explain that the domain is used for short links, show the expected `domain/key` shape, and point managers to sign in. It should not act like a marketing landing page or imply that visitors need to adopt the product.

## 6. Do's and Don'ts

### Do:
- **Do** keep client-facing link creation and sharing fast: the primary action should be obvious in one glance.
- **Do** derive public domain copy from `APP_URL`; never hardcode deployment-specific branding in reusable templates.
- **Do** keep the root page quiet and explanatory: most public visitors arrive through `/{urlKey}`, not `/`.
- **Do** use built-in Tailwind/Filament color systems before adding custom colors.
- **Do** keep dashboard actions neutral unless state or workflow requires emphasis.
- **Do** preserve Filament/Flux component conventions inside the authenticated app unless a workflow need justifies a custom treatment.

### Don't:
- **Don't** use generic SaaS polish: interchangeable gradients, hero metric blocks, glass panels, or startup-template layouts.
- **Don't** use terminal cosplay: monospace everywhere, CLI-like decoration, or hacker styling just because URLs are technical.
- **Don't** use busy analytics-suite styling: dense enterprise BI charts, crowded metric walls, or heavy dashboards.
- **Don't** turn `/` into a marketing page unless the product strategy changes.
- **Don't** use side-stripe accent borders, gradient text, or decorative grid backgrounds as filler.
- **Don't** make public link management feel theatrical; this is a client-facing utility with craft, not a campaign site.
