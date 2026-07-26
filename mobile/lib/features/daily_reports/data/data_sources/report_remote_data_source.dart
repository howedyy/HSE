import '../../../../core/network/api_client.dart';
import '../models/report_model.dart';

abstract class ReportRemoteDataSource {
  Future<List<ReportModel>> getReports({int page = 1, int limit = 20});
  Future<Map<String, dynamic>> getFormOptions();
  Future<bool> submitReport(Map<String, dynamic> reportData);
}

class ReportRemoteDataSourceImpl implements ReportRemoteDataSource {
  final ApiClient apiClient;

  ReportRemoteDataSourceImpl({required this.apiClient});

  @override
  Future<List<ReportModel>> getReports({int page = 1, int limit = 20}) async {
    try {
      final response = await apiClient.dio.get(
        'reports/list.php',
        queryParameters: {'page': page, 'limit': limit},
      );

      if (response.statusCode == 200) {
        final List<dynamic> data = response.data['data'];
        return data.map((e) => ReportModel.fromJson(e)).toList();
      }
      throw Exception('Failed to load reports');
    } catch (e) {
      throw Exception('Error fetching reports: $e');
    }
  }

  @override
  Future<Map<String, dynamic>> getFormOptions() async {
    try {
      final lookupRes = await apiClient.dio.get('lookup/options.php');
      final obsRes = await apiClient.dio.get('report_options/list.php');

      Map<String, dynamic> projectsAndDepts = {};
      if (lookupRes.statusCode == 200 && lookupRes.data is Map) {
        projectsAndDepts = Map<String, dynamic>.from(lookupRes.data);
      }

      List<dynamic> observations = [];
      if (obsRes.statusCode == 200 && obsRes.data != null && obsRes.data is Map) {
        observations = obsRes.data['data'] ?? [];
      }

      return {
        'projects': projectsAndDepts['projects'] ?? [],
        'departments': projectsAndDepts['departments'] ?? [],
        'observations': observations,
      };
    } catch (e) {
      throw Exception('Error fetching form options: $e');
    }
  }

  @override
  Future<bool> submitReport(Map<String, dynamic> reportData) async {
    try {
      final response = await apiClient.dio.post(
        'reports/submit.php',
        data: reportData,
      );

      if (response.statusCode == 200 && response.data is Map) {
        return response.data['success'] == true;
      }
      return false;
    } catch (e) {
      throw Exception('Error submitting report: $e');
    }
  }
}
