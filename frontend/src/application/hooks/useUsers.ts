import { useQuery } from '@tanstack/react-query';
import api from '../../infrastructure/api/client';
import type { UserRecord } from '../../domain/entities/UserRecord';

export const useUsers = () => {
    const { data, isLoading, error } = useQuery<{ data: UserRecord[] }>({
        queryKey: ['users'],
        queryFn: () => api.get('/users/list'),
    });

    return {
        users: data?.data ?? [],
        isLoading,
        error,
    };
};
