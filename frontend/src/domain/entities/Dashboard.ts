// Domain entity matching the dashboard API response
export interface DashboardStats {
    total_ptw: number;
    pending_ptw: number;
    completed_ptw: number;
    active_ptw: number;
    today_reports: number;
}

export interface BestPractices {
    percentage: number;
    projectName: string;
    totalGood: number;
}

export interface HighRisk {
    complianceRate: number;
    topProject: string;
    topDepartment: string;
    totalHighRisk: number;
    overdueCount: number;
}

export interface MonthlyTrend {
    month: string;
    count: number;
}

export interface Incident {
    id: number;
    date: string;
    description: string;
    risk: string;
    status: number;
    project: string;
}

export interface DashboardData {
    stats: DashboardStats;
    bestPractices: BestPractices;
    highRisk: HighRisk;
    taskCompletion: number;
    monthlyTrend: MonthlyTrend[];
    recentIncidents: Incident[];
}
