<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>غير مصرح بالدخول</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            margin: 20px;
        }
        
        .error-icon {
            font-size: 80px;
            color: #ff6b6b;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #ff6b6b;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🚫</div>
        <h1 class="error-title">غير مصرح بالدخول</h1>
        <p class="error-message">
            عذراً، ليس لديك صلاحية للوصول إلى هذه الصفحة. <br>
            يرجى التواصل مع المسؤول لطلب الصلاحيات المطلوبة.
        </p>
        
        <?php if (isset($_SESSION['username'])): ?>
        <div class="user-info">
            <strong>المستخدم الحالي:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?><br>
            <strong>نوع المستخدم:</strong> <?php echo htmlspecialchars($_SESSION['user_type'] ?? 'غير محدد'); ?>
        </div>
        <?php endif; ?>
        
        <div class="btn-group">
            <a href="start_page.php" class="btn btn-primary">العودة للصفحة الرئيسية</a>
            <button onclick="history.back()" class="btn btn-secondary">الصفحة السابقة</button>
            <a href="logout.php" class="btn btn-danger">تسجيل خروج</a>
        </div>
    </div>
</body>
</html> 