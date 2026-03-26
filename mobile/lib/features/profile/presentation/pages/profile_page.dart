import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../../auth/presentation/provider/auth_provider.dart';

class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.user;

    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        title: Text('Profile',
            style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            const SizedBox(height: 16),

            // ── Avatar ──
            Container(
              width: 90,
              height: 90,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.blueAccent.withValues(alpha: 0.15),
                border: Border.all(
                    color: Colors.blueAccent.withValues(alpha: 0.4), width: 2),
              ),
              child: const Icon(Icons.person_rounded,
                  size: 48, color: Colors.blueAccent),
            ),
            const SizedBox(height: 16),

            Text(
              user?.username ?? 'Unknown',
              style: GoogleFonts.outfit(
                  fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              _roleLabel(user?.role),
              style: const TextStyle(color: Colors.white54, fontSize: 14),
            ),
            const SizedBox(height: 32),

            // ── Info Card ──
            Container(
              decoration: BoxDecoration(
                color: const Color(0xFF1E293B),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.white10),
              ),
              child: Column(
                children: [
                  _ProfileRow(
                    icon: Icons.badge_outlined,
                    label: 'Username',
                    value: user?.username ?? '-',
                  ),
                  const Divider(height: 1, color: Colors.white10),
                  _ProfileRow(
                    icon: Icons.security_rounded,
                    label: 'Role',
                    value: _roleLabel(user?.role),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 32),

            // ── Logout ──
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: () => auth.logout(),
                icon: const Icon(Icons.logout, color: Colors.redAccent),
                label: Text(
                  'Logout',
                  style: GoogleFonts.inter(
                      color: Colors.redAccent, fontWeight: FontWeight.w600),
                ),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  side: const BorderSide(color: Colors.redAccent),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProfileRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _ProfileRow({
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: Colors.blueAccent, size: 22),
      title: Text(label,
          style: const TextStyle(color: Colors.white54, fontSize: 12)),
      subtitle: Text(
        value,
        style: GoogleFonts.inter(
            color: Colors.white, fontWeight: FontWeight.w500),
      ),
    );
  }
}

/// Converts the integer role from UserModel to a readable label.
String _roleLabel(int? role) {
  switch (role) {
    case 1:
      return 'Administrator';
    case 2:
      return 'Safety Manager';
    case 3:
      return 'Site Engineer';
    case 4:
      return 'Inspector';
    default:
      return 'User';
  }
}
