export interface DailyReport {
    id: number;
    date: string;
    project_name: string;
    department_name: string;
    work_type: string;
    risk: string;
    observation_description: string;
    description: string;
    observation: string;
    operation_corrective: string;
    report_status: number; // 0=Open, 1=Closed
    closed_at: string | null;
    image_upload: string | null;
    created_by: string;
    closed_by_username: string | null;
    comments_count: number;
}

export interface ReportListResponse {
    data: DailyReport[];
    total: number;
    page: number;
    totalPages: number;
}
