import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export const ProtectedRoute: React.FC = () => {
    const { isAuthenticated } = useAuth();

    if (!isAuthenticated) {
        return <Navigate to="/login" replace />;
    }

    return <Outlet />;
};

export const RoleGuard: React.FC<{ allowedRoles: number[]; children: React.ReactNode }> = ({ allowedRoles, children }) => {
    const { user } = useAuth();

    if (!user || !allowedRoles.includes(user.role)) {
        return <div className="p-8 text-center text-red-500 font-bold underline">Access Denied: Insufficient Permissions</div>;
    }

    return <>{children}</>;
};
