import React, { useState, useEffect } from 'react';
import { useUsers, useUserDetail, usePermissions, useUpdateUser } from '../../application/hooks/useUsers';
import { useLookups } from '../../application/hooks/useLookups';
import { useTranslation } from 'react-i18next';
import api from '../../infrastructure/api/client';
import { 
    Shield, Search, Edit3, X, Save, Check,
    Lock, User, Mail, Briefcase, 
    ChevronRight, Key, Info, RefreshCcw
} from 'lucide-react';

const roleBadge = (type: number, t: any) => {
    const map: Record<number, { label: string; style: string }> = {
        1: { label: t('roles.admin', 'Admin'), style: 'bg-purple-100/50 text-purple-700 border-purple-200' },
        2: { label: t('roles.hse', 'HSE'), style: 'bg-blue-100/50 text-blue-700 border-blue-200' },
        3: { label: t('roles.operation', 'Operation'), style: 'bg-teal-100/50 text-teal-700 border-teal-200' },
    };
    const s = map[type] ?? { label: t('roles.unknown', 'Unknown'), style: 'bg-gray-100 text-gray-600 border-gray-200' };
    return <span className={`text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full border font-black ${s.style}`}>{s.label}</span>;
};

const UserManagementPage: React.FC = () => {
    const { t } = useTranslation();
    const { users, isLoading } = useUsers();
    const { departments: allDepartments } = useLookups();
    const [editingUserId, setEditingUserId] = useState<number | null>(null);
    const [formData, setFormData] = useState<any>(null);
    const [selectedPermissions, setSelectedPermissions] = useState<string[]>([]);
    
    const { data: userDetail, isLoading: isLoadingDetail } = useUserDetail(editingUserId);
    const { data: allPermsData } = usePermissions();
    const updateUser = useUpdateUser();

    // Reset form when modal closes or opens for a new user
    useEffect(() => {
        if (!editingUserId) {
            setFormData(null);
            setSelectedPermissions([]);
        }
    }, [editingUserId]);

    // Group permissions by page
    const groupedPermissions = allPermsData?.data?.reduce((acc: any, p: any) => {
        if (!acc[p.page]) acc[p.page] = [];
        acc[p.page].push(p);
        return acc;
    }, {}) || {};

    useEffect(() => {
        if (userDetail?.data && userDetail.data.user.id === editingUserId) {
            const u = userDetail.data.user;
            setFormData({
                id: u.id,
                username: u.username,
                userType: u.user_type,
                departmentId: u.department_id,
                status: u.user_status,
                editorName: u.editor_name,
                jobTitle: u.job_title,
                password: ''
            });
            setSelectedPermissions(userDetail.data.permissions || []);
        }
    }, [userDetail]);

    const handlePermissionToggle = (value: string) => {
        setSelectedPermissions(prev => 
            prev.includes(value) 
                ? prev.filter(p => p !== value) 
                : [...prev, value]
        );
    };

    const handleRoleChange = async (type: number) => {
        setFormData((prev: any) => ({ ...prev, userType: type }));
        
        if (window.confirm(t('users.loadDefaultsConfirm', "Do you want to load default permissions for this role?"))) {
            try {
                const response: any = await api.get(`/users/role_defaults?role_type=${type}`);
                if (response.data) {
                    setSelectedPermissions(response.data);
                }
            } catch (err) {
                console.error("Failed to load defaults", err);
            }
        }
    };

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            await updateUser.mutateAsync({
                ...formData,
                permissions: selectedPermissions
            });
            setEditingUserId(null);
            alert(t('users.updateSuccess', "User updated successfully!"));
        } catch (err) {
            alert(t('users.updateError', "Failed to update user"));
        }
    };

    return (
        <div className="max-w-full mx-auto space-y-8 animate-in fade-in duration-500">
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 flex items-center gap-3 tracking-tight">
                        <div className="p-2 bg-purple-600 text-white rounded-2xl shadow-lg shadow-purple-200">
                            <Shield size={28} />
                        </div>
                        {t('users.title')}
                    </h1>
                    <p className="text-sm font-medium text-gray-500 mt-2 ml-14">{t('users.subtitle')} • {t('users.totalActive', { count: users.length })}</p>
                </div>
            </div>

            <div className="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden relative">
                {isLoading ? (
                    <div className="p-10 space-y-4">
                        {[...Array(6)].map((_, i) => <div key={i} className="h-16 bg-gray-50 rounded-2xl animate-pulse" />)}
                    </div>
                ) : users.length === 0 ? (
                    <div className="text-center py-24 bg-gray-50/50">
                        <div className="w-20 h-20 bg-gray-100 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                            <Search size={40} className="text-gray-300" />
                        </div>
                        <p className="text-gray-500 font-bold uppercase tracking-widest text-sm">{t('users.noRecords', 'No identity records found')}</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="bg-gray-50/50 border-b border-gray-100">
                                    <th className="text-left px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 rtl:text-right">{t('users.account')}</th>
                                    <th className="text-left px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">{t('users.securityLevel')}</th>
                                    <th className="text-left px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 rtl:text-right">{t('users.assignment')}</th>
                                    <th className="text-left px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">{t('common.status')}</th>
                                    <th className="text-right px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 rtl:text-left">{t('common.actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {users.map((u) => (
                                    <tr key={u.id} className="hover:bg-purple-50/30 transition-all duration-300 group">
                                        <td className="px-8 py-5">
                                            <div className="flex items-center gap-4">
                                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 via-indigo-500 to-blue-500 flex items-center justify-center text-white text-sm font-black shadow-lg shadow-blue-100 shrink-0 group-hover:scale-110 transition-transform">
                                                    {u.username.substring(0, 2).toUpperCase()}
                                                </div>
                                                <div>
                                                    <div className="text-gray-900 font-black tracking-tight">{u.username}</div>
                                                    <div className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{u.editorName || t('users.systemUser', 'System User')}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-8 py-5 text-center">{roleBadge(u.userType, t)}</td>
                                        <td className="px-8 py-5">
                                            <div className="text-sm font-bold text-gray-700">{u.department}</div>
                                            <div className="text-[10px] font-bold text-gray-400 uppercase mt-0.5">{u.jobTitle || t('users.unassignedTitle', 'Unassigned Title')}</div>
                                        </td>
                                        <td className="px-8 py-5 text-center">
                                            <span className={`inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-colors ${
                                                u.status === 'Active' 
                                                    ? 'bg-emerald-50 text-emerald-600 border-emerald-100' 
                                                    : 'bg-gray-100 text-gray-400 border-gray-200'
                                            }`}>
                                                <span className={`w-2 h-2 rounded-full ${u.status === 'Active' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300'}`} />
                                                {u.status === 'Active' ? t('common.active', 'Active') : t('common.inactive', 'Inactive')}
                                            </span>
                                        </td>
                                        <td className="px-8 py-5 text-right rtl:text-left">
                                            <button 
                                                onClick={() => setEditingUserId(u.id)}
                                                className="p-2.5 bg-white text-gray-400 border border-gray-100 rounded-xl hover:bg-purple-600 hover:text-white hover:border-purple-600 hover:shadow-lg hover:shadow-purple-100 transition-all"
                                                title={t('common.edit')}
                                            >
                                                <Edit3 size={18} strokeWidth={2.5} />
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Edit Sidebar / Modal */}
            {editingUserId && (
                <div className="fixed inset-0 z-[100] flex justify-end">
                    <div className="absolute inset-0 bg-gray-900/40 backdrop-blur-sm animate-in fade-in duration-300" onClick={() => setEditingUserId(null)} />
                    
                    <div className="relative w-full max-w-2xl bg-white h-screen shadow-2xl overflow-y-auto animate-in slide-in-from-right rtl:slide-in-from-left duration-500 ease-out">
                        <div className="sticky top-0 z-20 bg-white border-b border-gray-100 px-8 py-6 flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <div className="p-3 bg-purple-50 text-purple-600 rounded-2xl">
                                    <User size={24} />
                                </div>
                                <div>
                                    <h2 className="text-xl font-black text-gray-900">{t('users.profileEngine')}</h2>
                                    <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">{t('users.accountId')}: #{editingUserId}</p>
                                </div>
                            </div>
                            <button 
                                onClick={() => setEditingUserId(null)}
                                className="p-2 text-gray-400 hover:bg-gray-100 rounded-xl transition-colors"
                            >
                                <X size={24} />
                            </button>
                        </div>

                        {isLoadingDetail || !formData ? (
                            <div className="p-12 flex flex-col items-center justify-center gap-6">
                                <RefreshCcw size={48} className="text-purple-600 animate-spin" />
                                <p className="text-gray-400 font-bold uppercase tracking-widest text-xs">{t('common.loading')}</p>
                            </div>
                        ) : (
                            <form onSubmit={handleSave} className="p-8 space-y-10">
                                {/* Basic Info Section */}
                                <div className="space-y-6">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Info size={16} className="text-purple-600" />
                                        <h3 className="text-sm font-black text-gray-900 uppercase tracking-wider">{t('users.identityDetails')}</h3>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-6">
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.username')}</label>
                                            <div className="relative">
                                                <User className="absolute left-4 rtl:left-auto rtl:right-4 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                                                <input 
                                                    type="text" 
                                                    value={formData.username}
                                                    onChange={e => setFormData({...formData, username: e.target.value})}
                                                    className="w-full pl-11 rtl:pl-4 rtl:pr-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-600 font-bold text-gray-700 outline-none transition-all"
                                                    required
                                                />
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.password')}</label>
                                            <div className="relative">
                                                <Lock className="absolute left-4 rtl:left-auto rtl:right-4 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                                                <input 
                                                    type="password" 
                                                    placeholder="••••••••"
                                                    value={formData.password}
                                                    onChange={e => setFormData({...formData, password: e.target.value})}
                                                    className="w-full pl-11 rtl:pl-4 rtl:pr-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-600 font-bold text-gray-700 outline-none transition-all"
                                                />
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.clearance')}</label>
                                            <select 
                                                value={formData.userType}
                                                onChange={e => handleRoleChange(Number(e.target.value))}
                                                className="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-600 font-bold text-gray-700 outline-none transition-all cursor-pointer appearance-none"
                                            >
                                                <option value={1}>{t('roles.admin')}</option>
                                                <option value={2}>{t('roles.hse')}</option>
                                                <option value={3}>{t('roles.operation')}</option>
                                            </select>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.unit')}</label>
                                            <select 
                                                value={formData.departmentId}
                                                onChange={e => setFormData({...formData, departmentId: e.target.value})}
                                                className="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-600 font-bold text-gray-700 outline-none transition-all cursor-pointer appearance-none"
                                            >
                                                <option value="">{t('common.public', 'Public/General')}</option>
                                                {allDepartments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                                            </select>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-6 pt-2">
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.fullName')}</label>
                                            <div className="relative">
                                                <Mail className="absolute left-4 rtl:left-auto rtl:right-4 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                                                <input 
                                                    type="text" 
                                                    value={formData.editorName}
                                                    onChange={e => setFormData({...formData, editorName: e.target.value})}
                                                    className="w-full pl-11 rtl:pl-4 rtl:pr-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-600 font-bold text-gray-700 outline-none transition-all"
                                                    required
                                                />
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{t('users.jobTitle')}</label>
                                            <div className="relative">
                                                <Briefcase className="absolute left-4 rtl:left-auto rtl:right-4 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                                                <input 
                                                    type="text" 
                                                    value={formData.jobTitle}
                                                    onChange={e => setFormData({...formData, jobTitle: e.target.value})}
                                                    className="w-full pl-11 rtl:pl-4 rtl:pr-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-600 font-bold text-gray-700 outline-none transition-all"
                                                    required
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4 bg-gray-50 p-4 rounded-3xl border border-gray-100">
                                        <div className="flex-1">
                                            <p className="text-xs font-black text-gray-900 uppercase tracking-tight">{t('users.activeStatus')}</p>
                                            <p className="text-[10px] text-gray-400 font-bold">{t('users.activeStatusSub', 'Grant or revoke system access immediately.')}</p>
                                        </div>
                                        <button 
                                            type="button"
                                            onClick={() => setFormData({...formData, status: formData.status === 1 ? 0 : 1})}
                                            className={`relative w-14 h-8 rounded-full transition-all duration-300 ring-4 ring-transparent focus:ring-purple-600/10 ${formData.status === 1 ? 'bg-emerald-500' : 'bg-gray-300'}`}
                                        >
                                            <div className={`absolute top-1 left-1 bg-white w-6 h-6 rounded-full shadow-md transition-transform duration-300 ${formData.status === 1 ? 'translate-x-6' : ''}`} />
                                        </button>
                                    </div>
                                </div>

                                {/* Permissions Section */}
                                <div className="space-y-6 pb-24">
                                    <div className="flex items-center justify-between mb-4">
                                        <div className="flex items-center gap-2">
                                            <Key size={16} className="text-purple-600" />
                                            <h3 className="text-sm font-black text-gray-900 uppercase tracking-wider">{t('users.permissions')}</h3>
                                        </div>
                                        <span className="text-[10px] font-black text-purple-600 bg-purple-50 px-3 py-1 rounded-full uppercase tracking-widest">
                                            {t('users.activeRules', { count: selectedPermissions.length })}
                                        </span>
                                    </div>

                                    <div className="grid grid-cols-1 gap-4">
                                        {Object.entries(groupedPermissions).map(([page, perms]: [string, any]) => (
                                            <div key={page} className="bg-white border border-gray-100 rounded-3xl p-5 space-y-4 hover:border-purple-200 transition-colors group">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-8 h-8 bg-gray-50 text-gray-400 group-hover:bg-purple-50 group-hover:text-purple-600 rounded-xl flex items-center justify-center transition-colors">
                                                        <ChevronRight size={16} className="rtl:rotate-180" />
                                                    </div>
                                                    <h4 className="text-xs font-black text-gray-800 uppercase tracking-widest">
                                                        {t(`modules.${page.split('.')[0]}`, page.split('.')[0].replace(/_/g, ' '))} {t('common.module', 'Module')}
                                                    </h4>
                                                </div>
                                                
                                                <div className="grid grid-cols-1 gap-2">
                                                    {perms.map((p: any) => (
                                                        <div 
                                                            key={p.value} 
                                                            onClick={() => handlePermissionToggle(p.value)}
                                                            className={`flex items-center justify-between p-3 rounded-2xl cursor-pointer transition-all ${
                                                                selectedPermissions.includes(p.value)
                                                                    ? 'bg-purple-50 border border-purple-100'
                                                                    : 'bg-white border border-transparent hover:bg-gray-50'
                                                            }`}
                                                        >
                                                            <div className="flex flex-col">
                                                                <span className={`text-[11px] font-bold ${selectedPermissions.includes(p.value) ? 'text-purple-700' : 'text-gray-600'}`}>
                                                                    {p.description}
                                                                </span>
                                                                <span className="text-[9px] font-medium text-gray-400/80">{t('common.command', 'Command')}: {p.action}</span>
                                                            </div>
                                                            <div className={`w-5 h-5 rounded-lg border-2 flex items-center justify-center transition-all ${
                                                                selectedPermissions.includes(p.value)
                                                                    ? 'bg-purple-600 border-purple-600 text-white'
                                                                    : 'border-gray-200 bg-white'
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

                                {/* Save Button */}
                                <div className="fixed bottom-0 right-0 rtl:left-0 w-full max-w-2xl bg-white/80 backdrop-blur-md border-t border-gray-100 p-8 flex gap-4 z-30">
                                    <button 
                                        type="submit"
                                        disabled={updateUser.isPending}
                                        className="flex-1 flex items-center justify-center gap-3 bg-purple-600 text-white px-8 py-4 rounded-3xl font-black uppercase tracking-widest text-xs hover:bg-purple-700 disabled:opacity-50 shadow-xl shadow-purple-100 transition-all hover:-translate-y-1 active:translate-y-0"
                                    >
                                        <Save size={18} />
                                        {updateUser.isPending ? t('common.saving', 'Propagating Changes...') : t('common.save')}
                                    </button>
                                    <button 
                                        type="button"
                                        onClick={() => setEditingUserId(null)}
                                        className="px-8 py-4 bg-gray-100 text-gray-400 rounded-3xl font-black uppercase tracking-widest text-xs hover:bg-gray-200 transition-all"
                                    >
                                        {t('common.cancel')}
                                    </button>
                                </div>
                            </form>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default UserManagementPage;
