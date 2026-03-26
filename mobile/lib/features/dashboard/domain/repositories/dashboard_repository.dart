import '../../data/data_sources/dashboard_remote_data_source.dart';
import '../../data/models/dashboard_stats_model.dart';

abstract class DashboardRepository {
  Future<DashboardStatsModel> getStats();
}

class DashboardRepositoryImpl implements DashboardRepository {
  final DashboardRemoteDataSource remoteDataSource;

  DashboardRepositoryImpl({required this.remoteDataSource});

  @override
  Future<DashboardStatsModel> getStats() {
    return remoteDataSource.getStats();
  }
}
