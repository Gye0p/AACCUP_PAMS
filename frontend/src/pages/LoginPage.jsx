import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/useAuth'
import { login } from '../api'
import { jwtDecode } from 'jwt-decode'

export default function LoginPage() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
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
    <main className="relative min-h-screen overflow-hidden bg-[#f7f9ff] text-[#18243a]">
      <div className="absolute inset-y-0 left-0 hidden w-[58%] bg-[#0a3d91] lg:block" />
      <div className="absolute inset-y-0 left-0 hidden w-[58%] opacity-20 lg:block">
        <div className="absolute -left-24 -top-28 h-80 w-80 rounded-full border-[64px] border-[#78b7ff]" />
        <div className="absolute right-[-120px] bottom-[-170px] h-[430px] w-[430px] rounded-full border-[78px] border-[#ffd84d]" />
      </div>

      <div className="relative mx-auto min-h-screen max-w-[1440px] px-5 py-6 sm:px-8 lg:px-12">
        <div className="grid min-h-[calc(100vh-48px)] items-stretch lg:grid-cols-[1.18fr_.82fr]">
          <section className="hidden flex-col justify-between py-8 pr-14 text-white lg:flex">
            <div className="flex items-center gap-4">
              <img src="/quamc-logo.png" alt="NORSU QUAMC" className="h-16 w-16 object-contain" />
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#ffd84d]">
                  Quality Assurance
                </p>
                <p className="mt-1 text-lg font-semibold" style={{ fontFamily: 'Poppins, sans-serif' }}>
                  AACCUP PAMS
                </p>
              </div>
            </div>

            <div className="max-w-xl pb-8">
              <div className="mb-7 h-1 w-16 rounded-full bg-[#ffd84d]" />
              <h1 className="text-5xl leading-[1.08] font-bold tracking-tight" style={{ fontFamily: 'Poppins, sans-serif' }}>
                Accreditation,
                <br />
                clearly monitored.
              </h1>
              <p className="mt-6 max-w-md text-base leading-7 text-[#dce8f8]">
                A single workspace for managing accreditation cycles, activities,
                evidence, and internal quality reviews.
              </p>
            </div>

            <p className="text-xs text-[#a9c5e7]">
              Negros Oriental State University &middot; QUAMC
            </p>
          </section>

          <section className="flex items-center justify-center">
            <div className="w-full max-w-[440px]">
              <div className="mb-8 flex items-center justify-center lg:hidden">
                <img src="/quamc-logo.png" alt="NORSU QUAMC" className="h-20 w-20 object-contain" />
              </div>

              <div className="rounded-[28px] border border-white/80 bg-white px-6 py-8 shadow-[0_24px_70px_rgba(20,61,118,0.16)] sm:px-10 sm:py-10">
                <div className="mb-8">
                  <p className="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#1768c7]">
                    Welcome back
                  </p>
                  <h2 className="text-[30px] leading-tight font-bold text-[#15233a]" style={{ fontFamily: 'Poppins, sans-serif' }}>
                    Sign in to PAMS
                  </h2>
                  <p className="mt-2 text-sm text-[#718096]">
                    Enter your institutional account details.
                  </p>
                </div>

                {error && (
                  <div role="alert" className="mb-5 flex items-start gap-3 rounded-xl border border-[#ffcfd4] bg-[#fff3f4] px-4 py-3 text-sm text-[#a52a38]">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="mt-0.5 shrink-0">
                      <circle cx="12" cy="12" r="9" />
                      <path d="M12 7v6M12 17h.01" />
                    </svg>
                    <span>{error}</span>
                  </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-5">
                  <div>
                    <label htmlFor="email" className="mb-2 block text-sm font-semibold text-[#263650]">
                      Email address
                    </label>
                    <input
                      id="email"
                      type="email"
                      value={email}
                      onChange={e => setEmail(e.target.value)}
                      required
                      autoComplete="email"
                      autoFocus
                      className="h-12 w-full rounded-xl border border-[#dbe4f0] bg-[#fbfcff] px-4 text-[15px] text-[#17243a] outline-none transition placeholder:text-[#a7b1bf] focus:border-[#1768c7] focus:ring-4 focus:ring-[#1768c7]/10"
                      placeholder="you@norsu.edu.ph"
                    />
                  </div>

                  <div>
                    <label htmlFor="password" className="mb-2 block text-sm font-semibold text-[#263650]">
                      Password
                    </label>
                    <div className="relative">
                      <input
                        id="password"
                        type={showPassword ? 'text' : 'password'}
                        value={password}
                        onChange={e => setPassword(e.target.value)}
                        required
                        autoComplete="current-password"
                        className="h-12 w-full rounded-xl border border-[#dbe4f0] bg-[#fbfcff] px-4 pr-12 text-[15px] text-[#17243a] outline-none transition placeholder:text-[#a7b1bf] focus:border-[#1768c7] focus:ring-4 focus:ring-[#1768c7]/10"
                        placeholder="Enter your password"
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(value => !value)}
                        className="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-lg text-[#718096] transition hover:bg-[#edf3fb] hover:text-[#1768c7]"
                        aria-label={showPassword ? 'Hide password' : 'Show password'}
                      >
                        {showPassword ? (
                          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                            <path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 4.2A10.7 10.7 0 0112 4c5.5 0 9 4.9 9 8a9.2 9.2 0 01-2.1 3.9M6.6 6.6A10.3 10.3 0 003 12c0 3.1 3.5 8 9 8a9.7 9.7 0 004.1-.9" />
                          </svg>
                        ) : (
                          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                            <path d="M3 12c0-3.1 3.5-8 9-8s9 4.9 9 8-3.5 8-9 8-9-4.9-9-8z" />
                            <circle cx="12" cy="12" r="3" />
                          </svg>
                        )}
                      </button>
                    </div>
                  </div>

                  <button
                    type="submit"
                    disabled={loading}
                    className="flex h-12 w-full items-center justify-center rounded-xl bg-[#1768c7] px-4 text-[15px] font-bold text-white shadow-[0_10px_24px_rgba(23,104,199,.24)] transition hover:bg-[#0e56ad] active:translate-y-px disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    {loading ? (
                      <>
                        <svg className="mr-2 animate-spin" width="17" height="17" viewBox="0 0 24 24" fill="none">
                          <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="3" opacity=".25" />
                          <path d="M21 12a9 9 0 00-9-9" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
                        </svg>
                        Signing in...
                      </>
                    ) : 'Sign in'}
                  </button>
                </form>

                <div className="mt-8 border-t border-[#edf1f6] pt-5 text-center">
                  <p className="text-xs text-[#8a96a8]">
                    Authorized users only &middot; {new Date().getFullYear()}
                  </p>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </main>
  )
}
