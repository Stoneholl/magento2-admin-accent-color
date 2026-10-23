# Stoneholl Admin Accent Color

Stoneholl Admin Accent Color is a small Magento Open Source module for changing selected accent colors in the
standard Magento Admin theme. It is intentionally narrower than a complete Admin theme or an environment-labeling
extension.

## Features

- Independent accent and text colors for active tabs, primary buttons, and the active top-level menu item.
- Active tab backgrounds, inactive tab text and configuration section heading colors.
- Sidebar background, text/icons, hover states and submenu colors.
- Conservative content-link colors, excluding links with classes, roles or data-role attributes.
- A local live preview on global and personal settings pages, including hover samples.
- One compact element editor with Background and Text always visible, conditional border/hover fields and one swatch strip.
- Local named personal/shared palette presets with Save As, Load and permission-checked deletion.
- Reset All Colors clears all eighteen colors while preserving area switches and saved presets.
- Compact area switches and non-blocking contrast warnings for configured preview text.
- Global settings for every administrator, with an optional personal override mode.
- A native color picker plus text input accepting `#RRGGBB`, `RRGGBB`, or `rgb(R, G, B)`.
- Compact preset swatches fill only the current field, without applying a complete palette.
- Empty backgrounds preserve native styling. A configured background with empty text uses readable white or black.
- Save-time normalization and the CSS-boundary allowlist prevent arbitrary CSS from reaching the stylesheet.
- Color values are normalized and restricted to HEX or RGB input.
- No core edits, preferences, generated CSS files, or static content deployment after a configuration change.

The module does not recolor destructive actions, errors, warnings, validation states, or disabled controls. It does
not provide environment badges, arbitrary CSS, dark mode, logo customization, or a full
Admin theme.

## Compatibility

The package declares Magento Open Source 2.3.7-p4 through 2.4.9 and PHP 7.4 through 8.5,
within each Magento release's supported PHP combinations. Composer ranges are install-time
metadata, not evidence that every Magento/PHP combination was runtime-tested.

Release-candidate runtime checks use Magento Open Source **2.3.7-p4 / PHP 7.4.33** and
**2.4.9 / PHP 8.5.9**, with released Core 1.0.0. Installation, schema upgrade, DI compilation,
local preset/preference boundaries and Admin assets are checked on those exact pairs. PHP 7.4/8.0/8.5
syntax checks cover source grammar only; intermediate Magento/PHP combinations remain declarations.
Adobe Commerce and custom Admin themes are not certified by these checks.

Custom Admin themes, including child themes and themes that replace Magento's Admin DOM or selectors, are not
styled. The configuration page reports the detected theme and the module returns no CSS for an unsupported theme.
There is no force-apply option. Support for M137 or another theme should be supplied by a separately tested adapter,
not by broad selectors in this package.

## Installation with Composer

Stoneholl Core 1.x is required for the shared offline product registry and Admin navigation. Stoneholl Connector is
optional and never controls Admin Accent Color functionality. After the package is published in a Composer repository:

```bash
composer require stoneholl/magento2-admin-accent-color
bin/magento module:enable Stoneholl_Core Stoneholl_AdminAccentColor
bin/magento setup:upgrade
bin/magento cache:flush
```

Run dependency-injection compilation and static content deployment as required by the deployment mode.

## Installation from app/code

Place Core at `app/code/Stoneholl/Core` and this module at `app/code/Stoneholl/AdminAccentColor`, then run:

```bash
bin/magento module:enable Stoneholl_Core Stoneholl_AdminAccentColor
bin/magento setup:upgrade
bin/magento cache:flush
```

## Configuration

Open **Stores > Configuration > Stoneholl > Admin Accent Color**.

1. Set **Enabled** to **Yes**.
2. Select **Global for All Administrators** or **Allow Personal Overrides**.
3. Enable the required accent areas and enter their colors. Leave a color empty to use the standard theme value.
4. Save the configuration and reload the Admin page.

When personal overrides are allowed, each administrator can open **My Account > Configure My Accent Colors**. A
personal palette can be removed at any time by selecting **Use Global Settings** and saving.

Global configuration is intentionally available only at Default scope. Website and Store scopes do not map to the
Admin area.

## Preview and readability

Choose an **Element**. **Background** and **Text** remain visible together; unsupported properties are disabled.
Extra border and hover rows appear for relevant elements. Each row has a HEX/RGB input, picker and **Clear**.
Select the Background/Text row to direct the single strip of nine prepared swatches to that property.
Switching elements preserves other colors. Five compact switches enable areas. Native named controls remain
available for Magento validation/submission and are visible as a fallback until the editor initializes.

**Save As...** stores a complete versioned palette (all eighteen colors and five area switches), including an unsaved
draft. Choose **Personal** for your own presets or **Shared** for presets visible to permitted editors. Shared
creation/deletion requires module configuration permission; personal operations require **My Account**. Full
administrators cannot access other users' personal presets. Names are 1–64 letters/numbers, spaces, dots, hyphens,
underscores or parentheses. Each scope permits up to 50 presets. Existing names are not overwritten.

**Load** replaces the draft palette and updates the preview. **Reset All Colors** clears every color, including colors
in disabled areas, and preserves area switches, module/mode settings and saved presets. Both require the native
**Save** / **Save Config** to apply. Preset creation and deletion take effect immediately; deleting a preset keeps the
current colors. There is no import/export or remote synchronization.

The preview reads unsaved form values locally. It does not save configuration or recolor the
surrounding Admin until the native form is saved. On the personal page, **Use Global Settings**
previews the current global palette and disables personal editing; choose **Use My Colors** to edit.

With a configured background and empty text, the stylesheet chooses white when its sRGB contrast
ratio is at least 4.5:1, otherwise black. Button and submenu hover backgrounds are derived separately,
and their automatic text is recalculated for that state. Existing explicit text colors are preserved.
A manual low-contrast pair produces a preview warning; **Use automatic text** clears that foreground.
Saving remains available. Border colors and unmodified native theme colors are not contrast-certified.

The same nine prepared swatch colors remain available. Blue, green, purple, teal, red and dark backgrounds use white
sample text; orange and white use black. The preview checks configured text against its sample
backgrounds, not every possible background introduced by other extensions. Verify the actual Admin
pages, especially when setting only foreground colors.

## Upgrade and removal

Back up the database before upgrading. Run `setup:upgrade` to add the nullable color columns and
new area switches to `stoneholl_admin_accent_user` and the local `stoneholl_admin_accent_preset` table, then compile DI and deploy Admin static content
as required by your deployment mode. Existing identifiers, configuration paths and personal colors
are retained. Admin-user foreign keys declare the width required by the Magento 2.3.7 schema validator. New fields are initially empty; new areas have no visual effect until a color is chosen.

Global settings require **Admin Accent Color Configuration** permission. Personal editing requires
**My Account**. Any authenticated administrator can receive their effective global stylesheet without
My Account permission. Anonymous requests cannot read a palette. All personal writes are bound to
the current session user and Magento form-key protection.

Choose **Use Global Settings** and save to remove your personal record. Deleting an administrator
cascades to that user's preference and personal presets. Shared presets survive administrator deletion.
Disabling the module retains configuration, preferences and presets.
Removing its source and subsequently running declarative-schema upgrades can remove the module's
tables; preserve any preferences and presets you need before uninstalling. Remove global configuration explicitly
as part of an approved uninstall procedure. No automatic credential, account or Connector cleanup occurs.

## Security and caching

Effective settings are returned as a same-origin CSS resource linked from authenticated Admin pages. The response is
private and uses `no-store`; it contains only allowlisted selectors and normalized six-digit HEX values. The live stylesheet needs
no inline style/script injection, unsafe CSP allowances, or writable generated asset directories.
The local preview and preset samples update only normalized color properties through the CSSOM.

Personal preferences are stored in `stoneholl_admin_accent_user`, keyed to `admin_user` with cascading deletion. No
customer data or Admin identity details are copied into the table.

Presets are local normalized JSON snapshots in `stoneholl_admin_accent_preset`. Personal ownership comes exclusively
from the authenticated session; shared writes require module configuration ACL. List responses contain shared and
current-user records only and use `no-store`/`nosniff`. Mutations use native POST/form-key protection, parameterized
queries, strict version/field/type validation and a bounded 8 KB payload. Invalid input cannot partially save a palette.
The module remains free under MIT, requires offline Core, and does not require Connector, licensing or a network account.

## Theme and module conflicts

Another extension may override the same selectors with greater specificity or `!important`. A dark or customized
theme may also use incompatible foreground colors. In either case this module deliberately does not escalate with
aggressive selectors or `!important`.

Full Admin suites such as Magefan Admin View, Mage-OS M137, Mage2Dark, or system9 Admin Theme solve broader and
different problems. Use this package when only a conservative accent adjustment to the standard Magento Admin is
required.

## License

This module is released under the MIT License. See [LICENSE](LICENSE).

## Composer package naming

The canonical Composer name is `stoneholl/magento2-admin-accent-color`. The former `stoneholl/module-admin-accent-color` name is a legacy identity. The renamed package replaces that identity at `self.version`, so Composer installs one copy of the Magento module. Existing immutable releases remain unchanged.

When migrating an existing root requirement, back up the project and remove the old requirement without updating, then require the new package with its dependencies:

```bash
composer remove stoneholl/module-admin-accent-color --no-update
composer require stoneholl/magento2-admin-accent-color --with-all-dependencies
```

Run the Magento upgrade, compilation and cache commands documented above according to your deployment mode. Keep native `Stoneholl_*` module names and configuration paths unchanged.
