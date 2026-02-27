import { useQuery } from '@tanstack/react-query';
import api from '../../infrastructure/api/client';

interface LookupData {
    projects: { id: string; project_name: string }[];
    departments: { id: string; department_name: string }[];
}

export const useLookups = () => {
    const { data, isLoading } = useQuery<LookupData>({
        queryKey: ['lookups'],
        queryFn: () => api.get('/lookup/options'),
        staleTime: 5 * 60 * 1000, // Cache for 5 minutes
    });

    return {
        projects: data?.projects ?? [],
        departments: data?.departments ?? [],
        isLoading,
    };
};
