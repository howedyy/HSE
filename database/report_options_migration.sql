-- ============================================================
-- Migration: Report Observation Types & Work Types
-- Run this SQL against your `hse` database in phpMyAdmin
-- ============================================================

-- 1. Observation Types (e.g. "متابعه الاعمال", "فحص الموقع", "مخالفات السلوك")
CREATE TABLE IF NOT EXISTS `report_observation_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2. Work Types (sub-types linked to an observation type)
CREATE TABLE IF NOT EXISTS `report_work_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `observation_type_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_rwt_observation` (`observation_type_id`),
  CONSTRAINT `fk_rwt_observation` FOREIGN KEY (`observation_type_id`) REFERENCES `report_observation_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ─── Seed: Observation Types ────────────────────────────────
INSERT INTO `report_observation_types` (`id`, `name`, `status`) VALUES
(1, 'متابعه الاعمال', 1),
(2, 'فحص الموقع',     1),
(3, 'مخالفات السلوك', 1);

-- ─── Seed: Work Types (متابعه الاعمال → id=1) ───────────────
INSERT INTO `report_work_types` (`name`, `observation_type_id`, `status`) VALUES
('اعمال روتينية',                    1, 1),
('تصريح عمل في اماكن محصورة',        1, 1),
('تصريح عمل على ارتفاع',             1, 1),
('اعمال خطرة بدون تصريح',             1, 1),
('تصريح عمل بمواد كميائية خطرة',      1, 1),
('تصريح حفر',                        1, 1),
('تصريح عزل طاقه',                   1, 1),
('تصريح اعمال ساخنه',                1, 1),
('تصريح رفع',                        1, 1);

-- ─── Seed: Work Types (فحص الموقع → id=2) ───────────────────
INSERT INTO `report_work_types` (`name`, `observation_type_id`, `status`) VALUES
('فحص انظمة واجهزة الاطفاء',                    2, 1),
('فحص التوصيلات الكهربائية',                     2, 1),
('فحص انظمة السباكة',                            2, 1),
('فحص الحجر الهاشمي والرخام والجبسم بورد',       2, 1),
('فحص الزجاج السيكوريت',                         2, 1),
('فحص الديكوريشن الخشب واللوفارات الالومنيوم',   2, 1),
('فحص حالة التخزين',                             2, 1),
('فحص النظافة العامة للمكان',                     2, 1),
('فحص حالة الطريق',                              2, 1),
('فحص حالة اللاند اسكيب',                         2, 1),
('فحص البنية التحتية',                            2, 1),
('فحص وجود حشارات او حيوانات ضارة',               2, 1);

-- ─── Seed: Work Types (مخالفات السلوك → id=3) ───────────────
INSERT INTO `report_work_types` (`name`, `observation_type_id`, `status`) VALUES
('مخالفة قيادة مركبة',                  3, 1),
('عدم ارتداء مهمات الوقاية الشخصية',    3, 1),
('التصرف بشكل غير امن',                 3, 1);
