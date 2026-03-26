import '../../data/models/user_model.dart';
import '../../data/data_sources/auth_remote_data_source.dart';

abstract class AuthRepository {
  Future<UserModel> login(String username, String password);
}

class AuthRepositoryImpl implements AuthRepository {
  final AuthRemoteDataSource remoteDataSource;

  AuthRepositoryImpl({required this.remoteDataSource});

  @override
  Future<UserModel> login(String username, String password) {
    return remoteDataSource.login(username, password);
  }
}
