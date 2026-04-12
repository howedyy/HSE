import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { useLookups, useReportOptions, usePTWOptions } from '../../application/hooks/useLookups';
import { usePermissions } from '../../application/hooks/useUsers';
import api from '../../infrastructure/api/client';
import {
    Settings, UserPlus, FolderOpen,
    User, Lock, Briefcase, Mail, Check,
    Key, ChevronRight, RefreshCw,
    Plus, Power, Info, Save, CheckCircle2,
    XCircle, AlertCircle, ListTree, PlusCircle, Trash2, ClipboardCheck
} from 'lucide-react';

// ─── Types ───────────────────────────────────────────────────────────────────
interface Project {
    id: number;
    project_name: string;
    project_status: number; // 1 = active, 0 = inactive
    region?: number | null;
    email?: string | null;
}

type Toast = { type: 'success' | 'error'; message: string } | null;

// ─── Toast Component ─────────────────────────────────────────────────────────
const ToastNotif = ({ toast, onDismiss }: { toast: Toast; onDismiss: () => void }) => {
    useEffect(() => {
        if (!toast) return;
        const t = setTimeout(onDismiss, 3500);
        return () => clearTimeout(t);
    }, [toast]);

    if (!toast) return null;

    const isSuccess = toast.type === 'success';
    return (
        <div className={`fixed top-6 right-6 z-[999] flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl border animate-in slide-in-from-top duration-300 ${
            isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'
        }`}>
            {isSuccess ? <CheckCircle2 size={20} className="text-emerald-600 shrink-0" /> : <XCircle size={20} className="text-red-500 shrink-0" />}
            <span className="text-sm font-bold">{toast.message}</span>
        </div>
    );
};

// ─── Add User Tab ─────────────────────────────────────────────────────────────
const AddUserTab: React.FC = () => {
    const { t } = useTranslation();
    const { departments } = useLookups();
    const { data: allPermsData } = usePermissions();

    const defaultForm = {
        username: '', password: '', userType: 2,
        departmentId: '', userStatus: 1,
        editorName: '', jobTitle: ''
    };

    const [form, setForm] = useState<any>(defaultForm);
    const [selectedPermissions, setSelectedPermissions] = useState<string[]>([]);
    const [loading, setLoading] = useState(false);
    const [toast, setToast] = useState<Toast>(null);

    const groupedPermissions = allPermsData?.data?.reduce((acc: any, p: any) => {
        if (!acc[p.page]) acc[p.page] = [];
        acc[p.page].push(p);
        return acc;
    }, {}) || {};

    const handleRoleChange = async (type: number) => {
        setForm((prev: any) => ({ ...prev, userType: type }));
        try {
            const response: any = await api.get(`/users/role_defaults?role_type=${type}`);
            if (response.data) setSelectedPermissions(response.data);
        } catch { /* silently skip */ }
    };

    const togglePerm = (value: string) => {
        setSelectedPermissions(prev =>
            prev.includes(value) ? prev.filter(p => p !== value) : [...prev, value]
        );
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        try {
            await api.post('/users/create', { ...form, permissions: selectedPermissions });
            setToast({ type: 'success', message: t('settings.userCreated', 'User created successfully!') });
            setForm(defaultForm);
            setSelectedPermissions([]);
        } catch (err: any) {
            const msg = err?.response?.data?.error ?? t('settings.userCreateFailed', 'Failed to create user');
            setToast({ type: 'error', message: msg });
        } finally {
            setLoading(false);
        }
    };

    const inputClass = "w-full pl-11 rtl:pl-4 rtl:pr-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-600 font-semibold text-gray-700 outline-none transition-all";
    const plainInputClass = "w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-600 font-semibold text-gray-700 outline-none transition-all cursor-pointer appearance-none";

    return (
        <>
            <ToastNotif toast={toast} onDismiss={() => setToast(null)} />
            <form onSubmit={handleSubmit} className="space-y-8">

                {/* Identity Section */}
                <div className="bg-white rounded-3xl border border-gray-100 p-8 space-y-6">
                    <div className="flex items-center gap-2 mb-2">
                        <Info size={16} className="text-blue-600" />
                        <h3 className="text-sm font-black text-gray-900 uppercase tracking-wider">
                            {t('users.identityDetails', 'Identity Details')}
                        </h3>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {/* Username */}
                        <div className="space-y-2">
                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.username', 'Username')}</label>
                            <div className="relative">
                                <User className="absolute left-4 rtl:left-auto rtl:right-4 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                                <input type="text" required value={form.username}
                                    onChange={e => setForm({ ...form, username: e.target.value })}
                                    className={inputClass} />
                            </div>
                        </div>

                        {/* Password */}
                        <div className="space-y-2">
                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.password', 'Password')}</label>
                            <div className="relative">
                                <Lock className="absolute left-4 rtl:left-auto rtl:right-4 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                                <input type="password" required placeholder="••••••••" value={form.password}
                                    onChange={e => setForm({ ...form, password: e.target.value })}
                                    className={inputClass} />
                            </div>
                        </div>

                        {/* Full Name */}
                        <div className="space-y-2">
                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.fullName', 'Full Name')}</label>
                            <div className="relative">
                                <Mail className="absolute left-4 rtl:left-auto rtl:right-4 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                                <input type="text" required value={form.editorName}
                                    onChange={e => setForm({ ...form, editorName: e.target.value })}
                                    className={inputClass} />
                            </div>
                        </div>

                        {/* Job Title */}
                        <div className="space-y-2">
                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.jobTitle', 'Job Title')}</label>
                            <div className="relative">
                                <Briefcase className="absolute left-4 rtl:left-auto rtl:right-4 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                                <input type="text" required value={form.jobTitle}
                                    onChange={e => setForm({ ...form, jobTitle: e.target.value })}
                                    className={inputClass} />
                            </div>
                        </div>

                        {/* Role */}
                        <div className="space-y-2">
                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.clearance', 'Role')}</label>
                            <select value={form.userType}
                                onChange={e => handleRoleChange(Number(e.target.value))}
                                className={plainInputClass}>
                                <option value={1}>{t('roles.admin', 'Admin')}</option>
                                <option value={2}>{t('roles.hse', 'HSE')}</option>
                                <option value={3}>{t('roles.operation', 'Operation')}</option>
                            </select>
                        </div>

                        {/* Department */}
                        <div className="space-y-2">
                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.unit', 'Department')}</label>
                            <select value={form.departmentId}
                                onChange={e => setForm({ ...form, departmentId: e.target.value })}
                                className={plainInputClass}>
                                <option value="">{t('common.public', 'General / Public')}</option>
                                {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                            </select>
                        </div>
                    </div>

                    {/* Status Toggle */}
                    <div className="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div className="flex-1">
                            <p className="text-xs font-black text-gray-900 uppercase tracking-tight">{t('users.activeStatus', 'Account Status')}</p>
                            <p className="text-[10px] text-gray-400 font-bold">{t('users.activeStatusSub', 'Grant or revoke system access immediately.')}</p>
                        </div>
                        <button type="button"
                            onClick={() => setForm({ ...form, userStatus: form.userStatus === 1 ? 0 : 1 })}
                            className={`relative w-14 h-8 rounded-full transition-all duration-300 ring-4 ring-transparent focus:ring-blue-600/10 ${form.userStatus === 1 ? 'bg-emerald-500' : 'bg-gray-300'}`}>
                            <div className={`absolute top-1 left-1 bg-white w-6 h-6 rounded-full shadow-md transition-transform duration-300 ${form.userStatus === 1 ? 'translate-x-6' : ''}`} />
                        </button>
                    </div>
                </div>

                {/* Permissions Section */}
                <div className="bg-white rounded-3xl border border-gray-100 p-8 space-y-6">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <Key size={16} className="text-blue-600" />
                            <h3 className="text-sm font-black text-gray-900 uppercase tracking-wider">{t('users.permissions', 'Permissions')}</h3>
                        </div>
                        <span className="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-widest">
                            {t('users.activeRules', { count: selectedPermissions.length })}
                        </span>
                    </div>

                    <div className="grid grid-cols-1 gap-4">
                        {Object.entries(groupedPermissions).map(([page, perms]: [string, any]) => (
                            <div key={page} className="bg-gray-50/80 border border-gray-100 rounded-2xl p-5 space-y-3 hover:border-blue-200 transition-colors group">
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 bg-white text-gray-400 group-hover:bg-blue-50 group-hover:text-blue-600 rounded-xl flex items-center justify-center transition-colors border border-gray-100">
                                        <ChevronRight size={14} className="rtl:rotate-180" />
                                    </div>
                                    <h4 className="text-xs font-black text-gray-700 uppercase tracking-widest">
                                        {t(`modules.${page.split('.')[0]}`, page.split('.')[0].replace(/_/g, ' '))} {t('common.module', 'Module')}
                                    </h4>
                                </div>
                                <div className="grid grid-cols-1 gap-2">
                                    {perms.map((p: any) => (
                                        <div key={p.value} onClick={() => togglePerm(p.value)}
                                            className={`flex items-center justify-between p-3 rounded-xl cursor-pointer transition-all select-none ${
                                                selectedPermissions.includes(p.value)
                                                    ? 'bg-blue-50 border border-blue-100'
                                                    : 'bg-white border border-transparent hover:bg-gray-50'
                                            }`}>
                                            <div className="flex flex-col">
                                                <span className={`text-[11px] font-bold ${selectedPermissions.includes(p.value) ? 'text-blue-700' : 'text-gray-600'}`}>
                                                    {p.description}
                                                </span>
                                                <span className="text-[9px] font-medium text-gray-400">{t('common.command', 'Command')}: {p.action}</span>
                                            </div>
                                            <div className={`w-5 h-5 rounded-lg border-2 flex items-center justify-center transition-all shrink-0 ${
                                                selectedPermissions.includes(p.value) ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-200 bg-white'
                                            }`}>
                                                {selectedPermissions.includes(p.value) && <Check size={12} strokeWidth={4} />}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Submit */}
                <button type="submit" disabled={loading}
                    className="w-full flex items-center justify-center gap-3 bg-blue-600 text-white px-8 py-4 rounded-3xl font-black uppercase tracking-widest text-xs hover:bg-blue-700 disabled:opacity-50 shadow-xl shadow-blue-100 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    {loading ? <RefreshCw size={18} className="animate-spin" /> : <Save size={18} />}
                    {loading ? t('common.saving', 'Creating User...') : t('settings.createUser', 'Create User')}
                </button>
            </form>
        </>
    );
};

// ─── Projects Tab ─────────────────────────────────────────────────────────────
const ProjectsTab: React.FC = () => {
    const { t } = useTranslation();
    const [projects, setProjects] = useState<Project[]>([]);
    const [loadingList, setLoadingList] = useState(true);
    const [newName, setNewName] = useState('');
    const [newRegion, setNewRegion] = useState<number>(1);
    const [newEmail, setNewEmail] = useState('');
    const [addingProject, setAddingProject] = useState(false);
    const [togglingId, setTogglingId] = useState<number | null>(null);
    const [toast, setToast] = useState<Toast>(null);

    const fetchProjects = async () => {
        setLoadingList(true);
        try {
            const res: any = await api.get('/projects/list');
            setProjects(res.data ?? []);
        } catch {
            setToast({ type: 'error', message: t('settings.fetchProjectsFailed', 'Failed to load projects') });
        } finally {
            setLoadingList(false);
        }
    };

    useEffect(() => { fetchProjects(); }, []);

    const handleAdd = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newName.trim()) return;
        setAddingProject(true);
        try {
            const res: any = await api.post('/projects/create', { 
                project_name: newName.trim(),
                region: newRegion,
                email: newEmail.trim()
            });
            setProjects(prev => [...prev, { 
                id: res.id, 
                project_name: newName.trim(), 
                project_status: 1,
                region: newRegion,
                email: newEmail.trim()
            }]);
            setNewName('');
            setNewEmail('');
            setToast({ type: 'success', message: t('settings.projectCreated', 'Project created!') });
        } catch {
            setToast({ type: 'error', message: t('settings.projectCreateFailed', 'Failed to create project') });
        } finally {
            setAddingProject(false);
        }
    };

    const handleToggle = async (id: number) => {
        setTogglingId(id);
        try {
            const res: any = await api.post('/projects/toggle', { id });
            setProjects(prev => prev.map(p => p.id === id ? { ...p, project_status: res.project_status } : p));
        } catch {
            setToast({ type: 'error', message: t('settings.toggleFailed', 'Failed to update project status') });
        } finally {
            setTogglingId(null);
        }
    };

    const activeCount = projects.filter(p => p.project_status === 1).length;

    return (
        <>
            <ToastNotif toast={toast} onDismiss={() => setToast(null)} />

            <div className="space-y-6">
                {/* Add Project Card */}
                <div className="bg-white rounded-3xl border border-gray-100 p-6">
                    <div className="flex items-center gap-2 mb-4">
                        <Plus size={16} className="text-blue-600" />
                        <h3 className="text-sm font-black text-gray-900 uppercase tracking-wider">
                            {t('settings.addProject', 'Add New Project')}
                        </h3>
                    </div>
                    <form onSubmit={handleAdd} className="space-y-4">
                        <div className="flex flex-col sm:flex-row gap-3">
                            <input
                                type="text"
                                value={newName}
                                onChange={e => setNewName(e.target.value)}
                                placeholder={t('settings.projectNamePlaceholder', 'Enter project name...')}
                                className="flex-[2] px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-600 font-semibold text-gray-700 outline-none transition-all text-sm"
                                required
                            />
                            <select
                                value={newRegion}
                                onChange={e => setNewRegion(Number(e.target.value))}
                                className="flex-1 px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-600 font-semibold text-gray-700 outline-none transition-all text-sm cursor-pointer"
                            >
                                <option value={1}>{t('settings.regionWest', 'West Region')}</option>
                                <option value={2}>{t('settings.regionEast', 'East Region')}</option>
                            </select>
                        </div>
                        <div className="flex gap-3">
                            <input
                                type="email"
                                value={newEmail}
                                onChange={e => setNewEmail(e.target.value)}
                                placeholder={t('settings.projectEmailPlaceholder', 'Project email (optional)...')}
                                className="flex-1 px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-600 font-semibold text-gray-700 outline-none transition-all text-sm"
                            />
                            <button type="submit" disabled={addingProject}
                                className="flex items-center gap-2 px-8 py-3 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50 transition-all shadow-lg shadow-blue-100">
                                {addingProject ? <RefreshCw size={16} className="animate-spin" /> : <Plus size={16} />}
                                {t('common.add', 'Add')}
                            </button>
                        </div>
                    </form>
                </div>

                {/* Projects List */}
                <div className="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                    <div className="p-6 border-b border-gray-50 flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                                <FolderOpen size={20} />
                            </div>
                            <div>
                                <h3 className="font-black text-gray-900 text-sm">{t('settings.projectsTitle', 'All Projects')}</h3>
                                <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                    {activeCount} {t('common.active', 'Active')} / {projects.length} {t('settings.total', 'Total')}
                                </p>
                            </div>
                        </div>
                        <button onClick={fetchProjects} className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors">
                            <RefreshCw size={16} />
                        </button>
                    </div>

                    {loadingList ? (
                        <div className="p-8 space-y-3">
                            {[...Array(5)].map((_, i) => <div key={i} className="h-16 bg-gray-50 rounded-2xl animate-pulse" />)}
                        </div>
                    ) : projects.length === 0 ? (
                        <div className="py-20 flex flex-col items-center gap-4 text-center">
                            <div className="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center">
                                <AlertCircle size={32} className="text-gray-300" />
                            </div>
                            <p className="text-sm font-bold text-gray-400 uppercase tracking-widest">
                                {t('settings.noProjects', 'No projects found')}
                            </p>
                        </div>
                    ) : (
                        <div className="divide-y divide-gray-50">
                            {projects.map(project => (
                                <div key={project.id}
                                    className="flex items-center justify-between px-6 py-4 hover:bg-gray-50/50 transition-colors group">
                                    <div className="flex items-center gap-4">
                                        <div className={`w-2.5 h-2.5 rounded-full shrink-0 transition-colors ${
                                            project.project_status === 1 ? 'bg-emerald-500' : 'bg-gray-300'
                                        }`} />
                                        <div>
                                            <p className="font-bold text-gray-800 text-sm group-hover:text-blue-700 transition-colors">
                                                {project.project_name}
                                            </p>
                                            <p className="text-[10px] font-bold uppercase tracking-widest mt-0.5 transition-colors">
                                                <span className={project.project_status === 1 ? 'text-emerald-500' : 'text-gray-400'}>
                                                    {project.project_status === 1 ? t('common.active', 'Active') : t('common.inactive', 'Inactive')}
                                                </span>
                                                <span className="text-gray-300 mx-1">•</span>
                                                <span className="text-gray-400">
                                                    {project.region === 1 ? t('settings.regionWestShort', 'West') : project.region === 2 ? t('settings.regionEastShort', 'East') : t('common.unknown', 'Unknown')}
                                                </span>
                                                {project.email && (
                                                    <>
                                                        <span className="text-gray-300 mx-1">•</span>
                                                        <span className="text-gray-400 lowercase">{project.email}</span>
                                                    </>
                                                )}
                                                <span className="text-gray-300 mx-1">•</span>
                                                <span className="text-gray-400">ID #{project.id}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        onClick={() => handleToggle(project.id)}
                                        disabled={togglingId === project.id}
                                        title={project.project_status === 1 ? t('settings.deactivate', 'Deactivate') : t('settings.activate', 'Activate')}
                                        className={`flex items-center gap-2 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all disabled:opacity-50 ${
                                            project.project_status === 1
                                                ? 'bg-red-50 text-red-500 border-red-100 hover:bg-red-100'
                                                : 'bg-emerald-50 text-emerald-600 border-emerald-100 hover:bg-emerald-100'
                                        }`}>
                                        {togglingId === project.id
                                            ? <RefreshCw size={14} className="animate-spin" />
                                            : <Power size={14} />
                                        }
                                        {project.project_status === 1 ? t('settings.deactivate', 'Deactivate') : t('settings.activate', 'Activate')}
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
};

// ─── Report Options Tab ───────────────────────────────────────────────────────
const ReportOptionsTab: React.FC = () => {
    const { t } = useTranslation();
    const { observationTypes, isLoading, refetch } = useReportOptions();
    const [newObsName, setNewObsName] = useState('');
    const [newWorkNames, setNewWorkNames] = useState<Record<number, string>>({});
    const [loading, setLoading] = useState<string | null>(null);
    const [toast, setToast] = useState<Toast>(null);

    const handleAddObservation = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newObsName.trim()) return;
        setLoading('add-obs');
        try {
            await api.post('/report_options/create', { type: 'observation', name: newObsName.trim() });
            setNewObsName('');
            refetch();
            setToast({ type: 'success', message: t('common.success', 'Success') });
        } catch {
            setToast({ type: 'error', message: t('settings.reportOptions.createFailed', 'Failed to create') });
        } finally {
            setLoading(null);
        }
    };

    const handleAddWorkType = async (obsId: number) => {
        const name = newWorkNames[obsId]?.trim();
        if (!name) return;
        setLoading(`add-work-${obsId}`);
        try {
            await api.post('/report_options/create', { type: 'work_type', name, observation_type_id: obsId });
            setNewWorkNames(prev => ({ ...prev, [obsId]: '' }));
            refetch();
            setToast({ type: 'success', message: t('common.success', 'Success') });
        } catch {
            setToast({ type: 'error', message: t('settings.reportOptions.createFailed', 'Failed to create') });
        } finally {
            setLoading(null);
        }
    };

    const handleToggle = async (type: 'observation' | 'work_type', id: number) => {
        setLoading(`toggle-${type}-${id}`);
        try {
            await api.post('/report_options/toggle', { type, id });
            refetch();
        } catch {
            setToast({ type: 'error', message: t('settings.reportOptions.toggleFailed', 'Failed to update status') });
        } finally {
            setLoading(null);
        }
    };

    return (
        <>
            <ToastNotif toast={toast} onDismiss={() => setToast(null)} />
            <div className="space-y-6">
                {/* Add Observation Type */}
                <div className="bg-white rounded-3xl border border-gray-100 p-6">
                    <div className="flex items-center gap-2 mb-4">
                        <PlusCircle size={16} className="text-blue-600" />
                        <h3 className="text-sm font-black text-gray-900 uppercase tracking-wider">
                            {t('settings.reportOptions.addObservation', 'Add Observation Type')}
                        </h3>
                    </div>
                    <form onSubmit={handleAddObservation} className="flex gap-3">
                        <input
                            type="text"
                            value={newObsName}
                            onChange={e => setNewObsName(e.target.value)}
                            placeholder={t('settings.reportOptions.obsPlaceholder', 'e.g. Site Inspection...')}
                            className="flex-1 px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-600 font-semibold text-gray-700 outline-none transition-all text-sm"
                            required
                        />
                        <button type="submit" disabled={loading === 'add-obs'}
                            className="flex items-center gap-2 px-8 py-3 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50 transition-all shadow-lg shadow-blue-100">
                            {loading === 'add-obs' ? <RefreshCw size={16} className="animate-spin" /> : <Plus size={16} />}
                            {t('common.add', 'Add')}
                        </button>
                    </form>
                </div>

                {/* List of Observation Types */}
                {isLoading ? (
                    <div className="space-y-4">
                        {[...Array(3)].map((_, i) => <div key={i} className="h-32 bg-gray-50 rounded-3xl animate-pulse" />)}
                    </div>
                ) : observationTypes.length === 0 ? (
                    <div className="bg-white rounded-3xl border border-gray-100 p-12 text-center">
                        <ListTree size={48} className="text-gray-200 mx-auto mb-4" />
                        <p className="text-gray-400 font-bold uppercase tracking-widest text-sm">
                            {t('settings.reportOptions.noOptions', 'No report options configured.')}
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-6">
                        {observationTypes.map(obs => (
                            <div key={obs.id} className="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                                <div className="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                                    <div className="flex items-center gap-3">
                                        <div className={`w-2 h-2 rounded-full ${obs.status === 1 ? 'bg-emerald-500' : 'bg-gray-300'}`} />
                                        <h4 className="font-black text-gray-900 text-sm uppercase tracking-tight">{obs.name}</h4>
                                    </div>
                                    <button
                                        onClick={() => handleToggle('observation', obs.id)}
                                        disabled={loading === `toggle-observation-${obs.id}`}
                                        className={`px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all ${
                                            obs.status === 1 ? 'bg-red-50 text-red-500 border-red-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                        }`}
                                    >
                                        {loading === `toggle-observation-${obs.id}` ? <RefreshCw size={14} className="animate-spin" /> : obs.status === 1 ? t('settings.deactivate', 'Deactivate') : t('settings.activate', 'Activate')}
                                    </button>
                                </div>
                                
                                <div className="p-6 space-y-4">
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        {obs.work_types.map(wt => (
                                            <div key={wt.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-2xl border border-gray-100 hover:border-blue-200 transition-colors">
                                                <span className={`text-[11px] font-bold ${wt.status === 1 ? 'text-gray-700' : 'text-gray-400 line-through'}`}>{wt.name}</span>
                                                <button
                                                    onClick={() => handleToggle('work_type', wt.id)}
                                                    disabled={loading === `toggle-work_type-${wt.id}`}
                                                    className={`p-1.5 rounded-lg transition-colors ${wt.status === 1 ? 'text-red-400 hover:bg-red-50' : 'text-emerald-500 hover:bg-emerald-50'}`}
                                                >
                                                    {loading === `toggle-work_type-${wt.id}` ? <RefreshCw size={12} className="animate-spin" /> : <Power size={12} />}
                                                </button>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Add Work Type for this category */}
                                    <div className="flex gap-2 pt-2">
                                        <input
                                            type="text"
                                            value={newWorkNames[obs.id] || ''}
                                            onChange={e => setNewWorkNames(prev => ({ ...prev, [obs.id]: e.target.value }))}
                                            placeholder={t('settings.reportOptions.workTypePlaceholder', 'Add work description...')}
                                            className="flex-1 px-4 py-2 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-600 font-semibold text-gray-700 outline-none transition-all text-xs"
                                        />
                                        <button
                                            onClick={() => handleAddWorkType(obs.id)}
                                            disabled={loading === `add-work-${obs.id}`}
                                            className="px-4 py-2 bg-gray-900 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-colors disabled:opacity-50"
                                        >
                                            {loading === `add-work-${obs.id}` ? <RefreshCw size={14} className="animate-spin" /> : <Plus size={14} />}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
};

// ─── PTW Options Tab ──────────────────────────────────────────────────────────
const PTWOptionsTab: React.FC = () => {
    const { t } = useTranslation();
    const { operationTypes, safetyMeasures, isLoading, refetch } = usePTWOptions();
    const [newOp, setNewOp] = useState({ name: '', risk: '' });
    const [newMeasure, setNewMeasure] = useState('');
    const [loading, setLoading] = useState<string | null>(null);
    const [toast, setToast] = useState<Toast>(null);

    const handleAddOp = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newOp.name.trim()) return;
        setLoading('add-op');
        try {
            await api.post('/ptw_config/create', { type: 'operation', name: newOp.name.trim(), risk: newOp.risk.trim() });
            setNewOp({ name: '', risk: '' });
            refetch();
            setToast({ type: 'success', message: t('common.success', 'Success') });
        } catch {
            setToast({ type: 'error', message: t('settings.reportOptions.createFailed', 'Failed to create') });
        } finally {
            setLoading(null);
        }
    };

    const handleAddMeasure = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newMeasure.trim()) return;
        setLoading('add-measure');
        try {
            await api.post('/ptw_config/create', { type: 'measure', name: newMeasure.trim() });
            setNewMeasure('');
            refetch();
            setToast({ type: 'success', message: t('common.success', 'Success') });
        } catch {
            setToast({ type: 'error', message: t('settings.reportOptions.createFailed', 'Failed to create') });
        } finally {
            setLoading(null);
        }
    };

    const handleToggle = async (type: 'operation' | 'measure', id: number, currentStatus: number) => {
        setLoading(`toggle-${type}-${id}`);
        try {
            await api.post('/ptw_config/toggle', { type, id, status: currentStatus === 1 ? 0 : 1 });
            refetch();
        } catch {
            setToast({ type: 'error', message: t('settings.reportOptions.toggleFailed', 'Failed to update status') });
        } finally {
            setLoading(null);
        }
    };

    return (
        <>
            <ToastNotif toast={toast} onDismiss={() => setToast(null)} />
            <div className="space-y-8">
                {/* Add Operation Type */}
                <div className="bg-white rounded-3xl border border-gray-100 p-6">
                    <div className="flex items-center gap-2 mb-4">
                        <PlusCircle size={16} className="text-indigo-600" />
                        <h3 className="text-sm font-black text-gray-900 uppercase tracking-wider">
                           Add PTW Operation Type
                        </h3>
                    </div>
                    <form onSubmit={handleAddOp} className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <input
                            type="text"
                            value={newOp.name}
                            onChange={e => setNewOp({ ...newOp, name: e.target.value })}
                            placeholder="Operation Type"
                            className="px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-600 font-semibold text-gray-700 outline-none transition-all text-sm"
                            required
                        />
                        <input
                            type="text"
                            value={newOp.risk}
                            onChange={e => setNewOp({ ...newOp, risk: e.target.value })}
                            placeholder="Risk Assessment"
                            className="px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-600 font-semibold text-gray-700 outline-none transition-all text-sm"
                            required
                        />
                        <button type="submit" disabled={loading === 'add-op'}
                            className="flex items-center justify-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50 transition-all shadow-lg shadow-indigo-100">
                            {loading === 'add-op' ? <RefreshCw size={16} className="animate-spin" /> : <Plus size={16} />}
                            Add
                        </button>
                    </form>
                </div>

                {/* Operation Types List */}
                <div className="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                    <div className="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h4 className="font-black text-gray-900 text-sm uppercase tracking-tight">PTW Operation Types</h4>
                    </div>
                    <div className="divide-y divide-gray-50">
                        {operationTypes.map(op => (
                            <div key={op.id} className="flex items-center justify-between p-4 hover:bg-gray-50/50 transition-colors">
                                <div className="flex flex-col gap-1 max-w-[70%]">
                                    <span className={`text-sm font-bold ${op.is_active === 1 ? 'text-gray-900' : 'text-gray-400 line-through'}`}>{op.operation_name}</span>
                                    <span className="text-[10px] text-gray-500 font-medium italic">{op.risk_assessment}</span>
                                </div>
                                <button
                                    onClick={() => handleToggle('operation', op.id, op.is_active)}
                                    disabled={loading === `toggle-operation-${op.id}`}
                                    className={`px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all ${
                                        op.is_active === 1 ? 'bg-red-50 text-red-500 border-red-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                    }`}
                                >
                                    {loading === `toggle-operation-${op.id}` ? <RefreshCw size={14} className="animate-spin" /> : op.is_active === 1 ? "Deactivate" : "Activate"}
                                </button>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Safety Measures Section */}
                <div className="grid grid-cols-1 gap-8">
                    <div className="bg-white rounded-3xl border border-gray-100 p-6">
                        <div className="flex items-center gap-2 mb-4">
                            <PlusCircle size={16} className="text-emerald-600" />
                            <h3 className="text-sm font-black text-gray-900 uppercase tracking-wider">
                                Add Safety Measure
                            </h3>
                        </div>
                        <form onSubmit={handleAddMeasure} className="flex gap-3">
                            <input
                                type="text"
                                value={newMeasure}
                                onChange={e => setNewMeasure(e.target.value)}
                                placeholder="Enter safety measure text..."
                                className="flex-1 px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-600 font-semibold text-gray-700 outline-none transition-all text-sm"
                                required
                            />
                            <button type="submit" disabled={loading === 'add-measure'}
                                className="flex items-center gap-2 px-8 py-3 bg-emerald-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 disabled:opacity-50 transition-all shadow-lg shadow-emerald-100">
                                {loading === 'add-measure' ? <RefreshCw size={16} className="animate-spin" /> : <Plus size={16} />}
                                Add
                            </button>
                        </form>
                    </div>

                    <div className="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                        <h4 className="font-black text-gray-900 text-sm uppercase tracking-tight mb-4">Safety Measures (Checkboxes)</h4>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {safetyMeasures.map(sm => (
                                <div key={sm.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                    <span className={`text-[11px] font-bold ${sm.is_active === 1 ? 'text-gray-700' : 'text-gray-400 line-through'}`}>{sm.measure_name}</span>
                                    <button
                                        onClick={() => handleToggle('measure', sm.id, sm.is_active)}
                                        disabled={loading === `toggle-measure-${sm.id}`}
                                        className={`p-1.5 rounded-lg transition-colors ${sm.is_active === 1 ? 'text-red-400 hover:bg-red-50' : 'text-emerald-500 hover:bg-emerald-50'}`}
                                    >
                                        {loading === `toggle-measure-${sm.id}` ? <RefreshCw size={12} className="animate-spin" /> : <Power size={12} />}
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

// ─── Main Settings Page ───────────────────────────────────────────────────────
const SettingsPage: React.FC = () => {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState<'add-user' | 'projects' | 'report-options' | 'ptw-options'>('add-user');

    const tabs = [
        { key: 'add-user' as const, label: t('settings.tabs.users', 'Add User'), icon: UserPlus },
        { key: 'projects' as const, label: t('settings.tabs.projects', 'Projects'), icon: FolderOpen },
        { key: 'report-options' as const, label: t('settings.tabs.reportOptions', 'Report Options'), icon: ListTree },
        { key: 'ptw-options' as const, label: t('settings.tabs.ptwOptions', 'PTW Options'), icon: ClipboardCheck },
    ];

    return (
        <div className="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">

            {/* Header */}
            <div className="flex items-center gap-4">
                <div className="p-3 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-200">
                    <Settings size={28} />
                </div>
                <div>
                    <h1 className="text-3xl font-black text-gray-900 tracking-tight">{t('nav.settings', 'Settings')}</h1>
                    <p className="text-sm font-medium text-gray-500 mt-1">{t('settings.subtitle', 'Manage users and project configuration')}</p>
                </div>
            </div>

            {/* Tab Switcher */}
            <div className="flex gap-2 bg-gray-100/80 p-1.5 rounded-2xl w-fit">
                {tabs.map(tab => {
                    const Icon = tab.icon;
                    const isActive = activeTab === tab.key;
                    return (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            className={`flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-black uppercase tracking-wider transition-all duration-200 ${
                                isActive
                                    ? 'bg-white text-blue-700 shadow-md shadow-gray-200'
                                    : 'text-gray-500 hover:text-gray-700'
                            }`}>
                            <Icon size={16} />
                            {tab.label}
                        </button>
                    );
                })}
            </div>

            {/* Tab Content */}
            <div className="animate-in fade-in duration-300" key={activeTab}>
                {activeTab === 'add-user' && <AddUserTab />}
                {activeTab === 'projects' && <ProjectsTab />}
                {activeTab === 'report-options' && <ReportOptionsTab />}
                {activeTab === 'ptw-options' && <PTWOptionsTab />}
            </div>
        </div>
    );
};

export default SettingsPage;
