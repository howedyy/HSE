import React, { useState, useEffect } from 'react';
import api from '../../infrastructure/api/client';
import { useLookups } from '../../application/hooks/useLookups';
import { useTranslation } from 'react-i18next';
import {
    AlertTriangle, Filter, RefreshCw, Clock, Building,
    CheckCircle, ClipboardCheck, Zap, Activity
} from 'lucide-react';

const PtwAnalyticsPage: React.FC = () => {
    const { t, i18n } = useTranslation();
    const { projects, departments: allDepartments } = useLookups();
    const [stats, setStats] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState<Record<string, string>>({
        project: '',
        department: '',
        operation: '',
        startDate: '',
        endDate: ''
    });

    const operations = [
        "أعمال حفر", "أعمال لحام كهربي", "العمل على ارتفاع سبايدر", "أعمال رفع أحمال بمعدات ثقيلة",
        "العمل علي سقالة", "العمل على السلم المفصلى", "أعمال نقل بمعدات ثقيلة",
        "العمل بداخل الغرف المغلقة", "العمل على السلم الهيدروليكي", "إعمال كهرباء الجهد المتوسط", "العمل بالمواد الخطرة"
    ];

    const fetchAnalytics = async () => {
        setLoading(true);
        try {
            const query = new URLSearchParams(filters).toString();
            const data = await api.get(`/reports/ptw_analytics?${query}`);
            setStats(data);
        } catch (error) {
            console.error('PTW Analytics Error:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchAnalytics();
    }, [filters]);

    const statusConfig: Record<number, { label: string, color: string, bg: string }> = {
        0: { label: t('ptw.status.notApproved', 'Not Approved'), color: 'text-amber-600', bg: 'bg-amber-50' },
        1: { label: t('ptw.status.approved', 'Approved'), color: 'text-blue-600', bg: 'bg-blue-50' },
        2: { label: t('ptw.status.finished', 'Finished'), color: 'text-emerald-600', bg: 'bg-emerald-50' },
        3: { label: t('ptw.status.notCompleted', 'Not Completed'), color: 'text-rose-600', bg: 'bg-rose-50' }
    };

    if (loading && !stats) return (
        <div className="flex flex-col items-center justify-center min-h-[60vh] gap-4">
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
                <h3 className="text-xl font-black text-gray-900 dark:text-gray-100">{t('common.error')}</h3>
                <p className="text-gray-500 dark:text-slate-400 font-medium">{t('ptw.syncError', 'Unable to connect to the PTW data engine.')}</p>
            </div>
            <button
                onClick={fetchAnalytics}
                className="px-8 py-3 bg-gray-900 text-white rounded-2xl font-bold hover:bg-gray-800 transition-all shadow-xl shadow-gray-200 flex items-center gap-2"
            >
                <RefreshCw size={18} />
                {t('ptw.retry', 'Retry Connection')}
            </button>
        </div>
    );

    const byType = Array.isArray(stats.byType) ? stats.byType : [];
    const byDepartment = Array.isArray(stats.byDepartment) ? stats.byDepartment : [];
    const overTime = Array.isArray(stats.overTime) ? stats.overTime : [];
    const overdueItems = Array.isArray(stats.overdue?.items) ? stats.overdue.items : [];
    const overdueCount = stats.overdue?.count ?? 0;

    const totalPermits = byType.reduce((acc: number, r: any) => acc + Number(r.count || 0), 0);
    const highEnergyCount = byType.filter((t: any) => {
        const typeStr = String(t.type || '');
        return typeStr.includes('كهرباء') || typeStr.includes('لحام');
    }).reduce((acc: number, t: any) => acc + Number(t.count || 0), 0);

    const completionRate = totalPermits > 0 
        ? Math.round((overdueItems.filter((i: any) => i.ptw_status === 2).length / totalPermits) * 100) 
        : 0;

    return (
        <div className="max-w-7xl mx-auto space-y-4 md:space-y-8 animate-in fade-in duration-700">
            {/* Header & Advanced Filter Bar */}
            <div className="flex flex-col xl:flex-row xl:items-center justify-between gap-6">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tight">{t('ptw.analyticsTitle')}</h1>
                    <p className="text-gray-500 dark:text-slate-400 mt-1 font-medium">{t('ptw.analyticsSubtitle')}</p>
                </div>

                <div className="flex flex-wrap items-center gap-3 bg-white dark:bg-slate-900 p-3 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-x-auto max-w-full">
                    <div className="flex items-center gap-2 px-3 border-r border-l border-gray-100 dark:border-slate-800 shrink-0">
                        <Filter size={16} className="text-blue-600" />
                        <span className="text-xs font-bold uppercase tracking-widest text-gray-400">{t('common.filter')}</span>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase ml-1">{t('common.startDate')}</label>
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
                            <option value="">{t('common.all')}</option>
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
                            <option value="">{t('common.all')}</option>
                            {allDepartments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                        </select>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase ml-1">{t('ptw.operation', 'Operation')}</label>
                        <select
                            value={filters.operation}
                            onChange={e => setFilters(prev => ({ ...prev, operation: e.target.value }))}
                            className="text-xs bg-gray-50 dark:bg-slate-950/50 border-none rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none font-semibold min-w-[150px]"
                        >
                            <option value="">{t('common.all')}</option>
                            {operations.map(op => <option key={op} value={op}>{op}</option>)}
                        </select>
                    </div>

                    <button
                        onClick={() => setFilters({ project: '', department: '', operation: '', startDate: '', endDate: '' })}
                        className="p-2 bg-gray-50 dark:bg-slate-950/50 text-gray-400 rounded-xl hover:bg-red-50 hover:text-red-500 transition-colors mt-auto"
                        title={t('common.reset')}
                    >
                        <RefreshCw size={18} />
                    </button>
                </div>
            </div>

            {/* Metrics Bar */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-gray-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <Clock size={80} className="text-orange-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">{t('ptw.overdue')}</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-orange-600 leading-none">{overdueCount}</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-orange-400 leading-none mb-1 uppercase">{t('common.critical', 'CRITICAL')}</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none uppercase">{t('ptw.timeExceeded', 'TIME EXCEEDED')}</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-gray-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <ClipboardCheck size={80} className="text-blue-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">{t('ptw.total')}</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-gray-900 dark:text-gray-100 leading-none">{totalPermits}</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-blue-500 leading-none mb-1 uppercase">{t('ptw.issued', 'ISSUED')}</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none uppercase">{t('ptw.workUnits', 'WORK UNITS')}</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-gray-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <Zap size={80} className="text-amber-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">{t('ptw.highEnergy')}</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-amber-600 leading-none">{highEnergyCount}</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-amber-500 leading-none mb-1 uppercase">{t('ptw.dangerous', 'DANGEROUS')}</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none uppercase">{t('ptw.operationType', 'OPERATION TYPE')}</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-gray-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <Activity size={80} className="text-emerald-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">{t('ptw.completionRate')}</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-emerald-600 leading-none">{completionRate}%</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-emerald-500 leading-none mb-1 uppercase">{t('common.success', 'SUCCESS')}</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none uppercase">{t('ptw.closedCycles', 'CLOSED CYCLES')}</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Charts Section */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Departments Chart */}
                <div className="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-gray-100 dark:border-slate-800 shadow-sm p-4 md:p-8 space-y-6 md:space-y-8">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                            <Building size={24} />
                        </div>
                        <div>
                            <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">{t('ptw.distribution')}</h3>
                            <p className="text-xs text-gray-400 font-medium">{t('ptw.distributionSub', 'Departmental engagement in permit system')}</p>
                        </div>
                    </div>

                    <div className="space-y-5">
                        {byDepartment.map((dept: any, i: number) => {
                            const maxValue = Math.max(...byDepartment.map((d: any) => d.count), 1);
                            const width = (dept.count / maxValue) * 100;
                            return (
                                <div key={i} className="group">
                                    <div className="flex items-center justify-between text-sm mb-2 px-1">
                                        <span className="font-bold text-gray-700 dark:text-gray-300 group-hover:text-blue-600 transition-colors uppercase tracking-tight">{dept.name}</span>
                                        <span className="font-black text-blue-600">{dept.count}</span>
                                    </div>
                                    <div className="h-4 w-full bg-gray-50 dark:bg-slate-950/50 rounded-full overflow-hidden border border-gray-100 dark:border-slate-800">
                                        <div
                                            className="h-full bg-gradient-to-r from-blue-600 to-indigo-500 rounded-full transition-all duration-1000 ease-out"
                                            style={{ width: `${width}%`, transitionDelay: `${i * 80}ms` }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Operation Types Circle */}
                <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-gray-100 dark:border-slate-800 shadow-sm p-8 flex flex-col items-center justify-center space-y-8">
                    <div className="text-center">
                        <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">{t('ptw.profile')}</h3>
                        <p className="text-xs text-gray-400 font-medium">{t('ptw.profileSub', 'Permit categorization by task type')}</p>
                    </div>

                    <div className="relative w-56 h-56">
                        <svg className="w-full h-full -rotate-90" viewBox="0 0 32 32">
                            <circle cx="16" cy="16" r="14" fill="transparent" stroke="#f8fafc" strokeWidth="4" />
                            {(() => {
                                let offset = 0;
                                const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
                                return byType.slice(0, 6).map((item: any, i: number) => {
                                    const dash = totalPermits > 0 ? (item.count / totalPermits) * 88 : 0;
                                    const currentOffset = offset;
                                    offset += dash;
                                    return (
                                        <circle
                                            key={i}
                                            cx="16" cy="16" r="14" fill="transparent"
                                            stroke={colors[i % colors.length]}
                                            strokeWidth="4"
                                            strokeDasharray={`${dash} 88`}
                                            strokeDashoffset={-currentOffset}
                                            className="transition-all duration-1000 ease-in-out"
                                        />
                                    );
                                });
                            })()}
                        </svg>
                        <div className="absolute inset-0 flex flex-col items-center justify-center">
                            <span className="text-3xl font-black text-gray-900 dark:text-gray-100 leading-none">{byType.length}</span>
                            <span className="text-[8px] font-black text-gray-400 uppercase tracking-widest mt-1">{t('ptw.categories')}</span>
                        </div>
                    </div>

                    <div className="w-full space-y-2">
                        {byType.slice(0, 4).map((item: any, i: number) => (
                            <div key={i} className="flex items-center justify-between p-2 rounded-xl bg-gray-50 dark:bg-slate-950/50/50 border border-gray-100 dark:border-slate-800">
                                <div className="flex items-center gap-2 overflow-hidden">
                                    <div className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'][i] }} />
                                    <span className="text-[10px] font-bold text-gray-600 dark:text-gray-400 truncate">{item.type}</span>
                                </div>
                                <span className="text-[10px] font-black text-gray-900 dark:text-gray-100 ml-2">{item.count}</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Unique Feature: Timeline with Interactive Visuals */}
            <div className="bg-gray-900 rounded-[3rem] p-10 text-white relative overflow-hidden">
                <div className="absolute top-0 right-0 w-1/2 h-full opacity-10 pointer-events-none">
                    <Activity size={400} className="text-white" />
                </div>
                
                <div className="relative z-10 flex flex-col lg:flex-row lg:items-end justify-between gap-8">
                    <div>
                        <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 text-[10px] font-black uppercase tracking-widest mb-4">
                            <Zap size={14} /> {t('ptw.trendAnalysis', 'Trend Analysis')}
                        </div>
                        <h2 className="text-4xl font-black tracking-tight mb-2">{t('ptw.pulse')}</h2>
                        <p className="text-gray-400 max-w-md font-medium text-sm">{t('ptw.pulseSub', 'Historical permit issuance volume tracking across the operational timeline.')}</p>
                    </div>

                    <div className="flex-1 max-w-3xl h-48 flex items-end gap-2 px-4">
                        {overTime.slice(-20).map((day: any, i: number) => {
                            const max = Math.max(...overTime.map((d: any) => d.count), 1);
                            const height = (day.count / max) * 100;
                            return (
                                <div key={i} className="flex-1 flex flex-col items-center gap-2 group">
                                    <div className="relative w-full">
                                        <div 
                                            className="w-full bg-blue-500 rounded-t-lg transition-all duration-1000 ease-out group-hover:bg-blue-400 group-hover:shadow-[0_0_20px_rgba(59,130,246,0.5)]"
                                            style={{ height: `${height}%`, transitionDelay: `${i * 30}ms` }}
                                        />
                                        <div className="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 text-[10px] font-black px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                            {day.count}
                                        </div>
                                    </div>
                                    <span className="text-[8px] font-bold text-gray-500 dark:text-slate-400 uppercase rotate-45 mt-4 origin-left truncate w-8">
                                        {new Date(day.date).toLocaleDateString(i18n.language === 'en' ? 'en-US' : 'ar-EG', { month: 'short', day: 'numeric' })}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>

            {/* Overdue Section */}
            <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden mb-12">
                <div className="p-8 border-b border-gray-50 bg-gradient-to-r from-orange-50/50 to-white flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-orange-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-orange-200">
                            <Clock size={24} />
                        </div>
                        <div>
                            <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">{t('ptw.registry')}</h3>
                            <p className="text-xs text-orange-500 font-bold uppercase tracking-widest mt-0.5 animate-pulse">{t('ptw.registrySub', 'Compliance Warning')} • {overdueCount} {t('ptw.pending', 'Pending')}</p>
                        </div>
                    </div>
                    <button className="px-6 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-bold hover:bg-gray-800 transition-all flex items-center gap-2">
                        <AlertTriangle size={16} /> {t('ptw.markPriority', 'Mark Priority')}
                    </button>
                </div>

                <div className="overflow-x-auto w-full">
                    <table className="w-full text-left border-collapse rtl:text-right">
                        <thead>
                            <tr className="bg-gray-50 dark:bg-slate-950/50/50 border-b border-gray-100 dark:border-slate-800">
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">{t('ptw.table.idDate', 'Permit ID / Issued')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">{t('ptw.table.projectDept', 'Project / Dept')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">{t('ptw.table.type', 'Operation Type')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">{t('common.status')}</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right rtl:text-left">{t('ptw.table.compliance', 'Compliance')}</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {overdueItems.length > 0 ? (
                                overdueItems.map((r: any, i: number) => {
                                    const config = statusConfig[r.ptw_status as number] || { label: 'Unknown', color: 'text-gray-400', bg: 'bg-gray-50 dark:bg-slate-950/50' };
                                    return (
                                        <tr key={i} className="hover:bg-gray-50 dark:bg-slate-950/50/30 transition-colors group">
                                            <td className="px-8 py-6">
                                                <div className="font-black text-gray-900 dark:text-gray-100 group-hover:text-blue-600 transition-colors">#{r.id}</div>
                                                <div className="text-xs text-gray-400 font-medium mt-1">{new Date(r.permit_date).toLocaleDateString(i18n.language === 'en' ? 'en-US' : 'ar-EG')}</div>
                                            </td>
                                            <td className="px-8 py-6">
                                                <div className="font-bold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-tight">{r.project_name || 'General Project'}</div>
                                                <div className="flex items-center gap-1.5 text-xs text-gray-400 font-semibold mt-1">
                                                    <div className="w-1.5 h-1.5 rounded-full bg-blue-400" />
                                                    {r.department_name}
                                                </div>
                                            </td>
                                            <td className="px-8 py-6">
                                                <div className="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400 rounded-lg text-[10px] font-bold">
                                                    < Zap size={12} className="text-amber-500" />
                                                    {r.operation_type}
                                                </div>
                                            </td>
                                            <td className="px-8 py-6 text-center">
                                                <span className={`inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-current/10 ${config.bg} ${config.color}`}>
                                                    {config.label}
                                                </span>
                                            </td>
                                            <td className="px-8 py-6 text-right rtl:text-left">
                                                <div className="flex items-center justify-end gap-2 text-[10px] font-black text-orange-600 bg-orange-50 px-3 py-2 rounded-xl border border-orange-100 ml-auto w-fit">
                                                    <AlertTriangle size={14} />
                                                    {t('ptw.criticalDelay', 'CRITICAL DELAY')}
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan={5} className="px-8 py-20 text-center">
                                        <div className="flex flex-col items-center gap-3">
                                            <div className="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center">
                                                <CheckCircle size={32} />
                                            </div>
                                            <p className="text-gray-500 dark:text-slate-400 font-bold uppercase tracking-widest text-sm">{t('ptw.noOverdue', 'Permit Shield Active • 0 Overdue Items')}</p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default PtwAnalyticsPage;
