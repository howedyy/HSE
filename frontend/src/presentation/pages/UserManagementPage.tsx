import React from 'react';
import { useUsers } from '../../application/hooks/useUsers';
import { Users, Shield, Search } from 'lucide-react';

const roleBadge = (type: number) => {
    const map: Record<number, { label: string; style: string }> = {
        1: { label: 'Admin', style: 'bg-purple-50 text-purple-700 border-purple-200' },
        2: { label: 'HSE', style: 'bg-blue-50 text-blue-700 border-blue-200' },
        3: { label: 'Operation', style: 'bg-teal-50 text-teal-700 border-teal-200' },
    };
    const s = map[type] ?? { label: 'Unknown', style: 'bg-gray-100 text-gray-600 border-gray-200' };
    return <span className={`text-xs px-2.5 py-1 rounded-full border font-medium ${s.style}`}>{s.label}</span>;
};

const UserManagementPage: React.FC = () => {
    const { users, isLoading } = useUsers();

    return (
        <div className="max-w-full mx-auto space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <Shield size={24} className="text-purple-600" /> User Management
                    </h1>
                    <p className="text-sm text-gray-500 mt-1">{users.length} registered users</p>
                </div>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                {isLoading ? (
                    <div className="p-10 space-y-3">
                        {[...Array(6)].map((_, i) => <div key={i} className="h-12 bg-gray-100 rounded-lg animate-pulse" />)}
                    </div>
                ) : users.length === 0 ? (
                    <div className="text-center py-20">
                        <Search size={40} className="mx-auto text-gray-300 mb-3" />
                        <p className="text-gray-500">No users found</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 border-b border-gray-100">
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">#</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Username</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Role</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Department</th>
                                    <th className="text-left px-5 py-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {users.map((u, i) => (
                                    <tr key={u.id} className="hover:bg-purple-50/30 transition-colors">
                                        <td className="px-5 py-3.5 text-gray-400 font-mono text-xs">{i + 1}</td>
                                        <td className="px-5 py-3.5">
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                                    {u.username.substring(0, 2).toUpperCase()}
                                                </div>
                                                <span className="text-gray-800 font-medium">{u.username}</span>
                                            </div>
                                        </td>
                                        <td className="px-5 py-3.5">{roleBadge(u.userType)}</td>
                                        <td className="px-5 py-3.5 text-gray-600">{u.department}</td>
                                        <td className="px-5 py-3.5">
                                            <span className={`inline-flex items-center gap-1.5 text-xs font-medium ${u.status === 'Active' ? 'text-green-600' : 'text-gray-400'}`}>
                                                <span className={`w-2 h-2 rounded-full ${u.status === 'Active' ? 'bg-green-500' : 'bg-gray-300'}`} />
                                                {u.status}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
};

export default UserManagementPage;
