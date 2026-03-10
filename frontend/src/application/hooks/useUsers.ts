import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import api from '../../infrastructure/api/client';
import type { UserRecord } from '../../domain/entities/UserRecord';

export const useUsers = () => {
    const { data, isLoading, error, refetch } = useQuery<{ data: UserRecord[] }>({
        queryKey: ['users'],
        queryFn: () => api.get('/users/list'),
    });

    return {
        users: data?.data ?? [],
        isLoading,
        error,
        refetch
    };
};

export const useUserDetail = (id: number | null) => {
    return useQuery({
        queryKey: ['user', id],
        queryFn: () => api.get(`/users/get?id=${id}`),
        enabled: !!id,
    });
};

export const usePermissions = () => {
    return useQuery({
        queryKey: ['system-permissions'],
        queryFn: () => api.get('/users/permissions'),
    });
};

export const useRoleDefaults = (roleType: number | null) => {
    return useQuery({
        queryKey: ['role-defaults', roleType],
        queryFn: () => api.get(`/users/role_defaults?role_type=${roleType}`),
        enabled: !!roleType,
    });
};

export const useUpdateUser = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (data: any) => api.post('/users/update', data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['users'] });
        },
    });
};
