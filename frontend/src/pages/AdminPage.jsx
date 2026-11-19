import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  createInternalAccreditorAssignment,
  createCycle,
  deleteInternalAccreditorAssignment,
  fetchCycles,
  fetchInternalAccreditorAssignments,
  fetchPrograms,
  fetchUsers,
  fetchAreas,
} from '../api'
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
  const { data: areas = [] } = useQuery({
    queryKey: ['aaccup-areas'],
    queryFn: fetchAreas
  })

  const [form, setForm] = useState({
    program: '',
    academicYear: '',
    sarReceivedDate: '',
    accreditationLevel: '',
    programType: '',
    optionalAreaIds: [],
  })
  const [formError, setFormError] = useState('')
  const [successMsg, setSuccessMsg] = useState('')

  const createMutation = useMutation({
    mutationFn: createCycle,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['admin-cycles'] })
      setForm({ program: '', academicYear: '', sarReceivedDate: '', accreditationLevel: '', programType: '', optionalAreaIds: [] })
      setFormError('')
      setSuccessMsg('Cycle launched successfully. Its accreditation areas were generated.')
      setTimeout(() => setSuccessMsg(''), 5000)
    },
    onError: (err) => {
      setFormError(err.response?.data?.detail || 'Failed to create cycle.')
    }
  })

  const handleSubmit = (e) => {
    e.preventDefault()
    setFormError('')
    if (form.accreditationLevel === 'Level III') {
      if (!form.programType) {
        setFormError('Select whether this is an undergraduate or graduate program.')
        return
      }
      if (form.optionalAreaIds.length !== 2) {
        setFormError('Select exactly two optional areas for Level III.')
        return
      }
    }
    createMutation.mutate({
      program: form.program,
      academicYear: form.academicYear,
      sarReceivedDate: new Date(form.sarReceivedDate).toISOString(),
      accreditationLevel: form.accreditationLevel,
      programType: form.accreditationLevel === 'Level III' ? form.programType : null,
      optionalAreaIds: form.accreditationLevel === 'Level III' ? form.optionalAreaIds : null,
    })
  }

  const standardAreas = areas.filter(area =>
    ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'].includes(area.areaNumber)
  )
  const level3MandatoryNumbers = form.programType === 'graduate' ? ['III', 'VI'] : ['III', 'V']
  const optionalAreas = standardAreas.filter(area =>
    !level3MandatoryNumbers.includes(area.areaNumber)
  )
  const toggleOptionalArea = (areaId) => {
    setForm(previous => {
      const selected = previous.optionalAreaIds.includes(areaId)
        ? previous.optionalAreaIds.filter(id => id !== areaId)
        : previous.optionalAreaIds.length < 2
          ? [...previous.optionalAreaIds, areaId]
          : previous.optionalAreaIds
      return { ...previous, optionalAreaIds: selected }
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

              <FormField label="Accreditation Level" required>
                <select
                  value={form.accreditationLevel}
                  onChange={e => setForm(f => ({
                    ...f,
                    accreditationLevel: e.target.value,
                    programType: '',
                    optionalAreaIds: [],
                  }))}
                  required
                  className="form-select"
                >
                  <option value="">Select accreditation level</option>
                  <option value="Level I">Level I — 10 areas</option>
                  <option value="Level II">Level II — 10 areas</option>
                  <option value="Level III">Level III — mandatory and 2 optional areas</option>
                  <option value="Level IV">Level IV — 5 mandatory areas</option>
                </select>
              </FormField>

              {form.accreditationLevel === 'Level III' && (
                <>
                  <FormField label="Program Type" required>
                    <select
                      value={form.programType}
                      onChange={e => setForm(f => ({
                        ...f,
                        programType: e.target.value,
                        optionalAreaIds: [],
                      }))}
                      required
                      className="form-select"
                    >
                      <option value="">Select program type</option>
                      <option value="undergraduate">Undergraduate — mandatory: III Instruction, V Research</option>
                      <option value="graduate">Graduate — mandatory: III Instruction, VI Extension</option>
                    </select>
                  </FormField>

                  {form.programType && (
                    <div>
                      <label className="block text-[12px] font-medium text-[#b0b0b0] mb-2">
                        Select 2 Optional Areas
                        <span className="text-[#6b6b6b] ml-2">
                          {form.optionalAreaIds.length}/2 selected
                        </span>
                      </label>
                      <div className="space-y-1.5 max-h-56 overflow-y-auto">
                        {optionalAreas.map(area => (
                          <label
                            key={area.id}
                            className="flex items-start gap-2 text-[12px] text-[#b0b0b0] bg-[#121212] border border-[#2e2e2e] rounded px-3 py-2"
                          >
                            <input
                              type="checkbox"
                              checked={form.optionalAreaIds.includes(area.id)}
                              onChange={() => toggleOptionalArea(area.id)}
                              className="mt-0.5 accent-[#76ff03]"
                            />
                            <span>
                              <strong className="text-[#76ff03]">Area {area.areaNumber}</strong>
                              {' — '}
                              {area.name}
                            </span>
                          </label>
                        ))}
                      </div>
                    </div>
                  )}
                </>
              )}

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

            <InternalAccreditorAssignmentPanel />
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

function InternalAccreditorAssignmentPanel() {
  const qc = useQueryClient()
  const [internalAccreditor, setInternalAccreditor] = useState('')
  const [program, setProgram] = useState('')
  const [error, setError] = useState('')

  const { data: users = [] } = useQuery({ queryKey: ['users'], queryFn: fetchUsers })
  const { data: programs = [] } = useQuery({ queryKey: ['programs'], queryFn: fetchPrograms })
  const { data: assignments = [], isLoading } = useQuery({
    queryKey: ['internal-accreditor-assignments'],
    queryFn: fetchInternalAccreditorAssignments,
  })

  const createMutation = useMutation({
    mutationFn: () => createInternalAccreditorAssignment({ internalAccreditor, program }),
    onSuccess: () => {
      setInternalAccreditor('')
      setProgram('')
      setError('')
      qc.invalidateQueries({ queryKey: ['internal-accreditor-assignments'] })
    },
    onError: (err) => setError(err.response?.data?.detail || 'Failed to assign the Internal Accreditor.'),
  })

  const deleteMutation = useMutation({
    mutationFn: (id) => deleteInternalAccreditorAssignment(id),
    onSuccess: () => {
      setError('')
      qc.invalidateQueries({ queryKey: ['internal-accreditor-assignments'] })
    },
    onError: (err) => setError(err.response?.data?.detail || 'Failed to remove the assignment.'),
  })

  const internalAccreditors = users.filter(user => user.role === 'ROLE_INTERNAL_ACCREDITOR')

  return (
    <div className="bg-[#1a1a1a] border border-[#252525] rounded-md p-5 mt-6">
      <h2 className="text-[14px] font-semibold text-white mb-1" style={{ fontFamily: 'Poppins, sans-serif' }}>
        Assign Internal Accreditor
      </h2>
      <p className="text-[12px] text-[#6b6b6b] mb-4">
        Assign one IA to the whole program. The IA will review all areas in that program.
      </p>

      {error && <p className="text-[12px] text-[#f87171] mb-3">{error}</p>}

      <form
        className="space-y-3"
        onSubmit={(event) => {
          event.preventDefault()
          createMutation.mutate()
        }}
      >
        <select value={internalAccreditor} onChange={event => setInternalAccreditor(event.target.value)} required className="form-select w-full">
          <option value="">Select Internal Accreditor</option>
          {internalAccreditors.map(user => (
            <option key={user.id} value={user['@id'] || `/api/users/${user.id}`}>
              {user.fullName || user.email}
            </option>
          ))}
        </select>
        <select value={program} onChange={event => setProgram(event.target.value)} required className="form-select w-full">
          <option value="">Select program</option>
          {programs.map(programOption => (
            <option key={programOption.id} value={programOption['@id'] || `/api/programs/${programOption.id}`}>
              {programOption.name} ({programOption.code})
            </option>
          ))}
        </select>
        <button
          type="submit"
          disabled={createMutation.isPending}
          className="w-full bg-[#76ff03] hover:bg-[#65e000] text-[#121212] font-semibold text-[13px] px-4 py-2.5 rounded disabled:opacity-50"
        >
          {createMutation.isPending ? 'Assigning...' : 'Assign Program'}
        </button>
      </form>

      {!isLoading && assignments.length > 0 && (
        <div className="mt-4 space-y-2">
          {assignments.map(assignment => (
            <div key={assignment.id} className="flex items-center justify-between gap-2 text-[11px] text-[#8a8a8a]">
              <span>
                {assignment.internalAccreditor?.email} - {assignment.program?.code || assignment.program?.name}
              </span>
              <button
                type="button"
                onClick={() => deleteMutation.mutate(assignment.id)}
                disabled={deleteMutation.isPending}
                className="text-[#f87171] hover:text-[#fca5a5] disabled:opacity-50"
              >
                Remove
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
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
