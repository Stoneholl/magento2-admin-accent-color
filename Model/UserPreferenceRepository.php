<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

class UserPreferenceRepository
{
    /** @var UserPreferenceFactory */
    private $preferenceFactory;
    /** @var ResourceModel\UserPreference */
    private $resource;

    /**
     * Initialize explicit UserPreferenceRepository dependencies.
     *
     * @param UserPreferenceFactory $preferenceFactory
     * @param ResourceModel\UserPreference $resource
     */
    public function __construct(
        UserPreferenceFactory $preferenceFactory,
        ResourceModel\UserPreference $resource
    ) {
        $this->preferenceFactory = $preferenceFactory;
        $this->resource = $resource;
    }

    /**
     * Load a preference by its unique administrator reference.
     *
     * @param int $userId
     * @return UserPreference|null
     */
    public function getByUserId(int $userId): ?UserPreference
    {
        $preference = $this->preferenceFactory->create();
        $this->resource->load($preference, $userId, 'user_id');
        return $preference->getId() ? $preference : null;
    }

    /**
     * Save the complete personal palette for an administrator.
     *
     * @param int $userId
     * @param array $data
     * @return UserPreference
     */
    public function saveForUser(int $userId, array $data): UserPreference
    {
        $preference = $this->getByUserId($userId) ?: $this->preferenceFactory->create();
        $preference->addData($data);
        $preference->setData('user_id', $userId);
        $this->resource->save($preference);
        return $preference;
    }

    /**
     * Remove only the specified administrator's palette.
     *
     * @param int $userId
     * @return void
     */
    public function deleteForUser(int $userId): void
    {
        $preference = $this->getByUserId($userId);
        if ($preference !== null) {
            $this->resource->delete($preference);
        }
    }
}
