<?php
include 'constants/dbconnect.php';
session_start();
require_once "constants/auth.php";

if (!hasAccess('ptw.php', 'submit')) {
    header("Location: unauthorized.php");
    exit;
}
include 'include/header.php';


/*
session_start();
if (!isset($_SESSION['username'])) {
    die("User not logged in.");
}

$username = $_SESSION['username'];
$query = "SELECT editor_name FROM users WHERE username =?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$editor_name = $row['editor_name'] ?? ''; // Assign the retrieved name or empty if not found


// Fetch job_title from users table
$query = "SELECT job_title FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$job_title = $row['job_title'] ?? ''; // Assign retrieved value or empty if not found
/*
$query = "SELECT department_name 
          FROM users 
          JOIN departments d ON u.department_id = id 
          WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$department_name = $row['department_name'] ?? '';
*/

// Don't generate permit number here - it will be generated at submission time
$next_permit = "Will be auto-generated";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="custom/css/style.css">
    <title>PTW</title>
<script src="assests/jquery/jquery-3.6.0.min.js"></script>
</head>
<body>
    <form id="my_form" action="submit_button/submit_ptw.php" method="POST" enctype="multipart/form-data" style="max-width: 800px; margin: 0 auto; width: 100%; box-shadow:  0 4px 12px rgba(0, 123, 255, 0.2);">
<h1 class="custom-header">القسم الاول</h1>
<table class="styled-table">
   
<tr>
    <th>اسم محرر الطلب</th>
        <td><input type="text" name="editor_name" required></td>
</tr>
    
<tr>
    <th>الوظيفه</th>
    <td><input type="text" name="job_title" required></td>
</tr>
<tr>
    <th>الادارة</th>
    <td class="data-row">
        <select name="department" id="department">
                <option value="">اختار</option>
                 <?php
            $sql = "SELECT id, department_name FROM department WHERE department_status = 1";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
              echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['department_name']) . "</option>";
            }
            ?>
</select>
    </td>
</tr>
    <tr>
    <th>اسم المشروع</th>
    <td class="data-row">
        <select name="project_name" id="projectFilter" required>
            <option value="">اختـر المشروع</option>
            <?php
            $sql = "SELECT project_name FROM project";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['project_name']) . "'>" . htmlspecialchars($row['project_name']) . "</option>";
            }
            ?>
        </select>
    </td>
</tr>
    <tr>
        <th>موقع العمل</th>
        <td><input type="text" name="work_location" required></td>
    </tr>
    <tr>
    <th>رقم التصريح</th>
    <td><input type="text" name="permit_number" value="<?php echo $next_permit; ?>" readonly style="color: #666; font-style: italic;"></td>
</tr>
<!--
    <tr>
        <th>المقاول الباطن</th>
            <td><input type="text" name="subcontractor"></td>
    </tr>
-->
    <?php
    $min_date = date('Y-m-d');
    $max_date = date('Y-m-d', strtotime('+2 days'));
    ?>
    <tr>
        <th>تاريخ بدء الاعمال</th>
        <td><input type="date" name="permit_date" min="<?php echo $min_date; ?>" max="<?php echo $max_date; ?>" required></td>
    </tr>
    <tr>
        <th>توقيت بدء الاعمال</th>
        <td><input type="time" name="start_time" required></td>
    </tr>
    <tr>
        <th>إلى الساعة</th>
        <td><input type="time" name="end_time" value="16:00" max="16:00" required></td>
    </tr>
    <tr>
        <th>وصف طبيعه العمل</th>
        <td><input type="text" name="work_description" required></td>
    </tr>
    <tr>
        <th>المعدات و الادوات المستخدمه</th>
        <td><input type="text" name="tools_equipment" required></td>
    </tr>
    <tr>
        <th>صورة مرفقة (اختياري)</th>
        <td><input type="file" name="attachment_image" accept="image/*"></td>
    </tr>
    <tr>
  <th>هل يوجد مقاول؟</th>
  <td><input type="checkbox" id="toggleCompany" /> نعم</td>
</tr>
<tr>
  <th>اسم الشركة/المقاول</th>
  <td>
    <input type="text" name="company_name" id="companyInput" placeholder="أدخل اسم الشركة/المقاول" disabled>
  </td>
</tr>
    <tr>
        <th> مسئول التنفيذ</th>
        <td><input type="text" name="execution_manager_signature" placeholder="أدخل اسم المسئول" required></td>
    </tr>
    <tr>
        <th>مسؤول الإدارة محرر التصريح</th>
        <td><input type="text" name="admin_signature" placeholder="أدخل اسم المسؤول" required></td>
    </tr>
    <tr>
        <th>التوقيع</th>
        <td><input type="text" name="admin_signature" placeholder="أدخل التوقيع" required></td>
    </tr>
    </table>
<h1 class="custom-header">القسم الثانى: نوع العملية التي سيتم إجراؤها</h1>
<table class="styled-table">
    <tr>
        <th>نوع العملية</th>
        <td>
            <select class="select-3" name="operation" id="operation" required>
                <option value="" disabled selected>اختر نوع العملية</option>
                
                <option value="تقليم الجذور ">تقليم الجذور</option>
                <option value="السباكة">السباكة</option>
                <option value="أعمال حفر">أعمال حفر</option>
                <option value="أعمال لحام كهربي">أعمال لحام كهربي</option>
                <option value="العمل على ارتفاع سبايدر">العمل على ارتفاع سبايدر</option>
                <option value="أعمال رفع أحمال بمعدات ثقيلة">أعمال رفع أحمال بمعدات ثقيلة</option>
                <option value="العمل علي سقالة">العمل علي سقالة</option>
                <option value="العمل على السلم المفصلى">العمل على السلم المفصلى</option>
                <option value="أعمال نقل بمعدات ثقيلة">أعمال نقل بمعدات ثقيلة</option>
                <option value="العمل بداخل الغرف المغلقة">العمل بداخل الغرف المغلقة</option>
                <option value="العمل على السلم الهيدروليكي">العمل على السلم الهيدروليكي</option>
                <option value="إعمال كهرباء الجهد المتوسط">إعمال كهرباء الجهد المتوسط</option>
                <option value="العمل بالمواد الخطرة">العمل بالمواد الخطرة</option>
              <!--  <option value="أعمال أخرى">أعمال أخرى</option>-->
            </select>
        </td>
    </tr>
</table>

<h1 class="custom-header">القسم الثالث: طبيعة المخاطر والخطورة المرتبطة بالمهمة</h1>
<table class="styled-table">
    <tr>
        <th>طبيعة المخاطر</th>
        <td>
            <select class="select-3" name="risk" id="risk" required>
                <option value="" disabled selected>اختر طبيعة المخاطر</option>
                <!--
                <option value="سقوط من على ارتفاع">سقوط من على ارتفاع</option>
                <option value="عمل في حفر">عمل في حفر</option>
                <option value="عمل في مكان مغلق">عمل في مكان مغلق</option>
                <option value="ضوضاء">ضوضاء</option>
                <option value="مخاطر كهربائية">مخاطر كهربائية</option>
                <option value="مخاطر حريق">مخاطر حريق</option>
                <option value="مخاطر كيميائية">مخاطر كيميائية</option>
                <option value="أدخنة / غازات">أدخنة / غازات</option>
                <option value="مخاطر أخرى">مخاطر أخرى</option>-->
            </select>
        </td>
    </tr>
</table>
   <h1 class="custom-header">القسم الرابع: الإجراءات المتخذة</h1>
    <table class="styled-table">
        <tr>
            <th>الاشراف الدائم</th>
            <td><input type="checkbox" name="safety[]" value="الاشراف الدائم"></td>
        </tr>
        <tr>
            <th>تحليل مخاطر الوظيفية</th>
            <td><input type="checkbox" name="safety[]" value="تحليل مخاطر الوظيفية"></td>
        </tr>
        <tr>
            <th>تقييم مخاطر</th>
            <td><input type="checkbox" name="safety[]" value="تقييم مخاطر"></td>
        </tr>
        <tr>
            <th>عزل مصدر الطاقة</th>
            <td><input type="checkbox" name="safety[]" value="عزل مصدر الطاقة"></td>
        </tr>
        <tr>
            <th>اختبار غازات</th>
            <td><input type="checkbox" name="safety[]" value="اختبار غازات"></td>
        </tr>
        <tr>
            <th>وسيلة اطفاء حريق مناسبة</th>
            <td><input type="checkbox" name="safety[]" value="وسيلة اطفاء حريق مناسبة"></td>
        </tr>
        <tr>
            <th>وضع شريط تحذير حول مكان العمل</th>
            <td><input type="checkbox" name="safety[]" value="وضع شريط تحذير حول مكان العمل"></td>
        </tr>
        <tr>
            <th>وضع اقماع فسفورية حول مكان العمل</th>
            <td><input type="checkbox" name="safety[]" value="وضع اقماع فسفورية حول مكان العمل"></td>
        </tr>
        <tr>
            <th>وضع علامة تحذيرية او علامات ارشادية</th>
            <td><input type="checkbox" name="safety[]" value="وضع علامة تحذيرية او علامات ارشادية"></td>
        </tr>
        <tr>
            <th>محاضرة توعية بالمخاطر</th>
            <td><input type="checkbox" name="safety[]" value="محاضرة توعية بالمخاطر"></td>
        </tr>
        <tr>
            <th>مهمات وقاية اضافية</th>
            <td><input type="checkbox" name="safety[]" value="مهمات وقاية اضافية"></td>
        </tr>
        <tr>
            <th>احتياطات سلامة أخرى</th>
            <td><input type="checkbox" name="safety[]" value="احتياطات سلامة أخرى"></td>
        </tr>
    </table>
<!--
<h1 class="custom-header">القسم الخامس: إصدار التصريح</h1>
<table class="styled-table">
    <tr>
        <td colspan="2">
            <p>يستوفى هذا الجزء عن طريق المسئول عن أداء وتنفيذ العمل</p>
            <p>أقر بأننى قد قمت بقراءة وفهم كل ظروف العمل وإجراءات السلامة والأحتياطات اللازمة وتم التأكد من أن فريق العمل الذى سوف يقوم بتنفيذه تحت ادارتي قرأ وفهم وسيلتزم بكل ظروف العمل وإجراءات السلامة والأحتياطات اللازمة قبل بدء العمل, كما سألتزم بإبلاغ مدير/ القسم الهندسي المسؤول في حالة انتهاء العمل او تأجيله.</p>
        </td>
    </tr>
    <tr>
        <th>اسم الشركة/المقاول</th>
        <td><input type="text" name="company_name" placeholder="أدخل اسم الشركة/المقاول"></td>
    </tr>
    <tr>
        <th>توقيع مسئول التنفيذ</th>
        <td><input type="text" name="execution_manager_signature" placeholder="أدخل اسم المسئول"></td>
    </tr>
</table>

<h1 class="custom-header">القسم السادس: التصديق علي التصريح</h1>
<table class="styled-table">
    <tr>
        <th>مسؤول السلامة والصحة المهنية</th>
        <td><input type="text" name="safety_manager" placeholder="أدخل اسم المسؤول"></td>
    </tr>
    <tr>
        <th>التوقيع</th>
        <td><input type="text" name="safety_signature" placeholder="أدخل التوقيع"></td>
    </tr>
</table>

<h1 class="custom-header">القسم السابع: الموافقة على تمديد العمل</h1>
<table class="styled-table">
    <tr>
        <th>التصريح ساري من الساعة</th>
        <td><input type="time" name="extension_start_time"></td>
    </tr>
    <tr>
        <th>إلى الساعة</th>
        <td><input type="time" name="extension_end_time"></td>
    </tr>
    <tr>
        <th>توقيع مسؤول الإدارة محرر التصريح</th>
        <td><input type="text" name="admin_signature_2" placeholder="أدخل التوقيع"></td>
    </tr>
    <tr>
        <th>توقيع مسؤول السلامة والصحة المهنية</th>
        <td><input type="text" name="safety_signature_2" placeholder="أدخل التوقيع"></td>
    </tr>
</table>

<h1 class="custom-header">القسم الثامن: الانتهاء / تأجيل / الغاء الأعمال</h1>
<table class="styled-table">
    <tr>
        <th>حالة العمل</th>
        <td>
            <input type="radio" name="work_status" value="تم إنجاز العمل"> تم إنجاز العمل
            <input type="radio" name="work_status" value="لم يتم إنجاز العمل"> لم يتم إنجاز العمل
            <input type="radio" name="work_status" value="تم إلغاء العمل"> تم إلغاء العمل
        </td>
    </tr>
    <tr>
        <th>سبب الإلغاء</th>
        <td><input type="text" name="cancellation_reason" placeholder="أدخل سبب الإلغاء إذا تم إلغاء العمل"></td>
    </tr>
    <tr>
        <th>تم الانتهاء من العمل تماما وتم إعادة المكان/المعدات إلى الوضع المعتاد في أمان الساعة</th>
        <td><input type="time" name="completion_time"></td>
    </tr>
    <tr>
        <th>التاريخ</th>
        <td><input type="date" name="completion_date"></td>
    </tr>
    <tr>
        <th>توقيع المشرف المسؤول عن تنفيذ العمل</th>
        <td><input type="text" name="supervisor_signature" placeholder="أدخل التوقيع"></td>
    </tr>
    <tr>
        <th>توقيع الإدارة محرر التصريح</th>
        <td><input type="text" name="admin_signature_3" placeholder="أدخل التوقيع"></td>
    </tr>
    <tr>
        <th>توقيع مسؤول السلامة</th>
        <td><input type="text" name="safety_signature_3" placeholder="أدخل التوقيع"></td>
    </tr>
</table>
-->
<h1 class="custom-header">القسم الخامس: ملاحظات</h1>
<div class="instruction-box">
    <ul>
        <li>يبدأ العمل فقط بإصدار هذا التصريح و إخطار إدارة الصيانة عند القيام بأي أعمال حفر قد تؤثر على البنية التحتية أو شبكة الكهرباء.</li>
        <li>هذا التصريح لا يعتبر صالحاً إلا إذا كانت كل الأقسام مملوءة بالكامل.</li>
        <li>لا تبدأ العمل قبل أن يتم اعتماد التصريح بالموافقة من قبل منسق تصريح العمل.</li>
        <li>يلغى التصريح تلقائياً في حالة تغير ظروف العمل أو إذا تم إجراء أي تعديل في التصريح بعد إصداره، ويجب إصدار تصريح جديد.</li>
        <li>يجب ألا يتم البدء بالعمل حتى تستكمل جميع معايير السلامة المذكورة في هذا التقرير من المرحلة الأولى إلى المرحلة السادسة.</li>
        <li>أقصى مدة لصلاحية التصريح هي8 ساعات فقط، ويتم طلب تمديد للأعمال إذا لزم الأمر.</li>
    </ul>
</div>
<button type="submit">إرسال البيانات</button>
<div class="form-group">
    <a href="logout.php" class="btn">Logout</a>
</div>
</form>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text">جاري إرسال التصريح...</div>
        <div class="loading-progress-bar">
            <div class="loading-progress-fill" id="progressFill"></div>
        </div>
        <div class="loading-percentage" id="loadingPercentage">0%</div>
    </div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
}

.loading-content {
    background: white;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    min-width: 300px;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #1d546d;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
    font-family: 'Cairo', sans-serif;
}

.loading-progress-bar {
    width: 100%;
    height: 10px;
    background: #f0f0f0;
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 10px;
}

.loading-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #1d546d, #5f9598);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 5px;
}

.loading-percentage {
    font-size: 16px;
    color: #666;
    font-weight: bold;
}
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const myForm = document.getElementById("my_form");

    if (myForm) {
        myForm.addEventListener("submit", function(event) {
            event.preventDefault();
            
            // Show loading overlay
            const loadingOverlay = document.getElementById('loadingOverlay');
            const progressFill = document.getElementById('progressFill');
            const loadingPercentage = document.getElementById('loadingPercentage');
            
            loadingOverlay.style.display = 'flex';
            
            // Disable form inputs to prevent user interaction
            const formElements = myForm.querySelectorAll('input, select, textarea, button');
            formElements.forEach(element => element.disabled = true);
            
            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                if (progress < 90) {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    progressFill.style.width = progress + '%';
                    loadingPercentage.textContent = Math.round(progress) + '%';
                }
            }, 200);
            
            let formData = new FormData(this);
            
            fetch("submit_button/submit_ptw.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Complete progress
                clearInterval(progressInterval);
                progress = 100;
                progressFill.style.width = '100%';
                loadingPercentage.textContent = '100%';
                
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                    
                    if (data.status === "success") {
                        alert(data.message);
                        window.location.href = "ptw_overview.php";
                    } else {
                        // Re-enable form on error
                        formElements.forEach(element => element.disabled = false);
                        alert("حدث خطأ أثناء الإرسال:\n" + data.message);
                    }
                }, 500);
            })
            .catch(error => {
                clearInterval(progressInterval);
                loadingOverlay.style.display = 'none';
                formElements.forEach(element => element.disabled = false);
                console.error("حدث خطأ أثناء الإرسال:", error);
                alert("فشل الاتصال بالخادم. حاول مرة أخرى.");
            });
        });
    }

    // Restrict end time selection
    const endTime = document.getElementById("endTime");
    if (endTime) {
        endTime.addEventListener("change", function () {
            let selectedTime = this.value;
            let maxTime = "16:00";
            if (selectedTime > maxTime) {
                alert("لا يمكنك اختيار وقت بعد 4:00 مساءً");
                this.value = maxTime;
            }
        });
    }

    // Validate Permit Date
    const permitDateInput = document.querySelector('input[name="permit_date"]');
    if (permitDateInput) {
        permitDateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0,0,0,0);
            
            const maxDate = new Date();
            maxDate.setDate(today.getDate() + 2);
            maxDate.setHours(23,59,59,999);

            if (selectedDate < today || selectedDate > maxDate) {
                alert('تاريخ بدء الأعمال يجب أن يكون اليوم أو خلال 48 ساعة فقط.');
                this.value = ''; // Clear invalid date
            }
        });
    }

    // Dynamic risk options based on operation selection
const operationMapping = {
        "تقليم الجذور ": ["سقوط الاشجار  علي  الافراد والممتلكات "],
        "السباكة": ["انسكاب وتسريب  وغمر"],
        "أعمال حفر": ["عمل في حفر"],
        "أعمال لحام كهربي": ["مخاطر حريق"],
        "العمل على ارتفاع سبايدر": ["سقوط من على ارتفاع"],
        "أعمال رفع أحمال بمعدات ثقيلة": ["ضوضاء"],
        "العمل علي سقالة": ["سقوط من على ارتفاع"],
        "العمل على السلم المفصلى": ["سقوط من على ارتفاع"],
        "أعمال نقل بمعدات ثقيلة": ["ضوضاء"],
        "العمل بداخل الغرف المغلقة": ["عمل في مكان مغلق"],
        "العمل على السلم الهيدروليكي": ["سقوط من على ارتفاع"],
        "إعمال كهرباء الجهد المتوسط": ["مخاطر كهربائية"],
        "العمل بالمواد الخطرة": ["مخاطر كيميائية"],
    };

    const operationSelect = document.getElementById("operation");
    const riskSelect = document.getElementById("risk");

    if (operationSelect && riskSelect) {
        operationSelect.addEventListener("change", function () {
            const selectedOperation = this.value;
            riskSelect.innerHTML = '<option value="" disabled selected>اختر طبيعة المخاطر</option>'; 

            if (operationMapping[selectedOperation]) {
                operationMapping[selectedOperation].forEach(risk => {
                    const option = document.createElement("option");
                    option.value = risk;
                    option.textContent = risk;
                    riskSelect.appendChild(option);
                });
            }
        });
    }
});
      
// checkbox اسم المقاول 
document.addEventListener("DOMContentLoaded", function () {
    const toggleCheckbox = document.getElementById("toggleCompany");
    const companyInput = document.getElementById("companyInput");

    toggleCheckbox.addEventListener("change", function () {
      companyInput.disabled = !this.checked;
    });
  });


  document.getElementById("projectFilter").addEventListener("change", function () {
    const selectedProject = this.value.toLowerCase();
    const rows = document.querySelectorAll("tr.data-row");

    rows.forEach(row => {
        const projectCell = row.querySelector("td.project-column");
        const match = !selectedProject || (projectCell && projectCell.textContent.toLowerCase().includes(selectedProject));
        row.style.display = match ? "" : "none";
    });
});

</script>
</body>
</html>