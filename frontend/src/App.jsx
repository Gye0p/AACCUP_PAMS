import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from './context/AuthContext'
import { ProtectedRoute, RoleRoute } from './routes/ProtectedRoute'

import LoginPage from './pages/LoginPage'
import DashboardPage from './pages/DashboardPage'
import AdminPage from './pages/AdminPage'
import CycleDetailPage from './pages/CycleDetailPage'
import ActivityFormPage from './pages/ActivityFormPage'
import IAReviewPage from './pages/IAReviewPage'
import EvidenceUploadPage from './pages/EvidenceUploadPage'
import GanttPage from './pages/GanttPage'

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          {/* Public Routes */}
          <Route path="/login" element={<LoginPage />} />

          {/* Protected Routes */}
          <Route element={<ProtectedRoute />}>
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            <Route path="/dashboard" element={<DashboardPage />} />
            <Route path="/cycles/:id" element={<CycleDetailPage />} />
            
            {/* IA Review */}
            <Route path="/ia-review/:id" element={<IAReviewPage />} />
            
            {/* Activities (Program Head, Admin, IA) */}
            <Route path="/activities/new" element={<ActivityFormPage />} />
            <Route path="/activities/:id/edit" element={<ActivityFormPage />} />
            
            {/* Evidence Upload */}
            <Route path="/evidence/:activityId" element={<EvidenceUploadPage />} />

            {/* Gantt Chart */}
            <Route path="/cycles/:id/gantt" element={<GanttPage />} />

            {/* Admin Only */}
            <Route element={<RoleRoute allowedRoles={['ROLE_QUAMC_ADMIN']} />}>
              <Route path="/admin" element={<AdminPage />} />
            </Route>
          </Route>

          {/* Fallback */}
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  )
}

export default App
