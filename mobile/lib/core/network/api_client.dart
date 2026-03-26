import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiClient {
  final Dio dio;
  
  // NOTE: Change this to your computer's IP address if testing on a physical device.
  // Use '10.0.2.2' for Android Emulator.
  // Use 'localhost' for Web or Windows.
  static const String baseUrl = 'http://10.0.2.2/Edara-HSE111/api/'; 

  ApiClient({required this.dio}) {
    dio.options.baseUrl = baseUrl;
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
