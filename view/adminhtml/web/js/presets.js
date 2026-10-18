define(['jquery', 'mage/translate', 'Magento_Ui/js/modal/confirm'], function ($, $t, confirm) {
    'use strict';

    return function (editor, root, urls) {
        var select = root.querySelector('.stoneholl-preset-select'),
            name = root.querySelector('.stoneholl-preset-name'),
            scope = root.querySelector('.stoneholl-preset-scope'),
            savePanel = root.querySelector('.stoneholl-preset-save'),
            status = root.querySelector('.stoneholl-preset-status'),
            items = [], capabilities = {}, busy = false, ready = false;

        function selected() {
            return items.filter(function (item) { return String(item.id) === select.value; })[0];
        }

        function sync() {
            var item = selected();

            root.querySelector('[data-preset-action="load"]').disabled = busy || !item || editor.readOnly();
            root.querySelector('[data-preset-action="delete"]').disabled = busy || !item || !item.can_delete;
            root.querySelector('[data-preset-action="save-as"]').disabled = busy || !ready || editor.readOnly() ||
                !(capabilities.can_shared || capabilities.can_personal);
            root.querySelector('[data-preset-action="save"]').disabled = busy || editor.readOnly();
            select.disabled = busy || !ready;
            scope.disabled = busy;
            name.disabled = busy;
        }

        function request(action, data, method, done) {
            var token = editor.form.querySelector('[name="form_key"]'), result, succeeded = false;

            if (busy) {
                return;
            }
            busy = true;
            sync();
            if (method === 'POST') {
                data.form_key = token ? token.value : '';
            }
            $.ajax({url: urls[action], type: method, dataType: 'json', data: data, cache: false})
                .done(function (response) {
                    if (!response || !response.success) {
                        status.textContent = response && response.message ? response.message : $t('The preset request failed.');
                        return;
                    }
                    result = response.data;
                    succeeded = true;
                })
                .fail(function (xhr) {
                    status.textContent = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message :
                        $t('Preset request failed. Reload the page if your session has expired.');
                })
                .always(function () {
                    busy = false;
                    if (succeeded) { done(result); }
                    sync();
                });
        }

        function render(data, selectedId) {
            var shared = document.createElement('optgroup'), personal = document.createElement('optgroup'), empty;

            capabilities = data;
            items = data.presets;
            select.textContent = '';
            empty = document.createElement('option');
            empty.value = '';
            empty.textContent = $t('Select a preset');
            select.appendChild(empty);
            shared.label = $t('Shared');
            personal.label = $t('Personal');
            items.forEach(function (item) {
                var option = document.createElement('option');

                option.value = String(item.id);
                option.textContent = item.name;
                (item.scope === 'shared' ? shared : personal).appendChild(option);
            });
            if (shared.children.length) { select.appendChild(shared); }
            if (personal.children.length) { select.appendChild(personal); }
            select.value = selectedId ? String(selectedId) : '';
            scope.textContent = '';
            ['personal', 'shared'].forEach(function (key) {
                if (capabilities['can_' + key]) {
                    var option = document.createElement('option');

                    option.value = key;
                    option.textContent = key === 'shared' ? $t('Shared') : $t('Personal');
                    scope.appendChild(option);
                }
            });
            if (!editor.form.querySelector('[name="use_global"]') && capabilities.can_shared) {
                scope.value = 'shared';
            }
            ready = true;
        }

        function reloadAfterMutation(id, message) {
            request('index', {}, 'GET', function (data) { render(data, id); status.textContent = message; });
        }

        root.querySelector('[data-preset-action="load"]').addEventListener('click', function () {
            var item = selected();

            if (!item || busy) { return; }
            try {
                editor.apply(item.palette);
                status.textContent = $t('Preset loaded into preview. Save to apply colors.');
            } catch (error) {
                status.textContent = error.message;
            }
        });
        root.querySelector('[data-preset-action="save-as"]').addEventListener('click', function () {
            savePanel.hidden = false;
            savePanel.style.display = 'flex';
            name.value = '';
            name.focus();
        });
        root.querySelector('[data-preset-action="cancel"]').addEventListener('click', function () {
            savePanel.hidden = true;
            savePanel.style.display = 'none';
        });
        root.querySelector('[data-preset-action="save"]').addEventListener('click', function () {
            if (editor.readOnly() || busy) { return; }
            request('save', {scope: scope.value, name: name.value, palette: JSON.stringify(editor.snapshot())}, 'POST',
                function (data) {
                    savePanel.hidden = true;
                    savePanel.style.display = 'none';
                    reloadAfterMutation(data.id, $t('Preset saved. Current colors still require Save.'));
                });
        });
        root.querySelector('[data-preset-action="delete"]').addEventListener('click', function () {
            var item = selected();

            if (!item || !item.can_delete || busy) { return; }
            confirm({title: $t('Delete Preset'), content: $t('Delete the selected preset? Current colors will be kept.'),
                actions: {confirm: function () {
                    request('delete', {preset_id: item.id, scope: item.scope}, 'POST', function () {
                        reloadAfterMutation(null, $t('Preset deleted. Current colors were kept.'));
                    });
                }}});
        });
        select.addEventListener('change', sync);
        editor.form.addEventListener('change', sync);
        editor.form.addEventListener('input', sync);
        request('index', {}, 'GET', function (data) { render(data); });
    };
});
