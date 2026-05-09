-- Migration: Add profile_photo column to user table
-- Run this on existing databases to add profile photo support
-- Date: 2026-05-06

USE foodsave_db;

-- Check if column exists before adding (MySQL 8.0+)
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `profile_photo` VARCHAR(255) NULL AFTER `date_naissance`;

-- If using older MySQL versions and the above doesn't work, try:
-- ALTER TABLE `user` ADD COLUMN `profile_photo` VARCHAR(255) NULL AFTER `date_naissance`;
