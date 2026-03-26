import '../../../../core/network/api_client.dart';
import '../models/ptw_model.dart';

abstract class PtwRemoteDataSource {
  Future<List<PtwModel>> getPermits({int page = 1, int limit = 20});
}

class PtwRemoteDataSourceImpl implements PtwRemoteDataSource {
  final ApiClient apiClient;

  PtwRemoteDataSourceImpl({required this.apiClient});

  @override
  Future<List<PtwModel>> getPermits({int page = 1, int limit = 20}) async {
    try {
      final response = await apiClient.dio.get(
        'ptw/list.php',
        queryParameters: {
          'page': page,
          'limit': limit,
        },
      );

      if (response.statusCode == 200) {
        final List<dynamic> data = response.data['data'];
        return data.map((item) => PtwModel.fromJson(item)).toList();
      } else {
        throw Exception('Failed to load permits');
      }
    } catch (e) {
      throw Exception('Error fetching permits: $e');
    }
  }
}
