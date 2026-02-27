import React from 'react';
import type { DailyReport } from '../../domain/entities/DailyReport';
import { AlertCircle, CheckCircle2, Clock, MapPin } from 'lucide-react';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

interface Props {
    report: DailyReport;
    className?: string;
}

export const DailyReportCard: React.FC<Props> = ({ report, className }) => {
    const riskColors = {
        Low: 'bg-green-100 text-green-800 border-green-200',
        Medium: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        High: 'bg-red-100 text-red-800 border-red-200',
    };

    return (
        <div className={cn("p-4 rounded-xl border bg-white shadow-sm hover:shadow-md transition-shadow", className)}>
            <div className="flex justify-between items-start mb-3">
                <div className="flex items-center gap-2">
                    <span className={cn("px-2.5 py-0.5 rounded-full text-xs font-semibold border", riskColors[report.risk])}>
                        {report.risk} Risk
                    </span>
                    <span className="text-gray-400 text-xs flex items-center gap-1">
                        <Clock size={12} />
                        {new Date(report.date).toLocaleDateString()}
                    </span>
                </div>
                {report.reportStatus === 1 ? (
                    <CheckCircle2 size={18} className="text-green-500" />
                ) : (
                    <AlertCircle size={18} className="text-amber-500" />
                )}
            </div>

            <h3 className="font-bold text-gray-900 mb-1">{report.observation}</h3>
            <p className="text-sm text-gray-600 line-clamp-2 mb-3">{report.description}</p>

            <div className="grid grid-cols-2 gap-2 mt-auto">
                <div className="flex items-center gap-1.5 text-xs text-gray-500">
                    <MapPin size={12} />
                    {report.project}
                </div>
                <div className="text-right text-xs text-gray-400 italic">
                    {report.department}
                </div>
            </div>
        </div>
    );
};
