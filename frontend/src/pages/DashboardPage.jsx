import { useQuery } from '@tanstack/react-query'
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { fetchCycle, fetchDashboard, fetchGantt } from '../api'
import { getUrgencyLabel, formatDate } from '../utils'
import { useAuth } from '../context/useAuth'
import Layout from '../components/Layout'
import GanttChart from '../components/GanttChart'
import { AreaAccordionItem } from './CycleDetailPage'

export default function DashboardPage() {
  const { isAdmin, isAreaCoordinator, isProgramHead } = useAuth()
  const showDashboardGantt = isProgramHead()
  const { data: cycles, isLoading, error } = useQuery({
    queryKey: ['dashboard'],
    queryFn: fetchDashboard,
  })
  const activeCycle = cycles?.find(cycle => cycle.status === 'active') || cycles?.[0]
  const { data: ganttTasks, isLoading: isGanttLoading, error: ganttError } = useQuery({
    queryKey: ['dashboard-gantt', activeCycle?.cycleId],
    queryFn: () => fetchGantt(activeCycle.cycleId),
    enabled: showDashboardGantt && Boolean(activeCycle?.cycleId),
  })
  const { data: currentCycle } = useQuery({
    queryKey: ['dashboard-cycle', activeCycle?.cycleId],
    queryFn: () => fetchCycle(activeCycle.cycleId),
    enabled: showDashboardGantt && Boolean(activeCycle?.cycleId),
  })

  if (isLoading) return (
    <Layout>
      <PageHeader isProgramHead={showDashboardGantt} />
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        {[1, 2, 3].map(i => (
          <div key={i} className="bg-[#1a1a1a] border border-[#252525] rounded-md p-5 animate-pulse">
            <div className="h-3 w-24 bg-[#2a2a2a] rounded mb-3" />
            <div className="h-4 w-40 bg-[#2a2a2a] rounded mb-5" />
            <div className="h-3 w-32 bg-[#2a2a2a] rounded mb-2" />
            <div className="h-3 w-28 bg-[#2a2a2a] rounded" />
          </div>
        ))}
      </div>
    </Layout>
  )

  if (error) return (
    <Layout>
      <PageHeader isProgramHead={showDashboardGantt} />
      <div className="mt-6 bg-[#1e1212] border border-[#4a1a1a] rounded-md px-5 py-4 flex items-center gap-3">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#f87171" strokeWidth="2">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span className="text-[13px] text-[#f87171]">Failed to load accreditation cycles. Please refresh and try again.</span>
      </div>
    </Layout>
  )

  return (
    <Layout>
      <PageHeader isProgramHead={showDashboardGantt} />

      {/* Urgency legend */}
      <div className="flex items-center gap-6 mt-4 mb-6">
        <LegendItem color="#76ff03" label="30+ days remaining" />
        <LegendItem color="#f59e0b" label="8–30 days" />
        <LegendItem color="#ef4444" label="Under 8 days / Overdue" />
      </div>

      {cycles?.length === 0 ? (
        <EmptyState />
      ) : (
        <>
          {showDashboardGantt && activeCycle && (
            <>
              <CurrentCyclePeriod cycle={activeCycle} />
              <DashboardSchedule
                cycle={activeCycle}
                tasks={ganttTasks}
                isLoading={isGanttLoading}
                error={ganttError}
              />
              {currentCycle && (
                <DashboardCycleDetails
                  cycle={currentCycle}
                  canAct={isAdmin() || isProgramHead()}
                  canUploadAreaEvidence={isAdmin() || isAreaCoordinator()}
                  canManageSarRecommendations={isAdmin()}
                />
              )}
            </>
          )}
          {!showDashboardGantt && (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {cycles?.map(cycle => (
                <CycleCard key={cycle.cycleId} cycle={cycle} />
              ))}
            </div>
          )}
        </>
      )}
    </Layout>
  )
}

function CurrentCyclePeriod({ cycle }) {
  return (
    <section className="mb-4 bg-[#1a1a1a] border border-[#252525] rounded-md px-5 py-4">
      <div className="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <p className="text-[11px] font-medium text-[#6b6b6b] uppercase tracking-wider">
            Current Cycle Period
          </p>
          <p className="text-[15px] font-semibold text-white mt-1">
            AY {cycle.academicYear}
          </p>
        </div>
        <div className="flex items-center gap-5">
          <div>
            <p className="text-[11px] text-[#6b6b6b]">Deadline</p>
            <div className="flex items-center gap-2 mt-1">
              <span
                className="inline-block w-2 h-2 rounded-full flex-shrink-0"
                style={{ background: getUrgencyColor(cycle.urgency) }}
                aria-hidden="true"
              />
              <p className="text-[12px] text-[#c0c0c0] font-medium">{formatDate(cycle.deadline)}</p>
            </div>
            <p className="text-[10px] mt-1" style={{ color: getUrgencyColor(cycle.urgency) }}>
              {getUrgencyLabel(cycle.daysRemaining)}
            </p>
          </div>
          <StatusPill status={cycle.status} />
        </div>
      </div>
    </section>
  )
}

function getUrgencyColor(urgency) {
  return {
    green: '#76ff03',
    amber: '#f59e0b',
    red: '#ef4444',
  }[urgency] || '#76ff03'
}

function DashboardSchedule({ cycle, tasks, isLoading, error }) {
  return (
    <section className="mb-6 bg-[#1a1a1a] border border-[#252525] rounded-md overflow-hidden">
      <div className="flex items-center justify-between gap-4 px-5 py-4 border-b border-[#252525]">
        <div>
          <h2 className="text-[14px] font-semibold text-white">Current Cycle Schedule</h2>
          <p className="text-[12px] text-[#8a8a8a] mt-1">
            {cycle.programCode} · Active accreditation activities
          </p>
        </div>
        <Link
          to={`/cycles/${cycle.cycleId}`}
          className="flex-shrink-0 text-[12px] font-medium text-[#76ff03] hover:text-white transition-colors"
        >
          Open cycle details
        </Link>
      </div>
      {isLoading && (
        <div className="h-40 p-5 animate-pulse">
          <div className="h-full bg-[#222] rounded" />
        </div>
      )}
      {error && (
        <div className="px-5 py-6 text-[13px] text-[#f87171]">
          Failed to load the current cycle schedule.
        </div>
      )}
      {!isLoading && !error && tasks?.length === 0 && (
        <div className="px-5 py-8 text-[13px] text-[#6b6b6b]">
          No approved or completed activities are available yet.
        </div>
      )}
      {!isLoading && !error && tasks?.length > 0 && <GanttChart tasks={tasks} />}
    </section>
  )
}

function DashboardCycleDetails({ cycle, canAct, canUploadAreaEvidence, canManageSarRecommendations }) {
  const [openArea, setOpenArea] = useState(null)

  return (
    <section className="mb-6">
      <div className="flex items-center justify-between gap-4 mb-3">
        <h2 className="text-[13px] font-medium text-[#8a8a8a] uppercase tracking-wider">
          AACCUP Areas — {cycle.areaAssignments?.length || 0} total
        </h2>
        <Link
          to={`/cycles/${cycle.id}`}
          className="text-[12px] font-medium text-[#76ff03] hover:text-white transition-colors"
        >
          Open full cycle page
        </Link>
      </div>
      <div className="space-y-2">
        {cycle.areaAssignments?.map((areaAssignment, index) => (
          <AreaAccordionItem
            key={areaAssignment.id}
            areaAssignment={areaAssignment}
            areaIndex={index}
            cycle={cycle}
            isOpen={openArea === areaAssignment.id}
            onToggle={() => setOpenArea(openArea === areaAssignment.id ? null : areaAssignment.id)}
            cycleDeadline={cycle.complianceDeadline}
            canAct={canAct}
            canEditActivity={canAct}
            canUploadAreaEvidence={canUploadAreaEvidence}
            canManageSarRecommendations={canManageSarRecommendations}
          />
        ))}
      </div>
    </section>
  )
}

function PageHeader({ isProgramHead }) {
  return (
    <div>
      <h1 className="text-xl font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
        {isProgramHead ? 'My Accreditation Cycle' : 'Accreditation Cycles'}
      </h1>
      <p className="text-[13px] text-[#8a8a8a] mt-1">
        {isProgramHead
          ? 'Your program accreditation cycle and compliance schedule.'
          : 'Active accreditation cycles and their compliance status.'}
      </p>
    </div>
  )
}

function LegendItem({ color, label }) {
  return (
    <div className="flex items-center gap-2">
      <span className="inline-block w-2 h-2 rounded-full flex-shrink-0" style={{ background: color }} />
      <span className="text-[12px] text-[#8a8a8a]">{label}</span>
    </div>
  )
}

function EmptyState() {
  return (
    <div className="border border-[#252525] border-dashed rounded-md px-6 py-16 text-center">
      <div className="flex items-center justify-center w-10 h-10 bg-[#1e1e1e] rounded-md mx-auto mb-4">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#8a8a8a" strokeWidth="1.5">
          <path strokeLinecap="round" strokeLinejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <p className="text-[14px] font-medium text-[#d0d0d0] mb-1">No active cycles</p>
      <p className="text-[13px] text-[#6b6b6b]">
        Accreditation cycles will appear here once they are launched by an administrator.
      </p>
    </div>
  )
}

function CycleCard({ cycle }) {
  const urgencyColors = {
    green: { border: '#76ff03', badge: 'bg-[#76ff03]/10 text-[#76ff03]' },
    amber: { border: '#f59e0b', badge: 'bg-[#f59e0b]/10 text-[#f59e0b]' },
    red:   { border: '#ef4444', badge: 'bg-[#ef4444]/10 text-[#ef4444]' },
  }
  const uc = urgencyColors[cycle.urgency] || urgencyColors.green

  return (
    <Link
      to={`/cycles/${cycle.cycleId}`}
      className="
        group block bg-[#1a1a1a] border border-[#252525] rounded-md p-5
        hover:border-[#3a3a3a] hover:bg-[#1e1e1e]
        transition-all duration-150
      "
      style={{ borderLeft: `3px solid ${uc.border}` }}
    >
      {/* Header row */}
      <div className="flex items-start justify-between gap-3 mb-3">
        <div className="flex items-center gap-2 min-w-0">
          <span className="text-[15px] font-semibold text-white leading-none" style={{ fontFamily: 'Poppins, sans-serif' }}>
            {cycle.programCode}
          </span>
          <span className="text-[10px] font-medium text-[#6b6b6b] bg-[#252525] px-1.5 py-0.5 rounded uppercase tracking-wide">
            {cycle.collegeCode}
          </span>
        </div>
        <span className={`flex-shrink-0 text-[11px] font-medium px-2 py-0.5 rounded ${uc.badge}`}>
          {getUrgencyLabel(cycle.daysRemaining)}
        </span>
      </div>

      {/* Program name */}
      <p className="text-[12px] text-[#8a8a8a] mb-4 leading-snug line-clamp-2">
        {cycle.programName}
      </p>

      {/* Meta */}
      <div className="space-y-1 border-t border-[#252525] pt-3">
        <div className="flex justify-between items-center">
          <span className="text-[11px] text-[#6b6b6b]">Academic Year</span>
          <span className="text-[12px] text-[#c0c0c0] font-medium">AY {cycle.academicYear}</span>
        </div>
        <div className="flex justify-between items-center">
          <span className="text-[11px] text-[#6b6b6b]">Deadline</span>
          <span className="text-[12px] text-[#c0c0c0] font-medium">{formatDate(cycle.deadline)}</span>
        </div>
        <div className="flex justify-between items-center">
          <span className="text-[11px] text-[#6b6b6b]">Status</span>
          <StatusPill status={cycle.status} />
        </div>
      </div>

      {cycle.isOverdueForRenewal && (
        <div className="mt-3 flex items-center gap-1.5 text-[11px] text-[#f59e0b]">
          <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          Overdue for renewal
        </div>
      )}
    </Link>
  )
}

function StatusPill({ status }) {
  const styles = {
    active:    'bg-[#76ff03]/10 text-[#76ff03]',
    completed: 'bg-[#3b82f6]/10 text-[#60a5fa]',
    default:   'bg-[#252525] text-[#8a8a8a]',
  }
  const cls = styles[status] || styles.default

  return (
    <span className={`text-[11px] font-medium px-1.5 py-0.5 rounded uppercase tracking-wide ${cls}`}>
      {status}
    </span>
  )
}
