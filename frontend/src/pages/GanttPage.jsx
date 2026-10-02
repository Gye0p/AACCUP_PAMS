import { useEffect, useRef } from 'react'
import { useParams, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import FrappeGantt from 'frappe-gantt'
import '../frappe-gantt.css'
import { fetchGantt } from '../api'
import Layout from '../components/Layout'

export default function GanttPage() {
  const { id } = useParams()
  const ganttRef = useRef(null)
  const ganttInstanceRef = useRef(null)

  const { data: tasks, isLoading, error } = useQuery({
    queryKey: ['gantt', id],
    queryFn: () => fetchGantt(id)
  })

  useEffect(() => {
    if (tasks?.length > 0 && ganttRef.current) {
      ganttInstanceRef.current = new FrappeGantt(ganttRef.current, tasks, {
        header_height: 50,
        column_width: 30,
        step: 24,
        view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
        bar_height: 20,
        bar_corner_radius: 2,
        arrow_curve: 5,
        padding: 18,
        view_mode: 'Week',
        date_format: 'YYYY-MM-DD',
        custom_popup_html: function(task) {
          return `
            <div style="
              padding: 10px 14px;
              background: #1e1e1e;
              border: 1px solid #2e2e2e;
              border-radius: 4px;
              min-width: 160px;
            ">
              <div style="font-size: 12px; font-weight: 600; color: #f5f5f5; margin-bottom: 4px;">${task.name}</div>
              <div style="font-size: 11px; color: #8a8a8a;">Progress: <span style="color: #76ff03;">${task.progress}%</span></div>
            </div>
          `
        }
      })
    }
    return () => {
      ganttInstanceRef.current = null
    }
  }, [tasks])

  return (
    <Layout>
      {/* Header */}
      <div className="flex items-start justify-between gap-4 flex-wrap mb-6">
        <div>
          <nav className="flex items-center gap-2 text-[12px] text-[#6b6b6b] mb-2">
            <Link to={`/cycles/${id}`} className="hover:text-[#b0b0b0] transition-colors">
              Cycle Detail
            </Link>
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
              <path strokeLinecap="round" strokeLinejoin="round" d="M9 18l6-6-6-6"/>
            </svg>
            <span className="text-[#8a8a8a]">Compliance Schedule</span>
          </nav>
          <h1 className="text-xl font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
            Compliance Schedule
          </h1>
          <p className="text-[13px] text-[#8a8a8a] mt-1">
            Gantt chart of approved and completed activities for this cycle.
          </p>
        </div>
      </div>

      {/* States */}
      {isLoading && (
        <div className="bg-[#1a1a1a] border border-[#252525] rounded-md p-8">
          <div className="space-y-3 animate-pulse">
            <div className="h-10 bg-[#222] rounded" />
            {[1,2,3,4,5].map(i => <div key={i} className="h-8 bg-[#1e1e1e] rounded" />)}
          </div>
        </div>
      )}

      {error && (
        <div className="flex items-center gap-3 bg-[#1e1212] border border-[#4a1a1a] rounded-md px-5 py-4">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#f87171" strokeWidth="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <span className="text-[13px] text-[#f87171]">Failed to load schedule data. Please refresh and try again.</span>
        </div>
      )}

      {!isLoading && !error && tasks?.length === 0 && (
        <div className="border border-dashed border-[#252525] rounded-md px-6 py-16 text-center">
          <div className="flex items-center justify-center w-10 h-10 bg-[#1e1e1e] rounded-md mx-auto mb-4">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#6b6b6b" strokeWidth="1.5">
              <path strokeLinecap="round" strokeLinejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </div>
          <p className="text-[14px] font-medium text-[#d0d0d0] mb-1">No schedule data</p>
          <p className="text-[13px] text-[#6b6b6b]">
            Approved and completed activities will appear in the chart once available.
          </p>
        </div>
      )}

      {!isLoading && tasks?.length > 0 && (
        <div className="bg-[#1a1a1a] border border-[#252525] rounded-md overflow-hidden">
          <div className="overflow-x-auto">
            <svg ref={ganttRef} style={{ minWidth: '100%' }} />
          </div>
        </div>
      )}
    </Layout>
  )
}
