import { useState, useCallback } from 'react'
import { jwtDecode } from 'jwt-decode'
import { AuthContext } from './useAuth'

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem('jwt_token'))
  const [user, setUser] = useState(() => {
    const stored = localStorage.getItem('jwt_user')
    return stored ? JSON.parse(stored) : null
  })

  const loginSuccess = useCallback((jwtToken, userData) => {
    localStorage.setItem('jwt_token', jwtToken)
    localStorage.setItem('jwt_user', JSON.stringify(userData))
    setToken(jwtToken)
    setUser(userData)
  }, [])

  const logout = useCallback(() => {
    localStorage.removeItem('jwt_token')
    localStorage.removeItem('jwt_user')
    setToken(null)
    setUser(null)
  }, [])

  const getRoles = useCallback(() => {
    if (!token) return []
    try {
      return jwtDecode(token).roles || []
    } catch {
      return []
    }
  }, [token])

  const hasRole = useCallback((role) => getRoles().includes(role), [getRoles])

  const isAdmin = () => hasRole('ROLE_QUAMC_ADMIN')
  const isProgramHead = () => hasRole('ROLE_PROGRAM_HEAD')
  const isIA = () => hasRole('ROLE_INTERNAL_ACCREDITOR')
  const isDean = () => hasRole('ROLE_DEAN')
  const isReadOnly = () =>
    hasRole('ROLE_PRESIDENT') || hasRole('ROLE_CAMPUS_DIRECTOR') || hasRole('ROLE_VPAA')

  return (
    <AuthContext.Provider value={{ token, user, loginSuccess, logout, getRoles, hasRole, isAdmin, isProgramHead, isIA, isDean, isReadOnly }}>
      {children}
    </AuthContext.Provider>
  )
}
