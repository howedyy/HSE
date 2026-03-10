import React, { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { useLookups } from '../../application/hooks/useLookups';
import { useAuth } from '../context/AuthContext';
import api from '../../infrastructure/api/client';
import { phpUrl } from '../../shared/utils/phpUrl';
import {
    ClipboardCheck, ChevronLeft, ChevronRight, Search, Filter,
    X, Plus, ChevronDown, ChevronUp, CheckCircle, Flag,
    AlertTriangle, Trash2, FileText, Loader2, Download,
} from 'lucide-react';

// ─── Types ───────────────────────────────────────────────────────────────────
interface PTWPermit {
    id: number; permit_number: string; permit_date: string;
    editor_name: string; job_title: string; project_name: string;
    department_name: string; department: string; operation_type: string;
    ptw_status: number; work_location: string; work_description: string;
    tools_equipment: string; safety_measures: string; risk_assessment: string;
    company_name: string; execution_manager_signature: string;
    admin_signature: string; safety_manager: string; safety_signature: string;
    start_time: string; end_time: string;
}
interface HistoryEntry { action: string; action_by: string; action_date: string; notes: string; }
interface ImageEntry { image_path: string; image_type: string; }

// ─── Status Config ───────────────────────────────────────────────────────────
const STATUS: Record<number, { label: string; style: string; icon: string }> = {
    0: { label: 'Not Approved', style: 'bg-amber-50 text-amber-700 border-amber-200', icon: '⏳' },
    1: { label: 'Approved', style: 'bg-blue-50 text-blue-700 border-blue-200', icon: '✅' },
    2: { label: 'Finished', style: 'bg-green-50 text-green-700 border-green-200', icon: '✔' },
    3: { label: 'Not Completed', style: 'bg-orange-50 text-orange-700 border-orange-200', icon: '❌' },
    4: { label: 'Non-Compliance', style: 'bg-red-50 text-red-700 border-red-200', icon: '⚠️' },
};

const OPERATIONS = [
    'تقليم الجذور', 'السباكة', 'أعمال حفر', 'أعمال لحام كهربي',
    'العمل على ارتفاع سبايدر', 'أعمال رفع أحمال بمعدات ثقيلة',
    'العمل علي سقالة', 'العمل على السلم المفصلى', 'أعمال نقل بمعدات ثقيلة',
    'العمل بداخل الغرف المغلقة', 'العمل على السلم الهيدروليكي',
    'إعمال كهرباء الجهد المتوسط', 'العمل بالمواد الخطرة',
];

const ic = "w-full px-3 py-2 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none transition-all";
const btn = (color: string) => `inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-all hover:-translate-y-0.5 shadow-sm disabled:opacity-40 disabled:cursor-not-allowed disabled:translate-y-0 ${color}`;

// ─── Sub-component: Expandable Detail Row ─────────────────────────────────────
const ExpandedDetails: React.FC<{ permit: PTWPermit }> = ({ permit }) => {
    const { data: history, isLoading: hLoad } = useQuery<HistoryEntry[]>({
        queryKey: ['ptw-history', permit.permit_number],
        queryFn: () => api.get(`/ptw/history?permit_number=${encodeURIComponent(permit.permit_number)}`),
        staleTime: 30000,
    });
    const { data: images, isLoading: iLoad } = useQuery<ImageEntry[]>({
        queryKey: ['ptw-images', permit.permit_number],
        queryFn: () => api.get(`/ptw/images?permit_number=${encodeURIComponent(permit.permit_number)}`),
        staleTime: 30000,
    });

    const [lightbox, setLightbox] = useState<string | null>(null);
    const baseUrl = (import.meta.env.VITE_API_URL || '/api').replace('/api', '');

    return (
        <tr className="bg-gray-50">
            <td colSpan={13} className="px-6 py-5">
                {lightbox && (
                    <div className="fixed inset-0 z-50 bg-black/80 flex items-center justify-center" onClick={() => setLightbox(null)}>
                        <img src={lightbox} alt="PTW" className="max-h-[90vh] max-w-[90vw] rounded-xl shadow-2xl" />
                    </div>
                )}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {/* Details columns */}
                    <div className="md:col-span-2 space-y-3">
                        <h4 className="font-bold text-gray-700 text-sm flex items-center gap-2">
                            <FileText size={14} className="text-indigo-500" /> Permit Details
                        </h4>
                        <div className="grid grid-cols-2 gap-2 text-xs text-gray-600">
                            {[
                                ['Work Location', permit.work_location],
                                ['Work Description', permit.work_description],
                                ['Tools & Equipment', permit.tools_equipment],
                                ['Risk Assessment', permit.risk_assessment],
                                ['Safety Measures', permit.safety_measures],
                                ['Execution Manager', permit.execution_manager_signature],
                                ['Admin Signature', permit.admin_signature],
                                ['Safety Manager', permit.safety_manager],
                                ['Start Time', permit.start_time],
                                ['End Time', permit.end_time],
                                ['Company', permit.company_name || '—'],
                            ].map(([label, val]) => val ? (
                                <div key={label} className="bg-white rounded-lg p-2.5 border border-gray-100">
                                    <div className="font-semibold text-gray-500 text-[10px] uppercase mb-0.5">{label}</div>
                                    <div className="text-gray-800 font-medium" dir="rtl">{val}</div>
                                </div>
                            ) : null)}
                        </div>

                        {/* History */}
                        <h4 className="font-bold text-gray-700 text-sm flex items-center gap-2 mt-4">
                            📋 PTW History
                        </h4>
                        {hLoad ? (
                            <div className="h-6 bg-gray-200 animate-pulse rounded" />
                        ) : history?.length ? (
                            <div className="space-y-1.5">
                                {history.map((h, i) => (
                                    <div key={i} className="flex items-start gap-3 bg-white border-l-4 border-indigo-400 rounded-r-lg p-2.5">
                                        <div className="flex-shrink-0 w-20 text-[10px] text-gray-400 font-mono">
                                            {new Date(h.action_date).toLocaleDateString()}
                                        </div>
                                        <div>
                                            <span className="font-semibold text-xs text-indigo-700">{h.action}</span>
                                            <span className="text-gray-500 text-xs ml-2">by {h.action_by}</span>
                                            {h.notes && <div className="text-xs text-gray-500 mt-0.5">{h.notes}</div>}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-xs text-gray-400 italic">No history recorded yet.</p>
                        )}
                    </div>

                    {/* Images */}
                    <div>
                        <h4 className="font-bold text-gray-700 text-sm flex items-center gap-2 mb-3">
                            📷 Attached Images
                        </h4>
                        {iLoad ? (
                            <div className="h-20 bg-gray-200 animate-pulse rounded-xl" />
                        ) : images?.length ? (
                            <div className="flex flex-wrap gap-2">
                                {images.map((img, i) => (
                                    <img
                                        key={i}
                                        src={`${baseUrl}/assests/uploads/ptw_closure/${img.image_path}`}
                                        alt={img.image_type}
                                        className="w-24 h-24 object-cover rounded-xl border-2 border-gray-200 cursor-pointer hover:scale-105 transition-transform shadow-sm"
                                        onClick={() => setLightbox(`${baseUrl}/assests/uploads/ptw_closure/${img.image_path}`)}
                                    />
                                ))}
                            </div>
                        ) : (
                            <div className="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center text-xs text-gray-400">
                                No images attached
                            </div>
                        )}
                    </div>
                </div>
            </td>
        </tr>
    );
};

// ─── Modal: Approve ───────────────────────────────────────────────────────────
const ApproveModal: React.FC<{ permit: PTWPermit; onClose: () => void; onDone: () => void }> = ({ permit, onClose, onDone }) => {
    const [form, setForm] = useState({ safety_manager: '', safety_signature: '' });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const submit = async () => {
        setLoading(true); setError('');
        try {
            await api.post('/ptw/approve', { permit_number: permit.permit_number, ...form });
            onDone();
        } catch (e: any) {
            setError(e?.response?.data?.message || 'Failed to approve.');
        } finally { setLoading(false); }
    };

    return (
        <Modal title={`✅ Approve Permit ${permit.permit_number}`} onClose={onClose}>
            <p className="text-sm text-gray-500 mb-4">Enter safety details to approve this permit.</p>
            {error && <div className="text-xs text-red-600 bg-red-50 p-2 rounded-lg mb-3">{error}</div>}
            <label className="block text-xs font-semibold text-gray-700 mb-1">Safety Manager</label>
            <input className={`${ic} mb-3`} value={form.safety_manager} onChange={e => setForm(f => ({ ...f, safety_manager: e.target.value }))} placeholder="أدخل اسم مسؤول السلامة" />
            <label className="block text-xs font-semibold text-gray-700 mb-1">Safety Signature</label>
            <input className={`${ic} mb-4`} value={form.safety_signature} onChange={e => setForm(f => ({ ...f, safety_signature: e.target.value }))} placeholder="التوقيع" />
            <button disabled={loading || !form.safety_manager} onClick={submit} className="w-full py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 disabled:opacity-40 flex items-center justify-center gap-2">
                {loading ? <Loader2 size={16} className="animate-spin" /> : <CheckCircle size={16} />} Approve Permit
            </button>
        </Modal>
    );
};

// ─── Modal: Finish ────────────────────────────────────────────────────────────
const FinishModal: React.FC<{ permit: PTWPermit; onClose: () => void; onDone: () => void }> = ({ permit, onClose, onDone }) => {
    const [form, setForm] = useState({ cancellation_reason: '', completion_date: '', admin_signature_3: '', safety_signature_3: '' });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const submit = async () => {
        setLoading(true); setError('');
        try {
            await api.post('/ptw/finish', { permit_number: permit.permit_number, ...form });
            onDone();
        } catch (e: any) {
            setError(e?.response?.data?.message || 'Failed to finish.');
        } finally { setLoading(false); }
    };

    return (
        <Modal title={`✔ Finish Permit ${permit.permit_number}`} onClose={onClose}>
            <p className="text-sm text-gray-500 mb-4">Complete or close this permit. Leave cancellation reason empty to mark as Finished.</p>
            {error && <div className="text-xs text-red-600 bg-red-50 p-2 rounded-lg mb-3">{error}</div>}
            {[
                ['Completion Date', 'date', 'completion_date'],
                ['Admin Signature', 'text', 'admin_signature_3'],
                ['Safety Signature', 'text', 'safety_signature_3'],
            ].map(([label, type, key]) => (
                <div key={key} className="mb-3">
                    <label className="block text-xs font-semibold text-gray-700 mb-1">{label}</label>
                    <input type={type} className={ic} value={(form as any)[key]} onChange={e => setForm(f => ({ ...f, [key]: e.target.value }))} />
                </div>
            ))}
            <div className="mb-4">
                <label className="block text-xs font-semibold text-gray-700 mb-1">Cancellation Reason (if not completed)</label>
                <input className={ic} value={form.cancellation_reason} onChange={e => setForm(f => ({ ...f, cancellation_reason: e.target.value }))} placeholder="Leave empty if work was completed" />
            </div>
            <button disabled={loading} onClick={submit} className="w-full py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 disabled:opacity-40 flex items-center justify-center gap-2">
                {loading ? <Loader2 size={16} className="animate-spin" /> : <Flag size={16} />}
                {form.cancellation_reason ? 'Mark Not Completed' : 'Mark as Finished'}
            </button>
        </Modal>
    );
};

// ─── Reusable Modal Wrapper ───────────────────────────────────────────────────
const Modal: React.FC<{ title: string; onClose: () => void; children: React.ReactNode }> = ({ title, onClose, children }) => (
    <div className="fixed inset-0 z-40 bg-black/50 flex items-center justify-center p-4" onClick={e => e.target === e.currentTarget && onClose()}>
        <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div className="flex items-center justify-between mb-5">
                <h3 className="font-bold text-gray-900 text-base">{title}</h3>
                <button onClick={onClose} className="text-gray-400 hover:text-gray-600 transition-colors"><X size={20} /></button>
            </div>
            {children}
        </div>
    </div>
);

// ─── Main Page ────────────────────────────────────────────────────────────────
const PTWPage: React.FC = () => {
    const navigate = useNavigate();
    const qc = useQueryClient();
    const { hasPermission } = useAuth();
    const { projects, departments } = useLookups();

    const [page, setPage] = useState(1);
    const [filters, setFilters] = useState<Record<string, string>>({});
    const [showFilters, setShowFilters] = useState(false);
    const [expanded, setExpanded] = useState<string | null>(null);
    const [approving, setApproving] = useState<PTWPermit | null>(null);
    const [finishing, setFinishing] = useState<PTWPermit | null>(null);
    const [actionError, setActionError] = useState('');

    // Permissions — mirror ptw_overview.php hasAccess() calls
    const canSubmit = hasPermission('ptw.php', 'submit');
    const canEdit = hasPermission('ptw_overview.php', 'edit');
    const canFinish = hasPermission('ptw_overview.php', 'finish');
    const canDelete = hasPermission('ptw_overview.php', 'delete');
    const canExport = hasPermission('ptw_overview.php', 'export') || hasPermission('ptw_overview.php', 'export_excel') || hasPermission('ptw_overview.php', 'view');

    // Data fetch
    const params = new URLSearchParams({ ...filters, page: String(page), limit: '20' });
    const { data, isLoading } = useQuery<{ data: PTWPermit[]; total: number; totalPages: number; page: number }>({
        queryKey: ['ptw-permits', filters, page],
        queryFn: () => api.get(`/ptw/list?${params.toString()}`),
    });

    const permits = data?.data ?? [];
    const total = data?.total ?? 0;
    const totalPages = data?.totalPages ?? 1;

    const invalidate = useCallback(() => {
        qc.invalidateQueries({ queryKey: ['ptw-permits'] });
    }, [qc]);

    const updateFilter = (key: string, value: string) => {
        setPage(1);
        setFilters(prev => {
            const next = { ...prev };
            if (value === '') delete next[key]; else next[key] = value;
            return next;
        });
    };

    const handleNonCompliance = async (permit: PTWPermit) => {
        if (!window.confirm(`Mark permit ${permit.permit_number} as Non-Compliance?`)) return;
        try {
            await api.post('/ptw/noncompliance', { permit_number: permit.permit_number });
            invalidate();
        } catch (e: any) {
            setActionError(e?.response?.data?.message || 'Failed.');
        }
    };

    const handleDelete = async (permit: PTWPermit) => {
        if (!window.confirm(`Delete permit ${permit.permit_number} permanently? This cannot be undone.`)) return;
        try {
            await api.post('/ptw/delete', { permit_number: permit.permit_number });
            invalidate();
        } catch (e: any) {
            setActionError(e?.response?.data?.message || 'Failed to delete.');
        }
    };

    return (
        <div className="max-w-full mx-auto space-y-5">
            {/* Action error banner */}
            {actionError && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                    {actionError}
                    <button onClick={() => setActionError('')}><X size={16} /></button>
                </div>
            )}

            {/* Modals */}
            {approving && <ApproveModal permit={approving} onClose={() => setApproving(null)} onDone={() => { setApproving(null); invalidate(); }} />}
            {finishing && <FinishModal permit={finishing} onClose={() => setFinishing(null)} onDone={() => { setFinishing(null); invalidate(); }} />}

            {/* Header */}
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <ClipboardCheck size={24} className="text-indigo-600" /> Permit to Work Overview
                    </h1>
                    <p className="text-sm text-gray-500 mt-1">{total} permits in system</p>
                </div>
                <div className="flex items-center gap-3 flex-wrap">
                    {canExport && (
                        <a href={phpUrl('export_ptw_overview_excel.php')} target="_blank" rel="noreferrer"
                            className="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition-colors shadow-sm">
                            <Download size={15} /> Export Excel
                        </a>
                    )}
                    <button
                        onClick={() => setShowFilters(!showFilters)}
                        className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all ${showFilters ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}`}
                    >
                        {showFilters ? <X size={16} /> : <Filter size={16} />}
                        {showFilters ? 'Hide Filters' : 'Filter'}
                        {Object.keys(filters).length > 0 && !showFilters && (
                            <span className="ml-1 bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                {Object.keys(filters).length}
                            </span>
                        )}
                    </button>
                    {canSubmit && (
                        <button onClick={() => navigate('/permits/new')}
                            className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm">
                            <Plus size={16} /> New Permit
                        </button>
                    )}
                </div>
            </div>

            {/* Filter Panel */}
            {showFilters && (
                <div className="bg-white rounded-2xl border border-gray-100 p-5">
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Permit Number</label>
                            <input placeholder="Search PTW..." className={ic} value={filters.permitNumber ?? ''}
                                onChange={e => updateFilter('permitNumber', e.target.value)} />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Project</label>
                            <select className={ic} value={filters.project ?? ''} onChange={e => updateFilter('project', e.target.value)}>
                                <option value="">All Projects</option>
                                {projects.map(p => <option key={p.id} value={p.project_name}>{p.project_name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Department</label>
                            <select className={ic} value={filters.department ?? ''} onChange={e => updateFilter('department', e.target.value)}>
                                <option value="">All Departments</option>
                                {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Operation Type</label>
                            <select className={ic} value={filters.operation ?? ''} onChange={e => updateFilter('operation', e.target.value)}>
                                <option value="">All Operations</option>
                                {OPERATIONS.map(op => <option key={op} value={op}>{op}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                            <select className={ic} value={filters.status ?? ''} onChange={e => updateFilter('status', e.target.value)}>
                                <option value="">All Statuses</option>
                                <option value="0">⏳ Not Approved</option>
                                <option value="1">✅ Approved</option>
                                <option value="2">✔ Finished</option>
                                <option value="3">❌ Not Completed</option>
                                <option value="4">⚠️ Non-Compliance</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Start Date</label>
                            <input type="date" className={ic} value={filters.startDate ?? ''} onChange={e => updateFilter('startDate', e.target.value)} />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">End Date</label>
                            <input type="date" className={ic} value={filters.endDate ?? ''} onChange={e => updateFilter('endDate', e.target.value)} />
                        </div>
                        <div className="flex items-end">
                            <button onClick={() => { setFilters({}); setPage(1); }}
                                className="w-full py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Table */}
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
                                    <th className="px-3 py-3 w-8"></th>
                                    {['Permit #', 'Department', 'Project', 'Work Location', 'Date', 'Operation', 'Status', 'Actions'].map(h => (
                                        <th key={h} className="text-left px-4 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold whitespace-nowrap">{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {permits.map(p => {
                                    const s = STATUS[p.ptw_status] ?? STATUS[0];
                                    const isOpen = expanded === p.permit_number;
                                    return (
                                        <React.Fragment key={p.id}>
                                            <tr className={`border-b border-gray-50 transition-colors ${isOpen ? 'bg-indigo-50/40' : 'hover:bg-gray-50/60'}`}>
                                                {/* Expand toggle */}
                                                <td className="px-3 py-3">
                                                    <button
                                                        onClick={() => setExpanded(isOpen ? null : p.permit_number)}
                                                        className={`w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold transition-colors ${isOpen ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600'}`}
                                                    >
                                                        {isOpen ? <ChevronUp size={14} /> : <ChevronDown size={14} />}
                                                    </button>
                                                </td>
                                                <td className="px-4 py-3 font-mono text-xs text-indigo-600 font-bold whitespace-nowrap">{p.permit_number}</td>
                                                <td className="px-4 py-3 text-gray-600 text-xs">{p.department_name || '—'}</td>
                                                <td className="px-4 py-3 text-gray-800 font-medium text-xs max-w-[120px] truncate">{p.project_name}</td>
                                                <td className="px-4 py-3 text-gray-600 text-xs max-w-[100px] truncate">{p.work_location}</td>
                                                <td className="px-4 py-3 text-gray-600 text-xs">{p.permit_date ? new Date(p.permit_date).toLocaleDateString('en-GB') : '—'}</td>
                                                <td className="px-4 py-3 text-gray-600 text-xs max-w-[140px] truncate" dir="rtl">{p.operation_type}</td>
                                                <td className="px-4 py-3">
                                                    <span className={`text-[10px] px-2 py-1 rounded-full border font-semibold whitespace-nowrap ${s.style}`}>
                                                        {s.icon} {s.label}
                                                    </span>
                                                </td>
                                                {/* Actions */}
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-1.5 flex-wrap">
                                                        {/* PDF Export */}
                                                        {canExport && (
                                                            <a href={phpUrl('export_ptw_pdf.php', { permit_number: p.permit_number })}
                                                                target="_blank" rel="noreferrer"
                                                                className={btn('bg-red-500 hover:bg-red-600')}>
                                                                <FileText size={11} /> PDF
                                                            </a>
                                                        )}
                                                        {/* Approve (edit permission, only for Pending) */}
                                                        {canEdit && (
                                                            <button
                                                                disabled={p.ptw_status !== 0}
                                                                onClick={() => setApproving(p)}
                                                                className={btn('bg-green-500 hover:bg-green-600')}>
                                                                <CheckCircle size={11} /> Approve
                                                            </button>
                                                        )}
                                                        {/* Finish (finish permission, only for Approved) */}
                                                        {canFinish && (
                                                            <button
                                                                disabled={p.ptw_status !== 1}
                                                                onClick={() => setFinishing(p)}
                                                                className={btn('bg-blue-500 hover:bg-blue-600')}>
                                                                <Flag size={11} /> Finish
                                                            </button>
                                                        )}
                                                        {/* Non-Compliance (edit permission, only for Approved) */}
                                                        {canEdit && (
                                                            <button
                                                                disabled={p.ptw_status !== 1}
                                                                onClick={() => handleNonCompliance(p)}
                                                                className={btn('bg-orange-500 hover:bg-orange-600')}>
                                                                <AlertTriangle size={11} /> Non-Compliance
                                                            </button>
                                                        )}
                                                        {/* Delete (delete permission) */}
                                                        {canDelete && (
                                                            <button
                                                                onClick={() => handleDelete(p)}
                                                                className={btn('bg-rose-600 hover:bg-rose-700')}>
                                                                <Trash2 size={11} /> Delete
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                            {/* Expanded detail row */}
                                            {isOpen && <ExpandedDetails permit={p} />}
                                        </React.Fragment>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination */}
                {totalPages > 1 && (
                    <div className="flex items-center justify-between px-5 py-4 border-t border-gray-100">
                        <p className="text-xs text-gray-400">Page {page} of {totalPages} · {total} records</p>
                        <div className="flex items-center gap-2">
                            <button disabled={page <= 1} onClick={() => setPage(1)} className="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 text-xs">⇤</button>
                            <button disabled={page <= 1} onClick={() => setPage(p => p - 1)} className="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30"><ChevronLeft size={15} /></button>
                            {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                                const start = Math.max(1, Math.min(page - 2, totalPages - 4));
                                const num = start + i;
                                if (num > totalPages) return null;
                                return (
                                    <button key={num} onClick={() => setPage(num)}
                                        className={`w-9 h-9 rounded-lg text-sm font-medium ${page === num ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 border border-gray-200'}`}>
                                        {num}
                                    </button>
                                );
                            })}
                            <button disabled={page >= totalPages} onClick={() => setPage(p => p + 1)} className="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30"><ChevronRight size={15} /></button>
                            <button disabled={page >= totalPages} onClick={() => setPage(totalPages)} className="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 text-xs">⇥</button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default PTWPage;
