define(['Stoneholl_AdminAccentColor/js/palette', 'mage/translate'], function (palette, $t) {
    'use strict';

    return function (config, element) {
        var form = element.closest('form') || document.getElementById('stoneholl-accent-preferences'),
            feedback = element.querySelector('.stoneholl-preview-feedback'),
            globalValues = JSON.parse(element.getAttribute('data-global-palette') || '{}'),
            controls = {}, defaults = {};

        if (!form || element.getAttribute('data-preview-ready')) {
            return;
        }
        element.setAttribute('data-preview-ready', '1');
        Array.prototype.forEach.call(form.querySelectorAll('input[name],select[name]'), function (input) {
            var name = input.name, match = name.match(/^groups\[areas\]\[fields\]\[([a-z_]+)\]\[value\]$/);

            if (match) {
                controls[match[1]] = input;
            } else if (/^(tabs|buttons|menu|sidebar|links)_/.test(name) && input.type !== 'hidden') {
                controls[name] = input;
            }
        });
        Array.prototype.forEach.call(element.querySelectorAll('[data-preview]'), function (sample) {
            var style = window.getComputedStyle(sample);

            defaults[sample.getAttribute('data-preview')] = {background: style.backgroundColor, color: style.color};
        });

        function readValues() {
            var values = {}, globalToggle = form.querySelector('[name="use_global"]');

            if (globalToggle && globalToggle.value === '1') {
                return globalValues;
            }
            Object.keys(controls).forEach(function (name) {
                var input = controls[name];

                values[name] = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
            });
            return values;
        }

        function update() {
            var values = readValues(), warnings = [], invalid = [], checked = 0,
                globalToggle = form.querySelector('[name="use_global"]');

            if (globalToggle) {
                Array.prototype.forEach.call(form.querySelectorAll('.stoneholl-palette-area'), function (area) {
                    area.disabled = globalToggle.value === '1';
                });
            }

            function color(name) {
                var area = name.split('_')[0], value = String(values[name] || '');

                if (values[area + '_enabled'] === '0') {
                    return null;
                }
                if (value.trim() && !palette.normalize(value) && invalid.indexOf(name) === -1) {
                    invalid.push(name);
                }
                return palette.normalize(value);
            }

            function sample(role, background, foreground, label, textField, check) {
                var node = element.querySelector('[data-preview="' + role + '"]'), actual, bg, fg, ratio;

                node.style.backgroundColor = background || '';
                node.style.color = foreground || (background ? palette.readableText(background) : '');
                if (!check) {
                    return;
                }
                actual = window.getComputedStyle(node);
                bg = palette.normalize(actual.backgroundColor) || palette.normalize(defaults[role].background) || '#FFFFFF';
                fg = palette.normalize(actual.color);
                if (!fg) {
                    return;
                }
                checked++;
                ratio = palette.ratio(fg, bg);
                if (ratio < 4.5) {
                    warnings.push({label: label, ratio: ratio, field: textField, auto: !!background});
                }
            }

            function pair(role, bgField, textField, label) {
                var bg = color(bgField), fg = color(textField);

                sample(role, bg, fg, label, textField, !!(bg || fg));
            }

            pair('tab', 'tabs_background', 'tabs_text_color', $t('Active tab'));
            element.querySelector('[data-preview="tab"]').style.borderTopColor = color('tabs_color') || '';
            sample('inactive-tab', null, color('tabs_inactive_color'), $t('Inactive tab'),
                'tabs_inactive_color', !!color('tabs_inactive_color'));
            sample('inactive-tab-hover', null, color('tabs_inactive_color'), $t('Inactive tab hover'),
                'tabs_inactive_color', !!color('tabs_inactive_color'));
            pair('heading', 'tabs_heading_color', 'tabs_heading_text_color', $t('Section heading'));
            pair('menu', 'menu_color', 'menu_text_color', $t('Active menu'));
            pair('sidebar-item', 'sidebar_color', 'sidebar_text_color', $t('Sidebar'));
            sample('sidebar', color('sidebar_color'), null, '', '', false);
            sample('sidebar-hover', color('sidebar_hover_color') ||
                (color('sidebar_color') ? palette.darken(color('sidebar_color')) : null),
                color('sidebar_hover_text_color') || color('sidebar_text_color'), $t('Sidebar hover'),
                color('sidebar_hover_text_color') ? 'sidebar_hover_text_color' : 'sidebar_text_color',
                !!(color('sidebar_hover_color') || color('sidebar_color') ||
                    color('sidebar_hover_text_color') || color('sidebar_text_color')));
            pair('submenu', 'sidebar_submenu_color', 'sidebar_submenu_text_color', $t('Submenu'));
            sample('submenu-hover', color('sidebar_submenu_color') ? palette.darken(color('sidebar_submenu_color')) : null,
                color('sidebar_submenu_text_color'), $t('Submenu hover'), 'sidebar_submenu_text_color',
                !!(color('sidebar_submenu_color') || color('sidebar_submenu_text_color')));
            pair('button', 'buttons_color', 'buttons_text_color', $t('Primary action'));
            sample('button-hover', color('buttons_color') ? palette.darken(color('buttons_color')) : null,
                color('buttons_text_color'), $t('Primary action hover'), 'buttons_text_color',
                !!(color('buttons_color') || color('buttons_text_color')));
            sample('link', null, color('links_color'), $t('Content link'), 'links_color', !!color('links_color'));
            sample('link-hover', null, color('links_hover_color') || color('links_color'), $t('Link hover'),
                color('links_hover_color') ? 'links_hover_color' : 'links_color',
                !!(color('links_hover_color') || color('links_color')));
            feedback.textContent = '';
            feedback.classList.toggle('_warning', warnings.length > 0 || invalid.length > 0);
            if (warnings.length || invalid.length) {
                var list = document.createElement('ul');

                warnings.forEach(function (warning) {
                    var item = document.createElement('li'), button;

                    item.textContent = warning.label + ': ' + warning.ratio.toFixed(2) + ':1 — ' +
                        $t('Low contrast. Aim for at least 4.5:1.');
                    if (warning.auto && controls[warning.field] &&
                        (!form.querySelector('[name="use_global"]') ||
                        form.querySelector('[name="use_global"]').value !== '1')) {
                        button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'action-default';
                        button.textContent = $t('Use automatic text');
                        button.addEventListener('click', function () {
                            controls[warning.field].value = '';
                            controls[warning.field].dispatchEvent(new Event('change', {bubbles: true}));
                        });
                        item.appendChild(button);
                    }
                    list.appendChild(item);
                });
                invalid.forEach(function (name) {
                    var item = document.createElement('li');

                    item.textContent = name + ': ' + $t('Invalid color. Preview uses the theme value.');
                    list.appendChild(item);
                });
                feedback.appendChild(list);
            } else {
                feedback.textContent = checked ? $t('Configured preview text meets 4.5:1, including hover states.') :
                    $t('Theme colors. Choose a color to preview your palette.');
            }
        }

        form.addEventListener('input', update);
        form.addEventListener('change', update);
        update();
    };
});
