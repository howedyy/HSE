import 'package:flutter/material.dart';
import '../../domain/repositories/dashboard_repository.dart';
import '../../data/models/dashboard_stats_model.dart';

class DashboardProvider extends ChangeNotifier {
  final DashboardRepository repository;
  
  DashboardStatsModel? _stats;
  bool _isLoading = false;
  String? _error;

  DashboardStatsModel? get stats => _stats;
  bool get isLoading => _isLoading;
  String? get error => _error;

  DashboardProvider({required this.repository});

  Future<void> fetchStats() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _stats = await repository.getStats();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }
}
