import { BrowserRouter as Router, Routes, Route, Outlet } from 'react-router-dom';
import MainLayout from './presentation/layouts/MainLayout';
import DashboardPage from './presentation/pages/DashboardPage';
import DailyReportPage from './presentation/pages/DailyReportPage';
import AnalyticsPage from './presentation/pages/AnalyticsPage';
import CreateReportPage from './presentation/pages/CreateReportPage';
import EditReportPage from './presentation/pages/EditReportPage';
import PTWPage from './presentation/pages/PTWPage';
import CreatePTWPage from './presentation/pages/CreatePTWPage';
import PtwAnalyticsPage from './presentation/pages/PtwAnalyticsPage';
import UserManagementPage from './presentation/pages/UserManagementPage';
import LoginPage from './presentation/pages/LoginPage';
import { ProtectedRoute } from './presentation/components/SecurityGuards';

function App() {
  return (
    <Router>
      <Routes>
        {/* Public Routes */}
        <Route path="/login" element={<LoginPage />} />

        {/* Protected Routes */}
        <Route element={<ProtectedRoute />}>
          <Route element={
            <MainLayout>
              <Outlet />
            </MainLayout>
          }>
            <Route path="/" element={<DashboardPage />} />
            <Route path="/reports" element={<DailyReportPage />} />
            <Route path="/reports/analytics" element={<AnalyticsPage />} />
            <Route path="/reports/new" element={<CreateReportPage />} />
            <Route path="/reports/edit/:id" element={<EditReportPage />} />
            <Route path="/permits" element={<PTWPage />} />
            <Route path="/permits/analytics" element={<PtwAnalyticsPage />} />
            <Route path="/permits/new" element={<CreatePTWPage />} />
            <Route path="/users" element={<UserManagementPage />} />
            <Route path="/settings" element={
              <div className="text-center py-20 text-gray-400">Settings — Coming Soon</div>
            } />
          </Route>
        </Route>
      </Routes>
    </Router>
  );
}

export default App;
