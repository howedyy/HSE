import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../domain/repositories/auth_repository.dart';
import '../../data/models/user_model.dart';

class AuthProvider extends ChangeNotifier {
  final AuthRepository authRepository;
  UserModel? _user;
  bool _isLoading = false;
  String? _error;

  UserModel? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _user != null;

  AuthProvider({required this.authRepository}) {
    _loadUserFromPrefs();
  }

  Future<void> _loadUserFromPrefs() async {
    // Basic logic to check if a session exists
    final prefs = await SharedPreferences.getInstance();
    final hasSession = prefs.containsKey('PHPSESSID');
    if (hasSession) {
      // In a real app, we'd call /auth/me.php here to verify
      // For now, we'll assume valid if session exists or wait for manual login
    }
  }

  Future<bool> login(String username, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _user = await authRepository.login(username, password);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('PHPSESSID');
    _user = null;
    notifyListeners();
  }
}
