import '../../data/data_sources/report_remote_data_source.dart';
import '../../data/models/report_model.dart';

abstract class ReportRepository {
  Future<List<ReportModel>> getReports({int page = 1});
  Future<Map<String, dynamic>> getFormOptions();
  Future<bool> submitReport(Map<String, dynamic> reportData);
}

class ReportRepositoryImpl implements ReportRepository {
  final ReportRemoteDataSource remoteDataSource;

  ReportRepositoryImpl({required this.remoteDataSource});

  @override
  Future<List<ReportModel>> getReports({int page = 1}) {
    return remoteDataSource.getReports(page: page);
  }

  @override
  Future<Map<String, dynamic>> getFormOptions() {
    return remoteDataSource.getFormOptions();
  }

  @override
  Future<bool> submitReport(Map<String, dynamic> reportData) {
    return remoteDataSource.submitReport(reportData);
  }
}
