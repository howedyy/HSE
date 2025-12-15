-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: hse
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `daily_report`
--

DROP TABLE IF EXISTS `daily_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NULL DEFAULT NULL,
  `project` varchar(45) DEFAULT NULL,
  `department` varchar(45) DEFAULT NULL,
  `work_type` varchar(45) DEFAULT NULL,
  `risk` varchar(45) DEFAULT NULL,
  `observation_description` varchar(45) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `image_upload` longtext DEFAULT NULL,
  `observation` varchar(45) DEFAULT NULL,
  `operation_corrective` varchar(45) DEFAULT NULL,
  `report_status` int(11) DEFAULT 0,
  `closed_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `closure_notes` text DEFAULT NULL,
  `closure_image` varchar(255) DEFAULT NULL,
  `closed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `closed_by` (`closed_by`),
  CONSTRAINT `daily_report_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `daily_report_ibfk_2` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_report`
--

LOCK TABLES `daily_report` WRITE;
/*!40000 ALTER TABLE `daily_report` DISABLE KEYS */;
INSERT INTO `daily_report` VALUES (1,'2025-07-04 21:00:00','2','2','تصريح عمل في اماكن محصورة','عالية','ملاحظة تحتاج الي تصحيح','stop action ','uploads/1751720823_WhatsApp_Image_2025-03-19_','متابعه الاعمال','لم يتم تنفيذ تعليمات السلامه',1,'2025-07-06 10:37:38',NULL,NULL,NULL,NULL),(2,'2025-07-02 21:00:00','6','2','فحص انظمة واجهزة الاطفاء','عالية','ملاحظة تحتاج الي تصحيح','isolate electricity ','uploads/1751720903_WhatsApp_Image_2025-03-19_','فحص الموقع','لم يتم تنفيذ تعليمات السلامه',1,'2025-07-05 15:27:09',NULL,NULL,NULL,NULL),(3,'2025-07-04 21:00:00','7','4','تصريح عمل في اماكن محصورة','عالية','ممارسه جيده','good','uploads/1751720966_WhatsApp_Image_2025-03-19_','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(4,'2025-06-30 21:00:00','1','7','فحص التوصيلات الكهربائية','متوسطه','ملاحظة تحتاج الي تصحيح','dwcwedcewc','uploads/1751721585_WhatsApp_Image_2025-03-19_','فحص الموقع','لم يتم تنفيذ تعليمات السلامه',1,'2025-07-06 10:49:40',NULL,NULL,NULL,NULL),(5,'2025-07-01 21:00:00','5','8','اعمال روتينية','منخفضة','ممارسه جيده','mmmmmm','uploads/1751721910_WhatsApp_Image_2025-03-19_','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(6,'2025-06-22 21:00:00','3','4','فحص النظافة العامة للمكان','متوسطه','ملاحظة تحتاج الي تصحيح','xbnzm','uploads/1751721956_WhatsApp_Image_2025-03-19_','فحص الموقع','لم يتم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(7,'2025-07-06 21:00:00','1','2','تصريح عمل على ارتفاع','متوسطه','ممارسه جيده','vfghh','uploads/1751837806_WhatsApp_Image_2025-03-19_','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-07 14:16:38',NULL,NULL,NULL,NULL),(8,'2025-07-06 21:00:00','4','2','فحص البنية التحتية','عالية','ملاحظة تحتاج الي تصحيح','kkkkk','uploads/1751837964_WhatsApp_Image_2025-03-19_','فحص الموقع','لم يتم تنفيذ تعليمات السلامه',1,'2025-07-12 23:52:49',NULL,NULL,NULL,NULL),(9,'2025-07-06 21:00:00','6','4','تصريح عمل على ارتفاع','عالية','ممارسه جيده','cs','uploads/1751838046_WhatsApp_Image_2025-03-19_','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(10,'2025-07-06 21:00:00','8','7','تصريح عزل طاقه','متوسطه','ممارسه جيده','qqqadd','uploads/1751838092_WhatsApp_Image_2025-03-19_','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(11,'2025-07-06 21:00:00','2','8','فحص التوصيلات الكهربائية','عالية','ممارسه جيده','wcadcadc','uploads/1751838117_WhatsApp_Image_2025-03-19_','فحص الموقع','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(12,'2025-07-06 21:00:00','9','3','اعمال روتينية','منخفضة','ملاحظة تحتاج الي تصحيح','cqcaca','uploads/1751838154_WhatsApp_Image_2025-03-19_','متابعه الاعمال','لم يتم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(13,'2025-07-06 21:00:00','10','6','تصريح اعمال ساخنه','عالية','ممارسه جيده','daasa','uploads/1751838201_WhatsApp_Image_2025-03-19_','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(14,'2025-07-06 21:00:00','3','9','اعمال خطرة بدون تصريح','عالية','ممارسه جيده','mmmmm','uploads/1751842051_WhatsApp_Image_2025-03-19_','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(15,'2025-07-08 21:00:00','3','2','اعمال روتينية','متوسطه','ممارسه جيده','done','uploads/1752063376_WhatsApp_Image_2025-03-19_','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-09 23:18:56',NULL,NULL,NULL,NULL),(16,'2025-07-13 21:00:00','2','2','فحص انظمة واجهزة الاطفاء','عالية','ممارسه جيده','jndcsjn','uploads/1752496312_WhatsApp_Image_2025-03-19_','فحص الموقع','تم تنفيذ تعليمات السلامه',1,'2025-07-14 15:16:22',NULL,NULL,NULL,NULL),(17,'2025-07-13 21:00:00','2','2','فحص انظمة واجهزة الاطفاء','عالية','ممارسه جيده','jndcsjn','uploads/1752496314_WhatsApp_Image_2025-03-19_','فحص الموقع','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(18,'2025-07-13 21:00:00','2','2','فحص انظمة واجهزة الاطفاء','عالية','ممارسه جيده','jndcsjn','uploads/1752496340_WhatsApp_Image_2025-03-19_','فحص الموقع','تم تنفيذ تعليمات السلامه',1,'2025-07-17 12:01:10',NULL,'pop up ','uploads/closures/1752742870_closure_Image-5.jpg',1),(19,'2025-07-13 21:00:00','8','1','اعمال روتينية','عالية','ممارسه جيده','kkkkkkoooooooo','submit_button/uploads/1752496818_WhatsApp_Ima','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-17 12:01:28',NULL,'bsnam','uploads/closures/1752742888_closure_Image-4.jpg',1),(20,'2025-07-13 21:00:00','10','5','اعمال روتينية','متوسطه','ممارسه جيده','hope','submit_button/uploads/1752497484_WhatsApp_Ima','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(21,'2025-07-13 21:00:00','1','1','فحص التوصيلات الكهربائية','منخفضة','ملاحظة تحتاج الي تصحيح','stay','submit_button/uploads/1752497829_WhatsApp_Ima','فحص الموقع','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(22,'2025-07-13 21:00:00','5','1','اعمال روتينية','منخفضة','ملاحظة تحتاج الي تصحيح','stay 2','submit_button/1752498144_WhatsApp_Image_2025-','متابعه الاعمال','لم يتم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(23,'2025-07-13 21:00:00','1','1','اعمال روتينية','منخفضة','ملاحظة تحتاج الي تصحيح','hope 3','submit_button/uploads/1752500275_WhatsApp_Ima','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(24,'2025-07-13 21:00:00','1','1','اعمال روتينية','عالية','ممارسه جيده','hope 4 this','submit_button/uploads/1752500663_WhatsApp_Ima','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(25,'2025-07-13 21:00:00','1','1','اعمال روتينية','عالية','ممارسه جيده','hope 4 this','submit_button/uploads/1752500797_WhatsApp_Ima','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(26,'2025-07-13 21:00:00','3','3','فحص انظمة واجهزة الاطفاء','عالية','ممارسه جيده','rocky','submit_button/uploads/1752501023_8.png','فحص الموقع','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(27,'2025-07-13 21:00:00','2','2','اعمال روتينية','عالية','ممارسه جيده','ruby','submit_button/uploads/1752501130_Image-5.jpg','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,NULL,NULL,NULL,NULL),(28,'2025-07-13 21:00:00','2','2','اعمال روتينية','عالية','ممارسه جيده','rocky&ruby','submit_button/uploads/1752501284_Image-2.jpg','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-16 11:30:53',NULL,NULL,NULL,NULL),(29,'2025-07-15 21:00:00','3','3','فحص انظمة واجهزة الاطفاء','عالية','ممارسه جيده','you are the best ever when you try to get the','submit_button/uploads/1752664532_Image-1.jpg','فحص الموقع','تم تنفيذ تعليمات السلامه',1,'2025-07-16 14:33:46',NULL,NULL,NULL,NULL),(30,'2025-07-15 21:00:00','1','1','اعمال روتينية','عالية','ممارسه جيده','you are the best ever when you try to get the connect to your higher self with healthy life , workout,sleep+8,and be kind\r\n','submit_button/uploads/1752665112_Image-4.jpg','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-16 14:48:57',NULL,NULL,NULL,NULL),(31,'2025-07-15 21:00:00','3','3','اعمال روتينية','عالية','ممارسه جيده','you are the best ever when you try to get the connect to your higher self with healthy life , workout,sleep+8,and be kind repeat it for 9009338393030 time ','submit_button/uploads/1752666619_Image-3.jpg','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-16 15:02:02',NULL,NULL,NULL,NULL),(32,'2025-07-15 21:00:00','3','2','اعمال روتينية','عالية','ممارسه جيده','it come with effort ','submit_button/uploads/1752667236_Image-2.jpg','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-17 11:23:43',1,'stop think ','uploads/closures/1752740623_closure_Image-1.jpg',1),(33,'2025-07-16 21:00:00','2','1','اعمال روتينية','عالية','ممارسه جيده','jhxwjhws','submit_button/uploads/1752745039_Image-2.jpg','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-17 14:37:17',1,'moham','uploads/closures/1752752237_closure_Image-1.jpg',1),(34,'2025-07-16 21:00:00','3','3','فحص انظمة واجهزة الاطفاء','عالية','ممارسه جيده','l;dsmsdcsdm;c,sd,','submit_button/uploads/1752752444_Screenshot_2','فحص الموقع','تم تنفيذ تعليمات السلامه',1,'2025-07-17 14:41:02',1,'lsaxklaslkm','uploads/closures/1752752462_closure_Screenshot_2025-06-30_005128.png',1),(35,'2025-07-23 21:00:00','1','3','فحص الحجر الهاشمي والرخام والجبسم بورد','منخفضة','ملاحظة تحتاج الي تصحيح','mmdklss;pwpws','[\"submit_button\\/uploads\\/1752754745_6878ea39','فحص الموقع','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(36,'2025-07-20 04:10:00','3','1','اعمال روتينية','عالية','ممارسه جيده','lkdms kmsdkm k;','[\"submit_button\\/uploads\\/1752754832_6878ea90','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(37,'2025-07-24 21:00:00','5','2','فحص انظمة واجهزة الاطفاء','عالية','ممارسه جيده','msmsdllksdlkkwqwqp','[\"submit_button\\/uploads\\/1752755048_6878eb683297c_Image-1.jpg\",\"submit_button\\/uploads\\/1752755048_6878eb683382b_Image-4.jpg\",\"submit_button\\/uploads\\/1752755048_6878eb6833907_Image-5.jpg\"]','فحص الموقع','تم تنفيذ تعليمات السلامه',1,'2025-07-23 13:26:48',1,'hrke','uploads/closures/1753266408_closure_desktop-wallpaper-gustave-courbet-classic-art-oil-painting-and-mobile-backgrounds-gustave-courbet.jpg',1),(38,'2025-07-30 21:00:00','6','3','اعمال روتينية','عالية','ممارسه جيده','mmmmmmmmssssssssssssspppppppppppppppplllllllllllll','[\"submit_button\\/uploads\\/1752755357_6878ec9d60e1a_Screenshot_2025-05-24_130435.png\",\"submit_button\\/uploads\\/1752755357_6878ec9d61052_Screenshot_2025-05-26_233318.png\",\"submit_button\\/uploads\\/1752755357_6878ec9d611f2_Screenshot_2025-05-26_233759.png\",\"submit_button\\/uploads\\/1752755357_6878ec9d61373_Screenshot_2025-07-02_123353.png\"]','متابعه الاعمال','لم يتم تنفيذ تعليمات السلامه',1,'2025-07-17 15:33:13',1,'nalnlnalsl','uploads/closures/1752755593_closure_Screenshot_2025-07-10_150154.png',1),(39,'2025-07-19 21:00:00','1','2','اعمال روتينية','عالية','ممارسه جيده','kkk','[\"submit_button\\/uploads\\/1753000801_687cab616ebe7_1.png\",\"submit_button\\/uploads\\/1753000801_687cab616efe0_2.png\",\"submit_button\\/uploads\\/1753000801_687cab616f204_3.png\",\"submit_button\\/uploads\\/1753000801_687cab616f414_4.png\",\"submit_button\\/uploads\\/1753000801_687cab616f666_5.png\",\"submit_button\\/uploads\\/1753000801_687cab616fa37_6.png\",\"submit_button\\/uploads\\/1753000801_687cab616fd3a_7.png\",\"submit_button\\/uploads\\/1753000801_687cab61700aa_8.png\",\"submit_button\\/uploads\\/1753000801_687cab6170410_9.png\",\"submit_button\\/uploads\\/1753000801_687cab6170741_10.png\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-20 11:41:07',1,'llll','',1),(40,'2025-07-20 08:16:49','2','1','اعمال روتينية','عالية','ممارسه جيده','kkksssooo','[\"submit_button\\/uploads\\/1753003009_687cb40155672_4.png\",\"submit_button\\/uploads\\/1753003009_687cb401559d8_9.png\",\"submit_button\\/uploads\\/1753003009_687cb4015df46_10.png\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-20 20:21:50',1,'stopped ','',1),(41,'2025-07-20 08:27:44','1','6','اعمال روتينية','عالية','ممارسه جيده','mxksod','[\"submit_button\\/uploads\\/1753003664_687cb69032fc3_7.png\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(42,'2025-07-20 08:32:45','5','8','فحص الحجر الهاشمي والرخام والجبسم بورد','عالية','ممارسه جيده','mmmkkkoooo','[\"submit_button\\/uploads\\/1753003965_687cb7bdebec1_4.png\"]','فحص الموقع','تم تنفيذ تعليمات السلامه',1,'2025-07-20 20:35:58',1,'mfkfh','',1),(43,'2025-07-20 08:41:41','3','2','اعمال روتينية','عالية','ممارسه جيده','llllssssss','[\"submit_button\\/uploads\\/1753004501_687cb9d5201b9_3.png\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-20 13:51:11',1,' rocky ruby','',1),(44,'2025-07-20 08:45:41','10','4','فحص التوصيلات الكهربائية','عالية','ممارسه جيده','mmmmmmmmmmmmmaaaaaaaaa','[\"submit_button\\/uploads\\/1753004741_687cbac5cc833_4.png\"]','فحص الموقع','تم تنفيذ تعليمات السلامه',1,'2025-07-20 23:42:32',1,'stttttoooooopppppppp','',1),(45,'2025-07-20 09:51:25','7','3','فحص انظمة واجهزة الاطفاء','عالية','ممارسه جيده','mmmmmmmmmaaaaaaa','[\"submit_button\\/uploads\\/1753005085_687cbc1d7fd1d_7.png\"]','فحص الموقع','تم تنفيذ تعليمات السلامه',1,'2025-07-20 23:15:58',1,'logkfm','',1),(46,'2025-07-21 08:22:32','4','4','فحص انظمة واجهزة الاطفاء','عالية','ملاحظة تحتاج الي تصحيح','mkfdlso','[\"submit_button\\/uploads\\/1753086152_687df8c810fa7_WhatsApp_Image_2025-07-21_at_11_17_49_AM.jpeg\",\"submit_button\\/uploads\\/1753086152_687df8c811158_WhatsApp_Image_2025-07-21_at_11_17_11_AM.jpeg\"]','فحص الموقع','لم يتم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(47,'2025-07-21 08:25:59','9','5','فحص الحجر الهاشمي والرخام والجبسم بورد','متوسطه','ممارسه جيده','mznxgtrwf','[\"submit_button\\/uploads\\/1753086359_687df9973d756_WhatsApp_Image_2025-07-21_at_11_17_49_AM.jpeg\",\"submit_button\\/uploads\\/1753086359_687df9973d8f0_WhatsApp_Image_2025-07-21_at_11_17_11_AM.jpeg\"]','فحص الموقع','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(48,'2025-07-23 07:19:11','4','5','تصريح عمل على ارتفاع','عالية','ملاحظة تحتاج الي تصحيح','bal7','[\"submit_button\\/uploads\\/1753255151_68808cef9e8f2_1200px-Enrique_Simonet_-_Marina_veneciana_6MB.jpg\",\"submit_button\\/uploads\\/1753255151_68808cef9ed46_desktop-wallpaper-gustave-courbet-classic-art-oil-painting-and-mobile-backgrounds-gustave-courbet.jpg\",\"submit_button\\/uploads\\/1753255151_68808cef9ee82_download.jpg\"]','متابعه الاعمال','لم يتم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(49,'2025-07-23 12:05:54','8','1','فحص التوصيلات الكهربائية','عالية','ممارسه جيده','mdksls','[\"submit_button\\/uploads\\/1753272354_6880d0222ab5d_DSC_7242.jpg\"]','فحص الموقع','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(50,'2025-07-23 12:11:25','15','3','اعمال روتينية','عالية','ممارسه جيده','jxncnzslc','[\"submit_button\\/uploads\\/1753272685_6880d16db7235_DSC_7242_-_Copy__2_.jpg\",\"submit_button\\/uploads\\/1753272687_6880d16f55191_DSC_7242_-_Copy__3_.jpg\",\"submit_button\\/uploads\\/1753272689_6880d1710d899_DSC_7242_-_Copy__4_.jpg\",\"submit_button\\/uploads\\/1753272690_6880d1727a440_DSC_7242_-_Copy__5_.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(51,'2025-07-23 12:11:31','15','3','اعمال روتينية','عالية','ممارسه جيده','jxncnzslc','[\"submit_button\\/uploads\\/1753272691_6880d173f0ad6_DSC_7242_-_Copy__2_.jpg\",\"submit_button\\/uploads\\/1753272693_6880d1759678b_DSC_7242_-_Copy__3_.jpg\",\"submit_button\\/uploads\\/1753272694_6880d176ec0c6_DSC_7242_-_Copy__4_.jpg\",\"submit_button\\/uploads\\/1753272696_6880d178765f5_DSC_7242_-_Copy__5_.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(52,'2025-07-23 12:11:38','15','3','اعمال روتينية','عالية','ممارسه جيده','jxncnzslc','[\"submit_button\\/uploads\\/1753272698_6880d17a13484_DSC_7242_-_Copy__2_.jpg\",\"submit_button\\/uploads\\/1753272699_6880d17b7b169_DSC_7242_-_Copy__3_.jpg\",\"submit_button\\/uploads\\/1753272700_6880d17ce6866_DSC_7242_-_Copy__4_.jpg\",\"submit_button\\/uploads\\/1753272702_6880d17e88042_DSC_7242_-_Copy__5_.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(53,'2025-07-23 12:11:43','15','3','اعمال روتينية','عالية','ممارسه جيده','jxncnzslc','[\"submit_button\\/uploads\\/1753272703_6880d17ff173d_DSC_7242_-_Copy__2_.jpg\",\"submit_button\\/uploads\\/1753272705_6880d1814fcf0_DSC_7242_-_Copy__3_.jpg\",\"submit_button\\/uploads\\/1753272706_6880d182ccdbf_DSC_7242_-_Copy__4_.jpg\",\"submit_button\\/uploads\\/1753272708_6880d1844103e_DSC_7242_-_Copy__5_.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(54,'2025-07-23 12:13:08','12','10','اعمال روتينية','عالية','ممارسه جيده','mxhsjso','[\"submit_button\\/uploads\\/1753272788_6880d1d4833fa_DSC_7242_-_Copy__2_.jpg\",\"submit_button\\/uploads\\/1753272790_6880d1d62114c_DSC_7242_-_Copy__3_.jpg\",\"submit_button\\/uploads\\/1753272791_6880d1d773bbe_DSC_7242_-_Copy__4_.jpg\",\"submit_button\\/uploads\\/1753272793_6880d1d94da52_DSC_7242_-_Copy__5_.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(55,'2025-07-23 12:13:15','12','10','اعمال روتينية','عالية','ممارسه جيده','mxhsjso','[\"submit_button\\/uploads\\/1753272795_6880d1dba55e2_DSC_7242_-_Copy__2_.jpg\",\"submit_button\\/uploads\\/1753272798_6880d1de02f5e_DSC_7242_-_Copy__3_.jpg\",\"submit_button\\/uploads\\/1753272800_6880d1e0712d9_DSC_7242_-_Copy__4_.jpg\",\"submit_button\\/uploads\\/1753272802_6880d1e2bdf93_DSC_7242_-_Copy__5_.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(56,'2025-07-23 12:15:43','14','1','اعمال روتينية','عالية','ممارسه جيده','xsaxax','[\"submit_button\\/uploads\\/1753272943_6880d26f593fe_images__4_.jpg\",\"submit_button\\/uploads\\/1753272943_6880d26f59621_1200px-Enrique_Simonet_-_Marina_veneciana_6MB.jpg\",\"submit_button\\/uploads\\/1753272943_6880d26f597be_desktop-wallpaper-gustave-courbet-classic-art-oil-painting-and-mobile-backgrounds-gustave-courbet.jpg\",\"submit_button\\/uploads\\/1753272943_6880d26f5997d_download.jpg\",\"submit_button\\/uploads\\/1753272943_6880d26f59b95_images__3_.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',1,'2025-07-23 15:34:03',1,'oijioij','uploads/closures/1753274042_closure_DSC_7242_-_Copy__4_.jpg',1),(57,'2025-07-29 08:30:30','10','1','اعمال روتينية','عالية','ممارسه جيده','soad','[\"submit_button\\/uploads\\/1753777830_688886a61a437_1200px-Enrique_Simonet_-_Marina_veneciana_6MB.jpg\",\"submit_button\\/uploads\\/1753777830_688886a61a877_desktop-wallpaper-gustave-courbet-classic-art-oil-painting-and-mobile-backgrounds-gustave-courbet.jpg\",\"submit_button\\/uploads\\/1753777830_688886a61a9e6_download.jpg\",\"submit_button\\/uploads\\/1753777830_688886a61ac77_images__3_.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL),(58,'2025-07-30 07:47:14','3','1','اعمال روتينية','عالية','ممارسه جيده','nozvndsnlvn','[\"submit_button\\/uploads\\/1753861634_6889ce02abfa2_images__4_.jpg\",\"submit_button\\/uploads\\/1753861634_6889ce02ac13a_1200px-Enrique_Simonet_-_Marina_veneciana_6MB.jpg\",\"submit_button\\/uploads\\/1753861634_6889ce02ac259_desktop-wallpaper-gustave-courbet-classic-art-oil-painting-and-mobile-backgrounds-gustave-courbet.jpg\"]','متابعه الاعمال','تم تنفيذ تعليمات السلامه',0,NULL,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `daily_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(255) DEFAULT NULL,
  `department_status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department`
--

LOCK TABLES `department` WRITE;
/*!40000 ALTER TABLE `department` DISABLE KEYS */;
INSERT INTO `department` VALUES (1,'HSE','1'),(2,'Maintenance','1'),(3,'Environment','1'),(4,'Agricultural','1'),(5,'Internal','1'),(6,'Workshop','1'),(7,'SpecialOP','1'),(8,'Security','1'),(9,'pest','1'),(10,'customer service ','1'),(11,'sodic','1');
/*!40000 ALTER TABLE `department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(45) DEFAULT NULL,
  `project_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
INSERT INTO `project` VALUES (1,'Wes_town',1),(2,'Portal',1),(3,'Polygon',1),(4,'Allegria',1),(5,'Allegria_res',1),(6,'Polygon_x',1),(7,'Forty_west',1),(8,'SMD',1),(9,'6west Res',1),(10,'HUB',1),(11,'Pavlion',1),(12,'EDNC',1),(13,'east town ETR',1),(14,'SODIC east',1),(15,'villet',1),(16,'caesar',1),(17,'june',1),(18,'ogami',1),(19,'6west Comm',1);
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ptw`
--

DROP TABLE IF EXISTS `ptw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ptw` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `editor_name` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `work_location` varchar(255) DEFAULT NULL,
  `permit_number` varchar(255) NOT NULL,
  `subcontractor` varchar(255) DEFAULT NULL,
  `permit_date` date DEFAULT NULL,
  `work_description` varchar(45) DEFAULT NULL,
  `tools_equipment` varchar(45) DEFAULT NULL,
  `operation_type` varchar(225) DEFAULT NULL,
  `risk_assessment` varchar(255) DEFAULT NULL,
  `safety_measures` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `execution_manager_signature` varchar(255) DEFAULT NULL,
  `start_time` varchar(255) DEFAULT NULL,
  `end_time` varchar(255) DEFAULT NULL,
  `admin_signature` varchar(255) DEFAULT NULL,
  `safety_manager` varchar(255) DEFAULT NULL,
  `safety_signature` varchar(255) DEFAULT NULL,
  `work_status` int(11) DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `admin_signature_3` varchar(255) DEFAULT NULL,
  `safety_signature_3` varchar(255) DEFAULT NULL,
  `ptw_status` int(11) NOT NULL DEFAULT 0,
  `noncompliance_reason` text DEFAULT NULL,
  `noncompliance_date` date DEFAULT NULL,
  `noncompliance_time` time DEFAULT NULL,
  `noncompliance_officer` varchar(255) DEFAULT NULL,
  `corrective_actions` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ptw`
--

LOCK TABLES `ptw` WRITE;
/*!40000 ALTER TABLE `ptw` DISABLE KEYS */;
INSERT INTO `ptw` VALUES (1,'','','','','','PTW_001','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','','haitham','mohamed4',NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(2,'','','','','','PTW_002','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(3,'','','','','','PTW_003','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(4,'','','','','','PTW_004','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(5,'','','','','','PTW_005','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,3,'mmmmmmmm','0000-00-00','mmmmmm','',3,NULL,NULL,NULL,NULL,NULL),(6,'','','','','','PTW_005','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(7,'','','','','','PTW_005','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(8,'','','','','','PTW_006','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(9,'','','','','','PTW_006','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(10,'','','','','','PTW_007','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(11,'','','','','','PTW_008','','0000-00-00','العمل علي سقالة','سقوط من على ارتفاع','','16:00','',NULL,'','','','','me4','mohamed4',3,'testttt','2025-06-17','you8888','you3',3,NULL,NULL,NULL,NULL,NULL),(12,'','','','','','PTW_009','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(13,'','','','','','PTW_010','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','','haitham','haitham',3,'stopit','2025-06-16','mohamed','you 2',3,NULL,NULL,NULL,NULL,NULL),(14,'','','','','','PTW_011','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','','m','m',2,'','2025-06-16','mohamed','mohamed',2,NULL,NULL,NULL,NULL,NULL),(15,'','','','','','PTW_012','','0000-00-00','أعمال حفر','عمل في حفر','','16:00','','وضع شريط تحذير حول مكان العمل,وضع اقماع فسفورية حول مكان العمل','','','','','haitham','mohamed',2,'','2025-06-17','mo','you8888',2,NULL,NULL,NULL,NULL,NULL),(16,'','','','','','PTW_013','','0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,3,'Raining','2025-06-17','Gr','Uj',3,NULL,NULL,NULL,NULL,NULL),(17,'rashad ahmed','admin ','','','','PTW_014',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','','R','G',NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(18,'mohamed','developer','','','','PTW_015',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(19,'','','','','','PTW_016',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','','sssssssssss','sssssssssssssssssss',2,'','0000-00-00','yes','',2,NULL,NULL,NULL,NULL,NULL),(20,'','','','','','PTW_017',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','','ok','ok',2,'','0000-00-00','ok2','',2,NULL,NULL,NULL,NULL,NULL),(21,'','','','','','PTW_018',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(22,'','','','','','PTW_019',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(23,'','','','','','PTW_020',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,'kmew;dmv;l,','2025-07-23','13:43:52','l;,adsl;,','ls;;d;ds;z'),(24,'','','','','','PTW_021',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(25,'','','','','','PTW_022',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,'','','','','we','no',3,'kol','0000-00-00','kol','',3,NULL,NULL,NULL,NULL,NULL),(26,'','','','','','PTW_023',NULL,'0000-00-00','العمل على ارتفاع سبايدر',NULL,'','16:00','',NULL,NULL,'','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(27,'','','','','','PTW_024',NULL,'0000-00-00','أعمال لحام كهربي','أدخنة / غازات','','16:00','',NULL,NULL,'','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(28,'hasan','hasan','development','edara','edara','PTW_025',NULL,'2025-06-23','أعمال لحام كهربي','مخاطر كهربائية','08:00','16:00','ali','اختبار غازات,محاضرة توعية بالمخاطر',NULL,'height','safety line','mohamed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(29,'','','','','','PTW_026',NULL,'0000-00-00',NULL,NULL,'','16:00','',NULL,NULL,'','','','Mmm','Mmmmmmm',NULL,NULL,NULL,NULL,NULL,4,'kldslvnvlds','2025-07-23','11:31:27',';smdvk;mdsk','cmvkxmvkcm'),(30,'','','','','','PTW_027',NULL,'0000-00-00','','',NULL,NULL,NULL,NULL,'','','16:00','','iam','you are',3,'raining','0000-00-00','not that','',3,NULL,NULL,NULL,NULL,NULL),(31,'mohamed','developer','digital solution ','west region','polygon','PTW_028',NULL,'2025-06-24','electronic ','laptop','العمل علي سقالة','سقوط من على ارتفاع','عزل مصدر الطاقة,اختبار غازات,وسيلة اطفاء حريق مناسبة',NULL,'eslam','08:00','16:00','mohamed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,'mgd;lm','2025-07-31','15:34:20',';msd;m',';lmsld;m;vds'),(32,'','','','Allegria','','PTW_029',NULL,'0000-00-00','','',NULL,NULL,NULL,NULL,'','','16:00','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(33,'','','','HUB','','PTW_030',NULL,'0000-00-00','','',NULL,NULL,NULL,NULL,'','','16:00','','hema','amir',NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL),(34,'','','','SMD','','PTW_031',NULL,'0000-00-00','','','العمل بداخل الغرف المغلقة','عمل في مكان مغلق',NULL,NULL,'','','16:00','','haitham','mohamed4',3,'wind','0000-00-00','stoped','',3,NULL,NULL,NULL,NULL,NULL),(35,'','','','Wes_town','','PTW_032',NULL,'0000-00-00','','',NULL,NULL,NULL,NULL,'','','16:00','','ko','ko1',3,'wind','0000-00-00','key','',3,NULL,NULL,NULL,NULL,NULL),(36,'','','','6west','','PTW_033',NULL,'0000-00-00','','',NULL,NULL,NULL,NULL,'','','16:00','','mahmoud negm','islam',3,'yes2','0000-00-00','yes','',3,NULL,NULL,NULL,NULL,NULL),(37,'hasan','hasan','','6west','','PTW_034',NULL,'0000-00-00','','',NULL,NULL,NULL,NULL,'','','16:00','','done','done2',3,'high risk','0000-00-00','stopped','',3,NULL,NULL,NULL,NULL,NULL),(38,'','','','6west','','PTW_035',NULL,'0000-00-00','','',NULL,NULL,NULL,NULL,'','','16:00','','essa','essam',3,'stopped for not right','0000-00-00','sabz','',3,NULL,NULL,NULL,NULL,NULL),(39,'','','','6west','','PTW_036',NULL,'2025-06-30','','',NULL,NULL,NULL,NULL,'','','16:00','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL),(40,'','','','Allegria','','PTW_037',NULL,'2025-06-25','','',NULL,NULL,NULL,NULL,'','','16:00','','haitham','mohamed',NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL),(41,'','','','Forty_west','','PTW_038',NULL,'0000-00-00','','',NULL,NULL,NULL,NULL,'','','16:00','','muhhamed','essa',2,'','0000-00-00','essam','',2,NULL,NULL,NULL,NULL,NULL),(42,'mohamed','D.T supervisor','Digital solutions ','Portal','floor 4 ','PTW_039',NULL,'2025-07-07','asnaslknc','.mamcsm',NULL,NULL,NULL,'','shreif','08:00','16:00','mohamed','ok','ok1',3,'wind','0000-00-00','no','',3,NULL,NULL,NULL,NULL,NULL),(43,'mohamed','dt.senior','digital transformation','Allegria_res','sps','PTW_040',NULL,'2025-07-08','work at height ','ladder','العمل على السلم المفصلى','سقوط من على ارتفاع','الاشراف الدائم,تحليل مخاطر الوظيفية,وضع شريط تحذير حول مكان العمل,وضع اقماع فسفورية حول مكان العمل',NULL,'shreif','08:00','16:00','mohamed','ahmed ali','islam',3,'wind','0000-00-00','ahmad','',3,NULL,NULL,NULL,NULL,NULL),(44,'حسن','اداري ادارة البيئه','3','Allegria_res','عماره 5 الدور الثاني','PTW_041',NULL,'2025-07-09','اعمال تطهير للخزانات','مهمات وقايه ','العمل بداخل الغرف المغلقة','عمل في مكان مغلق','الاشراف الدائم,تحليل مخاطر الوظيفية,تقييم مخاطر,اختبار غازات,وضع شريط تحذير حول مكان العمل,وضع اقماع فسفورية حول مكان العمل,محاضرة توعية بالمخاطر,مهمات وقاية اضافية','ستار كلين','احمد علاء','08:00','16:00','حسن','haitham','haitham',NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL),(45,'asd','camkm','4','Portal','edara','PTW_042',NULL,'2025-07-09','dnajnca','lsacnlknalk','العمل على ارتفاع سبايدر','سقوط من على ارتفاع','اختبار غازات,وضع شريط تحذير حول مكان العمل,وضع علامة تحذيرية او علامات ارشادية',NULL,'lkanclknalcnl','08:00','16:00','klmcaskl','lkjhkj','jgfjh',3,'rt','0000-00-00','hjhgjgjh','',3,NULL,NULL,NULL,NULL,NULL),(46,'ascs','hjvhxsh','2','Portal','dslknvldn','PTW_043',NULL,'2025-07-09','dsfvf','kdsckl','أعمال لحام كهربي','مخاطر كهربائية','الاشراف الدائم,تحليل مخاطر الوظيفية,تقييم مخاطر',NULL,'dsvcdsv','08:00','16:00','.sdmkcm','hassan','mo essam',3,'توقف العمل بسبب الرياح','0000-00-00','mohamed','',3,NULL,NULL,NULL,NULL,NULL),(47,'mohamed','mko','3','Portal','edara','PTW_044',NULL,'2025-07-13','work7','toolsq','العمل على ارتفاع سبايدر','سقوط من على ارتفاع','اختبار غازات,وسيلة اطفاء حريق مناسبة,وضع شريط تحذير حول مكان العمل',NULL,'mo','08:00','16:00','moooooo','mkl','mkl',2,'','0000-00-00','mjk','',2,NULL,NULL,NULL,NULL,NULL),(48,'souad','developer','3','Allegria','sps','PTW_045',NULL,'2025-07-30','willding','face mask','أعمال لحام كهربي','مخاطر حريق','عزل مصدر الطاقة,اختبار غازات',NULL,'mohamed','10:00','16:00','soud','ahamd ali','moustafa',NULL,NULL,NULL,NULL,NULL,4,'not wearing ppe','2025-07-30','09:23:02','moustafa','wear the PPE');
/*!40000 ALTER TABLE `ptw` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ptw_history`
--

DROP TABLE IF EXISTS `ptw_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ptw_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permit_number` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `action_by` varchar(100) NOT NULL,
  `action_date` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ptw_history`
--

LOCK TABLES `ptw_history` WRITE;
/*!40000 ALTER TABLE `ptw_history` DISABLE KEYS */;
INSERT INTO `ptw_history` VALUES (1,'PTW_030','Marked as Non Compliance','mohamed','2025-07-23 12:25:17','Non-compliance reason: liwejlvldfk','2025-07-23 09:25:17'),(2,'PTW_026','Marked as Non Compliance','mohamed','2025-07-23 12:31:27','Non-compliance reason: kldslvnvlds','2025-07-23 09:31:27'),(3,'PTW_020','Marked as Non Compliance','mohamed','2025-07-23 14:43:52','Non-compliance reason: kmew;dmv;l,','2025-07-23 11:43:52'),(4,'PTW_045','Marked as Non Compliance','mohamed','2025-07-30 10:23:02','Non-compliance reason: not wearing ppe','2025-07-30 07:23:02'),(5,'PTW_028','Marked as Non Compliance','mohamed','2025-07-31 16:34:20','Non-compliance reason: mgd;lm','2025-07-31 13:34:20');
/*!40000 ALTER TABLE `ptw_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ptw_images`
--

DROP TABLE IF EXISTS `ptw_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ptw_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permit_number` varchar(50) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` varchar(50) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ptw_images`
--

LOCK TABLES `ptw_images` WRITE;
/*!40000 ALTER TABLE `ptw_images` DISABLE KEYS */;
INSERT INTO `ptw_images` VALUES (1,'PTW_030','1753262717_noncompliance_pexels-alexasfotos-2220336.jpg','noncompliance','2025-07-23 09:25:17'),(2,'PTW_026','1753263087_noncompliance_images (3).jpg','noncompliance','2025-07-23 09:31:27'),(3,'PTW_020','1753271032_noncompliance_images (4).jpg','noncompliance','2025-07-23 11:43:52'),(4,'PTW_045','1753860182_noncompliance_pexels-alexasfotos-2220336.jpg','noncompliance','2025-07-30 07:23:02'),(5,'PTW_028','1753968860_noncompliance_pexels-velroy-10786372.jpg','noncompliance','2025-07-31 13:34:20');
/*!40000 ALTER TABLE `ptw_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` int(11) NOT NULL,
  `page` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_permission` (`user_id`),
  CONSTRAINT `fk_user_permission` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1,'*','*',NULL),(2,2,'dailyreport.php','submit',NULL),(3,2,'ptw_overview.php','edit',NULL),(4,2,'dailyreport_overview.php','close_observation',NULL),(5,2,'dailyreport.php','submit',NULL),(6,3,'ptw.php','submit',NULL),(7,3,'finish_ptw.php','submit',NULL),(8,3,'ptw_overview.php','finish',NULL),(9,2,'dailyreport_overview.php','',NULL),(16,1,'dailyreport.php','view',NULL),(17,1,'dailyreport_overview.php','view',NULL),(24,2,'dailyreport.php','view',NULL),(25,2,'dailyreport_overview.php','view',NULL),(26,1,'dailyreport.php','view',NULL),(27,1,'dailyreport_overview.php','view',NULL),(28,1,'ptw.php','view',NULL),(29,1,'ptw_overview.php','view',NULL),(36,1,'dailyreport.php','view',NULL),(37,2,'dailyreport.php','view',NULL),(38,1,'dailyreport.ph','submit',NULL),(39,2,'dailyreport.ph','submit',NULL),(40,1,'dailyreport_overview.php','view',NULL),(41,2,'dailyreport_overview.php','view',NULL),(42,1,'dailyreport_overview.php','submit',NULL),(43,2,'dailyreport_overview.php','submit',NULL),(44,1,'ptw.php','submit',NULL),(45,3,'ptw.php','submit',NULL),(46,1,'ptw_overview.php','view',NULL),(47,2,'ptw_overview.php','view',NULL),(48,3,'ptw_overview.php','view',NULL),(49,1,'ptw_overview.php','edit',NULL),(50,2,'ptw_overview.php','edit',NULL),(51,1,'ptw_overview.php','finish',NULL),(52,3,'ptw_overview.php','finish',NULL),(64,1,'ptw.php','view',NULL),(65,3,'ptw.php','view',NULL),(91,0,'ptw.php','submit',NULL),(92,0,'ptw.php','view',NULL),(93,0,'ptw_overview.php','finish',NULL),(94,0,'ptw_overview.php','view',NULL),(158,1,'dailyreport_overview.php','export',1),(159,2,'dailyreport_overview.php','export',2),(160,1,'dailyreport_analysis.php','view',1),(161,2,'dailyreport_analysis.php','view',2),(162,1,'ptw_analysis.php','view',1),(163,2,'ptw_analysis.php','view',2),(164,2,'dailyreport.php','submit',51),(165,2,'dailyreport.php','view',51),(166,2,'dailyreport_overview.php','submit',51),(167,2,'dailyreport_overview.php','view',51),(168,2,'ptw_overview.php','edit',51),(169,2,'ptw_overview.php','view',51),(170,2,'dailyreport.php','submit',52),(171,2,'dailyreport.php','view',52),(172,2,'dailyreport_overview.php','submit',52),(173,2,'dailyreport_overview.php','view',52),(174,2,'ptw_overview.php','edit',52),(175,2,'ptw_overview.php','view',52),(176,1,'ptw_overview.php','export',1),(177,2,'dailyreport.php','submit',53),(178,2,'dailyreport.php','view',53),(179,2,'dailyreport_overview.php','submit',53),(180,2,'dailyreport_overview.php','view',53),(181,2,'ptw_overview.php','edit',53),(182,2,'ptw_overview.php','view',53),(183,2,'dailyreport_analysis.php','view',53),(184,2,'dailyreport_overview.php','export',53),(185,2,'ptw.php','submit',53),(186,2,'ptw.php','view',53),(187,2,'ptw_analysis.php','view',53),(188,2,'ptw_overview.php','finish',53);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_type`
--

DROP TABLE IF EXISTS `role_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_type`
--

LOCK TABLES `role_type` WRITE;
/*!40000 ALTER TABLE `role_type` DISABLE KEYS */;
INSERT INTO `role_type` VALUES (1,'dailyreport.php','view','Access the daily report form'),(2,'dailyreport.php','submit','Submit a new daily report'),(3,'dailyreport_overview.php','view','View all submitted daily reports'),(4,'dailyreport_overview.php','submit','Close a daily report observation'),(5,'ptw.php','view','Access the PTW form'),(6,'ptw.php','submit','Submit a new PTW'),(7,'ptw_overview.php','view','View PTW submissions'),(8,'ptw_overview.php','edit','Edit submitted PTWs'),(9,'ptw_overview.php','finish','Finish a PTW'),(10,'user.php','view','View all system users'),(11,'add_user.php','create','Add a new user to the system'),(12,'dailyreport_overview.php','export','Export to excel sheet '),(13,'dailyreport_analysis.php','view','view analysis'),(14,'ptw_analysis.php','view','view analysis'),(15,'ptw_overview.php','export','export pdf sheet ');
/*!40000 ALTER TABLE `role_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_type_permissions`
--

DROP TABLE IF EXISTS `role_type_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_type_permissions` (
  `role_type` int(11) NOT NULL,
  `page` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  PRIMARY KEY (`role_type`,`page`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_type_permissions`
--

LOCK TABLES `role_type_permissions` WRITE;
/*!40000 ALTER TABLE `role_type_permissions` DISABLE KEYS */;
INSERT INTO `role_type_permissions` VALUES (1,'add_user.php','create'),(1,'add_user.php','view'),(1,'dailyreport.php','submit'),(1,'dailyreport.php','view'),(1,'dailyreport_overview.php','submit'),(1,'dailyreport_overview.php','view'),(1,'ptw.php','submit'),(1,'ptw.php','view'),(1,'ptw_overview.php','edit'),(1,'ptw_overview.php','export'),(1,'ptw_overview.php','finish'),(1,'ptw_overview.php','view'),(1,'user.php','view'),(2,'dailyreport.php','submit'),(2,'dailyreport.php','view'),(2,'dailyreport_overview.php','submit'),(2,'dailyreport_overview.php','view'),(2,'ptw_overview.php','edit'),(2,'ptw_overview.php','view'),(3,'ptw.php','submit'),(3,'ptw.php','view'),(3,'ptw_overview.php','finish'),(3,'ptw_overview.php','view');
/*!40000 ALTER TABLE `role_type_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `user_type` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `user_status` varchar(45) DEFAULT NULL,
  `editor_name` varchar(45) DEFAULT NULL,
  `job_title` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'mohamed','123',1,1,'1','mohamed','developer'),(2,'ahmad aly','321',2,1,'1','ahmad aly','HSE manager '),(3,'mahmoud negm','147',2,1,'1','mahmoud negm','HSE senior manager '),(4,'ehab','1234',2,1,'1','ehab ','HSE section head '),(5,'islam','456',2,1,'1','islam','HSE supervisor '),(6,'abdelrhman ','789',2,1,'1','abd-elrhman','HSE supervisor '),(7,'osama','369',2,1,'1','osama esmat','HSE supervisor '),(8,'moustafa ','357',2,1,'1','moustafa','HSE supervisor'),(9,'maint-R','159',3,2,'1','maintenance-R','admin '),(10,'maint-C','987',3,2,'1','maintenance-C','admin'),(11,'Agricultural','agri',3,4,'1','Agricultural','admin'),(12,'pestcontrol','pest123',3,9,'1','pestcontrol','admin'),(13,'environment ','envi123',3,3,'1','environment','admin'),(14,'security','sec456',3,8,'1','security','admin'),(15,'workshop','work789',3,6,'1','workshop','admin'),(16,'inter','inter147',3,5,'1','internal','admin'),(17,'spop','spop963',3,7,'1','specialoperation','admin'),(51,'ahmad hamdy','mdc123',2,1,'1','ahmad hamdy','HSE supervisor '),(52,'ayman','asd456',2,1,'1','ayman','HSE supervisor '),(53,'soad','mmm',2,2,'1','soad','training ');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-03 10:12:52
