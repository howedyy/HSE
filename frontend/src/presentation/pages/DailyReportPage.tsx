import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDailyReports } from '../../application/hooks/useDailyReports';
import { useLookups } from '../../application/hooks/useLookups';
import { useAuth } from '../context/AuthContext';
import { useTranslation } from 'react-i18next';
import api from '../../infrastructure/api/client';
import {
    FileText, Filter, ChevronLeft, ChevronRight,
    CheckCircle, Clock, Search, X, Plus, Eye, Trash2, Loader2, Image as ImageIcon, Mail, Check, Pencil, MessageSquare, Send
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
    const [emailingReport, setEmailingReport] = useState<any>(null);
    const [comments, setComments] = useState<any[]>([]);
    const [newComment, setNewComment] = useState('');
    const [commentImages, setCommentImages] = useState<File[]>([]);
    const [isLoadingComments, setIsLoadingComments] = useState(false);
    const [isSubmittingComment, setIsSubmittingComment] = useState(false);


    const { reports, total, totalPages, isLoading, refetch } = useDailyReports(filters, page);
    const { projects, departments, users } = useLookups();
    const canSubmit = hasPermission('dailyreport.php', 'submit');
    const canDelete = hasPermission('dailyreport_overview.php', 'delete');
    const canClose = hasPermission('dailyreport_overview.php', 'submit');
    const canSendEmail = hasPermission('dailyreport_overview.php', 'send_email') || hasPermission('dailyreport_overview.php', 'send email');
    const canAddComment = hasPermission('dailyreport_overview.php', 'add_comment');
    const canDeleteComment = hasPermission('dailyreport_overview.php', 'delete_comment');
    const canExport = hasPermission('dailyreport_overview.php', 'export_excel') || hasPermission('dailyreport_overview.php', 'export');


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

    const fetchComments = async (reportId: number) => {
        setIsLoadingComments(true);
        try {
            const data: any = await api.get(`/reports/get_comments?report_id=${reportId}`);
            setComments(Array.isArray(data) ? data : []);
        } catch (err) {
            console.error('Failed to fetch comments:', err);
        } finally {
            setIsLoadingComments(false);
        }
    };

    const handleAddComment = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newComment.trim() && commentImages.length === 0) return;

        setIsSubmittingComment(true);
        try {
            const formData = new FormData();
            formData.append('report_id', String(selectedReport.id));
            formData.append('comment_text', newComment);
            
            for (let i = 0; i < commentImages.length; i++) {
                formData.append('comment_image[]', commentImages[i]);
            }

            await api.post('/reports/add_comment', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setNewComment('');
            setCommentImages([]);
            fetchComments(selectedReport.id);
        } catch (err: any) {
            alert(err.response?.data?.message || 'Failed to add comment');
        } finally {
            setIsSubmittingComment(false);
        }
    };

    const handleDeleteComment = async (commentId: number) => {
        if (!window.confirm(t('dailyReport.confirm.deleteComment', 'Are you sure you want to delete this comment?'))) return;
        try {
            await api.delete(`/reports/delete_comment?id=${commentId}`);
            fetchComments(selectedReport.id);
        } catch (err: any) {
            alert(err.response?.data?.message || 'Failed to delete comment');
        }
    };

    const handleCommentImageChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        if (!e.target.files) return;
        const files = Array.from(e.target.files);
        const compressedFiles = await Promise.all(
            files.map(file => compressImage(file))
        );
        setCommentImages(prev => [...prev, ...compressedFiles as any]);
    };

    const removeCommentImage = (index: number) => {
        setCommentImages(prev => prev.filter((_, i) => i !== index));
    };

    const handleSelectReport = (report: any) => {
        setSelectedReport(report);
        if (report) {
            fetchComments(report.id);
        }
    };

    const handleExport = () => {
        const queryParams = new URLSearchParams(filters);
        const exportUrl = `${import.meta.env.VITE_API_BASE_URL}/export_daily_report_all.php?${queryParams.toString()}`;
        window.open(exportUrl, '_blank');
    };

    return (
        <div className="max-w-full mx-auto space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <FileText size={24} className="text-blue-600" /> {t('dailyReport.title')}
                    </h1>
                    <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">{t('dailyReport.subtitle', { count: total })}</p>
                </div>
                <div className="flex items-center gap-3">
                    <button
                        onClick={() => setShowFilters(!showFilters)}
                        className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all ${showFilters ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:bg-slate-950/50'}`}
                    >
                        {showFilters ? <X size={16} /> : <Filter size={16} />}
                        {showFilters ? t('common.filter') : t('common.filter')}
                    </button>
                    {canExport && (
                        <button
                            onClick={handleExport}
                            className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors shadow-sm"
                        >
                            <FileText size={16} /> {t('dailyReport.exportExcel')}
                        </button>
                    )}
                    {canSubmit && (
                        <button onClick={() => navigate('/reports/new')} className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">
                            <Plus size={16} /> {t('dailyReport.newReport')}
                        </button>
                    )}
                </div>
            </div>

            {showFilters && (
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-9 gap-4 animate-in slide-in-from-top-2 duration-200">
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.startDate')}</label>
                        <input type="date" onChange={(e) => updateFilter('startDate', e.target.value)} value={filters.startDate ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.endDate')}</label>
                        <input type="date" onChange={(e) => updateFilter('endDate', e.target.value)} value={filters.endDate ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.project')}</label>
                        <select onChange={(e) => updateFilter('project', e.target.value)} value={filters.project ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allProjects')}</option>
                            {projects.map(p => <option key={p.id} value={p.id}>{p.project_name}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.department')}</label>
                        <select onChange={(e) => updateFilter('department', e.target.value)} value={filters.department ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allDepartments')}</option>
                            {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('dailyReport.filters.createdBy')}</label>
                        <select onChange={(e) => updateFilter('created_by', e.target.value)} value={filters.created_by ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allUsers')}</option>
                            {users.map(u => <option key={u.id} value={u.id}>{u.username}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('dailyReport.filters.risk')}</label>
                        <select onChange={(e) => updateFilter('risk', e.target.value)} value={filters.risk ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allRisks')}</option>
                            <option value="عالية">{t('analytics.filters.high')}</option>
                            <option value="متوسطه">{t('analytics.filters.medium')}</option>
                            <option value="منخفضة">{t('analytics.filters.low')}</option>
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('common.status')}</label>
                        <select onChange={(e) => updateFilter('status', e.target.value)} value={filters.status ?? ''} className="px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{t('analytics.filters.allStatuses')}</option>
                            <option value="0">{t('analytics.filters.open')}</option>
                            <option value="1">{t('analytics.filters.resolved')}</option>
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">{t('dailyReport.filters.limit')}</label>
                        <select onChange={(e) => updateFilter('limit', e.target.value)} value={filters.limit ?? '20'} className="px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10">{t('dailyReport.filters.entries', { count: 10 })}</option>
                            <option value="20">{t('dailyReport.filters.entries', { count: 20 })}</option>
                            <option value="50">{t('dailyReport.filters.entries', { count: 50 })}</option>
                            <option value="100">{t('dailyReport.filters.entries', { count: 100 })}</option>
                        </select>
                    </div>
                    <div className="flex flex-col gap-1.5 justify-end">
                        <button 
                            onClick={() => { setFilters({}); setPage(1); }}
                            className="w-full py-2 rounded-xl border border-gray-200 dark:border-slate-700 text-xs font-bold text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:bg-slate-950/50 transition-colors bg-white dark:bg-slate-900 shadow-sm"
                        >
                            {t('common.reset')}
                        </button>
                    </div>
                </div>
            )}

            {/* Table */}
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 overflow-hidden shadow-sm">
                {isLoading ? (
                    <div className="p-10 space-y-3">
                        {[...Array(8)].map((_, i) => <div key={i} className="h-10 bg-gray-50 dark:bg-slate-950/50 rounded-lg animate-pulse" />)}
                    </div>
                ) : reports.length === 0 ? (
                    <div className="text-center py-20">
                        <Search size={40} className="mx-auto text-gray-200 mb-3" />
                        <p className="text-gray-500 dark:text-slate-400 font-medium">No reports match your filters</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto w-full">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 dark:bg-slate-950/50/50 border-b border-gray-100 dark:border-slate-800">
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.id')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.date')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.project')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.department')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.workType')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.risk')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.status')}</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.createdBy')}</th>
                                    <th className="text-center px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.details.commentsCount')}</th>
                                    <th className="text-right px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">{t('dailyReport.table.actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {reports.map((r: any) => (
                                    <tr key={r.id} className="hover:bg-blue-50/20 transition-colors">
                                        <td className="px-5 py-4 text-gray-400 font-mono text-[10px]">#{r.id}</td>
                                        <td className="px-5 py-4 text-gray-600 dark:text-gray-400">{new Date(r.date).toLocaleDateString(i18n.language === 'en' ? 'en-US' : 'ar-EG')}</td>
                                        <td className="px-5 py-4 text-gray-900 dark:text-gray-100 font-semibold">{r.project_name}</td>
                                        <td className="px-5 py-4 text-gray-600 dark:text-gray-400">{r.department_name}</td>
                                        <td className="px-5 py-4 text-gray-600 dark:text-gray-400 max-w-[150px] truncate">{r.work_type}</td>
                                        <td className="px-5 py-4">
                                            <span className={`text-[10px] px-2.5 py-1 rounded-full border font-bold ${riskColor(r.risk)}`}>
                                                {getRiskLabel(r.risk, t)}
                                            </span>
                                        </td>
                                        <td className="px-5 py-4">{statusBadge(r.report_status, t)}</td>
                                        <td className="px-5 py-4 text-gray-600 dark:text-gray-400 font-medium">{r.created_by}</td>
                                        <td className="px-5 py-4 text-center">
                                            {r.comments_count > 0 && (
                                                <div className="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold">
                                                    <MessageSquare size={10} />
                                                    {r.comments_count}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-5 py-4">
                                            <div className="flex items-center justify-end gap-1">
                                                <button
                                                    onClick={() => handleSelectReport(r)}

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
                                                        onClick={() => setEmailingReport(r)}
                                                        disabled={r.email_sent === 1 || isEmailing === r.id}
                                                        className={`p-2 rounded-xl transition-colors ${r.email_sent === 1
                                                            ? 'text-gray-400 bg-gray-100 dark:bg-slate-800 cursor-not-allowed'
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
                    <div className="flex items-center justify-between px-6 py-4 bg-gray-50 dark:bg-slate-950/50/30 border-t border-gray-100 dark:border-slate-800">
                        <p className="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                            {t('dailyReport.pagination.info', { page, totalPages, total })}
                        </p>
                        <div className="flex items-center gap-2">
                            <button disabled={page <= 1} onClick={() => setPage(p => p - 1)} className="p-2 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:bg-slate-950/50 disabled:opacity-30 transition-all"><ChevronLeft size={16} /></button>
                            <button disabled={page >= totalPages} onClick={() => setPage(p => p + 1)} className="p-2 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:bg-slate-950/50 disabled:opacity-30 transition-all"><ChevronRight size={16} /></button>
                        </div>
                    </div>
                )}
            </div>

            {/* View Details Modal */}
            {selectedReport && (
                <div className="fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4 overflow-y-auto">
                    <div className="bg-white dark:bg-slate-900 rounded-[2rem] w-[95%] md:w-full max-w-3xl my-4 md:my-8 shadow-2xl animate-in zoom-in-95 duration-300 overflow-hidden">
                        <div className="bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-white relative">
                            <button onClick={() => handleSelectReport(null)} className="absolute top-6 right-6 p-2 bg-white dark:bg-slate-900/10 hover:bg-white dark:bg-slate-900/20 rounded-full transition-colors text-white">

                                <X size={20} />
                            </button>
                            <div className="flex items-start gap-4">
                                <div className="p-3 bg-white dark:bg-slate-900/10 rounded-2xl">
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
                                    <p className="font-semibold text-gray-900 dark:text-gray-100">{selectedReport.project_name}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">{t('common.department')}</p>
                                    <p className="font-semibold text-gray-900 dark:text-gray-100">{selectedReport.department_name}</p>
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
                                    <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">{t('dailyReport.details.observationReport')}</h3>
                                    <div className="bg-gray-50 dark:bg-slate-950/50 rounded-2xl p-6 text-gray-700 dark:text-gray-300 leading-relaxed border border-gray-100 dark:border-slate-800 whitespace-pre-wrap">
                                        <div className="grid grid-cols-1 gap-4 text-sm">
                                            <div>
                                                <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{t('dailyReport.details.type')}</p>
                                                <p className="font-semibold text-gray-900 dark:text-gray-100">{selectedReport.observation}</p>
                                            </div>
                                            <div>
                                                <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{t('dailyReport.details.description')}</p>
                                                <p className="font-semibold text-gray-900 dark:text-gray-100">{selectedReport.work_type}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="bg-white dark:bg-slate-900 rounded-2xl p-6 text-gray-700 dark:text-gray-300 leading-relaxed border border-gray-100 dark:border-slate-800 whitespace-pre-wrap">
                                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">{t('dailyReport.details.detailedObservation')}</p>
                                        {selectedReport.description || t('dailyReport.messages.noDescription', 'No detailed description provided.')}
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">{t('dailyReport.details.safetyCompliance')}</h3>
                                    <div className="bg-gray-50 dark:bg-slate-950/50 rounded-2xl p-6 text-gray-700 dark:text-gray-300 border border-gray-100 dark:border-slate-800 italic">
                                        <p className="text-sm font-bold text-gray-900 dark:text-gray-100 underline mb-3 text-center">{selectedReport.observation_description}</p>
                                        <div>
                                            <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{t('dailyReport.details.correctiveAction')}</p>
                                            <p className="font-semibold text-gray-900 dark:text-gray-100">{selectedReport.operation_corrective}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Observation Images */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">{t('dailyReport.details.initialEvidence')}</h3>
                                <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    {(() => {
                                        try {
                                            const imgs = JSON.parse(selectedReport.image_upload);
                                            if (!imgs || imgs.length === 0) return <p className="text-sm text-gray-400 p-4 bg-gray-50 dark:bg-slate-950/50 rounded-2xl">{t('dailyReport.details.noImages')}</p>;
                                            return imgs.map((img: string, i: number) => (
                                                <div key={i} className="aspect-square rounded-2xl overflow-hidden border border-gray-200 dark:border-slate-700 group relative">
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
                                                    <div className="aspect-square rounded-2xl overflow-hidden border border-gray-200 dark:border-slate-700 group">
                                                        <img
                                                            src={`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/${selectedReport.image_upload}`}
                                                            alt=""
                                                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 cursor-zoom-in"
                                                            onClick={() => window.open(`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/${selectedReport.image_upload}`)}
                                                        />
                                                    </div>
                                                );
                                            }
                                            return <p className="text-sm text-gray-400 p-4 bg-gray-50 dark:bg-slate-950/50 rounded-2xl">{t('dailyReport.details.noImages')}</p>;
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

                            {/* Comments Section */}
                            <div className="pt-10 border-t border-gray-100 dark:border-slate-800 space-y-8">
                                <div className="flex items-center justify-between">
                                    <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <MessageSquare size={22} className="text-blue-600" />
                                        {t('dailyReport.details.comments', 'Discussion & Comments')}
                                    </h3>
                                    <span className="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                                        {comments.length} {t('dailyReport.details.commentsCount', 'Comments')}
                                    </span>
                                </div>

                                {/* Comment List */}
                                <div className="space-y-6 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                    {isLoadingComments ? (
                                        <div className="flex flex-col items-center py-10 text-gray-400">
                                            <Loader2 size={32} className="animate-spin mb-2" />
                                            <p className="text-sm font-medium">Loading conversation...</p>
                                        </div>
                                    ) : comments.length === 0 ? (
                                        <div className="text-center py-10 bg-gray-50 dark:bg-slate-950/50 rounded-[2rem] border border-dashed border-gray-200 dark:border-slate-700">
                                            <MessageSquare size={32} className="mx-auto text-gray-300 mb-2" />
                                            <p className="text-gray-500 dark:text-slate-400 text-sm">{t('dailyReport.messages.noComments', 'No comments yet. Start the discussion!')}</p>
                                        </div>
                                    ) : (
                                        comments.map((comment) => (
                                            <div key={comment.id} className={`flex gap-4 ${comment.is_owner ? 'flex-row-reverse' : ''}`}>
                                                <div className="flex-shrink-0">
                                                    <div className={`w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm ${comment.is_owner ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400'}`}>
                                                        {comment.username.substring(0, 1).toUpperCase()}
                                                    </div>
                                                </div>
                                                <div className={`flex-1 space-y-2 max-w-[85%] ${comment.is_owner ? 'text-right' : ''}`}>
                                                    <div className={`flex items-center gap-2 mb-1 ${comment.is_owner ? 'justify-end' : ''}`}>
                                                        <span className="font-bold text-sm text-gray-900 dark:text-gray-100">{comment.username}</span>
                                                        <span className="text-[10px] text-gray-400 font-medium">
                                                            {new Date(comment.created_at).toLocaleString(i18n.language === 'en' ? 'en-US' : 'ar-EG')}
                                                        </span>
                                                        {comment.is_owner && canDeleteComment && (
                                                            <button onClick={() => handleDeleteComment(comment.id)} className="p-1 text-red-400 hover:text-red-600 transition-colors">
                                                                <Trash2 size={12} />
                                                            </button>
                                                        )}
                                                    </div>
                                                    <div className={`p-4 rounded-2xl text-sm leading-relaxed shadow-sm ${comment.is_owner ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 text-gray-700 dark:text-gray-300 rounded-tl-none'}`}>
                                                        {comment.comment_text}
                                                    </div>
                                                    {comment.images && comment.images.length > 0 && (
                                                        <div className={`flex flex-wrap gap-2 mt-2 ${comment.is_owner ? 'justify-end' : ''}`}>
                                                            {comment.images.map((img: string, i: number) => (
                                                                <img
                                                                    key={i}
                                                                    src={`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/comments/${img}`}
                                                                    alt="Attachment"
                                                                    className="w-20 h-20 object-cover rounded-lg border border-gray-200 dark:border-slate-700 cursor-zoom-in hover:opacity-80 transition-opacity"
                                                                    onClick={() => window.open(`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/comments/${img}`)}
                                                                />
                                                            ))}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>

                                {/* Add Comment Form */}
                                {canAddComment && (
                                    <form onSubmit={handleAddComment} className="space-y-4 pt-4">
                                        <div className="relative">
                                            <textarea
                                                value={newComment}
                                                onChange={(e) => setNewComment(e.target.value)}
                                                placeholder={t('dailyReport.comments.placeholder', 'Write a comment...')}
                                                className="w-full px-5 py-4 rounded-[1.5rem] border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-950/50 focus:bg-white dark:bg-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all resize-none text-sm font-medium pr-14"
                                                rows={2}
                                            />
                                            <button
                                                type="submit"
                                                disabled={isSubmittingComment || (!newComment.trim() && commentImages.length === 0)}
                                                className="absolute right-3 bottom-3 p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 shadow-lg shadow-blue-500/30"
                                            >
                                                {isSubmittingComment ? <Loader2 size={18} className="animate-spin" /> : <Send size={18} />}
                                            </button>
                                        </div>

                                        {/* Image Attachments */}
                                        <div className="flex flex-wrap items-center gap-3">
                                            <label className="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 cursor-pointer hover:bg-gray-50 dark:bg-slate-950/50 transition-colors">
                                                <ImageIcon size={14} className="text-blue-600" />
                                                {t('dailyReport.comments.attachImage', 'Attach Images')}
                                                <input
                                                    type="file"
                                                    multiple
                                                    accept="image/*"
                                                    className="hidden"
                                                    onChange={handleCommentImageChange}
                                                />
                                            </label>

                                            {commentImages.map((file, index) => (
                                                <div key={index} className="relative group">
                                                    <img
                                                        src={URL.createObjectURL(file)}
                                                        alt="Preview"
                                                        className="w-12 h-12 object-cover rounded-xl border border-blue-200 shadow-sm"
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => removeCommentImage(index)}
                                                        className="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity"
                                                    >
                                                        <X size={10} />
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    </form>
                                )}
                            </div>
                        </div>


                        {/* Bottom Close Button */}
                        <div className="p-8 border-t border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-950/50 flex justify-end">
                            <button
                                onClick={() => handleSelectReport(null)}

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
                    <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] w-full max-w-lg shadow-2xl animate-in slide-in-from-bottom-8 duration-300">
                        <div className="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50 dark:bg-slate-950/50/50 rounded-t-[2.5rem]">
                            <div>
                                <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100">{t('dailyReport.resolution.title')}</h2>
                                <p className="text-xs text-gray-500 dark:text-slate-400 mt-1 font-medium italic">{t('dailyReport.resolution.caseId', { id: isClosing.id })}</p>
                            </div>
                            <button onClick={() => setIsClosing(null)} className="p-3 bg-white dark:bg-slate-900 hover:bg-gray-100 dark:bg-slate-800 rounded-2xl transition-all shadow-sm">
                                <X size={20} />
                            </button>
                        </div>
                        <form onSubmit={handleCloseSubmit} className="p-4 md:p-8 space-y-6 md:space-y-8">
                            <div className="space-y-3">
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">{t('dailyReport.resolution.notesLabel')}</label>
                                <textarea
                                    required
                                    value={closureNotes}
                                    onChange={e => setClosureNotes(e.target.value)}
                                    rows={4}
                                    className="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-950/50 focus:bg-white dark:bg-slate-900 focus:ring-4 focus:ring-green-500/10 focus:border-green-500 outline-none transition-all resize-none text-sm font-medium"
                                    placeholder={t('dailyReport.resolution.notesPlaceholder')}
                                />
                            </div>

                            <div className="space-y-3">
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">{t('dailyReport.resolution.photoLabel')}</label>
                                <div className="flex items-center gap-6 p-4 bg-gray-50 dark:bg-slate-950/50 rounded-2xl border border-dashed border-gray-200 dark:border-slate-700">
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
                                        <label className="w-24 h-24 rounded-2xl bg-white dark:bg-slate-900 border-2 border-dashed border-gray-200 dark:border-slate-700 flex flex-col items-center justify-center gap-1.5 cursor-pointer hover:border-green-400 hover:bg-green-50 transition-all hover:scale-105 group">
                                            <ImageIcon size={24} className="text-gray-300 group-hover:text-green-500 transition-colors" />
                                            <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest">{isCompressing ? '...' : t('dailyReport.resolution.upload')}</span>
                                            <input type="file" onChange={handleClosureImageChange} accept="image/*" className="hidden" disabled={isCompressing} />
                                        </label>
                                    )}
                                    <div className="flex-1">
                                        <p className="text-sm font-bold text-gray-700 dark:text-gray-300 leading-tight">{t('dailyReport.resolution.proofTitle')}</p>
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
            {/* Email Confirmation Modal */}
            {emailingReport && (
                <div className="fixed inset-0 bg-gray-900/60 backdrop-blur-md z-[60] flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] w-full max-w-2xl shadow-2xl animate-in zoom-in-95 duration-300 overflow-hidden flex flex-col max-h-[90vh]">
                        <div className="bg-gradient-to-r from-indigo-600 to-blue-700 p-8 text-white relative flex-shrink-0">
                            <button onClick={() => setEmailingReport(null)} className="absolute top-6 right-6 p-2 bg-white dark:bg-slate-900/10 hover:bg-white dark:bg-slate-900/20 rounded-full transition-colors text-white">
                                <X size={20} />
                            </button>
                            <div className="flex items-start gap-4">
                                <div className="p-3 bg-white dark:bg-slate-900/10 rounded-2xl">
                                    <Mail size={28} />
                                </div>
                                <div>
                                    <h2 className="text-xl font-bold">{t('dailyReport.email.title', 'Send Report via Email')}</h2>
                                    <p className="text-indigo-100/80 text-sm mt-1">{t('dailyReport.email.subtitle', 'Confirm sending report #')}{emailingReport.id}</p>
                                </div>
                            </div>
                        </div>

                        <div className="p-4 md:p-8 space-y-4 md:space-y-6 bg-white dark:bg-slate-900 overflow-y-auto custom-scrollbar">
                            <div className="bg-gray-50 dark:bg-slate-950/50 rounded-2xl p-5 border border-gray-100 dark:border-slate-800 space-y-3">
                                <div className="text-xs text-gray-600 dark:text-gray-400 space-y-2">
                                    <div className="bg-white dark:bg-slate-900 p-3 rounded-lg border border-gray-100 dark:border-slate-800 space-y-2">
                                        <div className="flex items-start gap-2">
                                            <span className="font-bold text-gray-400 uppercase tracking-widest text-[10px] w-12 pt-0.5">To:</span>
                                            <span className="font-medium text-gray-800 dark:text-gray-200 break-all">{emailingReport.project_department_email || <span className="text-gray-400 italic">No specific department email</span>}</span>
                                        </div>
                                        <div className="flex items-start gap-2">
                                            <span className="font-bold text-gray-400 uppercase tracking-widest text-[10px] w-12 pt-0.5">CC:</span>
                                            <div className="flex flex-col gap-1 font-medium text-gray-800 dark:text-gray-200 break-all">
                                                {emailingReport.project_email && <span>{emailingReport.project_email}</span>}
                                                {emailingReport.department_email && <span>{emailingReport.department_email}</span>}
                                                <span>Ahmed.ali@edaraproperty.net</span>
                                                <span>hse.manager@edaraproperty.net</span>
                                            </div>
                                        </div>
                                        <div className="flex items-start gap-2 pt-2 border-t border-gray-100 dark:border-slate-800">
                                            <span className="font-bold text-gray-400 uppercase tracking-widest text-[10px] w-12 pt-0.5">Subject:</span>
                                            <span className="font-medium text-gray-800 dark:text-gray-200 break-all">HSE Observation Report - {emailingReport.project_name} (ID: #{emailingReport.id})</span>
                                        </div>
                                    </div>
                                    
                                    <div className="mt-4">
                                        <span className="font-bold text-gray-400 uppercase tracking-widest text-[10px] block mb-2">Message Body Preview:</span>
                                        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 overflow-x-auto shadow-sm">
                                            <div style={{ fontFamily: 'Arial, sans-serif', minWidth: '400px', margin: '0 auto', border: '1px solid #ddd', borderRadius: '8px', backgroundColor: '#f9f9f9' }}>
                                                <div style={{ backgroundColor: '#2196F3', color: 'white', padding: '15px', borderRadius: '5px 5px 0 0', textAlign: 'center' }}>
                                                    <h2 style={{ margin: 0, fontSize: '16px' }}>New HSE Observation Report</h2>
                                                </div>
                                                <div style={{ backgroundColor: 'white', padding: '20px', borderRadius: '0 0 5px 5px' }}>
                                                    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '12px' }}>
                                                        <tbody>
                                                            <tr><td style={{ padding: '8px 0', borderBottom: '1px solid #eee', width: '100px' }}><b>ID:</b></td><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}>#{emailingReport.id}</td></tr>
                                                            <tr><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}><b>Project:</b></td><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}>{emailingReport.project_name}</td></tr>
                                                            <tr><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}><b>Department:</b></td><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}>{emailingReport.department_name}</td></tr>
                                                            <tr><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}><b>Risk:</b></td><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}>
                                                                <span style={{ color: emailingReport.risk === 'عالية' || emailingReport.risk === 'High' ? '#d9534f' : emailingReport.risk === 'متوسطه' || emailingReport.risk === 'Medium' ? '#f0ad4e' : '#5cb85c', fontWeight: 'bold' }}>
                                                                    {emailingReport.risk === 'عالية' || emailingReport.risk === 'High' ? '🔴 High Risk' : emailingReport.risk === 'متوسطه' || emailingReport.risk === 'Medium' ? '🟡 Medium Risk' : '🟢 Low Risk'}
                                                                </span>
                                                            </td></tr>
                                                            <tr><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}><b>Observation:</b></td><td style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}>{emailingReport.observation_description}</td></tr>
                                                        </tbody>
                                                    </table>
                                                    <div style={{ marginTop: '20px', padding: '15px', borderLeft: '4px solid #2196F3', background: '#e3f2fd', fontSize: '12px' }}>
                                                        <p style={{ margin: '0 0 10px 0' }}><b>Description:</b></p>
                                                        <p style={{ margin: 0, whiteSpace: 'pre-wrap' }}>{emailingReport.description}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="flex gap-3 pt-2">
                                <button
                                    onClick={() => setEmailingReport(null)}
                                    className="flex-1 py-3.5 px-4 bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 transition-colors shadow-sm"
                                >
                                    {t('common.cancel', 'Cancel')}
                                </button>
                                <button
                                    onClick={() => {
                                        const id = emailingReport.id;
                                        setEmailingReport(null);
                                        handleSendEmail(id);
                                    }}
                                    disabled={isEmailing === emailingReport.id}
                                    className="flex-1 py-3.5 px-4 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 flex justify-center items-center gap-2"
                                >
                                    {isEmailing === emailingReport.id ? <Loader2 size={18} className="animate-spin" /> : <><Send size={18} /> {t('common.send', 'Send')}</>}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DailyReportPage;
