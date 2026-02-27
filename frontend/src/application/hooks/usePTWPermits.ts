import { useQuery } from '@tanstack/react-query';
import api from '../../infrastructure/api/client';
import type { PTWListResponse } from '../../domain/entities/PTW';

export const usePTWPermits = (filters: Record<string, string> = {}, page = 1) => {
    const params = new URLSearchParams({ ...filters, page: String(page), limit: '20' });

    const { data, isLoading, error } = useQuery<PTWListResponse>({
        queryKey: ['ptw-permits', filters, page],
        queryFn: () => api.get(`/ptw/list?${params.toString()}`),
    });

    return {
        permits: data?.data ?? [],
        total: data?.total ?? 0,
        totalPages: data?.totalPages ?? 1,
        currentPage: data?.page ?? 1,
        isLoading,
        error,
    };
};
