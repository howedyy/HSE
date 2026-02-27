<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle Language Switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    $_SESSION['curr_lang'] = $_GET['lang'];
}

// Default Language
$lang_code = $_SESSION['curr_lang'] ?? 'en'; 
$dir = ($lang_code === 'ar') ? 'rtl' : 'ltr';

// Translations
$translations = [
    'en' => [
        'app_name' => 'Edara-HSE111',
        'subtitle' => 'Safety Management System • Dashboard',
        'completed_permits' => 'Completed Permits',
        'active_permits' => 'Active Permits',
        'pending_approval' => 'Pending Approval',
        'todays_incidents' => "Today's Incidents",
        'todays_reports' => "Today's Daily Reports",
        'safety_compliance' => 'Safety Compliance',
        'compliance_rate' => 'Compliance Rate',
        'compliance_desc' => 'Overall safety adherence based on permit completion and incident resolution rates.',
        'activity_monitoring' => 'Workforce Activity (Monthly)',
        'recent_reports' => 'Recent Reports',
        'no_incidents' => 'No recent incidents found.',
        'new_permit' => 'New Permit',
        'new_report' => 'New Report',
        'manage_users' => 'Manage Users',
        'compliant' => 'Compliant',
        'non_compliant' => 'Non-Compliant',
        'permits_trend' => 'Permits & Reports Trend',
        'status_resolved' => 'Resolved',
        'status_open' => 'Open',
        'risk_level' => 'Risk Level',
        'months' => [
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'May' => 'May', 'Jun' => 'Jun',
            'Jul' => 'Jul', 'Aug' => 'Aug', 'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dec'
        ],
        'incident_resolution' => 'Incident Resolution',
        'task_completion' => 'Task Completion',
        'resolution_rate' => 'Resolution Rate',
        'completion_rate' => 'Completion Rate',
        'incidents_resolved_desc' => 'Percentage of reported incidents and observations that have been successfully closed.',
        'high_risk_monitoring' => 'High Risk Monitoring',
        'high_risk_performance' => 'Risk Performance',
        'top_risk_project' => 'Top Risk Project',
        'high_risk_desc' => 'Monitoring projects with high-risk overdue reports (over 8 hours).',
        'top_risk_dept' => 'Top Risk Dept',
        'dept_risk_desc' => 'Monitoring departments with highest overdue high-risk reports.',
        'permits_completed_desc' => 'Ratio of permits successfully completed compared to the total permits issued.',
        'resolved' => 'Resolved',
        'pending' => 'Pending',
        'completed' => 'Completed',
        'active' => 'Active',
        'hse_user_activity' => 'HSE User Activity',
        'activity_rate' => 'Activity Rate',
        'most_active_user' => 'Most Active User',
        'hse_activity_desc' => 'Percentage of HSE users who created permits or reports today.',
        'active_users' => 'Active Users',
        'inactive_users' => 'Inactive Users',
        'recent_incidents' => 'Recent Incidents',
        'best_safety_practices' => 'Best Safety Practices',
        'good_practices_rate' => 'Good Practices Rate',
        'best_project' => 'Best Project',
        'best_practices_desc' => 'Project with the highest percentage of good safety practices.',
        'good_practices' => 'Good Practices',
        'other_reports' => 'Other Reports'
    ],
    'ar' => [
        'app_name' => 'Edara-HSE111',
        'subtitle' => 'نظام إدارة السلامة • لوحة التحكم',
        'completed_permits' => 'تصاريح مكتملة',
        'active_permits' => 'تصاريح نشطة (قيد التنفيذ)',
        'pending_approval' => 'بانتظار الموافقة',
        'todays_incidents' => 'تقارير اليوم',
        'todays_reports' => 'تقارير اليوم اليومية',
        'safety_compliance' => 'الامتثال للسلامة',
        'compliance_rate' => 'نسبة الامتثال',
        'compliance_desc' => 'مؤشر الالتزام العام بمعايير السلامة بناءً على التصاريح المكتملة والحوادث.',
        'activity_monitoring' => 'مراقبة النشاط (شهري)',
        'recent_reports' => 'أحدث تقارير والملاحظات',
        'no_incidents' => 'لا توجد حوادث حديثة لعرضها.',
        'new_permit' => 'تصريح جديد',
        'new_report' => 'تقرير جديد',
        'manage_users' => 'المستخدمين',
        'compliant' => 'ممتثل',
        'non_compliant' => 'غير ممتثل',
        'permits_trend' => 'المؤشر العام للتصاريح والتقارير',
        'status_resolved' => 'مغلق',
        'status_open' => 'مفتوح',
        'risk_level' => 'مستوى الخطر',
        'months' => [
            'Jan' => 'يناير', 'Feb' => 'فبراير', 'Mar' => 'مارس', 'Apr' => 'أبريل', 'May' => 'مايو', 'Jun' => 'يونيو',
            'Jul' => 'يوليو', 'Aug' => 'أغسطس', 'Sep' => 'سبتمبر', 'Oct' => 'أكتوبر', 'Nov' => 'نوفمبر', 'Dec' => 'ديسمبر'
        ],
        'incident_resolution' => 'حل الحوادث والملاحظات',
        'task_completion' => 'إكمال المهام',
        'resolution_rate' => 'نسبة الحل',
        'completion_rate' => 'نسبة الإكمال',
        'incidents_resolved_desc' => 'نسبة الحوادث والملاحظات المبلغ عنها التي تم إغلاقها بنجاح.',
        'high_risk_monitoring' => 'مراقبة المخاطر العالية',
        'high_risk_performance' => 'أداء المخاطر',
        'top_risk_project' => 'أعلى مشروع خطورة',
        'high_risk_desc' => 'مراقبة المشاريع التي لديها تقارير عالية الخطورة متأخرة (أكثر من 8 ساعات).',
        'top_risk_dept' => 'أعلى قسم خطورة',
        'dept_risk_desc' => 'مراقبة الأقسام التي لديها أعلى نسبة تأخير في الحوادث عالية الخطورة.',
        'permits_completed_desc' => 'نسبة التصاريح المكتملة بنجاح مقارنة بإجمالي التصاريح الصادرة.',
        'resolved' => 'محلولة',
        'pending' => 'قيد الانتظار',
        'completed' => 'مكتملة',
        'active' => 'نشطة',
        'hse_user_activity' => 'نشاط مستخدمي HSE',
        'activity_rate' => 'معدل النشاط',
        'most_active_user' => 'المستخدم الأكثر نشاطاً',
        'hse_activity_desc' => 'نسبة مستخدمي HSE الذين أنشأوا تصاريح أو تقارير اليوم.',
        'active_users' => 'مستخدمون نشطون',
        'inactive_users' => 'مستخدمون غير نشطين',
        'recent_incidents' => 'أحدث الحوادث',
        'best_safety_practices' => 'أفضل الممارسات الآمنة',
        'good_practices_rate' => 'معدل الممارسات الجيدة',
        'best_project' => 'أفضل مشروع',
        'best_practices_desc' => 'المشروع الذي يحتوي على أعلى نسبة من الممارسات الآمنة الجيدة.',
        'good_practices' => 'ممارسات جيدة',
        'other_reports' => 'تقارير أخرى'
    ]
];

// Helper function
function __($key) {
    global $translations, $lang_code;
    return $translations[$lang_code][$key] ?? $key;
}
?>
