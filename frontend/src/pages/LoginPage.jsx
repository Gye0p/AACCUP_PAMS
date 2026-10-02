import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/useAuth'
import { login } from '../api'
import { jwtDecode } from 'jwt-decode'

export default function LoginPage() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const { loginSuccess } = useAuth()
  const navigate = useNavigate()

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      const res = await login(email, password)
      const token = res.data.token
      const decoded = jwtDecode(token)
      loginSuccess(token, { email: decoded.username, roles: decoded.roles })
      navigate('/dashboard')
    } catch (err) {
      setError(err.response?.data?.message || 'Invalid credentials. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#121212] flex">
      {/* Left panel — branding */}
      <div className="hidden lg:flex flex-col justify-between w-80 bg-[#161616] border-r border-[#252525] px-8 py-10 flex-shrink-0">
        <div>
          <div className="flex items-center gap-3 mb-10">
            <div className="flex items-center justify-center w-9 h-9 bg-[#76ff03] rounded">
              <svg width="18" height="18" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 1L14 4.5V11.5L8 15L2 11.5V4.5L8 1Z" fill="#121212"/>
              </svg>
            </div>
            <div>
              <div className="text-[14px] font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
                AACCUP PAMS
              </div>
              <div className="text-[10px] text-[#6b6b6b] uppercase tracking-wider">
                Monitoring System
              </div>
            </div>
          </div>

          <div className="space-y-6">
            <div>
              <h2 className="text-[13px] font-medium text-[#76ff03] uppercase tracking-wider mb-2" style={{ fontFamily: 'Poppins, sans-serif' }}>
                System
              </h2>
              <p className="text-[13px] text-[#8a8a8a] leading-relaxed">
                Program Accreditation Monitoring System for tracking compliance activities across AACCUP accreditation cycles.
              </p>
            </div>
            <div>
              <h2 className="text-[13px] font-medium text-[#76ff03] uppercase tracking-wider mb-2" style={{ fontFamily: 'Poppins, sans-serif' }}>
                Institution
              </h2>
              <p className="text-[13px] text-[#8a8a8a] leading-relaxed">
                Negros Oriental State University<br/>
                Quality Assurance &amp; Management Center
              </p>
            </div>
          </div>
        </div>

        <p className="text-[11px] text-[#3a3a3a]">
          NORSU QUAMC &copy; {new Date().getFullYear()}
        </p>
      </div>

      {/* Right panel — form */}
      <div className="flex-1 flex items-center justify-center px-6 py-12">
        <div className="w-full max-w-sm">
          {/* Mobile logo */}
          <div className="flex items-center gap-3 mb-8 lg:hidden">
            <div className="flex items-center justify-center w-8 h-8 bg-[#76ff03] rounded">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 1L14 4.5V11.5L8 15L2 11.5V4.5L8 1Z" fill="#121212"/>
              </svg>
            </div>
            <span className="text-[14px] font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
              AACCUP PAMS
            </span>
          </div>

          <div className="mb-8">
            <h1 className="text-2xl font-semibold text-white mb-1" style={{ fontFamily: 'Poppins, sans-serif' }}>
              Sign in
            </h1>
            <p className="text-[13px] text-[#8a8a8a]">
              Use your institutional credentials to access the system.
            </p>
          </div>

          {error && (
            <div className="flex items-start gap-2.5 bg-[#1e1212] border border-[#4a1a1a] rounded px-4 py-3 mb-6">
              <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#f87171" strokeWidth="2" className="flex-shrink-0 mt-0.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span className="text-[13px] text-[#f87171]">{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
                Email address
              </label>
              <input
                type="email"
                value={email}
                onChange={e => setEmail(e.target.value)}
                required
                autoFocus
                className="
                  w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded
                  px-3 py-2.5 text-[13px] text-white placeholder-[#4a4a4a]
                  focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/30
                  transition-colors duration-150
                "
                placeholder="you@norsu.edu.ph"
              />
            </div>

            <div>
              <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
                Password
              </label>
              <input
                type="password"
                value={password}
                onChange={e => setPassword(e.target.value)}
                required
                className="
                  w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded
                  px-3 py-2.5 text-[13px] text-white placeholder-[#4a4a4a]
                  focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/30
                  transition-colors duration-150
                "
                placeholder="••••••••"
              />
            </div>

            <button
              type="submit"
              disabled={loading}
              className="
                w-full bg-[#76ff03] hover:bg-[#65e000] active:bg-[#56cc00]
                text-[#121212] font-semibold text-[13px]
                px-4 py-2.5 rounded
                transition-colors duration-150
                disabled:opacity-50 disabled:cursor-not-allowed
                mt-2
              "
            >
              {loading ? (
                <span className="flex items-center justify-center gap-2">
                  <svg className="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" strokeDasharray="32" strokeDashoffset="8"/>
                  </svg>
                  Signing in…
                </span>
              ) : 'Sign In'}
            </button>
          </form>

          <p className="mt-6 text-[11px] text-[#3a3a3a] text-center">
            NORSU QUAMC &copy; {new Date().getFullYear()}
          </p>
        </div>
      </div>
    </div>
  )
}
