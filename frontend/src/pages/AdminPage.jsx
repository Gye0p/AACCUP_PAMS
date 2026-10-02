import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { fetchCycles, createCycle, fetchPrograms } from '../api'
import Layout from '../components/Layout'

export default function AdminPage() {
  const qc = useQueryClient()

  const { data: cycles, isLoading: loadingCycles } = useQuery({
    queryKey: ['admin-cycles'],
    queryFn: fetchCycles
  })

  const { data: programs } = useQuery({
    queryKey: ['programs'],
    queryFn: fetchPrograms
  })

  const [form, setForm] = useState({ program: '', academicYear: '', sarReceivedDate: '' })
  const [formError, setFormError] = useState('')
  const [successMsg, setSuccessMsg] = useState('')

  const createMutation = useMutation({
    mutationFn: createCycle,
    onSuccess: () => {
      qc.invalidateQueries(['admin-cycles'])
      setForm({ program: '', academicYear: '', sarReceivedDate: '' })
      setFormError('')
      setSuccessMsg('Cycle launched successfully. 10 Area Assignments were auto-generated.')
      setTimeout(() => setSuccessMsg(''), 5000)
    },
    onError: (err) => {
      setFormError(err.response?.data?.detail || 'Failed to create cycle.')
    }
  })

  const handleSubmit = (e) => {
    e.preventDefault()
    setFormError('')
    createMutation.mutate({
      program: form.program,
      academicYear: form.academicYear,
      sarReceivedDate: new Date(form.sarReceivedDate).toISOString()
    })
  }

  return (
    <Layout>
      <div className="mb-6">
        <h1 className="text-xl font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
          Administration
        </h1>
        <p className="text-[13px] text-[#8a8a8a] mt-1">
          Manage accreditation cycles, programs, and system configuration.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Launch cycle form */}
        <div className="lg:col-span-1">
          <div className="bg-[#1a1a1a] border border-[#252525] rounded-md p-5">
            <h2 className="text-[14px] font-semibold text-white mb-4" style={{ fontFamily: 'Poppins, sans-serif' }}>
              Launch New Cycle
            </h2>

            {successMsg && (
              <div className="flex items-start gap-2.5 bg-[#0f1e10] border border-[#1a4a1a] rounded px-3 py-2.5 mb-4">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#4ade80" strokeWidth="2" className="flex-shrink-0 mt-0.5">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span className="text-[12px] text-[#4ade80]">{successMsg}</span>
              </div>
            )}

            {formError && (
              <div className="flex items-start gap-2.5 bg-[#1e1212] border border-[#4a1a1a] rounded px-3 py-2.5 mb-4">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#f87171" strokeWidth="2" className="flex-shrink-0 mt-0.5">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span className="text-[12px] text-[#f87171]">{formError}</span>
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <FormField label="Program" required>
                <select
                  value={form.program}
                  onChange={e => setForm(f => ({ ...f, program: e.target.value }))}
                  required
                  className="form-select"
                >
                  <option value="">Select a program</option>
                  {programs?.map(p => (
                    <option key={p.id} value={p['@id']}>{p.name} ({p.code})</option>
                  ))}
                </select>
              </FormField>

              <FormField label="Academic Year" required>
                <input
                  value={form.academicYear}
                  onChange={e => setForm(f => ({ ...f, academicYear: e.target.value }))}
                  placeholder="e.g. 2025–2026"
                  required
                  className="form-input"
                />
              </FormField>

              <FormField label="SAR Received Date" required>
                <input
                  type="date"
                  value={form.sarReceivedDate}
                  onChange={e => setForm(f => ({ ...f, sarReceivedDate: e.target.value }))}
                  required
                  className="form-input"
                />
              </FormField>

              <button
                type="submit"
                disabled={createMutation.isPending}
                className="
                  w-full bg-[#76ff03] hover:bg-[#65e000] active:bg-[#56cc00]
                  text-[#121212] font-semibold text-[13px]
                  px-4 py-2.5 rounded
                  transition-colors duration-150
                  disabled:opacity-50 disabled:cursor-not-allowed
                "
              >
                {createMutation.isPending ? (
                  <span className="flex items-center justify-center gap-2">
                    <svg className="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" strokeDasharray="32" strokeDashoffset="8"/>
                    </svg>
                    Launching…
                  </span>
                ) : 'Launch Cycle'}
              </button>
            </form>
          </div>
        </div>

        {/* Cycles table */}
        <div className="lg:col-span-2">
          <div className="bg-[#1a1a1a] border border-[#252525] rounded-md overflow-hidden">
            <div className="px-5 py-4 border-b border-[#252525]">
              <h2 className="text-[14px] font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
                All Cycles
              </h2>
            </div>

            {loadingCycles ? (
              <div className="p-5 space-y-3">
                {[1,2,3].map(i => (
                  <div key={i} className="h-10 bg-[#222] rounded animate-pulse" />
                ))}
              </div>
            ) : cycles?.length === 0 ? (
              <div className="px-5 py-12 text-center">
                <p className="text-[13px] text-[#6b6b6b]">No cycles created yet.</p>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-[13px]">
                  <thead>
                    <tr className="border-b border-[#252525]">
                      <Th>ID</Th>
                      <Th>Program</Th>
                      <Th>Academic Year</Th>
                      <Th>Compliance Deadline</Th>
                      <Th>Status</Th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-[#1e1e1e]">
                    {cycles?.map(c => (
                      <tr key={c.id} className="hover:bg-[#1e1e1e] transition-colors duration-100">
                        <Td className="text-[#6b6b6b] font-mono text-[11px]">{c.id}</Td>
                        <Td className="font-medium text-[#d0d0d0]">{c.program?.code}</Td>
                        <Td className="text-[#b0b0b0]">{c.academicYear}</Td>
                        <Td className="text-[#b0b0b0]">{new Date(c.complianceDeadline).toLocaleDateString('en-PH')}</Td>
                        <Td>
                          <CycleStatusPill status={c.status} />
                        </Td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      </div>
    </Layout>
  )
}

function FormField({ label, required, children }) {
  return (
    <div>
      <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
        {label}{required && <span className="text-[#ef4444] ml-0.5">*</span>}
      </label>
      {children}
    </div>
  )
}

function Th({ children }) {
  return (
    <th className="px-4 py-3 text-left text-[11px] font-medium text-[#6b6b6b] uppercase tracking-wider whitespace-nowrap">
      {children}
    </th>
  )
}

function Td({ children, className = '' }) {
  return (
    <td className={`px-4 py-3 ${className}`}>{children}</td>
  )
}

function CycleStatusPill({ status }) {
  const map = {
    active:    'bg-[#76ff03]/10 text-[#76ff03]',
    completed: 'bg-[#3b82f6]/10 text-[#60a5fa]',
  }
  const cls = map[status] || 'bg-[#252525] text-[#8a8a8a]'
  return (
    <span className={`inline-block text-[10px] font-medium px-1.5 py-0.5 rounded uppercase tracking-wide ${cls}`}>
      {status}
    </span>
  )
}
