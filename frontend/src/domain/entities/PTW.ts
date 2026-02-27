export interface PTWPermit {
    id: number;
    permit_number: string;
    permit_date: string;
    editor_name: string;
    job_title: string;
    project_name: string;
    department_name: string;
    operation_type: string;
    ptw_status: number; // 0=Pending, 1=Approved, 2=Completed
    work_location: string;
    work_description: string;
}

export interface PTWListResponse {
    data: PTWPermit[];
    total: number;
    page: number;
    totalPages: number;
}
