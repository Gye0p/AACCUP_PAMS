import { useEffect, useReducer, useState } from 'react'
import { useNavigate, useSearchParams, useParams } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { fetchActivity, fetchAreaAssignment, createActivity, updateActivity } from '../api'
import { formatDateShort } from '../utils'
import Layout from '../components/Layout'

export default function ActivityFormPage() {
  const navigate = useNavigate()
  const qc = useQueryClient()
  const [searchParams] = useSearchParams()
  const { id: editId } = useParams()

  const areaAssignmentIri = searchParams.get('areaAssignment') || ''
  const requestedDeadline = searchParams.get('deadline') || ''

  const { data: activity } = useQuery({
    queryKey: ['activity', editId],
    queryFn: () => fetchActivity(editId),
    enabled: !!editId,
  })

  const activityAssignmentIri = typeof activity?.areaAssignment === 'string'
    ? activity.areaAssignment
    : activity?.areaAssignment?.['@id'] || ''
  const effectiveAreaAssignmentIri = areaAssignmentIri || activityAssignmentIri
  const areaAssignmentId = effectiveAreaAssignmentIri.split('/').pop()
  const deadline = requestedDeadline || activity?.areaAssignment?.cycle?.complianceDeadline || ''

  const { data: aa } = useQuery({
    queryKey: ['area-assignment', areaAssignmentId],
    queryFn: () => fetchAreaAssignment(areaAssignmentId),
    enabled: !!areaAssignmentId,
  })

  const [form, dispatchForm] = useReducer((state, action) => {
    if (action.type === 'hydrate') return action.value
    return { ...state, ...action.patch }
  }, {
    title: '',
    description: '',
    startDate: '',
    endDate: '',
  })

  useEffect(() => {
    if (!activity) return
    dispatchForm({
      type: 'hydrate',
      value: {
        title: activity.title || '',
        description: activity.description || '',
        startDate: activity.startDate?.slice(0, 10) || '',
        endDate: activity.endDate?.slice(0, 10) || '',
      },
    })
  }, [activity])
  const [error, setError] = useState('')
  const maxDate = deadline ? formatDateShort(deadline) : ''

  const mutation = useMutation({
    mutationFn: (data) => editId
      ? updateActivity(editId, data)
      : createActivity(data),
    onSuccess: () => {
      qc.invalidateQueries(['cycle'])
      navigate(-1)
    },
    onError: (err) => {
      setError(err.response?.data?.detail || 'Failed to save activity. Please check the form and try again.')
    }
  })

  const handleSubmit = (e) => {
    e.preventDefault()
    setError('')
    const payload = {
      title: form.title,
      description: form.description,
      startDate: form.startDate ? new Date(form.startDate).toISOString() : null,
      endDate: form.endDate ? new Date(form.endDate).toISOString() : null,
    }
    if (!editId) payload.areaAssignment = effectiveAreaAssignmentIri
    mutation.mutate(payload)
  }

  const handleTransition = (transition) => {
    mutation.mutate({ transition })
  }

  return (
    <Layout>
      {/* Back button */}
      <button
        onClick={() => navigate(-1)}
        className="flex items-center gap-1.5 text-[12px] text-[#8a8a8a] hover:text-[#b0b0b0] transition-colors mb-5"
      >
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
          <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back
      </button>

      <div className="mb-6">
        <h1 className="text-xl font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
          {editId ? 'Edit Activity' : 'New Compliance Activity'}
        </h1>
        <p className="text-[13px] text-[#8a8a8a] mt-1">
          {editId
            ? 'Update the activity details below.'
            : 'Fill in the details for this compliance activity.'}
        </p>
      </div>

      <div className="max-w-xl">
        {/* Context banner */}
        {aa && (
          <div className="bg-[#1a1a1a] border border-[#252525] rounded-md px-4 py-3 mb-5 flex items-center gap-3">
            <span className="text-[12px] font-semibold text-[#76ff03]">Area {aa.area?.areaNumber}</span>
            <span className="text-[#3a3a3a]">—</span>
            <span className="text-[13px] text-[#b0b0b0]">{aa.area?.name}</span>
            {maxDate && (
              <>
                <span className="text-[#3a3a3a] ml-auto">Deadline</span>
                <span className="text-[12px] font-medium text-[#f59e0b]">{maxDate}</span>
              </>
            )}
          </div>
        )}

        {/* Error */}
        {error && (
          <div className="flex items-start gap-2.5 bg-[#1e1212] border border-[#4a1a1a] rounded px-4 py-3 mb-5">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#f87171" strokeWidth="2" className="flex-shrink-0 mt-0.5">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span className="text-[13px] text-[#f87171]">{error}</span>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
              Title <span className="text-[#ef4444]">*</span>
            </label>
            <input
              value={form.title}
              onChange={e => dispatchForm({ type: 'patch', patch: { title: e.target.value } })}
              required
              maxLength={300}
              placeholder="Activity title"
              className="
                w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded
                px-3 py-2.5 text-[13px] text-white placeholder-[#4a4a4a]
                focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/20
                transition-colors duration-150
              "
            />
          </div>

          <div>
            <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
              Description
            </label>
            <textarea
              value={form.description}
              onChange={e => dispatchForm({ type: 'patch', patch: { description: e.target.value } })}
              placeholder="Optional description or notes"
              rows={3}
              className="
                w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded
                px-3 py-2.5 text-[13px] text-white placeholder-[#4a4a4a]
                focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/20
                transition-colors duration-150 resize-y
              "
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
                Start Date <span className="text-[#ef4444]">*</span>
              </label>
              <input
                type="date"
                value={form.startDate}
                onChange={e => dispatchForm({ type: 'patch', patch: { startDate: e.target.value } })}
                required
                max={maxDate || undefined}
                className="
                  w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded
                  px-3 py-2.5 text-[13px] text-white
                  focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/20
                  transition-colors duration-150
                "
              />
            </div>
            <div>
              <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
                End Date <span className="text-[#ef4444]">*</span>
                {maxDate && <span className="text-[#8a8a8a] font-normal ml-1">(max: {maxDate})</span>}
              </label>
              <input
                type="date"
                value={form.endDate}
                onChange={e => dispatchForm({ type: 'patch', patch: { endDate: e.target.value } })}
                required
                max={maxDate || undefined}
                className="
                  w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded
                  px-3 py-2.5 text-[13px] text-white
                  focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/20
                  transition-colors duration-150
                "
              />
            </div>
          </div>

          {/* Actions */}
          <div className="flex items-center gap-3 pt-2">
            <button
              type="button"
              onClick={() => navigate(-1)}
              className="
                text-[13px] font-medium text-[#b0b0b0] hover:text-white
                border border-[#2e2e2e] hover:border-[#424242]
                bg-transparent hover:bg-[#1e1e1e]
                px-4 py-2.5 rounded transition-all duration-150
              "
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={mutation.isPending}
              className="
                bg-[#76ff03] hover:bg-[#65e000] active:bg-[#56cc00]
                text-[#121212] font-semibold text-[13px]
                px-4 py-2.5 rounded
                transition-colors duration-150
                disabled:opacity-50 disabled:cursor-not-allowed
              "
            >
              {mutation.isPending ? 'Saving…' : (editId ? 'Update Activity' : 'Create Activity')}
            </button>
            {editId && (
              <button
                type="button"
                onClick={() => handleTransition('submit')}
                disabled={mutation.isPending}
                className="
                  bg-[#1a3a1a] hover:bg-[#254a25] border border-[#2a4a2a]
                  text-[#4ade80] font-medium text-[13px]
                  px-4 py-2.5 rounded
                  transition-all duration-150
                  disabled:opacity-50 disabled:cursor-not-allowed
                "
              >
                Submit for Review
              </button>
            )}
          </div>
        </form>
      </div>
    </Layout>
  )
}
