define([], function () {
    'use strict';

    var presets = ['#EB5202', '#1979C3', '#2E7D32', '#6F42C1', '#00796B',
        '#C62828', '#303030', '#FFFFFF', '#000000'];

    function normalize(value) {
        var match = String(value || '').trim().match(/^#?([0-9a-f]{6})$/i), channels;

        if (match) {
            return '#' + match[1].toUpperCase();
        }
        match = String(value || '').trim().match(/^rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)$/i);
        if (!match) {
            return null;
        }
        channels = match.slice(1).map(Number);
        if (channels.some(function (channel) { return channel > 255; })) {
            return null;
        }
        return '#' + channels.map(function (channel) {
            return ('0' + channel.toString(16)).slice(-2);
        }).join('').toUpperCase();
    }

    function luminance(color) {
        var channels = [1, 3, 5].map(function (offset) {
            var value = parseInt(color.substr(offset, 2), 16) / 255;

            return value <= 0.04045 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
        });

        return channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
    }

    function ratio(first, second) {
        var a = luminance(first), b = luminance(second);

        return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05);
    }

    function readableText(background) {
        return ratio('#FFFFFF', background) >= 4.5 ? '#FFFFFF' : '#000000';
    }

    function darken(background) {
        return '#' + [1, 3, 5].map(function (offset) {
            return ('0' + Math.round(parseInt(background.substr(offset, 2), 16) * 0.88).toString(16)).slice(-2);
        }).join('').toUpperCase();
    }

    return {presets: presets, normalize: normalize, ratio: ratio, readableText: readableText, darken: darken};
});
