<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

class EffectiveSettingsResolver
{
    /** @var Config */
    private $config;
    /** @var UserPreferenceRepository */
    private $preferenceRepository;

    /**
     * Initialize explicit EffectiveSettingsResolver dependencies.
     *
     * @param Config $config
     * @param UserPreferenceRepository $preferenceRepository
     */
    public function __construct(Config $config, UserPreferenceRepository $preferenceRepository)
    {
        $this->config = $config;
        $this->preferenceRepository = $preferenceRepository;
    }

    /**
     * Use a complete personal palette when allowed, otherwise the global palette.
     *
     * @param int $userId
     * @return Settings
     */
    public function resolve(int $userId): Settings
    {
        if ($this->config->allowsPersonalOverrides()) {
            $preference = $this->preferenceRepository->getByUserId($userId);
            if ($preference !== null) {
                return $preference->toSettings();
            }
        }

        return $this->config->getGlobalSettings();
    }
}
