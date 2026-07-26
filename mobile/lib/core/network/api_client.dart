import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiClient {
  final Dio dio;
  
  // Default to Android Studio Emulator loopback address with port 8081
  static const String defaultBaseUrl = 'http://10.0.2.2:8081/Edara-HSE111/api/'; 
  static String currentBaseUrl = defaultBaseUrl;

  static Future<String> getSavedBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('custom_base_url') ?? defaultBaseUrl;
  }

  static Future<void> initBaseUrl(Dio dio) async {
    final prefs = await SharedPreferences.getInstance();
    final savedUrl = prefs.getString('custom_base_url');
    if (savedUrl != null && savedUrl.isNotEmpty) {
      currentBaseUrl = savedUrl;
    } else {
      currentBaseUrl = defaultBaseUrl;
    }
    dio.options.baseUrl = currentBaseUrl;
  }

  static Future<void> saveBaseUrl(String newUrl) async {
    String formattedUrl = newUrl.trim();
    if (!formattedUrl.endsWith('/')) {
      formattedUrl += '/';
    }
    currentBaseUrl = formattedUrl;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('custom_base_url', formattedUrl);
  }

  ApiClient({required this.dio}) {
    dio.options.baseUrl = currentBaseUrl;
    dio.options.connectTimeout = const Duration(seconds: 10);
    dio.options.receiveTimeout = const Duration(seconds: 10);
    
    // Add logging for debugging
    dio.interceptors.add(LogInterceptor(
      requestBody: true,
      responseBody: true,
      logPrint: (obj) => debugPrint(obj.toString()),
    ));
    
    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final prefs = await SharedPreferences.getInstance();
        final savedUrl = prefs.getString('custom_base_url');
        if (savedUrl != null && savedUrl.isNotEmpty) {
          currentBaseUrl = savedUrl;
          options.baseUrl = savedUrl;
        } else {
          options.baseUrl = currentBaseUrl;
        }

        final sessionId = prefs.getString('PHPSESSID');
        if (sessionId != null) {
          options.headers['Cookie'] = 'PHPSESSID=$sessionId';
        }
        return handler.next(options);
      },
      onResponse: (response, handler) async {
        // If the login returns a token, we store it
        if (response.data is Map && response.data['token'] != null) {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('PHPSESSID', response.data['token']);
        }
        return handler.next(response);
      },
    ));
  }
}
