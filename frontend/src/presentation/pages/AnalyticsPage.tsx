import React, { useState, useEffect } from 'react';
import api from '../../infrastructure/api/client';
import { useLookups } from '../../application/hooks/useLookups';
import { useTranslation } from 'react-i18next';
import {
    BarChart2, PieChart, AlertTriangle,
    Filter, RefreshCw, Clock, Building,
    Calendar, CheckCircle, Mail, Send, X, Loader2
} from 'lucide-react';

const AnalyticsPage: React.FC = () => {
    const { t, i18n } = useTranslation();
    const { projects, departments: allDepartments, users } = useLookups();
    const [stats, setStats] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState<Record<string, string>>({ 
        project: '', 
        department: '',
        risk: '',
        status: '',
        created_by: '',
        startDate: '',
        endDate: ''
    });

    const [emailingReport, setEmailingReport] = useState<any>(null);
    const [isEmailing, setIsEmailing] = useState<number | null>(null);

    const handleSendEmail = async (id: number) => {
        try {
            setIsEmailing(id);
            await api.post('/reports/send_email', { report_id: id, is_escalation: true });
            alert(t('dailyReport.email.success', 'Email sent successfully'));
            fetchAnalytics();
        } catch (error) {
            console.error('Email failed', error);
            alert(t('dailyReport.email.error', 'Failed to send email'));
        } finally {
            setIsEmailing(null);
        }
    };

    const fetchAnalytics = async () => {
        setLoading(true);
        try {
            const query = new URLSearchParams(filters).toString();
            const data = await api.get(`/reports/analytics?${query}`);
            setStats(data);
        } catch (error) {
            console.error('Analytics Error:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchAnalytics();
    }, [filters]);

    // Risk Colors
    const riskColors: Record<string, string> = {
        'عالية': '#ef4444', 'High': '#ef4444',
        'متوسطه': '#fbbf24', 'Medium': '#fbbf24',
        'منخفضة': '#10b981', 'Low': '#10b981'
    };

    if (loading && !stats) return (
        <div className="flex flex-col items-center justify-center h-screen gap-4">
            <RefreshCw className="animate-spin text-blue-600" size={48} />
            <p className="text-gray-500 dark:text-slate-400 font-medium tracking-widest uppercase text-xs">{t('common.loading')}</p>
        </div>
    );

    if (!stats) return (
        <div className="flex flex-col items-center justify-center min-h-[60vh] gap-6 p-8">
            <div className="w-20 h-20 bg-red-50 text-red-500 rounded-[2rem] flex items-center justify-center">
                <AlertTriangle size={40} />
            </div>
            <div className="text-center space-y-2">
                <h3 className="text-xl font-black text-gray-900 dark:text-gray-100">{t('analytics.syncFailed')}</h3>
                <p className="text-gray-500 dark:text-slate-400 font-medium">{t('analytics.syncError')}</p>
            </div>
            <button
                onClick={fetchAnalytics}
                className="px-8 py-3 bg-gray-900 text-white rounded-2xl font-bold hover:bg-gray-800 transition-all shadow-xl shadow-gray-200 flex items-center gap-2"
            >
                <RefreshCw size={18} />
                {t('ptw.retry')}
            </button>
        </div>
    );

    return (
        <div className="max-w-7xl mx-auto space-y-4 md:space-y-8 animate-in fade-in duration-700">
            {/* Header & Advanced Filter Bar */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tight">{t('analytics.title')}</h1>
                    <p className="text-gray-500 dark:text-slate-400 mt-1 font-medium">{t('analytics.subtitle')}</p>
                </div>

                <div className="flex flex-wrap items-center gap-3 bg-white dark:bg-slate-900 p-3 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-x-auto max-w-full">
                    <div className="flex items-center gap-2 px-3 border-r border-gray-100 dark:border-slate-800 shrink-0">
                        <Filter size={16} className="text-blue-600" />
                        <span className="text-xs font-bold uppercase tracking-widest text-gray-400">{t('common.filter')}</span>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase ml-1">{t('analytics.filters.dateRange')}</label>
                        <div className="flex items-center gap-2">
                            <input 
                                type="date" 
                                value={filters.startDate} 
                                onChange={e => setFilters(prev => ({ ...prev, startDate: e.target.value }))}
                                className="text-xs bg-gray-50 dark:bg-slate-950/50 border-none rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none font-semibold"
                            />
                            <span className="text-gray-300">-</span>
                            <input 
                                type="date" 
                                value={filters.endDate} 
                                onChange={e => setFilters(prev => ({ ...prev, endDate: e.target.value }))}
                                className="text-xs bg-gray-50 dark:bg-slate-950/50 border-none rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none font-semibold"
                            />
                        </div>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase ml-1">{t('common.project')}</label>
                        <select
                            value={filters.project}
                            onChange={e => setFilters(prev => ({ ...prev, project: e.target.value }))}
                            className="text-xs bg-gray-50 dark:bg-slate-950/50 border-none rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none font-semibold min-w-[120px]"
                        >
                            <option value="">{t('analytics.filters.allProjects')}</option>
                            {projects.map(p => <option key={p.id} value={p.id}>{p.project_name}</option>)}
                        </select>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase ml-1">{t('common.department')}</label>
                        <select
                            value={filters.department}
                            onChange={e => setFilters(prev => ({ ...prev, department: e.target.value }))}
                            className="text-xs bg-gray-50 dark:bg-slate-950/50 border-none rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none font-semibold min-w-[120px]"
                        >
                            <option value="">{t('analytics.filters.allDepartments')}</option>
                            {allDepartments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                        </select>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase ml-1">{t('ptw.dangerous')}</label>
                        <select
                            value={filters.risk}
                            onChange={e => setFilters(prev => ({ ...prev, risk: e.target.value }))}
                            className="text-xs bg-gray-50 dark:bg-slate-950/50 border-none rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none font-semibold min-w-[100px]"
                        >
                            <option value="">{t('analytics.filters.allRisks')}</option>
                            <option value="عالية">{t('analytics.filters.high')}</option>
                            <option value="متوسطه">{t('analytics.filters.medium')}</option>
                            <option value="منخفضة">{t('analytics.filters.low')}</option>
                        </select>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase ml-1">{t('common.status')}</label>
                        <select
                            value={filters.status}
                            onChange={e => setFilters(prev => ({ ...prev, status: e.target.value }))}
                            className="text-xs bg-gray-50 dark:bg-slate-950/50 border-none rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none font-semibold min-w-[100px]"
                        >
                            <option value="">{t('analytics.filters.allStatuses')}</option>
                            <option value="0">{t('analytics.filters.open')}</option>
                            <option value="1">{t('analytics.filters.resolved')}</option>
                        </select>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase ml-1">{t('users.systemUser')}</label>
                        <select
                            value={filters.created_by}
                            onChange={e => setFilters(prev => ({ ...prev, created_by: e.target.value }))}
                            className="text-xs bg-gray-50 dark:bg-slate-950/50 border-none rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none font-semibold min-w-[120px]"
                        >
                            <option value="">{t('analytics.filters.allUsers')}</option>
                            {users.map(u => <option key={u.id} value={u.id}>{u.username}</option>)}
                        </select>
                    </div>

                    <button
                        onClick={() => setFilters({ project: '', department: '', risk: '', status: '', created_by: '', startDate: '', endDate: '' })}
                        className="p-2 bg-gray-50 dark:bg-slate-950/50 text-gray-400 rounded-xl hover:bg-red-50 hover:text-red-500 transition-colors mt-auto"
                        title={t('common.reset')}
                    >
                        <RefreshCw size={18} />
                    </button>
                    
                    <button
                        onClick={fetchAnalytics}
                        className="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors mt-auto"
                        title={t('ptw.retry')}
                    >
                        <RefreshCw size={18} className={loading ? 'animate-spin' : ''} />
                    </button>
                </div>
            </div>

            {/* Quick Metrics Bar */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-gray-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <AlertTriangle size={80} className="text-red-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">{t('analytics.metrics.overdue')}</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-red-600 leading-none">{stats.overdue.count}</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-red-400 leading-none mb-1">{t('analytics.metrics.unresolved')}</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none">{t('analytics.metrics.timeExceeded')}</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-gray-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <CheckCircle size={80} className="text-emerald-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">{t('analytics.metrics.total')}</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-gray-900 dark:text-gray-100 leading-none">
                            {stats.byRisk.reduce((acc: number, r: any) => acc + r.count, 0)}
                        </span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-emerald-500 leading-none mb-1">{t('analytics.metrics.cumulative')}</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none">{t('analytics.metrics.observations')}</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-gray-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <BarChart2 size={80} className="text-violet-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">{t('analytics.metrics.focus')}</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-violet-600 leading-none">{stats.byDepartment.length}</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-violet-400 leading-none mb-1">{t('analytics.metrics.active')}</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none">{t('analytics.metrics.safetyUnits')}</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-gray-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <Calendar size={80} className="text-blue-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">{t('analytics.metrics.coverage')}</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-blue-600 leading-none">30+</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-blue-400 leading-none mb-1">{t('analytics.metrics.historical')}</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none">{t('analytics.metrics.dayAnalysis')}</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Charts Row */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* 1. Department Safety Engagement */}
                <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-gray-100 dark:border-slate-800 shadow-sm p-4 md:p-8 space-y-6 md:space-y-8">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                                <Building size={24} />
                            </div>
                            <div>
                                <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">{t('analytics.charts.distribution')}</h3>
                                <p className="text-xs text-gray-400 font-medium">{t('analytics.charts.distributionSub')}</p>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6">
                        {stats.byDepartment.map((dept: any, i: number) => {
                            const maxValue = Math.max(...stats.byDepartment.map((d: any) => d.count), 1);
                            const width = (dept.count / maxValue) * 100;
                            return (
                                <div key={i} className="group cursor-default">
                                    <div className="flex items-center justify-between text-sm mb-2">
                                        <span className="font-bold text-gray-700 dark:text-gray-300 group-hover:text-blue-600 transition-colors uppercase tracking-tight">{dept.name}</span>
                                        <span className="px-3 py-1 bg-gray-50 dark:bg-slate-950/50 rounded-lg font-black text-blue-600">{dept.count}</span>
                                    </div>
                                    <div className="h-3 w-full bg-gray-50 dark:bg-slate-950/50 rounded-full overflow-hidden border border-gray-100 dark:border-slate-800">
                                        <div
                                            className="h-full bg-gradient-to-r from-blue-600 to-indigo-500 rounded-full transition-all duration-1000 ease-out"
                                            style={{ width: `${width}%`, transitionDelay: `${i * 100}ms` }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* 2. Risk Profile */}
                <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-gray-100 dark:border-slate-800 shadow-sm p-8 space-y-10">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center">
                            <PieChart size={24} />
                        </div>
                        <div>
                            <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">{t('analytics.charts.riskMatrix')}</h3>
                            <p className="text-xs text-gray-400 font-medium">{t('analytics.charts.riskMatrixSub')}</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 items-center gap-12">
                        {/* Circle Visualization */}
                        <div className="relative w-48 h-48 mx-auto">
                            <svg className="w-full h-full -rotate-90" viewBox="0 0 32 32">
                                <circle cx="16" cy="16" r="14" fill="transparent" stroke="#f8fafc" strokeWidth="3" />
                                {(() => {
                                    const total = stats.byRisk.reduce((acc: number, r: any) => acc + r.count, 0);
                                    if (total === 0) return null;
                                    let offset = 0;
                                    return stats.byRisk.map((r: any, i: number) => {
                                        const dash = (r.count / total) * 88; // 2 * pi * r = 87.9
                                        const circleOffset = offset;
                                        offset += dash;
                                        return (
                                            <circle
                                                key={i}
                                                cx="16" cy="16" r="14" fill="transparent"
                                                stroke={riskColors[r.risk] || '#ddd'}
                                                strokeWidth="3.5"
                                                strokeDasharray={`${dash} 88`}
                                                strokeDashoffset={-circleOffset}
                                                className="transition-all duration-1000 ease-in-out"
                                            />
                                        );
                                    });
                                })()}
                            </svg>
                            <div className="absolute inset-0 flex flex-col items-center justify-center">
                                <span className="text-2xl font-black text-gray-900 dark:text-gray-100 leading-none">
                                    {stats.byRisk.length}
                                </span>
                                <span className="text-[8px] font-black text-gray-400 uppercase tracking-widest mt-1">{t('analytics.charts.riskLevels')}</span>
                            </div>
                        </div>

                        {/* Legend */}
                        <div className="space-y-5">
                            {stats.byRisk.map((r: any, i: number) => (
                                <div key={i} className="flex items-center justify-between p-4 rounded-2xl bg-gray-50 dark:bg-slate-950/50/50 border border-gray-100 dark:border-slate-800 group hover:border-blue-200 transition-all">
                                    <div className="flex items-center gap-3">
                                        <div className="w-4 h-4 rounded-md" style={{ backgroundColor: riskColors[r.risk] }} />
                                        <span className="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-tight">
                                            {r.risk === 'عالية' || r.risk === 'High' ? t('analytics.filters.high') :
                                             r.risk === 'متوسطه' || r.risk === 'Medium' ? t('analytics.filters.medium') :
                                             t('analytics.filters.low')}
                                        </span>
                                    </div>
                                    <span className="text-sm font-black text-gray-900 dark:text-gray-100">{r.count}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Overdue Reports Advanced View */}
            <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div className="p-8 border-b border-gray-50 bg-gradient-to-r from-red-50/50 to-white flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-red-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                            <Clock size={24} />
                        </div>
                        <div>
                            <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">{t('analytics.overdue.title')}</h3>
                            <p className="text-xs text-red-500 font-bold uppercase tracking-widest mt-0.5 animate-pulse">
                                {t('analytics.overdue.subtitle', { count: stats.overdue.count })}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto w-full">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-gray-50 dark:bg-slate-950/50/50 border-b border-gray-100 dark:border-slate-800">
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">{t('ptw.table.idDate')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">{t('ptw.table.projectDept')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">{t('ptw.dangerous')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">{t('analytics.overdue.delayHours')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">{t('common.status')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">{t('common.actions', 'Actions')}</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {stats.overdue.items.length > 0 ? (
                                stats.overdue.items.map((r: any, i: number) => (
                                    <tr key={i} className="hover:bg-gray-50 dark:bg-slate-950/50/30 transition-colors group">
                                        <td className="px-8 py-6">
                                            <div className="font-black text-gray-900 dark:text-gray-100 group-hover:text-blue-600 transition-colors">#{r.id}</div>
                                            <div className="text-xs text-gray-400 font-medium mt-1">
                                                {new Date(r.date).toLocaleDateString(i18n.language === 'en' ? 'en-US' : 'ar-EG')}
                                            </div>
                                        </td>
                                        <td className="px-8 py-6">
                                            <div className="font-bold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-tight">{r.project_name}</div>
                                            <div className="flex items-center gap-1.5 text-xs text-gray-400 font-semibold mt-1">
                                                <div className="w-1.5 h-1.5 rounded-full bg-blue-400" />
                                                {r.department_name} • {r.created_by}
                                            </div>
                                        </td>
                                        <td className="px-8 py-6 text-center">
                                            <span
                                                className="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-gray-100 dark:border-slate-800"
                                                style={{ color: riskColors[r.risk], backgroundColor: `${riskColors[r.risk]}10` }}
                                            >
                                                {r.risk === 'عالية' || r.risk === 'High' ? t('analytics.filters.high') :
                                                 r.risk === 'متوسطه' || r.risk === 'Medium' ? t('analytics.filters.medium') :
                                                 t('analytics.filters.low')}
                                            </span>
                                        </td>
                                        <td className="px-8 py-6 text-center">
                                            <div className="font-black text-gray-900 dark:text-gray-100 group-hover:scale-110 transition-transform origin-center">{r.delay_hours}h</div>
                                            <div className="text-[9px] font-bold text-red-500 uppercase mt-0.5">{t('analytics.overdue.late', { hours: r.exceeded_by })}</div>
                                        </td>
                                        <td className="px-8 py-6 text-right">
                                            <div className="flex items-center justify-end gap-2 text-xs font-black text-red-600 bg-red-50/50 px-3 py-2 rounded-xl border border-red-100 ml-auto w-fit">
                                                <AlertTriangle size={14} />
                                                {t('analytics.overdue.nonCompliance')}
                                            </div>
                                        </td>
                                        <td className="px-8 py-6 text-center">
                                            <button 
                                                onClick={() => setEmailingReport(r)}
                                                className="p-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-100 transition-colors shadow-sm inline-flex items-center justify-center"
                                                title="Send Escalation Email"
                                            >
                                                <Mail size={18} />
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={6} className="px-8 py-20 text-center">
                                        <div className="flex flex-col items-center gap-3">
                                            <div className="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center">
                                                <CheckCircle size={32} />
                                            </div>
                                            <p className="text-gray-500 dark:text-slate-400 font-bold uppercase tracking-widest text-sm">{t('analytics.overdue.allClear')}</p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Email Confirmation Modal */}
            {emailingReport && (
                <div className="fixed inset-0 bg-gray-900/60 backdrop-blur-md z-[60] flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] w-full max-w-2xl shadow-2xl animate-in zoom-in-95 duration-300 overflow-hidden flex flex-col max-h-[90vh]">
                        <div className="bg-gradient-to-r from-red-600 to-rose-700 p-8 text-white relative flex-shrink-0">
                            <button onClick={() => setEmailingReport(null)} className="absolute top-6 right-6 p-2 bg-white dark:bg-slate-900/10 hover:bg-white dark:bg-slate-900/20 rounded-full transition-colors text-white">
                                <X size={20} />
                            </button>
                            <div className="flex items-start gap-4">
                                <div className="p-3 bg-white dark:bg-slate-900/10 rounded-2xl">
                                    <Mail size={28} />
                                </div>
                                <div>
                                    <h2 className="text-xl font-bold">{t('dailyReport.email.escalationTitle', 'Send Escalation Email')}</h2>
                                    <p className="text-red-100/80 text-sm mt-1">{t('dailyReport.email.subtitle', 'Confirm sending report #')}{emailingReport.id}</p>
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
                                            <span className="font-medium text-gray-800 dark:text-gray-200 break-all">⚠️ URGENT ESCALATION: Overdue HSE Observation - {emailingReport.project_name} (ID: #{emailingReport.id})</span>
                                        </div>
                                    </div>
                                    
                                    <div className="mt-4">
                                        <span className="font-bold text-gray-400 uppercase tracking-widest text-[10px] block mb-2">Message Body Preview:</span>
                                        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 overflow-x-auto shadow-sm">
                                            <div style={{ fontFamily: 'Arial, sans-serif', minWidth: '400px', margin: '0 auto', border: '1px solid #ddd', borderRadius: '8px', backgroundColor: '#f9f9f9' }}>
                                                <div style={{ backgroundColor: '#dc2626', color: 'white', padding: '15px', borderRadius: '5px 5px 0 0', textAlign: 'center' }}>
                                                    <h2 style={{ margin: 0, fontSize: '16px' }}>ESCALATED: Overdue HSE Observation Report</h2>
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
                                                    <div style={{ marginTop: '20px', padding: '15px', borderLeft: '4px solid #dc2626', background: '#fef2f2', fontSize: '12px' }}>
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
                                    className="flex-1 py-3.5 px-4 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-200 flex justify-center items-center gap-2"
                                >
                                    {isEmailing === emailingReport.id ? <Loader2 size={18} className="animate-spin" /> : <><Send size={18} /> {t('common.send', 'Send Escalation')}</>}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default AnalyticsPage;
