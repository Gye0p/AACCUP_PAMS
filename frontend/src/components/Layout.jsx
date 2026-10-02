import { useState } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { ROLE_LABELS } from '../utils'

export default function Layout({ children }) {
  const { user, getRoles, logout, isAdmin } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [sidebarOpen, setSidebarOpen] = useState(false)

  const handleLogout = () => {
    logout()
    navigate('/login')
  }

  const primaryRole = getRoles()[0]
  const roleLabel = ROLE_LABELS[primaryRole] || 'User'

  const isActive = (path) => location.pathname === path

  return (
    <div className="flex min-h-screen bg-[#121212]">
      {/* Mobile overlay */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-20 bg-black/60 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* SIDEBAR */}
      <aside
        className={`
          fixed top-0 left-0 z-30 h-full w-60 flex flex-col
          bg-[#161616] border-r border-[#252525]
          transform transition-transform duration-200 ease-in-out
          lg:relative lg:translate-x-0 lg:z-auto
          ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
        `}
      >
        {/* Logo */}
        <div className="flex items-center gap-3 px-5 py-5 border-b border-[#252525]">
          <div className="flex items-center justify-center w-8 h-8 bg-[#76ff03] rounded">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M8 1L14 4.5V11.5L8 15L2 11.5V4.5L8 1Z" fill="#121212"/>
            </svg>
          </div>
          <div>
            <div className="text-[13px] font-semibold text-white leading-tight" style={{ fontFamily: 'Poppins, sans-serif' }}>
              AACCUP PAMS
            </div>
            <div className="text-[10px] text-[#6b6b6b] uppercase tracking-wider">
              Quality Assurance
            </div>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
          <NavItem
            to="/dashboard"
            label="Dashboard"
            active={isActive('/dashboard')}
            icon={
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
              </svg>
            }
          />
          {isAdmin() && (
            <NavItem
              to="/admin"
              label="Administration"
              active={isActive('/admin')}
              icon={
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              }
            />
          )}
        </nav>

        {/* User section */}
        <div className="px-3 py-3 border-t border-[#252525]">
          <div className="px-2 py-2.5 rounded-md">
            <div className="text-[12px] font-medium text-[#d0d0d0] truncate mb-0.5">{user?.email}</div>
            <div className="text-[10px] text-[#6b6b6b] uppercase tracking-wider mb-3">{roleLabel}</div>
            <button
              onClick={handleLogout}
              className="w-full text-left text-[12px] text-[#b0b0b0] hover:text-[#f5f5f5] transition-colors duration-150 flex items-center gap-2 group"
            >
              <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75" className="flex-shrink-0">
                <path strokeLinecap="round" strokeLinejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Sign Out
            </button>
          </div>
        </div>
      </aside>

      {/* MAIN */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Mobile top bar */}
        <header className="lg:hidden flex items-center justify-between px-4 py-3 border-b border-[#252525] bg-[#161616]">
          <button
            onClick={() => setSidebarOpen(true)}
            className="p-1 rounded text-[#b0b0b0] hover:text-white hover:bg-[#252525] transition-colors"
            aria-label="Open navigation"
          >
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
              <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <span className="text-[13px] font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
            AACCUP PAMS
          </span>
          <div className="w-7" />
        </header>

        <main className="flex-1 overflow-y-auto">
          <div className="max-w-5xl mx-auto px-6 py-8 lg:px-8">
            {children}
          </div>
        </main>
      </div>
    </div>
  )
}

function NavItem({ to, label, active, icon }) {
  return (
    <Link
      to={to}
      className={`
        flex items-center gap-2.5 px-2.5 py-2 rounded text-[13px] font-medium
        transition-colors duration-150
        ${active
          ? 'bg-[#76ff03]/10 text-[#76ff03]'
          : 'text-[#8a8a8a] hover:text-[#d0d0d0] hover:bg-[#1e1e1e]'
        }
      `}
    >
      <span className={`flex-shrink-0 ${active ? 'text-[#76ff03]' : ''}`}>{icon}</span>
      {label}
    </Link>
  )
}
