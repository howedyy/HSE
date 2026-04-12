-- Table for PTW Operation Types and their associated Risk Assessment
CREATE TABLE IF NOT EXISTS `ptw_operation_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `operation_name` VARCHAR(255) NOT NULL,
    `risk_assessment` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for PTW Safety Measures (Section 4 checkboxes)
CREATE TABLE IF NOT EXISTS `ptw_safety_measures` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `measure_name` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial values for Operation Types
INSERT INTO `ptw_operation_types` (`operation_name`, `risk_assessment`) VALUES
('تقليم الجذور', 'سقوط الاشجار  علي  الافراد والممتلكات'),
('السباكة', 'انسكاب وتسريب  وغمر'),
('أعمال حفر', 'عمل في حفر'),
('أعمال لحام كهربي', 'مخاطر حريق'),
('العمل على ارتفاع سبايدر', 'سقوط من على ارتفاع'),
('أعمال رفع أحمال بمعدات ثقيلة', 'ضوضاء'),
('العمل علي سقالة', 'سقوط من على ارتفاع'),
('العمل على السلم المفصلى', 'سقوط من على ارتفاع'),
('أعمال نقل بمعدات ثقيلة', 'ضوضاء'),
('العمل بداخل الغرف المغلقة', 'عمل في مكان مغلق'),
('العمل على السلم الهيدروليكي', 'سقوط من على ارتفاع'),
('إعمال كهرباء الجهد المتوسط', 'مخاطر كهربائية'),
('العمل بالمواد الخطرة', 'مخاطر كيميائية');

-- Insert initial values for Safety Measures
INSERT INTO `ptw_safety_measures` (`measure_name`) VALUES
('الاشراف الدائم'),
('تحليل مخاطر الوظيفية'),
('تقييم مخاطر'),
('عزل مصدر الطاقة'),
('اختبار غازات'),
('وسيلة اطفاء حريق مناسبة'),
('وضع شريط تحذير حول مكان العمل'),
('وضع اقماع فسفورية حول مكان العمل'),
('وضع علامة تحذيرية او علامات ارشادية'),
('محاضرة توعية بالمخاطر'),
('مهمات وقاية اضافية'),
('احتياطات سلامة أخرى');
