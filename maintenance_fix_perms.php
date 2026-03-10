<?php
require_once "constants/dbconnect.php";

// 1. Standardize 'send email' to 'send_email' in role_type
echo "Standardizing role_type actions...\n";
$conn->query("UPDATE role_type SET action = 'send_email' WHERE action = 'send email'");
$conn->query("UPDATE role_type SET action = 'export_excel' WHERE action = 'export excel sheet' OR action = 'export'");

// 2. Fix duplicates in role_type (Keep original IDs, but unify strings)
// Actually, it's safer to just let them have same action/page and rely on strings.

// 3. Standardize role_type_permissions
echo "Standardizing role_type_permissions...\n";
$conn->query("UPDATE role_type_permissions SET action = 'send_email' WHERE action = 'send email'");
$conn->query("UPDATE role_type_permissions SET action = 'export_excel' WHERE action = 'export excel sheet' OR action = 'export'");

// 4. Standardize role_permissions
echo "Standardizing role_permissions...\n";
$conn->query("UPDATE role_permissions SET action = 'send_email' WHERE action = 'send email'");
$conn->query("UPDATE role_permissions SET action = 'export_excel' WHERE action = 'export excel sheet' OR action = 'export'");

// 5. Remove exact duplicates in role_type_permissions (same role, same page:action)
$conn->query("DELETE t1 FROM role_type_permissions t1 INNER JOIN role_type_permissions t2 WHERE t1.id > t2.id AND t1.role_type = t2.role_type AND t1.page = t2.page AND t1.action = t2.action");

// 6. Remove exact duplicates in role_permissions (same user, same page:action)
$conn->query("DELETE t1 FROM role_permissions t1 INNER JOIN role_permissions t2 WHERE t1.id > t2.id AND t1.user_id = t2.user_id AND t1.page = t2.page AND t1.action = t2.action");

echo "Maintenance complete.\n";
?>
