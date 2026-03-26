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
