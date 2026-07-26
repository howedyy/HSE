import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../data/models/report_model.dart';

class ReportCard extends StatelessWidget {
  final ReportModel report;
  const ReportCard({super.key, required this.report});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white10),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => _showReportDetails(context),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
          // ── Header ──
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
            child: Row(
              children: [
                _RiskDot(level: report.riskLevel),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    report.observationDescription.isNotEmpty
                        ? report.observationDescription
                        : 'Daily Report #${report.id}',
                    style: GoogleFonts.inter(
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                      fontSize: 14,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 8),
                _StatusBadge(isClosed: report.isClosed),
              ],
            ),
          ),
          const Divider(height: 1, color: Colors.white10),

          // ── Body ──
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [
                    if (report.projectName.isNotEmpty)
                      _InfoChip(
                          icon: Icons.business, label: report.projectName),
                    if (report.departmentName.isNotEmpty)
                      _InfoChip(
                          icon: Icons.apartment,
                          label: report.departmentName),
                  ],
                ),
                if (report.description.isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Text(
                    report.description,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        color: Colors.white60, fontSize: 13),
                  ),
                ],
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      report.date.length >= 10
                          ? report.date.substring(0, 10)
                          : report.date,
                      style: const TextStyle(
                          color: Colors.white38, fontSize: 12),
                    ),
                    Text(
                      'By ${report.createdBy}',
                      style: const TextStyle(
                          color: Colors.white38, fontSize: 12),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    ),
  ),
);
}

  void _showReportDetails(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: const Color(0xFF0F172A),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.95,
        minChildSize: 0.4,
        expand: false,
        builder: (_, scrollController) => SingleChildScrollView(
          controller: scrollController,
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Drag handle
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.white24,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              // Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      _RiskDot(level: report.riskLevel),
                      const SizedBox(width: 8),
                      Text(
                        'Risk: ${report.risk.isNotEmpty ? report.risk : report.riskLevel.toUpperCase()}',
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.w600,
                          color: Colors.white70,
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ),
                  _StatusBadge(isClosed: report.isClosed),
                ],
              ),
              const SizedBox(height: 16),
              // Title
              Text(
                report.observationDescription.isNotEmpty
                    ? report.observationDescription
                    : 'Daily Report #${report.id}',
                style: GoogleFonts.outfit(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Report ID #${report.id} • ${report.date}',
                style: GoogleFonts.inter(
                  fontSize: 13,
                  color: const Color(0xFF94A3B8),
                ),
              ),
              const Divider(height: 32, color: Colors.white10),
              // Metadata grid
              Wrap(
                spacing: 12,
                runSpacing: 12,
                children: [
                  if (report.projectName.isNotEmpty)
                    _DetailBadge(label: 'Project', value: report.projectName, icon: Icons.business),
                  if (report.departmentName.isNotEmpty)
                    _DetailBadge(label: 'Department', value: report.departmentName, icon: Icons.apartment),
                  if (report.workType.isNotEmpty)
                    _DetailBadge(label: 'Work Type', value: report.workType, icon: Icons.work_outline),
                ],
              ),
              const SizedBox(height: 24),
              // Full Description
              Text(
                'Observation Details',
                style: GoogleFonts.outfit(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.white10),
                ),
                child: Text(
                  report.description.isNotEmpty
                      ? report.description
                      : 'No additional description provided.',
                  style: GoogleFonts.inter(color: Colors.white70, fontSize: 14, height: 1.4),
                ),
              ),
              const SizedBox(height: 24),
              // Created by info
              Row(
                children: [
                  const Icon(Icons.person_outline, size: 16, color: Colors.blueAccent),
                  const SizedBox(width: 8),
                  Text(
                    'Created by ${report.createdBy} on ${report.date}',
                    style: GoogleFonts.inter(fontSize: 13, color: Colors.white60),
                  ),
                ],
              ),
              // If Closed details
              if (report.isClosed) ...[
                const SizedBox(height: 16),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.greenAccent.withValues(alpha: 0.05),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.greenAccent.withValues(alpha: 0.2)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.check_circle_outline, color: Colors.greenAccent, size: 18),
                          const SizedBox(width: 8),
                          Text(
                            'Closed by ${report.closedByUsername ?? "Admin"}',
                            style: GoogleFonts.inter(color: Colors.greenAccent, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                      if (report.closedAt != null && report.closedAt!.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          'Closed at: ${report.closedAt}',
                          style: GoogleFonts.inter(color: Colors.white60, fontSize: 12),
                        ),
                      ],
                      if (report.closureNotes != null && report.closureNotes!.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Text(
                          'Notes: ${report.closureNotes}',
                          style: GoogleFonts.inter(color: Colors.white70, fontSize: 13),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 32),
              // Close button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1E293B),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () => Navigator.pop(ctx),
                  child: Text('Close', style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Helpers ──

class _RiskDot extends StatelessWidget {
  final String level;
  const _RiskDot({required this.level});

  @override
  Widget build(BuildContext context) {
    final color = switch (level) {
      'high' => Colors.redAccent,
      'medium' => Colors.amberAccent,
      'low' => Colors.greenAccent,
      _ => Colors.blueGrey,
    };
    return Container(
      width: 10,
      height: 10,
      decoration: BoxDecoration(shape: BoxShape.circle, color: color),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final bool isClosed;
  const _StatusBadge({required this.isClosed});

  @override
  Widget build(BuildContext context) {
    final color = isClosed ? Colors.greenAccent : Colors.amberAccent;
    final label = isClosed ? 'Closed' : 'Open';
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.5)),
      ),
      child: Text(
        label,
        style: GoogleFonts.inter(
            fontSize: 11, fontWeight: FontWeight.w600, color: color),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  final IconData icon;
  final String label;
  const _InfoChip({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 13, color: Colors.blueAccent),
        const SizedBox(width: 4),
        Text(
          label,
          style: const TextStyle(color: Colors.white70, fontSize: 12),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
      ],
    );
  }
}

class _DetailBadge extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  const _DetailBadge({required this.label, required this.value, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.white10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: Colors.blueAccent),
          const SizedBox(width: 6),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: GoogleFonts.inter(fontSize: 10, color: const Color(0xFF94A3B8)),
              ),
              Text(
                value,
                style: GoogleFonts.inter(fontSize: 12, color: Colors.white, fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
