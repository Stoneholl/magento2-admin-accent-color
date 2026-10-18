define([
    'Stoneholl_AdminAccentColor/js/palette',
    'Stoneholl_AdminAccentColor/js/presets',
    'mage/translate'
], function (palette, initializePresets, $t) {
    'use strict';

    return function (unused, element) {
        var form = element.closest('form') || document.getElementById('stoneholl-accent-preferences'),
            target = element.querySelector('.stoneholl-palette-target'),
            swatches = element.querySelector('.stoneholl-accent-color-swatches'),
            preview = element.closest('.stoneholl-palette-preview'),
            config = JSON.parse(preview.getAttribute('data-editor-config') || '{}'),
            globalValues = JSON.parse(preview.getAttribute('data-global-palette') || '{}'),
            controls = {}, flags, rows, activeSlot = 'background', dirty = {}, observer;

        if (!form || element.getAttribute('data-palette-ready')) {
            return;
        }
        Array.prototype.forEach.call(form.querySelectorAll('input[name],select[name]'), function (source) {
            var match = source.name.match(/^groups\[areas\]\[fields\]\[([a-z_]+)\]\[value\]$/);

            if (match) {
                controls[match[1]] = source;
            } else if (/^(tabs|buttons|menu|sidebar|links)_/.test(source.name) && source.type !== 'hidden') {
                controls[source.name] = source;
            }
        });
        flags = Array.prototype.slice.call(element.querySelectorAll('[data-palette-toggle]'));
        rows = Array.prototype.slice.call(element.querySelectorAll('[data-color-row]'));
        if (!config.colors || config.colors.some(function (key) { return !controls[key]; }) ||
            flags.some(function (flag) { return !controls[flag.dataset.paletteToggle + '_enabled']; })) {
            element.hidden = true;
            return;
        }
        element.setAttribute('data-palette-ready', '1');

        function usesGlobal() {
            var toggle = form.querySelector('[name="use_global"]');

            return !!(toggle && toggle.value === '1');
        }

        function readOnly() {
            var toggle = form.querySelector('[name="use_global"]');

            return usesGlobal() || !!(toggle && toggle.matches(':disabled'));
        }

        function areaEnabled(area) {
            var source = controls[area + '_enabled'];

            return usesGlobal() ? globalValues[area + '_enabled'] === '1' :
                (source.type === 'checkbox' ? source.checked : source.value !== '0');
        }

        function definition() {
            return config.elements[target.value];
        }

        function canEdit(slot) {
            var entry = definition();

            return !!(entry && entry[slot] && !readOnly() && areaEnabled(entry.area) &&
                !controls[entry[slot]].matches(':disabled'));
        }

        function sync() {
            var entry = definition(), selectedColor;

            if (!entry) {
                return;
            }
            if (!entry[activeSlot]) {
                activeSlot = entry.background ? 'background' : 'text';
            }
            rows.forEach(function (row) {
                var slot = row.dataset.colorRow, source = controls[entry[slot]],
                    input = row.querySelector('.stoneholl-shared-color-value'),
                    value = source ? (usesGlobal() ? globalValues[entry[slot]] || '' : source.value) : '',
                    normalized = palette.normalize(value), enabled = canEdit(slot),
                    button = row.querySelector('.stoneholl-color-slot');

                row.hidden = slot !== 'background' && slot !== 'text' && !source;
                row.style.display = row.hidden ? 'none' : '';
                row.classList.toggle('_selected', activeSlot === slot);
                button.setAttribute('aria-pressed', activeSlot === slot ? 'true' : 'false');
                button.disabled = !enabled;
                if (input.value !== value) {
                    input.value = value;
                }
                input.disabled = !enabled;
                input.placeholder = source ? (slot === 'text' ? $t('Automatic') : $t('Theme / automatic')) : $t('Not available');
                row.querySelector('.stoneholl-accent-color-picker').value = normalized || '#EB5202';
                row.querySelector('.stoneholl-accent-color-picker').disabled = !enabled;
                row.querySelector('.stoneholl-accent-color-reset').disabled = !enabled;
            });
            selectedColor = palette.normalize(usesGlobal() ? globalValues[entry[activeSlot]] :
                controls[entry[activeSlot]].value);
            Array.prototype.forEach.call(swatches.children, function (button) {
                var selected = selectedColor === button.dataset.color;

                button.classList.toggle('_active', selected);
                button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                button.disabled = !canEdit(activeSlot);
            });
            flags.forEach(function (flag) {
                var source = controls[flag.dataset.paletteToggle + '_enabled'];

                flag.checked = areaEnabled(flag.dataset.paletteToggle);
                flag.disabled = readOnly() || source.matches(':disabled');
            });
            preview.querySelector('[data-palette-reset-all]').disabled = readOnly();
        }

        function notify() {
            controls[config.colors[0]].dispatchEvent(new Event('change', {bubbles: true}));
        }

        function includeColor(key) {
            dirty[key] = true;
            // Native configuration dependencies must not omit reset/loaded colors in disabled areas.
            if (controls[key].name.indexOf('groups[') === 0) {
                controls[key].disabled = false;
            }
        }

        function write(slot, value) {
            var key = definition()[slot];

            if (!canEdit(slot)) {
                return;
            }
            activeSlot = slot;
            controls[key].value = value;
            controls[key].dispatchEvent(new Event('input', {bubbles: true}));
            controls[key].dispatchEvent(new Event('change', {bubbles: true}));
        }

        function snapshot() {
            var result = {schema_version: 1, colors: {}, areas: {}};

            config.colors.forEach(function (key) { result.colors[key] = controls[key].value || null; });
            flags.forEach(function (flag) { result.areas[flag.dataset.paletteToggle] = areaEnabled(flag.dataset.paletteToggle); });
            return result;
        }

        function apply(data) {
            var normalized = {};

            if (readOnly() || !data || data.schema_version !== 1 || !data.colors || !data.areas) {
                throw new Error($t('The preset cannot be loaded in this mode.'));
            }
            config.colors.forEach(function (key) {
                var value = data.colors[key];

                if (value !== null && (typeof value !== 'string' || !palette.normalize(value))) {
                    throw new Error($t('The preset contains an invalid color.'));
                }
                normalized[key] = value === null ? '' : palette.normalize(value);
            });
            flags.forEach(function (flag) {
                if (typeof data.areas[flag.dataset.paletteToggle] !== 'boolean') {
                    throw new Error($t('The preset is incomplete.'));
                }
            });
            flags.forEach(function (flag) {
                var source = controls[flag.dataset.paletteToggle + '_enabled'], value = data.areas[flag.dataset.paletteToggle];

                if (source.type === 'checkbox') {
                    source.checked = value;
                } else {
                    source.value = value ? '1' : '0';
                }
                source.dispatchEvent(new Event('change', {bubbles: true}));
            });
            config.colors.forEach(function (key) {
                controls[key].value = normalized[key];
                includeColor(key);
            });
            notify();
        }

        palette.presets.forEach(function (hex) {
            var button = document.createElement('button');

            button.type = 'button';
            button.className = 'stoneholl-accent-color-swatch';
            button.dataset.color = hex;
            button.setAttribute('aria-label', $t('Use color') + ' ' + hex);
            button.title = hex;
            button.textContent = 'Aa';
            button.style.backgroundColor = hex;
            button.style.color = palette.readableText(hex);
            button.addEventListener('click', function () { write(activeSlot, hex); });
            swatches.appendChild(button);
        });
        rows.forEach(function (row) {
            var slot = row.dataset.colorRow, input = row.querySelector('.stoneholl-shared-color-value'),
                picker = row.querySelector('.stoneholl-accent-color-picker');

            function activate() { activeSlot = slot; sync(); }
            row.querySelector('.stoneholl-color-slot').addEventListener('click', activate);
            input.addEventListener('focus', activate);
            picker.addEventListener('focus', activate);
            input.addEventListener('input', function () { write(slot, input.value); });
            input.addEventListener('change', function () { write(slot, input.value); });
            picker.addEventListener('input', function () { write(slot, picker.value.toUpperCase()); });
            row.querySelector('.stoneholl-accent-color-reset').addEventListener('click', function () { write(slot, ''); });
        });
        target.addEventListener('change', function () { activeSlot = definition().background ? 'background' : 'text'; sync(); });
        flags.forEach(function (flag) {
            flag.addEventListener('input', function () {
                var native = controls[flag.dataset.paletteToggle + '_enabled'];

                if (flag.disabled) {
                    return;
                }
                if (native.type === 'checkbox') {
                    native.checked = flag.checked;
                } else {
                    native.value = flag.checked ? '1' : '0';
                }
                native.dispatchEvent(new Event('change', {bubbles: true}));
            });
        });
        preview.querySelector('[data-palette-reset-all]').addEventListener('click', function () {
            if (readOnly()) {
                return;
            }
            config.colors.forEach(function (key) { controls[key].value = ''; includeColor(key); });
            notify();
            preview.querySelector('.stoneholl-preset-status').textContent = $t('All colors reset in preview. Save to apply.');
        });
        form.addEventListener('input', sync);
        form.addEventListener('change', sync);
        form.addEventListener('submit', function () {
            if (!readOnly()) {
                Object.keys(dirty).forEach(includeColor);
            }
        }, true);
        observer = new MutationObserver(sync);
        Object.keys(controls).forEach(function (key) {
            observer.observe(controls[key], {attributes: true, attributeFilter: ['disabled']});
        });
        Array.prototype.forEach.call(form.querySelectorAll('fieldset'), function (fieldset) {
            observer.observe(fieldset, {attributes: true, attributeFilter: ['disabled']});
        });
        config.colors.forEach(function (key) {
            var section = controls[key].closest('.stoneholl-palette-area') || controls[key].closest('.section-config');

            if (section) {
                section.hidden = true;
                section.style.display = 'none';
            }
        });
        sync();
        initializePresets({form: form, readOnly: readOnly, snapshot: snapshot, apply: apply}, preview, config.urls);
    };
});
