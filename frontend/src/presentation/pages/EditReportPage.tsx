import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useLookups } from '../../application/hooks/useLookups';
import { useAuth } from '../context/AuthContext';
import api from '../../infrastructure/api/client';
import { FileText, ArrowLeft, CheckCircle, Loader2, Image as ImageIcon, X, AlertCircle } from 'lucide-react';
import { compressImage } from '../../shared/utils/imageCompression';

const workTypeMapping: Record<string, string[]> = {
    "متابعه الاعمال": [
        "اعمال روتينية", "تصريح عمل في اماكن محصورة", "تصريح عمل على ارتفاع",
        "اعمال خطرة بدون تصريح", "تصريح عمل بمواد كميائية خطرة", "تصريح حفر",
        "تصريح عزل طاقه", "تصريح اعمال ساخنه", "تصريح رفع"
    ],
    "فحص الموقع": [
        "فحص انظمة واجهزة الاطفاء", "فحص التوصيلات الكهربائية", "فحص انظمة السباكة",
        "فحص الحجر الهاشمي والرخام والجبسم بورد", "فحص الزجاج السيكوريت",
        "فحص الديكوريشن الخشب واللوفارات الالومنيوم", "فحص حالة التخزين",
        "فحص النظافة العامة للمكان", "فحص حالة الطريق", "فحص حالة اللاند اسكيب",
        "فحص البنية التحتية", "فحص وجود حشارات او حيوانات ضارة"
    ],
    "مخالفات السلوك": [
        "مخالفة قيادة مركبة", "عدم ارتداء مهمات الوقاية الشخصية", "التصرف بشكل غير امن"
    ],
};

const selectClass = "w-full px-4 py-3 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all";
const labelClass = "block text-sm font-semibold text-gray-700 mb-1.5";

const EditReportPage: React.FC = () => {
    const navigate = useNavigate();
    const { id } = useParams();
    const { projects, departments } = useLookups();
    const { user } = useAuth();
    const [form, setForm] = useState({
        project: '', department: '', observation: '', work_type: '', risk: '',
        observation_description: '', operation_corrective: '', description: '',
    });
    const [existingImages, setExistingImages] = useState<string[]>([]);
    const [newImages, setNewImages] = useState<File[]>([]);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState('');
    const [compressing, setCompressing] = useState(false);

    useEffect(() => {
        const fetchReport = async () => {
            try {
                const report: any = await api.get(`/reports/get?id=${id}`);

                // RBAC: Only admin or creator
                if (user?.role !== 1 && report.user_id !== user?.id) {
                    setError("You don't have permission to edit this report.");
                    setLoading(false);
                    return;
                }

                if (report.report_status !== 0) {
                    setError("This report is already resolved and cannot be edited.");
                    setLoading(false);
                    return;
                }

                if (report.email_sent === 1) {
                    setError("This report cannot be edited because an email has already been dispatched.");
                    setLoading(false);
                    return;
                }

                setForm({
                    project: report.project,
                    department: report.department,
                    observation: report.observation,
                    work_type: report.work_type,
                    risk: report.risk,
                    observation_description: report.observation_description,
                    operation_corrective: report.operation_corrective,
                    description: report.description,
                });

                try {
                    const imgs = JSON.parse(report.image_upload || '[]');
                    setExistingImages(Array.isArray(imgs) ? imgs : []);
                } catch {
                    setExistingImages(report.image_upload ? [report.image_upload] : []);
                }

                setLoading(false);
            } catch (err: any) {
                setError(err.response?.data?.message || err.message || "Failed to load report.");
                setLoading(false);
            }
        };
        fetchReport();
    }, [id, user]);

    const set = (key: string, value: string) => setForm(prev => ({ ...prev, [key]: value }));

    const handleImageChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        if (!e.target.files) return;
        setCompressing(true);
        const files = Array.from(e.target.files);
        const compressed = await Promise.all(
            files.map(f => compressImage(f))
        );
        setNewImages(prev => [...prev, ...compressed]);
        setCompressing(false);
    };

    const removeNewImage = (index: number) => {
        setNewImages(prev => prev.filter((_, i) => i !== index));
    };

    const removeExistingImage = (index: number) => {
        setExistingImages(prev => prev.filter((_, i) => i !== index));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitting(true);
        setError('');
        try {
            const formData = new FormData();
            formData.append('id', id || '');
            Object.entries(form).forEach(([key, value]) => {
                formData.append(key, value);
            });
            formData.append('existing_images', JSON.stringify(existingImages));
            newImages.forEach(img => {
                formData.append('images[]', img);
            });

            await api.post('/reports/update', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            setSuccess(true);
            setTimeout(() => navigate('/reports'), 1500);
        } catch (err: any) {
            setError(err?.response?.data?.message || err?.message || 'Update failed.');
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <div className="max-w-lg mx-auto text-center py-20 flex flex-col items-center gap-4">
                <Loader2 className="animate-spin text-blue-600" size={40} />
                <p className="text-gray-500 font-medium italic">Loading report details...</p>
            </div>
        );
    }

    if (success) {
        return (
            <div className="max-w-lg mx-auto text-center py-20">
                <div className="inline-flex p-4 rounded-full bg-green-50 text-green-500 mb-4 animate-bounce">
                    <CheckCircle size={40} />
                </div>
                <h2 className="text-xl font-bold text-gray-800">Report Updated!</h2>
                <p className="text-gray-500 mt-2">Redirecting to reports list...</p>
            </div>
        );
    }

    return (
        <div className="max-w-2xl mx-auto pb-10">
            <button onClick={() => navigate('/reports')} className="flex items-center gap-2 text-sm text-gray-500 hover:text-blue-600 mb-6 transition-colors font-medium">
                <ArrowLeft size={16} /> Back to Reports
            </button>

            <div className="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-3 mb-8">
                    <div className="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                        <FileText size={20} className="text-amber-600" />
                    </div>
                    Edit Observation #{id}
                </h1>

                {error && <div className="bg-red-50 text-red-700 px-5 py-4 rounded-xl text-sm mb-6 border border-red-200 flex items-center gap-3">
                    <AlertCircle size={18} />
                    {error}
                </div>}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label className={labelClass}>Project (المشروع)</label>
                            <select required value={form.project} onChange={e => set('project', e.target.value)} className={selectClass}>
                                <option value="">Select</option>
                                {projects.map(p => <option key={p.id} value={p.id}>{p.project_name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className={labelClass}>Department (القسم)</label>
                            <select required value={form.department} onChange={e => set('department', e.target.value)} className={selectClass}>
                                <option value="">Select</option>
                                {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                            </select>
                        </div>
                    </div>

                    <div>
                        <label className={labelClass}>Observation Type (طبيعه العمل)</label>
                        <select required value={form.observation} onChange={e => { set('observation', e.target.value); set('work_type', ''); }} className={selectClass}>
                            <option value="">Select</option>
                            <option value="متابعه الاعمال">متابعه الاعمال</option>
                            <option value="فحص الموقع">فحص الموقع</option>
                            <option value="مخالفات السلوك">مخالفات السلوك</option>
                        </select>
                    </div>

                    {form.observation && (
                        <div className="animate-in slide-in-from-top-2">
                            <label className={labelClass}>Work Description (وصف العمل)</label>
                            <select required value={form.work_type} onChange={e => set('work_type', e.target.value)} className={selectClass}>
                                <option value="">Select</option>
                                {workTypeMapping[form.observation]?.map(wt => <option key={wt} value={wt}>{wt}</option>)}
                            </select>
                        </div>
                    )}

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label className={labelClass}>Risk Level (شدة الخطورة)</label>
                            <select required value={form.risk} onChange={e => set('risk', e.target.value)} className={selectClass}>
                                <option value="">Select</option>
                                <option value="عالية">عالية (High)</option>
                                <option value="متوسطه">متوسطه (Medium)</option>
                                <option value="منخفضة">منخفضة (Low)</option>
                            </select>
                        </div>
                        <div>
                            <label className={labelClass}>Safety Compliance</label>
                            <select required value={form.observation_description} onChange={e => set('observation_description', e.target.value)} className={selectClass}>
                                <option value="">Select</option>
                                <option value="ممارسه جيده">ممارسة جيده (Good Practice)</option>
                                <option value="ملاحظة تحتاج الي تصحيح">ملاحظة تحتاج الي تصحيح (Needs Correction)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label className={labelClass}>Corrective Action (الاجراء التصحيحي)</label>
                        <select required value={form.operation_corrective} onChange={e => set('operation_corrective', e.target.value)} className={selectClass}>
                            <option value="">Select</option>
                            <option value="تم تنفيذ تعليمات السلامه">تم تنفيذ تعليمات السلامة</option>
                            <option value="لم يتم تنفيذ تعليمات السلامه">لم يتم تنفيذ تعليمات السلامة</option>
                            <option value="ايقاف الاعمال">ايقاف الاعمال</option>
                        </select>
                    </div>

                    <div>
                        <label className={labelClass}>Notes (ملاحظات)</label>
                        <textarea value={form.description} onChange={e => set('description', e.target.value)} rows={4} className={`${selectClass} resize-none`} placeholder="Enter additional observations..." />
                    </div>

                    <div className="space-y-4">
                        <label className={labelClass}>Observation Evidence (الصور الادله)</label>

                        {/* Existing Images */}
                        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            {existingImages.map((img, idx) => (
                                <div key={idx} className="relative group aspect-square rounded-xl overflow-hidden border border-gray-100 bg-gray-50 shadow-sm">
                                    <img src={`${import.meta.env.VITE_API_BASE_URL}/assests/uploads/${img}`} alt="existing" className="w-full h-full object-cover" />
                                    <button
                                        type="button"
                                        onClick={() => removeExistingImage(idx)}
                                        className="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                    >
                                        <X size={12} />
                                    </button>
                                    <div className="absolute inset-x-0 bottom-0 bg-black/40 text-white text-[8px] py-1 text-center font-bold">Existing</div>
                                </div>
                            ))}

                            {/* New Images */}
                            {newImages.map((img, idx) => (
                                <div key={idx} className="relative group aspect-square rounded-xl overflow-hidden border border-blue-100 shadow-sm">
                                    <img src={URL.createObjectURL(img)} alt="preview" className="w-full h-full object-cover" />
                                    <button
                                        type="button"
                                        onClick={() => removeNewImage(idx)}
                                        className="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                    >
                                        <X size={12} />
                                    </button>
                                    <div className="absolute inset-x-0 bottom-0 bg-blue-600/60 text-white text-[8px] py-1 text-center font-bold uppercase italic">New Upload</div>
                                </div>
                            ))}

                            <label className="aspect-square rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all hover:scale-[1.02]">
                                <ImageIcon size={24} className="text-gray-400" />
                                <span className="text-[10px] font-black uppercase text-gray-500 text-center px-2">
                                    {compressing ? 'Optimizing...' : 'Add More'}
                                </span>
                                <input
                                    type="file"
                                    multiple
                                    accept="image/*"
                                    onChange={handleImageChange}
                                    className="hidden"
                                    disabled={compressing}
                                />
                            </label>
                        </div>
                    </div>

                    <div className="flex gap-4 pt-4">
                        <button type="button" onClick={() => navigate('/reports')} className="flex-1 py-3.5 bg-gray-50 text-gray-700 rounded-xl font-bold border border-gray-100 hover:bg-gray-100 transition-all">
                            Cancel
                        </button>
                        <button type="submit" disabled={submitting || compressing} className="flex-[2] py-3.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 disabled:opacity-50 transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20 active:scale-95">
                            {submitting ? <><Loader2 size={18} className="animate-spin" /> Updating...</> : 'Save Changes'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default EditReportPage;
