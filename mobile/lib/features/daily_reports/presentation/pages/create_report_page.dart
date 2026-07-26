import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../provider/report_provider.dart';

class CreateReportPage extends StatefulWidget {
  const CreateReportPage({super.key});

  @override
  State<CreateReportPage> createState() => _CreateReportPageState();
}

class _CreateReportPageState extends State<CreateReportPage> {
  final _formKey = GlobalKey<FormState>();

  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  final _correctiveController = TextEditingController();

  bool _isLoadingOptions = true;
  bool _isSubmitting = false;

  List<dynamic> _projects = [];
  List<dynamic> _departments = [];
  List<dynamic> _observations = [];

  String? _selectedProject;
  String? _selectedDepartment;
  String? _selectedObservation;
  String? _selectedWorkType;
  String _selectedRisk = 'Low';

  List<dynamic> _availableWorkTypes = [];

  @override
  void initState() {
    super.initState();
    _loadOptions();
  }

  Future<void> _loadOptions() async {
    setState(() => _isLoadingOptions = true);
    try {
      final provider = Provider.of<ReportProvider>(context, listen: false);
      final opts = await provider.fetchFormOptions();
      if (!mounted) return;
      setState(() {
        _projects = opts['projects'] ?? [];
        _departments = opts['departments'] ?? [];
        _observations = opts['observations'] ?? [];

        if (_projects.isNotEmpty) {
          _selectedProject = _projects.first['id']?.toString();
        }
        if (_departments.isNotEmpty) {
          _selectedDepartment = _departments.first['id']?.toString();
        }
        if (_observations.isNotEmpty) {
          _selectedObservation = _observations.first['name']?.toString();
          _updateWorkTypes(_selectedObservation);
        }
        _isLoadingOptions = false;
      });
    } catch (e) {
      if (mounted) {
        setState(() => _isLoadingOptions = false);
      }
    }
  }

  void _updateWorkTypes(String? observationName) {
    if (observationName == null) return;
    final cat = _observations.firstWhere(
      (o) => o['name']?.toString() == observationName,
      orElse: () => null,
    );
    if (cat != null && cat['work_types'] is List) {
      _availableWorkTypes = cat['work_types'];
      if (_availableWorkTypes.isNotEmpty) {
        _selectedWorkType = _availableWorkTypes.first['name']?.toString();
      } else {
        _selectedWorkType = null;
      }
    } else {
      _availableWorkTypes = [];
      _selectedWorkType = null;
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedProject == null || _selectedDepartment == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a project and department')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    final provider = Provider.of<ReportProvider>(context, listen: false);
    final reportData = {
      'project': _selectedProject,
      'department': _selectedDepartment,
      'observation': _selectedObservation ?? 'Safety',
      'work_type': _selectedWorkType ?? 'General',
      'risk': _selectedRisk,
      'observation_description': _titleController.text.trim(),
      'description': _descController.text.trim(),
      'operation_corrective': _correctiveController.text.trim(),
    };

    final success = await provider.submitReport(reportData);

    if (!mounted) return;
    setState(() => _isSubmitting = false);

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.check_circle, color: Colors.greenAccent),
              const SizedBox(width: 10),
              Text('Report submitted successfully!', style: GoogleFonts.inter()),
            ],
          ),
          backgroundColor: const Color(0xFF1E293B),
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            provider.error ?? 'Failed to submit report. Please check permissions and try again.',
            style: GoogleFonts.inter(),
          ),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    _correctiveController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        title: Text('New Daily Report', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: _isLoadingOptions
          ? const Center(child: CircularProgressIndicator(color: Colors.blueAccent))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Site Observation Details',
                      style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.w600, color: Colors.white),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Document safety findings and corrective actions',
                      style: GoogleFonts.inter(fontSize: 13, color: const Color(0xFF94A3B8)),
                    ),
                    const SizedBox(height: 20),

                    // Project Dropdown
                    _buildLabel('Project *'),
                    _buildDropdown(
                      value: _selectedProject,
                      items: _projects.map((p) {
                        return DropdownMenuItem<String>(
                          value: p['id']?.toString(),
                          child: Text(p['project_name']?.toString() ?? 'Project'),
                        );
                      }).toList(),
                      onChanged: (val) => setState(() => _selectedProject = val),
                      hint: 'Select Project',
                    ),
                    const SizedBox(height: 16),

                    // Department Dropdown
                    _buildLabel('Department *'),
                    _buildDropdown(
                      value: _selectedDepartment,
                      items: _departments.map((d) {
                        return DropdownMenuItem<String>(
                          value: d['id']?.toString(),
                          child: Text(d['department_name']?.toString() ?? 'Department'),
                        );
                      }).toList(),
                      onChanged: (val) => setState(() => _selectedDepartment = val),
                      hint: 'Select Department',
                    ),
                    const SizedBox(height: 16),

                    // Observation Category & Work Type
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildLabel('Category'),
                              _buildDropdown(
                                value: _selectedObservation,
                                items: _observations.map((o) {
                                  return DropdownMenuItem<String>(
                                    value: o['name']?.toString(),
                                    child: Text(o['name']?.toString() ?? ''),
                                  );
                                }).toList(),
                                onChanged: (val) {
                                  setState(() {
                                    _selectedObservation = val;
                                    _updateWorkTypes(val);
                                  });
                                },
                                hint: 'Category',
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildLabel('Work Type'),
                              _buildDropdown(
                                value: _selectedWorkType,
                                items: _availableWorkTypes.map((w) {
                                  return DropdownMenuItem<String>(
                                    value: w['name']?.toString(),
                                    child: Text(w['name']?.toString() ?? ''),
                                  );
                                }).toList(),
                                onChanged: (val) => setState(() => _selectedWorkType = val),
                                hint: 'Work Type',
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Risk Level Selector
                    _buildLabel('Risk Level'),
                    Row(
                      children: [
                        _buildRiskChip('Low', Colors.greenAccent),
                        const SizedBox(width: 10),
                        _buildRiskChip('Medium', Colors.amberAccent),
                        const SizedBox(width: 10),
                        _buildRiskChip('High', Colors.redAccent),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Observation Title Input
                    _buildLabel('Observation Title *'),
                    TextFormField(
                      controller: _titleController,
                      style: GoogleFonts.inter(color: Colors.white),
                      decoration: _inputDecoration('e.g. Scaffolding guardrail missing'),
                      validator: (val) =>
                          val == null || val.trim().isEmpty ? 'Title is required' : null,
                    ),
                    const SizedBox(height: 16),

                    // Description Input
                    _buildLabel('Detailed Description *'),
                    TextFormField(
                      controller: _descController,
                      maxLines: 4,
                      style: GoogleFonts.inter(color: Colors.white),
                      decoration: _inputDecoration('Describe the observation in detail...'),
                      validator: (val) =>
                          val == null || val.trim().isEmpty ? 'Description is required' : null,
                    ),
                    const SizedBox(height: 16),

                    // Corrective Action Input
                    _buildLabel('Corrective Action (Optional)'),
                    TextFormField(
                      controller: _correctiveController,
                      maxLines: 2,
                      style: GoogleFonts.inter(color: Colors.white),
                      decoration: _inputDecoration('State any corrective measures applied...'),
                    ),
                    const SizedBox(height: 32),

                    // Submit Button
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blueAccent,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                          elevation: 2,
                        ),
                        onPressed: _isSubmitting ? null : _submit,
                        child: _isSubmitting
                            ? const SizedBox(
                                width: 24,
                                height: 24,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2.5,
                                ),
                              )
                            : Text(
                                'Submit Daily Report',
                                style: GoogleFonts.outfit(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                      ),
                    ),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        text,
        style: GoogleFonts.inter(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: Colors.white70,
        ),
      ),
    );
  }

  Widget _buildDropdown({
    required String? value,
    required List<DropdownMenuItem<String>> items,
    required ValueChanged<String?> onChanged,
    required String hint,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white10),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          isExpanded: true,
          dropdownColor: const Color(0xFF1E293B),
          style: GoogleFonts.inter(color: Colors.white, fontSize: 14),
          hint: Text(hint, style: const TextStyle(color: Colors.white38)),
          items: items,
          onChanged: onChanged,
        ),
      ),
    );
  }

  Widget _buildRiskChip(String label, Color color) {
    final isSelected = _selectedRisk == label;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _selectedRisk = label),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isSelected ? color.withValues(alpha: 0.15) : const Color(0xFF1E293B),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? color : Colors.white10,
              width: isSelected ? 1.5 : 1,
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(shape: BoxShape.circle, color: color),
              ),
              const SizedBox(width: 8),
              Text(
                label,
                style: GoogleFonts.inter(
                  color: isSelected ? color : Colors.white70,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  InputDecoration _inputDecoration(String hint) {
    return InputDecoration(
      hintText: hint,
      hintStyle: const TextStyle(color: Colors.white24, fontSize: 14),
      filled: true,
      fillColor: const Color(0xFF1E293B),
      contentPadding: const EdgeInsets.all(16),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: Colors.white10),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: Colors.white10),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: Colors.blueAccent),
      ),
    );
  }
}
