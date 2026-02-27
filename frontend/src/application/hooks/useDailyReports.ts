import { useQuery } from '@tanstack/react-query';
import api from '../../infrastructure/api/client';
import type { ReportListResponse } from '../../domain/entities/DailyReport';

export const useDailyReports = (filters: Record<string, string> = {}, page = 1) => {
    const params = new URLSearchParams({ ...filters, page: String(page), limit: '20' });

    const { data, isLoading, error } = useQuery<ReportListResponse>({
        queryKey: ['daily-reports', filters, page],
        queryFn: () => api.get(`/reports/list?${params.toString()}`),
    });

    return {
        reports: data?.data ?? [],
        total: data?.total ?? 0,
        totalPages: data?.totalPages ?? 1,
        currentPage: data?.page ?? 1,
        isLoading,
        error,
    };
};
