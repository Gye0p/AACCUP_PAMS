import { useEffect, useRef } from 'react'
import FrappeGantt from 'frappe-gantt'
import '../frappe-gantt.css'

export default function GanttChart({ tasks }) {
  const ganttRef = useRef(null)
  const ganttInstanceRef = useRef(null)

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
              background: #ffffff;
              border: 1px solid #dbe4f0;
              border-radius: 4px;
              min-width: 160px;
            ">
              <div style="font-size: 12px; font-weight: 600; color: #18243a; margin-bottom: 4px;">${task.name}</div>
              <div style="font-size: 11px; color: #718096;">Progress: <span style="color: #1768c7;">${task.progress}%</span></div>
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
    <div className="overflow-x-auto">
      <svg ref={ganttRef} style={{ minWidth: '100%' }} />
    </div>
  )
}
