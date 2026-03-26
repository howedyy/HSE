import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'core/network/api_client.dart';
import 'features/auth/presentation/provider/auth_provider.dart';
import 'features/auth/domain/repositories/auth_repository.dart';
import 'features/auth/data/data_sources/auth_remote_data_source.dart';
import 'features/auth/presentation/pages/login_page.dart';
import 'features/dashboard/presentation/pages/dashboard_page.dart';

void main() {
  final dio = Dio();
  final apiClient = ApiClient(dio: dio);
  final authDataSource = AuthRemoteDataSourceImpl(apiClient: apiClient);
  final authRepository = AuthRepositoryImpl(remoteDataSource: authDataSource);

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => AuthProvider(authRepository: authRepository),
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
        primarySwatch: Colors.blue,
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
