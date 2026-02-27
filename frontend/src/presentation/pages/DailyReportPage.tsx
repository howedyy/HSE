import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDailyReports } from '../../application/hooks/useDailyReports';
import { useLookups } from '../../application/hooks/useLookups';
import { useAuth } from '../context/AuthContext';
import {
    FileText, Filter, ChevronLeft, ChevronRight,
    CheckCircle, Clock, Search, X, Plus
} from 'lucide-react';

const riskColor = (risk: string) => {
    if (risk === 'عالية' || risk === 'High') return 'bg-red-100 text-red-700 border-red-200';
    if (risk === 'متوسطه' || risk === 'Medium') return 'bg-amber-100 text-amber-700 border-amber-200';
    return 'bg-green-100 text-green-700 border-green-200';
};

const statusBadge = (status: number) =>
    status === 1
        ? <span className="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-green-50 text-green-700 border border-green-200 font-medium"><CheckCircle size={12} /> Resolved</span>
        : <span className="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-red-50 text-red-700 border border-red-200 font-medium"><Clock size={12} /> Open</span>;

const DailyReportPage: React.FC = () => {
    const navigate = useNavigate();
    const { hasPermission } = useAuth();
    const [page, setPage] = useState(1);
    const [filters, setFilters] = useState<Record<string, string>>({});
    const [showFilters, setShowFilters] = useState(false);

    const { reports, total, totalPages, isLoading } = useDailyReports(filters, page);
    const { projects, departments } = useLookups();
    const canSubmit = hasPermission('dailyreport.php', 'submit');

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

    return (
        <div className="max-w-full mx-auto space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <FileText size={24} className="text-blue-600" /> Daily Reports
                    </h1>
                    <p className="text-sm text-gray-500 mt-1">{total} observations recorded</p>
                </div>
                <div className="flex items-center gap-3">
                    <button
                        onClick={() => setShowFilters(!showFilters)}
                        className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all ${showFilters ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}`}
                    >
                        {showFilters ? <X size={16} /> : <Filter size={16} />}
                        {showFilters ? 'Hide Filters' : 'Filters'}
                    </button>
                    {canSubmit && (
                        <button onClick={() => navigate('/reports/new')} className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">
                            <Plus size={16} /> New Report
                        </button>
                    )}
                </div>
            </div>

            {/* Filter Panel */}
            {showFilters && (
                <div className="bg-white rounded-2xl border border-gray-100 p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <select onChange={(e) => updateFilter('project', e.target.value)} value={filters.project ?? ''} className="px-3 py-2.5 rounded-xl border border-gray-200 text-sm bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">All Projects</option>
                        {projects.map(p => <option key={p.id} value={p.id}>{p.project_name}</option>)}
                    </select>
                    <select onChange={(e) => updateFilter('department', e.target.value)} value={filters.department ?? ''} className="px-3 py-2.5 rounded-xl border border-gray-200 text-sm bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">All Departments</option>
                        {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                    </select>
                    <select onChange={(e) => updateFilter('risk', e.target.value)} value={filters.risk ?? ''} className="px-3 py-2.5 rounded-xl border border-gray-200 text-sm bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">All Risk Levels</option>
                        <option value="عالية">High</option>
                        <option value="متوسطه">Medium</option>
                        <option value="منخفضة">Low</option>
                    </select>
                    <select onChange={(e) => updateFilter('status', e.target.value)} value={filters.status ?? ''} className="px-3 py-2.5 rounded-xl border border-gray-200 text-sm bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">All Statuses</option>
                        <option value="0">Open</option>
                        <option value="1">Resolved</option>
                    </select>
                </div>
            )}

            {/* Table */}
            <div className="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                {isLoading ? (
                    <div className="p-10 space-y-3">
                        {[...Array(8)].map((_, i) => <div key={i} className="h-10 bg-gray-100 rounded-lg animate-pulse" />)}
                    </div>
                ) : reports.length === 0 ? (
                    <div className="text-center py-20">
                        <Search size={40} className="mx-auto text-gray-300 mb-3" />
                        <p className="text-gray-500 font-medium">No reports match your filters</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 border-b border-gray-100">
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">#</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Date</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Project</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Department</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Work Type</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Risk</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Status</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Created By</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {reports.map((r) => (
                                    <tr key={r.id} className="hover:bg-blue-50/30 transition-colors">
                                        <td className="px-5 py-3.5 text-gray-600 font-mono text-xs">{r.id}</td>
                                        <td className="px-5 py-3.5 text-gray-700">{new Date(r.date).toLocaleDateString()}</td>
                                        <td className="px-5 py-3.5 text-gray-800 font-medium">{r.project_name}</td>
                                        <td className="px-5 py-3.5 text-gray-600">{r.department_name}</td>
                                        <td className="px-5 py-3.5 text-gray-600 max-w-[200px] truncate">{r.work_type}</td>
                                        <td className="px-5 py-3.5">
                                            <span className={`text-xs px-2.5 py-1 rounded-full border font-medium ${riskColor(r.risk)}`}>{r.risk}</span>
                                        </td>
                                        <td className="px-5 py-3.5">{statusBadge(r.report_status)}</td>
                                        <td className="px-5 py-3.5 text-gray-500">{r.created_by}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination */}
                {totalPages > 1 && (
                    <div className="flex items-center justify-between px-5 py-4 border-t border-gray-100">
                        <p className="text-xs text-gray-400">
                            Page {page} of {totalPages} · {total} records
                        </p>
                        <div className="flex items-center gap-2">
                            <button disabled={page <= 1} onClick={() => setPage(p => p - 1)} className="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 transition-colors"><ChevronLeft size={16} /></button>
                            {[...Array(Math.min(totalPages, 5))].map((_, i) => {
                                const num = i + 1;
                                return <button key={num} onClick={() => setPage(num)} className={`w-9 h-9 rounded-lg text-sm font-medium transition-colors ${page === num ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`}>{num}</button>;
                            })}
                            <button disabled={page >= totalPages} onClick={() => setPage(p => p + 1)} className="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 transition-colors"><ChevronRight size={16} /></button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default DailyReportPage;
