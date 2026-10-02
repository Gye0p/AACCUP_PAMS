import { useState } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { fetchAreaAssignment, submitIaReview, updateActivity } from '../api'
import { STATE_LABELS, formatDate } from '../utils'
import { useAuth } from '../context/AuthContext'
import Layout from '../components/Layout'

export default function IAReviewPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const { isIA, isAdmin } = useAuth()
  const qc = useQueryClient()

  const { data: aa, isLoading } = useQuery({
    queryKey: ['area-assignment', id],
    queryFn: () => fetchAreaAssignment(id),
  })

  const [iaStatus, setIaStatus] = useState(aa?.iaStatus || 'pending')
  const [iaComments, setIaComments] = useState(aa?.iaComments || '')
  const [reviewSuccess, setReviewSuccess] = useState(false)

  const reviewMutation = useMutation({
    mutationFn: () => submitIaReview(id, { iaStatus, iaComments }),
    onSuccess: () => {
      qc.invalidateQueries(['area-assignment', id])
      setReviewSuccess(true)
      setTimeout(() => setReviewSuccess(false), 5000)
    }
  })

  const transitionMutation = useMutation({
    mutationFn: ({ actId, transition }) => updateActivity(actId, { transition }),
    onSuccess: () => qc.invalidateQueries(['area-assignment', id])
  })

  if (isLoading) return (
    <Layout>
      <div className="space-y-4">
        <div className="h-3 w-32 bg-[#252525] rounded animate-pulse" />
        <div className="h-6 w-64 bg-[#252525] rounded animate-pulse" />
        <div className="mt-6 space-y-3">
          {[1,2].map(i => <div key={i} className="h-20 bg-[#1a1a1a] border border-[#252525] rounded-md animate-pulse" />)}
        </div>
      </div>
    </Layout>
  )

  const canReview = isIA() || isAdmin()
  const pendingActivities = aa?.complianceActivities?.filter(a =>
    ['submitted', 'under_review'].includes(a.state)
  ) || []

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
        <div className="flex items-center gap-2 mb-1">
          <span className="text-[12px] font-semibold text-[#76ff03]">Area {aa?.area?.areaNumber}</span>
          <span className="text-[#3a3a3a]">—</span>
          <span className="text-[12px] text-[#8a8a8a]">IA Review</span>
        </div>
        <h1 className="text-xl font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
          {aa?.area?.name}
        </h1>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {/* Activities for review */}
        <div className="lg:col-span-3">
          <div className="bg-[#1a1a1a] border border-[#252525] rounded-md overflow-hidden">
            <div className="px-5 py-4 border-b border-[#252525] flex items-center justify-between">
              <h2 className="text-[14px] font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
                Activities Pending Review
              </h2>
              {pendingActivities.length > 0 && (
                <span className="text-[11px] text-[#8a8a8a] bg-[#252525] px-2 py-0.5 rounded">
                  {pendingActivities.length}
                </span>
              )}
            </div>

            {pendingActivities.length === 0 ? (
              <div className="px-5 py-12 text-center">
                <div className="flex items-center justify-center w-10 h-10 bg-[#1e1e1e] rounded-md mx-auto mb-3">
                  <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#6b6b6b" strokeWidth="1.5">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                  </svg>
                </div>
                <p className="text-[13px] text-[#6b6b6b]">No activities pending review for this area.</p>
              </div>
            ) : (
              <div className="divide-y divide-[#1e1e1e]">
                {pendingActivities.map(act => (
                  <ActivityReviewCard
                    key={act.id}
                    activity={act}
                    canReview={canReview}
                    onTransition={(transition) => transitionMutation.mutate({ actId: act.id, transition })}
                    isPending={transitionMutation.isPending}
                  />
                ))}
              </div>
            )}
          </div>
        </div>

        {/* IA Area-level decision */}
        {canReview && (
          <div className="lg:col-span-2">
            <div className="bg-[#1a1a1a] border border-[#252525] rounded-md p-5">
              <h2 className="text-[14px] font-semibold text-white mb-4" style={{ fontFamily: 'Poppins, sans-serif' }}>
                Area-Level Decision
              </h2>

              {reviewSuccess && (
                <div className="flex items-start gap-2.5 bg-[#0f1e10] border border-[#1a4a1a] rounded px-3 py-2.5 mb-4">
                  <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#4ade80" strokeWidth="2" className="flex-shrink-0 mt-0.5">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                  <span className="text-[12px] text-[#4ade80]">IA review saved successfully.</span>
                </div>
              )}

              <div className="space-y-4">
                <div>
                  <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
                    IA Status
                  </label>
                  <select
                    value={iaStatus}
                    onChange={e => setIaStatus(e.target.value)}
                    className="
                      w-full bg-[#121212] border border-[#2e2e2e] rounded
                      px-3 py-2.5 text-[13px] text-white
                      focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/20
                      transition-colors duration-150
                    "
                  >
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="needs_revision">Needs Revision</option>
                  </select>
                </div>

                <div>
                  <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
                    Comments
                  </label>
                  <textarea
                    value={iaComments}
                    onChange={e => setIaComments(e.target.value)}
                    placeholder="Enter review comments or notes…"
                    rows={5}
                    className="
                      w-full bg-[#121212] border border-[#2e2e2e] rounded
                      px-3 py-2.5 text-[13px] text-white placeholder-[#4a4a4a]
                      focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/20
                      transition-colors duration-150 resize-y
                    "
                  />
                </div>

                <button
                  onClick={() => reviewMutation.mutate()}
                  disabled={reviewMutation.isPending}
                  className="
                    w-full bg-[#76ff03] hover:bg-[#65e000] active:bg-[#56cc00]
                    text-[#121212] font-semibold text-[13px]
                    px-4 py-2.5 rounded
                    transition-colors duration-150
                    disabled:opacity-50 disabled:cursor-not-allowed
                  "
                >
                  {reviewMutation.isPending ? 'Saving…' : 'Save IA Review'}
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </Layout>
  )
}

function ActivityReviewCard({ activity: act, canReview, onTransition, isPending }) {
  const stateStyles = {
    submitted:    'bg-[#1e2f6b]/60 text-[#60a5fa]',
    under_review: 'bg-[#3a2800]/80 text-[#f59e0b]',
  }
  const cls = stateStyles[act.state] || 'bg-[#252525] text-[#8a8a8a]'

  return (
    <div className="px-5 py-4">
      <div className="flex items-start justify-between gap-3 flex-wrap mb-1">
        <div>
          <span className="text-[13px] font-medium text-[#d0d0d0]">{act.title}</span>
          <span className={`inline-block text-[10px] font-medium px-1.5 py-0.5 rounded uppercase tracking-wide ml-2 ${cls}`}>
            {STATE_LABELS[act.state]}
          </span>
        </div>
        <span className="text-[11px] text-[#6b6b6b] whitespace-nowrap">
          {formatDate(act.startDate)} — {formatDate(act.endDate)}
        </span>
      </div>

      {act.description && (
        <p className="text-[12px] text-[#8a8a8a] mt-1 mb-3 leading-relaxed">{act.description}</p>
      )}

      {canReview && (
        <div className="flex items-center gap-2 mt-3">
          {act.state === 'submitted' && (
            <button
              onClick={() => onTransition('start_review')}
              disabled={isPending}
              className="
                text-[12px] font-medium text-[#60a5fa]
                border border-[#1e3a6b] bg-[#1e2f6b]/20 hover:bg-[#1e2f6b]/40
                px-3 py-1.5 rounded transition-all duration-150
                disabled:opacity-50 disabled:cursor-not-allowed
              "
            >
              Start Review
            </button>
          )}
          {act.state === 'under_review' && (
            <>
              <button
                onClick={() => onTransition('approve')}
                disabled={isPending}
                className="
                  text-[12px] font-medium text-[#76ff03]
                  border border-[#2a4a1a] bg-[#76ff03]/10 hover:bg-[#76ff03]/20
                  px-3 py-1.5 rounded transition-all duration-150
                  disabled:opacity-50 disabled:cursor-not-allowed
                "
              >
                Approve
              </button>
              <button
                onClick={() => onTransition('request_revision')}
                disabled={isPending}
                className="
                  text-[12px] font-medium text-[#f87171]
                  border border-[#4a1a1a] bg-[#ef4444]/10 hover:bg-[#ef4444]/20
                  px-3 py-1.5 rounded transition-all duration-150
                  disabled:opacity-50 disabled:cursor-not-allowed
                "
              >
                Request Revision
              </button>
            </>
          )}
        </div>
      )}
    </div>
  )
}
