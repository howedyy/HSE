import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useLookups } from '../../application/hooks/useLookups';
import { useAuth } from '../context/AuthContext';
import api from '../../infrastructure/api/client';
import { ClipboardCheck, ArrowLeft, CheckCircle, Loader2 } from 'lucide-react';

const operationTypes = [
    "تقليم الجذور", "السباكة", "أعمال حفر", "أعمال لحام كهربي",
    "العمل على ارتفاع سبايدر", "أعمال رفع أحمال بمعدات ثقيلة",
    "العمل علي سقالة", "العمل على السلم المفصلى", "أعمال نقل بمعدات ثقيلة",
    "العمل بداخل الغرف المغلقة", "العمل على السلم الهيدروليكي",
    "إعمال كهرباء الجهد المتوسط", "العمل بالمواد الخطرة",
];

const selectClass = "w-full px-4 py-3 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all";
const inputClass = selectClass;
const labelClass = "block text-sm font-semibold text-gray-700 mb-1.5";

const CreatePTWPage: React.FC = () => {
    const navigate = useNavigate();
    const { departments } = useLookups();
    const { hasPermission, user } = useAuth();

    const [form, setForm] = useState({
        editor_name: user?.editorName ?? user?.username ?? '',
        job_title: user?.jobTitle ?? '',
        department: '', project_name: '', work_location: '',
        permit_date: '', start_time: '', end_time: '16:00',
        work_description: '', tools_equipment: '', operation_type: '',
        company_name: '', execution_manager: '', admin_signature: '',
    });
    const [submitting, setSubmitting] = useState(false);
    const [success, setSuccess] = useState<string | null>(null);
    const [error, setError] = useState('');

    // RBAC
    if (!hasPermission('ptw.php', 'submit')) {
        return (
            <div className="max-w-lg mx-auto text-center py-20">
                <div className="inline-flex p-4 rounded-full bg-red-50 text-red-400 mb-4">
                    <ClipboardCheck size={32} />
                </div>
                <h2 className="text-xl font-bold text-gray-800">Access Denied</h2>
                <p className="text-gray-500 mt-2">You do not have permission to submit PTW permits.</p>
            </div>
        );
    }

    const set = (key: string, value: string) => setForm(prev => ({ ...prev, [key]: value }));

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitting(true);
        setError('');
        try {
            const res: any = await api.post('/ptw/submit', form);
            setSuccess(res.permitNumber || 'Created');
            setTimeout(() => navigate('/permits'), 2000);
        } catch (err: any) {
            setError(err?.response?.data?.message || err?.message || 'Submission failed.');
        } finally {
            setSubmitting(false);
        }
    };

    if (success) {
        return (
            <div className="max-w-lg mx-auto text-center py-20">
                <div className="inline-flex p-4 rounded-full bg-green-50 text-green-500 mb-4 animate-bounce">
                    <CheckCircle size={40} />
                </div>
                <h2 className="text-xl font-bold text-gray-800">Permit {success} Submitted!</h2>
                <p className="text-gray-500 mt-2">Redirecting to permits list...</p>
            </div>
        );
    }

    const today = new Date().toISOString().split('T')[0];
    const maxDate = new Date(Date.now() + 2 * 86400000).toISOString().split('T')[0];

    return (
        <div className="max-w-2xl mx-auto">
            <button onClick={() => navigate('/permits')} className="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 mb-6 transition-colors">
                <ArrowLeft size={16} /> Back to Permits
            </button>

            <div className="bg-white rounded-2xl border border-gray-100 p-8">
                <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-3 mb-8">
                    <div className="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                        <ClipboardCheck size={20} className="text-indigo-600" />
                    </div>
                    New Permit to Work
                </h1>

                {error && <div className="bg-red-50 text-red-700 px-4 py-3 rounded-xl text-sm mb-6 border border-red-200">{error}</div>}

                <form onSubmit={handleSubmit} className="space-y-5">
                    {/* Section 1: Basic Info */}
                    <div className="text-xs uppercase tracking-wider text-indigo-600 font-semibold mb-1">Section 1 — القسم الأول</div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label className={labelClass}>Editor Name (اسم محرر الطلب)</label>
                            <input required value={form.editor_name} onChange={e => set('editor_name', e.target.value)} className={inputClass} />
                        </div>
                        <div>
                            <label className={labelClass}>Job Title (الوظيفه)</label>
                            <input required value={form.job_title} onChange={e => set('job_title', e.target.value)} className={inputClass} />
                        </div>
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label className={labelClass}>Department (الادارة)</label>
                            <select required value={form.department} onChange={e => set('department', e.target.value)} className={selectClass}>
                                <option value="">Select</option>
                                {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className={labelClass}>Project Name (المشروع)</label>
                            <input required value={form.project_name} onChange={e => set('project_name', e.target.value)} className={inputClass} placeholder="Enter project name" />
                        </div>
                    </div>
                    <div>
                        <label className={labelClass}>Work Location (موقع العمل)</label>
                        <input required value={form.work_location} onChange={e => set('work_location', e.target.value)} className={inputClass} />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label className={labelClass}>Permit Date</label>
                            <input type="date" required min={today} max={maxDate} value={form.permit_date} onChange={e => set('permit_date', e.target.value)} className={inputClass} />
                        </div>
                        <div>
                            <label className={labelClass}>Start Time</label>
                            <input type="time" required value={form.start_time} onChange={e => set('start_time', e.target.value)} className={inputClass} />
                        </div>
                        <div>
                            <label className={labelClass}>End Time</label>
                            <input type="time" required max="16:00" value={form.end_time} onChange={e => set('end_time', e.target.value)} className={inputClass} />
                        </div>
                    </div>
                    <div>
                        <label className={labelClass}>Work Description (وصف طبيعه العمل)</label>
                        <input required value={form.work_description} onChange={e => set('work_description', e.target.value)} className={inputClass} />
                    </div>
                    <div>
                        <label className={labelClass}>Tools & Equipment (المعدات و الادوات)</label>
                        <input required value={form.tools_equipment} onChange={e => set('tools_equipment', e.target.value)} className={inputClass} />
                    </div>

                    {/* Section 2: Operation Type */}
                    <div className="text-xs uppercase tracking-wider text-indigo-600 font-semibold mb-1 pt-4">Section 2 — نوع العملية</div>
                    <div>
                        <label className={labelClass}>Operation Type (نوع العملية)</label>
                        <select required value={form.operation_type} onChange={e => set('operation_type', e.target.value)} className={selectClass}>
                            <option value="">Select operation</option>
                            {operationTypes.map(op => <option key={op} value={op}>{op}</option>)}
                        </select>
                    </div>

                    {/* Section 3: Signatures */}
                    <div className="text-xs uppercase tracking-wider text-indigo-600 font-semibold mb-1 pt-4">Section 3 — التوقيعات</div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label className={labelClass}>Contractor Company (اختياري)</label>
                            <input value={form.company_name} onChange={e => set('company_name', e.target.value)} className={inputClass} placeholder="Leave empty if none" />
                        </div>
                        <div>
                            <label className={labelClass}>Execution Manager (مسئول التنفيذ)</label>
                            <input required value={form.execution_manager} onChange={e => set('execution_manager', e.target.value)} className={inputClass} />
                        </div>
                    </div>
                    <div>
                        <label className={labelClass}>Admin Signature (مسؤول الإدارة)</label>
                        <input required value={form.admin_signature} onChange={e => set('admin_signature', e.target.value)} className={inputClass} />
                    </div>

                    <button type="submit" disabled={submitting} className="w-full py-3.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 disabled:opacity-50 transition-all flex items-center justify-center gap-2">
                        {submitting ? <><Loader2 size={18} className="animate-spin" /> Submitting...</> : 'Submit Permit'}
                    </button>
                </form>
            </div>
        </div>
    );
};

export default CreatePTWPage;
