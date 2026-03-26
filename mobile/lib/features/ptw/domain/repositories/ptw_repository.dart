import '../../data/data_sources/ptw_remote_data_source.dart';
import '../../data/models/ptw_model.dart';

abstract class PtwRepository {
  Future<List<PtwModel>> getPermits({int page = 1});
}

class PtwRepositoryImpl implements PtwRepository {
  final PtwRemoteDataSource remoteDataSource;

  PtwRepositoryImpl({required this.remoteDataSource});

  @override
  Future<List<PtwModel>> getPermits({int page = 1}) {
    return remoteDataSource.getPermits(page: page);
  }
}
