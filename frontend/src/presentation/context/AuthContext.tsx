import React, { createContext, useContext, useState, useEffect, type ReactNode } from 'react';
import type { User, Permission, AuthState } from '../../domain/entities/Auth';
import api from '../../infrastructure/api/client';

interface LoginResponse {
    user: User;
    permissions: Permission[];
    token?: string;
}

interface AuthContextType extends AuthState {
    login: (credentials: any) => Promise<void>;
    logout: () => void;
    hasPermission: (page: string, action: string) => boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [authState, setAuthState] = useState<AuthState>({
        user: null,
        permissions: [],
        isAuthenticated: false,
    });

    useEffect(() => {
        const verifyAuth = async () => {
            try {
                const data = await api.get<any, LoginResponse>('/auth/me');
                if (data && data.user) {
                    setAuthState({
                        user: data.user,
                        permissions: data.permissions ?? [],
                        isAuthenticated: true,
                    });
                }
            } catch {
                setAuthState({ user: null, permissions: [], isAuthenticated: false });
            }
        };
        verifyAuth();
    }, []);

    const login = async (credentials: any) => {
        const data = await api.post<any, LoginResponse>('/auth/login', credentials);
        if (data.token) localStorage.setItem('hse_auth_token', data.token);
        setAuthState({
            user: data.user,
            permissions: data.permissions ?? [],
            isAuthenticated: true,
        });
    };

    const logout = async () => {
        try {
            await api.post('/auth/logout');
        } catch { /* ignore logout failures */ }
        localStorage.removeItem('hse_auth_token');
        setAuthState({ user: null, permissions: [], isAuthenticated: false });
        window.location.href = '/login';
    };

    const hasPermission = (page: string, action: string) => {
        return authState.permissions.some(
            (p) => (p.page === page || p.page === '*') && (p.action === action || p.action === '*')
        );
    };

    return (
        <AuthContext.Provider value={{ ...authState, login, logout, hasPermission }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) throw new Error('useAuth must be used within an AuthProvider');
    return context;
};
