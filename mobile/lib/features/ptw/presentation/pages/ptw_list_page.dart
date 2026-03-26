import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../provider/ptw_provider.dart';
import '../../data/models/ptw_model.dart';

class PtwListPage extends StatefulWidget {
  const PtwListPage({super.key});

  @override
  State<PtwListPage> createState() => _PtwListPageState();
}

class _PtwListPageState extends State<PtwListPage> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (!mounted) return;
      Provider.of<PtwProvider>(context, listen: false).fetchPermits();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        title: Text('Permits To Work', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => Provider.of<PtwProvider>(context, listen: false).fetchPermits(refresh: true),
          ),
        ],
      ),
      body: Consumer<PtwProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.permits.isEmpty) {
            return const Center(child: CircularProgressIndicator(color: Colors.blueAccent));
          }

          if (provider.error != null && provider.permits.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.redAccent),
                  const SizedBox(height: 16),
                  Text(provider.error!, style: const TextStyle(color: Colors.white70)),
                  TextButton(
                    onPressed: () => provider.fetchPermits(refresh: true),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () => provider.fetchPermits(refresh: true),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.permits.length,
              itemBuilder: (context, index) {
                final permit = provider.permits[index];
                return _PtwCard(permit: permit);
              },
            ),
          );
        },
      ),
    );
  }
}

class _PtwCard extends StatelessWidget {
  final PtwModel permit;
  const _PtwCard({required this.permit});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header with Status
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: const BoxDecoration(
              border: Border(bottom: BorderSide(color: Colors.white10)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  permit.permitNumber,
                  style: GoogleFonts.inter(
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                    fontSize: 16,
                  ),
                ),
                _StatusBadge(status: permit.ptwStatus, text: permit.statusText),
              ],
            ),
          ),
          
          // Body
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _InfoRow(icon: Icons.business, label: permit.projectName),
                const SizedBox(height: 8),
                _InfoRow(icon: Icons.location_on_outlined, label: permit.workLocation),
                const SizedBox(height: 12),
                Text(
                  permit.workDescription,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: Colors.white70, fontSize: 13),
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      permit.permitDate,
                      style: const TextStyle(color: Colors.white38, fontSize: 12),
                    ),
                    const Icon(Icons.arrow_forward_ios, size: 14, color: Colors.blueAccent),
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

class _StatusBadge extends StatelessWidget {
  final int status;
  final String text;
  const _StatusBadge({required this.status, required this.text});

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status) {
      case 1: color = Colors.greenAccent; break;
      case 2: color = Colors.amberAccent; break;
      case 3: color = Colors.redAccent; break;
      case 4: color = Colors.blueGrey; break;
      default: color = Colors.grey;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.5)),
      ),
      child: Text(
        text,
        style: GoogleFonts.inter(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: color,
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  const _InfoRow({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 16, color: Colors.blueAccent),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(color: Colors.white, fontSize: 14),
          ),
        ),
      ],
    );
  }
}
