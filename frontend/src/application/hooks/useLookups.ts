import { useQuery } from '@tanstack/react-query';
import api from '../../infrastructure/api/client';

interface LookupData {
    projects: { id: string; project_name: string }[];
    departments: { id: string; department_name: string }[];
    users: { id: string; username: string }[];
}

export interface WorkType {
    id: number;
    name: string;
    status: number;
}

export interface ObservationType {
    id: number;
    name: string;
    status: number;
    work_types: WorkType[];
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
        users: data?.users ?? [],
        isLoading,
    };
};

export const useReportOptions = () => {
    const { data, isLoading, refetch } = useQuery<{ data: ObservationType[] }>({
        queryKey: ['report_options'],
        queryFn: () => api.get('/report_options/list'),
        staleTime: 5 * 60 * 1000,
    });

    return {
        observationTypes: data?.data ?? [],
        isLoading,
        refetch,
    };
};

export interface PTWOperationType {
    id: number;
    operation_name: string;
    risk_assessment: string;
    is_active: number;
}

export interface PTWSafetyMeasure {
    id: number;
    measure_name: string;
    is_active: number;
}

export const usePTWOptions = () => {
    const { data, isLoading, refetch } = useQuery<{ operation_types: PTWOperationType[], safety_measures: PTWSafetyMeasure[] }>({
        queryKey: ['ptw_options'],
        queryFn: () => api.get('/ptw_config/list'),
        staleTime: 5 * 60 * 1000,
    });

    return {
        operationTypes: data?.operation_types ?? [],
        safetyMeasures: data?.safety_measures ?? [],
        isLoading,
        refetch,
    };
};
