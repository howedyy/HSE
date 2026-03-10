import React, { type ReactNode } from 'react';
import { useAuth } from '../context/AuthContext';
import { NavLink } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import {
    LayoutDashboard, FileText, ClipboardCheck,
    Settings, LogOut, Bell, ShieldCheck, Users, BarChart2, Activity, Globe
} from 'lucide-react';

interface Props {
    children: ReactNode;
}

const MainLayout: React.FC<Props> = ({ children }) => {
    const { user, logout } = useAuth();
    const { t, i18n } = useTranslation();

    const initials = user?.username
        ? user.username.substring(0, 2).toUpperCase()
        : 'U';

    const roleName = user?.role === 1 ? t('roles.admin') :
        user?.role === 2 ? t('roles.hse') :
        user?.role === 3 ? t('roles.operation') : t('roles.unknown');

    const toggleLanguage = () => {
        const nextLng = i18n.language === 'en' ? 'ar' : 'en';
        i18n.changeLanguage(nextLng);
    };

    return (
        <div className="flex h-screen bg-gray-50 font-sans text-gray-900">
            {/* Sidebar */}
            <aside className="w-64 bg-white border-r border-l flex flex-col shrink-0">
                <div className="p-6 border-b">
                    <div className="flex items-center gap-2">
                        <div className="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                            <ShieldCheck size={18} className="text-white" />
                        </div>
                        <div>
                            <h1 className="text-lg font-bold text-gray-900 leading-none">{t('app.title')}</h1>
                            <p className="text-[10px] text-gray-400 uppercase tracking-wider">{t('app.subtitle')}</p>
                        </div>
                    </div>
                </div>

                <nav className="flex-1 px-3 py-4 space-y-1">
                    <SidebarLink to="/" icon={<LayoutDashboard size={18} />} label={t('nav.dashboard')} />
                    <SidebarLink to="/reports" icon={<FileText size={18} />} label={t('nav.dailyReports')} />
                    <SidebarLink to="/reports/analytics" icon={<BarChart2 size={18} />} label={t('nav.analytics')} />
                    <SidebarLink to="/permits" icon={<ClipboardCheck size={18} />} label={t('nav.ptwPermits')} />
                    <SidebarLink to="/permits/analytics" icon={<Activity size={18} />} label={t('nav.ptwAnalytics')} />

                    {/* Admin-only links */}
                    {user?.role === 1 && (
                        <>
                            <div className="pt-4 pb-1 px-3">
                                <p className="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">{t('users.subtitle')}</p>
                            </div>
                            <SidebarLink to="/users" icon={<Users size={18} />} label={t('nav.userManagement')} />
                            <SidebarLink to="/settings" icon={<Settings size={18} />} label={t('nav.settings')} />
                        </>
                    )}
                </nav>

                {/* User Info + Logout */}
                <div className="p-4 border-t">
                    <div className="flex items-center gap-3 px-2 mb-3">
                        <div className="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs shrink-0">
                            {initials}
                        </div>
                        <div className="min-w-0">
                            <p className="text-sm font-semibold text-gray-800 truncate">{user?.username}</p>
                            <p className="text-[11px] text-gray-400">{roleName}</p>
                        </div>
                    </div>
                    <button
                        onClick={logout}
                        className="flex items-center gap-3 px-3 py-2 text-gray-500 hover:bg-red-50 hover:text-red-600 rounded-lg w-full transition-colors text-sm"
                    >
                        <LogOut size={16} />
                        <span>{t('nav.logout')}</span>
                    </button>
                </div>
            </aside>

            {/* Main Content */}
            <main className="flex-1 flex flex-col overflow-hidden">
                {/* Top Header */}
                <header className="h-14 bg-white border-b flex items-center justify-between px-8 shrink-0">
                    <h2 className="text-sm font-semibold text-gray-500">
                        {new Date().toLocaleDateString(i18n.language === 'en' ? 'en-US' : 'ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                    </h2>
                    <div className="flex items-center gap-3">
                        <button 
                            onClick={toggleLanguage}
                            className="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <Globe size={16} className="text-blue-600" />
                            <span>{i18n.language === 'en' ? 'العربية' : 'English'}</span>
                        </button>
                        <button className="p-2 text-gray-400 hover:text-blue-600 relative rounded-lg hover:bg-blue-50 transition-colors">
                            <Bell size={18} />
                            <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white" />
                        </button>
                    </div>
                </header>

                {/* Scrollable Area */}
                <div className="flex-1 overflow-y-auto p-8 bg-gray-50">
                    {children}
                </div>
            </main>
        </div>
    );
};

// Sidebar navigation link with active state
// Sidebar navigation link with active state
const SidebarLink = ({ to, icon, label }: { to: string; icon: React.ReactNode; label: string }) => (
    <NavLink
        to={to}
        end={to === '/'}
        className={({ isActive }) =>
            `flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all ${isActive
                ? 'bg-blue-50 text-blue-700 font-semibold'
                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
            }`
        }
    >
        {icon}
        <span>{label}</span>
    </NavLink>
);

export default MainLayout;
