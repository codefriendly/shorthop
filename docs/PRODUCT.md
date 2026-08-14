# Product

## Register

product

## Users

ShortHop is personally operated, but client-facing: one operator manages short links for their own client work instead of relying on commercial shorteners like Bitly. The operator needs fast private management, while recipients should experience clean, trustworthy branded links.

## Product Purpose

ShortHop is a client-facing URL shortener for an `APP_URL`-defined domain. It exists to make creating, finding, opening, and understanding short links fast without turning the redirect path into a complicated application surface. Success means the operator can add a link quickly, share a branded short URL with confidence, trust redirects to stay thin and reliable, and get just enough visit insight to know what people open.

## Brand Personality

Precise, quiet, tactile. The product should feel deliberate and professional: more like a well-kept client link ledger than a generic SaaS analytics dashboard.

## Anti-references

Avoid generic SaaS polish: interchangeable gradients, hero metric blocks, glass panels, and startup-template layouts. Avoid terminal cosplay: the app should not look like a CLI just because URLs are technical. Avoid busy analytics-suite styling: dense enterprise BI patterns are the wrong weight for a client-facing short-link tool.

## Design Principles

1. Fast link management comes first: creating, finding, copying, and opening client links should feel effortless.
2. Keep redirects visually and architecturally quiet: public link resolution should feel reliable, not like a marketing or dashboard experience.
3. Show only useful signal: visit data should clarify client-facing activity without becoming a heavy analytics product.
4. Use tactile specificity over generic polish: labels, surfaces, and copy should feel crafted for short links rather than borrowed from SaaS templates.
5. Keep `/` explanatory, not promotional: most public visitors use `/{urlKey}`, while root visitors only need to know what the domain is.
6. Preserve portability: public copy and branding should derive from configuration where possible, especially `APP_URL`, so the project can be open-sourced.

## Accessibility & Inclusion

Aim for WCAG AAA where practical, with WCAG AA as a minimum baseline. Maintain strong text contrast, visible keyboard focus, readable line lengths, and reduced-motion alternatives for any animation. Avoid relying on color alone for state or meaning.
