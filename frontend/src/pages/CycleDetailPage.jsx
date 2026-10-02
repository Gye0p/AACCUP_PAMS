import { useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { downloadReport, fetchCycle } from '../api'
import { formatDate, STATE_LABELS } from '../utils'
import { useAuth } from '../context/AuthContext'
import Layout from '../components/Layout'

export default function CycleDetailPage() {
  const { id } = useParams()
  const { isAdmin, isProgramHead } = useAuth()
  const [openArea, setOpenArea] = useState(null)
  const [reportLoading, setReportLoading] = useState(false)
  const [reportError, setReportError] = useState('')

  const { data: cycle, isLoading } = useQuery({
    queryKey: ['cycle', id],
    queryFn: () => fetchCycle(id),
  })

  if (isLoading) return (
    <Layout>
      <div className="space-y-4">
        <div className="h-3 w-32 bg-[#252525] rounded animate-pulse" />
        <div className="h-6 w-64 bg-[#252525] rounded animate-pulse" />
        <div className="h-3 w-48 bg-[#252525] rounded animate-pulse" />
        <div className="mt-6 space-y-2">
          {[1,2,3,4,5].map(i => <div key={i} className="h-12 bg-[#1a1a1a] border border-[#252525] rounded-md animate-pulse" />)}
        </div>
      </div>
    </Layout>
  )

  const program = cycle?.program
  const deadline = cycle?.complianceDeadline

  const handleReportDownload = async () => {
    setReportLoading(true)
    setReportError('')
    try {
      await downloadReport(id)
    } catch (error) {
      setReportError(error.response?.data?.detail || 'The report could not be generated.')
    } finally {
      setReportLoading(false)
    }
  }

  return (
    <Layout>
      {/* Breadcrumb */}
      <nav className="flex items-center gap-2 text-[12px] text-[#6b6b6b] mb-5">
        <Link to="/dashboard" className="hover:text-[#b0b0b0] transition-colors">Dashboard</Link>
        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
          <path strokeLinecap="round" strokeLinejoin="round" d="M9 18l6-6-6-6"/>
        </svg>
        <span className="text-[#8a8a8a]">Cycle Detail</span>
      </nav>

      {/* Header */}
      <div className="flex items-start justify-between gap-4 flex-wrap mb-6">
        <div>
          <h1 className="text-xl font-semibold text-white leading-tight" style={{ fontFamily: 'Poppins, sans-serif' }}>
            {program?.name}
          </h1>
          <div className="flex items-center gap-3 mt-2 flex-wrap">
            <span className="text-[12px] text-[#8a8a8a]">AY {cycle?.academicYear}</span>
            <span className="text-[#3a3a3a]">·</span>
            <span className="text-[12px] text-[#8a8a8a]">
              Deadline: <span className="text-[#b0b0b0] font-medium">{formatDate(deadline)}</span>
            </span>
            <span className="text-[#3a3a3a]">·</span>
            <CycleStatusPill status={cycle?.status} />
          </div>
        </div>

        <div className="flex items-center gap-2 flex-shrink-0">
          <button
            type="button"
            onClick={handleReportDownload}
            disabled={reportLoading}
            className="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#b0b0b0] hover:text-white border border-[#2e2e2e] hover:border-[#424242] bg-[#1a1a1a] hover:bg-[#222] px-3 py-2 rounded transition-all duration-150 disabled:opacity-50"
          >
            {reportLoading ? 'Generating...' : 'Generate Report'}
          </button>
          {(isAdmin() || isProgramHead()) && (
            <Link
              to={`/cycles/${id}/gantt`}
              className="
                inline-flex items-center gap-1.5 text-[12px] font-medium
                text-[#b0b0b0] hover:text-white
                border border-[#2e2e2e] hover:border-[#424242]
                bg-[#1a1a1a] hover:bg-[#222]
                px-3 py-2 rounded transition-all duration-150
              "
            >
              <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Schedule
            </Link>
          )}
        </div>
      </div>

      {reportError && (
        <div className="mb-5 bg-[#1e1212] border border-[#4a1a1a] rounded-md px-4 py-3 text-[13px] text-[#f87171]">
          {reportError}
        </div>
      )}

      {/* AACCUP Areas */}
      <div className="mb-3">
        <h2 className="text-[13px] font-medium text-[#8a8a8a] uppercase tracking-wider">
          AACCUP Areas — {cycle?.areaAssignments?.length || 0} total
        </h2>
      </div>

      <div className="space-y-2">
        {cycle?.areaAssignments?.map(aa => (
          <AreaAccordionItem
            key={aa.id}
            areaAssignment={aa}
            isOpen={openArea === aa.id}
            onToggle={() => setOpenArea(openArea === aa.id ? null : aa.id)}
            cycleDeadline={deadline}
            canAct={isAdmin() || isProgramHead()}
            canEditActivity={isAdmin() || isProgramHead()}
          />
        ))}
      </div>
    </Layout>
  )
}

function AreaAccordionItem({ areaAssignment, isOpen, onToggle, cycleDeadline, canAct, canEditActivity }) {
  const aa = areaAssignment

  const iaStatusStyles = {
    pending:        { badge: 'bg-[#252525] text-[#8a8a8a]' },
    approved:       { badge: 'bg-[#76ff03]/10 text-[#76ff03]' },
    needs_revision: { badge: 'bg-[#ef4444]/10 text-[#ef4444]' },
  }
  const iaStyle = iaStatusStyles[aa.iaStatus] || iaStatusStyles.pending

  const activityCount = aa.complianceActivities?.length || 0

  return (
    <div className="bg-[#1a1a1a] border border-[#252525] rounded-md overflow-hidden">
      {/* Accordion header */}
      <button
        onClick={onToggle}
        className="
          w-full flex items-center justify-between px-5 py-3.5
          hover:bg-[#1e1e1e] transition-colors duration-100
          text-left focus:outline-none focus-visible:ring-1 focus-visible:ring-[#76ff03]/50
        "
        aria-expanded={isOpen}
      >
        <div className="flex items-center gap-3 min-w-0">
          <span className="flex-shrink-0 text-[12px] font-semibold text-[#76ff03] w-14">
            Area {aa.area?.areaNumber}
          </span>
          <span className="text-[13px] text-[#d0d0d0] truncate">{aa.area?.name}</span>
          {activityCount > 0 && (
            <span className="flex-shrink-0 text-[11px] text-[#6b6b6b] bg-[#252525] px-1.5 py-0.5 rounded">
              {activityCount}
            </span>
          )}
        </div>
        <div className="flex items-center gap-3 flex-shrink-0 ml-4">
          <span className={`text-[10px] font-medium px-2 py-0.5 rounded uppercase tracking-wide ${iaStyle.badge}`}>
            IA: {aa.iaStatus?.replace('_', ' ') || 'Pending'}
          </span>
          <svg
            width="14" height="14" fill="none" viewBox="0 0 24 24"
            stroke="#6b6b6b" strokeWidth="2"
            className={`transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}
          >
            <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </button>

      {/* Accordion body */}
      {isOpen && (
        <div className="border-t border-[#252525] px-5 py-4">
          {aa.iaComments && (
            <div className="flex items-start gap-2.5 bg-[#1a1800] border border-[#3a3000] rounded px-3 py-2.5 mb-4">
              <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#d4a017" strokeWidth="2" className="flex-shrink-0 mt-0.5">
                <path strokeLinecap="round" strokeLinejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
              </svg>
              <div>
                <span className="text-[11px] font-medium text-[#d4a017] block mb-0.5">IA Comments</span>
                <p className="text-[12px] text-[#c0a040] leading-relaxed">{aa.iaComments}</p>
              </div>
            </div>
          )}

          {/* Actions */}
          <div className="flex items-center gap-2 mb-4">
            {canAct && (
              <Link
                to={`/activities/new?areaAssignment=${aa['@id']}&deadline=${encodeURIComponent(cycleDeadline)}`}
                className="
                  inline-flex items-center gap-1.5 text-[12px] font-medium
                  bg-[#76ff03] hover:bg-[#65e000] text-[#121212]
                  px-3 py-1.5 rounded transition-colors duration-150
                "
              >
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                New Activity
              </Link>
            )}
            <Link
              to={`/ia-review/${aa.id}`}
              className="
                inline-flex items-center gap-1.5 text-[12px] font-medium
                text-[#b0b0b0] hover:text-white
                border border-[#2e2e2e] hover:border-[#424242]
                bg-transparent hover:bg-[#222]
                px-3 py-1.5 rounded transition-all duration-150
              "
            >
              IA Review
            </Link>
          </div>

          {/* Activities table */}
          {aa.complianceActivities?.length > 0 ? (
            <div className="border border-[#252525] rounded overflow-hidden">
              <table className="w-full text-[12px]">
                <thead>
                  <tr className="border-b border-[#252525] bg-[#161616]">
                    <th className="px-3 py-2.5 text-left text-[11px] font-medium text-[#6b6b6b] uppercase tracking-wide">Activity</th>
                    <th className="px-3 py-2.5 text-left text-[11px] font-medium text-[#6b6b6b] uppercase tracking-wide hidden sm:table-cell">Start</th>
                    <th className="px-3 py-2.5 text-left text-[11px] font-medium text-[#6b6b6b] uppercase tracking-wide hidden sm:table-cell">End</th>
                    <th className="px-3 py-2.5 text-left text-[11px] font-medium text-[#6b6b6b] uppercase tracking-wide">State</th>
                    <th className="px-3 py-2.5 text-left text-[11px] font-medium text-[#6b6b6b] uppercase tracking-wide">Evidence</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#1e1e1e]">
                  {aa.complianceActivities.map(act => (
                    <tr key={act.id} className="hover:bg-[#1e1e1e] transition-colors duration-100">
                      <td className="px-3 py-2.5">
                        {canEditActivity ? (
                          <Link
                            to={`/activities/${act.id}/edit`}
                            className="text-[#b0b0b0] hover:text-[#76ff03] transition-colors line-clamp-1"
                          >
                            {act.title}
                          </Link>
                        ) : (
                          <span className="text-[#b0b0b0] line-clamp-1">{act.title}</span>
                        )}
                      </td>
                      <td className="px-3 py-2.5 text-[#8a8a8a] hidden sm:table-cell whitespace-nowrap">
                        {formatDate(act.startDate)}
                      </td>
                      <td className="px-3 py-2.5 text-[#8a8a8a] hidden sm:table-cell whitespace-nowrap">
                        {formatDate(act.endDate)}
                      </td>
                      <td className="px-3 py-2.5">
                        <ActivityStatePill state={act.state} />
                      </td>
                      <td className="px-3 py-2.5">
                        <Link
                          to={`/evidence/${act.id}`}
                          className="text-[12px] text-[#b0b0b0] hover:text-[#76ff03] transition-colors inline-flex items-center gap-1"
                        >
                          <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                          </svg>
                          Upload
                        </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="border border-dashed border-[#2a2a2a] rounded px-4 py-6 text-center">
              <p className="text-[12px] text-[#6b6b6b]">No activities yet. Use &ldquo;New Activity&rdquo; to add one.</p>
            </div>
          )}
        </div>
      )}
    </div>
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

function ActivityStatePill({ state }) {
  const stateStyles = {
    draft:          'bg-[#252525] text-[#8a8a8a]',
    submitted:      'bg-[#1e2f6b]/60 text-[#60a5fa]',
    under_review:   'bg-[#3a2800]/80 text-[#f59e0b]',
    needs_revision: 'bg-[#3a1212]/80 text-[#f87171]',
    approved:       'bg-[#76ff03]/10 text-[#76ff03]',
    completed:      'bg-[#1e3a5f]/60 text-[#7dd3fc]',
  }
  const cls = stateStyles[state] || 'bg-[#252525] text-[#8a8a8a]'
  return (
    <span className={`inline-block text-[10px] font-medium px-1.5 py-0.5 rounded uppercase tracking-wide ${cls}`}>
      {STATE_LABELS[state] || state}
    </span>
  )
}
