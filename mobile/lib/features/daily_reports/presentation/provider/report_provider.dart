import 'package:flutter/material.dart';
import '../../domain/repositories/report_repository.dart';
import '../../data/models/report_model.dart';

class ReportProvider extends ChangeNotifier {
  final ReportRepository reportRepository;

  List<ReportModel> _reports = [];
  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  bool _hasMore = true;

  List<ReportModel> get reports => _reports;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasMore => _hasMore;

  ReportProvider({required this.reportRepository});

  Future<void> fetchReports({bool refresh = false}) async {
    if (refresh) {
      _reports = [];
      _currentPage = 1;
      _hasMore = true;
    }

    if (!_hasMore || _isLoading) return;

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final newReports = await reportRepository.getReports(page: _currentPage);
      if (newReports.isEmpty) {
        _hasMore = false;
      } else {
        _reports.addAll(newReports);
        _currentPage++;
      }
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<Map<String, dynamic>> fetchFormOptions() async {
    try {
      return await reportRepository.getFormOptions();
    } catch (e) {
      return {'projects': [], 'departments': [], 'observations': []};
    }
  }

  Future<bool> submitReport(Map<String, dynamic> reportData) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await reportRepository.submitReport(reportData);
      _isLoading = false;
      if (success) {
        await fetchReports(refresh: true);
      }
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
