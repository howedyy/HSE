class PtwModel {
  final int id;
  final String permitNumber;
  final String permitDate;
  final String editorName;
  final String jobTitle;
  final String projectName;
  final String departmentName;
  final String operationType;
  final int ptwStatus;
  final String workLocation;
  final String workDescription;

  PtwModel({
    required this.id,
    required this.permitNumber,
    required this.permitDate,
    required this.editorName,
    required this.jobTitle,
    required this.projectName,
    required this.departmentName,
    required this.operationType,
    required this.ptwStatus,
    required this.workLocation,
    required this.workDescription,
  });

  factory PtwModel.fromJson(Map<String, dynamic> json) {
    return PtwModel(
      id: json['id'] ?? 0,
      permitNumber: json['permit_number'] ?? '',
      permitDate: json['permit_date'] ?? '',
      editorName: json['editor_name'] ?? '',
      jobTitle: json['job_title'] ?? '',
      projectName: json['project_name'] ?? '',
      departmentName: json['department_name'] ?? '',
      operationType: json['operation_type'] ?? '',
      ptwStatus: json['ptw_status'] ?? 0,
      workLocation: json['work_location'] ?? '',
      workDescription: json['work_description'] ?? '',
    );
  }

  String get statusText {
    switch (ptwStatus) {
      case 1: return 'Approved';
      case 2: return 'Pending';
      case 3: return 'Rejected';
      case 4: return 'Closed';
      default: return 'Unknown';
    }
  }
}
