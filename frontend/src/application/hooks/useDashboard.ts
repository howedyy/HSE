import { useQuery } from '@tanstack/react-query';
import api from '../../infrastructure/api/client';
import type { DashboardData } from '../../domain/entities/Dashboard';

export const useDashboard = () => {
    const { data, isLoading, error } = useQuery<DashboardData>({
        queryKey: ['dashboard-stats'],
        queryFn: () => api.get('/dashboard/stats'),
        refetchInterval: 30000, // Auto-refresh every 30 seconds
        staleTime: 10000,
    });

    return {
        dashboard: data ?? null,
        isLoading,
        error,
    };
};
