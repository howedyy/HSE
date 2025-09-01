<?php
require_once 'include/header.php';
require_once 'constants/dbconnect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="custom/css/style.css">
    <title>HSE Daily Report</title>
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
        <h1>HSE Daily Report</h1>
        <form id="my-form-DR" action="submit_button/submit_dailyreport.php" enctype="multipart/form-data" method="post">
            <div class="form-group-DR">
                <label for="date">التاريخ والوقت:</label>
                <input type="datetime-local" id="date" name="date" readonly>

            </div>
            <div class="form-group-DR">
                <label for="projectname">المشروع:</label>
                <select name="projectname" id="projectname" required>
                    <option value="">اختار</option>
                    <?php
                    $sql = "SELECT id, project_name FROM project WHERE project_status = 1";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['project_name']) . "</option>";
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
                        echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['department_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="observation">طبيعه العمل:</label>
                <select id="observation" name="observation" required onchange="updateWorkType(this.value)">
                    <option value="">اختار</option>
                    <option value="متابعه الاعمال">متابعه الاعمال</option>
                    <option value="فحص الموقع">فحص الموقع</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="work_type"> وصف العمل:</label>
                <select id="work_type" name="work_type" required>
                    <option value="">اختار</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="risk">شدة الخطورة:</label>
                <select id="risk" name="risk" required>
                    <option value="">اختار</option>
                    <option value="عالية">عالية</option>
                    <option value="متوسطه">متوسطه</option>
                    <option value="منخفضة">منخفضة</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="observation_description">تطابق العمل مع متطلبات السلامه:</label>
                <select id="observation_description" name="observation_description" required>
                    <option value="">اختار</option>
                    <option value="ممارسه جيده">ممارسة جيده</option>
                    <option value="ملاحظة تحتاج الي تصحيح">ملاحظة تحتاج الي تصحيح</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="operation_corrective">الاجراء التصحيحي من ادارة التشغيل:</label>
                <select id="operation_corrective" name="operation_corrective" required>
                    <option value="">اختار</option>
                    <option value="تم تنفيذ تعليمات السلامه">تم تنفيذ تعليمات السلامة</option>
                    <option value="لم يتم تنفيذ تعليمات السلامه">لم يتم تنفيذ تعليمات السلامة</option>
                    <option value="ايقاف الاعمال">ايقاف الاعمال</option>
                </select>
            </div>
            <div class="form-group-DR">
                <label for="description">ملاحظات:</label>
                <textarea id="description" name="description" rows="8" cols="50"></textarea>
            </div>
            <label for="image">الصور:</label>
            <input type="file" id="image" name="images[]" accept="image/*" multiple required>
            <div id="imagePreviewContainer" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
            <div class="form-group">
                <button type="submit">Submit Report</button>
            </div>
            <div class="form-group">
                <a href="logout.php" class="btn">Logout</a>
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

                var now = new Date();
    
    var year = now.getFullYear();
    var month = (now.getMonth() + 1).toString().padStart(2, '0');
    var day = now.getDate().toString().padStart(2, '0');
    var hours = now.getHours().toString().padStart(2, '0');
    var minutes = now.getMinutes().toString().padStart(2, '0');
    
    document.getElementById('date').value = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
})();
            var now = new Date();
            var year = now.getFullYear();
            var month = (now.getMonth() + 1).toString().padStart(2, '0');
            var day = now.getDate().toString().padStart(2, '0');
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            
            var dateTimeString = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
            document.getElementById('date').value = dateTimeString;
            
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
                ]
            };

            document.getElementById('observation').onchange = function() {
                var selectedValue = this.value;
                var workTypeSelect = document.getElementById('work_type');
                
                workTypeSelect.innerHTML = '<option value="">اختار</option>';
                
                if (selectedValue && workTypeMapping[selectedValue]) {
                    workTypeMapping[selectedValue].forEach(function(option) {
                        var optionElement = document.createElement('option');
                        optionElement.value = option;
                        optionElement.textContent = option;
                        workTypeSelect.appendChild(optionElement);
                    });
                }
            };

            document.getElementById('observation_description').onchange = function() {
                var selectedValue = this.value;
                var operationCorrectiveSelect = document.getElementById('operation_corrective');
                
                operationCorrectiveSelect.innerHTML = '<option value="">اختار</option>';
                
                if (selectedValue === 'ممارسه جيده') {
                    var option = document.createElement('option');
                    option.value = 'تم تنفيذ تعليمات السلامه';
                    option.textContent = 'تم تنفيذ تعليمات السلامة';
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
                        operationCorrectiveSelect.appendChild(option);
                    });
                } else {
                    var allOptions = [
                        { value: 'تم تنفيذ تعليمات السلامه', text: 'تم تنفيذ تعليمات السلامة' },
                        { value: 'لم يتم تنفيذ تعليمات السلامه', text: 'لم يتم تنفيذ تعليمات السلامة' },
                        { value: 'ايقاف الاعمال', text: 'ايقاف الاعمال' }
                    ];
                    
                    allOptions.forEach(function(optionData) {
                        var option = document.createElement('option');
                        option.value = optionData.value;
                        option.textContent = optionData.text;
                        operationCorrectiveSelect.appendChild(option);
                    });
                }
            };
            
            var initialObservation = document.getElementById('observation').value;
            if (initialObservation && workTypeMapping[initialObservation]) {
                var workTypeSelect = document.getElementById('work_type');
                workTypeSelect.innerHTML = '<option value="">اختار</option>';
                
                workTypeMapping[initialObservation].forEach(function(option) {
                    var optionElement = document.createElement('option');
                    optionElement.value = option;
                    optionElement.textContent = option;
                    workTypeSelect.appendChild(optionElement);
                });
            }
            
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
                        responseMessage.innerHTML = 'تم إرسال التقرير بنجاح!';
                        responseMessage.className = 'success';
                        
                        setTimeout(function() {
                            window.location.href = "dailyreport.php";
                        }, 1000);
                    } else {
                        responseMessage.innerHTML = "حدث خطأ أثناء الإرسال: " + xhr.responseText;
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