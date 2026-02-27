import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { usePTWPermits } from '../../application/hooks/usePTWPermits';
import { useAuth } from '../context/AuthContext';
import {
    ClipboardCheck, ChevronLeft, ChevronRight,
    Search, Filter, X, Plus
} from 'lucide-react';

const statusMap: Record<number, { label: string; style: string }> = {
    0: { label: 'Pending', style: 'bg-amber-50 text-amber-700 border-amber-200' },
    1: { label: 'Approved', style: 'bg-blue-50 text-blue-700 border-blue-200' },
    2: { label: 'Completed', style: 'bg-green-50 text-green-700 border-green-200' },
    3: { label: 'Non-Compliance', style: 'bg-red-50 text-red-700 border-red-200' },
};

const PTWPage: React.FC = () => {
    const navigate = useNavigate();
    const { hasPermission } = useAuth();
    const [page, setPage] = useState(1);
    const [filters, setFilters] = useState<Record<string, string>>({});
    const [showFilters, setShowFilters] = useState(false);

    const { permits, total, totalPages, isLoading } = usePTWPermits(filters, page);
    const canSubmit = hasPermission('ptw.php', 'submit');

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
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <ClipboardCheck size={24} className="text-indigo-600" /> PTW Permits
                    </h1>
                    <p className="text-sm text-gray-500 mt-1">{total} permits in system</p>
                </div>
                <div className="flex items-center gap-3">
                    <button
                        onClick={() => setShowFilters(!showFilters)}
                        className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all ${showFilters ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}`}
                    >
                        {showFilters ? <X size={16} /> : <Filter size={16} />}
                        {showFilters ? 'Hide' : 'Filter'}
                    </button>
                    {canSubmit && (
                        <button onClick={() => navigate('/permits/new')} className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm">
                            <Plus size={16} /> New Permit
                        </button>
                    )}
                </div>
            </div>

            {showFilters && (
                <div className="bg-white rounded-2xl border border-gray-100 p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <select onChange={(e) => updateFilter('status', e.target.value)} value={filters.status ?? ''} className="px-3 py-2.5 rounded-xl border border-gray-200 text-sm">
                        <option value="">All Statuses</option>
                        <option value="0">Pending</option>
                        <option value="1">Approved</option>
                        <option value="2">Completed</option>
                    </select>
                </div>
            )}

            <div className="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                {isLoading ? (
                    <div className="p-10 space-y-3">
                        {[...Array(8)].map((_, i) => <div key={i} className="h-10 bg-gray-100 rounded-lg animate-pulse" />)}
                    </div>
                ) : permits.length === 0 ? (
                    <div className="text-center py-20">
                        <Search size={40} className="mx-auto text-gray-300 mb-3" />
                        <p className="text-gray-500 font-medium">No permits match your filters</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 border-b border-gray-100">
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Permit #</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Date</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Editor</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Project</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Department</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Operation</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {permits.map((p) => {
                                    const s = statusMap[p.ptw_status] ?? statusMap[0];
                                    return (
                                        <tr key={p.id} className="hover:bg-indigo-50/30 transition-colors">
                                            <td className="px-5 py-3.5 font-mono text-xs text-indigo-600 font-semibold">{p.permit_number}</td>
                                            <td className="px-5 py-3.5 text-gray-700">{new Date(p.permit_date).toLocaleDateString()}</td>
                                            <td className="px-5 py-3.5 text-gray-800 font-medium">{p.editor_name}</td>
                                            <td className="px-5 py-3.5 text-gray-600">{p.project_name}</td>
                                            <td className="px-5 py-3.5 text-gray-600">{p.department_name}</td>
                                            <td className="px-5 py-3.5 text-gray-600 max-w-[180px] truncate">{p.operation_type}</td>
                                            <td className="px-5 py-3.5">
                                                <span className={`text-xs px-2.5 py-1 rounded-full border font-medium ${s.style}`}>{s.label}</span>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}

                {totalPages > 1 && (
                    <div className="flex items-center justify-between px-5 py-4 border-t border-gray-100">
                        <p className="text-xs text-gray-400">Page {page} of {totalPages} · {total} records</p>
                        <div className="flex items-center gap-2">
                            <button disabled={page <= 1} onClick={() => setPage(p => p - 1)} className="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30"><ChevronLeft size={16} /></button>
                            {[...Array(Math.min(totalPages, 5))].map((_, i) => {
                                const num = i + 1;
                                return <button key={num} onClick={() => setPage(num)} className={`w-9 h-9 rounded-lg text-sm font-medium ${page === num ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`}>{num}</button>;
                            })}
                            <button disabled={page >= totalPages} onClick={() => setPage(p => p + 1)} className="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30"><ChevronRight size={16} /></button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default PTWPage;
