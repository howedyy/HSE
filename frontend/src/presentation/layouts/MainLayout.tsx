import React, { useState, type ReactNode } from 'react';
import { useAuth } from '../context/AuthContext';
import { NavLink } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import {
    LayoutDashboard, FileText, ClipboardCheck,
    Settings, LogOut, Bell, ShieldCheck, Users, BarChart2, Activity, Globe, Menu, X
} from 'lucide-react';
import { ThemeToggle } from '../components/ThemeToggle';

interface Props {
    children: ReactNode;
}

const MainLayout: React.FC<Props> = ({ children }) => {
    const { user, logout } = useAuth();
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
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
        <div className="flex h-screen bg-gray-50 dark:bg-slate-950 font-sans text-gray-900 dark:text-gray-100 transition-colors">
            {/* Mobile Sidebar Overlay */}
            {isMobileMenuOpen && (
                <div 
                    className="fixed inset-0 bg-gray-900/50 dark:bg-black/60 z-40 md:hidden backdrop-blur-sm"
                    onClick={() => setIsMobileMenuOpen(false)}
                />
            )}

            {/* Sidebar */}
            <aside className={`fixed inset-y-0 left-0 rtl:left-auto rtl:right-0 z-50 w-64 bg-white dark:bg-slate-900 border-r rtl:border-r-0 rtl:border-l border-gray-200 dark:border-slate-800 flex flex-col shrink-0 transition-transform duration-300 transform ${isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full'} md:relative md:translate-x-0 rtl:md:translate-x-0`}>
                <div className="p-6 border-b border-gray-200 dark:border-slate-800 flex justify-between items-center">
                    <div className="flex items-center gap-2">
                        <div className="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                            <ShieldCheck size={18} className="text-white" />
                        </div>
                        <div>
                            <h1 className="text-lg font-bold text-gray-900 dark:text-white leading-none">{t('app.title')}</h1>
                            <p className="text-[10px] text-gray-400 dark:text-slate-400 uppercase tracking-wider">{t('app.subtitle')}</p>
                        </div>
                    </div>
                    <button 
                        className="md:hidden p-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg"
                        onClick={() => setIsMobileMenuOpen(false)}
                    >
                        <X size={20} />
                    </button>
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
                <div className="p-4 border-t border-gray-200 dark:border-slate-800">
                    <div className="flex items-center gap-3 px-2 mb-3">
                        <div className="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs shrink-0">
                            {initials}
                        </div>
                        <div className="min-w-0">
                            <p className="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">{user?.username}</p>
                            <p className="text-[11px] text-gray-400 dark:text-slate-400">{roleName}</p>
                        </div>
                    </div>
                    <button
                        onClick={logout}
                        className="flex items-center gap-3 px-3 py-2 text-gray-500 dark:text-slate-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400 rounded-lg w-full transition-colors text-sm"
                    >
                        <LogOut size={16} />
                        <span>{t('nav.logout')}</span>
                    </button>
                </div>
            </aside>

            {/* Main Content */}
            <main className="flex-1 flex flex-col overflow-hidden">
                {/* Top Header */}
                <header className="h-14 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 flex items-center justify-between px-4 md:px-8 shrink-0 transition-colors">
                    <div className="flex items-center gap-3">
                        <button 
                            className="md:hidden p-2 -ml-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg"
                            onClick={() => setIsMobileMenuOpen(true)}
                        >
                            <Menu size={20} />
                        </button>
                        <h2 className="text-xs md:text-sm font-semibold text-gray-500 dark:text-slate-400 hidden sm:block">
                            {new Date().toLocaleDateString(i18n.language === 'en' ? 'en-US' : 'ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                        </h2>
                    </div>
                    <div className="flex items-center gap-3">
                        <button 
                            onClick={toggleLanguage}
                            className="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-gray-600 dark:text-gray-400 dark:text-slate-300 hover:bg-gray-100 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-lg transition-colors"
                        >
                            <Globe size={16} className="text-blue-600" />
                            <span>{i18n.language === 'en' ? 'العربية' : 'English'}</span>
                        </button>
                        <button className="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 relative rounded-lg hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors">
                            <Bell size={18} />
                            <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-900" />
                        </button>
                        <ThemeToggle />
                    </div>
                </header>

                {/* Scrollable Area */}
                <div className="flex-1 overflow-y-auto p-4 md:p-8 bg-gray-50 dark:bg-slate-950 transition-colors w-full relative">
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
                ? 'bg-blue-50 text-blue-700 font-semibold dark:bg-blue-900/30 dark:text-blue-400'
                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:bg-slate-800 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-slate-800 dark:hover:text-gray-100'
            }`
        }
    >
        {icon}
        <span>{label}</span>
    </NavLink>
);

export default MainLayout;
