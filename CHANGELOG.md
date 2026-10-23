# Changelog

All notable changes to this project will be documented in this file.


## 1.2.1 - 2026-10-23

- Rename the Composer package to `stoneholl/magento2-admin-accent-color` and replace its corresponding legacy `stoneholl/module-admin-accent-color` identity at `self.version`.
- Require the canonical Core package from version 1.0.1 and update installation/migration documentation.
- Keep Magento module identifiers, configuration, storage and licensing unchanged.

## 1.2.0 - 2026-10-18

- Declare compatible Admin-user foreign-key widths for Magento 2.3.7 schema installation and upgrades.

- Keep Background/Text together in an element editor with one prepared-color strip and conditional extra properties.
- Add versioned local personal/shared presets with session ownership, ACL, CSRF and bounded atomic persistence.
- Add Reset All Colors for every color, including disabled areas, without removing presets or area switches.

- Add sidebar/submenu and content-link colors, active tab backgrounds, inactive text and section headings.
- Add compact global/personal local previews with a field selector, one shared palette, readable preset samples,
  clearing and compact area switches.
- Derive readable white/black text for configured backgrounds with no explicit foreground, including hover states.
- Warn about manual low-contrast preview text without blocking saves or replacing explicit choices.
- Allow authenticated global styling without My Account editing permission.
- Preserve destructive/disabled split-button exclusions and reject non-text color input before conversion.
- Add nullable personal color columns without changing existing product/configuration identifiers.
- Document compatibility evidence, privacy, upgrades and preference retention; remove method-docblock suppression.

## 1.1.0 - 2026-09-09

- Add independent text colors for active tabs, primary buttons, and the active Admin menu item.
- Add compact color swatches to global and per-administrator color fields.
- Leave Magento and storefront logos unchanged.
- Declare PHP 8.0 support for its compatible Magento release line and document the supported Magento/PHP pairs.

## 1.0.1 - 2026-08-29

- Remove contrast-ratio validation from global and per-administrator color settings.
- Keep strict HEX/RGB normalization for every configured color.
- Prevent a PayPal Braintree 4.6.1 initialization error from disabling Configuration page controls.

## 1.0.0 - 2026-08-29

- Add independently configurable colors for active tabs, primary buttons, and the active Admin menu item.
- Add global settings and optional per-administrator overrides.
- Add strict HEX/RGB normalization for every configured color.
- Apply styles only to the standard `Magento/backend` Admin theme through a private CSP-safe stylesheet response.
