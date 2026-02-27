export type UserRole = 1 | 2 | 3; // 1: Admin, 2: HSE Supervisor, 3: Manager

export interface User {
    id: number;
    username: string;
    role: UserRole;
    departmentId?: number;
    editorName?: string;
    jobTitle?: string;
}

export interface Permission {
    page: string;
    action: string;
}

export interface AuthState {
    user: User | null;
    permissions: Permission[];
    isAuthenticated: boolean;
    token?: string;
}
