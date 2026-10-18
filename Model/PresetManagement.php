<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

/** Bounded, local-only preset persistence with session-derived ownership on every operation. */
class PresetManagement
{
    private const TABLE = 'stoneholl_admin_accent_preset';
    private const LIMIT = 50;

    /** @var ResourceConnection */
    private $resource;
    /** @var PresetAccess */
    private $access;
    /** @var PaletteSnapshot */
    private $snapshot;
    /** @var Json */
    private $json;

    /**
     * @param ResourceConnection $resource
     * @param PresetAccess $access
     * @param PaletteSnapshot $snapshot
     * @param Json $json
     */
    public function __construct(
        ResourceConnection $resource,
        PresetAccess $access,
        PaletteSnapshot $snapshot,
        Json $json
    ) {
        $this->resource = $resource;
        $this->access = $access;
        $this->snapshot = $snapshot;
        $this->json = $json;
    }

    /**
     * Expose shared and current-user presets only, without disclosing other owners' IDs.
     *
     * @return array
     * @throws AuthorizationException
     */
    public function available(): array
    {
        $actor = $this->access->actorId();
        if (!$this->access->canPersonal() && !$this->access->canShared()) {
            throw new AuthorizationException(__('Preset access is not allowed.'));
        }
        $keys = ['shared'];
        if ($this->access->canPersonal()) {
            $keys[] = 'user-' . $actor;
        }
        $db = $this->resource->getConnection();
        $rows = $db->fetchAll($db->select()->from($this->resource->getTableName(self::TABLE))
            ->where('scope_key IN (?)', $keys)->order(['scope_key', 'name', 'preset_id'])->limit(100));
        $presets = [];
        foreach ($rows as $row) {
            if (strlen($row['palette_json']) > 8192) {
                throw new LocalizedException(__('A stored preset is invalid.'));
            }
            $shared = $row['scope_key'] === 'shared';
            $presets[] = [
                'id' => (int)$row['preset_id'], 'name' => $row['name'],
                'scope' => $shared ? 'shared' : 'personal',
                'can_delete' => $shared ? $this->access->canShared() : $this->access->canPersonal(),
                'palette' => $this->snapshot->normalize($this->json->unserialize($row['palette_json']))
            ];
        }
        return ['presets' => $presets, 'can_shared' => $this->access->canShared(),
            'can_personal' => $this->access->canPersonal()];
    }

    /**
     * Create an immutable named snapshot; duplicates never overwrite an existing preset.
     *
     * @param mixed $scope
     * @param mixed $name
     * @param mixed $paletteJson
     * @return int
     * @throws LocalizedException
     */
    public function save($scope, $name, $paletteJson): int
    {
        $key = $this->access->scopeKey($scope);
        $name = $this->snapshot->normalizeName($name);
        if (!is_string($paletteJson) || strlen($paletteJson) > 8192) {
            throw new LocalizedException(__('The preset palette must be valid JSON of at most 8 KB.'));
        }
        try {
            $palette = $this->snapshot->normalize($this->json->unserialize($paletteJson));
        } catch (\InvalidArgumentException $error) {
            throw new LocalizedException(__('The preset palette must be valid JSON.'));
        }
        $table = $this->resource->getTableName(self::TABLE);
        $db = $this->resource->getConnection();
        $db->beginTransaction();
        try {
            // Lock the indexed scope range, including an empty range, before enforcing its capacity.
            $existing = $db->fetchCol($db->select()->from($table, ['preset_id'])
                ->where('scope_key = ?', $key)->limit(self::LIMIT + 1)->forUpdate(true));
            if (count($existing) >= self::LIMIT) {
                throw new LocalizedException(__('A maximum of 50 presets is allowed in each scope.'));
            }
            $db->insert($table, [
                'scope_key' => $key, 'owner_user_id' => $scope === 'personal' ? $this->access->actorId() : null,
                'name' => $name, 'palette_json' => $this->json->serialize($palette)
            ]);
            $id = (int)$db->lastInsertId($table);
            $db->commit();
            return $id;
        } catch (\Throwable $error) {
            $db->rollBack();
            if ($error instanceof DuplicateException) {
                throw new LocalizedException(__('A preset with this name already exists in this scope.'));
            }
            throw $error;
        }
    }

    /**
     * Delete only an exact ID in the caller's authorized namespace.
     *
     * @param mixed $id
     * @param mixed $scope
     * @return void
     * @throws AuthorizationException
     */
    public function delete($id, $scope): void
    {
        $key = $this->access->scopeKey($scope);
        if ((!is_string($id) && !is_int($id)) || preg_match('/^[1-9][0-9]{0,9}$/D', (string)$id) !== 1
            || (float)$id > 4294967295
        ) {
            throw new AuthorizationException(__('Preset access is not allowed.'));
        }
        $count = $this->resource->getConnection()->delete($this->resource->getTableName(self::TABLE), [
            'preset_id = ?' => (int)$id, 'scope_key = ?' => $key
        ]);
        if ($count !== 1) {
            throw new AuthorizationException(__('Preset access is not allowed.'));
        }
    }
}
