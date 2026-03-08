import React, { useState, useEffect } from 'react';
import api from '../../infrastructure/api/client';
import { useLookups } from '../../application/hooks/useLookups';
import {
    BarChart2, PieChart, AlertTriangle,
    Filter, RefreshCw, Clock, Building,
    Calendar, CheckCircle
} from 'lucide-react';

const AnalyticsPage: React.FC = () => {
    const { projects, departments: allDepartments } = useLookups();
    const [stats, setStats] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState({ project: '', department: '' });

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
            <p className="text-gray-500 font-medium tracking-widest uppercase text-xs">Processing Safety Data Analytics...</p>
        </div>
    );

    if (!stats) return (
        <div className="flex flex-col items-center justify-center min-h-[60vh] gap-6 p-8">
            <div className="w-20 h-20 bg-red-50 text-red-500 rounded-[2rem] flex items-center justify-center">
                <AlertTriangle size={40} />
            </div>
            <div className="text-center space-y-2">
                <h3 className="text-xl font-black text-gray-900">Analytics Sync Failed</h3>
                <p className="text-gray-500 font-medium">The data engine encountered a synchronization issue with the server.</p>
            </div>
            <button
                onClick={fetchAnalytics}
                className="px-8 py-3 bg-gray-900 text-white rounded-2xl font-bold hover:bg-gray-800 transition-all shadow-xl shadow-gray-200 flex items-center gap-2"
            >
                <RefreshCw size={18} />
                Retry Connection
            </button>
        </div>
    );

    return (
        <div className="max-w-7xl mx-auto space-y-8 animate-in fade-in duration-700">
            {/* Header & Advanced Filter Bar */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 tracking-tight">Daily Report Analytics</h1>
                    <p className="text-gray-500 mt-1 font-medium">HSE Insight Dashboard • Live Statistics</p>
                </div>

                <div className="flex items-center gap-3 bg-white p-2 rounded-2xl border border-gray-100 shadow-sm overflow-x-auto max-w-full">
                    <div className="flex items-center gap-2 px-3 border-r border-gray-100 shrink-0">
                        <Filter size={16} className="text-blue-600" />
                        <span className="text-xs font-bold uppercase tracking-widest text-gray-400">Filter</span>
                    </div>

                    <select
                        value={filters.project}
                        onChange={e => setFilters(prev => ({ ...prev, project: e.target.value }))}
                        className="text-sm bg-gray-50 border-none rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none font-semibold min-w-[140px]"
                    >
                        <option value="">All Projects</option>
                        {projects.map(p => <option key={p.id} value={p.id}>{p.project_name}</option>)}
                    </select>

                    <select
                        value={filters.department}
                        onChange={e => setFilters(prev => ({ ...prev, department: e.target.value }))}
                        className="text-sm bg-gray-50 border-none rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none font-semibold min-w-[140px]"
                    >
                        <option value="">All Departments</option>
                        {allDepartments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                    </select>

                    <button
                        onClick={fetchAnalytics}
                        className="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors"
                        title="Manual Refresh"
                    >
                        <RefreshCw size={20} className={loading ? 'animate-spin' : ''} />
                    </button>
                </div>
            </div>

            {/* Quick Metrics Bar */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div className="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <AlertTriangle size={80} className="text-red-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">Overdue Alerts</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-red-600 leading-none">{stats.overdue.count}</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-red-400 leading-none mb-1">UNRESOLVED</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none">TIME EXCEEDED</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <CheckCircle size={80} className="text-emerald-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">Total Reports</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-gray-900 leading-none">
                            {stats.byRisk.reduce((acc: number, r: any) => acc + r.count, 0)}
                        </span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-emerald-500 leading-none mb-1">CUMULATIVE</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none">OBSERVATIONS</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <BarChart2 size={80} className="text-violet-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">Departments In-Focus</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-violet-600 leading-none">{stats.byDepartment.length}</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-violet-400 leading-none mb-1">ACTIVE</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none">SAFETY UNITS</span>
                        </div>
                    </div>
                </div>

                <div className="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <Calendar size={80} className="text-blue-600" />
                    </div>
                    <p className="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2">Monthly Coverage</p>
                    <div className="flex items-end gap-3">
                        <span className="text-4xl font-black text-blue-600 leading-none">30+</span>
                        <div className="flex flex-col">
                            <span className="text-[10px] font-bold text-blue-400 leading-none mb-1">HISTORICAL</span>
                            <span className="text-[10px] font-bold text-gray-400 leading-none">DAY ANALYSIS</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Charts Row */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* 1. Department Safety Engagement */}
                <div className="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 space-y-8">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                                <Building size={24} />
                            </div>
                            <div>
                                <h3 className="text-xl font-bold text-gray-900">Department Safety Distribution</h3>
                                <p className="text-xs text-gray-400 font-medium">Reports logged per operational unit</p>
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
                                        <span className="font-bold text-gray-700 group-hover:text-blue-600 transition-colors uppercase tracking-tight">{dept.name}</span>
                                        <span className="px-3 py-1 bg-gray-50 rounded-lg font-black text-blue-600">{dept.count}</span>
                                    </div>
                                    <div className="h-3 w-full bg-gray-50 rounded-full overflow-hidden border border-gray-100">
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
                <div className="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 space-y-10">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center">
                            <PieChart size={24} />
                        </div>
                        <div>
                            <h3 className="text-xl font-bold text-gray-900">Observation Risk Matrix</h3>
                            <p className="text-xs text-gray-400 font-medium">Criticality breakdown of reported hazards</p>
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
                                <span className="text-2xl font-black text-gray-900 leading-none">
                                    {stats.byRisk.length}
                                </span>
                                <span className="text-[8px] font-black text-gray-400 uppercase tracking-widest mt-1">Levels</span>
                            </div>
                        </div>

                        {/* Legend */}
                        <div className="space-y-5">
                            {stats.byRisk.map((r: any, i: number) => (
                                <div key={i} className="flex items-center justify-between p-4 rounded-2xl bg-gray-50/50 border border-gray-100 group hover:border-blue-200 transition-all">
                                    <div className="flex items-center gap-3">
                                        <div className="w-4 h-4 rounded-md" style={{ backgroundColor: riskColors[r.risk] }} />
                                        <span className="text-sm font-bold text-gray-700 uppercase tracking-tight">{r.risk} Risk</span>
                                    </div>
                                    <span className="text-sm font-black text-gray-900">{r.count}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Overdue Reports Advanced View */}
            <div className="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                <div className="p-8 border-b border-gray-50 bg-gradient-to-r from-red-50/50 to-white flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-red-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                            <Clock size={24} />
                        </div>
                        <div>
                            <h3 className="text-xl font-bold text-gray-900">Critical Overdue Observations</h3>
                            <p className="text-xs text-red-500 font-bold uppercase tracking-widest mt-0.5 animate-pulse">Action Required • {stats.overdue.count} Items</p>
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-gray-50/50 border-b border-gray-100">
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID / Date</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Ownership</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Risk Level</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Delay Hours</th>
                                <th className="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Violation Tag</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {stats.overdue.items.length > 0 ? (
                                stats.overdue.items.map((r: any, i: number) => (
                                    <tr key={i} className="hover:bg-gray-50/30 transition-colors group">
                                        <td className="px-8 py-6">
                                            <div className="font-black text-gray-900 group-hover:text-blue-600 transition-colors">#{r.id}</div>
                                            <div className="text-xs text-gray-400 font-medium mt-1">{new Date(r.date).toLocaleDateString()}</div>
                                        </td>
                                        <td className="px-8 py-6">
                                            <div className="font-bold text-gray-800 text-sm uppercase tracking-tight">{r.project_name}</div>
                                            <div className="flex items-center gap-1.5 text-xs text-gray-400 font-semibold mt-1">
                                                <div className="w-1.5 h-1.5 rounded-full bg-blue-400" />
                                                {r.department_name} • {r.created_by}
                                            </div>
                                        </td>
                                        <td className="px-8 py-6 text-center">
                                            <span
                                                className="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-gray-100"
                                                style={{ color: riskColors[r.risk], backgroundColor: `${riskColors[r.risk]}10` }}
                                            >
                                                {r.risk}
                                            </span>
                                        </td>
                                        <td className="px-8 py-6 text-center">
                                            <div className="font-black text-gray-900 group-hover:scale-110 transition-transform origin-center">{r.delay_hours}h</div>
                                            <div className="text-[9px] font-bold text-red-500 uppercase mt-0.5">+{r.exceeded_by}h Late</div>
                                        </td>
                                        <td className="px-8 py-6 text-right">
                                            <div className="flex items-center justify-end gap-2 text-xs font-black text-red-600 bg-red-50/50 px-3 py-2 rounded-xl border border-red-100 ml-auto w-fit">
                                                <AlertTriangle size={14} />
                                                NON-COMPLIANCE
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={5} className="px-8 py-20 text-center">
                                        <div className="flex flex-col items-center gap-3">
                                            <div className="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center">
                                                <CheckCircle size={32} />
                                            </div>
                                            <p className="text-gray-500 font-bold uppercase tracking-widest text-sm">All Clear! No Overdue Reports</p>
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

export default AnalyticsPage;
