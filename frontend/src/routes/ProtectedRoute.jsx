import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '../context/useAuth'

export function ProtectedRoute() {
  const { token } = useAuth()
  return token ? <Outlet /> : <Navigate to="/login" replace />
}

export function RoleRoute({ allowedRoles }) {
  const { getRoles } = useAuth()
  const userRoles = getRoles()
  const allowed = allowedRoles.some(role => userRoles.includes(role))
  return allowed ? <Outlet /> : <Navigate to="/dashboard" replace />
}
