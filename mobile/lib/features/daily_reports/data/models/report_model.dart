class ReportModel {
  final int id;
  final String date;
  final String projectName;
  final String departmentName;
  final String workType;
  final String risk;
  final String observationDescription;
  final String description;
  final int reportStatus;
  final String? closedAt;
  final String createdBy;
  final String? closedByUsername;
  final String? imageUpload;
  final String? closureNotes;

  ReportModel({
    required this.id,
    required this.date,
    required this.projectName,
    required this.departmentName,
    required this.workType,
    required this.risk,
    required this.observationDescription,
    required this.description,
    required this.reportStatus,
    this.closedAt,
    required this.createdBy,
    this.closedByUsername,
    this.imageUpload,
    this.closureNotes,
  });

  factory ReportModel.fromJson(Map<String, dynamic> json) {
    return ReportModel(
      id: json['id'] ?? 0,
      date: json['date'] ?? '',
      projectName: json['project_name'] ?? '',
      departmentName: json['department_name'] ?? '',
      workType: json['work_type'] ?? '',
      risk: json['risk'] ?? '',
      observationDescription: json['observation_description'] ?? '',
      description: json['description'] ?? '',
      reportStatus: json['report_status'] ?? 0,
      closedAt: json['closed_at'],
      createdBy: json['created_by'] ?? '',
      closedByUsername: json['closed_by_username'],
      imageUpload: json['image_upload'],
      closureNotes: json['closure_notes'],
    );
  }

  /// UI label: 'Closed' or 'Open'
  String get statusText => reportStatus == 1 ? 'Closed' : 'Open';
  bool get isClosed => reportStatus == 1;
  bool get isOpen => reportStatus == 0;

  /// Returns a normalised risk key for colour mapping: 'high' | 'medium' | 'low' | 'unknown'
  String get riskLevel {
    final r = risk.toLowerCase();
    if (r.contains('high') || r.contains('عالية')) return 'high';
    if (r.contains('medium') || r.contains('متوسط')) return 'medium';
    if (r.contains('low') || r.contains('منخفض')) return 'low';
    return 'unknown';
  }
}
