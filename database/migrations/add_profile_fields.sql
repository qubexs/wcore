-- Run this in phpMyAdmin - adds only missing columns
-- Use separate queries or run as a block - MySQL will skip existing columns error

-- Step 1: Add Personal Information columns (run each separately)
-- ALTER TABLE `users` ADD COLUMN `salutation` VARCHAR(20) NULL AFTER `name`;
-- ALTER TABLE `users` ADD COLUMN `professional_title` VARCHAR(100) NULL AFTER `salutation`;
-- ALTER TABLE `users` ADD COLUMN `job_title` VARCHAR(100) NULL AFTER `professional_title`;
-- ALTER TABLE `users` ADD COLUMN `bio` TEXT NULL AFTER `job_title`;

-- Step 2: Add Contact Information columns
-- ALTER TABLE `users` ADD COLUMN `secondary_email` VARCHAR(255) NULL AFTER `email`;
-- ALTER TABLE `users` ADD COLUMN `mobile_phone` VARCHAR(30) NULL AFTER `phone`;
-- ALTER TABLE `users` ADD COLUMN `fax` VARCHAR(30) NULL AFTER `mobile_phone`;

-- Step 3: Add Professional Information columns
-- ALTER TABLE `users` ADD COLUMN `specialization` VARCHAR(100) NULL AFTER `job_title`;
-- ALTER TABLE `users` ADD COLUMN `mmc_reg_no` VARCHAR(50) NULL AFTER `specialization`;
-- ALTER TABLE `users` ADD COLUMN `mmc_reg_expiry` DATE NULL AFTER `mmc_reg_no`;
-- ALTER TABLE `users` ADD COLUMN `other_reg_no` VARCHAR(50) NULL AFTER `mmc_reg_expiry`;
-- ALTER TABLE `users` ADD COLUMN `other_reg_expiry` DATE NULL AFTER `other_reg_no`;
-- ALTER TABLE `users` ADD COLUMN `key_credentials` TEXT NULL AFTER `other_reg_expiry`;

-- Step 4: Add Preferences columns
-- ALTER TABLE `users` ADD COLUMN `preferred_language` VARCHAR(10) DEFAULT 'en' AFTER `avatar`;
-- ALTER TABLE `users` ADD COLUMN `timezone` VARCHAR(50) DEFAULT 'UTC' AFTER `preferred_language`;
-- ALTER TABLE `users` ADD COLUMN `two_factor_enabled` TINYINT(1) DEFAULT 0 AFTER `timezone`;

-- Alternative: Run all at once (will show errors for existing columns but will add missing ones)
-- Uncomment below to run:
-- ALTER TABLE `users` ADD COLUMN `salutation` VARCHAR(20) NULL AFTER `name`;
-- ALTER TABLE `users` ADD COLUMN `professional_title` VARCHAR(100) NULL AFTER `salutation`;
-- ALTER TABLE `users` ADD COLUMN `job_title` VARCHAR(100) NULL AFTER `professional_title`;
-- ALTER TABLE `users` ADD COLUMN `bio` TEXT NULL AFTER `job_title`;
-- ALTER TABLE `users` ADD COLUMN `secondary_email` VARCHAR(255) NULL AFTER `email`;
-- ALTER TABLE `users` ADD COLUMN `mobile_phone` VARCHAR(30) NULL AFTER `phone`;
-- ALTER TABLE `users` ADD COLUMN `fax` VARCHAR(30) NULL AFTER `mobile_phone`;
-- ALTER TABLE `users` ADD COLUMN `specialization` VARCHAR(100) NULL AFTER `job_title`;
-- ALTER TABLE `users` ADD COLUMN `mmc_reg_no` VARCHAR(50) NULL AFTER `specialization`;
-- ALTER TABLE `users` ADD COLUMN `mmc_reg_expiry` DATE NULL AFTER `mmc_reg_no`;
-- ALTER TABLE `users` ADD COLUMN `other_reg_no` VARCHAR(50) NULL AFTER `mmc_reg_expiry`;
-- ALTER TABLE `users` ADD COLUMN `other_reg_expiry` DATE NULL AFTER `other_reg_no`;
-- ALTER TABLE `users` ADD COLUMN `key_credentials` TEXT NULL AFTER `other_reg_expiry`;
-- ALTER TABLE `users` ADD COLUMN `preferred_language` VARCHAR(10) DEFAULT 'en' AFTER `avatar`;
-- ALTER TABLE `users` ADD COLUMN `timezone` VARCHAR(50) DEFAULT 'UTC' AFTER `preferred_language`;
-- ALTER TABLE `users` ADD COLUMN `two_factor_enabled` TINYINT(1) DEFAULT 0 AFTER `timezone`;

-- Quick check: Which columns are missing?
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' 
AND COLUMN_NAME IN ('salutation', 'professional_title', 'job_title', 'bio', 
'secondary_email', 'mobile_phone', 'fax', 'specialization', 'mmc_reg_no', 
'mmc_reg_expiry', 'other_reg_no', 'other_reg_expiry', 'key_credentials', 
'preferred_language', 'timezone', 'two_factor_enabled');