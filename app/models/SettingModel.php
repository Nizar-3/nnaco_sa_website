<?php
/**
 * SettingModel - Data Access Object untuk tabel system_settings
 */
class SettingModel {
    private Database $db;
    private static array $cache = [];
    private static bool $loadedAll = false;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Ambil semua konfigurasi dari database
     */
    public function getAllSettings(): array {
        if (self::$loadedAll && !empty(self::$cache)) {
            return array_values(self::$cache);
        }

        try {
            $this->db->query("SELECT * FROM system_settings ORDER BY setting_group ASC, setting_key ASC");
            $rows = $this->db->resultSet();
            self::$cache = [];
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row;
            }
            self::$loadedAll = true;
            return $rows;
        } catch (Exception $e) {
            error_log('[SettingModel] Gagal membaca system_settings: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil konfigurasi berdasarkan group (ai, otp, security)
     */
    public function getByGroup(string $group): array {
        $all = $this->getAllSettings();
        $filtered = [];
        foreach ($all as $item) {
            if (($item['setting_group'] ?? '') === $group) {
                $filtered[] = $item;
            }
        }
        return $filtered;
    }

    /**
     * Ambil satu baris setting berdasarkan key
     */
    public function getRecord(string $key): ?array {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        try {
            $this->db->query("SELECT * FROM system_settings WHERE setting_key = :key LIMIT 1");
            $this->db->bind('key', $key);
            $record = $this->db->single();
            if ($record) {
                self::$cache[$key] = $record;
                return $record;
            }
        } catch (Exception $e) {
            error_log("[SettingModel] Gagal membaca key $key: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Ambil nilai mentah dari setting_key
     */
    public function getValue(string $key, ?string $default = null): ?string {
        $record = $this->getRecord($key);
        if ($record !== null && array_key_exists('setting_value', $record)) {
            return $record['setting_value'];
        }
        return $default;
    }

    /**
     * Simpan atau perbarui nilai konfigurasi
     */
    public function set(string $key, ?string $value, ?int $updatedBy = null): bool {
        try {
            $this->db->query("
                UPDATE system_settings
                SET setting_value = :val,
                    updated_by = :uid,
                    updated_at = NOW()
                WHERE setting_key = :key
            ");
            $this->db->bind('val', $value);
            $this->db->bind('uid', $updatedBy);
            $this->db->bind('key', $key);
            $this->db->execute();

            if (isset(self::$cache[$key])) {
                self::$cache[$key]['setting_value'] = $value;
                self::$cache[$key]['updated_by'] = $updatedBy;
            }
            return true;
        } catch (Exception $e) {
            error_log("[SettingModel] Gagal menyimpan key $key: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update beberapa setting dalam satu transaksi
     */
    public function updateBatch(array $keyValuePairs, ?int $updatedBy = null): bool {
        try {
            $this->db->beginTransaction();
            foreach ($keyValuePairs as $key => $value) {
                $this->db->query("
                    UPDATE system_settings
                    SET setting_value = :val,
                        updated_by = :uid,
                        updated_at = NOW()
                    WHERE setting_key = :key
                ");
                $this->db->bind('val', $value);
                $this->db->bind('uid', $updatedBy);
                $this->db->bind('key', $key);
                $this->db->execute();

                if (isset(self::$cache[$key])) {
                    self::$cache[$key]['setting_value'] = $value;
                    self::$cache[$key]['updated_by'] = $updatedBy;
                }
            }
            $this->db->commit();
            self::clearCache();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[SettingModel] Gagal updateBatch: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bersihkan cache in-memory
     */
    public static function clearCache(): void {
        self::$cache = [];
        self::$loadedAll = false;
    }
}
