class DashboardStatsModel {
  final int totalPtw;
  final int pendingPtw;
  final int completedPtw;
  final int activePtw;
  final int todayReports;
  final int taskCompletion;
  final List<MonthlyTrend> monthlyTrend;

  DashboardStatsModel({
    required this.totalPtw,
    required this.pendingPtw,
    required this.completedPtw,
    required this.activePtw,
    required this.todayReports,
    required this.taskCompletion,
    required this.monthlyTrend,
  });

  factory DashboardStatsModel.fromJson(Map<String, dynamic> json) {
    final stats = json['stats'];
    final trend = (json['monthlyTrend'] as List)
        .map((e) => MonthlyTrend.fromJson(e))
        .toList();

    return DashboardStatsModel(
      totalPtw: stats['total_ptw'] ?? 0,
      pendingPtw: stats['pending_ptw'] ?? 0,
      completedPtw: stats['completed_ptw'] ?? 0,
      activePtw: stats['active_ptw'] ?? 0,
      todayReports: stats['today_reports'] ?? 0,
      taskCompletion: (json['taskCompletion'] as num?)?.toInt() ?? 0,
      monthlyTrend: trend,
    );
  }
}

class MonthlyTrend {
  final String month;
  final int count;

  MonthlyTrend({required this.month, required this.count});

  factory MonthlyTrend.fromJson(Map<String, dynamic> json) {
    return MonthlyTrend(
      month: json['month'] ?? '',
      count: json['count'] ?? 0,
    );
  }
}
