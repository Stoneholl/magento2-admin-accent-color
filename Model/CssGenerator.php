<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

/** Generate allowlisted standard-Admin selectors from normalized settings. */
class CssGenerator
{
    /** @var ColorContrast */
    private $contrast;

    /**
     * @param ColorContrast $contrast
     */
    public function __construct(ColorContrast $contrast)
    {
        $this->contrast = $contrast;
    }

    /**
     * Keep unconfigured areas and semantic/destructive states native.
     *
     * @param Settings $settings
     * @return string
     */
    public function generate(Settings $settings): string
    {
        $rules = [];
        $this->tabs($rules, $settings);
        $this->buttons($rules, $settings);
        $this->sidebar($rules, $settings);
        if ($settings->isMenuEnabled()) {
            $this->pair(
                $rules,
                $this->interactiveSelectors('.admin__menu .level-0._active>a'),
                $settings->getMenuColor(),
                $settings->getMenuTextColor()
            );
        }
        if ($settings->isAreaEnabled('links')) {
            $filter = ':not([class]):not([role]):not([data-role])';
            $this->rule($rules, '.page-content a' . $filter, 'color', $settings->getColor('links_color'));
            $this->rule(
                $rules,
                '.page-content a' . $filter . ':hover,.page-content a' . $filter . ':focus',
                'color',
                $settings->getColor('links_hover_color')
            );
        }
        return implode("\n", $rules) . ($rules === [] ? '' : "\n");
    }

    /**
     * Color active navigation, inactive tab text and configuration section headings.
     *
     * @param array $rules
     * @param Settings $settings
     * @return void
     */
    private function tabs(array &$rules, Settings $settings): void
    {
        if (!$settings->areTabsEnabled()) {
            return;
        }
        $activeTab = '.tabs-horiz .ui-state-active .ui-tabs-anchor';
        $activeNav = '.admin__page-nav-item._active .admin__page-nav-link,'
            . '.admin__page-nav-item.ui-state-active .admin__page-nav-link';
        $this->rule($rules, $activeTab, 'border-top-color', $settings->getTabsColor());
        $this->rule(
            $rules,
            '.admin__page-nav-item._active,.admin__page-nav-item.ui-state-active',
            'border-left-color',
            $settings->getTabsColor()
        );
        $background = $settings->getColor('tabs_background');
        $this->pair(
            $rules,
            $this->interactiveSelectors($activeTab . ',' . $activeNav),
            $background,
            $settings->getTabsTextColor()
        );
        $this->rule($rules, '.tabs-horiz .ui-state-active', 'background-color', $background);
        $this->rule($rules, $activeTab, 'border-bottom-color', $background);
        $this->rule(
            $rules,
            '.tabs-horiz .ui-state-default:not(.ui-state-active) .ui-tabs-anchor,'
            . '.admin__page-nav-item:not(._active):not(.ui-state-active) .admin__page-nav-link',
            'color',
            $settings->getColor('tabs_inactive_color')
        );
        $this->pair(
            $rules,
            $this->interactiveSelectors('.section-config>.entry-edit-head>a'),
            $settings->getColor('tabs_heading_color'),
            $settings->getColor('tabs_heading_text_color')
        );
    }

    /**
     * Use the same exclusion filter on buttons and their split separators.
     *
     * @param array $rules
     * @param Settings $settings
     * @return void
     */
    private function buttons(array &$rules, Settings $settings): void
    {
        if (!$settings->areButtonsEnabled()) {
            return;
        }
        $filter = ':not(.delete):not(.action-delete):not(.action-remove):not(.remove)'
            . ':not([disabled]):not(.disabled):not(._disabled):not([aria-disabled="true"])';
        $selectors = ['.action-primary' . $filter, '.page-actions .primary' . $filter];
        $states = [];
        foreach ($selectors as $selector) {
            foreach ([':hover', ':focus', ':active'] as $state) {
                $states[] = $selector . $state;
            }
        }
        $background = $settings->getButtonsColor();
        $text = $settings->getButtonsTextColor();
        $base = implode(',', $selectors);
        $this->pair($rules, $base, $background, $text);
        $this->rule($rules, $base, 'border-color', $background);
        $hover = $background === null ? null : $this->darken($background, 12);
        $this->pair($rules, implode(',', $states), $hover, $text);
        $this->rule($rules, implode(',', $states), 'border-color', $hover);
        $split = '.actions-split .action-primary' . $filter;
        $this->rule($rules, $split . ':after', 'border-left-color', $background);
        $this->rule(
            $rules,
            $split . ':hover:after,' . $split . ':focus:after,' . $split . ':active:after',
            'border-left-color',
            $hover
        );
    }

    /**
     * Preserve active-menu precedence and derive readable submenu hover colors.
     *
     * @param array $rules
     * @param Settings $settings
     * @return void
     */
    private function sidebar(array &$rules, Settings $settings): void
    {
        if (!$settings->isAreaEnabled('sidebar')) {
            return;
        }
        $background = $settings->getColor('sidebar_color');
        $text = $settings->getColor('sidebar_text_color');
        $this->rule($rules, '.menu-wrapper:before', 'background-color', $background);
        $normal = '.admin__menu .level-0:not(._active):not(._show)>a';
        $this->pair($rules, $normal, $background, $text);
        $hover = $settings->getColor('sidebar_hover_color');
        if ($hover === null && $background !== null) {
            $hover = $this->darken($background, 12);
        }
        $this->pair(
            $rules,
            '.admin__menu .level-0:not(._active):hover>a,'
            . '.admin__menu .level-0:not(._active)>a:focus',
            $hover,
            $settings->getColor('sidebar_hover_text_color') ?? $text
        );
        $submenu = $settings->getColor('sidebar_submenu_color');
        $submenuText = $settings->getColor('sidebar_submenu_text_color');
        $this->rule($rules, '.admin__menu .level-0>.submenu', 'background-color', $submenu);
        $this->pair($rules, '.admin__menu .submenu a,.admin__menu .submenu-title,'
            . '.admin__menu .submenu .submenu-group-title,.admin__menu .action-close', $submenu, $submenuText);
        if ($submenu !== null) {
            $submenuHover = $this->darken($submenu, 12);
            $this->pair($rules, '.admin__menu .submenu li a:hover,.admin__menu .submenu li a:focus,'
                . '.admin__menu .submenu li a:active', $submenuHover, $submenuText);
        }
    }

    /**
     * Use automatic readable text only when a background is explicitly configured.
     *
     * @param array $rules
     * @param string $selector
     * @param string|null $background
     * @param string|null $text
     * @return void
     */
    private function pair(array &$rules, string $selector, ?string $background, ?string $text): void
    {
        $this->rule($rules, $selector, 'background-color', $background);
        if ($text === null && $background !== null) {
            $text = $this->contrast->readableText($background);
        }
        $this->rule($rules, $selector, 'color', $text);
    }

    /**
     * Append one declaration only when its value is configured.
     *
     * @param array $rules
     * @param string $selector
     * @param string $property
     * @param string|null $color
     * @return void
     */
    private function rule(array &$rules, string $selector, string $property, ?string $color): void
    {
        if ($color !== null) {
            $rules[] = $selector . '{' . $property . ':' . $color . '}';
        }
    }

    /**
     * Keep native hover/focus rules from restoring unreadable text on a colored surface.
     *
     * @param string $selectors
     * @return string
     */
    private function interactiveSelectors(string $selectors): string
    {
        $result = [];
        foreach (explode(',', $selectors) as $selector) {
            foreach (['', ':hover', ':focus', ':active'] as $state) {
                $result[] = $selector . $state;
            }
        }
        return implode(',', $result);
    }

    /**
     * Derive an opaque hover background from the configured base color.
     *
     * @param string $color
     * @param int $percent
     * @return string
     */
    private function darken(string $color, int $percent): string
    {
        $factor = (100 - $percent) / 100;
        return sprintf(
            '#%02X%02X%02X',
            (int)round(hexdec(substr($color, 1, 2)) * $factor),
            (int)round(hexdec(substr($color, 3, 2)) * $factor),
            (int)round(hexdec(substr($color, 5, 2)) * $factor)
        );
    }
}
