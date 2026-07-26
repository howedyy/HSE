import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../provider/report_provider.dart';
import '../widgets/report_card.dart';
import 'create_report_page.dart';

class DailyReportsPage extends StatefulWidget {
  const DailyReportsPage({super.key});

  @override
  State<DailyReportsPage> createState() => _DailyReportsPageState();
}

class _DailyReportsPageState extends State<DailyReportsPage> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (!mounted) return;
      Provider.of<ReportProvider>(context, listen: false).fetchReports();
    });
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      Provider.of<ReportProvider>(context, listen: false).fetchReports();
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        title: Text('Daily Reports',
            style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add_circle_outline, color: Colors.blueAccent),
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const CreateReportPage()),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => Provider.of<ReportProvider>(context, listen: false)
                .fetchReports(refresh: true),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const CreateReportPage()),
        ),
        backgroundColor: Colors.blueAccent,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: Text('New Report', style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
      ),
      body: Consumer<ReportProvider>(
        builder: (context, provider, _) {
          // Initial loading
          if (provider.isLoading && provider.reports.isEmpty) {
            return const Center(
              child: CircularProgressIndicator(color: Colors.blueAccent),
            );
          }

          // Error state (no data loaded yet)
          if (provider.error != null && provider.reports.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.wifi_off_rounded,
                      size: 56, color: Colors.redAccent),
                  const SizedBox(height: 16),
                  Text('Could not load reports',
                      style: GoogleFonts.inter(color: Colors.white70)),
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: () => provider.fetchReports(refresh: true),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          // Empty state
          if (provider.reports.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.inbox_rounded,
                      size: 56, color: Colors.white24),
                  const SizedBox(height: 16),
                  Text('No reports found',
                      style: GoogleFonts.inter(color: Colors.white38)),
                ],
              ),
            );
          }

          // Populated list with pagination
          return RefreshIndicator(
            color: Colors.blueAccent,
            onRefresh: () => provider.fetchReports(refresh: true),
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount:
                  provider.reports.length + (provider.hasMore ? 1 : 0),
              itemBuilder: (context, index) {
                if (index == provider.reports.length) {
                  return const Padding(
                    padding: EdgeInsets.symmetric(vertical: 20),
                    child: Center(
                        child: CircularProgressIndicator(
                            color: Colors.blueAccent)),
                  );
                }
                return ReportCard(report: provider.reports[index]);
              },
            ),
          );
        },
      ),
    );
  }
}
