import 'package:flutter/material.dart';
import '../../domain/repositories/ptw_repository.dart';
import '../../data/models/ptw_model.dart';

class PtwProvider extends ChangeNotifier {
  final PtwRepository ptwRepository;
  
  List<PtwModel> _permits = [];
  bool _isLoading = false;
  String? _error;

  List<PtwModel> get permits => _permits;
  bool get isLoading => _isLoading;
  String? get error => _error;

  PtwProvider({required this.ptwRepository});

  Future<void> fetchPermits({bool refresh = false}) async {
    if (refresh) _permits = [];
    
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _permits = await ptwRepository.getPermits();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }
}
