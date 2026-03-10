import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useLookups } from '../../application/hooks/useLookups';
import { useAuth } from '../context/AuthContext';
import api from '../../infrastructure/api/client';
import { ClipboardCheck, ArrowLeft, CheckCircle, Loader2, Paperclip } from 'lucide-react';

// ─── Operation → Risk mapping (mirrors PTW.php JS logic) ─────────────────────
const operationToRisk: Record<string, string> = {
    'تقليم الجذور': 'سقوط الاشجار  علي  الافراد والممتلكات',
    'السباكة': 'انسكاب وتسريب  وغمر',
    'أعمال حفر': 'عمل في حفر',
    'أعمال لحام كهربي': 'مخاطر حريق',
    'العمل على ارتفاع سبايدر': 'سقوط من على ارتفاع',
    'أعمال رفع أحمال بمعدات ثقيلة': 'ضوضاء',
    'العمل علي سقالة': 'سقوط من على ارتفاع',
    'العمل على السلم المفصلى': 'سقوط من على ارتفاع',
    'أعمال نقل بمعدات ثقيلة': 'ضوضاء',
    'العمل بداخل الغرف المغلقة': 'عمل في مكان مغلق',
    'العمل على السلم الهيدروليكي': 'سقوط من على ارتفاع',
    'إعمال كهرباء الجهد المتوسط': 'مخاطر كهربائية',
    'العمل بالمواد الخطرة': 'مخاطر كيميائية',
};

const operationTypes = Object.keys(operationToRisk);

// Section 4 safety measures (mirrors PTW.php checkboxes)
const safetyOptions = [
    'الاشراف الدائم',
    'تحليل مخاطر الوظيفية',
    'تقييم مخاطر',
    'عزل مصدر الطاقة',
    'اختبار غازات',
    'وسيلة اطفاء حريق مناسبة',
    'وضع شريط تحذير حول مكان العمل',
    'وضع اقماع فسفورية حول مكان العمل',
    'وضع علامة تحذيرية او علامات ارشادية',
    'محاضرة توعية بالمخاطر',
    'مهمات وقاية اضافية',
    'احتياطات سلامة أخرى',
];

const notes = [
    'يبدأ العمل فقط بإصدار هذا التصريح و إخطار إدارة الصيانة عند القيام بأي أعمال حفر قد تؤثر على البنية التحتية أو شبكة الكهرباء.',
    'هذا التصريح لا يعتبر صالحاً إلا إذا كانت كل الأقسام مملوءة بالكامل.',
    'لا تبدأ العمل قبل أن يتم اعتماد التصريح بالموافقة من قبل منسق تصريح العمل.',
    'يلغى التصريح تلقائياً في حالة تغير ظروف العمل أو إذا تم إجراء أي تعديل في التصريح بعد إصداره، ويجب إصدار تصريح جديد.',
    'يجب ألا يتم البدء بالعمل حتى تستكمل جميع معايير السلامة المذكورة في هذا التقرير من المرحلة الأولى إلى المرحلة السادسة.',
    'أقصى مدة لصلاحية التصريح هي 8 ساعات فقط، ويتم طلب تمديد للأعمال إذا لزم الأمر.',
];

// ─── Shared Style Tokens ────────────────────────────────────────────────────
const inputClass = "w-full px-4 py-3 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all";
const selectClass = inputClass;
const labelClass = "block text-sm font-semibold text-gray-700 mb-1.5";
const sectionHead = "text-xs uppercase tracking-wider text-indigo-600 font-semibold mb-3 pt-5 border-t border-gray-100 mt-5";

// ────────────────────────────────────────────────────────────────────────────
const CreatePTWPage: React.FC = () => {
    const navigate = useNavigate();
    const { departments, projects } = useLookups();
    const { hasPermission, user } = useAuth();

    const today = new Date().toISOString().split('T')[0];
    const maxDate = new Date(Date.now() + 2 * 86400000).toISOString().split('T')[0];

    // ── Form state ────────────────────────────────────────────────────────
    const [form, setForm] = useState({
        editor_name: user?.editorName ?? user?.username ?? '',
        job_title: user?.jobTitle ?? '',
        department: '',
        project_name: '',
        work_location: '',
        permit_date: '',
        start_time: '',
        end_time: '16:00',
        work_description: '',
        tools_equipment: '',
        operation_type: '',
        risk_assessment: '',
        company_name: '',
        execution_manager: '',
        admin_signature: '',
    });

    const [hasContractor, setHasContractor] = useState(false);
    const [safetyChecked, setSafetyChecked] = useState<string[]>([]);
    const [attachmentFile, setAttachmentFile] = useState<File | null>(null);
    const [submitting, setSubmitting] = useState(false);
    const [success, setSuccess] = useState<string | null>(null);
    const [error, setError] = useState('');

    // ── RBAC ─────────────────────────────────────────────────────────────
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

    // ── Helpers ───────────────────────────────────────────────────────────
    const set = (key: string, value: string) => setForm(prev => ({ ...prev, [key]: value }));

    const handleOperationChange = (op: string) => {
        set('operation_type', op);
        set('risk_assessment', operationToRisk[op] ?? '');
    };

    const toggleSafety = (option: string) => {
        setSafetyChecked(prev =>
            prev.includes(option) ? prev.filter(x => x !== option) : [...prev, option]
        );
    };

    // ── End-time validation (max 16:00) ──────────────────────────────────
    const handleEndTimeChange = (val: string) => {
        if (val > '16:00') {
            setError('لا يمكنك اختيار وقت انتهاء بعد الساعة 4:00 مساءً');
            set('end_time', '16:00');
        } else {
            setError('');
            set('end_time', val);
        }
    };

    // ── Submit ────────────────────────────────────────────────────────────
    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitting(true);
        setError('');

        try {
            // Build FormData to support file upload
            const fd = new FormData();
            Object.entries(form).forEach(([k, v]) => fd.append(k, v));
            safetyChecked.forEach(s => fd.append('safety_measures[]', s));
            if (attachmentFile) fd.append('attachment_image', attachmentFile);

            // Use axios directly with FormData (removes Content-Type so browser sets boundary)
            const res: any = await api.post('/ptw/submit', fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            setSuccess(res.permitNumber || 'Created');
            setTimeout(() => navigate('/permits'), 2500);
        } catch (err: any) {
            setError(err?.response?.data?.message || err?.message || 'Submission failed.');
        } finally {
            setSubmitting(false);
        }
    };

    // ── Success Screen ────────────────────────────────────────────────────
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

    // ── Render ────────────────────────────────────────────────────────────
    return (
        <div className="max-w-2xl mx-auto">
            <button
                onClick={() => navigate('/permits')}
                className="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 mb-6 transition-colors"
            >
                <ArrowLeft size={16} /> Back to Permits
            </button>

            <div className="bg-white rounded-2xl border border-gray-100 p-8">
                <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-3 mb-8">
                    <div className="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                        <ClipboardCheck size={20} className="text-indigo-600" />
                    </div>
                    New Permit to Work — تصريح العمل
                </h1>

                {error && (
                    <div className="bg-red-50 text-red-700 px-4 py-3 rounded-xl text-sm mb-6 border border-red-200">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-5" encType="multipart/form-data">

                    {/* ── Section 1: Basic Info ── القسم الأول ─────────────── */}
                    <div className={sectionHead}>Section 1 — القسم الأول</div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label className={labelClass}>اسم محرر الطلب (Editor Name)</label>
                            <input required className={inputClass} value={form.editor_name} onChange={e => set('editor_name', e.target.value)} />
                        </div>
                        <div>
                            <label className={labelClass}>الوظيفه (Job Title)</label>
                            <input required className={inputClass} value={form.job_title} onChange={e => set('job_title', e.target.value)} />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label className={labelClass}>الادارة (Department)</label>
                            <select required className={selectClass} value={form.department} onChange={e => set('department', e.target.value)}>
                                <option value="">اختار</option>
                                {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className={labelClass}>اسم المشروع (Project)</label>
                            <select required className={selectClass} value={form.project_name} onChange={e => set('project_name', e.target.value)}>
                                <option value="">اختـر المشروع</option>
                                {projects.map(p => <option key={p.id} value={p.project_name}>{p.project_name}</option>)}
                            </select>
                        </div>
                    </div>

                    <div>
                        <label className={labelClass}>موقع العمل (Work Location)</label>
                        <input required className={inputClass} value={form.work_location} onChange={e => set('work_location', e.target.value)} />
                    </div>

                    {/* Permit Number — auto-generated by backend */}
                    <div className="bg-gray-50 border border-dashed border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-500 italic">
                        رقم التصريح — سيتم توليده تلقائياً عند الإرسال
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label className={labelClass}>تاريخ بدء الاعمال</label>
                            <input type="date" required min={today} max={maxDate} className={inputClass} value={form.permit_date} onChange={e => set('permit_date', e.target.value)} />
                        </div>
                        <div>
                            <label className={labelClass}>توقيت بدء الاعمال</label>
                            <input type="time" required className={inputClass} value={form.start_time} onChange={e => set('start_time', e.target.value)} />
                        </div>
                        <div>
                            <label className={labelClass}>إلى الساعة (max 16:00)</label>
                            <input type="time" required max="16:00" className={inputClass} value={form.end_time} onChange={e => handleEndTimeChange(e.target.value)} />
                        </div>
                    </div>

                    <div>
                        <label className={labelClass}>وصف طبيعه العمل (Work Description)</label>
                        <input required className={inputClass} value={form.work_description} onChange={e => set('work_description', e.target.value)} />
                    </div>

                    <div>
                        <label className={labelClass}>المعدات و الادوات المستخدمه (Tools &amp; Equipment)</label>
                        <input required className={inputClass} value={form.tools_equipment} onChange={e => set('tools_equipment', e.target.value)} />
                    </div>

                    {/* Attachment image */}
                    <div>
                        <label className={labelClass}>
                            <Paperclip size={14} className="inline mr-1 text-gray-400" />
                            صورة مرفقة (اختياري)
                        </label>
                        <input
                            type="file"
                            accept="image/*"
                            className="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer"
                            onChange={e => setAttachmentFile(e.target.files?.[0] ?? null)}
                        />
                    </div>

                    {/* Contractor toggle */}
                    <div className="flex items-center gap-3">
                        <input
                            type="checkbox"
                            id="contractorToggle"
                            checked={hasContractor}
                            onChange={e => {
                                setHasContractor(e.target.checked);
                                if (!e.target.checked) set('company_name', '');
                            }}
                            className="w-4 h-4 accent-indigo-600 cursor-pointer"
                        />
                        <label htmlFor="contractorToggle" className="text-sm font-semibold text-gray-700 cursor-pointer">
                            هل يوجد مقاول؟ (Contractor)
                        </label>
                    </div>
                    {hasContractor && (
                        <div>
                            <label className={labelClass}>اسم الشركة/المقاول (Company Name)</label>
                            <input
                                className={inputClass}
                                placeholder="أدخل اسم الشركة/المقاول"
                                value={form.company_name}
                                onChange={e => set('company_name', e.target.value)}
                            />
                        </div>
                    )}

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label className={labelClass}>مسئول التنفيذ (Execution Manager)</label>
                            <input required className={inputClass} placeholder="أدخل اسم المسئول" value={form.execution_manager} onChange={e => set('execution_manager', e.target.value)} />
                        </div>
                        <div>
                            <label className={labelClass}>مسؤول الإدارة محرر التصريح (Admin Signature)</label>
                            <input required className={inputClass} placeholder="أدخل اسم المسؤول" value={form.admin_signature} onChange={e => set('admin_signature', e.target.value)} />
                        </div>
                    </div>

                    {/* ── Section 2: Operation Type ── القسم الثاني ────────── */}
                    <div className={sectionHead}>Section 2 — نوع العملية التي سيتم إجراؤها</div>

                    <div>
                        <label className={labelClass}>نوع العملية (Operation Type)</label>
                        <select required className={selectClass} value={form.operation_type} onChange={e => handleOperationChange(e.target.value)}>
                            <option value="">اختر نوع العملية</option>
                            {operationTypes.map(op => <option key={op} value={op}>{op}</option>)}
                        </select>
                    </div>

                    {/* ── Section 3: Risk ── القسم الثالث ─────────────────── */}
                    <div className={sectionHead}>Section 3 — طبيعة المخاطر والخطورة المرتبطة بالمهمة</div>

                    <div>
                        <label className={labelClass}>طبيعة المخاطر (Risk Assessment)</label>
                        <input
                            readOnly
                            className={`${inputClass} bg-gray-50 text-gray-600 italic cursor-not-allowed`}
                            value={form.risk_assessment || 'يتم تحديده تلقائياً بناءً على نوع العملية'}
                            dir="rtl"
                        />
                        <p className="mt-1 text-xs text-gray-400">
                            Auto-filled when you select an operation type above
                        </p>
                    </div>

                    {/* ── Section 4: Safety Measures ── القسم الرابع ──────── */}
                    <div className={sectionHead}>Section 4 — الإجراءات المتخذة</div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6">
                        {safetyOptions.map(opt => (
                            <label key={opt} className="flex items-center gap-3 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    checked={safetyChecked.includes(opt)}
                                    onChange={() => toggleSafety(opt)}
                                    className="w-4 h-4 accent-indigo-600"
                                />
                                <span className="text-sm text-gray-700 group-hover:text-indigo-600 transition-colors" dir="rtl">
                                    {opt}
                                </span>
                            </label>
                        ))}
                    </div>

                    {/* ── Section 5: Notes ── القسم الخامس ───────────────── */}
                    <div className={sectionHead}>Section 5 — ملاحظات</div>

                    <div className="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <ul className="space-y-2" dir="rtl">
                            {notes.map((n, i) => (
                                <li key={i} className="text-sm text-amber-800 flex gap-2">
                                    <span className="text-amber-500 font-bold mt-0.5">•</span>
                                    <span>{n}</span>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* ── Submit Button ─────────────────────────────────── */}
                    <button
                        type="submit"
                        disabled={submitting}
                        className="w-full py-3.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 disabled:opacity-50 transition-all flex items-center justify-center gap-2 mt-4"
                    >
                        {submitting
                            ? <><Loader2 size={18} className="animate-spin" /> جاري إرسال التصريح...</>
                            : 'إرسال البيانات — Submit Permit'
                        }
                    </button>
                </form>
            </div>
        </div>
    );
};

export default CreatePTWPage;
