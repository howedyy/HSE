class UserModel {
  final int id;
  final String username;
  final int role;
  final String editorName;
  final String jobTitle;

  UserModel({
    required this.id,
    required this.username,
    required this.role,
    required this.editorName,
    required this.jobTitle,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] ?? 0,
      username: json['username'] ?? '',
      role: json['role'] ?? 0,
      editorName: json['editorName'] ?? '',
      jobTitle: json['jobTitle'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'username': username,
      'role': role,
      'editorName': editorName,
      'jobTitle': jobTitle,
    };
  }
}
