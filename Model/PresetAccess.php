<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;

/** Operation-boundary permission checks; a configuration administrator does not own others' personal presets. */
class PresetAccess
{
    /** @var Session */
    private $session;
    /** @var AuthorizationInterface */
    private $authorization;

    /**
     * @param Session $session
     * @param AuthorizationInterface $authorization
     */
    public function __construct(Session $session, AuthorizationInterface $authorization)
    {
        $this->session = $session;
        $this->authorization = $authorization;
    }

    /**
     * Resolve identity only from Magento's authenticated Admin session.
     *
     * @return int
     * @throws AuthorizationException
     */
    public function actorId(): int
    {
        $user = $this->session->getUser();
        if (!$this->session->isLoggedIn() || !$user || !(int)$user->getId()) {
            throw new AuthorizationException(__('Preset access is not allowed.'));
        }
        return (int)$user->getId();
    }

    /**
     * Read the caller's personal-preset permission.
     *
     * @return bool
     */
    public function canPersonal(): bool
    {
        return $this->authorization->isAllowed('Magento_Backend::myaccount');
    }

    /**
     * Require explicit module configuration permission for shared-preset mutations.
     *
     * @return bool
     */
    public function canShared(): bool
    {
        return $this->authorization->isAllowed('Stoneholl_AdminAccentColor::config');
    }

    /**
     * Derive the unique storage namespace from current identity and the requested closed scope.
     *
     * @param mixed $scope
     * @return string
     * @throws AuthorizationException
     */
    public function scopeKey($scope): string
    {
        $actor = $this->actorId();
        if ($scope === 'shared' && $this->canShared()) {
            return 'shared';
        }
        if ($scope === 'personal' && $this->canPersonal()) {
            return 'user-' . $actor;
        }
        throw new AuthorizationException(__('Preset access is not allowed.'));
    }
}
