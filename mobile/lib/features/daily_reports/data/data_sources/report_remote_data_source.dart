import '../../../../core/network/api_client.dart';
import '../models/report_model.dart';

abstract class ReportRemoteDataSource {
  Future<List<ReportModel>> getReports({int page = 1, int limit = 20});
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
}
