import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

const resources = {
  en: {
    translation: {
      "app": {
        "title": "Edara HSE",
        "subtitle": "Safety Management"
      },
      "nav": {
        "dashboard": "Dashboard",
        "dailyReports": "Daily Reports",
        "analytics": "Analytics",
        "ptwPermits": "PTW Permits",
        "ptwAnalytics": "PTW Analytics",
        "userManagement": "User Management",
        "settings": "Settings",
        "logout": "Logout"
      },
      "common": {
        "loading": "Loading...",
        "error": "Error",
        "save": "Save",
        "cancel": "Cancel",
        "edit": "Edit",
        "delete": "Delete",
        "status": "Status",
        "actions": "Actions",
        "search": "Search",
        "filter": "Filters",
        "reset": "Reset",
        "startDate": "Start Date",
        "endDate": "End Date",
        "project": "Project",
        "department": "Department",
        "all": "All",
        "active": "Active",
        "inactive": "Inactive",
        "module": "Module",
        "command": "Command",
        "saving": "Saving...",
        "public": "Public/General",
        "success": "Success",
        "critical": "Critical",
        "unassigned": "Unassigned"
      },
      "dashboard": {
        "welcome": "Welcome back, {{name}}",
        "subtitle": "Here's your HSE overview for today.",
        "stats": {
          "completedPermits": "Completed Permits",
          "activePermits": "Active Permits",
          "pendingApproval": "Pending Approval",
          "todayReports": "Today's Reports"
        },
        "compliance": {
          "bestPractices": "Best Safety Practices",
          "highRisk": "High Risk Compliance",
          "permitRate": "Permit Completion Rate",
          "top": "Top: {{project}}",
          "hotspot": "Hotspot: {{project}}",
          "of": "{{completed}} of {{total}} permits"
        },
        "trend": {
          "title": "Permits Trend (6 Months)"
        },
        "incidents": {
          "title": "Recent Observations",
          "viewAll": "View All",
          "noRecords": "No recent observations found.",
          "resolved": "Resolved",
          "open": "Open"
        }
      },
      "ptw": {
        "title": "Permit to Work Overview",
        "subtitle": "{{count}} permits in system",
        "analyticsTitle": "PTW Permit Analytics",
        "analyticsSubtitle": "Permit-to-Work Oversight • Real-time Compliance",
        "overdue": "Overdue Permits",
        "total": "Total Permits",
        "highEnergy": "High Energy Ops",
        "completionRate": "Completion Rate",
        "distribution": "Permit Distribution by Unit",
        "distributionSub": "Departmental engagement in permit system",
        "profile": "Operation Profile",
        "profileSub": "Permit categorization by task type",
        "pulse": "Operational Pulse",
        "pulseSub": "Historical volume tracking",
        "syncError": "Unable to connect to PTW data engine.",
        "retry": "Retry",
        "operation": "Operation",
        "timeExceeded": "Time Exceeded",
        "issued": "Issued",
        "workUnits": "Work Units",
        "dangerous": "Risk Level",
        "operationType": "Operation Type",
        "closedCycles": "Closed Cycles",
        "trendAnalysis": "Trend Analysis",
        "markPriority": "Mark Priority",
        "criticalDelay": "Critical Delay",
        "noOverdue": "Permit Shield Active • 0 Overdue Items",
        "pending": "Pending",
        "newPermit": "New Permit",
        "exportExcel": "Export Excel",
        "details": "Permit Details",
        "history": "PTW History",
        "noHistory": "No history recorded yet.",
        "images": "Attached Images",
        "noImages": "No images attached",
        "approve": "Approve Permit",
        "finish": "Finish Permit",
        "markNotCompleted": "Mark Not Completed",
        "markFinished": "Mark as Finished",
        "nonCompliance": "Non-Compliance",
        "delete": "Delete",
        "status": {
          "pending": "Not Approved",
          "approved": "Approved",
          "finished": "Finished",
          "notCompleted": "Not Completed",
          "nonCompliance": "Non-Compliance"
        },
        "table": {
          "idDate": "Permit # / Date",
          "projectDept": "Project / Department",
          "type": "Operation",
          "compliance": "Compliance",
          "location": "Work Location",
          "actions": "Actions",
          "workLocation": "Work Location",
          "description": "Work Description",
          "tools": "Tools & Equipment",
          "risk": "Risk Assessment",
          "measures": "Safety Measures",
          "manager": "Execution Manager",
          "admin": "Admin Signature",
          "safety": "Safety Manager",
          "start": "Start Time",
          "end": "End Time",
          "company": "Company"
        },
        "messages": {
          "noPermits": "No permits match your filters"
        },
        "filters": {
          "permitNumber": "Permit Number",
          "searchPlaceholder": "Search PTW...",
          "allOperations": "All Operations",
          "reset": "Reset Filters"
        },
        "operations": {
          "cold": "Cold Work",
          "hot": "Hot Work",
          "radiography": "Radiography Work",
          "confined": "Confined Space",
          "excavation": "Excavation",
          "height": "Work at Height"
        }
      },
      "users": {
        "title": "Security & User Control",
        "subtitle": "Admin Oversight",
        "totalActive": "{{count}} Active Accounts",
        "noRecords": "No identity records found",
        "account": "Account",
        "securityLevel": "Security Level",
        "assignment": "Assignment",
        "systemUser": "System User",
        "unassignedTitle": "Unassigned Title",
        "profileEngine": "User Profile Engine",
        "accountId": "Account ID",
        "identityDetails": "Identity Details",
        "username": "Account Username",
        "password": "Password",
        "clearance": "Security Clearance",
        "unit": "Unit Assignment",
        "fullName": "Legal Full Name",
        "jobTitle": "Professional Job Title",
        "activeStatus": "Account Active Status",
        "activeStatusSub": "Grant or revoke system access immediately.",
        "permissions": "Firewall & Permissions",
        "activeRules": "{{count}} Active Rules",
        "loadDefaultsConfirm": "Do you want to load default permissions for this role?",
        "updateSuccess": "User updated successfully!",
        "updateError": "Failed to update user"
      },
      "login": {
        "title": "Edara HSE",
        "subtitle": "Enterprise Health, Safety & Environment Management",
        "username": "Username",
        "password": "Password",
        "forgotPassword": "Forgot Password?",
        "signIn": "Sign In to Dashboard",
        "footer1": "Secure login with end-to-end encryption.",
        "footer2": "© 2026 Edara Systems. All rights reserved.",
        "error": "Invalid username or password. Please try again."
      },
      "analytics": {
        "title": "Daily Report Analytics",
        "subtitle": "HSE Insight Dashboard • Live Statistics",
        "syncFailed": "Analytics Sync Failed",
        "syncError": "The data engine encountered a synchronization issue with the server.",
        "metrics": {
          "overdue": "Overdue Alerts",
          "total": "Total Reports",
          "focus": "Departments In-Focus",
          "coverage": "Monthly Coverage",
          "unresolved": "UNRESOLVED",
          "timeExceeded": "TIME EXCEEDED",
          "cumulative": "CUMULATIVE",
          "observations": "OBSERVATIONS",
          "active": "ACTIVE",
          "safetyUnits": "SAFETY UNITS",
          "historical": "HISTORICAL",
          "dayAnalysis": "DAY ANALYSIS"
        },
        "charts": {
          "distribution": "Department Safety Distribution",
          "distributionSub": "Reports logged per operational unit",
          "riskMatrix": "Observation Risk Matrix",
          "riskMatrixSub": "Criticality breakdown of reported hazards",
          "riskLevels": "Levels"
        },
        "overdue": {
          "title": "Critical Overdue Observations",
          "subtitle": "Action Required • {{count}} Items",
          "nonCompliance": "NON-COMPLIANCE",
          "delayHours": "Delay Hours",
          "late": "+{{hours}}h Late",
          "allClear": "All Clear! No Overdue Reports"
        },
        "filters": {
          "dateRange": "Date Range",
          "allProjects": "All Projects",
          "allDepartments": "All Departments",
          "allRisks": "All Risks",
          "allStatuses": "All Statuses",
          "allUsers": "All Users",
          "high": "High",
          "medium": "Medium",
          "low": "Low",
          "open": "Open",
          "resolved": "Resolved"
        }
      },
      "dailyReport": {
        "title": "Daily Reports",
        "subtitle": "{{count}} observations recorded",
        "newReport": "New Report",
        "filters": {
          "createdBy": "Created By",
          "risk": "Risk",
          "limit": "Limit",
          "entries": "{{count}} entries"
        },
        "table": {
          "id": "#",
          "date": "Date",
          "project": "Project",
          "department": "Department",
          "workType": "Work Type",
          "risk": "Risk",
          "status": "Status",
          "createdBy": "Created By",
          "actions": "Actions"
        },
        "status": {
          "resolved": "Resolved",
          "open": "Open"
        },
        "actions": {
          "viewDetails": "View Full Details",
          "editReport": "Edit Report",
          "mailSent": "Mail Sent",
          "sendEmail": "Send Email",
          "resolveHazard": "Resolve Hazard",
          "deleteRecord": "Delete Record"
        },
        "pagination": {
          "info": "Page {{page}} / {{totalPages}} · {{total}} entries"
        },
        "details": {
          "title": "Observation #{{id}}",
          "loggedBy": "Logged by {{name}} on {{date}}",
          "riskLevel": "Risk Level",
          "observationReport": "Observation Report",
          "type": "Type (طبيعه العمل)",
          "description": "Description (وصف العمل)",
          "detailedObservation": "Detailed Observation (ملاحظات)",
          "safetyCompliance": "Safety Compliance",
          "correctiveAction": "Corrective Action (الاجراء)",
          "initialEvidence": "Initial Evidence",
          "noImages": "No images uploaded.",
          "resolutionVerified": "Resolution Verified",
          "actionTaken": "Action Taken",
          "closedBy": "Closed By Portfolio",
          "closeDetails": "Close Details"
        },
        "resolution": {
          "title": "Resolve Hazard",
          "caseId": "Case ID #{{id}}",
          "notesLabel": "Closure Notes (Final Action)",
          "notesPlaceholder": "Briefly describe the corrective action implemented...",
          "photoLabel": "Verification Photo",
          "upload": "Upload",
          "proofTitle": "Proof of compliance",
          "proofSub": "Images are automatically optimized for audit logs.",
          "submit": "Resolve Observation"
        },
        "confirm": {
          "delete": "Are you sure you want to delete this report?",
          "email": "Are you sure you want to send the observation report via email?"
        },
        "messages": {
          "emailSuccess": "Success: Email has been sent successfully.",
          "emailFailed": "Dispatch failed: {{error}}",
          "deleteFailed": "Delete failed",
          "closureFailed": "Closure failed"
        }
      },
      "roles": {
        "admin": "Admin",
        "hse": "HSE",
        "operation": "Operation",
        "unknown": "Unknown"
      },
      "modules": {
        "dailyreport": "Reports",
        "ptw": "Permits",
        "analytics": "Analytics",
        "users": "Security"
      },
      "settings": {
        "title": "Security & System Settings",
        "subtitle": "Administrative Oversight",
        "tabs": {
          "users": "Add New User",
          "projects": "Project Management"
        },
        "addUserTitle": "New User Profile",
        "addUserSubtitle": "Create a system-level account with specific permissions.",
        "projectManagementTitle": "Project Portfolio",
        "projectManagementSubtitle": "Manage active projects, regions, and communication emails.",
        "createUser": "Create User",
        "fetchProjectsFailed": "Failed to load projects",
        "projectCreated": "Project created!",
        "projectCreateFailed": "Failed to create project",
        "projectNamePlaceholder": "Enter project name...",
        "projectEmailPlaceholder": "Project email (optional)...",
        "regionWest": "West Region",
        "regionWestShort": "West",
        "regionEast": "East Region",
        "regionEastShort": "East",
        "addProject": "Add New Project",
        "toggleProjectFailed": "Failed to update project status",
        "projectsTitle": "All Projects",
        "total": "Total",
        "noProjects": "No projects found",
        "deactivate": "Deactivate",
        "activate": "Activate"
      }
    }
  },
  ar: {
    translation: {
      "app": {
        "title": "إدارة السلامة",
        "subtitle": "إدارة الصحة والسلامة"
      },
      "nav": {
        "dashboard": "لوحة القيادة",
        "dailyReports": "التقارير اليومية",
        "analytics": "التحليلات",
        "ptwPermits": "تصاريح العمل",
        "ptwAnalytics": "تحليلات التصاريح",
        "userManagement": "إدارة المستخدمين",
        "settings": "الإعدادات",
        "logout": "تسجيل الخروج"
      },
      "common": {
        "loading": "جار التحميل...",
        "error": "خطأ",
        "save": "حفظ",
        "cancel": "إلغاء",
        "edit": "تعديل",
        "delete": "حذف",
        "status": "الحالة",
        "actions": "الإجراءات",
        "search": "بحث",
        "filter": "الفلاتر",
        "reset": "إعادة تعيين",
        "startDate": "تاريخ البدء",
        "endDate": "تاريخ الانتهاء",
        "project": "المشروع",
        "department": "القسم",
        "all": "الكل",
        "active": "نشط",
        "inactive": "غير نشط",
        "module": "وحدة",
        "command": "أمر",
        "saving": "جاري الحفظ...",
        "public": "عام",
        "success": "ناجح",
        "critical": "حرج",
        "unassigned": "غير معين"
      },
      "dashboard": {
        "welcome": "مرحباً بعودتك، {{name}}",
        "subtitle": "إليك نظرة عامة على السلامة والبيئة اليوم.",
        "stats": {
          "completedPermits": "التصاريح المكتملة",
          "activePermits": "التصاريح النشطة",
          "pendingApproval": "في انتظار الموافقة",
          "todayReports": "تقارير اليوم"
        },
        "compliance": {
          "bestPractices": "أفضل ممارسات السلامة",
          "highRisk": "الامتثال للمخاطر العالية",
          "permitRate": "معدل إكمال التصاريح",
          "top": "الأفضل: {{project}}",
          "hotspot": "نقطة ساخنة: {{project}}",
          "of": "{{completed}} من {{total}} تصاريح"
        },
        "trend": {
          "title": "اتجاه التصاريح (6 أشهر)"
        },
        "incidents": {
          "title": "الملاحظات الأخيرة",
          "viewAll": "عرض الكل",
          "noRecords": "لا توجد ملاحظات حديثة.",
          "resolved": "تم الحل",
          "open": "مفتوح"
        }
      },
      "ptw": {
        "title": "نظرة عامة على تصاريح العمل",
        "subtitle": "{{count}} تصاريح في النظام",
        "analyticsTitle": "تحليلات تصاريح العمل (PTW)",
        "analyticsSubtitle": "رقابة تصاريح العمل • الامتثال في الوقت الفعلي",
        "overdue": "التصاريح المتأخرة",
        "total": "إجمالي التصاريح",
        "highEnergy": "عمليات الطاقة العالية",
        "completionRate": "معدل الإنجاز",
        "distribution": "توزيع التصاريح حسب الوحدة",
        "distributionSub": "مشاركة الأقسام في نظام التصاريح",
        "profile": "ملف العمليات",
        "profileSub": "تصنيف التصاريح حسب نوع المهمة",
        "pulse": "النبض التشغيلي",
        "pulseSub": "تتبع الحجم التاريخي",
        "registry": "سجل التصاريح المتأخرة",
        "registrySub": "تحذير الامتثال",
        "categories": "الفئات",
        "syncError": "غير قادر على الاتصال بمحرك بيانات PTW.",
        "retry": "إعادة المحاولة",
        "operation": "العملية",
        "timeExceeded": "تجاوز الوقت",
        "issued": "صادر",
        "workUnits": "وحدات العمل",
        "dangerous": "مستوى الخطر",
        "operationType": "نوع العملية",
        "closedCycles": "دورات مغلقة",
        "trendAnalysis": "تحليل الاتجاه",
        "markPriority": "تحديد الأولوية",
        "criticalDelay": "تأخير حرج",
        "noOverdue": "درع التصاريح نشط • 0 عناصر متأخرة",
        "pending": "معلق",
        "details": "تفاصيل التصريح",
        "history": "سجل التصريح",
        "noHistory": "لا يوجد سجل مسجل بعد.",
        "images": "الصور المرفقة",
        "noImages": "لا توجد صور مرفقة",
        "approve": "اعتماد التصريح",
        "finish": "إنهاء التصريح",
        "markNotCompleted": "تحديد كغير مكتمل",
        "markFinished": "تحديد كمكتمل",
        "nonCompliance": "عدم امتثال",
        "delete": "حذف",
        "status": {
          "pending": "غير معتمد",
          "approved": "معتمد",
          "finished": "منتهي",
          "notCompleted": "غير مكتمل",
          "nonCompliance": "عدم امتثال"
        },
        "table": {
          "idDate": "رقم التصريح / التاريخ",
          "projectDept": "المشروع / القسم",
          "type": "العملية",
          "compliance": "الامتثال",
          "location": "موقع العمل",
          "actions": "الإجراءات",
          "workLocation": "موقع العمل",
          "description": "وصف العمل",
          "tools": "الأدوات والمعدات",
          "risk": "تقييم المخاطر",
          "measures": "تدابير السلامة",
          "manager": "مدير التنفيذ",
          "admin": "توقيع المسؤول",
          "safety": "مسؤول السلامة",
          "start": "وقت البدء",
          "end": "وقت الانتهاء",
          "company": "الشركة"
        },
        "messages": {
          "noPermits": "لا توجد تصاريح تطابق الفلاتر الخاصة بك"
        },
        "exportExcel": "تصدير Excel",
        "newPermit": "تصريح جديد",
        "filters": {
          "permitNumber": "رقم التصريح",
          "searchPlaceholder": "بحث ...",
          "allOperations": "كل العمليات",
          "reset": "إعادة تعيين"
        },
        "operations": {
          "cold": "عمل بارد",
          "hot": "عمل ساخن",
          "radiography": "عمل تصوير إشعاعي",
          "confined": "مكان محصور",
          "excavation": "حفر",
          "height": "عمل على ارتفاع"
        }
      },
      "users": {
        "title": "الأمن والتحكم في المستخدمين",
        "subtitle": "رقابة المسؤول",
        "totalActive": "{{count}} حسابات نشطة",
        "noRecords": "لا توجد سجلات هوية",
        "account": "الحساب",
        "securityLevel": "مستوى الأمان",
        "assignment": "التعيين",
        "systemUser": "مستخدم النظام",
        "unassignedTitle": "مسمى غير معين",
        "profileEngine": "محرك ملف تعريف المستخدم",
        "accountId": "معرف الحساب",
        "identityDetails": "تفاصيل الهوية",
        "username": "اسم المستخدم",
        "password": "كلمة المرور",
        "clearance": "التصريح الأمني",
        "unit": "تعيين الوحدة",
        "fullName": "الاسم الكامل",
        "jobTitle": "المسمى الوظيفي",
        "activeStatus": "حالة الحساب",
        "activeStatusSub": "منح أو سحب الوصول إلى النظام فوراً.",
        "permissions": "الأذونات",
        "activeRules": "{{count}} قواعد نشطة",
        "loadDefaultsConfirm": "هل تريد تحميل الأذونات الافتراضية لهذا الدور؟",
        "updateSuccess": "تم تحديث المستخدم بنجاح!",
        "updateError": "فشل تحديث المستخدم"
      },
      "login": {
        "title": "إدارة السلامة والصحة المهنية",
        "subtitle": "إدارة الصحة والسلامة والبيئة للمؤسسات",
        "username": "اسم المستخدم",
        "password": "كلمة المرور",
        "forgotPassword": "هل نسيت كلمة السر؟",
        "signIn": "تسجيل الدخول إلى لوحة القيادة",
        "footer1": "تسجيل دخول آمن مع تشفير طرف إلى طرف.",
        "footer2": "© 2026 Edara Systems. جميع الحقوق محفوظة.",
        "error": "اسم المستخدم أو كلمة المرور غير صالحة. يرجى المحاولة مرة أخرى."
      },
      "analytics": {
        "title": "تحليلات التقارير اليومية",
        "subtitle": "لوحة رؤى السلامة • إحصائيات مباشرة",
        "syncFailed": "فشل مزامنة التحليلات",
        "syncError": "صادف محرك البيانات مشكلة في المزامنة مع الخادم.",
        "metrics": {
          "overdue": "تنبيهات متأخرة",
          "total": "إجمالي التقارير",
          "focus": "الأقسام تحت التركيز",
          "coverage": "التغطية الشهرية",
          "unresolved": "غير محلول",
          "timeExceeded": "تجاوز الوقت",
          "cumulative": "تراكمي",
          "observations": "ملاحظات",
          "active": "نشط",
          "safetyUnits": "وحدات السلامة",
          "historical": "تاريخي",
          "dayAnalysis": "تحليل يومي"
        },
        "charts": {
          "distribution": "توزيع سلامة الأقسام",
          "distributionSub": "التقارير المسجلة لكل وحدة عمليات",
          "riskMatrix": "مصفوفة مخاطر الملاحظات",
          "riskMatrixSub": "تحليل الحرج للمخاطر المبلغ عنها",
          "riskLevels": "مستويات"
        },
        "overdue": {
          "title": "الملاحظات المتأخرة الحرجة",
          "subtitle": "إجراء مطلوب • {{count}} عناصر",
          "nonCompliance": "عدم امتثال",
          "delayHours": "ساعات التأخير",
          "late": "+{{hours}} ساعة تأخير",
          "allClear": "كل شيء تمام! لا توجد تقارير متأخرة"
        },
        "filters": {
          "dateRange": "نطاق التاريخ",
          "allProjects": "كل المشاريع",
          "allDepartments": "كل الأقسام",
          "allRisks": "كل المخاطر",
          "allStatuses": "كل الحالات",
          "allUsers": "كل المستخدمين",
          "high": "عالية",
          "medium": "متوسطة",
          "low": "منخفضة",
          "open": "مفتوح",
          "resolved": "تم الحل"
        }
      },
      "dailyReport": {
        "title": "التقارير اليومية",
        "subtitle": "{{count}} ملاحظة مسجلة",
        "newReport": "تقرير جديد",
        "filters": {
          "createdBy": "أنشئ بواسطة",
          "risk": "المخاطر",
          "limit": "العدد",
          "entries": "{{count}} مدخلات"
        },
        "table": {
          "id": "#",
          "date": "التاريخ",
          "project": "المشروع",
          "department": "القسم",
          "workType": "طبيعة العمل",
          "risk": "المخاطر",
          "status": "الحالة",
          "createdBy": "أنشئ بواسطة",
          "actions": "الإجراءات"
        },
        "status": {
          "resolved": "تم الحل",
          "open": "مفتوح"
        },
        "actions": {
          "viewDetails": "عرض التفاصيل الكاملة",
          "editReport": "تعديل التقرير",
          "mailSent": "تم إرسال البريد",
          "sendEmail": "إرسال بريد إلكتروني",
          "resolveHazard": "حل الخطر",
          "deleteRecord": "حذف السجل"
        },
        "pagination": {
          "info": "صفحة {{page}} / {{totalPages}} · {{total}} مدخلات"
        },
        "details": {
          "title": "ملاحظة رقم #{{id}}",
          "loggedBy": "سجلت بواسطة {{name}} في {{date}}",
          "riskLevel": "مستوى الخطر",
          "observationReport": "تقرير الملاحظة",
          "type": "النوع (طبيعة العمل)",
          "description": "الوصف (وصف العمل)",
          "detailedObservation": "ملاحظات مفصلة",
          "safetyCompliance": "الامتثال للسلامة",
          "correctiveAction": "الإجراء التصحيحي",
          "initialEvidence": "الأدلة الأولية",
          "noImages": "لا توجد صور مرفوعة.",
          "resolutionVerified": "تم التحقق من الحل",
          "actionTaken": "الإجراء المتبع",
          "closedBy": "أغلق بواسطة",
          "closeDetails": "إغلاق التفاصيل"
        },
        "resolution": {
          "title": "حل الخطر",
          "caseId": "رقم الحالة #{{id}}",
          "notesLabel": "ملاحظات الإغلاق (الإجراء النهائي)",
          "notesPlaceholder": "صف بإيجاز الإجراء التصحيحي المطبق...",
          "photoLabel": "صورة التحقق",
          "upload": "رفع",
          "proofTitle": "دليل الامتثال",
          "proofSub": "يتم تحسين الصور تلقائياً لسجلات المراجعة.",
          "submit": "حل الملاحظة"
        },
        "confirm": {
          "delete": "هل أنت متأكد أنك تريد حذف هذا التقرير؟",
          "email": "هل أنت متأكد أنك تريد إرسال تقرير الملاحظة عبر البريد الإلكتروني؟"
        },
        "messages": {
          "emailSuccess": "نجاح: تم إرسال البريد الإلكتروني بنجاح.",
          "emailFailed": "فشل الإرسال: {{error}}",
          "deleteFailed": "فشل الحذف",
          "closureFailed": "فشل الإغلاق"
        }
      },
      "roles": {
        "admin": "مشرف",
        "hse": "مسؤول سلامة",
        "operation": "تشغيل",
        "unknown": "غير معروف"
      },
      "modules": {
        "dailyreport": "التقارير",
        "ptw": "التصاريح",
        "analytics": "التحليلات",
        "users": "الأمن"
      },
      "settings": {
        "title": "إعدادات الأمان والنظام",
        "subtitle": "الرقابة الإدارية",
        "tabs": {
          "users": "إضافة مستخدم جديد",
          "projects": "إدارة المشاريع"
        },
        "addUserTitle": "ملف تعريف مستخدم جديد",
        "addUserSubtitle": "إنشاء حساب على مستوى النظام مع أذونات محددة.",
        "projectManagementTitle": "محفظة المشاريع",
        "projectManagementSubtitle": "إدارة المشاريع النشطة والمناطق ورسائل البريد الإلكتروني للتواصل.",
        "createUser": "إنشاء مستخدم",
        "fetchProjectsFailed": "فشل تحميل المشاريع",
        "projectCreated": "تم إنشاء المشروع!",
        "projectCreateFailed": "فشل إنشاء المشروع",
        "projectNamePlaceholder": "أدخل اسم المشروع...",
        "projectEmailPlaceholder": "بريد المشروع (اختياري)...",
        "regionWest": "منطقة الغرب",
        "regionWestShort": "الغرب",
        "regionEast": "منطقة الشرق",
        "regionEastShort": "الشرق",
        "addProject": "إضافة مشروع جديد",
        "toggleProjectFailed": "فشل تحديث حالة المشروع",
        "projectsTitle": "جميع المشاريع",
        "total": "الإجمالي",
        "noProjects": "لم يتم العثور على مشاريع",
        "deactivate": "تعطيل",
        "activate": "تفعيل"
      }
    }
  }
};

i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources,
    fallbackLng: 'en',
    interpolation: {
      escapeValue: false
    }
  });

// Set document direction based on language
i18n.on('languageChanged', (lng) => {
  document.dir = lng === 'ar' ? 'rtl' : 'ltr';
  document.documentElement.lang = lng;
});

// Initialize direction
document.dir = i18n.language === 'ar' ? 'rtl' : 'ltr';
document.documentElement.lang = i18n.language;

export default i18n;
