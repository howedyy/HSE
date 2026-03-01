import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDailyReports } from '../../application/hooks/useDailyReports';
import { useLookups } from '../../application/hooks/useLookups';
import { useAuth } from '../context/AuthContext';
import api from '../../infrastructure/api/client';
import {
    FileText, Filter, ChevronLeft, ChevronRight,
    CheckCircle, Clock, Search, X, Plus, Eye, Trash2, Loader2, Image as ImageIcon
} from 'lucide-react';
import { compressImage } from '../../shared/utils/imageCompression';

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
    const [selectedReport, setSelectedReport] = useState<any>(null);
    const [isClosing, setIsClosing] = useState<any>(null);
    const [closureNotes, setClosureNotes] = useState('');
    const [closureImage, setClosureImage] = useState<File | null>(null);
    const [isSubmittingClosure, setIsSubmittingClosure] = useState(false);
    const [isCompressing, setIsCompressing] = useState(false);

    const { reports, total, totalPages, isLoading, refetch } = useDailyReports(filters, page);
    const { projects, departments } = useLookups();
    const canSubmit = hasPermission('dailyreport.php', 'submit');
    const canDelete = hasPermission('dailyreport_overview.php', 'delete');
    const canClose = hasPermission('dailyreport_overview.php', 'submit');

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
        if (!window.confirm('Are you sure you want to delete this report?')) return;
        try {
            await api.delete(`/reports/delete?id=${id}`);
            refetch();
        } catch (err: any) {
            alert(err.response?.data?.message || 'Delete failed');
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
            alert(err.response?.data?.message || 'Closure failed');
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
                <div className="bg-white rounded-2xl border border-gray-100 p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-in slide-in-from-top-2 duration-200">
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
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">#</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Date</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Project</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Department</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Work Type</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Risk</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Status</th>
                                    <th className="text-left px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Created By</th>
                                    <th className="text-right px-5 py-3 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {reports.map((r: any) => (
                                    <tr key={r.id} className="hover:bg-blue-50/20 transition-colors">
                                        <td className="px-5 py-4 text-gray-400 font-mono text-[10px]">#{r.id}</td>
                                        <td className="px-5 py-4 text-gray-600">{new Date(r.date).toLocaleDateString()}</td>
                                        <td className="px-5 py-4 text-gray-900 font-semibold">{r.project_name}</td>
                                        <td className="px-5 py-4 text-gray-600">{r.department_name}</td>
                                        <td className="px-5 py-4 text-gray-600 max-w-[150px] truncate">{r.work_type}</td>
                                        <td className="px-5 py-4">
                                            <span className={`text-[10px] px-2.5 py-1 rounded-full border font-bold ${riskColor(r.risk)}`}>{r.risk}</span>
                                        </td>
                                        <td className="px-5 py-4">{statusBadge(r.report_status)}</td>
                                        <td className="px-5 py-4 text-gray-600 font-medium">{r.created_by}</td>
                                        <td className="px-5 py-4">
                                            <div className="flex items-center justify-end gap-1">
                                                <button
                                                    onClick={() => setSelectedReport(r)}
                                                    className="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors"
                                                    title="View Full Details"
                                                >
                                                    <Eye size={16} />
                                                </button>

                                                {r.report_status === 0 && canClose && (
                                                    <button
                                                        onClick={() => {
                                                            setIsClosing(r);
                                                            setClosureNotes('');
                                                            setClosureImage(null);
                                                        }}
                                                        className="p-2 text-green-600 hover:bg-green-50 rounded-xl transition-colors"
                                                        title="Resolve Hazard"
                                                    >
                                                        <CheckCircle size={16} />
                                                    </button>
                                                )}

                                                {canDelete && (
                                                    <button
                                                        onClick={() => handleDelete(r.id)}
                                                        className="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                                                        title="Delete Record"
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
                            Page {page} / {totalPages} · {total} entries
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
                                    <h2 className="text-2xl font-bold">Observation #{selectedReport.id}</h2>
                                    <p className="text-blue-100/80 text-sm mt-1">Logged by {selectedReport.created_by} on {new Date(selectedReport.date).toLocaleString()}</p>
                                </div>
                            </div>
                        </div>

                        <div className="p-8 space-y-10">
                            {/* Meta Grid */}
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-6">
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Project</p>
                                    <p className="font-semibold text-gray-900">{selectedReport.project_name}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Department</p>
                                    <p className="font-semibold text-gray-900">{selectedReport.department_name}</p>
                                </div>
                                <div>
                                    <p className="text-[10px) font-bold text-gray-400 uppercase tracking-widest mb-1.5">Risk Level</p>
                                    <span className={`text-[10px] px-2.5 py-1 rounded-full border font-black ${riskColor(selectedReport.risk)}`}>{selectedReport.risk}</span>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Status</p>
                                    {statusBadge(selectedReport.report_status)}
                                </div>
                            </div>

                            {/* Observation Body */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-bold text-gray-900">Observation Report</h3>
                                <div className="bg-gray-50 rounded-2xl p-6 text-gray-700 leading-relaxed border border-gray-100 whitespace-pre-wrap">
                                    {selectedReport.description || 'No detailed description provided.'}
                                </div>
                            </div>

                            {/* Observation Images */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-bold text-gray-900">Initial Evidence</h3>
                                <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    {(() => {
                                        try {
                                            const imgs = JSON.parse(selectedReport.image_upload);
                                            if (!imgs || imgs.length === 0) return <p className="text-sm text-gray-400 p-4 bg-gray-50 rounded-2xl">No images uploaded.</p>;
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
                                            return <p className="text-sm text-gray-400 p-4 bg-gray-50 rounded-2xl">No images uploaded.</p>;
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
                                        <h3 className="text-xl font-bold text-green-900">Resolution Verified</h3>
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
                                                <p className="text-[10px] font-black text-green-600 uppercase tracking-widest mb-1.5">Action Taken</p>
                                                <p className="text-green-900 leading-relaxed font-semibold">{selectedReport.closure_notes || 'Resolved.'}</p>
                                            </div>
                                            <div className="pt-4 border-t border-green-200/50">
                                                <p className="text-[10px] font-bold text-green-700/60 uppercase tracking-widest">Closed By Portfolio</p>
                                                <p className="text-sm text-green-900 font-bold mt-1 text-right italic">— {selectedReport.closed_by_username || 'System'}</p>
                                                <p className="text-[10px] text-green-700/40 text-right">{new Date(selectedReport.closed_at).toLocaleString()}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
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
                                <h2 className="text-2xl font-bold text-gray-900">Resolve Hazard</h2>
                                <p className="text-xs text-gray-500 mt-1 font-medium italic">Case ID #{isClosing.id}</p>
                            </div>
                            <button onClick={() => setIsClosing(null)} className="p-3 bg-white hover:bg-gray-100 rounded-2xl transition-all shadow-sm">
                                <X size={20} />
                            </button>
                        </div>
                        <form onSubmit={handleCloseSubmit} className="p-8 space-y-8">
                            <div className="space-y-3">
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Closure Notes (Final Action)</label>
                                <textarea
                                    required
                                    value={closureNotes}
                                    onChange={e => setClosureNotes(e.target.value)}
                                    rows={4}
                                    className="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 outline-none transition-all resize-none text-sm font-medium"
                                    placeholder="Briefly describe the corrective action implemented..."
                                />
                            </div>

                            <div className="space-y-3">
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Verification Photo</label>
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
                                            <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest">{isCompressing ? '...' : 'Upload'}</span>
                                            <input type="file" onChange={handleClosureImageChange} accept="image/*" className="hidden" disabled={isCompressing} />
                                        </label>
                                    )}
                                    <div className="flex-1">
                                        <p className="text-sm font-bold text-gray-700 leading-tight">Proof of compliance</p>
                                        <p className="text-[10px] text-gray-400 mt-1.5 font-medium italic">Images are automatically optimized for audit logs.</p>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={isSubmittingClosure || isCompressing}
                                className="w-full py-5 bg-green-600 text-white rounded-[1.25rem] font-black text-xs uppercase tracking-widest hover:bg-green-700 shadow-xl shadow-green-500/30 disabled:opacity-50 transition-all flex items-center justify-center gap-3 active:scale-95"
                            >
                                {isSubmittingClosure ? <Loader2 size={18} className="animate-spin" /> : <><CheckCircle size={18} /> Resolve Observation</>}
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DailyReportPage;
