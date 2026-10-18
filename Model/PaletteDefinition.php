<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

/** Closed field list shared by personal storage, validation and configuration. */
class PaletteDefinition
{
    public const ELEMENTS = [
        'buttons' => ['label' => 'Primary Buttons', 'area' => 'buttons',
            'background' => 'buttons_color', 'text' => 'buttons_text_color'],
        'menu' => ['label' => 'Active Menu Item', 'area' => 'menu',
            'background' => 'menu_color', 'text' => 'menu_text_color'],
        'sidebar' => ['label' => 'Sidebar', 'area' => 'sidebar',
            'background' => 'sidebar_color', 'text' => 'sidebar_text_color',
            'hover_background' => 'sidebar_hover_color', 'hover_text' => 'sidebar_hover_text_color'],
        'submenu' => ['label' => 'Submenu', 'area' => 'sidebar',
            'background' => 'sidebar_submenu_color', 'text' => 'sidebar_submenu_text_color'],
        'tabs' => ['label' => 'Active Tabs', 'area' => 'tabs',
            'background' => 'tabs_background', 'text' => 'tabs_text_color', 'border' => 'tabs_color'],
        'inactive_tabs' => ['label' => 'Inactive Tabs', 'area' => 'tabs', 'text' => 'tabs_inactive_color'],
        'headings' => ['label' => 'Section Headings', 'area' => 'tabs',
            'background' => 'tabs_heading_color', 'text' => 'tabs_heading_text_color'],
        'links' => ['label' => 'Content Links', 'area' => 'links',
            'text' => 'links_color', 'hover_text' => 'links_hover_color']
    ];

    public const AREAS = [
        'tabs' => ['label' => 'Tabs and Sections', 'fields' => [
            'tabs_color' => 'Active Border',
            'tabs_background' => 'Active Background',
            'tabs_text_color' => 'Active Text',
            'tabs_inactive_color' => 'Inactive Text',
            'tabs_heading_color' => 'Section Heading Background',
            'tabs_heading_text_color' => 'Section Heading Text'
        ]],
        'buttons' => ['label' => 'Primary Buttons', 'fields' => [
            'buttons_color' => 'Background',
            'buttons_text_color' => 'Text'
        ]],
        'menu' => ['label' => 'Active Menu Item', 'fields' => [
            'menu_color' => 'Background',
            'menu_text_color' => 'Text and Icons'
        ]],
        'sidebar' => ['label' => 'Sidebar and Submenu', 'fields' => [
            'sidebar_color' => 'Background',
            'sidebar_text_color' => 'Text and Icons',
            'sidebar_hover_color' => 'Hover Background',
            'sidebar_hover_text_color' => 'Hover Text and Icons',
            'sidebar_submenu_color' => 'Submenu Background',
            'sidebar_submenu_text_color' => 'Submenu Text'
        ]],
        'links' => ['label' => 'Content Links', 'fields' => [
            'links_color' => 'Text',
            'links_hover_color' => 'Hover and Focus Text'
        ]]
    ];

    public const COLOR_FIELDS = [
        'tabs_color',
        'tabs_background',
        'tabs_text_color',
        'tabs_inactive_color',
        'tabs_heading_color',
        'tabs_heading_text_color',
        'buttons_color',
        'buttons_text_color',
        'menu_color',
        'menu_text_color',
        'sidebar_color',
        'sidebar_text_color',
        'sidebar_hover_color',
        'sidebar_hover_text_color',
        'sidebar_submenu_color',
        'sidebar_submenu_text_color',
        'links_color',
        'links_hover_color'
    ];
}
