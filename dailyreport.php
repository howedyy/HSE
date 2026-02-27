<?php
require_once 'include/header.php';
require_once 'constants/dbconnect.php';

$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$edit_data = null;

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM daily_report WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="custom/css/style.css">
    <title><?= $edit_id ? 'Edit Observation' : 'HSE Daily Report' ?></title>
    <script src="assests/jquery/jquery-3.7.1.min.js"></script>
       <style>
        /* Loading bar styles */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-bar {
            width: 300px;
            height: 20px;
            background: #f3f3f3;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .loading-progress {
            width: 0;
            height: 100%;
            background: #4CAF50;
            animation: loading 2s linear infinite;
        }

        @keyframes loading {
            0% { width: 0; }
            50% { width: 80%; }
            100% { width: 0; }
        }

        .loading-text {
            color: white;
            margin-top: 10px;
            font-family: Arial, sans-serif;
            font-size: 16px;
        }

        /* Ensure response message is styled */
        #responseMessage {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            display: none;
            text-align: center;
        }

        #responseMessage.success {
            background: #dff0d8;
            color: #3c763d;
        }

        #responseMessage.error {
            background: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="form-container-DR">
        <h1><?= $edit_id ? 'Edit Observation #' . $edit_id : 'HSE Daily Report' ?></h1>
        <form id="my-form-DR" action="submit_button/submit_dailyreport.php" enctype="multipart/form-data" method="post">
            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
            
            <div class="form-group-DR">
                <label for="date">التاريخ والوقت:</label>
                <input type="datetime-local" id="date" name="date" readonly value="<?= $edit_data ? date('Y-m-d\TH:i', strtotime($edit_data['date'])) : '' ?>">

            </div>
            <div class="form-group-DR">
                <label for="projectname">المشروع:</label>
                <select name="projectname" id="projectname" required>
                    <option value="">اختار</option>
                    <?php
                    $sql = "SELECT id, project_name FROM project WHERE project_status = 1";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $selected = ($edit_data && $edit_data['project'] == $row['id']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['project_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="department">القسم:</label>
                <select name="department" id="department" required>
                    <option value="">اختار</option>
                    <?php
                    $sql = "SELECT id, department_name FROM department WHERE department_status = 1";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $selected = ($edit_data && $edit_data['department'] == $row['id']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['department_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="observation">طبيعه العمل:</label>
                <select id="observation" name="observation" required onchange="updateWorkType(this.value)">
                    <option value="">اختار</option>
                    <option value="متابعه الاعمال" <?= ($edit_data && $edit_data['observation'] == 'متابعه الاعمال') ? 'selected' : '' ?>>متابعه الاعمال</option>
                    <option value="فحص الموقع" <?= ($edit_data && $edit_data['observation'] == 'فحص الموقع') ? 'selected' : '' ?>>فحص الموقع</option>
                    <option value="مخالفات السلوك" <?= ($edit_data && $edit_data['observation'] == 'مخالفات السلوك') ? 'selected' : '' ?>>مخالفات السلوك</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="work_type"> وصف العمل:</label>
                <select id="work_type" name="work_type" required data-selected="<?= $edit_data ? htmlspecialchars($edit_data['work_type']) : '' ?>">
                    <option value="">اختار</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="risk">شدة الخطورة:</label>
                <select id="risk" name="risk" required>
                    <option value="">اختار</option>
                    <option value="عالية" <?= ($edit_data && $edit_data['risk'] == 'عالية') ? 'selected' : '' ?>>عالية</option>
                    <option value="متوسطه" <?= ($edit_data && $edit_data['risk'] == 'متوسطه') ? 'selected' : '' ?>>متوسطه</option>
                    <option value="منخفضة" <?= ($edit_data && $edit_data['risk'] == 'منخفضة') ? 'selected' : '' ?>>منخفضة</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="observation_description">تطابق العمل مع متطلبات السلامه:</label>
                <select id="observation_description" name="observation_description" required>
                    <option value="">اختار</option>
                    <option value="ممارسه جيده" <?= ($edit_data && $edit_data['observation_description'] == 'ممارسه جيده') ? 'selected' : '' ?>>ممارسة جيده</option>
                    <option value="ملاحظة تحتاج الي تصحيح" <?= ($edit_data && $edit_data['observation_description'] == 'ملاحظة تحتاج الي تصحيح') ? 'selected' : '' ?>>ملاحظة تحتاج الي تصحيح</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="operation_corrective">الاجراء التصحيحي من ادارة التشغيل:</label>
                <select id="operation_corrective" name="operation_corrective" required data-selected="<?= $edit_data ? htmlspecialchars($edit_data['operation_corrective']) : '' ?>">
                    <option value="">اختار</option>
                    <option value="تم تنفيذ تعليمات السلامه" <?= ($edit_data && $edit_data['operation_corrective'] == 'تم تنفيذ تعليمات السلامه') ? 'selected' : '' ?>>تم تنفيذ تعليمات السلامة</option>
                    <option value="لم يتم تنفيذ تعليمات السلامه" <?= ($edit_data && $edit_data['operation_corrective'] == 'لم يتم تنفيذ تعليمات السلامه') ? 'selected' : '' ?>>لم يتم تنفيذ تعليمات السلامة</option>
                    <option value="ايقاف الاعمال" <?= ($edit_data && $edit_data['operation_corrective'] == 'ايقاف الاعمال') ? 'selected' : '' ?>>ايقاف الاعمال</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="description">ملاحظات (Notes):</label>
                <textarea id="description" name="description" rows="8" cols="50"><?= $edit_data ? htmlspecialchars($edit_data['description']) : '' ?></textarea>
            </div>

            <label for="image">الصور <?= $edit_id ? '(اترك فارغاً للاحتفاظ بالصور القديمة)' : '' ?>:</label>
            <input type="file" id="image" name="images[]" accept="image/*" multiple <?= $edit_id ? '' : 'required' ?>>
            <div id="imagePreviewContainer" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                <?php if ($edit_data && !empty($edit_data['image_upload'])): ?>
                    <?php 
                    $existing_images = json_decode($edit_data['image_upload'], true);
                    if ($existing_images) {
                        foreach ($existing_images as $img) {
                            echo "<img src='assests/uploads/" . htmlspecialchars($img) . "' style='max-width: 150px; border: 1px solid #ccc;'>";
                        }
                    }
                    ?>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <button type="submit"><?= $edit_id ? 'Update Report' : 'Submit Report' ?></button>
            </div>
            <div class="form-group">
                <a href="dailyreport_overview.php" class="btn">Back to Overview</a>
            </div>
        </form>
        <div id="responseMessage"></div>
        <div class="loading-overlay" id="loading-overlay">
            <div>
                <div class="loading-bar">
                    <div class="loading-progress"></div>
                </div>
                <div class="loading-text">جاري الإرسال...</div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            (function() {
                var edit_id = <?= $edit_id ?>;
                if (!edit_id) {
                    var now = new Date();
                    var year = now.getFullYear();
                    var month = (now.getMonth() + 1).toString().padStart(2, '0');
                    var day = now.getDate().toString().padStart(2, '0');
                    var hours = now.getHours().toString().padStart(2, '0');
                    var minutes = now.getMinutes().toString().padStart(2, '0');
                    document.getElementById('date').value = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
                }
            })();
            
            var workTypeMapping = {
                "متابعه الاعمال": [
                    "اعمال روتينية", "تصريح عمل في اماكن محصورة", "تصريح عمل على ارتفاع", 
                    "اعمال خطرة بدون تصريح", "تصريح عمل بمواد كميائية خطرة", "تصريح حفر", 
                    "تصريح عزل طاقه", "تصريح اعمال ساخنه", "تصريح رفع"
                ],
                "فحص الموقع": [
                    "فحص انظمة واجهزة الاطفاء", "فحص التوصيلات الكهربائية", "فحص انظمة السباكة", 
                    "فحص الحجر الهاشمي والرخام والجبسم بورد", "فحص الزجاج السيكوريت", 
                    "فحص الديكوريشن الخشب واللوفارات الالومنيوم", "فحص حالة التخزين", 
                    "فحص النظافة العامة للمكان", "فحص حالة الطريق", "فحص حالة اللاند اسكيب", 
                    "فحص البنية التحتية", "فحص وجود حشارات او حيوانات ضارة"
                ],
                "مخالفات السلوك": [
                    "مخالفة قيادة مركبة", "عدم ارتداء مهمات الوقاية الشخصية", "التصرف بشكل غير امن"
                ]
            };

            function populateWorkTypes(selectedValue, preSelected = '') {
                var workTypeSelect = document.getElementById('work_type');
                workTypeSelect.innerHTML = '<option value="">اختار</option>';
                
                if (selectedValue && workTypeMapping[selectedValue]) {
                    workTypeMapping[selectedValue].forEach(function(option) {
                        var optionElement = document.createElement('option');
                        optionElement.value = option;
                        optionElement.textContent = option;
                        if (option === preSelected) {
                            optionElement.selected = true;
                        }
                        workTypeSelect.appendChild(optionElement);
                    });
                }
            }

            document.getElementById('observation').onchange = function() {
                populateWorkTypes(this.value);
            };

            // Pre-populate if editing
            var edit_observation = document.getElementById('observation').value;
            var edit_work_type = document.getElementById('work_type').getAttribute('data-selected');
            if (edit_observation) {
                populateWorkTypes(edit_observation, edit_work_type);
            }

            document.getElementById('observation_description').onchange = function() {
                var selectedValue = this.value;
                var operationCorrectiveSelect = document.getElementById('operation_corrective');
                var currentSelected = operationCorrectiveSelect.getAttribute('data-selected');
                
                operationCorrectiveSelect.innerHTML = '<option value="">اختار</option>';
                
                if (selectedValue === 'ممارسه جيده') {
                    var option = document.createElement('option');
                    option.value = 'تم تنفيذ تعليمات السلامه';
                    option.textContent = 'تم تنفيذ تعليمات السلامة';
                    if (option.value === currentSelected) option.selected = true;
                    operationCorrectiveSelect.appendChild(option);
                } else if (selectedValue === 'ملاحظة تحتاج الي تصحيح') {
                    var correctiveOptions = [
                        { value: 'لم يتم تنفيذ تعليمات السلامه', text: 'لم يتم تنفيذ تعليمات السلامة' },
                        { value: 'ايقاف الاعمال', text: 'ايقاف الاعمال' }
                    ];
                    
                    correctiveOptions.forEach(function(optionData) {
                        var option = document.createElement('option');
                        option.value = optionData.value;
                        option.textContent = optionData.text;
                        if (option.value === currentSelected) option.selected = true;
                        operationCorrectiveSelect.appendChild(option);
                    });
                }
            };
            
            document.getElementById('my-form-DR').onsubmit = function(e) {
                e.preventDefault();
                
                document.getElementById('loading-overlay').style.display = 'flex';
                
                var formData = new FormData(this);
                var xhr = new XMLHttpRequest();
                
                xhr.open('POST', 'submit_button/submit_dailyreport.php', true);
                
                xhr.onload = function() {
                    document.getElementById('loading-overlay').style.display = 'none';
                    
                    var responseMessage = document.getElementById('responseMessage');
                    responseMessage.style.display = 'block';
                    
                    if (xhr.responseText.trim() === "success") {
                        responseMessage.innerHTML = 'تم حفظ التقرير بنجاح!';
                        responseMessage.className = 'success';
                        
                        setTimeout(function() {
                            window.location.href = "dailyreport_overview.php";
                        }, 1000);
                    } else {
                        responseMessage.innerHTML = "حدث خطأ أثناء الحفظ: " + xhr.responseText;
                        responseMessage.className = 'error';
                    }
                };
                
                xhr.onerror = function() {
                    document.getElementById('loading-overlay').style.display = 'none';
                    var responseMessage = document.getElementById('responseMessage');
                    responseMessage.style.display = 'block';
                    responseMessage.innerHTML = 'حدث خطأ أثناء الإرسال.';
                    responseMessage.className = 'error';
                };
                
                xhr.send(formData);
            };
            
            document.getElementById('image').onchange = function(event) {
                var files = event.target.files;
                var container = document.getElementById('imagePreviewContainer');
                container.innerHTML = '';
                
                for (var i = 0; i < files.length; i++) {
                    (function(file) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.maxWidth = '150px';
                            img.style.maxHeight = '150px';
                            img.style.border = '1px solid #ccc';
                            img.alt = 'Preview';
                            container.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    })(files[i]);
                }
            };
        });
    </script>
</body>
</html>

</html>