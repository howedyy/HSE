import React from 'react';
import { useDashboard } from '../../application/hooks/useDashboard';
import { useAuth } from '../context/AuthContext';
import { useTranslation } from 'react-i18next';
import {
    CircleCheck, Zap, Clock, FileText,
    TriangleAlert, Building, TrendingUp,
    ChevronRight, RefreshCw
} from 'lucide-react';

// --- Stat Card ---
const StatCard = ({ icon, value, label, color }: { icon: React.ReactNode; value: number; label: string; color: string }) => (
    <div className="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 p-6 hover:shadow-lg transition-shadow group">
        <div className={`w-10 h-10 rounded-xl flex items-center justify-center mb-4 ${color}`}>
            {icon}
        </div>
        <div className="text-3xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-700 transition-colors">{value}</div>
        <div className="text-sm text-gray-500 dark:text-slate-400 mt-1">{label}</div>
    </div>
);

// --- Ring Chart (pure CSS doughnut) ---
const RingChart = ({ percentage, color, label, detail }: { percentage: number; color: string; label: string; detail: string }) => {
    const radius = 54;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (percentage / 100) * circumference;

    return (
        <div className="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 p-6 flex flex-col items-center">
            <div className="relative w-32 h-32">
                <svg className="w-32 h-32 -rotate-90" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r={radius} fill="none" stroke="#f1f5f9" strokeWidth="10" />
                    <circle
                        cx="60" cy="60" r={radius} fill="none"
                        stroke={color} strokeWidth="10" strokeLinecap="round"
                        strokeDasharray={circumference} strokeDashoffset={offset}
                        className="transition-all duration-1000 ease-out"
                    />
                </svg>
                <div className="absolute inset-0 flex flex-col items-center justify-center">
                    <span className="text-2xl font-bold text-gray-900 dark:text-gray-100">{percentage}%</span>
                </div>
            </div>
            <p className="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-4">{label}</p>
            <p className="text-xs text-gray-400 mt-1 text-center">{detail}</p>
        </div>
    );
};

// --- Incident Row ---
const IncidentRow = ({ description, project, date, risk, status, t, i18n }: {
    description: string; project: string; date: string; risk: string; status: number; t: any; i18n: any;
}) => {
    const isHigh = risk === 'عالية' || risk === 'High';
    const isMedium = risk === 'متوسطة' || risk === 'Medium';
    const dotColor = isHigh ? 'bg-red-500' : isMedium ? 'bg-amber-500' : 'bg-green-500';
    const statusLabel = status === 1 ? t('dashboard.incidents.resolved') : t('dashboard.incidents.open');
    const statusStyle = status === 1
        ? 'bg-green-50 text-green-700 border-green-200'
        : 'bg-red-50 text-red-700 border-red-200';

    return (
        <div className="flex items-center gap-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 dark:bg-slate-950/50/50 rounded-lg px-2 transition-colors">
            <div className={`w-2.5 h-2.5 rounded-full shrink-0 ${dotColor}`} />
            <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{description || t('dashboard.incidents.noDescription', 'No description')}</p>
                <div className="flex items-center gap-3 text-xs text-gray-400 mt-0.5">
                    <span className="flex items-center gap-1"><Building size={12} /> {project}</span>
                    <span>{new Date(date).toLocaleDateString(i18n.language === 'en' ? 'en-US' : 'ar-EG')}</span>
                </div>
            </div>
            <span className={`text-xs font-medium px-2.5 py-1 rounded-full border ${statusStyle}`}>{statusLabel}</span>
        </div>
    );
};

// --- Main Dashboard ---
const DashboardPage: React.FC = () => {
    const { t, i18n } = useTranslation();
    const { dashboard, isLoading } = useDashboard();
    const { user } = useAuth();

    if (isLoading || !dashboard) {
        return (
            <div className="max-w-7xl mx-auto space-y-6">
                <div className="flex flex-col items-center justify-center min-h-[40vh] gap-4">
                    <RefreshCw className="animate-spin text-blue-600" size={32} />
                    <p className="text-gray-400 font-bold uppercase tracking-widest text-xs">{t('common.loading')}</p>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 opacity-50 pointer-events-none">
                    {[...Array(4)].map((_, i) => (
                        <div key={i} className="h-32 bg-gray-50 dark:bg-slate-950/50 rounded-2xl animate-pulse" />
                    ))}
                </div>
            </div>
        );
    }

    const { stats, bestPractices, highRisk, taskCompletion, monthlyTrend, recentIncidents } = dashboard;

    return (
        <div className="max-w-7xl mx-auto space-y-4 md:space-y-8 animate-in fade-in duration-700">
            {/* Welcome Header */}
            <div>
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {t('dashboard.welcome', { name: user?.username ?? 'User' })}
                </h1>
                <p className="text-gray-500 dark:text-slate-400 text-sm mt-1">{t('dashboard.subtitle')}</p>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <StatCard icon={<CircleCheck size={20} className="text-emerald-600" />} value={stats.completed_ptw} label={t('dashboard.stats.completedPermits')} color="bg-emerald-50" />
                <StatCard icon={<Zap size={20} className="text-blue-600" />} value={stats.active_ptw} label={t('dashboard.stats.activePermits')} color="bg-blue-50" />
                <StatCard icon={<Clock size={20} className="text-amber-600" />} value={stats.pending_ptw} label={t('dashboard.stats.pendingApproval')} color="bg-amber-50" />
                <StatCard icon={<FileText size={20} className="text-violet-600" />} value={stats.today_reports} label={t('dashboard.stats.todayReports')} color="bg-violet-50" />
            </div>

            {/* Compliance Ring Charts */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <RingChart
                    percentage={bestPractices.percentage}
                    color="#1c4d8d"
                    label={t('dashboard.compliance.bestPractices')}
                    detail={t('dashboard.compliance.top', { project: bestPractices.projectName })}
                />
                <RingChart
                    percentage={highRisk.complianceRate}
                    color="#f43f5e"
                    label={t('dashboard.compliance.highRisk')}
                    detail={t('dashboard.compliance.hotspot', { project: highRisk.topProject })}
                />
                <RingChart
                    percentage={taskCompletion}
                    color="#8b5cf6"
                    label={t('dashboard.compliance.permitRate')}
                    detail={t('dashboard.compliance.of', { completed: stats.completed_ptw, total: stats.total_ptw })}
                />
            </div>

            {/* Activity Trend Chart (simple bar chart with CSS) */}
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 p-6">
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-2">
                        <TrendingUp size={18} className="text-blue-600" />
                        <h3 className="font-semibold text-gray-800 dark:text-gray-200">{t('dashboard.trend.title')}</h3>
                    </div>
                </div>
                <div className="flex items-end gap-3 h-40">
                    {monthlyTrend.map((m, i) => {
                        const maxCount = Math.max(...monthlyTrend.map(t => t.count), 1);
                        const heightPct = (m.count / maxCount) * 100;
                        return (
                            <div key={i} className="flex-1 flex flex-col items-center gap-2">
                                <span className="text-xs font-medium text-gray-600 dark:text-gray-400">{m.count}</span>
                                <div
                                    className="w-full bg-gradient-to-t from-blue-600 to-blue-400 rounded-t-lg transition-all duration-700"
                                    style={{ height: `${Math.max(heightPct, 4)}%` }}
                                />
                                <span className="text-xs text-gray-400 uppercase tracking-tighter">
                                    {i18n.language === 'ar' ? m.monthAr || m.month : m.month}
                                </span>
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Recent Incidents */}
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 p-6">
                <div className="flex items-center justify-between mb-4">
                    <div className="flex items-center gap-2">
                        <TriangleAlert size={18} className="text-red-500" />
                        <h3 className="font-semibold text-gray-800 dark:text-gray-200">{t('dashboard.incidents.title')}</h3>
                    </div>
                    <a href="/reports" className="text-xs text-blue-600 flex items-center gap-1 hover:underline">
                        {t('dashboard.incidents.viewAll')} <ChevronRight size={14} className="rtl:rotate-180" />
                    </a>
                </div>
                {recentIncidents.length > 0 ? (
                    <div>
                        {recentIncidents.map((inc) => (
                            <IncidentRow
                                key={inc.id}
                                description={inc.description}
                                project={inc.project}
                                date={inc.date}
                                risk={inc.risk}
                                status={inc.status}
                                t={t}
                                i18n={i18n}
                            />
                        ))}
                    </div>
                ) : (
                    <p className="text-center text-gray-400 py-8 font-medium">{t('dashboard.incidents.noRecords')}</p>
                )}
            </div>
        </div>
    );
};

export default DashboardPage;
