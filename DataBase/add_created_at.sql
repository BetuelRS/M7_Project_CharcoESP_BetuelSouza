ALTER TABLE utilizadores ADD COLUMN IF NOT EXISTS `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `preferencias`;
UPDATE utilizadores SET created_at = NOW() WHERE created_at IS NULL;
