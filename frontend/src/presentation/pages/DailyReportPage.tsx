import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDailyReports } from '../../application/hooks/useDailyReports';
import { useLookups } from '../../application/hooks/useLookups';
import { useAuth } from '../context/AuthContext';
import { useTranslation } from 'react-i18next';
import api from '../../infrastructure/api/client';
import {
    FileText, Filter, ChevronLeft, ChevronRight,
    CheckCircle, Clock, Search, X, Plus, Eye, Trash2, Loader2, Image as ImageIcon, Mail, Check, Pencil
} from 'lucide-react';
import { compressImage } from '../../shared/utils/imageCompression';

const riskColor = (risk: string) => {
    if (risk === 'عالية' || risk === 'High') return 'bg-red-100 text-red-700 border-red-200';
    if (risk === 'متوسطه' || risk === 'Medium') return 'bg-amber-100 text-amber-700 border-amber-200';
    return 'bg-green-100 text-green-700 border-green-200';
};

const getRiskLabel = (risk: string, t: any) => {
    if (risk === 'عالية' || risk === 'High') return t('analytics.filters.high');
    if (risk === 'متوسطه' || risk === 'Medium') return t('analytics.filters.medium');
    return t('analytics.filters.low');
};

const statusBadge = (status: number, t: any) =>
    status === 1
        ? <span className="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-green-50 text-green-700 border border-green-200 font-medium"><CheckCircle size={12} /> {t('dailyReport.status.resolved')}</span>
        : <span className="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-red-50 text-red-700 border border-red-200 font-medium"><Clock size={12} /> {t('dailyReport.status.open')}</span>;

const DailyReportPage: React.FC = () => {
    const { t, i18n } = useTranslation();
    const navigate = useNavigate();
    const { hasPermission, user } = useAuth();
    const [page, setPage] = useState(1);
    const [filters, setFilters] = useState<Record<string, string>>({});
    const [showFilters, setShowFilters] = useState(false);
    const [selectedReport, setSelectedReport] = useState<any>(null);
    const [isClosing, setIsClosing] = useState<any>(null);
    const [closureNotes, setClosureNotes] = useState('');
    const [closureImage, setClosureImage] = useState<File | null>(null);
    const [isSubmittingClosure, setIsSubmittingClosure] = useState(false);
    const [isCompressing, setIsCompressing] = useState(false);
    const [isEmailing, setIsEmailing] = useState<number | null>(null);

    const { reports, total, totalPages, isLoading, refetch } = useDailyReports(filters, page);
    const { projects, departments, users } = useLookups();
    const canSubmit = hasPermission('dailyreport.php', 'submit');
    const canDelete = hasPermission('dailyreport_overview.php', 'delete');
    const canClose = hasPermission('dailyreport_overview.php', 'submit');
    const canSendEmail = hasPermission('dailyreport_overview.php', 'send_email') || hasPermission('dailyreport_overview.php', 'send email');

    const updateFilter = (key: string, value: string) => {
        setPage(1);
        if (value === '') {
            const next = { ...filters };
            delete next[key];
            setFilters(next);
        } else {
            setFilters({ ...filters, [key]: value });
        }
    };

    const handleDelete = async (id: number) => {
        if (!window.confirm(t('dailyReport.confirm.delete'))) return;
        try {
            await api.delete(`/reports/delete?id=${id}`);
            refetch();
        } catch (err: any) {
            alert(err.response?.data?.message || t('dailyReport.messages.deleteFailed'));
        }
    };

    const handleSendEmail = async (id: number) => {
        if (!window.confirm(t('dailyReport.confirm.email'))) return;

        setIsEmailing(id);
        try {
            const response: any = await api.post('/reports/send_email', { report_id: id });

            if (response.success) {
                alert(t('dailyReport.messages.emailSuccess'));
                refetch();
            } else {
                alert(t('dailyReport.messages.emailFailed', { error: response.message || 'Unknown error' }));
            }
        } catch (err: any) {
            console.error('Email Dispatch Error:', err);
            const errorMsg = err.response?.data?.message || err.message || 'Connection failed';
            alert(t('dailyReport.messages.emailFailed', { error: errorMsg }));
        } finally {
            setIsEmailing(null);
        }
    };

    const handleClosureImageChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        if (!e.target.files?.[0]) return;
        setIsCompressing(true);
        const compressed = await compressImage(e.target.files[0]);
        setClosureImage(compressed);
        setIsCompressing(false);
    };

    const handleCloseSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmittingClosure(true);
        try {
            const formData = new FormData();
            formData.append('id', isClosing.id);
            formData.append('notes', closureNotes);
            if (closureImage) formData.append('image', closureImage);

            await api.post('/reports/close', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            setIsClosing(null);
            setClosureNotes('');
            setClosureImage(null);
            refetch();
        } catch (err: any) {
            alert(err.response?.data?.message || t('dailyReport.messages.closureFailed'));
        } finally {
            setIsSubmittingClosure(false);
        }
    };

    return (
        <div className="max-w-full mx-auto space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <FileText size={24} className="text-blue-600" /> {t('dailyReport.title')}
                    </h1>
                    <p className="text-sm text-gray-500 mt-1">{t('dailyReport.subtitle', { count: total })}</p>
                </div>
                <div className="flex items-center gap-3">
                    <button
                        onClick={() => setShowFilters(!showFilters)}
                        className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all ${showFilters ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}`}
                    >
                        {showFilters ? <X size={16} /> : <Filter size={16} />}
                        {showFilters ? t('common.filter') : t('common.filter')}
                    </button>
                    {canSubmit && (
                        <button onClick={() => navigate('/reports/new')} className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">
                            <Plus size={16} /> {t('dailyReport.newReport')}
                        </button>
                    )}
                </div>
            </div>

            {showFilters && (
                <div className="bg-white rounded-2xl border border-gray-100 p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-9 gap-4 animate-in slide-in-from-top-2 duration-200">
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.startDate')}</label>
                        <input type="date" onChange={(e) => updateFilter('startDate', e.target.value)} value={filters.startDate ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.endDate')}</label>
                        <input type="date" onChange={(e) => updateFilter('endDate', e.target.value)} value={filters.endDate ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.project')}</label>
                        <select onChange={(e) => updateFilter('project', e.target.value)} value={filters.project ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allProjects')}</option>
                            {projects.map(p => <option key={p.id} value={p.id}>{p.project_name}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.department')}</label>
                        <select onChange={(e) => updateFilter('department', e.target.value)} value={filters.department ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allDepartments')}</option>
                            {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('dailyReport.filters.createdBy')}</label>
                        <select onChange={(e) => updateFilter('created_by', e.target.value)} value={filters.created_by ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allUsers')}</option>
                            {users.map(u => <option key={u.id} value={u.id}>{u.username}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('dailyReport.filters.risk')}</label>
                        <select onChange={(e) => updateFilter('risk', e.target.value)} value={filters.risk ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allRisks')}</option>
                            <option value="عالية">{t('analytics.filters.high')}</option>
                            <option value="متوسطه">{t('analytics.filters.medium')}</option>
                            <option value="منخفضة">{t('analytics.filters.low')}</option>
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.status')}</label>
                        <select onChange={(e) => updateFilter('status', e.target.value)} value={filters.status ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allStatuses')}</option>
                            <option value="0">{t('analytics.filters.open')}</option>
                            <option value="1">{t('analytics.filters.resolved')}</option>
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('dailyReport.filters.limit')}</label>
                        <select onChange={(e) => updateFilter('limit', e.target.value)} value={filters.limit ?? '20'} className="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10">{t('dailyReport.filters.entries', { count: 10 })}</option>
                            <option value="20">{t('dailyReport.filters.entries', { count: 20 })}</option>
                            <option value="50">{t('dailyReport.filters.entries', { count: 50 })}</option>
                            <option value="100">{t('dailyReport.filters.entries', { count: 100 })}</option>
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5 justify-end">
                        <button 
                            onClick={() => { setFilters({}); setPage(1); }}
                            className="w-full py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-500 hover:bg-gray-50 transition-colors bg-white shadow-sm"
                        >
                            {t('common.reset')}
                        </button>
                    </div>
                </div>
            )}

            {/* Table */}
            <div className="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                {isLoading ? (
                    <div className="p-10 space-y-3">
                        {[...Array(8)].map((_, i) => <div key={i} className="h-10 bg-gray-50 rounded-lg animate-pulse" />)}
                    </div>
                ) : reports.length === 0 ? (
                    <div className="text-center py-20">
                        <Search size={40} className="mx-auto text-gray-200 mb-3" />
                        <p className="text-gray-500 font-medium">No reports match your filters</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50/50 border-b border-gray-100">
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.id')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.date')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.project')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.department')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.workType')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.risk')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.status')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.createdBy')}</th>
                                    <th className="text-right px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {reports.map((r: any) => (
                                    <tr key={r.id} className="hover:bg-blue-50/20 transition-colors">
                                        <td className="px-5 py-4 text-gray-400 font-mono text-[10px]">#{r.id}</td>
                                        <td className="px-5 py-4 text-gray-600">{new Date(r.date).toLocaleDateString(i18n.language === 'en' ? 'en-US' : 'ar-EG')}</td>
                                        <td className="px-5 py-4 text-gray-900 font-semibold">{r.project_name}</td>
                                        <td className="px-5 py-4 text-gray-600">{r.department_name}</td>
                                        <td className="px-5 py-4 text-gray-600 max-w-[150px] truncate">{r.work_type}</td>
                                        <td className="px-5 py-4">
                                            <span className={`text-[10px] px-2.5 py-1 rounded-full border font-bold ${riskColor(r.risk)}`}>
                                                {getRiskLabel(r.risk, t)}
                                            </span>
                                        </td>
                                        <td className="px-5 py-4">{statusBadge(r.report_status, t)}</td>
                                        <td className="px-5 py-4 text-gray-600 font-medium">{r.created_by}</td>
                                        <td className="px-5 py-4">
                                            <div className="flex items-center justify-end gap-1">
                                                <button
                                                    onClick={() => setSelectedReport(r)}
                                                    className="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors"
                                                    title={t('dailyReport.actions.viewDetails')}
                                                >
                                                    <Eye size={16} />
                                                </button>

                                                {((user?.id === r.user_id) || (user?.role === 1)) && r.report_status === 0 && (
                                                    <button
                                                        onClick={() => navigate(`/reports/edit/${r.id}`)}
                                                        className="p-2 text-amber-600 hover:bg-amber-50 rounded-xl transition-colors"
                                                        title={t('dailyReport.actions.editReport')}
                                                    >
                                                        <Pencil size={16} />
                                                    </button>
                                                )}

                                                {canSendEmail && (
                                                    <button
                                                        onClick={() => handleSendEmail(r.id)}
                                                        disabled={r.email_sent === 1 || isEmailing === r.id}
                                                        className={`p-2 rounded-xl transition-colors ${r.email_sent === 1
                                                            ? 'text-gray-400 bg-gray-100 cursor-not-allowed'
                                                            : 'text-indigo-600 hover:bg-indigo-50'
                                                            }`}
                                                        title={r.email_sent === 1 ? t('dailyReport.actions.mailSent') : t('dailyReport.actions.sendEmail')}
                                                    >
                                                        {isEmailing === r.id ? <Loader2 size={16} className="animate-spin" /> :
                                                            r.email_sent === 1 ? <Check size={14} /> : <Mail size={16} />}
                                                    </button>
                                                )}

                                                {r.report_status === 0 && canClose && (
                                                    <button
                                                        onClick={() => {
                                                            setIsClosing(r);
                                                            setClosureNotes('');
                                                            setClosureImage(null);
                                                        }}
                                                        className="p-2 text-green-600 hover:bg-green-50 rounded-xl transition-colors"
                                                        title={t('dailyReport.actions.resolveHazard')}
                                                    >
                                                        <CheckCircle size={16} />
                                                    </button>
                                                )}

                                                {canDelete && (
                                                    <button
                                                        onClick={() => handleDelete(r.id)}
                                                        className="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                                                        title={t('dailyReport.actions.deleteRecord')}
                                                    >
                                                        <Trash2 size={16} />
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination */}
                {totalPages > 1 && (
                    <div className="flex items-center justify-between px-6 py-4 bg-gray-50/30 border-t border-gray-100">
                        <p className="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                            {t('dailyReport.pagination.info', { page, totalPages, total })}
                        </p>
                        <div className="flex items-center gap-2">
                            <button disabled={page <= 1} onClick={() => setPage(p => p - 1)} className="p-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-30 transition-all"><ChevronLeft size={16} /></button>
                            <button disabled={page >= totalPages} onClick={() => setPage(p => p + 1)} className="p-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-30 transition-all"><ChevronRight size={16} /></button>
                        </div>
                    </div>
                )}
            </div>

            {/* View Details Modal */}
            {selectedReport && (
                <div className="fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4 overflow-y-auto">
                    <div className="bg-white rounded-[2rem] w-full max-w-3xl my-8 shadow-2xl animate-in zoom-in-95 duration-300 overflow-hidden">
                        <div className="bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-white relative">
                            <button onClick={() => setSelectedReport(null)} className="absolute top-6 right-6 p-2 bg-white/10 hover:bg-white/20 rounded-full transition-colors text-white">
                                <X size={20} />
                            </button>
                            <div className="flex items-start gap-4">
                                <div className="p-3 bg-white/10 rounded-2xl">
                                    <FileText size={28} />
                                </div>
                                <div>
                                    <h2 className="text-2xl font-bold">{t('dailyReport.details.title', { id: selectedReport.id })}</h2>
                                    <p className="text-blue-100/80 text-sm mt-1">{t('dailyReport.details.loggedBy', { name: selectedReport.created_by, date: new Date(selectedReport.date).toLocaleString(i18n.language === 'en' ? 'en-US' : 'ar-EG') })}</p>
                                </div>
                            </div>
                        </div>

                        <div className="p-8 space-y-10">
                            {/* Meta Grid */}
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-6">
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">{t('common.project')}</p>
                                    <p className="font-semibold text-gray-900">{selectedReport.project_name}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">{t('common.department')}</p>
                                    <p className="font-semibold text-gray-900">{selectedReport.department_name}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">{t('dailyReport.details.riskLevel')}</p>
                                    <span className={`text-[10px] px-2.5 py-1 rounded-full border font-black ${riskColor(selectedReport.risk)}`}>{getRiskLabel(selectedReport.risk, t)}</span>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">{t('common.status')}</p>
                                    {statusBadge(selectedReport.report_status, t)}
                                </div>
                            </div>

                            {/* Observation Details */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                <div className="space-y-4">
                                    <h3 className="text-lg font-bold text-gray-900">{t('dailyReport.details.observationReport')}</h3>
                                    <div className="bg-gray-50 rounded-2xl p-6 text-gray-700 leading-relaxed border border-gray-100 whitespace-pre-wrap">
                                        <div className="grid grid-cols-1 gap-4 text-sm">
                                            <div>
                                                <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{t('dailyReport.details.type')}</p>
                                                <p className="font-semibold text-gray-900">{selectedReport.observation}</p>
                                            </div>
                                            <div>
                                                <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{t('dailyReport.details.description')}</p>
                                                <p className="font-semibold text-gray-900">{selectedReport.work_type}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="bg-white rounded-2xl p-6 text-gray-700 leading-relaxed border border-gray-100 whitespace-pre-wrap">
                                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">{t('dailyReport.details.detailedObservation')}</p>
                                        {selectedReport.description || t('dailyReport.messages.noDescription', 'No detailed description provided.')}
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <h3 className="text-lg font-bold text-gray-900">{t('dailyReport.details.safetyCompliance')}</h3>
                                    <div className="bg-gray-50 rounded-2xl p-6 text-gray-700 border border-gray-100 italic">
                                        <p className="text-sm font-bold text-gray-900 underline mb-3 text-center">{selectedReport.observation_description}</p>
                                        <div>
                                            <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{t('dailyReport.details.correctiveAction')}</p>
                                            <p className="font-semibold text-gray-900">{selectedReport.operation_corrective}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Observation Images */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-bold text-gray-900">{t('dailyReport.details.initialEvidence')}</h3>
                                <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    {(() => {
                                        try {
                                            const imgs = JSON.parse(selectedReport.image_upload);
                                            if (!imgs || imgs.length === 0) return <p className="text-sm text-gray-400 p-4 bg-gray-50 rounded-2xl">{t('dailyReport.details.noImages')}</p>;
                                            return imgs.map((img: string, i: number) => (
                                                <div key={i} className="aspect-square rounded-2xl overflow-hidden border border-gray-200 group relative">
                                                    <img
                                                        src={`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/${img}`}
                                                        alt=""
                                                        className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 cursor-zoom-in"
                                                        onClick={() => window.open(`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/${img}`)}
                                                    />
                                                </div>
                                            ));
                                        } catch {
                                            if (selectedReport.image_upload) {
                                                return (
                                                    <div className="aspect-square rounded-2xl overflow-hidden border border-gray-200 group">
                                                        <img
                                                            src={`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/${selectedReport.image_upload}`}
                                                            alt=""
                                                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 cursor-zoom-in"
                                                            onClick={() => window.open(`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/${selectedReport.image_upload}`)}
                                                        />
                                                    </div>
                                                );
                                            }
                                            return <p className="text-sm text-gray-400 p-4 bg-gray-50 rounded-2xl">{t('dailyReport.details.noImages')}</p>;
                                        }
                                    })()}
                                </div>
                            </div>

                            {/* Closure Section (If Resolved) */}
                            {selectedReport.report_status === 1 && (
                                <div className="p-8 bg-green-50 rounded-[2.5rem] border border-green-100 space-y-6">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 bg-green-600 text-white rounded-xl flex items-center justify-center">
                                            <CheckCircle size={20} />
                                        </div>
                                        <h3 className="text-xl font-bold text-green-900">{t('dailyReport.details.resolutionVerified')}</h3>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                                        {selectedReport.closure_image && (
                                            <div className="aspect-video rounded-3xl overflow-hidden border-4 border-white shadow-xl shadow-green-900/10">
                                                <img
                                                    src={`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/closures/${selectedReport.closure_image}`}
                                                    alt="Proof"
                                                    className="w-full h-full object-cover"
                                                />
                                            </div>
                                        )}
                                        <div className="space-y-4">
                                            <div>
                                                <p className="text-[10px] font-black text-green-600 uppercase tracking-widest mb-1.5">{t('dailyReport.details.actionTaken')}</p>
                                                <p className="text-green-900 leading-relaxed font-semibold">{selectedReport.closure_notes || t('dailyReport.status.resolved') + '.'}</p>
                                            </div>
                                            <div className="pt-4 border-t border-green-200/50">
                                                <p className="text-[10px] font-bold text-green-700/60 uppercase tracking-widest">{t('dailyReport.details.closedBy')}</p>
                                                <p className="text-sm text-green-900 font-bold mt-1 text-right italic">— {selectedReport.closed_by_username || 'System'}</p>
                                                <p className="text-[10px] text-green-700/40 text-right">{new Date(selectedReport.closed_at).toLocaleString(i18n.language === 'en' ? 'en-US' : 'ar-EG')}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Bottom Close Button */}
                        <div className="p-8 border-t border-gray-100 bg-gray-50 flex justify-end">
                            <button
                                onClick={() => setSelectedReport(null)}
                                className="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition-colors shadow-lg shadow-gray-200"
                            >
                                {t('dailyReport.details.closeDetails')}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Close Observation Modal */}
            {isClosing && (
                <div className="fixed inset-0 bg-gray-900/60 backdrop-blur-md z-[60] flex items-center justify-center p-4">
                    <div className="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl animate-in slide-in-from-bottom-8 duration-300">
                        <div className="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50 rounded-t-[2.5rem]">
                            <div>
                                <h2 className="text-2xl font-bold text-gray-900">{t('dailyReport.resolution.title')}</h2>
                                <p className="text-xs text-gray-500 mt-1 font-medium italic">{t('dailyReport.resolution.caseId', { id: isClosing.id })}</p>
                            </div>
                            <button onClick={() => setIsClosing(null)} className="p-3 bg-white hover:bg-gray-100 rounded-2xl transition-all shadow-sm">
                                <X size={20} />
                            </button>
                        </div>
                        <form onSubmit={handleCloseSubmit} className="p-8 space-y-8">
                            <div className="space-y-3">
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">{t('dailyReport.resolution.notesLabel')}</label>
                                <textarea
                                    required
                                    value={closureNotes}
                                    onChange={e => setClosureNotes(e.target.value)}
                                    rows={4}
                                    className="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 outline-none transition-all resize-none text-sm font-medium"
                                    placeholder={t('dailyReport.resolution.notesPlaceholder')}
                                />
                            </div>

                            <div className="space-y-3">
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">{t('dailyReport.resolution.photoLabel')}</label>
                                <div className="flex items-center gap-6 p-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                    {closureImage ? (
                                        <div className="relative w-24 h-24 rounded-2xl overflow-hidden border-2 border-green-500 shadow-xl shadow-green-500/20">
                                            <img src={URL.createObjectURL(closureImage)} alt="preview" className="w-full h-full object-cover" />
                                            <button
                                                type="button"
                                                onClick={() => setClosureImage(null)}
                                                className="absolute top-1.5 right-1.5 p-1 bg-red-500 text-white rounded-lg shadow-lg"
                                            >
                                                <X size={12} />
                                            </button>
                                        </div>
                                    ) : (
                                        <label className="w-24 h-24 rounded-2xl bg-white border-2 border-dashed border-gray-200 flex flex-col items-center justify-center gap-1.5 cursor-pointer hover:border-green-400 hover:bg-green-50 transition-all hover:scale-105 group">
                                            <ImageIcon size={24} className="text-gray-300 group-hover:text-green-500 transition-colors" />
                                            <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest">{isCompressing ? '...' : t('dailyReport.resolution.upload')}</span>
                                            <input type="file" onChange={handleClosureImageChange} accept="image/*" className="hidden" disabled={isCompressing} />
                                        </label>
                                    )}
                                    <div className="flex-1">
                                        <p className="text-sm font-bold text-gray-700 leading-tight">{t('dailyReport.resolution.proofTitle')}</p>
                                        <p className="text-[10px] text-gray-400 mt-1.5 font-medium italic">{t('dailyReport.resolution.proofSub')}</p>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={isSubmittingClosure || isCompressing}
                                className="w-full py-5 bg-green-600 text-white rounded-[1.25rem] font-black text-xs uppercase tracking-widest hover:bg-green-700 shadow-xl shadow-green-500/30 disabled:opacity-50 transition-all flex items-center justify-center gap-3 active:scale-95"
                            >
                                {isSubmittingClosure ? <Loader2 size={18} className="animate-spin" /> : <><CheckCircle size={18} /> {t('dailyReport.resolution.submit')}</>}
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DailyReportPage;
