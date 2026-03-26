import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

// Core
import 'core/network/api_client.dart';

// Auth
import 'features/auth/presentation/provider/auth_provider.dart';
import 'features/auth/domain/repositories/auth_repository.dart';
import 'features/auth/data/data_sources/auth_remote_data_source.dart';
import 'features/auth/presentation/pages/login_page.dart';

// Dashboard
import 'features/dashboard/presentation/provider/dashboard_provider.dart';
import 'features/dashboard/domain/repositories/dashboard_repository.dart';
import 'features/dashboard/data/data_sources/dashboard_remote_data_source.dart';
import 'features/dashboard/presentation/pages/dashboard_page.dart';

// PTW
import 'features/ptw/presentation/provider/ptw_provider.dart';
import 'features/ptw/domain/repositories/ptw_repository.dart';
import 'features/ptw/data/data_sources/ptw_remote_data_source.dart';

void main() {
  final dio = Dio();
  final apiClient = ApiClient(dio: dio);

  // Auth Injection
  final authDataSource = AuthRemoteDataSourceImpl(apiClient: apiClient);
  final authRepository = AuthRepositoryImpl(remoteDataSource: authDataSource);

  // Dashboard Injection
  final dashboardDataSource = DashboardRemoteDataSourceImpl(apiClient: apiClient);
  final dashboardRepository = DashboardRepositoryImpl(remoteDataSource: dashboardDataSource);

  // PTW Injection
  final ptwDataSource = PtwRemoteDataSourceImpl(apiClient: apiClient);
  final ptwRepository = PtwRepositoryImpl(remoteDataSource: ptwDataSource);

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => AuthProvider(authRepository: authRepository),
        ),
        ChangeNotifierProvider(
          create: (_) => DashboardProvider(repository: dashboardRepository),
        ),
        ChangeNotifierProvider(
          create: (_) => PtwProvider(ptwRepository: ptwRepository),
        ),
      ],
      child: const HSEApp(),
    ),
  );
}

class HSEApp extends StatelessWidget {
  const HSEApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Edara HSE',
      theme: ThemeData(
        useMaterial3: true,
        brightness: Brightness.dark,
        primaryColor: Colors.blueAccent,
        scaffoldBackgroundColor: const Color(0xFF0F172A),
        colorScheme: const ColorScheme.dark(
          primary: Colors.blueAccent,
          secondary: Colors.cyanAccent,
          surface: Color(0xFF1E293B),
        ),
      ),
      home: const AuthWrapper(),
    );
  }
}

class AuthWrapper extends StatelessWidget {
  const AuthWrapper({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);

    if (authProvider.isAuthenticated) {
      return const DashboardPage();
    } else {
      return const LoginPage();
    }
  }
}
