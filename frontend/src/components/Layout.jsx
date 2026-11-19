import { useState } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useAuth } from '../context/useAuth'
import { ROLE_LABELS } from '../utils'
import NotificationBell from './NotificationBell'

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
  const pageTitle = isActive('/admin')
    ? 'Administration'
    : isActive('/dashboard')
      ? 'Dashboard'
      : location.pathname.startsWith('/cycles/')
        ? 'Cycle Details'
        : location.pathname.startsWith('/ia-review/')
          ? 'IA Review'
          : location.pathname.startsWith('/activities/')
            ? 'Compliance Activity'
            : location.pathname.startsWith('/evidence/')
              ? 'Evidence'
              : 'AACCUP PAMS'

  return (
    <div className="flex min-h-screen bg-[#f7f9ff]">
      {/* Mobile overlay */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-20 bg-[#0a2b5e]/40 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* SIDEBAR */}
      <aside
        className={`
          fixed top-0 left-0 z-30 h-screen w-60 flex flex-col
          bg-white border-r border-[#dbe4f0] shadow-[8px_0_30px_rgba(20,61,118,.06)]
          transform transition-transform duration-200 ease-in-out
          lg:sticky lg:top-0 lg:translate-x-0 lg:z-auto
          ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
        `}
      >
        {/* Logo */}
        <div className="flex items-center gap-3 px-5 py-4 border-b border-[#dbe4f0]">
          <img src="/quamc-logo.png" alt="NORSU QUAMC" className="h-9 w-9 object-contain" />
          <div>
            <div className="text-[13px] font-semibold text-[#18243a] leading-tight" style={{ fontFamily: 'Poppins, sans-serif' }}>
              AACCUP PAMS
            </div>
            <div className="text-[10px] text-[#718096] uppercase tracking-wider">
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

        {/* Account section */}
        <div className="mt-auto border-t border-[#dbe4f0] p-3">
          <div className="rounded-xl border border-[#dbe4f0] bg-[#fbfcff] p-3">
            <div className="flex items-center gap-2.5">
              <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-[#eaf2fc] text-[11px] font-semibold text-[#1768c7]">
                {(user?.email || 'U').slice(0, 1).toUpperCase()}
              </div>
              <div className="min-w-0">
                <div className="text-[12px] font-medium text-[#263650] truncate">{user?.email}</div>
                <div className="text-[10px] text-[#718096] uppercase tracking-wider">{roleLabel}</div>
              </div>
            </div>
            <button
              onClick={handleLogout}
              className="mt-3 w-full border-t border-[#dbe4f0] px-2 pt-3 text-left text-[12px] text-[#718096] hover:text-[#1768c7] transition-colors duration-150 flex items-center gap-2 group"
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
        {/* Compact header */}
        <header className="sticky top-0 z-10 h-[72.89px] min-h-[72.89px] w-full flex flex-shrink-0 items-center justify-between px-4 lg:px-8 border-b border-[#dbe4f0] bg-white/95 backdrop-blur">
          <div className="flex items-center gap-3 min-w-0">
            <button
              onClick={() => setSidebarOpen(true)}
              className="lg:hidden p-1.5 rounded text-[#718096] hover:text-[#1768c7] hover:bg-[#edf3fb] transition-colors"
              aria-label="Open navigation"
            >
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>
            <h1 className="truncate text-[13px] font-semibold text-[#18243a]" style={{ fontFamily: 'Poppins, sans-serif' }}>
              {pageTitle}
            </h1>
          </div>

          <div className="flex items-center gap-2">
            <NotificationBell />
            <div className="hidden sm:flex h-7 items-center border-l border-[#dbe4f0] pl-3">
              <div className="max-w-44 truncate text-[11px] text-[#718096]">{user?.email}</div>
            </div>
          </div>
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
          : 'text-[#718096] hover:text-[#1768c7] hover:bg-[#edf3fb]'
        }
      `}
    >
      <span className={`flex-shrink-0 ${active ? 'text-[#76ff03]' : ''}`}>{icon}</span>
      {label}
    </Link>
  )
}
